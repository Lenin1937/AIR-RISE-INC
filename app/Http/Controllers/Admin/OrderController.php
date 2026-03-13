<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request): Response
    {
        // Get filters
        $search = $request->get('search');
        $status = $request->get('status');
        $serviceType = $request->get('service_type');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Build query
        $query = Order::with(['user', 'payments'])
            ->withCount('documents');

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('entity_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply service type filter
        if ($serviceType) {
            $query->where('service_type', $serviceType);
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Get paginated orders
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Transform orders data
        $ordersData = $orders->through(function ($order) {
            $totalPaid = $order->payments->where('status', 'completed')->sum('amount');
            
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'client_name' => $order->user?->full_name ?? $order->user?->name ?? 'N/A',
                'client_email' => $order->user?->email ?? 'N/A',
                'client_avatar' => $order->user?->profile_picture ? \Storage::url($order->user->profile_picture) : null,
                'service_type' => $order->service_type_name,
                'service_type_badge' => $this->getServiceTypeBadge($order->service_type),
                'entity_name' => $order->entity_name ?? 'N/A',
                'status' => $order->status,
                'status_display' => $order->status_display,
                'status_color' => $this->getStatusColor($order->status),
                'total_amount' => $order->total_amount ?? 0,
                'paid_amount' => $totalPaid,
                'payment_status' => $this->getPaymentStatus($order->total_amount, $totalPaid),
                'documents_count' => $order->documents_count ?? 0,
                'created_at' => $order->created_at->toISOString(),
                'created_at_human' => $order->created_at->format('M j, Y'),
                'progress_percentage' => $order->progress_percentage ?? 0,
            ];
        });

        // Calculate statistics
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'in_progress' => Order::whereIn('status', ['in_progress', 'under_review'])->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total_amount') ?? 0,
            'this_month_revenue' => Order::where('status', '!=', 'cancelled')
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount') ?? 0,
        ];

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $ordersData,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'service_type' => $serviceType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    private function getServiceTypeBadge($serviceType)
    {
        $badges = [
            'llc' => ['label' => 'LLC', 'color' => 'blue'],
            'c_corp' => ['label' => 'C-Corp', 'color' => 'purple'],
            's_corp' => ['label' => 'S-Corp', 'color' => 'indigo'],
            'nonprofit' => ['label' => 'Nonprofit', 'color' => 'green'],
            'green_card' => ['label' => 'Green Card', 'color' => 'teal'],
        ];
        
        return $badges[$serviceType] ?? ['label' => ucfirst($serviceType), 'color' => 'gray'];
    }

    private function getStatusColor($status)
    {
        $colors = [
            'pending' => 'yellow',
            'in_progress' => 'blue',
            'under_review' => 'purple',
            'completed' => 'green',
            'cancelled' => 'red',
            'on_hold' => 'orange',
        ];
        
        return $colors[$status] ?? 'gray';
    }

    private function getPaymentStatus($totalAmount, $paidAmount)
    {
        if ($paidAmount >= $totalAmount) {
            return ['status' => 'paid', 'label' => 'Paid', 'color' => 'green'];
        } elseif ($paidAmount > 0) {
            return ['status' => 'partial', 'label' => 'Partial', 'color' => 'yellow'];
        }
        return ['status' => 'unpaid', 'label' => 'Unpaid', 'color' => 'red'];
    }

    /**
     * Display the specified order
     */
    public function show(Order $order): Response
    {
        $order->load(['user', 'payments', 'documents']);

        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'client_name' => $order->user->full_name ?? 'N/A',
            'client_email' => $order->user->email ?? 'N/A',
            'client_phone' => $order->user->phone ?? 'N/A',
            'service_type' => $order->service_type_name,
            'status' => $order->status,
            'status_display' => $order->status_display,
            'package_type' => $order->package_type,
            'processing_speed' => $order->processing_speed,
            'amount' => $order->total_amount ?? 0,
            'service_fee' => $order->service_fee ?? 0,
            'subtotal' => $order->subtotal ?? 0,
            'state_fee' => $order->state_fee ?? 0,
            'processing_fee' => $order->processing_fee ?? 0,
            'currency' => $order->currency ?? 'USD',
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),
            'estimated_completion' => $order->estimated_completion_date?->toISOString(),
            'state' => $order->state,
            'business_name' => $order->entity_name,
            'business_purpose' => $order->business_purpose,
            'payment_method' => $order->payment_method,
            'requirements' => $order->requirements ?? [],
            'required_documents' => $order->required_documents ?? [],
            'progress_percentage' => $order->progress_percentage,
            'timeline_events' => $order->timeline_events,
            'applicant_info' => $order->applicant_info ?? [],
            'contact_info' => $order->contact_info ?? [],
            'business_details' => $order->business_details ?? [],
            // Green Card specific
            'lottery_year' => $order->lottery_year,
            'family_info' => $order->family_info ?? [],
            // C-Corp / S-Corp specific
            'shareholders' => $order->shareholders ?? [],
            'directors' => $order->directors ?? [],
            'officers' => $order->officers ?? [],
            'authorized_shares' => $order->authorized_shares,
            'par_value' => $order->par_value,
            'stock_class' => $order->stock_class,
            // LLC specific
            'members' => $order->members ?? [],
            'managers' => $order->managers ?? [],
            'management_structure' => $order->management_structure,
            // Nonprofit specific
            'charitable_purpose' => $order->charitable_purpose,
            'c501c3_application' => $order->{'501c3_application'} ?? false,
            'board_members' => $order->board_members ?? [],
            'addons' => $order->addons ?? [],
            'addons_total' => (float) ($order->addons_total ?? 0),
            'user' => [
                'id' => $order->user->id,
                'name' => $order->user->full_name,
                'email' => $order->user->email,
                'phone' => $order->user->phone,
                'company_name' => $order->user->company_name,
                'address_line_1' => $order->user->address_line_1,
                'address_line_2' => $order->user->address_line_2,
                'city' => $order->user->city,
                'state' => $order->user->state,
                'zip_code' => $order->user->zip_code,
                'country' => $order->user->country,
                'created_at' => $order->user->created_at->toISOString(),
                'profile_picture' => $order->user->profile_picture ? \Storage::url($order->user->profile_picture) : null,
            ],
            'payments' => $order->payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount ?? 0,
                    'status' => $payment->status,
                    'method' => $payment->payment_method,
                    'created_at' => $payment->created_at->toISOString(),
                ];
            }),
            'documents' => $order->documents->map(function ($document) {
                return [
                    'id' => $document->id,
                    'name' => $document->display_name ?: $document->name,
                    'type' => $document->type_display_name,
                    'status' => $document->status,
                    'created_at' => $document->created_at->toISOString(),
                    'size' => $document->formatted_file_size,
                ];
            }),
        ];

        return Inertia::render('Admin/Orders/Show', [
            'order' => $orderData,
        ]);
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit(Order $order): Response
    {
        $order->load(['user', 'payments', 'documents']);

        $orderData = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'client_name' => $order->user->full_name ?? 'N/A',
            'client_email' => $order->user->email ?? 'N/A',
            'client_phone' => $order->user->phone ?? 'N/A',
            'service_type' => $order->service_type_name,
            'status' => $order->status,
            'status_display' => $order->status_display,
            'package_type' => $order->package_type,
            'processing_speed' => $order->processing_speed,
            'amount' => ($order->total_amount ?? 0) * 100,
            'service_fee' => $order->service_fee ?? 0,
            'state_fee' => $order->state_fee ?? 0,
            'processing_fee' => $order->processing_fee ?? 0,
            'total_amount' => $order->total_amount ?? 0,
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),
            'estimated_completion' => $order->estimated_completion_date?->toISOString(),
            'state_filing_date' => $order->state_filing_date?->toISOString(),
            'state' => $order->state,
            'business_name' => $order->entity_name,
            'business_purpose' => $order->business_purpose,
            'payment_method' => $order->payment_method,
            'requirements' => $order->requirements ?? [],
            'required_documents' => $order->required_documents ?? [],
            'internal_notes' => $order->internal_notes,
            'special_instructions' => $order->special_instructions,
            'messages' => $order->messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->content,
                    'from_admin' => $message->from_admin,
                    'created_at' => $message->created_at->toISOString(),
                ];
            }),
        ];

        return Inertia::render('Admin/Orders/Edit', [
            'order' => $orderData,
        ]);
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,pending,in_progress,under_review,approved,filed,completed,cancelled,refunded',
            'estimated_completion_date' => 'nullable|date',
            'state_filing_date' => 'nullable|date',
            'processing_speed' => 'nullable|in:standard,expedited,rush',
            'entity_name' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:100',
            'business_purpose' => 'nullable|string',
            'service_fee' => 'nullable|numeric|min:0',
            'state_fee' => 'nullable|numeric|min:0',
            'processing_fee' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'internal_notes' => 'nullable|string',
            'special_instructions' => 'nullable|string',
            'is_draft' => 'nullable|boolean',
        ]);

        $previousStatus = $order->status;
        $order->update($validated);

        // Send status-change email if status changed and not a draft save
        if (! $request->boolean('is_draft') && isset($validated['status']) && $validated['status'] !== $previousStatus) {
            $order->refresh();
            app(OrderMailService::class)->sendStatusChanged($order, $request->input('special_instructions'));
        }

        $message = $request->is_draft ? 'Draft saved successfully!' : 'Order updated successfully!';

        return back()->with('success', $message);
    }

    /**
     * Approve an order
     */
    public function approve(Request $request, Order $order)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        // Update order status
        $order->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approval_notes' => $request->notes,
        ]);

        // Create notification for the user
        $user = $order->user;
        if ($user) {
            \App\Http\Controllers\NotificationController::createOrderNotification(
                $user,
                $order,
                'order_approved',
                [
                    'approval_notes' => $request->notes,
                    'approved_by' => auth()->user()->name,
                ]
            );

            // Send approval email
            $order->refresh();
            app(OrderMailService::class)->sendStatusChanged($order, $request->notes);
        }

        return redirect()->back()->with('success', 'Order approved successfully and client has been notified.');
    }

    /**
     * Reject an order
     */
    public function reject(Request $request, Order $order)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500'  // Limit message length
        ]);

        // Update order status
        $order->update([
            'status' => 'cancelled',
            'rejected_at' => now(),
            'rejection_reason' => $validated['message']
        ]);

        // Create notification for client
        \App\Http\Controllers\NotificationController::createOrderNotification(
            $order->user,
            $order,
            'order_rejected',
            [
                'rejection_reason' => $validated['message'],
                'rejected_by' => auth()->user()->name,
            ]
        );

        // Send rejection email
        $order->refresh();
        app(OrderMailService::class)->sendStatusChanged($order, $validated['message']);

        // Add timeline event
        $timelineEvents = $order->timeline_events ?? [];
        $timelineEvents[] = [
            'title' => 'Order Rejected',
            'description' => $validated['message'],
            'completed_at' => now()->toISOString(),
            'status' => 'cancelled'
        ];
        $order->update(['timeline_events' => $timelineEvents]);

        return back()->with('success', 'Order rejected successfully!');
    }

    /**
     * Send "Additional documents / information required" email to the client.
     */
    public function requestDocuments(Request $request, Order $order)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        app(OrderMailService::class)->sendDocumentsRequired($order, $validated['message']);

        return back()->with('success', 'Document request email sent to the client.');
    }

    /**
     * Download a document from an order
     */
    public function downloadDocument(Request $request, Order $order, string $documentType)
    {
        try {
            $documents = $order->required_documents ?? [];

            // Legacy: if documentType is 'photos' but stored as photo_0, photo_1 keys
            if ($documentType === 'photos' && !isset($documents['photos'])) {
                $legacyPhotos = [];
                foreach ($documents as $key => $val) {
                    if (preg_match('/^photo_\d+$/', $key)) {
                        $legacyPhotos[] = $val;
                    }
                }
                if (!empty($legacyPhotos)) {
                    $documents['photos'] = $legacyPhotos;
                }
            }

            if (!isset($documents[$documentType])) {
                return response()->json(['error' => 'Document not found'], 404);
            }

            $documentInfo = $documents[$documentType];

            // Handle both single documents and arrays of documents (like photos)
            if (is_array($documentInfo) && isset($documentInfo[0])) {
                $index = (int) $request->query('index', 0);
                if (!isset($documentInfo[$index])) {
                    return response()->json(['error' => 'Document not found at specified index'], 404);
                }
                $documentInfo = $documentInfo[$index];
            }

            // Legacy format: plain path string (no metadata)
            if (is_string($documentInfo)) {
                $filePath     = $documentInfo;
                $originalName = basename($documentInfo);
            } elseif (isset($documentInfo['stored_path']) || isset($documentInfo['path'])) {
                $filePath     = $documentInfo['stored_path'] ?? $documentInfo['path'];
                $originalName = $documentInfo['original_name'] ?? basename($filePath);
            } else {
                return response()->json(['error' => 'Document file path not found'], 404);
            }

            // Try private disk first (new uploads), fall back to public disk (legacy uploads)
            $disk = 'private';
            if (!Storage::disk('private')->exists($filePath)) {
                if (Storage::disk('public')->exists($filePath)) {
                    $disk = 'public';
                } else {
                    return response()->json(['error' => 'Document file not found in storage'], 404);
                }
            }

            $mimeType = Storage::disk($disk)->mimeType($filePath) ?: 'application/octet-stream';

            \Log::info('Admin downloaded document', [
                'admin_id' => auth()->id(),
                'order_id' => $order->id,
                'document_type' => $documentType,
                'original_name' => $originalName,
            ]);

            // Return the file download response with proper headers
            return Storage::disk($disk)->response($filePath, $originalName, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $originalName . '"'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Download failed with exception', [
                'order_id' => $order->id,
                'document_type' => $documentType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Download failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified order
     */
    public function destroy(Order $order)
    {
        $order->delete();
        
        return redirect()->route('admin.orders.index')
            ->with('success', 'Order deleted successfully!');
    }
}