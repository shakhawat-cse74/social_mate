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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Main FB Page", "Support Telegram Bot"
            $table->string('platform'); // facebook, instagram, whatsapp, telegram, webchat
            $table->string('platform_account_id')->nullable(); // Page ID, Phone Number ID, Bot Username
            $table->text('access_token')->nullable(); // Page token, Bot token, WhatsApp bearer
            $table->string('verify_token')->nullable(); // For Meta Webhook verification
            $table->string('webhook_secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('ai_enabled')->default(true);
            $table->json('settings')->nullable(); // greeting message, business hours, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
