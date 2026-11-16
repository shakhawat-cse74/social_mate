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
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('short_code', 10)->default('APP');
            $table->string('color', 50)->default('#6366f1');
            $table->text('description')->nullable();
            $table->string('outbound_api_url')->nullable();
            $table->boolean('is_builtin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable(); // field mapping, default headers, template
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};
