<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('language', 5)->default('en');
            $table->enum('client_type', ['prospect', 'active', 'past', 'internal'])->default('prospect');
            $table->string('service_type')->nullable(); // llc, c-corp, s-corp, nonprofit, tax, green-card
            $table->enum('source', ['website_form', 'manual', 'import', 'order', 'referral'])->default('manual');
            $table->json('tags')->nullable();
            $table->boolean('subscribed_marketing')->default(true);
            $table->boolean('subscribed_transactional')->default(true);
            $table->timestamp('unsubscribed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_contacts');
    }
};
