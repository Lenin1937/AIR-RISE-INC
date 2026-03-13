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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_chat_id')->constrained()->onDelete('cascade');
            
            // Message Content
            $table->enum('role', ['user', 'assistant', 'system']); // Who sent the message
            $table->text('content'); // Message text
            $table->json('metadata')->nullable(); // Token count, model used, etc.
            
            // AI Response Details
            $table->string('model')->nullable(); // AI model used (gemini-pro, etc.)
            $table->integer('tokens_used')->nullable();
            $table->integer('response_time_ms')->nullable(); // Time taken to generate
            $table->boolean('is_streamed')->default(false);
            
            // Message Status
            $table->boolean('is_error')->default(false);
            $table->text('error_message')->nullable();
            $table->boolean('is_flagged')->default(false); // Admin flagged for review
            
            // Context at time of message
            $table->string('page_context')->nullable(); // Page user was on when sending
            
            $table->timestamps();
            
            // Indexes
            $table->index('ai_chat_id');
            $table->index(['ai_chat_id', 'created_at']);
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
