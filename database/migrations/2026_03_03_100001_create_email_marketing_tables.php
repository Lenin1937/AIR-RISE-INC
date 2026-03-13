<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('conditions'); // [{field, operator, value, logic}]
            $table->unsignedInteger('contact_count')->default(0);
            $table->boolean('auto_update')->default(true);
            $table->timestamps();
        });

        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->enum('category', ['transactional', 'marketing'])->default('marketing');
            $table->boolean('ai_generated')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->foreignId('segment_id')->nullable()->constrained('email_segments')->nullOnDelete();
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'cancelled'])->default('draft');
            $table->string('from_name')->default('CORPIUS');
            $table->string('from_email')->default('hello@corpius.net');
            $table->string('reply_to')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('total_sent')->default(0);
            $table->unsignedInteger('total_delivered')->default(0);
            $table->unsignedInteger('total_opened')->default(0);
            $table->unsignedInteger('total_clicked')->default(0);
            $table->unsignedInteger('total_bounced')->default(0);
            $table->unsignedInteger('total_unsubscribed')->default(0);
            $table->json('ab_test')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('email_campaign_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->string('contact_email');
            $table->enum('event_type', ['sent', 'delivered', 'opened', 'clicked', 'bounced', 'spam', 'unsubscribed']);
            $table->string('url')->nullable(); // for click events
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
        });

        Schema::create('email_automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('trigger', ['new_contact', 'order_created', 'order_status_changed', 'document_uploaded', 'manual', 'tag_added', 'date_based']);
            $table->json('trigger_conditions')->nullable();
            $table->enum('status', ['active', 'paused', 'draft'])->default('draft');
            $table->json('steps'); // [{delay_days, template_id, subject_override, conditions}]
            $table->unsignedInteger('enrolled_count')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_automations');
        Schema::dropIfExists('email_campaign_events');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('email_segments');
    }
};
