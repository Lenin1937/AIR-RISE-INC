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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_id')->unique(); // NOT-2025-001234
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            
            // Notification Content
            $table->string('title');
            $table->text('message');
            $table->enum('type', [
                'order_update', 'payment_received', 'document_ready', 
                'document_required', 'compliance_reminder', 'welcome',
                'completion', 'system_alert', 'promotion', 'security'
            ]);
            
            // Status and Priority
            $table->boolean('is_read')->default(false);
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_actionable')->default(false);
            
            // Action Information
            $table->string('action_url')->nullable(); // URL to navigate to
            $table->string('action_text')->nullable(); // Button text
            $table->json('action_data')->nullable(); // Additional action data
            
            // Delivery Channels
            $table->boolean('sent_email')->default(false);
            $table->boolean('sent_sms')->default(false);
            $table->boolean('sent_push')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            
            // Read Status
            $table->timestamp('read_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            $table->string('correlation_id')->nullable();
            
            // Expiry and Auto-cleanup
            $table->timestamp('expires_at')->nullable();
            $table->boolean('auto_delete_after_read')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'is_read']);
            $table->index(['type', 'priority']);
            $table->index('notification_id');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
