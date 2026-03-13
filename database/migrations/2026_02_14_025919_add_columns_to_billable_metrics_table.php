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
        Schema::table('billable_metrics', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->after('user_id')->constrained()->onDelete('cascade');
            $table->string('metric_name')->after('subscription_id');
            $table->decimal('value', 15, 4)->after('metric_name');
            $table->enum('aggregation_type', ['sum', 'count', 'max', 'last'])->default('sum')->after('value');
            $table->string('unit')->nullable()->after('aggregation_type');
            $table->timestamp('recorded_at')->after('unit');
            $table->json('metadata')->nullable()->after('recorded_at');
            
            $table->index(['user_id', 'metric_name', 'recorded_at']);
            $table->index(['subscription_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billable_metrics', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'metric_name', 'recorded_at']);
            $table->dropIndex(['subscription_id', 'recorded_at']);
            $table->dropColumn([
                'user_id',
                'subscription_id',
                'metric_name',
                'value',
                'aggregation_type',
                'unit',
                'recorded_at',
                'metadata'
            ]);
        });
    }
};
