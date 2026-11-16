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
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "My WooCommerce Store", "Mobile App Backend"
            $table->string('url'); // Destination webhook URL
            $table->string('secret')->nullable(); // HMAC secret for payload signature verification
            $table->json('subscribed_events')->nullable(); // ['message.received', 'contact.created', 'stage.updated']
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_dispatched_at')->nullable();
            $table->integer('failure_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_endpoints');
    }
};
