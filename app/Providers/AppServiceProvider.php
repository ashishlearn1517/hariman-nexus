<?php

namespace App\Providers;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->hasRole(User::ROLE_SUPER_ADMIN)) {
                return true;
            }

            if ($user->roles()->exists()) {
                return null;
            }

            $legacyRole = User::legacyRoleMap()[$user->role] ?? $user->role;
            $permissions = AccessControl::rolePermissions()[$legacyRole] ?? [];

            return in_array($ability, $permissions, true) ? true : null;
        });
    }
}
