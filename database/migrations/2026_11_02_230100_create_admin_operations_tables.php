<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 180)->nullable();
            $table->string('action', 90);
            $table->string('module', 70);
            $table->string('method', 10);
            $table->string('path', 250);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->unsignedSmallInteger('status_code')->default(200);
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['module', 'created_at']);
        });
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('path', 240)->unique();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('robots', 40)->default('index,follow');
            $table->string('canonical', 255)->nullable();
            $table->string('og_title', 255)->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image', 255)->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('seo_pages');
        Schema::dropIfExists('admin_activity_logs');
    }
};
