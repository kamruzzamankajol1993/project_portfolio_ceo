<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('requested_profile_type', 20)->default('both'); // personal | company | both
            $table->string('status', 20)->default('pending')->index(); // pending | approved | rejected | cancelled
            $table->text('message')->nullable();
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status'], 'profile_access_customer_status_idx');
        });

        // An approved request grants the customer permission to create that profile type.
        Schema::create('customer_profile_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('profile_type', 20); // personal | company
            $table->boolean('can_create')->default(true);
            $table->foreignId('granted_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'profile_type'], 'customer_profile_access_type_uq');
        });

        // Which existing profile a customer may edit, and at what access level.
        Schema::create('profile_customer_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('access_level', 20)->default('owner'); // owner | editor | viewer
            $table->boolean('is_primary')->default(false);
            $table->foreignId('granted_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['profile_id', 'customer_id'], 'profile_customer_access_uq');
            $table->index(['customer_id', 'access_level'], 'profile_customer_access_customer_idx');
        });

        Schema::create('profile_review_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_version_id')->constrained('profile_versions')->cascadeOnDelete();
            $table->string('action', 30); // submitted | approved | rejected | changes_requested | published
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
            $table->index(['profile_version_id', 'created_at'], 'profile_review_logs_version_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_review_logs');
        Schema::dropIfExists('profile_customer_access');
        Schema::dropIfExists('customer_profile_access');
        Schema::dropIfExists('profile_access_requests');
    }
};
