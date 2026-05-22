<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Operations</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Products') }}</h2>
            </div>

            <a href="{{ route('sales.services.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Services') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @foreach (['product-created' => 'Product created successfully.', 'product-saved' => 'Product updated successfully.'] as $status => $message)
                @if (session('status') === $status)
                    <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __($message) }}</div>
                @endif
            @endforeach

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Add Product') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Create product records with automatic codes and ready-to-bill pricing.') }}</p>
                </div>

                <form method="POST" action="{{ route('sales.products.store') }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf

                    @include('sales.products.partials.form-fields', ['product' => null])

                    <div class="flex items-end justify-start lg:justify-end">
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                            {{ __('Add Product') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Product List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Showing the latest products first.') }}</p>
                    </div>
                    <span class="text-sm font-medium text-slate-500">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Code') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Product') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Unit Price') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($products as $product)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-950">{{ $product->product_code }}</td>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $product->name }}</div>
                                        <div class="mt-1 max-w-xl truncate text-xs text-slate-500">{{ $product->description ?: 'No description' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ number_format((float) $product->unit_price, 2) }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ __('Default unit price') }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($product->status === \App\Models\Product::STATUS_ACTIVE)
                                            <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                        @else
                                            <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <a href="{{ route('sales.products.edit', $product) }}" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No products yet. Add your first product above.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($products->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $products->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
