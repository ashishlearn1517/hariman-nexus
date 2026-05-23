<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-semibold text-slate-950">{{ __('Activity Logs') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Track user actions, transaction changes, payments, and login activity.') }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('activity-logs.index') }}" class="grid gap-4 lg:grid-cols-[1fr_220px_220px_auto]">
                    <div>
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input id="search" name="search" type="search" class="mt-2 block w-full" :value="$search" placeholder="Search user, description, or IP..." />
                    </div>
                    <div>
                        <x-input-label for="module" :value="__('Module')" />
                        <select id="module" name="module" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All Modules') }}</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module }}" @selected($moduleFilter === $module)>{{ ucfirst($module) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="action" :value="__('Action')" />
                        <select id="action" name="action" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ __('All Actions') }}</option>
                            @foreach ($actions as $action)
                                <option value="{{ $action }}" @selected($actionFilter === $action)>{{ ucfirst(str_replace('_', ' ', $action)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <x-primary-button class="px-5 py-3">{{ __('Filter') }}</x-primary-button>
                        <a href="{{ route('activity-logs.index') }}" class="rounded-md border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Reset') }}</a>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('When') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('User') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Module') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Action') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Description') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('IP') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $log->created_at?->format('d M Y, h:i A') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $log->user?->name ?: __('System') }}</div>
                                        @if ($log->user?->email)
                                            <div class="mt-1 text-xs text-slate-500">{{ $log->user->email }}</div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ ucfirst($log->module) }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-700">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                                    <td class="min-w-[360px] px-5 py-4 text-slate-700">{{ $log->description }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-500">{{ $log->ip_address ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No activity logs yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $logs->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
