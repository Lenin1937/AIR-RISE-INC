<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Drop the rigid check constraints on service_type and processing_speed so the
     * onboarding form (which sends display names like "C-Corporation" / "economic")
     * can save orders alongside legacy records (which used "c_corp" / "standard").
     * PHP-level validation in the controllers already enforces the allowed values.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_service_type_check');
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_processing_speed_check');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_service_type_check CHECK (
            service_type = ANY (ARRAY[
                'c_corp','s_corp','llc','nonprofit','ein_only',
                'registered_agent','compliance_kit','green_card',
                'green_card_lottery','tax_filing','bookkeeping'
            ])
        )");

        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_processing_speed_check CHECK (
            processing_speed = ANY (ARRAY['standard','expedited','rush'])
        )");
    }
};
