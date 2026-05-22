<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Settings</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Tax Settings') }}</h2>
            </div>

            <a href="{{ route('settings.currencies.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Currency Settings') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @foreach (['tax-created' => 'Tax setting created successfully.', 'tax-saved' => 'Tax setting updated successfully.'] as $status => $message)
                @if (session('status') === $status)
                    <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __($message) }}</div>
                @endif
            @endforeach

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Add Tax') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Create reusable tax rates for clients, quotations, invoices, and future billing workflows.') }}</p>
                </div>

                <form method="POST" action="{{ route('settings.taxes.store') }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf

                    @include('settings.taxes.partials.form-fields', ['tax' => null])

                    <div class="flex items-end justify-start lg:justify-end">
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                            {{ __('Add Tax') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Tax List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Default tax appears first, then configured tax rates.') }}</p>
                    </div>
                    <span class="text-sm font-medium text-slate-500">{{ $taxes->total() }} {{ Str::plural('tax', $taxes->total()) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Tax') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Rate') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($taxes as $tax)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-slate-950">{{ $tax->name }}</span>
                                            @if ($tax->is_default)
                                                <span class="rounded-md bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-700">{{ __('Default') }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-1 max-w-xl truncate text-xs text-slate-500">{{ $tax->description ?: __('No description') }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-950">{{ number_format((float) $tax->rate_percent, 4) }}%</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($tax->status === \App\Models\TaxSetting::STATUS_ACTIVE)
                                            <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                        @else
                                            <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <a href="{{ route('settings.taxes.edit', $tax) }}" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No tax settings yet. Add your first tax rate above.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($taxes->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $taxes->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
