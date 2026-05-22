<div class="grid gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4 sm:grid-cols-3">
    <div><span class="block text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Subtotal') }}</span><strong class="mt-1 block text-lg text-slate-950" x-text="money(subtotal)"></strong></div>
    <div><span class="block text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Tax') }}</span><strong class="mt-1 block text-lg text-slate-950" x-text="money(taxAmount)"></strong></div>
    <div><span class="block text-xs font-semibold uppercase tracking-widest text-slate-500">{{ __('Total') }}</span><strong class="mt-1 block text-lg text-slate-950" x-text="money(total)"></strong></div>
</div>
