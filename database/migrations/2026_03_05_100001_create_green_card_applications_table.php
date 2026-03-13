<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('green_card_applications', function (Blueprint $table) {
            $table->id();
            $table->string('session_token')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->enum('package_type', ['basic', 'family', 'premium'])->default('basic');
            $table->decimal('package_price', 10, 2)->default(49.00);
            $table->enum('status', [
                'draft',
                'payment_pending',
                'in_review',
                'changes_requested',
                'ready_for_submission',
                'submitted',
                'closed',
            ])->default('draft');
            $table->integer('current_step')->default(1);

            // Primary applicant
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('city_of_birth')->nullable();
            $table->string('country_of_birth')->nullable();
            $table->string('country_of_eligibility')->nullable();

            // Passport
            $table->string('passport_number')->nullable();
            $table->string('passport_country')->nullable();
            $table->date('passport_expiry')->nullable();

            // Contact
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state_province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('education_level')->nullable();

            // Family flags
            $table->boolean('has_spouse')->default(false);
            $table->boolean('has_children')->default(false);

            // Photo paths
            $table->string('primary_photo_path')->nullable();

            // Payment
            $table->string('payment_intent_id')->nullable();
            $table->string('payment_method')->nullable();
            $table->boolean('paid')->default(false);
            $table->timestamp('paid_at')->nullable();

            // Confirmations
            $table->boolean('confirmed_accuracy')->default(false);
            $table->boolean('confirmed_single_entry')->default(false);
            $table->boolean('confirmed_not_govt')->default(false);
            $table->boolean('confirmed_tos')->default(false);

            // Admin
            $table->string('confirmation_number')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('green_card_applications');
    }
};
