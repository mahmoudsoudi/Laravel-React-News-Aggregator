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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('content')->nullable();
            $table->string('url')->unique();
            $table->string('image_url')->nullable();
            $table->string('author')->nullable();
            $table->timestamp('published_at');
            $table->foreignId('news_source_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('external_id')->nullable(); // ID from the news source
            $table->json('metadata')->nullable(); // Additional data from source
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['published_at', 'is_active']);
            $table->index(['news_source_id', 'published_at']);
            $table->index(['category_id', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
