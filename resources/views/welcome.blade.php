<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Hariman Nexus') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 font-sans text-slate-950 antialiased">
        <main>
            <section class="bg-[#10243f] text-white">
                <div class="mx-auto flex min-h-[72vh] max-w-7xl flex-col px-6 py-8 sm:px-8 lg:px-10">
                    <header class="flex items-center justify-between gap-4">
                        <a href="{{ url('/') }}" class="flex items-center gap-3">
                            <img src="{{ asset('assets/images/hariman-nexus-symbol.png') }}" alt="Hariman Nexus" class="h-12 w-12 rounded-lg object-cover">
                            <div>
                                <p class="text-xl font-semibold leading-6">Hariman Nexus</p>
                                <p class="text-sm text-sky-100">Business Operations Platform</p>
                            </div>
                        </a>

                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-[#10243f] shadow-sm hover:bg-slate-100">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-[#10243f] shadow-sm hover:bg-slate-100">
                                Login
                            </a>
                        @endauth
                    </header>

                    <div class="grid flex-1 items-center gap-10 py-14 lg:grid-cols-[0.95fr_1.05fr]">
                        <div>
                            <img src="{{ asset('assets/images/hariman-nexus-wordmark.png') }}" alt="Hariman Nexus" class="mb-8 h-auto w-full max-w-xl">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-200">Hariman Nexus</p>
                            <h1 class="mt-5 max-w-3xl text-4xl font-bold leading-tight sm:text-5xl">
                                One workspace for sales, invoices, clients, and business records.
                            </h1>
                            <p class="mt-6 max-w-2xl text-base leading-8 text-slate-200">
                                Manage projects, clients, products, services, invoices, payments, and operational records from a secure business control system.
                            </p>

                            <div class="mt-8 flex flex-wrap gap-3">
                                @auth
                                    <a href="{{ route('dashboard') }}" class="rounded-md bg-cyan-400 px-5 py-3 text-sm font-semibold text-[#10243f] shadow-sm hover:bg-cyan-300">
                                        Open Dashboard
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="rounded-md bg-cyan-400 px-5 py-3 text-sm font-semibold text-[#10243f] shadow-sm hover:bg-cyan-300">
                                        Login to Workspace
                                    </a>
                                @endauth
                                <a href="#features" class="rounded-md border border-white/20 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10">
                                    View Features
                                </a>
                            </div>
                        </div>

                        <div class="rounded-lg border border-white/15 bg-white/10 p-5 shadow-2xl shadow-slate-950/20 backdrop-blur">
                            <div class="rounded-lg bg-white p-5 text-slate-950">
                                <div class="flex items-start justify-between border-b border-slate-200 pb-4">
                                    <div>
                                        <p class="text-sm font-medium text-slate-500">Operations snapshot</p>
                                        <p class="mt-1 text-2xl font-semibold">Sales pipeline</p>
                                    </div>
                                    <span class="rounded-md bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">Active</span>
                                </div>

                                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-md bg-slate-100 p-4">
                                        <p class="text-sm text-slate-500">Projects</p>
                                        <p class="mt-2 text-2xl font-semibold">Track</p>
                                    </div>
                                    <div class="rounded-md bg-slate-100 p-4">
                                        <p class="text-sm text-slate-500">Invoices</p>
                                        <p class="mt-2 text-2xl font-semibold">Bill</p>
                                    </div>
                                    <div class="rounded-md bg-slate-100 p-4">
                                        <p class="text-sm text-slate-500">Currencies</p>
                                        <p class="mt-2 text-2xl font-semibold">Multi</p>
                                    </div>
                                    <div class="rounded-md bg-slate-100 p-4">
                                        <p class="text-sm text-slate-500">Collections</p>
                                        <p class="mt-2 text-2xl font-semibold">Follow up</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="mx-auto max-w-7xl px-6 py-12 sm:px-8 lg:px-10">
                <div class="mb-8 max-w-3xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-700">Features</p>
                    <h2 class="mt-3 text-3xl font-semibold text-slate-950">Built for daily business operations</h2>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">Sales Records</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Manage projects, clients, products, and services from structured sales pages.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">Invoice Control</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Prepare invoices, monitor status, and keep billing activity organized.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">Multi-Currency</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Support business records across currencies without locking the system to one market.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">User Roles</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Create internal users with role assignments and keep registration private.</p>
                    </div>
                </div>
            </section>
        </main>
        <x-app-footer />
    </body>
</html>
