<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $module = (string) $request->query('module', '');
        $action = (string) $request->query('action', '');
        $search = trim((string) $request->query('search', ''));

        return view('activity-logs.index', [
            'logs' => ActivityLog::query()
                ->with('user')
                ->when($module !== '', fn ($query) => $query->where('module', $module))
                ->when($action !== '', fn ($query) => $query->where('action', $action))
                ->when($search !== '', fn ($query) => $query->where(function ($inner) use ($search): void {
                    $inner->where('description', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                }))
                ->latest('created_at')
                ->paginate(15)
                ->withQueryString(),
            'moduleFilter' => $module,
            'actionFilter' => $action,
            'search' => $search,
            'modules' => ActivityLog::query()->select('module')->distinct()->orderBy('module')->pluck('module'),
            'actions' => ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
