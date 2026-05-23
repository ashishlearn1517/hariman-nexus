<nav x-data="{ open: false }" class="bg-white border-b border-slate-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-10 w-10 rounded-md object-cover" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @canany(['view projects', 'view clients', 'view products', 'view services', 'view terms'])
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex h-16 items-center border-b-2 px-1 pt-1 {{ request()->routeIs('sales.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none">
                                <span>{{ __('Operations') }}</span>
                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @can('view projects')
                            <x-dropdown-link :href="route('sales.projects.index')">
                                {{ __('Projects') }}
                            </x-dropdown-link>
                            @endcan
                            @can('view clients')
                            <x-dropdown-link :href="route('sales.clients.index')">
                                {{ __('Clients') }}
                            </x-dropdown-link>
                            @endcan
                            @can('view products')
                            <x-dropdown-link :href="route('sales.products.index')">
                                {{ __('Products') }}
                            </x-dropdown-link>
                            @endcan
                            @can('view services')
                            <x-dropdown-link :href="route('sales.services.index')">
                                {{ __('Services') }}
                            </x-dropdown-link>
                            @endcan
                            @can('view terms')
                            <x-dropdown-link :href="route('sales.terms.index')">
                                {{ __('Terms') }}
                            </x-dropdown-link>
                            @endcan
                        </x-slot>
                    </x-dropdown>
                    @endcanany
                    @canany(['view quotations', 'view invoices'])
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex h-16 items-center border-b-2 px-1 pt-1 {{ request()->routeIs('transactions.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none">
                                <span>{{ __('Transactions') }}</span>
                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @can('view quotations')
                            <x-dropdown-link :href="route('transactions.quotations.index')">
                                {{ __('Quotations') }}
                            </x-dropdown-link>
                            @endcan
                            @can('view invoices')
                            <x-dropdown-link :href="route('transactions.invoices.index')">
                                {{ __('Invoices') }}
                            </x-dropdown-link>
                            @endcan
                        </x-slot>
                    </x-dropdown>
                    @endcanany
                    @can('view reports')
                    <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                        {{ __('Reports') }}
                    </x-nav-link>
                    @endcan
                    @can('manage settings')
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex h-16 items-center border-b-2 px-1 pt-1 {{ request()->routeIs('settings.*') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none">
                                <span>{{ __('Settings') }}</span>
                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('settings.company.edit')">
                                {{ __('Company Setup') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('settings.currencies.index')">
                                {{ __('Currency Settings') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('settings.taxes.index')">
                                {{ __('Tax Settings') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('settings.email.edit')">
                                {{ __('Email Settings') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('settings.numbering.edit')">
                                {{ __('Numbering') }}
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                    @endcan
                    @canany(['view users', 'create users', 'view activity logs'])
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex h-16 items-center border-b-2 px-1 pt-1 {{ request()->routeIs('register') ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} text-sm font-medium leading-5 transition duration-150 ease-in-out focus:outline-none">
                                <span>{{ __('User Access') }}</span>
                                <svg class="ms-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('register')">
                                {{ __('Add User') }}
                            </x-dropdown-link>
                            @can('view activity logs')
                            <x-dropdown-link :href="route('activity-logs.index')">
                                {{ __('Activity Logs') }}
                            </x-dropdown-link>
                            @endcan
                        </x-slot>
                    </x-dropdown>
                    @endcanany
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @can('view projects')
            <x-responsive-nav-link :href="route('sales.projects.index')" :active="request()->routeIs('sales.projects.*')">
                {{ __('Operations / Projects') }}
            </x-responsive-nav-link>
            @endcan
            @can('view clients')
            <x-responsive-nav-link :href="route('sales.clients.index')" :active="request()->routeIs('sales.clients.*')">
                {{ __('Operations / Clients') }}
            </x-responsive-nav-link>
            @endcan
            @can('view products')
            <x-responsive-nav-link :href="route('sales.products.index')" :active="request()->routeIs('sales.products.*')">
                {{ __('Operations / Products') }}
            </x-responsive-nav-link>
            @endcan
            @can('view services')
            <x-responsive-nav-link :href="route('sales.services.index')" :active="request()->routeIs('sales.services.*')">
                {{ __('Operations / Services') }}
            </x-responsive-nav-link>
            @endcan
            @can('view terms')
            <x-responsive-nav-link :href="route('sales.terms.index')" :active="request()->routeIs('sales.terms.*')">
                {{ __('Operations / Terms') }}
            </x-responsive-nav-link>
            @endcan
            @can('view quotations')
            <x-responsive-nav-link :href="route('transactions.quotations.index')" :active="request()->routeIs('transactions.quotations.*')">
                {{ __('Transactions / Quotations') }}
            </x-responsive-nav-link>
            @endcan
            @can('view invoices')
            <x-responsive-nav-link :href="route('transactions.invoices.index')" :active="request()->routeIs('transactions.invoices.*')">
                {{ __('Transactions / Invoices') }}
            </x-responsive-nav-link>
            @endcan
            @can('view reports')
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                {{ __('Reports') }}
            </x-responsive-nav-link>
            @endcan
            @can('manage settings')
            <x-responsive-nav-link :href="route('settings.company.edit')" :active="request()->routeIs('settings.company.*')">
                {{ __('Settings / Company Setup') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.currencies.index')" :active="request()->routeIs('settings.currencies.*')">
                {{ __('Settings / Currency Settings') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.taxes.index')" :active="request()->routeIs('settings.taxes.*')">
                {{ __('Settings / Tax Settings') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.email.edit')" :active="request()->routeIs('settings.email.*')">
                {{ __('Settings / Email Settings') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('settings.numbering.edit')" :active="request()->routeIs('settings.numbering.*')">
                {{ __('Settings / Numbering') }}
            </x-responsive-nav-link>
            @endcan
            @canany(['view users', 'create users'])
            <x-responsive-nav-link :href="route('register')" :active="request()->routeIs('register')">
                {{ __('User Access / Add User') }}
            </x-responsive-nav-link>
            @endcanany
            @can('view activity logs')
            <x-responsive-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">
                {{ __('User Access / Activity Logs') }}
            </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
