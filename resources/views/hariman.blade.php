<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $seoTitle = 'Hariman Technologies | ERP, Web, Mobile, AI and Digital Business Solutions';
            $seoDescription = 'Hariman Technologies provides one-stop business technology solutions including ERP systems, web applications, mobile applications, AI/ML, LLM workflows, desktop applications, automation, integrations, social media marketing, SEO, and website promotion.';
            $seoKeywords = 'Hariman Technologies, ERP solutions, web application development, mobile application development, AI ML solutions, LLM solutions, desktop applications, social media marketing, SEO, website promotion, business automation, technology company India';
            $seoUrl = route('hariman');
            $seoImage = asset('assets/images/hariman-technologies/hm-horizontal-logo.png');
            $structuredData = [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => 'Hariman Technologies',
                'url' => $seoUrl,
                'logo' => asset('assets/images/hariman-technologies/hm-app-icon.png'),
                'image' => $seoImage,
                'description' => $seoDescription,
                'email' => 'info@hariman.co.in',
                'telephone' => '+918233990399',
                'sameAs' => [],
                'makesOffer' => [
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => 'ERP Solutions',
                            'description' => 'Custom ERP and business operations systems.',
                        ],
                    ],
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => 'Web and Mobile Application Development',
                            'description' => 'Secure web applications and mobile applications for business requirements.',
                        ],
                    ],
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => 'AI, ML and LLM Solutions',
                            'description' => 'AI automation, machine learning, LLM workflows, and intelligent business tools.',
                        ],
                    ],
                    [
                        '@type' => 'Offer',
                        'itemOffered' => [
                            '@type' => 'Service',
                            'name' => 'Digital Marketing and Website Promotion',
                            'description' => 'Social media marketing, SEO, website promotion, and digital growth solutions.',
                        ],
                    ],
                ],
            ];
        @endphp

        <title>{{ $seoTitle }}</title>
        <meta name="description" content="{{ $seoDescription }}">
        <meta name="keywords" content="{{ $seoKeywords }}">
        <meta name="author" content="Hariman Technologies">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <link rel="canonical" href="{{ $seoUrl }}">
        <link rel="icon" type="image/png" href="{{ asset('assets/images/hariman-technologies/hm-app-icon.png') }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Hariman Technologies">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:url" content="{{ $seoUrl }}">
        <meta property="og:image" content="{{ $seoImage }}">
        <meta property="og:image:alt" content="Hariman Technologies technology solutions company">

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
    <body class="bg-[#f4f7fb] font-sans text-slate-950 antialiased">
        <main>
            <section class="bg-[#10243f] text-white">
                <div class="mx-auto flex min-h-[72vh] max-w-7xl flex-col px-6 py-7 sm:px-8 lg:px-10">
                    <header class="flex items-center justify-between gap-4">
                        <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3">
                            <img src="{{ asset('assets/images/hariman-technologies/hm-app-icon.png') }}" alt="Hariman Technologies" class="h-12 w-12 rounded-lg object-cover shadow-lg shadow-slate-950/25">
                            <div class="min-w-0">
                                <p class="truncate text-xl font-semibold leading-6">Hariman Technologies</p>
                                <p class="truncate text-sm text-sky-100">Technology solutions for modern businesses</p>
                            </div>
                        </a>

                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ url('/') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-[#10243f] shadow-sm hover:bg-slate-100">
                                Hariman Nexus
                            </a>
                            <button type="button" onclick="document.getElementById('forge-modal').classList.remove('hidden')" class="rounded-md border border-cyan-300/40 bg-cyan-400 px-4 py-2 text-sm font-semibold text-[#10243f] shadow-sm hover:bg-cyan-300">
                                Hariman Forge
                            </button>
                        </div>
                    </header>

                    <div class="grid flex-1 items-start gap-10 py-14 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
                        <div class="max-w-3xl">
                            <div class="mb-8 inline-flex max-w-lg rounded-md border border-white/10 bg-[#151529]/80 p-4 shadow-2xl shadow-slate-950/20">
                                <img src="{{ asset('assets/images/hariman-technologies/hm-horizontal-logo.png') }}" alt="Hariman Technologies" class="h-auto w-full rounded-sm">
                            </div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-200">One-stop technology partner</p>
                            <h1 class="mt-5 max-w-3xl text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">
                                Business software, automation, AI, and digital growth solutions under one roof.
                            </h1>
                            <p class="mt-6 max-w-2xl text-base leading-8 text-slate-200">
                                Hariman Technologies helps businesses design, build, launch, and improve technology systems that make operations faster, cleaner, and easier to manage.
                            </p>

                            <div class="mt-8 flex flex-wrap gap-3">
                                <a href="#solutions" class="rounded-md bg-cyan-400 px-5 py-3 text-sm font-semibold text-[#10243f] shadow-sm hover:bg-cyan-300">
                                    Explore Solutions
                                </a>
                                <a href="#capabilities" class="rounded-md border border-white/25 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10">
                                    Our Capabilities
                                </a>
                            </div>

                            <div class="mt-8 flex flex-wrap gap-3 text-sm">
                                <a href="mailto:info@hariman.co.in" class="rounded-md border border-white/15 bg-white/10 px-4 py-3 font-semibold text-white hover:bg-white/15">
                                    info@hariman.co.in
                                </a>
                                <a href="tel:+918233990399" class="rounded-md border border-white/15 bg-white/10 px-4 py-3 font-semibold text-white hover:bg-white/15">
                                    +91 8233990399
                                </a>
                            </div>
                        </div>

                        <div class="self-center rounded-lg border border-white/15 bg-white/10 p-4 shadow-2xl shadow-slate-950/25 backdrop-blur">
                            <div class="rounded-lg bg-white p-5 text-slate-950">
                                <div class="grid gap-5 border-b border-slate-200 pb-5 sm:grid-cols-[112px_1fr] sm:items-center">
                                    <div class="mx-auto w-28 rounded-lg bg-[#151529] p-3 shadow-inner sm:mx-0">
                                        <img src="{{ asset('assets/images/hariman-technologies/hm-mark.png') }}" alt="HM mark" class="h-auto w-full rounded-md object-cover">
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Delivery focus</p>
                                        <h2 class="mt-2 text-2xl font-semibold">From idea to working system</h2>
                                        <p class="mt-3 text-sm leading-6 text-slate-600">
                                            We combine planning, software development, integrations, design, automation, analytics, and digital marketing support so businesses do not have to coordinate scattered vendors.
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                    <div class="rounded-md bg-slate-50 p-4">
                                        <p class="text-sm font-semibold text-slate-950">ERP and operations</p>
                                        <p class="mt-2 text-sm text-slate-600">Centralized systems for daily business control.</p>
                                    </div>
                                    <div class="rounded-md bg-slate-50 p-4">
                                        <p class="text-sm font-semibold text-slate-950">Apps and platforms</p>
                                        <p class="mt-2 text-sm text-slate-600">Web, mobile, desktop, and cloud-ready tools.</p>
                                    </div>
                                    <div class="rounded-md bg-slate-50 p-4">
                                        <p class="text-sm font-semibold text-slate-950">AI and automation</p>
                                        <p class="mt-2 text-sm text-slate-600">AI/ML, LLM workflows, and process automation.</p>
                                    </div>
                                    <div class="rounded-md bg-slate-50 p-4">
                                        <p class="text-sm font-semibold text-slate-950">Digital growth</p>
                                        <p class="mt-2 text-sm text-slate-600">Marketing, website promotion, SEO, and campaigns.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div id="forge-modal" class="fixed inset-0 z-50 hidden bg-slate-950/70 px-4 py-6 backdrop-blur-sm">
                <div class="mx-auto flex min-h-full max-w-xl items-center">
                    <div class="w-full rounded-lg bg-white p-6 shadow-2xl">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-700">Coming soon</p>
                                <h2 class="mt-3 text-3xl font-semibold text-slate-950">Hariman Forge</h2>
                            </div>
                            <button type="button" onclick="document.getElementById('forge-modal').classList.add('hidden')" class="rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                                Close
                            </button>
                        </div>
                        <p class="mt-5 text-lg font-semibold text-slate-950">Spare part management application coming soon.</p>
                        <p class="mt-3 text-sm leading-7 text-slate-600">
                            Hariman Forge is planned as a focused spare parts management system for stock control, part catalogs, vendor records, purchase tracking, issue history, service usage, and operational reporting.
                        </p>
                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-md bg-slate-50 p-4">
                                <p class="font-semibold text-slate-950">Inventory clarity</p>
                                <p class="mt-2 text-sm text-slate-600">Know what parts are available, reserved, consumed, or running low.</p>
                            </div>
                            <div class="rounded-md bg-slate-50 p-4">
                                <p class="font-semibold text-slate-950">Service ready</p>
                                <p class="mt-2 text-sm text-slate-600">Connect spare part movement with jobs, teams, and business workflows.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section id="solutions" class="mx-auto max-w-7xl px-6 py-14 sm:px-8 lg:px-10">
                <div class="mb-8 max-w-3xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-700">Solutions</p>
                    <h2 class="mt-3 text-3xl font-semibold text-slate-950">Technology services for complete business requirements</h2>
                    <p class="mt-4 text-sm leading-7 text-slate-600">
                        Whether a business needs an internal ERP, customer-facing portal, automation workflow, or digital marketing engine, Hariman Technologies can plan and deliver the required solution.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">ERP Solutions</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Custom systems for finance, sales, operations, inventory, workflows, users, approvals, and reports.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">Web Applications</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Secure browser-based platforms for business teams, customers, partners, and internal departments.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">Mobile Applications</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Mobile-first applications for field staff, customers, service teams, delivery workflows, and business access on the move.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">AI, ML, and LLM Solutions</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Smart assistants, document intelligence, data models, prediction tools, automation, and LLM-powered workflows.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">Desktop Applications</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Reliable desktop tools for offline-friendly operations, local teams, device integrations, and specialized workflows.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">Social Media Marketing</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Campaign planning, brand visibility, content direction, creative support, and performance-led social growth.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">Website Promotion and SEO</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Website visibility, search improvement, landing pages, local promotion, analytics, and conversion-focused updates.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">Cloud and Integrations</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Payment gateways, email systems, APIs, cloud storage, reporting tools, CRM links, and third-party software integrations.</p>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <h3 class="font-semibold text-slate-950">Automation and Analytics</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Dashboards, notifications, scheduled jobs, business intelligence, process automation, and data-backed decisions.</p>
                    </div>
                </div>
            </section>

            <section id="capabilities" class="border-y border-slate-200 bg-white">
                <div class="mx-auto grid max-w-7xl gap-8 px-6 py-14 sm:px-8 lg:grid-cols-[0.85fr_1.15fr] lg:px-10">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-700">How we help</p>
                        <h2 class="mt-3 text-3xl font-semibold text-slate-950">A practical delivery partner for business transformation</h2>
                        <p class="mt-4 text-sm leading-7 text-slate-600">
                            We focus on useful technology: systems that reduce repeated work, improve visibility, protect business records, and give teams tools they can use every day.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="font-semibold text-slate-950">Consulting and planning</p>
                            <p class="mt-2 text-sm text-slate-600">Requirement study, workflow mapping, technical planning, and roadmap creation.</p>
                        </div>
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="font-semibold text-slate-950">Design and development</p>
                            <p class="mt-2 text-sm text-slate-600">Clean interfaces, secure development, database structure, and scalable modules.</p>
                        </div>
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="font-semibold text-slate-950">Deployment and training</p>
                            <p class="mt-2 text-sm text-slate-600">Launch support, hosting guidance, documentation, and team onboarding.</p>
                        </div>
                        <div class="rounded-md bg-slate-50 p-4">
                            <p class="font-semibold text-slate-950">Maintenance and growth</p>
                            <p class="mt-2 text-sm text-slate-600">Ongoing improvement, feature upgrades, security updates, and performance monitoring.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-6 py-14 sm:px-8 lg:px-10">
                <div class="rounded-lg bg-[#10243f] p-6 text-white shadow-xl shadow-slate-200 sm:p-8 lg:flex lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-200">Hariman Technologies</p>
                        <h2 class="mt-3 text-3xl font-semibold">One partner for software, AI, automation, and digital growth.</h2>
                        <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-200">
                            Build the system your business needs today, then keep improving it as your operations grow.
                        </p>
                        <div class="mt-5 flex flex-wrap gap-3 text-sm">
                            <a href="mailto:info@hariman.co.in" class="font-semibold text-cyan-200 hover:text-cyan-100">info@hariman.co.in</a>
                            <span class="text-slate-400">|</span>
                            <a href="tel:+918233990399" class="font-semibold text-cyan-200 hover:text-cyan-100">+91 8233990399</a>
                        </div>
                    </div>
                    <a href="{{ url('/') }}" class="mt-6 inline-flex rounded-md bg-cyan-400 px-5 py-3 text-sm font-semibold text-[#10243f] shadow-sm hover:bg-cyan-300 lg:mt-0">
                        View Hariman Nexus
                    </a>
                </div>
            </section>
        </main>
        <x-app-footer />
    </body>
</html>
