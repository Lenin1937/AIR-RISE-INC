<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // ORD-2025-001234
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Service Information
            $table->enum('service_type', [
                'c_corp', 's_corp', 'llc', 'nonprofit', 'ein_only', 
                'registered_agent', 'compliance_kit', 'green_card_lottery',
                'tax_filing', 'bookkeeping'
            ]);
            $table->string('state_of_incorporation', 2)->nullable();
            $table->string('entity_name');
            $table->text('business_purpose')->nullable();
            
            // C-Corp & S-Corp Specific Fields
            $table->json('shareholders')->nullable(); // Array of shareholder data
            $table->json('directors')->nullable(); // Array of director data
            $table->json('officers')->nullable(); // Array of officer data
            $table->integer('authorized_shares')->nullable();
            $table->decimal('par_value', 8, 4)->nullable();
            $table->string('stock_class')->nullable();
            
            // LLC Specific Fields
            $table->json('members')->nullable(); // Array of member data
            $table->json('managers')->nullable(); // Array of manager data
            $table->enum('management_structure', ['member_managed', 'manager_managed'])->nullable();
            
            // Nonprofit Specific Fields
            $table->text('charitable_purpose')->nullable();
            $table->boolean('501c3_application')->default(false);
            $table->json('board_members')->nullable(); // Array of board member data
            
            // Green Card Lottery Specific Fields
            $table->string('lottery_year')->nullable();
            $table->json('applicant_info')->nullable(); // Personal information
            $table->json('family_info')->nullable(); // Family member information
            
            // Order Status and Processing
            $table->enum('status', [
                'draft', 'pending', 'in_progress', 'under_review', 
                'approved', 'filed', 'completed', 'cancelled', 'refunded'
            ])->default('pending');
            
            // Pricing Information
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('state_fee', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            
            // Add-ons and Extras
            $table->json('addons')->nullable(); // Array of selected add-ons
            $table->decimal('addons_total', 10, 2)->default(0);
            
            // Processing Speed and Timeline
            $table->enum('processing_speed', ['standard', 'expedited', 'rush'])->default('standard');
            $table->integer('estimated_days')->nullable();
            $table->date('expected_completion_date')->nullable();
            
            // Government Filing Information
            $table->string('ein')->nullable(); // Federal EIN
            $table->date('ein_received_date')->nullable();
            $table->string('state_confirmation_number')->nullable();
            $table->date('state_filing_date')->nullable();
            $table->date('state_approval_date')->nullable();
            
            // Additional Information
            $table->text('special_instructions')->nullable();
            $table->text('internal_notes')->nullable(); // Admin-only notes
            
            // Compliance and Renewal
            $table->date('annual_report_due')->nullable();
            $table->decimal('annual_fee', 10, 2)->nullable();
            $table->boolean('auto_renewal')->default(false);
            
            // Important Timestamps
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index(['service_type', 'status']);
            $table->index('order_number');
            $table->index('state_of_incorporation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
