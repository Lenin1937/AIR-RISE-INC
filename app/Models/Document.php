<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'order_id',
        'name',
        'display_name',
        'type',
        'description',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'file_extension',
        'file_hash',
        'status',
        'category',
        'subcategory',
        'visibility',
        'is_encrypted',
        'encryption_key',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'rejection_reason',
        'version',
        'parent_document_id',
        'is_active_version',
        'metadata',
        'download_count',
        'last_downloaded_at',
        'expires_at',
        'generated_at',
        'uploaded_by',
        'admin_notes',
        'access_token',
        'is_permanent',
        'retention_until',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'is_encrypted' => 'boolean',
            'is_active_version' => 'boolean',
            'is_permanent' => 'boolean',
            'reviewed_at' => 'datetime',
            'last_downloaded_at' => 'datetime',
            'expires_at' => 'datetime',
            'generated_at' => 'datetime',
            'retention_until' => 'date',
        ];
    }

    /**
     * Generate unique document ID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($document) {
            if (!$document->document_id) {
                // Wrap in transaction so lockForUpdate actually prevents concurrent duplicates
                $document->document_id = \Illuminate\Support\Facades\DB::transaction(function () {
                    $next = \Illuminate\Support\Facades\DB::table('documents')
                        ->whereYear('created_at', date('Y'))
                        ->lockForUpdate()
                        ->count() + 1;
                    return 'DOC-' . date('Y') . '-' . str_pad($next, 6, '0', STR_PAD_LEFT);
                });
            }
        });
    }

    /**
     * Get the user that owns the document
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order this document belongs to
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who reviewed this document
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the user who uploaded this document
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the parent document (for versions)
     */
    public function parentDocument()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    /**
     * Get child documents (versions)
     */
    public function childDocuments()
    {
        return $this->hasMany(Document::class, 'parent_document_id');
    }

    /**
     * Get document download URL
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('documents.download', $this->id);
    }

    /**
     * Get document preview URL
     */
    public function getPreviewUrlAttribute(): string
    {
        return route('documents.preview', $this->id);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get document type display name
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match($this->type) {
            'certificate_of_incorporation' => 'Certificate of Incorporation',
            'articles_of_incorporation' => 'Articles of Incorporation',
            'operating_agreement' => 'Operating Agreement',
            'bylaws' => 'Corporate Bylaws',
            'ein_letter' => 'EIN Letter',
            'minutes' => 'Meeting Minutes',
            'stock_certificates' => 'Stock Certificates',
            'membership_certificates' => 'Membership Certificates',
            'compliance_kit' => 'Compliance Kit',
            'annual_report' => 'Annual Report',
            'tax_documents' => 'Tax Documents',
            'kyc_document' => 'KYC Document',
            'id_document' => 'ID Document',
            'proof_of_address' => 'Proof of Address',
            'bank_statement' => 'Bank Statement',
            default => 'Other Document',
        };
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayAttribute(): string
    {
        return match($this->category) {
            'formation' => 'Formation Documents',
            'certificates' => 'Certificates',
            'compliance' => 'Compliance Documents',
            'additional' => 'Additional Documents',
            default => 'Other',
        };
    }

    /**
     * Get subcategory display name
     */
    public function getSubcategoryDisplayAttribute(): string
    {
        if (!$this->subcategory) {
            return null;
        }

        return match($this->subcategory) {
            'articles' => 'Articles of Incorporation',
            'bylaws' => 'Bylaws',
            'operating_agreement' => 'Operating Agreement',
            'state_certificate' => 'State Certificate',
            'ein_letter' => 'EIN Letter',
            'good_standing' => 'Good Standing Certificate',
            'annual_report' => 'Annual Report',
            'tax_filings' => 'Tax Filings',
            'minutes' => 'Meeting Minutes',
            'resolutions' => 'Corporate Resolutions',
            'agreements' => 'Agreements',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->subcategory)),
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending Review',
            'in_progress' => 'In Progress',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
            default => 'Unknown',
        };
    }

    /**
     * Get status color classes
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'text-yellow-600 bg-yellow-100 border-yellow-200',
            'in_progress' => 'text-blue-600 bg-blue-100 border-blue-200',
            'approved' => 'text-green-600 bg-green-100 border-green-200',
            'rejected' => 'text-red-600 bg-red-100 border-red-200',
            'expired' => 'text-gray-600 bg-gray-100 border-gray-200',
            default => 'text-gray-600 bg-gray-100 border-gray-200',
        };
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        return $this->getFormattedFileSizeAttribute();
    }

    /**
     * Check if document is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if document is expiring soon (within 30 days)
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expires_at && 
               $this->expires_at->isFuture() && 
               $this->expires_at->diffInDays(now()) <= 30;
    }

    /**
     * Check if document is downloadable
     */
    public function getIsDownloadableAttribute(): bool
    {
        return $this->canBeDownloaded();
    }

    /**
     * Check if document can be previewed
     */
    public function getCanPreviewAttribute(): bool
    {
        return $this->canBePreviewed();
    }

    /**
     * Check if document can be downloaded
     */
    public function canBeDownloaded(): bool
    {
        return $this->status === 'approved' && 
               $this->visibility !== 'private' && 
               (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Check if document can be previewed
     */
    public function canBePreviewed(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/plain'
        ]);
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
        $this->update(['last_downloaded_at' => now()]);
    }

    /**
     * Get file contents
     */
    public function getFileContents()
    {
        return Storage::get($this->file_path);
    }

    /**
     * Delete file from storage
     */
    public function deleteFile()
    {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }

    /**
     * Scope for active versions only
     */
    public function scopeActiveVersions($query)
    {
        return $query->where('is_active_version', true);
    }

    /**
     * Scope for specific document type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for approved documents
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
