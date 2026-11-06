<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSIONS = [
        'customers.manage' => 'Customer Accounts',
        'profile-requests.manage' => 'Profile Requests',
    ];

    public function up(): void
    {
        foreach (self::PERMISSIONS as $name => $group) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['group' => $group]
            );

            $adminRole = Role::query()
                ->where('name', 'Admin')
                ->where('guard_name', 'web')
                ->first();

            if ($adminRole) {
                $adminRole->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', array_keys(self::PERMISSIONS))
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
