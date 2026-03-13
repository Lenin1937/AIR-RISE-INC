<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('email');
            $table->string('telegram_username')->nullable()->after('phone');
            $table->enum('registration_status', [
                'incomplete',       // Step 1 – Personal info needed
                'order_pending',    // Step 2 – Create order / payment
                'pending_approval', // Step 3 – Awaiting admin review
                'approved',         // Admin approved – full access
                'rejected',         // Admin rejected
            ])->default('incomplete')->after('marketing_consent');
            $table->text('rejection_reason')->nullable()->after('registration_status');
            $table->timestamp('approved_at')->nullable()->after('rejection_reason');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'telegram_username',
                'registration_status',
                'rejection_reason',
                'approved_at',
                'approved_by',
            ]);
        });
    }
};
