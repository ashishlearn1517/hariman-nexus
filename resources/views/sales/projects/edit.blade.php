<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Operations / Projects</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                    {{ __('Edit Project') }}
                </h2>
            </div>

            <a href="{{ route('sales.projects.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Back to Projects') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ $project->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Update project details or mark it inactive when it is no longer in use.') }}</p>
                </div>

                <form method="POST" action="{{ route('sales.projects.update', $project) }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="name" :value="__('Project Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $project->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="start_date" :value="__('Start Date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-2 block w-full" :value="old('start_date', $project->start_date->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="expected_delivery_time" :value="__('Expected Delivery Time')" />
                        <x-text-input id="expected_delivery_time" name="expected_delivery_time" type="text" class="mt-2 block w-full" :value="old('expected_delivery_time', $project->expected_delivery_time)" placeholder="Example: 6 weeks, Q3, or 2026-07-15" />
                        <x-input-error :messages="$errors->get('expected_delivery_time')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="awarded_to" :value="__('Project Awarded To')" />
                        <x-text-input id="awarded_to" name="awarded_to" type="text" class="mt-2 block w-full" :value="old('awarded_to', $project->awarded_to)" />
                        <x-input-error :messages="$errors->get('awarded_to')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $project->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-end justify-start gap-3 lg:justify-end">
                        <a href="{{ route('sales.projects.index') }}" class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                            {{ __('Save Project') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
