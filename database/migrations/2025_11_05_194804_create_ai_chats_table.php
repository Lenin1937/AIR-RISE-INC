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
        Schema::create('ai_chats', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique(); // Unique session identifier
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Null for guests
            
            // Context Information
            $table->string('page_url')->nullable(); // Current page when chat started
            $table->string('page_name')->nullable(); // Page name (Home, Pricing, etc.)
            $table->string('user_type')->default('guest'); // guest, client, admin
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            // Lead Information (for marketing pages)
            $table->string('visitor_name')->nullable();
            $table->string('visitor_email')->nullable();
            $table->string('visitor_phone')->nullable();
            
            // Chat Status
            $table->enum('status', ['active', 'closed', 'resolved', 'pending'])->default('active');
            $table->boolean('is_lead')->default(false); // If visitor provided contact info
            $table->integer('message_count')->default(0);
            
            // Admin Management
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional context data
            $table->text('notes')->nullable(); // Admin notes
            
            $table->timestamps();
            
            // Indexes
            $table->index('session_id');
            $table->index('user_id');
            $table->index(['status', 'created_at']);
            $table->index('visitor_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chats');
    }
};
