<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use App\Services\Billing\PayPalPaymentService;
use App\Services\OrderMailService;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index()
    {
        $user = auth()->user();
        
        $orders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Transform the orders data
        $transformedOrders = $orders->through(function ($order) {
            $serviceTypeDisplay = $this->getServiceTypeDisplayName($order->service_type);
            $statusDisplay = $this->getStatusDisplay($order->status);
            $progressPercentage = $this->getProgressPercentage($order->status);

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'entity_name' => $order->entity_name ?? 'N/A',
                'service_type' => $order->service_type,
                'service_type_display' => $serviceTypeDisplay,
                'status' => $order->status,
                'status_display' => $statusDisplay,
                'progress_percentage' => $progressPercentage,
                'total_amount' => (float) $order->total_amount ?? 0,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];
        });

        return Inertia::render('Orders/Index', [
            'orders' => $transformedOrders,
            'stats' => [
                'total_orders'  => Order::where('user_id', $user->id)->count(),
                'pending'       => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
                'in_progress'   => Order::where('user_id', $user->id)->whereIn('status', ['in_progress', 'under_review', 'approved', 'filed'])->count(),
                'completed'     => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
                'total_revenue' => (float) Order::where('user_id', $user->id)->whereNotIn('status', ['cancelled', 'refunded'])->sum('total_amount'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Orders/Create');
    }

    // ------------------------------------------------------------------
    // Order Draft persistence (one draft per user)
    // ------------------------------------------------------------------

    public function getDraft(Request $request)
    {
        $draft = \App\Models\OrderDraft::where('user_id', $request->user()->id)->first();
        return response()->json(['draft' => $draft?->form_data]);
    }

    public function saveDraft(Request $request)
    {
        $data = $request->all();
        unset($data['_token']); // strip CSRF token if inadvertently included
        \App\Models\OrderDraft::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['form_data' => $data]
        );
        return response()->json(['saved' => true]);
    }

    public function deleteDraft(Request $request)
    {
        \App\Models\OrderDraft::where('user_id', $request->user()->id)->delete();
        return response()->json(['deleted' => true]);
    }

    /**
     * Create Stripe checkout session for order payment
     */
    public function checkout(Request $request)    {
        // Validate order data (no documents - they'll be uploaded after payment)
        $validated = $request->validate([
            'serviceType' => 'required|string|in:C-Corporation,S-Corporation,LLC,Nonprofit,Green Card Lottery,Income Tax',
            'businessName' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'businessPurpose' => 'nullable|string',
            'speedOption' => 'required|string|in:economic,pro',
            'addons' => 'array',
            'applicantName' => 'nullable|string|max:255',
            'applicantEmail' => 'nullable|email|max:255',
            'applicantPhone' => 'nullable|string|max:50',
            'applicantDob' => 'nullable|date',
            'applicantSsn' => 'nullable|string|max:20',
            'applicantAddress' => 'nullable|string|max:500',
            'applicantCity' => 'nullable|string|max:100',
            'applicantZip' => 'nullable|string|max:20',
            'applicantCountry' => 'nullable|string|max:100',
        ]);

        // Map frontend speed options to backend package types
        $packageType = match($validated['speedOption']) {
            'economic' => 'starter',
            'pro' => 'premium',
            default => 'starter'
        };

        // For green card, use different package naming
        if ($validated['serviceType'] === 'Green Card Lottery') {
            $packageType = match($validated['speedOption']) {
                'economic' => 'basic',
                'pro' => 'premium',
                default => 'basic'
            };
        }

        // Calculate pricing with add-ons
        $pricing = $this->calculateOrderPricing(
            $validated['serviceType'], 
            $packageType, 
            $validated['speedOption'],
            $validated['addons'] ?? []
        );

        // Store only basic data in session (no file uploads)
        session([
            'pending_order' => [
                'serviceType' => $validated['serviceType'],
                'businessName' => $validated['businessName'] ?? null,
                'state' => $validated['state'] ?? null,
                'businessPurpose' => $validated['businessPurpose'] ?? null,
                'speedOption' => $validated['speedOption'],
                'addons' => $validated['addons'] ?? [],
                'applicantInfo' => [
                    'full_name' => $validated['applicantName'] ?? null,
                    'email' => $validated['applicantEmail'] ?? null,
                    'phone' => $validated['applicantPhone'] ?? null,
                    'date_of_birth' => $validated['applicantDob'] ?? null,
                    'ssn' => $validated['applicantSsn'] ?? null,
                    'address' => $validated['applicantAddress'] ?? null,
                    'city' => $validated['applicantCity'] ?? null,
                    'zip' => $validated['applicantZip'] ?? null,
                    'country' => $validated['applicantCountry'] ?? null,
                ],
            ],
            'pending_order_pricing' => $pricing,
        ]);

        $serviceNames = [
            'C-Corporation'      => 'C-Corporation Formation',
            'S-Corporation'      => 'S-Corporation Formation',
            'LLC'                => 'LLC Formation',
            'Nonprofit'          => 'Nonprofit Formation',
            'Green Card Lottery' => 'Green Card Lottery Application',
            'Income Tax'         => 'Income Tax Filing & Planning',
        ];

        // Redirect to payment page
        return Inertia::render('Orders/Payment', [
            'orderData' => $validated,
            'amount' => $pricing['total'],
            'serviceName' => $serviceNames[$validated['serviceType']] ?? 'Business Service',
            'pricing' => $pricing,
            'usdcWalletAddress' => config('services.stripe.usdc_wallet_address', ''),
        ]);
    }

    /**
     * Store a newly created order (3-step wizard from dashboard).
     */
    public function store(Request $request)
    {
        $allServices = [
            'C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit',
            'Green Card Lottery', 'Income Tax',
        ];

        $allAddons = [
            'ein', 'registered_agent', 'operating_agreement', 'annual_report',
            'apostille', 'good_standing', 'mail_forwarding', 'rush',
            'itin', 'tax_consult', 'banking',
            'gc_premium', 'gc_notification',
            'tax_bookkeeping', 'tax_amendment',
        ];

        $request->validate([
            'service_type'   => 'required|string|in:' . implode(',', $allServices),
            'state'          => 'required|string|max:100',
            'company_name'   => 'required|string|max:255',
            'payment_method' => 'required|string|in:stripe,paypal,wire',
            'addons'         => 'nullable|array',
            'addons.*'       => 'string|in:' . implode(',', $allAddons),
        ]);

        $user        = $request->user();
        $addonIds    = $request->addons ?? [];
        $addonsTotal = $this->calcAddonsTotal($addonIds);
        $basePrice   = $this->getServiceBasePrice($request->service_type);
        $totalAmount = $basePrice + $addonsTotal;

        $user->orders()->create([
            'order_number'   => 'ORD-' . strtoupper(uniqid()),
            'service_type'   => $request->service_type,
            'state'          => $request->state,
            'entity_name'    => $request->company_name,
            'payment_method' => $request->payment_method,
            'status'         => 'pending',
            'service_fee'    => $basePrice,
            'addons'         => $addonIds ?: null,
            'addons_total'   => $addonsTotal,
            'total_amount'   => $totalAmount,
            'currency'       => 'USD',
        ]);

        $this->markClientPendingApproval($user);

        // Notify client + admin about new order
        app(OrderMailService::class)->sendOrderCreated(
            $user->orders()->latest()->first()
        );

        return redirect()->route($this->isPendingReviewClient($user) ? 'onboarding.review' : 'orders.index')
            ->with('success', 'Order placed successfully! We will begin processing shortly.');
    }

    private function getServiceBasePrice(string $serviceType): float
    {
        return match ($serviceType) {
            'C-Corporation'      => 399.00,
            'S-Corporation'      => 880.00,
            'LLC'                => 1480.00,
            'Nonprofit'          => 499.00,
            'Green Card Lottery' => 49.00,
            'Income Tax'         => 499.00,
            default              => 0.00,
        };
    }

    private function calcAddonsTotal(array $ids): float
    {
        $prices = [
            'ein'                => 99.00,
            'registered_agent'   => 149.00,
            'operating_agreement'=> 99.00,
            'annual_report'      => 149.00,
            'apostille'          => 149.00,
            'good_standing'      => 99.00,
            'mail_forwarding'    => 99.00,
            'rush'               => 99.00,
            'itin'               => 249.00,
            'tax_consult'        => 149.00,
            'banking'            => 49.00,
            'gc_premium'         => 79.00,
            'gc_notification'    => 29.00,
            'tax_bookkeeping'    => 299.00,
            'tax_amendment'      => 199.00,
        ];
        return array_sum(array_map(fn ($id) => $prices[$id] ?? 0, $ids));
    }

    // ── legacy store logic kept below for reference ─────────────────────────
    private function storeLegacy(Request $request)
    {
        $isGreenCard = $request->serviceType === 'green_card';

        $validated = $request->validate([
            'serviceType' => 'required|string|in:c_corp,s_corp,llc,nonprofit,green_card',
            'businessName' => 'nullable|string|max:255',
            'state' => $isGreenCard ? 'nullable|string|max:255' : 'required|string|max:255',
            'businessPurpose' => 'nullable|string',
            'speedOption' => 'required|string|in:economic,pro',
            'addons' => 'array',
            'applicantName' => 'nullable|string|max:255',
            'applicantEmail' => 'nullable|email|max:255',
            'applicantPhone' => 'nullable|string|max:50',
            'applicantDob' => 'nullable|date',
            'applicantSsn' => 'nullable|string|max:20',
            'applicantAddress' => 'nullable|string|max:500',
            'applicantCity' => 'nullable|string|max:100',
            'applicantZip' => 'nullable|string|max:20',
            'applicantCountry' => 'nullable|string|max:100',
            'documents' => 'required|array',
            'documents.passport' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:20480',
            'documents.id_card' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:20480',
            'documents.drivers_license' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:20480',
            'documents.photos' => 'nullable|array|max:2',
            'documents.photos.*' => 'file|mimes:jpeg,jpg,png|max:10240',
            'paymentMethod' => 'required|string|in:stripe,paypal,apple_pay,google_pay,bank_transfer',
            'paymentScreenshot' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ]);

        // For non-Green Card services, business name is required
        if ($validated['serviceType'] !== 'green_card' && empty($validated['businessName'])) {
            return back()->withErrors(['businessName' => 'Business name is required for this service type.']);
        }

        // Document validation
        $hasIdentityDoc = (isset($validated['documents']['passport']) && $validated['documents']['passport'] instanceof \Illuminate\Http\UploadedFile) || 
                         (isset($validated['documents']['id_card']) && $validated['documents']['id_card'] instanceof \Illuminate\Http\UploadedFile) || 
                         (isset($validated['documents']['drivers_license']) && $validated['documents']['drivers_license'] instanceof \Illuminate\Http\UploadedFile);
        
        if (!$hasIdentityDoc) {
            return back()->withErrors(['documents' => 'Please upload at least one identity document (Passport, ID Card, or Driver\'s License).']);
        }

        // Green Card specific validation
        if ($isGreenCard) {
            $photoCount = 0;
            if (isset($validated['documents']['photos']) && is_array($validated['documents']['photos'])) {
                foreach ($validated['documents']['photos'] as $photo) {
                    if ($photo instanceof \Illuminate\Http\UploadedFile) {
                        $photoCount++;
                    }
                }
            }
            
            if ($photoCount < 2) {
                return back()->withErrors(['documents.photos' => 'Green Card application requires 2 photos.']);
            }
        }

        // Handle file uploads
        $documentPaths = [];
        $documentTypes = ['passport', 'id_card', 'drivers_license'];
        
        foreach ($documentTypes as $type) {
            if (isset($validated['documents'][$type]) && $validated['documents'][$type] instanceof \Illuminate\Http\UploadedFile) {
                $file = $validated['documents'][$type];
                $path = $file->store('order-documents/' . date('Y/m'), 'private');
                $documentPaths[$type] = [
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        // Handle photo uploads for Green Card
        if (isset($validated['documents']['photos']) && is_array($validated['documents']['photos'])) {
            $documentPaths['photos'] = [];
            foreach ($validated['documents']['photos'] as $index => $photo) {
                if ($photo instanceof \Illuminate\Http\UploadedFile) {
                    $path = $photo->store('order-documents/' . date('Y/m') . '/photos', 'private');
                    $documentPaths['photos'][] = [
                        'path' => $path,
                        'original_name' => $photo->getClientOriginalName(),
                        'size' => $photo->getSize(),
                        'mime_type' => $photo->getMimeType(),
                    ];
                }
            }
        }

        // Handle payment screenshot upload
        $paymentScreenshotPath = null;
        if (isset($validated['paymentScreenshot']) && $validated['paymentScreenshot'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $validated['paymentScreenshot'];
            $path = $file->store('payment-screenshots/' . date('Y/m'), 'private');
            $paymentScreenshotPath = [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];
        }

        // Calculate pricing based on service and package type
        $packageType = match($validated['speedOption']) {
            'economic' => 'starter',
            'pro' => 'premium',
            default => 'starter'
        };

        // For green card, use different package naming
        if ($validated['serviceType'] === 'green_card') {
            $packageType = match($validated['speedOption']) {
                'economic' => 'basic',
                'pro' => 'premium',
                default => 'basic'
            };
        }

        $pricing = $this->calculateOrderPricing(
            $validated['serviceType'],
            $packageType,
            $validated['speedOption'],
            $validated['addons'] ?? []
        );

        $order = $request->user()->orders()->create([
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'service_type' => $validated['serviceType'],
            'package_type' => $validated['speedOption'],
            'entity_name' => $validated['businessName'] ?? ($validated['serviceType'] === 'green_card' ? 'Green Card Application' : 'N/A'),
            'state' => $this->getStateAbbreviation($validated['state']),
            'business_purpose' => $validated['businessPurpose'],
            'requirements' => $validated['addons'] ?? [],
            'required_documents' => $documentPaths,
            'payment_method' => $validated['paymentMethod'],
            'payment_screenshot' => $paymentScreenshotPath,
            'subtotal' => $pricing['subtotal'],
            'state_fee' => $pricing['state_fee'],
            'processing_fee' => $pricing['processing_fee'],
            'total_amount' => $pricing['total'],
            'status' => 'pending',
            'estimated_completion_date' => now()->addDays($pricing['estimated_days']),
            'applicant_info' => [
                'full_name' => $validated['applicantName'] ?? null,
                'email' => $validated['applicantEmail'] ?? null,
                'phone' => $validated['applicantPhone'] ?? null,
                'date_of_birth' => $validated['applicantDob'] ?? null,
                'ssn' => $validated['applicantSsn'] ?? null,
                'address' => $validated['applicantAddress'] ?? null,
                'city' => $validated['applicantCity'] ?? null,
                'zip' => $validated['applicantZip'] ?? null,
                'country' => $validated['applicantCountry'] ?? null,
            ],
        ]);

        return redirect()->route('orders.index')
            ->with('success', 'Your order has been successfully submitted! We will begin processing your order shortly.');
    }

    /**
     * Process payment from payment page
     */
    /**
     * Create a Stripe PaymentIntent and return client_secret to frontend.
     */
    public function createPaymentIntent(Request $request)
    {
        $pricing = session('pending_order_pricing');

        if (!$pricing) {
            return response()->json(['error' => 'Session expired. Please start over.'], 422);
        }

        $request->validate([
            'name'    => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city'    => 'nullable|string|max:255',
            'state'   => 'nullable|string|max:255',
            'country' => 'nullable|string|max:2',
        ]);

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            $amountInCents = (int) round($pricing['total'] * 100);

            $orderData = session('pending_order');
            $serviceNames = [
                'C-Corporation'      => 'C-Corporation Formation',
                'S-Corporation'      => 'S-Corporation Formation',
                'LLC'                => 'LLC Formation',
                'Nonprofit'          => 'Nonprofit Formation',
                'Green Card Lottery' => 'Green Card Lottery Application',
                'Income Tax'         => 'Income Tax Filing',
            ];
            $description = $serviceNames[$orderData['serviceType'] ?? ''] ?? 'Business Service';

            $intent = \Stripe\PaymentIntent::create([
                'amount'      => $amountInCents,
                'currency'    => 'usd',
                'description' => $description,
                'metadata'    => [
                    'user_id'      => auth()->id(),
                    'service_type' => $orderData['serviceType'] ?? '',
                    'business'     => $orderData['businessName'] ?? '',
                ],
                'receipt_email' => auth()->user()->email,
            ]);

            // Store intent ID in session for verification at confirm step
            session(['pending_payment_intent_id' => $intent->id]);

            return response()->json(['client_secret' => $intent->client_secret]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe PaymentIntent error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function processPayment(Request $request)
    {
        // Get pending order data from session
        $orderData = session('pending_order');
        $pricing   = session('pending_order_pricing');

        if (!$orderData || !$pricing) {
            return response()->json(['error' => 'Session expired. Please start over.'], 422);
        }

        $request->validate([
            'payment_intent_id' => 'required|string',
            'payment_method'    => 'nullable|string|in:stripe,apple_pay,google_pay',
        ]);

        $paymentMethodLabel = $request->input('payment_method', 'stripe');

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            // Retrieve and verify the PaymentIntent from Stripe
            $intent = \Stripe\PaymentIntent::retrieve($request->payment_intent_id);

            if ($intent->status !== 'succeeded') {
                return response()->json(['error' => 'Payment not completed. Status: ' . $intent->status], 400);
            }

            // Verify amount matches
            $expectedCents = (int) round($pricing['total'] * 100);
            if ($intent->amount_received !== $expectedCents) {
                Log::warning('Stripe amount mismatch', [
                    'expected' => $expectedCents,
                    'received' => $intent->amount_received,
                ]);
            }

            $orderNumber = 'ORD-' . strtoupper(uniqid());

            $order = $request->user()->orders()->create([
                'order_number'             => $orderNumber,
                'service_type'             => $orderData['serviceType'],
                'package_type'             => $orderData['speedOption'],
                'entity_name'              => $orderData['businessName'] ?? ($orderData['serviceType'] === 'green_card' ? 'Green Card Application' : 'N/A'),
                'state'                    => $this->getStateAbbreviation($orderData['state'] ?? ''),
                'business_purpose'         => $orderData['businessPurpose'] ?? null,
                'requirements'             => $orderData['addons'] ?? [],
                'required_documents'       => [],
                'payment_method'           => $paymentMethodLabel,
                'subtotal'                 => $pricing['subtotal'],
                'state_fee'                => $pricing['state_fee'],
                'processing_fee'           => $pricing['processing_fee'],
                'total_amount'             => $pricing['total'],
                'status'                   => 'in_progress',
                'estimated_completion_date'=> now()->addDays($pricing['estimated_days']),
                'applicant_info'           => $orderData['applicantInfo'] ?? null,
            ]);

            $card            = null;
            $charge          = null;
            $billingAddress  = [];
            $cardHolderName  = null;
            $receiptUrl      = null;
            $stripeFee       = 0;
            $netAmount       = $pricing['total'];
            $stripeCustomerId = null;
            $stripePaymentMethodId = $intent->payment_method ?? null;

            if ($intent->latest_charge) {
                $charge = \Stripe\Charge::retrieve([
                    'id'     => $intent->latest_charge,
                    'expand' => ['balance_transaction', 'payment_method_details'],
                ]);

                $card           = $charge->payment_method_details?->card ?? null;
                $receiptUrl     = $charge->receipt_url ?? null;
                $cardHolderName = $charge->billing_details?->name ?? null;
                $stripeCustomerId = $charge->customer ?? null;

                // Billing address from Stripe charge
                $billingAddr = $charge->billing_details?->address ?? null;
                $billingAddress = [
                    'name'    => $charge->billing_details?->name ?? null,
                    'email'   => $charge->billing_details?->email ?? null,
                    'phone'   => $charge->billing_details?->phone ?? null,
                    'address' => [
                        'line1'       => $billingAddr?->line1 ?? null,
                        'line2'       => $billingAddr?->line2 ?? null,
                        'city'        => $billingAddr?->city ?? null,
                        'state'       => $billingAddr?->state ?? null,
                        'postal_code' => $billingAddr?->postal_code ?? null,
                        'country'     => $billingAddr?->country ?? null,
                    ],
                ];

                // Stripe processing fee from balance transaction
                if ($charge->balance_transaction && isset($charge->balance_transaction->fee)) {
                    $stripeFee = round($charge->balance_transaction->fee / 100, 2);
                    $netAmount = round(($charge->balance_transaction->net ?? ($pricing['total'] * 100 - $charge->balance_transaction->fee)) / 100, 2);
                }
            }

            // Fallback: use user's profile address if Stripe billing is empty
            $user = $request->user();
            if (empty(array_filter($billingAddress['address'] ?? []))) {
                $billingAddress = [
                    'name'    => $cardHolderName ?? $user->name,
                    'email'   => $user->email,
                    'phone'   => $user->phone ?? null,
                    'address' => [
                        'line1'       => $user->address_line_1 ?? null,
                        'line2'       => $user->address_line_2 ?? null,
                        'city'        => $user->city ?? null,
                        'state'       => $user->state ?? null,
                        'postal_code' => $user->zip_code ?? null,
                        'country'     => $user->country ?? null,
                    ],
                ];
            }

            Payment::create([
                'user_id'                   => auth()->id(),
                'order_id'                  => $order->id,
                'type'                      => 'order_payment',
                'payment_method'            => 'card',
                'status'                    => 'succeeded',
                'amount'                    => $pricing['total'],
                'currency'                  => 'usd',
                'net_amount'                => $netAmount,
                'processing_fee'            => $stripeFee,
                'stripe_payment_intent_id'  => $intent->id,
                'stripe_charge_id'          => $intent->latest_charge ?? null,
                'stripe_customer_id'        => $stripeCustomerId ?? $user->stripe_id ?? null,
                'stripe_payment_method_id'  => $stripePaymentMethodId,
                'card_last_four'            => $card?->last4 ?? null,
                'card_brand'                => $card?->brand ?? null,
                'card_exp_month'            => $card?->exp_month ?? null,
                'card_exp_year'             => $card?->exp_year ?? null,
                'billing_address'           => $billingAddress,
                'stripe_response'           => $charge ? [
                    'billing_details' => [
                        'name'    => $charge->billing_details?->name,
                        'email'   => $charge->billing_details?->email,
                        'phone'   => $charge->billing_details?->phone,
                        'address' => (array)($charge->billing_details?->address ?? []),
                    ],
                    'payment_method_details' => [
                        'type' => $charge->payment_method_details?->type,
                        'card' => [
                            'brand'    => $card?->brand,
                            'last4'    => $card?->last4,
                            'exp_month'=> $card?->exp_month,
                            'exp_year' => $card?->exp_year,
                            'country'  => $card?->country,
                            'funding'  => $card?->funding,
                            'network'  => $card?->network,
                        ],
                    ],
                ] : [],
                'receipt_url'               => $receiptUrl,
                'processed_at'              => now(),
            ]);

            $user = $request->user();
            $this->markClientPendingApproval($user);

            session()->forget(['pending_order', 'pending_order_pricing', 'pending_payment_intent_id']);

            app(OrderMailService::class)->sendPaymentConfirmed(
                $order,
                now()->format('F j, Y, g:i A') . ' UTC'
            );

            return response()->json([
                'success' => true,
                'redirect' => route($this->isPendingReviewClient($user) ? 'onboarding.review' : 'orders.index'),
            ]);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe processPayment error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Payment processing error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment processing failed. Please try again.'], 500);
        }
    }

    /**
     * Process a PayPal payment and create the order.
     */
    public function processPayPalPayment(Request $request)
    {
        $orderData = session('pending_order');
        $pricing   = session('pending_order_pricing');

        if (!$orderData || !$pricing) {
            return response()->json(['error' => 'Session expired. Please start over.'], 422);
        }

        $request->validate([
            'paypal_order_id' => 'required|string',
        ]);

        try {
            $paypalService = app(PayPalPaymentService::class);
            $result = $paypalService->captureOrder($request->paypal_order_id);

            if (!$result['success']) {
                return response()->json(['error' => $result['error'] ?? 'PayPal payment capture failed.'], 400);
            }

            $order = $request->user()->orders()->create([
                'order_number'             => 'ORD-' . strtoupper(uniqid()),
                'service_type'             => $orderData['serviceType'],
                'package_type'             => $orderData['speedOption'],
                'entity_name'              => $orderData['businessName'] ?? ($orderData['serviceType'] === 'green_card' ? 'Green Card Application' : 'N/A'),
                'state'                    => $this->getStateAbbreviation($orderData['state'] ?? ''),
                'business_purpose'         => $orderData['businessPurpose'] ?? null,
                'requirements'             => $orderData['addons'] ?? [],
                'required_documents'       => [],
                'payment_method'           => 'paypal',
                'subtotal'                 => $pricing['subtotal'],
                'state_fee'                => $pricing['state_fee'],
                'processing_fee'           => $pricing['processing_fee'],
                'total_amount'             => $pricing['total'],
                'status'                   => 'in_progress',
                'estimated_completion_date'=> now()->addDays($pricing['estimated_days']),
                'applicant_info'           => $orderData['applicantInfo'] ?? null,
            ]);

            Payment::create([
                'user_id'        => auth()->id(),
                'order_id'       => $order->id,
                'type'           => 'order_payment',
                'payment_method' => 'bank_transfer',
                'status'         => 'succeeded',
                'amount'         => $pricing['total'],
                'net_amount'     => $pricing['total'],
                'processed_at'   => now(),
            ]);

            $user = $request->user();
            $this->markClientPendingApproval($user);

            session()->forget(['pending_order', 'pending_order_pricing']);

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'redirect' => route($this->isPendingReviewClient($user) ? 'onboarding.review' : 'orders.index'),
            ]);

        } catch (\Exception $e) {
            Log::error('PayPal payment error: ' . $e->getMessage());
            return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create a cryptocurrency payment session
     */
    public function createCryptoPayment(Request $request)
    {
        $orderData = session('pending_order');
        $pricing   = session('pending_order_pricing');

        if (!$orderData || !$pricing) {
            return response()->json(['error' => 'Session expired. Please start over.'], 422);
        }

        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'currency'    => 'required|string|in:usdc',
            'description' => 'nullable|string|max:255',
            'screenshot'  => 'required|file|image|max:10240',
        ]);

        try {
            // Store the payment screenshot
            $screenshotPath = $request->file('screenshot')->store('payment_screenshots', 'public');

            $packageType = match($orderData['speedOption'] ?? 'economic') {
                'pro'      => ($orderData['serviceType'] === 'Green Card Lottery') ? 'premium' : 'premium',
                default    => ($orderData['serviceType'] === 'Green Card Lottery') ? 'basic'   : 'starter',
            };

            $order = $request->user()->orders()->create([
                'order_number'    => 'ORD-' . strtoupper(uniqid()),
                'service_type'    => $orderData['serviceType'],
                'package_type'    => $packageType,
                'entity_name'     => $orderData['businessName'] ?? ($orderData['serviceType'] === 'Green Card Lottery' ? 'Green Card Application' : 'N/A'),
                'state'           => $this->getStateAbbreviation($orderData['state'] ?? ''),
                'business_purpose'=> $orderData['businessPurpose'] ?? null,
                'requirements'    => $orderData['addons'] ?? [],
                'payment_method'  => 'crypto_usdc',
                'payment_screenshot' => $screenshotPath,
                'status'          => 'pending',
                'service_fee'     => $pricing['subtotal'] ?? $pricing['total'],
                'state_fee'       => $pricing['state_fee'] ?? 0,
                'processing_fee'  => $pricing['processing_fee'] ?? 0,
                'total_amount'    => $pricing['total'],
                'amount_paid'     => 0,
                'currency'        => 'usd',
                'addons'          => $orderData['addons'] ?? [],
                'applicant_info'  => $orderData['applicantInfo'] ?? [],
            ]);

            Payment::create([
                'user_id'       => auth()->id(),
                'order_id'      => $order->id,
                'type'          => 'order_payment',
                'payment_method'=> 'crypto_usdc',
                'status'        => 'pending',
                'amount'        => $pricing['total'],
                'currency'      => 'usd',
                'net_amount'    => $pricing['total'],
                'stripe_response' => ['wallet' => config('services.stripe.usdc_wallet_address'), 'screenshot' => $screenshotPath],
                'processed_at'  => now(),
            ]);

            $user = $request->user();
            $this->markClientPendingApproval($user);

            session()->forget(['pending_order', 'pending_order_pricing', 'pending_crypto_payment_link']);

            // Notify admin with screenshot attached
            app(OrderMailService::class)->sendCryptoPaymentReceived($order, storage_path('app/public/' . $screenshotPath));

            return response()->json([
                'success'  => true,
                'redirect' => route($this->isPendingReviewClient($user) ? 'onboarding.review' : 'orders.index'),
            ]);

        } catch (\Exception $e) {
            Log::error('Crypto direct payment error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to submit payment. Please try again.'], 500);
        }
    }

    /**
     * Handle successful crypto payment redirect
     */
    public function cryptoPaymentSuccess(Request $request)
    {
        $orderData = session('pending_order');
        $pricing   = session('pending_order_pricing');

        if (!$orderData || !$pricing) {
            return redirect()->route('orders.create')->with('error', 'Session expired. Please start over.');
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            // Retrieve the checkout session
            $sessionId = $request->query('session_id');
            if (!$sessionId) {
                return redirect()->route('orders.create')->with('error', 'Invalid payment session.');
            }

            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('orders.create')->with('error', 'Payment not completed.');
            }

            // Create the order
            $order = $request->user()->orders()->create([
                'order_number'             => 'ORD-' . strtoupper(uniqid()),
                'service_type'             => $orderData['serviceType'],
                'package_type'             => $orderData['speedOption'],
                'entity_name'              => $orderData['businessName'] ?? ($orderData['serviceType'] === 'green_card' ? 'Green Card Application' : 'N/A'),
                'state'                    => $this->getStateAbbreviation($orderData['state'] ?? ''),
                'business_purpose'         => $orderData['businessPurpose'] ?? null,
                'requirements'             => $orderData['addons'] ?? [],
                'required_documents'       => [],
                'payment_method'           => 'crypto',
                'subtotal'                 => $pricing['subtotal'],
                'state_fee'                => $pricing['state_fee'],
                'processing_fee'           => $pricing['processing_fee'],
                'total_amount'             => $pricing['total'],
                'status'                   => 'in_progress',
                'estimated_completion_date'=> now()->addDays($pricing['estimated_days']),
                'applicant_info'           => $orderData['applicantInfo'] ?? null,
            ]);

            // Create payment record
            Payment::create([
                'user_id'                   => auth()->id(),
                'order_id'                  => $order->id,
                'type'                      => 'order_payment',
                'payment_method'            => 'crypto',
                'status'                    => 'succeeded',
                'amount'                    => $pricing['total'],
                'currency'                  => 'usd',
                'net_amount'                => $pricing['total'],
                'stripe_payment_intent_id'  => $session->payment_intent,
                'stripe_customer_id'        => $session->customer,
                'stripe_response'           => ['session_id' => $sessionId],
                'processed_at'              => now(),
            ]);

            $user = $request->user();
            $this->markClientPendingApproval($user);

            session()->forget(['pending_order', 'pending_order_pricing', 'pending_crypto_payment_link']);

            app(OrderMailService::class)->sendPaymentConfirmed(
                $order,
                now()->format('F j, Y, g:i A') . ' UTC'
            );

            return redirect()->route($this->isPendingReviewClient($user) ? 'onboarding.review' : 'orders.index')
                ->with('success', 'Payment successful! Your order has been created.');

        } catch (\Exception $e) {
            Log::error('Crypto payment success handling error: ' . $e->getMessage());
            return redirect()->route('orders.create')->with('error', 'Failed to process payment completion.');
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): Response
    {
        // Ensure user can only view their own orders
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['payments', 'documents']);

        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'service_type' => $order->service_type_name,
            'status' => $order->status,
            'status_display' => $order->status_display,
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),
            'business_name' => $order->entity_name,
            'state' => $order->state,
            'package_type' => $order->package_type,
            'business_purpose' => $order->business_purpose,
            'contact_info' => $order->contact_info,
            'business_details' => $order->business_details,
            'requirements' => $order->requirements,
            'subtotal' => $order->subtotal * 100,
            'state_fee' => $order->state_fee * 100,
            'processing_fee' => $order->processing_fee * 100,
            'total' => $order->total_amount * 100,
            'progress' => [
                'step' => $order->progress_step,
                'total_steps' => 6,
                'current_stage' => $order->current_stage,
                'timeline' => $order->timeline_events,
            ],
            'estimated_completion' => $order->estimated_completion_date?->toISOString(),
            'documents' => $order->documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->display_name ?: $doc->name,
                    'type' => $doc->type_display_name,
                    'status' => $doc->status,
                    'created_at' => $doc->created_at->toISOString(),
                    'size' => $doc->formatted_file_size,
                ];
            }),
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount * 100,
                    'status' => $payment->status,
                    'method' => $payment->payment_method,
                    'created_at' => $payment->created_at->toISOString(),
                ];
            }),
        ];

        return Inertia::render('Orders/Show', [
            'order' => $orderData,
        ]);
    }

    /**
     * Calculate pricing for an order
     */
    private function calculateOrderPricing(string $serviceType, string $packageType, string $speedOption = 'economic', array $addons = []): array
    {
        // Base pricing matrix - flat service price, speed fee is charged separately
        $basePricing = [
            'C-Corporation'      => ['starter' => 399, 'standard' => 399, 'premium' => 399],
            'S-Corporation'      => ['starter' => 880, 'standard' => 880, 'premium' => 880],
            'LLC'                => ['starter' => 1480, 'standard' => 1480, 'premium' => 1480],
            'Nonprofit'          => ['starter' => 499, 'standard' => 499, 'premium' => 499],
            'Green Card Lottery' => ['basic' => 49, 'family' => 49, 'premium' => 49],
            'Income Tax'         => ['starter' => 499, 'standard' => 499, 'premium' => 499],
        ];

        $subtotal = $basePricing[$serviceType][$packageType] ?? 0;
        $stateFee = $serviceType === 'Green Card Lottery' ? 0 : 125; // No state fee for green card
        
        // Processing speed fee - matches frontend calculation
        $speedFee = $serviceType === 'Green Card Lottery' ? 0 : 
            ($speedOption === 'pro' ? 139 : ($speedOption === 'economic' ? 99 : 0));
        
        // Add-on pricing
        $addonPricing = [
            'registered_agent'    => 125,
            'ein'                 => 0,
            'corporate_kit'       => 85,
            'compliance'          => 50,
            'operating_agreement' => 99,
            'annual_report'       => 149,
            'apostille'           => 149,
            'good_standing'       => 99,
            'mail_forwarding'     => 99,
            'itin'                => 249,
            'tax_consult'         => 149,
            'banking'             => 49,
            'gc_premium'          => 79,
            'gc_notification'     => 29,
            'tax_bookkeeping'     => 299,
            'tax_amendment'       => 199,
        ];
        
        // Calculate add-ons total and create breakdown
        $addonsTotal = 0;
        $addonsBreakdown = [];
        foreach ($addons as $addonId) {
            if (isset($addonPricing[$addonId])) {
                $addonsTotal += $addonPricing[$addonId];
                $addonsBreakdown[$addonId] = [
                    'name' => $this->getAddonName($addonId),
                    'price' => $addonPricing[$addonId]
                ];
            }
        }
        
        $total = $subtotal + $stateFee + $speedFee + $addonsTotal;

        // Estimated completion days based on package tier
        $estimatedDays = match($packageType) {
            'premium' => 4,
            'standard' => 10,
            'starter', 'basic', 'family' => 21,
            default => 21
        };

        return [
            'subtotal' => $subtotal,
            'state_fee' => $stateFee,
            'processing_fee' => $speedFee,
            'addons_total' => $addonsTotal,
            'addons_breakdown' => $addonsBreakdown,
            'total' => $total,
            'estimated_days' => $estimatedDays,
        ];
    }

    /**
     * Get human-readable add-on name
     */
    private function getAddonName(string $addonId): string
    {
        $names = [
            'registered_agent' => 'Registered Agent (1 year)',
            'ein' => 'EIN Application',
            'corporate_kit' => 'Corporate Kit & Seal',
            'compliance' => 'Compliance Calendar',
            'operating_agreement' => 'Operating Agreement / By-Laws',
            'annual_report' => 'Annual Report Filing (1st Year)',
            'apostille' => 'Apostille / Document Authentication',
            'good_standing' => 'Certificate of Good Standing',
            'mail_forwarding' => 'USPS Mail Forwarding (1 Year)',
            'itin' => 'ITIN Application (Non-US Resident)',
            'tax_consult' => 'Tax Consultation (30 min)',
            'banking' => 'Business Bank Account Assistance',
            'gc_premium' => 'Green Card Premium Review',
            'gc_notification' => 'Result Notification Service',
            'tax_bookkeeping' => 'Monthly Bookkeeping (3 months)',
            'tax_amendment' => 'Prior Year Tax Amendment',
        ];
        
        return $names[$addonId] ?? $addonId;
    }

    /**
     * Get state abbreviation from full state name
     */
    private function getStateAbbreviation(?string $stateName): ?string
    {
        if (!$stateName) {
            return null;
        }

        $states = [
            'Alabama' => 'AL', 'Alaska' => 'AK', 'Arizona' => 'AZ', 'Arkansas' => 'AR',
            'California' => 'CA', 'Colorado' => 'CO', 'Connecticut' => 'CT', 'Delaware' => 'DE',
            'Florida' => 'FL', 'Georgia' => 'GA', 'Hawaii' => 'HI', 'Idaho' => 'ID',
            'Illinois' => 'IL', 'Indiana' => 'IN', 'Iowa' => 'IA', 'Kansas' => 'KS',
            'Kentucky' => 'KY', 'Louisiana' => 'LA', 'Maine' => 'ME', 'Maryland' => 'MD',
            'Massachusetts' => 'MA', 'Michigan' => 'MI', 'Minnesota' => 'MN', 'Mississippi' => 'MS',
            'Missouri' => 'MO', 'Montana' => 'MT', 'Nebraska' => 'NE', 'Nevada' => 'NV',
            'New Hampshire' => 'NH', 'New Jersey' => 'NJ', 'New Mexico' => 'NM', 'New York' => 'NY',
            'North Carolina' => 'NC', 'North Dakota' => 'ND', 'Ohio' => 'OH', 'Oklahoma' => 'OK',
            'Oregon' => 'OR', 'Pennsylvania' => 'PA', 'Rhode Island' => 'RI', 'South Carolina' => 'SC',
            'South Dakota' => 'SD', 'Tennessee' => 'TN', 'Texas' => 'TX', 'Utah' => 'UT',
            'Vermont' => 'VT', 'Virginia' => 'VA', 'Washington' => 'WA', 'West Virginia' => 'WV',
            'Wisconsin' => 'WI', 'Wyoming' => 'WY'
        ];

        return $states[$stateName] ?? $stateName;
    }

    /**
     * Get service type display name
     */
    private function getServiceTypeDisplayName($serviceType)
    {
        return match($serviceType) {
            'c_corp' => 'C-Corporation',
            's_corp' => 'S-Corporation', 
            'llc' => 'LLC Formation',
            'nonprofit' => 'Nonprofit Organization',
            'green_card' => 'Green Card Lottery',
            'ein_only' => 'EIN Only',
            'registered_agent' => 'Registered Agent',
            'compliance_kit' => 'Compliance Kit',
            'tax_filing' => 'Tax Filing',
            'bookkeeping' => 'Bookkeeping',
            default => ucfirst(str_replace('_', ' ', $serviceType))
        };
    }

    /**
     * Get status display name
     */
    private function getStatusDisplay($status)
    {
        return match($status) {
            'pending' => 'Pending Review',
            'in_progress' => 'In Progress',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'filed' => 'Filed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded',
            default => ucfirst(str_replace('_', ' ', $status))
        };
    }

    /**
     * Get progress percentage based on status
     */
    private function getProgressPercentage($status)
    {
        return match($status) {
            'draft' => 5,
            'pending' => 10,
            'in_progress' => 40,
            'under_review' => 60,
            'approved' => 80,
            'filed' => 90,
            'completed' => 100,
            'cancelled' => 0,
            'refunded' => 0,
            default => 10
        };
    }

    private function markClientPendingApproval($user): void
    {
        if (! $user || ! $user->isClient()) {
            return;
        }

        if ($user->registration_status !== 'approved' && $user->registration_status !== 'pending_approval') {
            $user->forceFill([
                'registration_status' => 'pending_approval',
                'rejection_reason' => null,
                'approved_at' => null,
                'approved_by' => null,
            ])->save();
        }
    }

    private function isPendingReviewClient($user): bool
    {
        return $user && $user->isClient() && in_array($user->registration_status, ['pending_approval', 'rejected'], true);
    }
}