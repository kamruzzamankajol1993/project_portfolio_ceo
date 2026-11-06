<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_version_id')->constrained('profile_versions')->cascadeOnDelete();
            $table->string('section_key', 90);
            $table->string('title', 190);
            $table->text('intro')->nullable();
            $table->string('section_type', 40)->default('content');
            $table->string('layout', 40)->default('default');
            $table->json('settings')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['profile_version_id', 'section_key'], 'profile_sections_version_key_uq');
            $table->index(['profile_version_id', 'sort_order'], 'profile_sections_order_idx');
        });

        Schema::create('profile_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('profile_sections')->cascadeOnDelete();
            $table->string('title', 220)->nullable();
            $table->string('subtitle', 220)->nullable();
            $table->longText('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('image_path')->nullable();
            $table->string('url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['section_id', 'sort_order'], 'profile_section_items_order_idx');
        });

        Schema::create('profile_version_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_version_id')->constrained('profile_versions')->cascadeOnDelete();
            $table->string('link_type', 50)->default('website');
            $table->string('label', 120)->nullable();
            $table->string('url', 500);
            $table->string('icon', 100)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['profile_version_id', 'link_type'], 'profile_version_links_type_idx');
        });

        Schema::create('profile_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_version_id')->constrained('profile_versions')->cascadeOnDelete();
            $table->string('title', 255);
            $table->string('publisher', 180)->nullable();
            $table->string('url', 500)->nullable();
            $table->date('source_date')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['profile_version_id', 'sort_order'], 'profile_references_order_idx');
        });

        Schema::create('profile_version_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_version_id')->constrained('profile_versions')->cascadeOnDelete();
            $table->foreignId('related_profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('relation_type', 60); // founder, co_founder, ceo, employee, subsidiary, partner...
            $table->string('position', 180)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['profile_version_id', 'related_profile_id', 'relation_type'], 'profile_version_relations_uq');
        });

        Schema::create('media_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('uploaded_by_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('collection', 60)->default('general')->index();
            $table->string('disk', 40)->default('public');
            $table->string('path', 500);
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('caption', 255)->nullable();
            $table->timestamps();
            $table->index(['disk', 'path'], 'media_assets_disk_path_idx');
        });

        Schema::create('profile_version_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_version_id')->constrained('profile_versions')->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
            $table->string('role', 60)->default('gallery'); // portrait, logo, banner, founder, gallery...
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['profile_version_id', 'media_asset_id', 'role'], 'profile_version_media_role_uq');
        });

        Schema::create('profile_version_seo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_version_id')->unique()->constrained('profile_versions')->cascadeOnDelete();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('robots', 40)->default('index,follow');
            $table->string('canonical_url', 500)->nullable();
            $table->string('og_title', 255)->nullable();
            $table->string('og_description', 500)->nullable();
            $table->string('og_image', 500)->nullable();
            $table->string('twitter_title', 255)->nullable();
            $table->string('twitter_description', 500)->nullable();
            $table->string('twitter_image', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_version_seo');
        Schema::dropIfExists('profile_version_media');
        Schema::dropIfExists('media_assets');
        Schema::dropIfExists('profile_version_relationships');
        Schema::dropIfExists('profile_references');
        Schema::dropIfExists('profile_version_links');
        Schema::dropIfExists('profile_section_items');
        Schema::dropIfExists('profile_sections');
    }
};
