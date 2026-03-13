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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->unique(); // PAY-2025-001234
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            
            // Payment Details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('type', ['order_payment', 'additional_fee', 'refund', 'renewal']);
            $table->text('description')->nullable();
            
            // Stripe Integration
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_payment_method_id')->nullable();
            
            // Payment Status
            $table->enum('status', [
                'pending', 'processing', 'succeeded', 'failed', 
                'cancelled', 'refunded', 'partially_refunded'
            ])->default('pending');
            
            // Payment Method
            $table->enum('payment_method', [
                'card', 'bank_transfer', 'ach', 'wire', 'check', 'cash'
            ])->default('card');
            
            // Card Information (masked)
            $table->string('card_last_four')->nullable();
            $table->string('card_brand')->nullable(); // visa, mastercard, etc.
            $table->string('card_exp_month')->nullable();
            $table->string('card_exp_year')->nullable();
            
            // Billing Address
            $table->json('billing_address')->nullable();
            
            // Fees & Processing
            $table->decimal('processing_fee', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2); // Amount after fees
            
            // Refund Information
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_reason')->nullable();
            
            // Receipt & Invoice
            $table->string('receipt_url')->nullable();
            $table->string('invoice_number')->nullable();
            $table->boolean('receipt_sent')->default(false);
            
            // Processing Details
            $table->json('stripe_response')->nullable(); // Store Stripe response
            $table->string('failure_code')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['order_id', 'type']);
            $table->index('stripe_payment_intent_id');
            $table->index('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
