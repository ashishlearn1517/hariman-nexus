<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Hariman Nexus') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-950 antialiased">
        <div class="flex min-h-screen flex-col bg-[#f5f7fb]">
            <div class="grid flex-1 lg:grid-cols-[1fr_520px]">
                <section class="hidden bg-[#10243f] px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between">
                    <a href="/" class="inline-flex items-center gap-3">
                        <x-application-logo class="h-12 w-12 rounded-lg object-cover" />
                        <div>
                            <div class="text-xl font-semibold leading-6">Hariman Nexus</div>
                            <div class="text-sm text-sky-100">Business Operations Platform</div>
                        </div>
                    </a>

                    <div class="max-w-xl">
                        <img src="{{ asset('assets/images/hariman-nexus-wordmark.png') }}" alt="Hariman Nexus" class="h-auto w-full max-w-md">
                        <p class="mt-8 text-sm font-medium uppercase tracking-[0.18em] text-cyan-200">Hariman Nexus</p>
                        <h1 class="mt-5 text-4xl font-semibold leading-tight">
                            Keep invoices, customers, and payments in one clear workspace.
                        </h1>
                        <p class="mt-5 max-w-lg text-base leading-7 text-slate-200">
                            Sign in to manage receivables, prepare customer invoices, and keep your finance team aligned.
                        </p>
                    </div>

                    <div class="max-w-xl rounded-lg border border-white/15 bg-white/10 p-5 shadow-2xl shadow-slate-950/20 backdrop-blur">
                        <div class="flex items-start justify-between border-b border-white/15 pb-4">
                            <div>
                                <p class="text-sm text-slate-300">Current invoice</p>
                                <p class="mt-1 text-2xl font-semibold">INV-2026-0018</p>
                            </div>
                            <span class="rounded-md bg-emerald-400/15 px-3 py-1 text-sm font-medium text-emerald-100">Paid</span>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-3 text-sm">
                            <div class="rounded-md bg-white/10 p-3">
                                <p class="text-slate-300">Client</p>
                                <p class="mt-1 font-semibold">Hariman Stores</p>
                            </div>
                            <div class="rounded-md bg-white/10 p-3">
                                <p class="text-slate-300">Due</p>
                                <p class="mt-1 font-semibold">May 28</p>
                            </div>
                            <div class="rounded-md bg-white/10 p-3">
                                <p class="text-slate-300">Currency</p>
                                <p class="mt-1 font-semibold">Multi</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold">
                            <span class="rounded-md bg-white/10 px-3 py-1 text-slate-100">INR</span>
                            <span class="rounded-md bg-white/10 px-3 py-1 text-slate-100">EUR</span>
                            <span class="rounded-md bg-white/10 px-3 py-1 text-slate-100">GBP</span>
                            <span class="rounded-md bg-white/10 px-3 py-1 text-slate-100">USD</span>
                            <span class="rounded-md bg-white/10 px-3 py-1 text-slate-100">AED</span>
                            <span class="rounded-md bg-white/10 px-3 py-1 text-slate-100">Any Currency</span>
                        </div>
                    </div>
                </section>

                <main class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
                    <div class="w-full max-w-md">
                        <div class="mb-8 flex items-center gap-3 lg:hidden">
                            <x-application-logo class="h-12 w-12 rounded-lg object-cover" />
                            <div>
                                <div class="text-xl font-semibold leading-6">Hariman Nexus</div>
                                <div class="text-sm text-slate-500">Business Operations Platform</div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>

            <x-app-footer />
        </div>
    </body>
</html>
