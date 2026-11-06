<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->string('slug', 180)->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('meta_robots', 40)->default('index,follow');
            $table->string('canonical_url', 255)->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });

        Schema::create('extra_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 50)->unique();
            $table->string('title', 255);
            $table->string('slug', 180)->unique();
            $table->longText('content')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('meta_robots', 40)->default('index,follow');
            $table->string('canonical_url', 255)->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_pages');
        Schema::dropIfExists('posts');
    }
};
