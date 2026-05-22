<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-cyan-700">Hariman Nexus</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">
                    {{ __('Dashboard') }}
                </h2>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Export
                </button>
                <button type="button" class="rounded-md bg-[#10243f] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#18365d]">
                    New Invoice
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Open invoices</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-950">128</p>
                        </div>
                        <span class="rounded-md bg-cyan-50 px-2.5 py-1 text-xs font-semibold text-cyan-700">+12%</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Across services, products, and project billings</p>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Awaiting payment</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-950">42</p>
                        </div>
                        <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Due</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Collection queue grouped by client and due date</p>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Paid this month</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-950">86</p>
                        </div>
                        <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Settled</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Receipts matched against invoice records</p>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Currencies</p>
                            <p class="mt-3 text-3xl font-semibold text-slate-950">5+</p>
                        </div>
                        <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Multi</span>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold text-slate-700">
                        <span class="rounded-md bg-slate-100 px-2.5 py-1">INR</span>
                        <span class="rounded-md bg-slate-100 px-2.5 py-1">EUR</span>
                        <span class="rounded-md bg-slate-100 px-2.5 py-1">GBP</span>
                        <span class="rounded-md bg-slate-100 px-2.5 py-1">USD</span>
                        <span class="rounded-md bg-slate-100 px-2.5 py-1">AED</span>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-950">Recent invoices</h3>
                            <p class="mt-1 text-sm text-slate-500">Latest billing activity by customer</p>
                        </div>
                        <button type="button" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            View all
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Invoice</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Customer</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Currency</th>
                                    <th class="px-5 py-3 text-left font-semibold text-slate-600">Status</th>
                                    <th class="px-5 py-3 text-right font-semibold text-slate-600">Due</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-950">INV-2026-0018</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">Acme Stores</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">USD</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Paid</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-slate-600">May 28</td>
                                </tr>
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-950">INV-2026-0017</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">Northline Works</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">GBP</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Pending</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-slate-600">Jun 04</td>
                                </tr>
                                <tr>
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-slate-950">INV-2026-0016</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">Harbor Retail</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">EUR</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <span class="rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Draft</span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-slate-600">Jun 10</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-slate-950">Collection queue</h3>
                            <p class="mt-1 text-sm text-slate-500">Priority follow-ups</p>
                        </div>
                        <span class="rounded-md bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">5 overdue</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-slate-950">Acme Stores</p>
                                <span class="text-sm font-semibold text-slate-700">USD</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">2 invoices awaiting confirmation</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-slate-950">Northline Works</p>
                                <span class="text-sm font-semibold text-slate-700">GBP</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">Payment reminder scheduled</p>
                        </div>

                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-slate-950">Harbor Retail</p>
                                <span class="text-sm font-semibold text-slate-700">EUR</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-500">Draft waiting for approval</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
