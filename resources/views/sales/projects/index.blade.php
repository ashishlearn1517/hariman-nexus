<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Operations</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                    {{ __('Projects') }}
                </h2>
            </div>

            <a href="{{ route('dashboard') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Back to Dashboard') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'project-created')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                    {{ __('Project created successfully.') }}
                </div>
            @endif

            @if (session('status') === 'project-saved')
                <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
                    {{ __('Project updated successfully.') }}
                </div>
            @endif

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Add Project') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Create project records for sales, billing, and delivery tracking.') }}</p>
                </div>

                <form method="POST" action="{{ route('sales.projects.store') }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Project Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="start_date" :value="__('Start Date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-2 block w-full" :value="old('start_date')" required />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="expected_delivery_time" :value="__('Expected Delivery Time')" />
                        <x-text-input id="expected_delivery_time" name="expected_delivery_time" type="text" class="mt-2 block w-full" :value="old('expected_delivery_time')" placeholder="Example: 6 weeks, Q3, or 2026-07-15" />
                        <x-input-error :messages="$errors->get('expected_delivery_time')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="awarded_to" :value="__('Project Awarded To')" />
                        <x-text-input id="awarded_to" name="awarded_to" type="text" class="mt-2 block w-full" :value="old('awarded_to')" />
                        <x-input-error :messages="$errors->get('awarded_to')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', 'active') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-end justify-start lg:justify-end">
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                            {{ __('Add Project') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Project List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Showing the latest projects first.') }}</p>
                    </div>
                    <span class="text-sm font-medium text-slate-500">{{ $projects->total() }} {{ Str::plural('project', $projects->total()) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Project Name') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Start Date') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Expected Delivery') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Awarded To') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($projects as $project)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-950">{{ $project->name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $project->start_date->format('M d, Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $project->expected_delivery_time ?: 'Not set' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $project->awarded_to ?: 'Not assigned' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($project->status === \App\Models\Project::STATUS_ACTIVE)
                                            <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                        @else
                                            <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <a href="{{ route('sales.projects.edit', $project) }}" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                            {{ __('Edit') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                        {{ __('No projects yet. Add your first project above.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($projects->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">
                        {{ $projects->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
