<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Operations</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Terms') }}</h2>
            </div>

            <a href="{{ route('sales.products.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Products') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ modalOpen: false, modalTitle: '', modalContent: '' }">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @foreach (['term-created' => 'Term created successfully.', 'term-saved' => 'Term updated successfully.'] as $status => $message)
                @if (session('status') === $status)
                    <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __($message) }}</div>
                @endif
            @endforeach

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Add Term') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Create reusable terms for invoices, quotations, and billing documents.') }}</p>
                </div>

                <form method="POST" action="{{ route('sales.terms.store') }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf

                    @include('sales.terms.partials.form-fields', ['term' => null])

                    <div class="flex items-end justify-start lg:justify-end">
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                            {{ __('Add Term') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Terms List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Click a term name to preview the full content.') }}</p>
                    </div>
                    <span class="text-sm font-medium text-slate-500">{{ $terms->total() }} {{ Str::plural('term', $terms->total()) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Term Name') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($terms as $term)
                                <tr>
                                    <td class="px-5 py-4">
                                        <button type="button"
                                            class="text-left font-semibold text-[#10243f] hover:underline"
                                            x-on:click="modalTitle = @js($term->name); modalContent = @js($term->content); modalOpen = true">
                                            {{ $term->name }}
                                        </button>
                                        <div class="mt-1 text-xs text-slate-500">{{ Str::length(trim($term->content)) }} {{ __('characters') }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($term->status === \App\Models\TermCondition::STATUS_ACTIVE)
                                            <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                        @else
                                            <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <a href="{{ route('sales.terms.edit', $term) }}" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No terms yet. Add your first reusable term above.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($terms->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $terms->links() }}</div>
                @endif
            </section>
        </div>

        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0">
            <div x-show="modalOpen" x-on:click="modalOpen = false" class="fixed inset-0 bg-slate-900/60"></div>
            <div x-show="modalOpen" class="relative mx-auto mt-12 max-w-2xl overflow-hidden rounded-lg bg-white shadow-xl">
                <div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <p class="text-sm font-medium text-cyan-700">{{ __('Term Content') }}</p>
                        <h3 class="mt-1 text-lg font-semibold text-slate-950" x-text="modalTitle"></h3>
                    </div>
                    <button type="button" x-on:click="modalOpen = false" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        {{ __('Close') }}
                    </button>
                </div>
                <div class="max-h-[60vh] overflow-y-auto whitespace-pre-line px-5 py-4 text-sm leading-7 text-slate-700" x-text="modalContent"></div>
            </div>
        </div>
    </div>
</x-app-layout>
