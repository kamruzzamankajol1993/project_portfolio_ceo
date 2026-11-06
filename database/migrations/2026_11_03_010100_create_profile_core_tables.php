<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // One public identity for either a person or a company.
        // Profile content itself is stored in immutable/reviewable versions.
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('profile_type', 20)->index(); // personal | company
            $table->string('slug', 190)->unique();
            $table->string('status', 20)->default('draft')->index(); // draft | published | archived
            $table->boolean('is_verified')->default(false);
            // FK is added after profile_versions is created (mutual reference uses RESTRICT, not CASCADE).
            $table->unsignedBigInteger('published_version_id')->nullable()->index();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->index(['profile_type', 'status'], 'profiles_type_status_idx');
        });

        Schema::create('profile_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->restrictOnDelete();
            $table->unsignedInteger('version_number');
            // A pending slug change stays here until the version is approved.
            $table->string('proposed_slug', 190)->nullable();
            $table->string('review_status', 24)->default('draft')->index();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('submitted_by_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->text('change_summary')->nullable();
            $table->timestamps();
            $table->unique(['profile_id', 'version_number'], 'profile_versions_profile_version_uq');
            $table->index(['review_status', 'submitted_at'], 'profile_versions_review_submitted_idx');
        });


        Schema::table('profiles', function (Blueprint $table) {
            $table->foreign('published_version_id', 'profiles_published_version_fk')
                ->references('id')->on('profile_versions')->restrictOnDelete();
        });

        Schema::create('personal_profile_details', function (Blueprint $table) {
            $table->foreignId('profile_version_id')->primary()->constrained('profile_versions')->cascadeOnDelete();
            $table->string('full_name', 180);
            $table->string('headline', 255)->nullable();
            $table->string('professional_title', 180)->nullable();
            $table->string('current_position', 180)->nullable();
            $table->string('company_organisation', 180)->nullable();
            $table->foreignId('industry_id')->nullable()->constrained('industries')->nullOnDelete();
            $table->string('location', 180)->nullable();
            $table->string('hometown', 180)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('occupation', 180)->nullable();
            $table->string('public_contact_email', 191)->nullable();
            $table->string('public_phone', 30)->nullable();
            $table->string('personal_website', 255)->nullable();
            $table->text('short_bio')->nullable();
            $table->longText('about')->nullable();
            $table->text('profile_quote')->nullable();
            $table->longText('early_life')->nullable();
            $table->longText('career_entrepreneurship')->nullable();
            $table->longText('education_summary')->nullable();
            $table->longText('awards_publications_recognition')->nullable();
            $table->boolean('show_public_email')->default(false);
            $table->boolean('show_public_phone')->default(false);
            $table->boolean('show_date_of_birth')->default(false);
            $table->timestamps();
        });

        Schema::create('company_profile_details', function (Blueprint $table) {
            $table->foreignId('profile_version_id')->primary()->constrained('profile_versions')->cascadeOnDelete();
            $table->string('company_name', 180);
            $table->string('tagline', 255)->nullable();
            $table->foreignId('industry_id')->nullable()->constrained('industries')->nullOnDelete();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->string('headquarters', 180)->nullable();
            $table->string('company_size', 60)->nullable();
            $table->string('business_type', 100)->nullable();
            $table->string('area_served', 255)->nullable();
            $table->string('official_website', 255)->nullable();
            $table->string('public_email', 191)->nullable();
            $table->string('public_phone', 30)->nullable();
            $table->text('short_description')->nullable();
            $table->longText('overview')->nullable();
            $table->longText('history_background')->nullable();
            $table->longText('mission')->nullable();
            $table->longText('vision')->nullable();
            $table->string('parent_company', 180)->nullable();
            $table->boolean('show_public_email')->default(false);
            $table->boolean('show_public_phone')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign('profiles_published_version_fk');
        });
        Schema::dropIfExists('company_profile_details');
        Schema::dropIfExists('personal_profile_details');
        Schema::dropIfExists('profile_versions');
        Schema::dropIfExists('profiles');
        Schema::dropIfExists('industries');
    }
};
