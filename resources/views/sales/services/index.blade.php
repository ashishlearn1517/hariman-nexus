<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Operations</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Services') }}</h2>
            </div>

            <a href="{{ route('sales.clients.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Clients') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @foreach (['service-created' => 'Service created successfully.', 'service-saved' => 'Service updated successfully.'] as $status => $message)
                @if (session('status') === $status)
                    <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __($message) }}</div>
                @endif
            @endforeach

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Add Service') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Create billable service records with standard default rates for invoice rows.') }}</p>
                </div>

                <form method="POST" action="{{ route('sales.services.store') }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf

                    @include('sales.services.partials.form-fields', ['service' => null])

                    <div class="flex items-end justify-start lg:justify-end">
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                            {{ __('Add Service') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Service List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Showing the latest services first.') }}</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:items-end">
                        <span class="text-sm font-medium text-slate-500">{{ $services->total() }} {{ Str::plural('service', $services->total()) }}</span>
                        @include('sales.partials.table-search', ['search' => $search, 'placeholder' => __('Search services...')])
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Service') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Default Rate') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($services as $service)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $service->long_name }}</div>
                                        <div class="mt-1 inline-flex rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $service->short_name }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ number_format((float) $service->default_rate, 2) }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ __('Default billing rate') }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($service->status === \App\Models\Service::STATUS_ACTIVE)
                                            <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                        @else
                                            <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <a href="{{ route('sales.services.edit', $service) }}" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No services yet. Add your first service above.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($services->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $services->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
