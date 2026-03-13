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
        Schema::table('users', function (Blueprint $table) {
            // Personal Information
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            
            // Business Information
            $table->string('company_name')->nullable();
            $table->string('preferred_state', 2)->nullable();
            $table->enum('user_type', ['individual', 'business'])->default('individual');
            $table->enum('business_type', ['C-Corp', 'S-Corp', 'LLC', 'Nonprofit'])->nullable();
            
            // Address Information
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->default('US');
            
            // Additional Fields
            $table->string('citizenship')->nullable();
            $table->string('preferred_language')->default('en');
            $table->string('timezone')->nullable();
            
            // Preferences and Consent
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('marketing_consent')->default(false);
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            $table->boolean('privacy_accepted')->default(false);
            $table->timestamp('privacy_accepted_at')->nullable();
            
            // Verification and Security
            $table->boolean('phone_verified')->default(false);
            $table->boolean('kyc_verified')->default(false);
            $table->timestamp('kyc_verified_at')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->text('ssn_encrypted')->nullable(); // Encrypted SSN
            
            // System Fields
            $table->string('registration_source')->default('web');
            $table->timestamp('last_login_at')->nullable();
            $table->date('data_retention_until')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'company_name',
                'preferred_state',
                'user_type',
                'business_type',
                'address_line_1',
                'address_line_2',
                'city',
                'state',
                'zip_code',
                'country',
                'citizenship',
                'preferred_language',
                'timezone',
                'email_notifications',
                'sms_notifications',
                'marketing_consent',
                'terms_accepted',
                'terms_accepted_at',
                'privacy_accepted',
                'privacy_accepted_at',
                'phone_verified',
                'kyc_verified',
                'kyc_verified_at',
                'two_factor_enabled',
                'two_factor_secret',
                'ssn_encrypted',
                'registration_source',
                'last_login_at',
                'data_retention_until',
            ]);
        });
    }
};
