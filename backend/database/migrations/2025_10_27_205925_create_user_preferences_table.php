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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('preferred_sources')->nullable(); // Array of news source IDs
            $table->json('preferred_categories')->nullable(); // Array of category IDs
            $table->json('excluded_sources')->nullable(); // Array of news source IDs to exclude
            $table->json('excluded_categories')->nullable(); // Array of category IDs to exclude
            $table->string('language', 5)->default('en');
            $table->string('country')->nullable();
            $table->integer('items_per_page')->default(20);
            $table->boolean('show_images')->default(true);
            $table->boolean('auto_refresh')->default(false);
            $table->integer('refresh_interval_minutes')->default(30);
            $table->json('notification_settings')->nullable(); // Email, push notifications, etc.
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['user_id', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
