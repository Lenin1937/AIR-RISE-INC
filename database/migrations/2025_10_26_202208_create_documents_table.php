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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_id')->unique(); // DOC-2025-001234
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            
            // Document Information
            $table->string('name'); // Original filename
            $table->string('display_name')->nullable(); // User-friendly name
            $table->enum('type', [
                'certificate_of_incorporation', 'articles_of_incorporation', 
                'operating_agreement', 'bylaws', 'ein_letter', 'minutes',
                'stock_certificates', 'membership_certificates', 'compliance_kit',
                'annual_report', 'tax_documents', 'kyc_document', 'id_document',
                'proof_of_address', 'bank_statement', 'other'
            ]);
            $table->text('description')->nullable();
            
            // File Information
            $table->string('file_path'); // Storage path
            $table->string('original_filename');
            $table->string('mime_type');
            $table->bigInteger('file_size'); // In bytes
            $table->string('file_extension');
            $table->string('file_hash')->nullable(); // For integrity checking
            
            // Document Status and Processing
            $table->enum('status', [
                'uploaded', 'processing', 'approved', 'rejected', 
                'pending_review', 'requires_action', 'archived'
            ])->default('uploaded');
            
            // Access and Security
            $table->enum('visibility', ['private', 'shared', 'public'])->default('private');
            $table->boolean('is_encrypted')->default(false);
            $table->string('encryption_key')->nullable();
            
            // Approval and Review
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Version Control
            $table->integer('version')->default(1);
            $table->foreignId('parent_document_id')->nullable()->constrained('documents')->onDelete('cascade');
            $table->boolean('is_active_version')->default(true);
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional document metadata
            $table->integer('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            
            // Retention and Expiry
            $table->date('expires_at')->nullable();
            $table->boolean('is_permanent')->default(false);
            $table->date('retention_until')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'type']);
            $table->index(['order_id', 'type']);
            $table->index(['status', 'type']);
            $table->index('document_id');
            $table->index('file_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
