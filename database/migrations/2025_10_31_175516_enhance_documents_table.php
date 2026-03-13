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
        Schema::table('documents', function (Blueprint $table) {
            // Only add columns that don't exist yet
            if (!Schema::hasColumn('documents', 'category')) {
                $table->string('category')->default('additional')->after('type');
            }
            if (!Schema::hasColumn('documents', 'subcategory')) {
                $table->string('subcategory')->nullable()->after('type');
            }
            if (!Schema::hasColumn('documents', 'generated_at')) {
                $table->timestamp('generated_at')->nullable()->after('file_hash');
            }
            if (!Schema::hasColumn('documents', 'is_public')) {
                $table->boolean('is_public')->default(false)->after('download_count');
            }
            if (!Schema::hasColumn('documents', 'access_token')) {
                $table->string('access_token')->nullable()->after('is_public');
            }
            if (!Schema::hasColumn('documents', 'access_token_expires_at')) {
                $table->timestamp('access_token_expires_at')->nullable()->after('access_token');
            }
            if (!Schema::hasColumn('documents', 'uploaded_by')) {
                $table->unsignedBigInteger('uploaded_by')->nullable()->after('access_token_expires_at');
                $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('documents', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('uploaded_by');
            }
            
            // Add indexes for performance
            if (!Schema::hasIndex('documents', 'documents_user_id_category_status_index')) {
                $table->index(['user_id', 'category', 'status']);
            }
            if (!Schema::hasIndex('documents', 'documents_order_id_category_index')) {
                $table->index(['order_id', 'category']);
            }
            if (!Schema::hasIndex('documents', 'documents_access_token_index')) {
                $table->index('access_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'uploaded_by')) {
                $table->dropForeign(['uploaded_by']);
            }
            $table->dropColumn([
                'category', 'subcategory', 'generated_at', 'is_public', 
                'access_token', 'access_token_expires_at', 'uploaded_by', 'admin_notes'
            ]);
        });
    }
};
