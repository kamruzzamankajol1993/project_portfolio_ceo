<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('name');
            $table->string('image')->nullable()->after('email');
            $table->enum('status',['active','inactive'])->default('active')->after('password');
            $table->text('address')->nullable();
            $table->text('bio')->nullable();
        });
        Schema::table(config('permission.table_names.permissions'), function (Blueprint $table) {
            $table->string('group')->default('General')->index();
        });
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name');
            $table->string('tagline')->nullable();
            $table->string('site_url')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone',30)->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->text('footer_text')->nullable();
            foreach (['facebook_url','instagram_url','youtube_url','linkedin_url','seo_canonical'] as $column) $table->string($column)->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();
            $table->string('seo_robots')->default('index,follow');
            $table->string('seo_og_image')->nullable();
            $table->string('seo_og_type')->default('website');
            $table->string('seo_twitter_card')->default('summary_large_image');
            $table->string('google_analytics_id')->nullable();
            $table->string('google_site_verification')->nullable();
            $table->longText('head_scripts')->nullable();
            $table->longText('body_scripts')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
        Schema::table(config('permission.table_names.permissions'),fn(Blueprint $table)=>$table->dropColumn('group'));
        Schema::table('users',fn(Blueprint $table)=>$table->dropColumn(['phone','image','status','address','bio']));
    }
};
