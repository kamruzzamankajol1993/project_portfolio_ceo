<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('contact_messages')) {
            Schema::create('contact_messages', function (Blueprint $table) {
                $table->id();
                $table->string('name', 180);
                $table->string('email', 191)->index();
                $table->string('phone', 30)->nullable();
                $table->string('subject', 180);
                $table->text('message');
                $table->string('status', 20)->default('unread')->index();
                $table->timestamp('read_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamps();
                $table->index(['status', 'created_at']);
            });
        }

        if (Schema::hasTable('permissions')) {
            $permissionId = DB::table('permissions')
                ->where('name', 'contact-messages.manage')
                ->where('guard_name', 'web')
                ->value('id');

            if (!$permissionId) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => 'contact-messages.manage',
                    'guard_name' => 'web',
                    'group' => 'Website Messages',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
                $roleIds = DB::table('roles')
                    ->where('guard_name', 'web')
                    ->whereIn('name', ['Admin', 'Super Admin'])
                    ->pluck('id');

                foreach ($roleIds as $roleId) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            $permissionId = DB::table('permissions')
                ->where('name', 'contact-messages.manage')
                ->where('guard_name', 'web')
                ->value('id');

            if ($permissionId && Schema::hasTable('role_has_permissions')) {
                DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            }

            DB::table('permissions')->where('name', 'contact-messages.manage')->where('guard_name', 'web')->delete();
        }

        Schema::dropIfExists('contact_messages');
    }
};
