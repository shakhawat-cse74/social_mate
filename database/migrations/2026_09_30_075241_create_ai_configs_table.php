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
        Schema::create('ai_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Default AI Assistant');
            $table->boolean('is_active')->default(true);
            $table->string('provider')->default('gemini'); // gemini, openai, mock
            $table->string('model')->default('gemini-1.5-flash');
            $table->text('api_key')->nullable();
            $table->text('system_prompt')->nullable();
            $table->longText('knowledge_base')->nullable(); // FAQs, product list, policies
            $table->boolean('auto_reply_enabled')->default(true);
            $table->json('handover_keywords')->nullable(); // triggers human takeover
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_configs');
    }
};
