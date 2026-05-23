<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Finance / Expense Categories</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Edit Category') }}</h2>
            </div>
            <a href="{{ route('finance.expense-categories.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Back to Categories') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ $category->category_code }} - {{ $category->category_name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Update the category name, description, or active status.') }}</p>
                </div>

                <form method="POST" action="{{ route('finance.expense-categories.update', $category) }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf
                    @method('PATCH')
                    @include('finance.expense-categories.partials.form-fields', ['category' => $category])
                    <div class="flex items-end justify-start gap-3 lg:justify-end">
                        <a href="{{ route('finance.expense-categories.index') }}" class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">{{ __('Save Category') }}</x-primary-button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
