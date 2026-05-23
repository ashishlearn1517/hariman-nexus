<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $seoTitle = 'Hariman Nexus | Invoicing, Quotations, Payments and Business Operations Platform';
            $seoDescription = 'Hariman Nexus is a secure business operations platform for quotations, invoices, payments, clients, projects, products, services, reports, audit logs, and multi-currency billing.';
            $seoKeywords = 'Hariman Nexus, invoicing software, quotation software, payment tracking, ERP software, business operations platform, multi currency invoices, invoice management, client management, business reports';
            $seoUrl = url('/');
            $seoImage = asset('assets/images/hariman-nexus-license-logo.png');
            $structuredData = [
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => 'Hariman Nexus',
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'Web',
                'description' => $seoDescription,
                'url' => $seoUrl,
                'image' => $seoImage,
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => 'Hariman Technologies',
                    'email' => 'info@hariman.co.in',
                    'telephone' => '+918233990399',
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'availability' => 'https://schema.org/InStock',
                    'price' => '0',
                    'priceCurrency' => 'INR',
                ],
            ];
        @endphp

        <title>{{ $seoTitle }}</title>
        <meta name="description" content="{{ $seoDescription }}">
        <meta name="keywords" content="{{ $seoKeywords }}">
        <meta name="author" content="Hariman Technologies">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <link rel="canonical" href="{{ $seoUrl }}">
        <link rel="icon" type="image/png" href="{{ asset('assets/images/hariman-nexus-symbol.png') }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Hariman Nexus">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:url" content="{{ $seoUrl }}">
        <meta property="og:image" content="{{ $seoImage }}">
        <meta property="og:image:alt" content="Hariman Nexus business operations platform">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
        <meta name="twitter:image" content="{{ $seoImage }}">

        <script type="application/ld+json">
            {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#f3f7fb] font-sans text-slate-950 antialiased">
        <main>
            <section class="overflow-hidden bg-[#10243f] text-white">
                <div class="mx-auto flex min-h-[78vh] max-w-7xl flex-col px-6 py-7 sm:px-8 lg:px-10">
                    <header class="flex items-center justify-between gap-4">
                        <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3">
                            <img src="{{ asset('assets/images/hariman-nexus-symbol.png') }}" alt="Hariman Nexus" class="h-12 w-12 rounded-lg object-cover shadow-lg shadow-slate-950/20">
                            <div class="min-w-0">
                                <p class="truncate text-xl font-semibold leading-6">Hariman Nexus</p>
                                <p class="truncate text-sm text-sky-100">Business Operations Platform</p>
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

                    <div class="grid flex-1 items-center gap-10 py-14 lg:grid-cols-[0.9fr_1.1fr]">
                        <div>
                            <img src="{{ asset('assets/images/hariman-nexus-wordmark.png') }}" alt="Hariman Nexus" class="mb-8 h-auto w-full max-w-xl">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-200">Invoicing, collections, and operations</p>
                            <h1 class="mt-5 max-w-3xl text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">
                                Run quotations, invoices, payments, and clients from one secure workspace.
                            </h1>
                            <p class="mt-6 max-w-2xl text-base leading-8 text-slate-200">
                                Hariman Nexus brings daily billing work into a controlled system with approvals, payment tracking, multi-currency records, reports, roles, and activity logs.
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
                                <a href="#features" class="rounded-md border border-white/25 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10">
                                    View Features
                                </a>
                            </div>

                            <div class="mt-8 grid max-w-xl grid-cols-3 gap-3 text-sm">
                                <div class="rounded-md border border-white/15 bg-white/10 p-3">
                                    <p class="font-semibold text-white">A4 PDFs</p>
                                    <p class="mt-1 text-slate-300">Print-ready</p>
                                </div>
                                <div class="rounded-md border border-white/15 bg-white/10 p-3">
                                    <p class="font-semibold text-white">Multi-currency</p>
                                    <p class="mt-1 text-slate-300">Global billing</p>
                                </div>
                                <div class="rounded-md border border-white/15 bg-white/10 p-3">
                                    <p class="font-semibold text-white">Audit logs</p>
                                    <p class="mt-1 text-slate-300">Accountable</p>
                                </div>
                            </div>
                        </div>

                        <div class="relative">
                            <div class="rounded-lg border border-white/15 bg-white/10 p-4 shadow-2xl shadow-slate-950/25 backdrop-blur">
                                <div class="overflow-hidden rounded-lg bg-white text-slate-950">
                                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Live workspace</p>
                                            <p class="mt-1 text-xl font-semibold">Invoice control desk</p>
                                        </div>
                                        <span class="rounded-md bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">Operational</span>
                                    </div>

                                    <div class="grid gap-4 p-5 xl:grid-cols-[0.9fr_1.1fr]">
                                        <div class="space-y-4">
                                            <div class="rounded-md border border-slate-200 p-4">
                                                <div class="flex items-center justify-between">
                                                    <p class="text-sm font-semibold text-slate-600">Total revenue</p>
                                                    <span class="rounded bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">This month</span>
                                                </div>
                                                <p class="mt-3 text-3xl font-bold">248,500.00</p>
                                                <div class="mt-4 h-2 rounded-full bg-slate-100">
                                                    <div class="h-2 w-3/4 rounded-full bg-cyan-500"></div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="rounded-md bg-rose-50 p-4">
                                                    <p class="text-sm text-rose-700">Overdue</p>
                                                    <p class="mt-2 text-2xl font-semibold">5</p>
                                                </div>
                                                <div class="rounded-md bg-amber-50 p-4">
                                                    <p class="text-sm text-amber-700">Due soon</p>
                                                    <p class="mt-2 text-2xl font-semibold">9</p>
                                                </div>
                                                <div class="rounded-md bg-emerald-50 p-4">
                                                    <p class="text-sm text-emerald-700">Paid</p>
                                                    <p class="mt-2 text-2xl font-semibold">86</p>
                                                </div>
                                                <div class="rounded-md bg-indigo-50 p-4">
                                                    <p class="text-sm text-indigo-700">Quotes</p>
                                                    <p class="mt-2 text-2xl font-semibold">32</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rounded-md border border-slate-200">
                                            <div class="grid grid-cols-[1fr_auto_auto] gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-widest text-slate-500">
                                                <span>Invoice</span>
                                                <span>Status</span>
                                                <span>Total</span>
                                            </div>
                                            <div class="divide-y divide-slate-200 text-sm">
                                                <div class="grid grid-cols-[1fr_auto_auto] items-center gap-3 px-4 py-4">
                                                    <div>
                                                        <p class="font-semibold">INV-2026-0018</p>
                                                        <p class="mt-1 text-slate-500">Acme Stores</p>
                                                    </div>
                                                    <span class="rounded bg-emerald-50 px-2 py-1 font-semibold text-emerald-700">Paid</span>
                                                    <span class="font-semibold">12,400</span>
                                                </div>
                                                <div class="grid grid-cols-[1fr_auto_auto] items-center gap-3 px-4 py-4">
                                                    <div>
                                                        <p class="font-semibold">INV-2026-0019</p>
                                                        <p class="mt-1 text-slate-500">Northline Works</p>
                                                    </div>
                                                    <span class="rounded bg-amber-50 px-2 py-1 font-semibold text-amber-700">Partial</span>
                                                    <span class="font-semibold">8,900</span>
                                                </div>
                                                <div class="grid grid-cols-[1fr_auto_auto] items-center gap-3 px-4 py-4">
                                                    <div>
                                                        <p class="font-semibold">QUO-2026-0021</p>
                                                        <p class="mt-1 text-slate-500">Harbor Retail</p>
                                                    </div>
                                                    <span class="rounded bg-blue-50 px-2 py-1 font-semibold text-blue-700">Approved</span>
                                                    <span class="font-semibold">34,200</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="mx-auto max-w-7xl px-6 py-14 sm:px-8 lg:px-10">
                <div class="mb-8 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-700">System features</p>
                        <h2 class="mt-3 text-3xl font-semibold text-slate-950">Built for professional invoice operations</h2>
                    </div>
                    <p class="max-w-xl text-sm leading-6 text-slate-600">
                        From client onboarding to payment follow-up, every key billing step has a place, a status, and an audit trail.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-cyan-700">01</p>
                        <h3 class="mt-3 font-semibold text-slate-950">Quotations to invoices</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Create quotations, approve them, convert them to invoices, and keep converted records controlled.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-emerald-700">02</p>
                        <h3 class="mt-3 font-semibold text-slate-950">Payments and receipts</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Record partial payments, upload receipts, and let balances move automatically.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-amber-700">03</p>
                        <h3 class="mt-3 font-semibold text-slate-950">Reminder workflow</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Send reminder and overdue emails from invoice records with controlled timing.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-indigo-700">04</p>
                        <h3 class="mt-3 font-semibold text-slate-950">Reports and exports</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Review revenue, outstanding balances, collections, statements, and conversion with PDF, CSV, and Excel exports.</p>
                    </div>
                </div>
            </section>

            <section class="border-y border-slate-200 bg-white">
                <div class="mx-auto grid max-w-7xl gap-8 px-6 py-12 sm:px-8 lg:grid-cols-[0.9fr_1.1fr] lg:px-10">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-700">Control layer</p>
                        <h2 class="mt-3 text-3xl font-semibold text-slate-950">Ready for teams, roles, and audit-safe financial work</h2>
                        <p class="mt-4 text-sm leading-7 text-slate-600">
                            Hariman Nexus is structured for secure multi-user access, role-based permissions, audit logs, soft-deleted financial records, and stored business files.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="font-semibold text-slate-950">Role-based access</p>
                            <p class="mt-2 text-sm text-slate-600">Super Admin, Admin, Accountant, Operations Staff, and Viewer permissions.</p>
                        </div>
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="font-semibold text-slate-950">Activity logs</p>
                            <p class="mt-2 text-sm text-slate-600">Track logins, creates, edits, payments, status changes, and archive actions.</p>
                        </div>
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="font-semibold text-slate-950">Soft archives</p>
                            <p class="mt-2 text-sm text-slate-600">Financial records are archived instead of permanently removed.</p>
                        </div>
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="font-semibold text-slate-950">Company setup</p>
                            <p class="mt-2 text-sm text-slate-600">Logo, tax, email, currency, numbering, and payment details in settings.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-6 py-14 sm:px-8 lg:px-10">
                <div class="rounded-lg bg-[#10243f] p-6 text-white shadow-xl shadow-slate-200 sm:p-8 lg:flex lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-200">Know more</p>
                        <h2 class="mt-3 text-3xl font-semibold">Connect for Hariman Nexus and business technology solutions.</h2>
                        <div class="mt-5 flex flex-wrap gap-3 text-sm">
                            <span class="rounded-md border border-white/15 bg-white/10 px-4 py-3 font-semibold text-white">Ashish</span>
                            <a href="mailto:info@hariman.co.in" class="rounded-md border border-white/15 bg-white/10 px-4 py-3 font-semibold text-white hover:bg-white/15">info@hariman.co.in</a>
                            <a href="tel:+918233990399" class="rounded-md border border-white/15 bg-white/10 px-4 py-3 font-semibold text-white hover:bg-white/15">+91 8233990399</a>
                        </div>
                    </div>
                    <a href="{{ route('hariman') }}" class="mt-6 inline-flex rounded-md bg-cyan-400 px-5 py-3 text-sm font-semibold text-[#10243f] shadow-sm hover:bg-cyan-300 lg:mt-0">
                        Hariman Technologies
                    </a>
                </div>
            </section>
        </main>
        <x-app-footer />
    </body>
</html>
