<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Operations</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ __('Clients') }}</h2>
            </div>

            <a href="{{ route('sales.projects.index') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                {{ __('Projects') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @foreach (['client-created' => 'Client created successfully.', 'client-saved' => 'Client updated successfully.'] as $status => $message)
                @if (session('status') === $status)
                    <div class="rounded-md bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ __($message) }}</div>
                @endif
            @endforeach

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-base font-semibold text-slate-950">{{ __('Add Client') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Create billing-ready client profiles linked to sales projects.') }}</p>
                </div>

                <form method="POST" action="{{ route('sales.clients.store') }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf

                    @include('sales.clients.partials.form-fields', ['client' => null])

                    <div class="flex items-end justify-start lg:justify-end">
                        <x-primary-button class="bg-[#10243f] px-5 py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                            {{ __('Add Client') }}
                        </x-primary-button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-950">{{ __('Client List') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Showing the latest clients first.') }}</p>
                    </div>
                    <div class="flex flex-col gap-3 sm:items-end">
                        <span class="text-sm font-medium text-slate-500">{{ $clients->total() }} {{ Str::plural('client', $clients->total()) }}</span>
                        @include('sales.partials.table-search', ['search' => $search, 'placeholder' => __('Search clients...')])
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Code') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Client') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Contact') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Tax') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($clients as $client)
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $client->client_code }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $types[$client->client_type] ?? ucfirst($client->client_type) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $client->name }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $client->project?->name ?? 'No project linked' }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                        <div>{{ $client->email }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $client->phone }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($client->tax_applicable)
                                            <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Tax {{ $client->tax_percent }}%</span>
                                        @else
                                            <span class="rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">No Tax</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($client->status === \App\Models\Client::STATUS_ACTIVE)
                                            <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __('Active') }}</span>
                                        @else
                                            <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <a href="{{ route('sales.clients.edit', $client) }}" class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No clients yet. Add your first client above.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($clients->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $clients->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
