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
        Schema::create('news_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('api_url');
            $table->string('api_key')->nullable();
            $table->json('api_config')->nullable(); // API configuration
            $table->string('logo_url')->nullable();
            $table->string('country')->nullable();
            $table->string('language', 5)->default('en');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_fetched_at')->nullable();
            $table->integer('fetch_interval_minutes')->default(60); // How often to fetch
            $table->timestamps();

            $table->index(['is_active', 'last_fetched_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
