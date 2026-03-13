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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->unique(); // MSG-2025-001234
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            
            // Message Content
            $table->string('subject');
            $table->text('body');
            $table->enum('type', [
                'general', 'order_update', 'document_request', 'payment_reminder',
                'compliance_notice', 'support_ticket', 'system_notification', 
                'welcome', 'completion_notice'
            ])->default('general');
            
            // Message Status
            $table->enum('status', ['draft', 'sent', 'delivered', 'read', 'archived'])->default('sent');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Threading and Conversation
            $table->foreignId('thread_id')->nullable()->constrained('messages')->onDelete('cascade');
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->onDelete('cascade');
            $table->boolean('is_thread_starter')->default(false);
            
            // Read Status and Tracking
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // Attachments
            $table->json('attachments')->nullable(); // Array of attachment file paths
            $table->boolean('has_attachments')->default(false);
            
            // Sender Information
            $table->enum('sender_type', ['client', 'admin', 'staff', 'system'])->default('client');
            $table->string('sender_name')->nullable(); // For system messages
            $table->string('sender_email')->nullable(); // For external messages
            
            // Flags and Categories
            $table->boolean('is_internal')->default(false); // Internal admin messages
            $table->boolean('is_automated')->default(false); // System-generated messages
            $table->boolean('requires_response')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_archived')->default(false);
            
            // Response and Resolution
            $table->timestamp('response_due_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            
            // Template and Automation
            $table->string('template_id')->nullable(); // For templated messages
            $table->json('template_variables')->nullable(); // Variables used in template
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional message data
            $table->string('correlation_id')->nullable(); // For tracking related messages
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['sender_id', 'status']);
            $table->index(['recipient_id', 'is_read']);
            $table->index(['order_id', 'type']);
            $table->index(['thread_id', 'created_at']);
            $table->index('message_id');
            $table->index(['type', 'priority']);
            $table->index('correlation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
