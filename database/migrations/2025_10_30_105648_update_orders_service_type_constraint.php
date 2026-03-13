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
            // Drop the existing constraint and recreate with green_card included
            $table->dropColumn('service_type');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('service_type', [
                'c_corp', 's_corp', 'llc', 'nonprofit', 'ein_only', 
                'registered_agent', 'compliance_kit', 'green_card', 'green_card_lottery',
                'tax_filing', 'bookkeeping'
            ])->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
        
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('service_type', [
                'c_corp', 's_corp', 'llc', 'nonprofit', 'ein_only', 
                'registered_agent', 'compliance_kit', 'green_card_lottery',
                'tax_filing', 'bookkeeping'
            ])->after('user_id');
        });
    }
};
