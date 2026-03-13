<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * Display a listing of the user's documents.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        // Get filter parameters
        $category = $request->get('category');
        $status   = $request->get('status');
        $search   = $request->get('search');
        $orderId  = $request->get('order_id');
        
        $query = $user->documents()->with(['order']);
        
        // Apply filters
        if ($category) {
            $query->where('category', $category);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($orderId) {
            $query->where('order_id', $orderId);
        }
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $documents = $query->latest()
            ->paginate(12)
            ->through(function ($document) {
                return [
                    'id' => $document->id,
                    'document_id' => $document->document_id,
                    'name' => $document->display_name ?: $document->name,
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
                    'expires_at' => $document->expires_at?->toISOString(),
                    'download_count' => $document->download_count,
                    'last_downloaded_at' => $document->last_downloaded_at?->toISOString(),
                    'url' => $document->status === 'approved' ? route('documents.download', $document) : null,
                    'preview_url' => $this->canPreview($document) ? route('documents.preview', $document) : null,
                    'order_id' => $document->order_id,
                    'order_name' => $document->order
                        ? ($document->order->entity_name ?: ($document->order->order_number ?: 'Order #'.$document->order->id))
                        : null,
                    'is_downloadable' => $document->status === 'approved',
                    'is_expired' => $document->expires_at && $document->expires_at->isPast(),
                    'is_expiring_soon' => $document->expires_at && $document->expires_at->diffInDays(now()) <= 30,
                    'can_preview' => $this->canPreview($document),
                ];
            });

        // Get document categories with counts
        $categoryCounts = $user->documents()
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $categories = [
            [
                'value' => 'formation',
                'label' => 'Formation Documents',
                'description' => 'Articles of incorporation, bylaws, and other formation paperwork',
                'count' => $categoryCounts['formation'] ?? 0,
                'icon' => 'document-text',
            ],
            [
                'value' => 'certificates',
                'label' => 'Certificates',
                'description' => 'State certificates, EIN confirmation, and official documents',
                'count' => $categoryCounts['certificates'] ?? 0,
                'icon' => 'shield-check',
            ],
            [
                'value' => 'compliance',
                'label' => 'Compliance Documents',
                'description' => 'Annual reports, tax filings, and amendment documents',
                'count' => $categoryCounts['compliance'] ?? 0,
                'icon' => 'clipboard-document-check',
            ],
            [
                'value' => 'additional',
                'label' => 'Additional Documents',
                'description' => 'Operating agreements, corporate kits, and supplementary files',
                'count' => $categoryCounts['additional'] ?? 0,
                'icon' => 'folder-plus',
            ],
        ];

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'categories' => $categories,
            'filters' => [
                'category' => $category,
                'status'   => $status,
                'search'   => $search,
                'order_id' => $orderId,
            ],
            'orders' => $user->orders()
                ->orderBy('created_at', 'desc')
                ->get(['id', 'entity_name'])
                ->map(fn($o) => ['value' => $o->id, 'label' => $o->entity_name ?? 'Order #'.$o->id])
                ->values()
                ->all(),
            'stats' => [
                'total'        => $user->documents()->count(),
                'approved'     => $user->documents()->where('status', 'approved')->count(),
                'pending'      => $user->documents()->whereIn('status', ['pending', 'pending_review'])->count(),
                'storage_used' => (int) $user->documents()->sum('file_size'),
            ],
            'statusOptions' => [
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'in_progress', 'label' => 'In Progress'],
                ['value' => 'approved', 'label' => 'Ready'],
                ['value' => 'expired', 'label' => 'Expired'],
            ],
        ]);
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document): Response
    {
        // Ensure user can only view their own documents
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        $document->load(['order']);

        $documentData = [
            'id' => $document->id,
            'name' => $document->display_name ?: $document->name,
            'original_name' => $document->name,
            'type' => $document->type_display_name,
            'status' => $document->status,
            'status_display' => $document->status_display,
            'created_at' => $document->created_at->toISOString(),
            'updated_at' => $document->updated_at->toISOString(),
            'size' => $document->formatted_file_size,
            'file_size' => $document->file_size,
            'mime_type' => $document->mime_type,
            'is_downloadable' => $document->status === 'approved',
            'expires_at' => $document->expires_at?->toISOString(),
            'description' => $document->description,
            'metadata' => $document->metadata,
            'order' => $document->order ? [
                'id' => $document->order->id,
                'order_number' => $document->order->order_number,
                'service_type' => $document->order->service_type_name,
                'entity_name' => $document->order->entity_name,
                'status' => $document->order->status,
            ] : null,
        ];

        return Inertia::render('Documents/Show', [
            'document' => $documentData,
        ]);
    }

    /**
     * Download the specified document.
     */
    public function download(Document $document): StreamedResponse
    {
        // Ensure user can only download their own documents
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if document is downloadable
        if ($document->status !== 'approved') {
            abort(403, 'Document is not available for download.');
        }

        // Check if document has expired
        if ($document->expires_at && $document->expires_at->isPast()) {
            abort(403, 'Document has expired and is no longer available for download.');
        }

        // Check if file exists
        if (!Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'Document file not found.');
        }

        // Update download count and last downloaded timestamp
        $document->increment('download_count');
        $document->update(['last_downloaded_at' => now()]);

        // Return file download response
        $safeFilename = preg_replace('/[^\w\s\-\.]/u', '_', $document->display_name ?: $document->name);
        return Storage::disk('private')->download(
            $document->file_path,
            $safeFilename,
            [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'attachment; filename="' . addslashes($safeFilename) . '"',
            ]
        );
    }

    /**
     * Preview a document
     */
    public function preview(Document $document)
    {
        // Ensure user can only preview their own documents
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        $path = Storage::disk('private')->path($document->file_path);

        if (!file_exists($path)) {
            abort(404);
        }

        $mime = $document->mime_type ?: 'application/octet-stream';
        $name = $document->display_name ?: $document->name;

        // Serve inline for previewable types
        if ($this->canPreview($document)) {
            return response()->file($path, [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="' . $name . '"',
            ]);
        }

        // For other file types, redirect to download
        return $this->download($document);
    }

    /**
     * Upload a new document.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png', // 10MB max
            'type' => 'required|string|in:kyc,tax,legal,other',
            'description' => 'nullable|string|max:500',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        // Verify the order belongs to the authenticated user
        if (!empty($validated['order_id'])) {
            $orderBelongsToUser = auth()->user()->orders()->where('id', $validated['order_id'])->exists();
            if (!$orderBelongsToUser) {
                abort(403, 'Order does not belong to your account.');
            }
        }

        $file = $request->file('file');
        
        // Store file in private storage
        $filePath = $file->store('documents/' . auth()->id(), 'private');
        
        // Create document record
        $document = auth()->user()->documents()->create([
            'name' => $file->getClientOriginalName(),
            'type' => $validated['type'],
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => 'pending_review',
            'description' => $validated['description'],
            'order_id' => $validated['order_id'],
            'uploaded_at' => now(),
        ]);

        return redirect()->route('documents.index')
            ->with('success', 'Document uploaded successfully and is pending review.');
    }

    /**
     * Show the upload form.
     */
    public function create(): Response
    {
        $user = auth()->user();
        
        // Get user's orders for the order selection dropdown
        $orders = $user->orders()
            ->select('id', 'order_number', 'service_type', 'entity_name')
            ->latest()
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'label' => $order->order_number . ' - ' . $order->service_type_name . ' (' . $order->entity_name . ')',
                    'order_number' => $order->order_number,
                    'service_type' => $order->service_type_name,
                    'entity_name' => $order->entity_name,
                ];
            });

        return Inertia::render('Documents/Create', [
            'orders' => $orders,
        ]);
    }

    /**
     * Helper methods
     */
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

    private function canPreview(Document $document): bool
    {
        return in_array($document->mime_type, [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'text/plain'
        ]);
    }

    /**
     * Generate secure sharing link
     */
    public function generateShareLink(Request $request, Document $document)
    {
        // Ensure user owns the document
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'expires_in_hours' => 'required|integer|min:1|max:168', // Max 7 days
            'password' => 'nullable|string|min:4|max:50',
            'download_limit' => 'nullable|integer|min:1|max:10',
        ]);

        $shareToken = Str::random(32);
        $expiresAt = now()->addHours($request->expires_in_hours);

        // Store sharing data in database or cache
        $shareData = [
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'expires_at' => $expiresAt,
            'password' => $request->password ? bcrypt($request->password) : null,
            'download_limit' => $request->download_limit,
            'download_count' => 0,
            'created_at' => now(),
        ];

        // Store in cache with expiration
        Cache::put("doc_share_{$shareToken}", $shareData, $expiresAt);

        $shareUrl = route('documents.shared', ['token' => $shareToken]);

        return response()->json([
            'success' => true,
            'share_url' => $shareUrl,
            'expires_at' => $expiresAt->toISOString(),
            'token' => $shareToken,
        ]);
    }

    /**
     * Access shared document
     */
    public function accessShared(Request $request, $token)
    {
        $shareData = Cache::get("doc_share_{$token}");

        if (!$shareData || Carbon::parse($shareData['expires_at'])->isPast()) {
            abort(404, 'Share link has expired or does not exist.');
        }

        $document = Document::findOrFail($shareData['document_id']);

        // Check download limit
        if ($shareData['download_limit'] && $shareData['download_count'] >= $shareData['download_limit']) {
            abort(403, 'Download limit exceeded for this shared link.');
        }

        // Check password if required
        if ($shareData['password']) {
            if (!$request->has('password') || !Hash::check($request->password, $shareData['password'])) {
                return response()->json([
                    'requires_password' => true,
                    'message' => 'This shared document is password protected.',
                ], 200);
            }
        }

        return response()->json([
            'document' => [
                'id' => $document->id,
                'name' => $document->name,
                'file_size_human' => $this->formatFileSize($document->file_size),
                'category_display' => $document->category_display,
                'created_at' => $document->created_at->format('M j, Y'),
                'download_url' => route('documents.download-shared', ['token' => $token]),
                'remaining_downloads' => $shareData['download_limit'] ? 
                    ($shareData['download_limit'] - $shareData['download_count']) : null,
            ],
        ]);
    }

    /**
     * Download shared document
     */
    public function downloadShared(Request $request, $token)
    {
        $shareData = Cache::get("doc_share_{$token}");

        if (!$shareData || Carbon::parse($shareData['expires_at'])->isPast()) {
            abort(404, 'Share link has expired or does not exist.');
        }

        $document = Document::findOrFail($shareData['document_id']);

        // Check download limit
        if ($shareData['download_limit'] && $shareData['download_count'] >= $shareData['download_limit']) {
            abort(403, 'Download limit exceeded for this shared link.');
        }

        // Check password if required
        if ($shareData['password']) {
            if (!$request->has('password') || !Hash::check($request->password, $shareData['password'])) {
                abort(403, 'Invalid password for protected document.');
            }
        }

        // Increment download count
        $shareData['download_count']++;
        Cache::put("doc_share_{$token}", $shareData, Carbon::parse($shareData['expires_at']));

        // Log the access
        $this->logDocumentAccess($document, 'shared_download', [
            'share_token' => $token,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->downloadDocument($document);
    }

    /**
     * Revoke sharing link
     */
    public function revokeShareLink(Request $request, $token)
    {
        $shareData = Cache::get("doc_share_{$token}");

        if (!$shareData) {
            abort(404, 'Share link does not exist.');
        }

        $document = Document::findOrFail($shareData['document_id']);

        // Ensure user owns the document
        if ($document->user_id !== auth()->id()) {
            abort(403);
        }

        Cache::forget("doc_share_{$token}");

        return response()->json([
            'success' => true,
            'message' => 'Share link has been revoked.',
        ]);
    }

    /**
     * Log document access for security
     */
    private function logDocumentAccess(Document $document, string $action, array $metadata = [])
    {
        // Update document access tracking
        $document->increment('download_count');
        $document->update(['last_downloaded_at' => now()]);

        // Log access in Laravel logs
        \Log::info("Document access: {$action}", [
            'document_id' => $document->id,
            'document_name' => $document->name,
            'user_id' => auth()->id() ?? null,
            'metadata' => $metadata,
        ]);
    }
}