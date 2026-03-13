<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     */
    public function index(Request $request): Response
    {
        $query = Document::query();
        
        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('first_name', 'like', "%{$search}%")
                               ->orWhere('last_name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $documents = $query->with(['user', 'order.user', 'uploadedBy'])
            ->latest()
            ->paginate(20)
            ->through(function ($document) {
                return [
                    'id' => $document->id,
                    'document_id' => $document->document_id,
                    'name' => $document->display_name ?: $document->name,
                    'file_name' => $document->original_filename,
                    'original_filename' => $document->original_filename,
                    'type' => $document->type,
                    'category' => $document->category ?? 'additional',
                    'subcategory' => $document->subcategory,
                    'category_display' => $this->getCategoryDisplay($document->category ?? 'additional'),
                    'subcategory_display' => $this->getSubcategoryDisplay($document->category ?? 'additional', $document->subcategory),
                    'status' => $document->status,
                    'status_display' => $this->getStatusDisplay($document->status),
                    'status_color' => $this->getStatusColor($document->status),
                    'file_size' => $document->file_size,
                    'file_size_human' => $document->formatted_file_size,
                    'mime_type' => $document->mime_type,
                    'created_at' => $document->created_at->toISOString(),
                    'updated_at' => $document->updated_at->toISOString(),
                    'expires_at' => $document->expires_at?->toISOString(),
                    'download_count' => $document->download_count,
                    'last_downloaded_at' => $document->last_downloaded_at?->toISOString(),
                    'client' => (function () use ($document) {
                        // Prefer the order's owner as the real client
                        $client = $document->order?->user ?? $document->user;
                        if (!$client) return null;
                        return [
                            'id'     => $client->id,
                            'name'   => $client->full_name,
                            'email'  => $client->email,
                            'avatar' => $client->profile_picture ? Storage::url($client->profile_picture) : null,
                        ];
                    })(),
                    'order' => $document->order ? [
                        'id' => $document->order->id,
                        'order_number' => $document->order->order_number,
                        'entity_name' => $document->order->entity_name,
                        'service_type' => $document->order->service_type,
                    ] : null,
                    'uploaded_by' => $document->uploadedBy ? [
                        'name' => $document->uploadedBy->first_name . ' ' . $document->uploadedBy->last_name,
                        'email' => $document->uploadedBy->email,
                    ] : null,
                    'admin_notes' => $document->admin_notes,
                    'preview_url' => route('admin.documents.preview', $document),
                    'download_url' => route('admin.documents.download', $document),
                    'is_expired' => $document->expires_at && $document->expires_at->isPast(),
                    'is_expiring_soon' => $document->expires_at && $document->expires_at->diffInDays(now()) <= 30,
                ];
            });

        // Get filter options
        $clients = User::whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->select('id', 'name', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->get()
            ->map(function ($user) {
                $displayName = trim($user->first_name . ' ' . $user->last_name) ?: $user->name;
                return [
                    'value' => $user->id,
                    'label' => $displayName . ' (' . $user->email . ')',
                ];
            });

        return Inertia::render('Admin/Documents/Index', [
            'documents' => $documents,
            'clients' => $clients,
            'categories' => $this->getCategoryOptions(),
            'statusOptions' => $this->getStatusOptions(),
            'filters' => $request->only(['user_id', 'category', 'status', 'search']),
        ]);
    }

        /**
     * Update document status
     */
    public function updateStatus(Request $request, Document $document)
    {
        $request->validate([
            'status' => 'required|string|in:uploaded,processing,pending_review,approved,rejected,requires_action,archived',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $document->status;
        $newStatus = $request->status;

        $document->update([
            'status' => $newStatus,
            'admin_notes' => $request->admin_notes,
        ]);

        // Notify user on any meaningful status change
        $notifiableStatuses = ['approved', 'rejected', 'requires_action'];
        if ($oldStatus !== $newStatus && in_array($newStatus, $notifiableStatuses)) {
            try {
                \App\Http\Controllers\NotificationController::createDocumentNotification(
                    $document->user,
                    $document,
                    'document_ready',
                    [
                        'old_status'       => $oldStatus,
                        'new_status'       => $newStatus,
                        'admin_notes'      => $request->admin_notes,
                        'updated_by_admin' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                        'is_approved'      => $newStatus === 'approved',
                        'requires_action'  => $newStatus === 'requires_action',
                    ]
                );
            } catch (\Exception $e) {
                \Log::error('Failed to create notification for document status update: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Document status updated successfully and user has been notified.',
        ]);
    }

    /**
     * Show document details
     */
    public function create(): Response
    {
        $clients = User::whereHas('roles', function ($query) {
                $query->where('name', 'client');
            })
            ->select('id', 'name', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->get()
            ->map(function ($user) {
                $displayName = trim($user->first_name . ' ' . $user->last_name) ?: $user->name;
                return [
                    'value' => $user->id,
                    'label' => $displayName . ' (' . $user->email . ')',
                ];
            });

        return Inertia::render('Admin/Documents/Create', [
            'clients' => $clients,
            'categories' => $this->getCategoryOptions(),
            'subcategoryOptions' => $this->getSubcategoryOptions(),
        ]);
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:formation,certificates,compliance,additional',
            'subcategory' => 'nullable|string|max:100',
            'user_id' => 'required|exists:users,id',
            'expires_at' => 'nullable|date|after:today',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $user = User::findOrFail($request->user_id);
        
        // Create organized storage path
        $year = date('Y');
        $month = date('m');
        $storagePath = "documents/{$user->id}/{$year}/{$month}";
        
        // Generate unique filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::slug($originalName) . '-' . Str::random(8) . '.' . $extension;
        
        // Store file securely
        $filePath = $file->storeAs($storagePath, $fileName, 'private');
        
        // Generate unique document ID
        // Map category to type enum
        $typeMapping = [
            'formation' => 'articles_of_incorporation',
            'certificates' => 'certificate_of_incorporation',
            'compliance' => 'compliance_kit',
            'additional' => 'other',
        ];
        $documentType = $typeMapping[$request->category] ?? 'other';
        
        // Create document record (document_id auto-generated by model boot)
        $document = Document::create([
            'name' => $request->name,
            'display_name' => $request->name,
            'type' => $documentType,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_extension' => $extension,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'user_id' => $request->user_id,
            'uploaded_by' => auth()->id(),
            'status' => 'approved', // Admin uploads are automatically approved
            'expires_at' => $request->expires_at,
            'admin_notes' => $request->admin_notes,
            'file_hash' => hash_file('sha256', $file->getRealPath()),
            'access_token' => Str::random(32),
            'metadata' => json_encode([
                'category' => $request->category,
                'subcategory' => $request->subcategory,
                'uploaded_by_admin' => true,
                'uploaded_by_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
            ]),
        ]);

        // Create notification for the user
        try {
            \App\Http\Controllers\NotificationController::createDocumentNotification(
                $user,
                $document,
                'document_ready', // Use valid notification type
                [
                    'uploaded_by_admin' => true,
                    'admin_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                ]
            );
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::error('Failed to create notification for document upload: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Document uploaded successfully and user has been notified.');
    }

    /**
     * Helper methods
     */
    private function getCategoryOptions(): array
    {
        return [
            ['value' => 'formation', 'label' => 'Formation Documents'],
            ['value' => 'certificates', 'label' => 'Certificates'],
            ['value' => 'compliance', 'label' => 'Compliance Documents'],
            ['value' => 'additional', 'label' => 'Additional Documents'],
        ];
    }

    private function getSubcategoryOptions(): array
    {
        return [
            'formation' => [
                ['value' => 'articles', 'label' => 'Articles of Incorporation/Organization'],
                ['value' => 'operating_agreement', 'label' => 'Operating Agreement/Bylaws'],
                ['value' => 'ein_letter', 'label' => 'EIN Letter from IRS'],
                ['value' => 'filing_receipt', 'label' => 'State Filing Receipt'],
                ['value' => 'registered_agent', 'label' => 'Registered Agent Certificate'],
            ],
            'certificates' => [
                ['value' => 'good_standing', 'label' => 'Certificate of Good Standing'],
                ['value' => 'business_license', 'label' => 'Business License'],
                ['value' => 'ein_confirmation', 'label' => 'EIN Confirmation'],
                ['value' => 'corporate_seal', 'label' => 'Corporate Seal Certificate'],
            ],
            'compliance' => [
                ['value' => 'annual_report', 'label' => 'Annual Report'],
                ['value' => 'tax_filing', 'label' => 'Tax Filing'],
                ['value' => 'amendment', 'label' => 'Amendment Document'],
                ['value' => 'dissolution', 'label' => 'Dissolution Papers'],
            ],
            'additional' => [
                ['value' => 'corporate_kit', 'label' => 'Corporate Kit'],
                ['value' => 'banking_resolution', 'label' => 'Banking Resolution'],
                ['value' => 'stock_certificate', 'label' => 'Stock Certificate'],
                ['value' => 'other', 'label' => 'Other Document'],
            ],
        ];
    }

    private function getStatusOptions(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'in_progress', 'label' => 'In Progress'],
            ['value' => 'approved', 'label' => 'Ready'],
            ['value' => 'rejected', 'label' => 'Rejected'],
        ];
    }

    private function getCategoryDisplay(string $category): string
    {
        $categories = [
            'formation' => 'Formation Documents',
            'certificates' => 'Certificates',
            'compliance' => 'Compliance Documents',
            'additional' => 'Additional Documents',
        ];
        
        return $categories[$category] ?? ucfirst($category);
    }

    private function getSubcategoryDisplay(string $category, ?string $subcategory): string
    {
        if (!$subcategory) return '';
        
        $subcategories = [
            'formation' => [
                'articles' => 'Articles of Incorporation/Organization',
                'operating_agreement' => 'Operating Agreement/Bylaws',
                'ein_letter' => 'EIN Letter from IRS',
                'filing_receipt' => 'State Filing Receipt',
                'registered_agent' => 'Registered Agent Certificate',
            ],
            'certificates' => [
                'good_standing' => 'Certificate of Good Standing',
                'business_license' => 'Business License',
                'ein_confirmation' => 'EIN Confirmation',
                'corporate_seal' => 'Corporate Seal Certificate',
            ],
            'compliance' => [
                'annual_report' => 'Annual Report',
                'tax_filing' => 'Tax Filing',
                'amendment' => 'Amendment Document',
                'dissolution' => 'Dissolution Papers',
            ],
            'additional' => [
                'corporate_kit' => 'Corporate Kit',
                'banking_resolution' => 'Banking Resolution',
                'stock_certificate' => 'Stock Certificate',
                'other' => 'Other Document',
            ],
        ];
        
        return $subcategories[$category][$subcategory] ?? ucfirst(str_replace('_', ' ', $subcategory));
    }

    private function getStatusDisplay(string $status): string
    {
        $statuses = [
            'pending' => 'Pending',
            'pending_review' => 'Pending Review',
            'in_progress' => 'In Progress',
            'approved' => 'Ready',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
        ];
        
        return $statuses[$status] ?? ucfirst($status);
    }

    private function getStatusColor(string $status): string
    {
        return match($status) {
            'pending', 'pending_review' => 'text-yellow-600 bg-yellow-100 border-yellow-200',
            'in_progress' => 'text-blue-600 bg-blue-100 border-blue-200',
            'approved' => 'text-green-600 bg-green-100 border-green-200',
            'rejected' => 'text-red-600 bg-red-100 border-red-200',
            'expired' => 'text-gray-600 bg-gray-100 border-gray-200',
            default => 'text-gray-600 bg-gray-100 border-gray-200',
        };
    }

    /**
     * Display the specified document
     */
    public function show(Document $document): Response
    {
        $document->load(['user', 'order', 'uploadedBy']);
        
        return Inertia::render('Admin/Documents/Show', [
            'document' => [
                'id' => $document->id,
                'document_id' => $document->document_id,
                'name' => $document->display_name ?: $document->name,
                'file_name' => $document->original_filename,
                'original_filename' => $document->original_filename,
                'type' => $document->type,
                'category' => $document->category ?? 'additional',
                'subcategory' => $document->subcategory,
                'category_display' => $this->getCategoryDisplay($document->category ?? 'additional'),
                'subcategory_display' => $this->getSubcategoryDisplay($document->category ?? 'additional', $document->subcategory),
                'status' => $document->status,
                'status_display' => $this->getStatusDisplay($document->status),
                'status_color' => $this->getStatusColor($document->status),
                'file_size' => $document->file_size,
                'file_size_human' => $document->formatted_file_size,
                'mime_type' => $document->mime_type,
                'created_at' => $document->created_at->toISOString(),
                'updated_at' => $document->updated_at->toISOString(),
                'expires_at' => $document->expires_at?->toISOString(),
                'download_count' => $document->download_count,
                'last_downloaded_at' => $document->last_downloaded_at?->toISOString(),
                'client' => $document->user ? [
                    'id'     => $document->user->id,
                    'name'   => $document->user->full_name,
                    'email'  => $document->user->email,
                    'avatar' => $document->user->profile_picture ? Storage::url($document->user->profile_picture) : null,
                ] : null,
                'order' => $document->order ? [
                    'id' => $document->order->id,
                    'order_number' => $document->order->order_number,
                    'service_type' => $document->order->service_type,
                ] : null,
                'uploaded_by' => $document->uploadedBy ? [
                    'id' => $document->uploadedBy->id,
                    'name' => $document->uploadedBy->first_name . ' ' . $document->uploadedBy->last_name,
                ] : null,
            ]
        ]);
    }

    /**
     * Download the specified document
     */
    public function preview(Document $document)
    {
        $path = \Illuminate\Support\Facades\Storage::disk('private')->path($document->file_path);
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }
        $mime = $document->mime_type ?: 'application/octet-stream';
        $name = $document->display_name ?: $document->original_filename ?: $document->name;
        return response()->file($path, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . $name . '"',
        ]);
    }

    public function download(Document $document)
    {
        // Log the download attempt
        Log::info('Document download attempt', [
            'document_id' => $document->id,
            'file_path' => $document->file_path,
            'user_id' => auth()->id()
        ]);

        // Check if document has file path
        if (!$document->file_path) {
            Log::error('No file path for document', ['document_id' => $document->id]);
            abort(404, 'No file path specified for this document');
        }

        // Check if file exists in storage
        if (!Storage::exists($document->file_path)) {
            // Try alternative paths in case of storage configuration issues
            $alternativePaths = [
                $document->file_path,
                'public/' . $document->file_path,
                'app/' . $document->file_path
            ];
            
            $foundPath = null;
            foreach ($alternativePaths as $path) {
                if (Storage::exists($path)) {
                    $foundPath = $path;
                    break;
                }
            }
            
            if (!$foundPath) {
                abort(404, 'File not found in storage: ' . $document->file_path);
            }
            
            $document->file_path = $foundPath;
        }

        // Increment download count
        $document->increment('download_count');
        $document->update(['last_downloaded_at' => now()]);

        // Stream file instead of loading into memory (prevents OOM on large files)
        $fileName = preg_replace('/[^\w\s\-\.]/u', '_', $document->original_filename ?: $document->name);

        return Storage::disk('private')->download(
            $document->file_path,
            $fileName,
            [
                'Content-Type'        => $document->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . addslashes($fileName) . '"',
            ]
        );
    }

    /**
     * Remove the specified document from storage
     */
    public function destroy(Document $document)
    {
        // Delete the physical file
        if ($document->file_path && Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        // Delete the database record
        $document->delete();

        return redirect()->route('admin.documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Perform bulk actions on multiple documents
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:documents,id',
            'action' => 'required|string|in:approve,reject,archive,delete',
        ]);

        $documents = Document::whereIn('id', $request->ids)->get();
        $count     = $documents->count();

        foreach ($documents as $document) {
            switch ($request->action) {
                case 'approve':
                    $oldStatus = $document->status;
                    $document->update(['status' => 'approved']);
                    if ($oldStatus !== 'approved') {
                        try {
                            \App\Http\Controllers\NotificationController::createDocumentNotification(
                                $document->user, $document, 'document_ready',
                                ['new_status' => 'approved', 'is_approved' => true]
                            );
                        } catch (\Exception $e) {
                            \Log::warning('Bulk approve notification failed: ' . $e->getMessage());
                        }
                    }
                    break;

                case 'reject':
                    $oldStatus = $document->status;
                    $document->update(['status' => 'rejected']);
                    if ($oldStatus !== 'rejected') {
                        try {
                            \App\Http\Controllers\NotificationController::createDocumentNotification(
                                $document->user, $document, 'document_ready',
                                ['new_status' => 'rejected', 'is_approved' => false]
                            );
                        } catch (\Exception $e) {
                            \Log::warning('Bulk reject notification failed: ' . $e->getMessage());
                        }
                    }
                    break;

                case 'archive':
                    $document->update(['status' => 'archived']);
                    break;

                case 'delete':
                    if ($document->file_path && Storage::exists($document->file_path)) {
                        Storage::delete($document->file_path);
                    }
                    $document->delete();
                    break;
            }
        }

        $label = match($request->action) {
            'approve'  => 'approved',
            'reject'   => 'rejected',
            'archive'  => 'archived',
            'delete'   => 'deleted',
            default    => 'updated',
        };

        return redirect()->route('admin.documents.index')
            ->with('success', "{$count} document(s) {$label} successfully.");
    }
}