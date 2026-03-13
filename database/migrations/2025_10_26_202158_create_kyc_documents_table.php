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
        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->string('kyc_id')->unique(); // KYC-2025-001234
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            
            // Document Type and Category
            $table->enum('document_type', [
                'passport', 'drivers_license', 'national_id', 'state_id',
                'utility_bill', 'bank_statement', 'lease_agreement',
                'tax_return', 'payslip', 'social_security_card',
                'birth_certificate', 'marriage_certificate', 'other'
            ]);
            $table->enum('document_category', [
                'identity_verification', 'address_verification', 
                'income_verification', 'business_verification'
            ]);
            
            // File Information
            $table->string('original_filename');
            $table->string('stored_filename'); // Encrypted/hashed filename
            $table->string('file_path'); // Encrypted storage path
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->string('file_hash'); // For integrity checking
            
            // Encryption and Security
            $table->boolean('is_encrypted')->default(true);
            $table->string('encryption_key'); // Document-specific encryption key
            $table->string('access_token')->nullable(); // For secure access
            
            // Document Content (Encrypted)
            $table->text('document_number_encrypted')->nullable(); // ID numbers, etc.
            $table->text('additional_info_encrypted')->nullable(); // Additional KYC data
            
            // Verification Status
            $table->enum('verification_status', [
                'pending', 'under_review', 'verified', 'rejected', 
                'expired', 'requires_resubmission'
            ])->default('pending');
            
            // Review and Approval
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Expiry and Validity
            $table->date('document_expiry_date')->nullable();
            $table->date('verification_valid_until')->nullable();
            $table->boolean('is_expired')->default(false);
            
            // Compliance and Retention
            $table->date('retention_until'); // Legal retention period
            $table->boolean('auto_delete_after_retention')->default(true);
            $table->enum('compliance_level', ['basic', 'enhanced', 'ultimate'])->default('basic');
            
            // Audit Trail
            $table->json('access_log')->nullable(); // Who accessed when
            $table->integer('access_count')->default(0);
            $table->timestamp('last_accessed_at')->nullable();
            
            // Document Metadata
            $table->string('issuing_country', 2)->nullable();
            $table->string('issuing_authority')->nullable();
            $table->date('issue_date')->nullable();
            $table->json('extracted_data')->nullable(); // OCR/parsed data
            
            // System Flags
            $table->boolean('is_sensitive')->default(true);
            $table->boolean('requires_manual_review')->default(false);
            $table->boolean('is_archived')->default(false);
            
            // Version Control
            $table->integer('version')->default(1);
            $table->foreignId('superseded_by')->nullable()->constrained('kyc_documents')->onDelete('set null');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'document_type']);
            $table->index(['order_id', 'verification_status']);
            $table->index(['verification_status', 'document_category']);
            $table->index('kyc_id');
            $table->index('access_token');
            $table->index('retention_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_documents');
    }
};
