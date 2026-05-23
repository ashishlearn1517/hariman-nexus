<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (AccessControl::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach (AccessControl::rolePermissions() as $roleName => $permissions) {
            Role::findOrCreate($roleName)->syncPermissions($permissions);
        }

        $users = User::query()->oldest('id')->get();

        foreach ($users as $index => $user) {
            if ($user->roles()->exists()) {
                continue;
            }

            $roleName = $index === 0
                ? User::ROLE_SUPER_ADMIN
                : User::legacyRoleMap()[$user->role] ?? User::ROLE_VIEWER;

            $user->assignRole($roleName);

            if (Schema::hasColumn('users', 'role')) {
                $user->forceFill(['role' => $roleName])->save();
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
