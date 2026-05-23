<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ActivityLogger
{
    public static function log(string $module, string $action, string $description, ?User $user = null): void
    {
        try {
            if (! Schema::hasTable('activity_logs')) {
                return;
            }

            ActivityLog::create([
                'user_id' => ($user ?? Auth::user())?->id,
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        } catch (Throwable) {
            // Audit logging should never block the business action being performed.
        }
    }
}
