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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Guest User');
            $table->string('avatar_url')->nullable();
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('platform')->default('webchat'); // facebook, instagram, whatsapp, telegram, webchat
            $table->string('platform_sender_id')->index(); // PSID, Chat ID, WhatsApp number
            $table->string('lead_status')->default('lead'); // lead, prospect, customer, churned
            $table->string('pipeline_stage')->default('new'); // new, contacted, interested, qualified, won, lost
            $table->decimal('deal_value', 12, 2)->default(0.00);
            $table->json('tags')->nullable(); // e.g. ["hot_lead", "vip", "bulk_inquiry"]
            $table->json('custom_attributes')->nullable(); // Any custom fields
            $table->text('notes')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->unique(['platform', 'platform_sender_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
