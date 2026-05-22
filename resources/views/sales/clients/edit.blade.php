<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Operations / Clients</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Edit Client') }}</h2>
            </div>

            <a href="{{ route('sales.clients.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Back to Clients') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ $client->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Update client details or mark the client inactive when no longer in use.') }}</p>
                </div>

                <form method="POST" action="{{ route('sales.clients.update', $client) }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf
                    @method('PATCH')

                    @include('sales.clients.partials.form-fields', ['client' => $client])

                    <div class="flex items-end justify-start gap-3 lg:justify-end">
                        <a href="{{ route('sales.clients.index') }}" class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                            {{ __('Save Client') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
