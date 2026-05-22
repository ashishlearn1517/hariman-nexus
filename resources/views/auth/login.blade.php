<x-guest-layout>
    <div class="mb-8">
        <p class="text-sm font-medium text-cyan-700">Secure sign in</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-950">Welcome back</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">
            Access the Hariman Nexus workspace with your account.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@company.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center text-sm text-slate-600">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-cyan-700 shadow-sm focus:ring-cyan-700" name="remember">
                <span class="ms-2">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-medium text-cyan-700 hover:text-cyan-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-700" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <div>
            <x-primary-button class="w-full justify-center bg-[#10243f] py-3 hover:bg-[#18365d] focus:bg-[#18365d] active:bg-[#0b1b30] focus:ring-cyan-700">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
