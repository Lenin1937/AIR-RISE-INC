<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'service_type',
        'state_of_incorporation',
        'state',
        'package_type',
        'processing_speed',
        'entity_name',
        'business_purpose',
        'shareholders',
        'directors',
        'officers',
        'authorized_shares',
        'par_value',
        'stock_class',
        'members',
        'managers',
        'management_structure',
        'charitable_purpose',
        '501c3_application',
        'board_members',
        'lottery_year',
        'applicant_info',
        'family_info',
        'contact_info',
        'business_details',
        'requirements',
        'required_documents',
        'payment_method',
        'payment_screenshot',
        'status',
        'service_fee',
        'state_fee',
        'subtotal',
        'processing_fee',
        'total_amount',
        'amount_paid',
        'currency',
        'addons',
        'addons_total',
        'estimated_days',
        'expected_completion_date',
        'estimated_completion_date',
        'ein',
        'ein_received_date',
        'state_confirmation_number',
        'state_filing_date',
        'state_approval_date',
        'special_instructions',
        'internal_notes',
        'annual_report_due',
        'annual_fee',
        'auto_renewal',
        'submitted_at',
        'completed_at',
        'approved_at',
        'rejected_at',
        'approval_notes',
        'rejection_reason',
        'timeline_events',
    ];

    protected function casts(): array
    {
        return [
            'shareholders' => 'array',
            'directors' => 'array',
            'officers' => 'array',
            'members' => 'array',
            'managers' => 'array',
            'board_members' => 'array',
            'applicant_info' => 'array',
            'family_info' => 'array',
            'contact_info' => 'array',
            'business_details' => 'array',
            'requirements' => 'array',
            'required_documents' => 'array',
            'addons' => 'array',
            'timeline_events' => 'array',
            '501c3_application' => 'boolean',
            'auto_renewal' => 'boolean',
            'service_fee' => 'decimal:2',
            'state_fee' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'processing_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'addons_total' => 'decimal:2',
            'annual_fee' => 'decimal:2',
            'par_value' => 'decimal:4',
            'expected_completion_date' => 'date',
            'estimated_completion_date' => 'date',
            'ein_received_date' => 'date',
            'state_filing_date' => 'date',
            'state_approval_date' => 'date',
            'annual_report_due' => 'date',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order's documents
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the order's payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the order's messages
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the order's addresses
     */
    public function addresses()
    {
        return $this->hasMany(OrderAddress::class);
    }

    /**
     * Check if order is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get formatted service type
     */
    public function getServiceTypeNameAttribute(): string
    {
        return match($this->service_type) {
            'c_corp' => 'C-Corporation',
            's_corp' => 'S-Corporation',
            'llc' => 'LLC',
            'nonprofit' => 'Nonprofit',
            'ein_only' => 'EIN Only',
            'registered_agent' => 'Registered Agent',
            'compliance_kit' => 'Compliance Kit',
            'green_card_lottery' => 'Green Card Lottery',
            'green_card' => 'Green Card Lottery',
            'tax_filing' => 'Tax Filing',
            'bookkeeping' => 'Bookkeeping',
            default => $this->service_type,
        };
    }

    /**
     * Get formatted status display
     */
    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'under_review' => 'Under Review',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'on_hold' => 'On Hold',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        return match($this->status) {
            'pending' => 10,
            'in_progress' => 40,
            'under_review' => 70,
            'completed' => 100,
            'cancelled' => 0,
            default => 20,
        };
    }

    /**
     * Get current progress step
     */
    public function getProgressStepAttribute(): int
    {
        return match($this->status) {
            'pending' => 1,
            'in_progress' => 3,
            'under_review' => 5,
            'completed' => 6,
            default => 1,
        };
    }

    /**
     * Get current stage
     */
    public function getCurrentStageAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Order Received',
            'in_progress' => 'Processing Documents',
            'under_review' => 'Government Review',
            'completed' => 'Formation Complete',
            'cancelled' => 'Order Cancelled',
            default => 'Processing',
        };
    }

    /**
     * Get timeline events
     */
    public function getTimelineEventsAttribute(): array
    {
        $timeline = [];
        
        $timeline[] = [
            'title' => 'Order Placed',
            'description' => 'Your order has been received and is being processed.',
            'completed_at' => $this->created_at->toISOString(),
            'status' => 'completed'
        ];

        if ($this->status !== 'pending') {
            $timeline[] = [
                'title' => 'Document Preparation',
                'description' => 'We are preparing your incorporation documents.',
                'completed_at' => $this->updated_at->toISOString(),
                'status' => 'completed'
            ];
        }

        if (in_array($this->status, ['under_review', 'completed'])) {
            $timeline[] = [
                'title' => 'State Filing',
                'description' => 'Documents have been submitted to the state.',
                'completed_at' => $this->state_filing_date?->toISOString(),
                'status' => 'completed'
            ];
        }

        if ($this->status === 'completed') {
            $timeline[] = [
                'title' => 'Formation Complete',
                'description' => 'Your business has been successfully formed!',
                'completed_at' => $this->completed_at?->toISOString() ?? $this->updated_at->toISOString(),
                'status' => 'completed'
            ];
        }

        return $timeline;
    }
}
