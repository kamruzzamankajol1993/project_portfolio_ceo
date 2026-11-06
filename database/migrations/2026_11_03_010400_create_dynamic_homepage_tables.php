<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Key/value settings keep hero copy, CTA labels, footer content, etc. editable.
        Schema::create('homepage_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key', 120)->unique();
            $table->string('group_key', 80)->default('general')->index();
            $table->string('label', 180)->nullable();
            $table->string('field_type', 40)->default('text');
            $table->json('value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Sections can be enabled, hidden, reordered, and populated without code changes.
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 100)->unique();
            $table->string('section_type', 50)->default('content');
            $table->string('title', 220)->nullable();
            $table->string('subtitle', 500)->nullable();
            $table->longText('content')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_enabled', 'sort_order'], 'homepage_sections_enabled_order_idx');
        });

        // A homepage item may be a profile card, a blog spotlight, a feature card, a stat, or a CTA.
        Schema::create('homepage_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homepage_section_id')->constrained('homepage_sections')->cascadeOnDelete();
            $table->foreignId('profile_id')->nullable()->constrained('profiles')->nullOnDelete();
            $table->foreignId('post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->string('item_type', 50)->default('card');
            $table->string('title_override', 220)->nullable();
            $table->string('subtitle', 220)->nullable();
            $table->text('description')->nullable();
            $table->string('value_text', 120)->nullable();
            $table->string('value_source', 60)->nullable(); // static | published_profiles | published_companies | published_people
            $table->string('icon', 100)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->string('url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['homepage_section_id', 'is_enabled', 'sort_order'], 'homepage_items_section_order_idx');
        });

        // Header and footer navigation are database-managed too (including dropdown parents).
        Schema::create('site_navigation_menus', function (Blueprint $table) {
            $table->id();
            $table->string('menu_key', 80)->unique(); // primary, footer_explore, footer_support...
            $table->string('title', 150)->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('site_navigation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('site_navigation_menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('site_navigation_items')->nullOnDelete();
            $table->string('label', 150);
            $table->string('url', 500)->nullable();
            $table->string('route_name', 150)->nullable();
            $table->string('target', 20)->default('_self');
            $table->string('icon', 100)->nullable();
            $table->string('css_class', 120)->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['menu_id', 'parent_id', 'sort_order'], 'site_nav_menu_parent_order_idx');
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email', 191)->unique();
            $table->string('status', 20)->default('subscribed')->index();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('site_navigation_items');
        Schema::dropIfExists('site_navigation_menus');
        Schema::dropIfExists('homepage_section_items');
        Schema::dropIfExists('homepage_sections');
        Schema::dropIfExists('homepage_settings');
    }
};
