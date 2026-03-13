<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    // ── Step 1 — Personal Information ──────────────────────────────────────

    public function personalInfo(): Response|RedirectResponse
    {
        $user = Auth::user();

        // If they've already passed this step, push forward
        if (in_array($user->registration_status, ['order_pending', 'pending_approval', 'approved'])) {
            return $this->redirectToCurrentStep($user);
        }

        return Inertia::render('Onboarding/PersonalInfo', [
            'user' => $user->only([
                'first_name', 'last_name', 'email', 'phone',
                'address_line_1', 'address_line_2', 'city', 'state', 'zip_code', 'country',
            ]),
        ]);
    }

    public function savePersonalInfo(Request $request): RedirectResponse
    {
        $request->validate([
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city'           => 'required|string|max:100',
            'state'          => 'required|string|max:100',
            'zip_code'       => 'required|string|max:20',
            'country'        => 'required|string|max:100',
            'date_of_birth'  => 'nullable|date',  // optional, no age restriction enforced
            'company_name'   => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $user->update([
            'address_line_1'  => $request->address_line_1,
            'address_line_2'  => $request->address_line_2,
            'city'            => $request->city,
            'state'           => $request->state,
            'zip_code'        => $request->zip_code,
            'country'         => $request->country ?? 'US',
            'company_name'    => $request->company_name,
            'registration_status' => 'order_pending',
        ]);

        return redirect()->route('orders.create');
    }

    // ── Step 2 — Create Order / Select Service ──────────────────────────────

    public function order(): Response|RedirectResponse
    {
        $user = Auth::user();

        if (in_array($user->registration_status, ['pending_approval', 'rejected', 'approved'])) {
            return $this->redirectToCurrentStep($user);
        }

        if ($user->registration_status === 'incomplete') {
            return redirect()->route('orders.create');
        }

        return redirect()->route('orders.create');
    }

    public function saveOrder(Request $request): RedirectResponse
    {
        $allServices = [
            'C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit',
            'Green Card Lottery', 'Income Tax',
        ];

        $allAddons = [
            'registered_agent', 'ein', 'corporate_kit', 'compliance',
            'operating_agreement', 'annual_report', 'apostille', 'good_standing',
            'mail_forwarding', 'itin', 'tax_consult', 'banking',
            'gc_premium', 'gc_notification', 'tax_bookkeeping', 'tax_amendment',
        ];

        $request->validate([
            'serviceType'     => 'required|string|in:' . implode(',', $allServices),
            'state'           => 'nullable|string|max:100',
            'businessName'    => 'nullable|string|max:255',
            'businessPurpose' => 'nullable|string|max:1000',
            'speedOption'     => 'required|string|in:economic,pro',
            'addons'          => 'nullable|array',
            'addons.*'        => 'string|in:' . implode(',', $allAddons),
            'applicantName'   => 'nullable|string|max:255',
            'applicantEmail'  => 'nullable|email|max:255',
            'applicantPhone'  => 'nullable|string|max:50',
            'applicantDob'    => 'nullable|date',
            'applicantSsn'    => 'nullable|string|max:20',
            'applicantAddress'=> 'nullable|string|max:255',
            'applicantCity'   => 'nullable|string|max:100',
            'applicantZip'    => 'nullable|string|max:20',
            'applicantCountry'=> 'nullable|string|max:100',
            'documents'       => 'nullable|array',
        ]);

        $user = Auth::user();

        $allowedAddons = $this->getAllowedAddonsForService($request->serviceType);
        $addonIds = collect($request->addons ?? [])
            ->filter(fn ($id) => in_array($id, $allowedAddons, true))
            ->values()
            ->all();
        $addonsTotal = $this->calculateAddonsTotal($addonIds);
        $speedFee    = $request->speedOption === 'pro' ? 149.00 : 99.00;
        $basePrice   = $this->getServicePrice($request->serviceType);
        $totalAmount = $basePrice + $addonsTotal + $speedFee;

        // Handle document uploads — store with metadata so admin can view & download
        $docPaths = [];
        if ($request->hasFile('documents')) {
            foreach (['passport', 'id_card', 'drivers_license'] as $docKey) {
                if ($request->hasFile("documents.{$docKey}")) {
                    $file = $request->file("documents.{$docKey}");
                    $docPaths[$docKey] = [
                        'stored_path'   => $file->store('onboarding-docs', 'private'),
                        'original_name' => $file->getClientOriginalName(),
                        'size'          => $file->getSize(),
                        'mime_type'     => $file->getMimeType(),
                    ];
                }
            }
            if ($request->hasFile('documents.photos')) {
                $docPaths['photos'] = [];
                foreach ($request->file('documents.photos') as $photo) {
                    $docPaths['photos'][] = [
                        'stored_path'   => $photo->store('onboarding-docs', 'private'),
                        'original_name' => $photo->getClientOriginalName(),
                        'size'          => $photo->getSize(),
                        'mime_type'     => $photo->getMimeType(),
                    ];
                }
            }
        }

        // Create the order
        Order::create([
            'order_number'     => 'ORD-' . strtoupper(uniqid()),
            'user_id'          => $user->id,
            'service_type'     => $request->serviceType,
            'state'            => $request->state,
            'entity_name'      => $request->businessName,
            'business_purpose' => $request->businessPurpose,
            'package_type'     => $request->speedOption === 'pro' ? 'premium' : 'starter',
            'processing_speed' => $request->speedOption,
            'payment_method'   => 'stripe',
            'status'           => 'pending',
            'service_fee'      => $basePrice,
            'addons'           => $addonIds ?: null,
            'addons_total'     => $addonsTotal,
            'total_amount'     => $totalAmount,
            'currency'         => 'USD',
            'applicant_info'   => [
                'full_name'     => $request->applicantName,
                'email'         => $request->applicantEmail,
                'phone'         => $request->applicantPhone,
                'date_of_birth' => $request->applicantDob,
                'ssn'           => $request->applicantSsn,
                'address'       => $request->applicantAddress,
                'city'          => $request->applicantCity,
                'zip'           => $request->applicantZip,
                'country'       => $request->applicantCountry,
            ],
            'required_documents' => $docPaths ?: null,
        ]);

        $user->update([
            'registration_status' => 'pending_approval',
            'company_name'        => $request->businessName ?? $user->company_name,
        ]);

        return redirect()->route('onboarding.review');
    }

    // ── Step 3 — Review / Awaiting Approval ────────────────────────────────

    public function review(): Response|RedirectResponse
    {
        $user = Auth::user()->load(['orders' => fn ($query) => $query->latest()]);

        if ($user->registration_status === 'approved') {
            return redirect()->route('dashboard');
        }

        if ($user->registration_status === 'incomplete') {
            return redirect()->route('orders.create');
        }

        if ($user->registration_status === 'order_pending') {
            return redirect()->route('orders.create');
        }

        $latestOrder = $user->orders->first();

        return Inertia::render('Onboarding/Review', [
            'user' => $user->only([
                'first_name', 'last_name', 'email', 'registration_status', 'rejection_reason',
            ]),
            'order' => $latestOrder ? [
                'service_type' => $latestOrder->service_type,
                'entity_name' => $latestOrder->entity_name,
                'state' => $latestOrder->state,
                'total_amount' => $latestOrder->total_amount,
            ] : null,
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function redirectToCurrentStep($user): RedirectResponse
    {
        return match ($user->registration_status) {
            'incomplete',
            'order_pending'    => redirect()->route('orders.create'),
            'pending_approval',
            'rejected'         => redirect()->route('onboarding.review'),
            'approved'         => redirect()->route('dashboard'),
            default            => redirect()->route('orders.create'),
        };
    }

    private function getServicePrice(string $serviceType): float
    {
        return match ($serviceType) {
            'C-Corporation'          => 399.00,
            'S-Corporation'          => 880.00,
            'LLC'                    => 1480.00,
            'Nonprofit'              => 499.00,
            'Green Card Lottery'     => 49.00,
            'Income Tax'             => 499.00,
            default                  => 0.00,
        };
    }

    private function calculateAddonsTotal(array $addonIds): float
    {
        $prices = [
            'registered_agent'    => 125.00,
            'ein'                 => 0.00,
            'corporate_kit'       => 85.00,
            'compliance'          => 50.00,
            'operating_agreement' => 99.00,
            'annual_report'       => 149.00,
            'apostille'           => 149.00,
            'good_standing'       => 99.00,
            'mail_forwarding'     => 99.00,
            'itin'                => 249.00,
            'tax_consult'         => 149.00,
            'banking'             => 49.00,
            'gc_premium'          => 79.00,
            'gc_notification'     => 29.00,
            'tax_bookkeeping'     => 299.00,
            'tax_amendment'       => 199.00,
        ];

        return array_sum(array_map(fn ($id) => $prices[$id] ?? 0, $addonIds));
    }

    private function getAllowedAddonsForService(string $serviceType): array
    {
        return match ($serviceType) {
            'C-Corporation' => [
                'registered_agent', 'ein', 'corporate_kit', 'compliance',
                'operating_agreement', 'annual_report', 'apostille', 'good_standing',
                'mail_forwarding', 'itin', 'tax_consult', 'banking',
            ],
            'S-Corporation' => [
                'registered_agent', 'ein', 'corporate_kit', 'compliance',
                'operating_agreement', 'annual_report', 'apostille', 'good_standing',
                'mail_forwarding', 'itin', 'tax_consult', 'banking',
            ],
            'LLC' => [
                'registered_agent', 'ein', 'corporate_kit', 'compliance',
                'operating_agreement', 'annual_report', 'apostille', 'good_standing',
                'mail_forwarding', 'itin', 'tax_consult', 'banking',
            ],
            'Nonprofit' => [
                'registered_agent', 'ein', 'corporate_kit', 'compliance',
                'annual_report', 'apostille', 'good_standing',
                'mail_forwarding', 'tax_consult', 'banking',
            ],
            'Green Card Lottery' => ['apostille', 'gc_premium', 'gc_notification'],
            'Income Tax' => ['itin', 'tax_consult', 'tax_bookkeeping', 'tax_amendment'],
            default => [],
        };
    }
}
