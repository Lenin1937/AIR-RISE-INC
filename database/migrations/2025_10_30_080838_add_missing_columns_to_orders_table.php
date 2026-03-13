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
        Schema::table('orders', function (Blueprint $table) {
            // Add missing columns that the controller expects
            $table->string('state', 2)->nullable()->after('state_of_incorporation');
            $table->string('package_type')->nullable()->after('service_type');
            $table->json('contact_info')->nullable()->after('family_info');
            $table->json('business_details')->nullable()->after('contact_info');
            $table->json('requirements')->nullable()->after('business_details');
            $table->decimal('subtotal', 10, 2)->nullable()->after('service_fee');
            $table->decimal('processing_fee', 10, 2)->nullable()->after('subtotal');
            $table->date('estimated_completion_date')->nullable()->after('expected_completion_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'state', 'package_type', 'contact_info', 'business_details', 
                'requirements', 'subtotal', 'processing_fee', 'estimated_completion_date'
            ]);
        });
    }
};
