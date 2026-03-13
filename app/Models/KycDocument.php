<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class KycDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'kyc_id',
        'user_id',
        'order_id',
        'document_type',
        'document_category',
        'original_filename',
        'stored_filename',
        'file_path',
        'mime_type',
        'file_size',
        'file_hash',
        'is_encrypted',
        'encryption_key',
        'access_token',
        'document_number_encrypted',
        'additional_info_encrypted',
        'verification_status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'rejection_reason',
        'document_expiry_date',
        'verification_valid_until',
        'is_expired',
        'retention_until',
        'auto_delete_after_retention',
        'compliance_level',
        'access_log',
        'access_count',
        'last_accessed_at',
        'issuing_country',
        'issuing_authority',
        'issue_date',
        'extracted_data',
        'is_sensitive',
        'requires_manual_review',
        'is_archived',
        'version',
        'superseded_by',
    ];

    protected function casts(): array
    {
        return [
            'access_log' => 'array',
            'extracted_data' => 'array',
            'is_encrypted' => 'boolean',
            'is_expired' => 'boolean',
            'auto_delete_after_retention' => 'boolean',
            'is_sensitive' => 'boolean',
            'requires_manual_review' => 'boolean',
            'is_archived' => 'boolean',
            'reviewed_at' => 'datetime',
            'document_expiry_date' => 'date',
            'verification_valid_until' => 'date',
            'retention_until' => 'date',
            'issue_date' => 'date',
            'last_accessed_at' => 'datetime',
        ];
    }

    /**
     * Generate unique KYC ID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($document) {
            if (!$document->kyc_id) {
                $document->kyc_id = 'KYC-' . date('Y') . '-' . str_pad(
                    static::whereYear('created_at', date('Y'))->count() + 1, 
                    6, '0', STR_PAD_LEFT
                );
            }

            // Generate encryption key if not provided
            if (!$document->encryption_key) {
                $document->encryption_key = bin2hex(random_bytes(32));
            }

            // Generate access token
            if (!$document->access_token) {
                $document->access_token = bin2hex(random_bytes(32));
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
     * Get the document that superseded this one
     */
    public function supersededBy()
    {
        return $this->belongsTo(KycDocument::class, 'superseded_by');
    }

    /**
     * Get documents superseded by this one
     */
    public function supersedes()
    {
        return $this->hasMany(KycDocument::class, 'superseded_by');
    }

    /**
     * Get document type display name
     */
    public function getDocumentTypeDisplayNameAttribute(): string
    {
        return match($this->document_type) {
            'passport' => 'Passport',
            'drivers_license' => 'Driver\'s License',
            'national_id' => 'National ID',
            'state_id' => 'State ID',
            'utility_bill' => 'Utility Bill',
            'bank_statement' => 'Bank Statement',
            'lease_agreement' => 'Lease Agreement',
            'tax_return' => 'Tax Return',
            'payslip' => 'Pay Slip',
            'social_security_card' => 'Social Security Card',
            'birth_certificate' => 'Birth Certificate',
            'marriage_certificate' => 'Marriage Certificate',
            default => 'Other Document',
        };
    }

    /**
     * Get document category display name
     */
    public function getDocumentCategoryDisplayNameAttribute(): string
    {
        return match($this->document_category) {
            'identity_verification' => 'Identity Verification',
            'address_verification' => 'Address Verification',
            'income_verification' => 'Income Verification',
            'business_verification' => 'Business Verification',
            default => ucfirst(str_replace('_', ' ', $this->document_category)),
        };
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Encrypt and store document number
     */
    public function setDocumentNumber($number)
    {
        $this->document_number_encrypted = Crypt::encrypt($number);
    }

    /**
     * Decrypt and get document number
     */
    public function getDocumentNumber()
    {
        if ($this->document_number_encrypted) {
            return Crypt::decrypt($this->document_number_encrypted);
        }
        return null;
    }

    /**
     * Encrypt and store additional info
     */
    public function setAdditionalInfo($info)
    {
        $this->additional_info_encrypted = Crypt::encrypt($info);
    }

    /**
     * Decrypt and get additional info
     */
    public function getAdditionalInfo()
    {
        if ($this->additional_info_encrypted) {
            return Crypt::decrypt($this->additional_info_encrypted);
        }
        return null;
    }

    /**
     * Log access to this document
     */
    public function logAccess($userId, $action = 'view')
    {
        $accessLog = $this->access_log ?? [];
        $accessLog[] = [
            'user_id' => $userId,
            'action' => $action,
            'timestamp' => now()->toISOString(),
            'ip_address' => request()->ip(),
        ];

        $this->update([
            'access_log' => $accessLog,
            'access_count' => $this->access_count + 1,
            'last_accessed_at' => now(),
        ]);
    }

    /**
     * Check if document is expired
     */
    public function checkExpiry()
    {
        if ($this->document_expiry_date && $this->document_expiry_date->isPast()) {
            $this->update(['is_expired' => true]);
            return true;
        }
        return false;
    }

    /**
     * Get verification status color for UI
     */
    public function getVerificationStatusColorAttribute(): string
    {
        return match($this->verification_status) {
            'verified' => 'green',
            'pending', 'under_review' => 'yellow',
            'rejected', 'requires_resubmission' => 'red',
            'expired' => 'gray',
            default => 'blue',
        };
    }

    /**
     * Check if document can be accessed
     */
    public function canBeAccessed($userId): bool
    {
        // Owner can always access
        if ($this->user_id === $userId) {
            return true;
        }

        // Admin users can access
        $user = User::find($userId);
        if ($user && $user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Get secure access URL
     */
    public function getSecureAccessUrlAttribute(): string
    {
        return route('kyc-documents.secure-access', [
            'document' => $this->id,
            'token' => $this->access_token
        ]);
    }

    /**
     * Delete file from secure storage
     */
    public function deleteSecureFile()
    {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }

    /**
     * Scope for verified documents
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope for pending verification
     */
    public function scopePendingVerification($query)
    {
        return $query->whereIn('verification_status', ['pending', 'under_review']);
    }

    /**
     * Scope for expired documents
     */
    public function scopeExpired($query)
    {
        return $query->where('is_expired', true)
                    ->orWhere('document_expiry_date', '<', now());
    }

    /**
     * Scope for documents due for retention cleanup
     */
    public function scopeDueForRetention($query)
    {
        return $query->where('retention_until', '<', now())
                    ->where('auto_delete_after_retention', true);
    }

    /**
     * Scope for documents by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('document_category', $category);
    }
}