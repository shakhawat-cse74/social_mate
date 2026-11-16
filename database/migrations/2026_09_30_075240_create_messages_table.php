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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
            $table->string('direction'); // inbound, outbound
            $table->string('sender_type'); // contact, human_agent, ai_agent, system
            $table->string('platform_message_id')->nullable()->index();
            $table->string('message_type')->default('text'); // text, image, audio, video, document, template
            $table->text('content');
            $table->json('attachments')->nullable();
            $table->string('status')->default('sent'); // queued, sent, delivered, read, failed
            $table->boolean('ai_generated')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
