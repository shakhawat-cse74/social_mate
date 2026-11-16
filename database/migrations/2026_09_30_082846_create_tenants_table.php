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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('plan_name')->default('Growth'); // Starter, Growth, Enterprise
            $table->string('status')->default('active'); // active, suspended, expired, trialing
            $table->json('allowed_channels')->nullable(); // ['facebook', 'whatsapp', 'telegram', 'instagram', 'webchat']
            $table->boolean('ai_enabled')->default(true);
            $table->unsignedInteger('max_agents')->default(5);
            $table->timestamp('subscription_starts_at')->nullable();
            $table->timestamp('subscription_expires_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
