<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit User') }}</h2>
            <a href="{{ route('register') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">{{ __('Back to User Access') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @php
                $messages = [
                    'self-role-blocked' => 'You cannot remove your own Super Admin role.',
                    'last-super-admin-blocked' => 'At least one active Super Admin must remain.',
                ];
            @endphp

            @if (session('status') && isset($messages[session('status')]))
                <div class="mb-6 rounded-md bg-amber-50 p-4 text-sm font-medium text-amber-700">
                    {{ __($messages[session('status')]) }}
                </div>
            @endif

            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="border-b border-slate-200 pb-4">
                    <h3 class="text-xl font-semibold text-slate-950">{{ $user->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $user->email }}</p>
                </div>

                <form method="POST" action="{{ route('users.update', $user) }}" class="mt-5 grid gap-5 lg:grid-cols-2">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $user->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <select id="role" name="role" required class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" @selected(old('role', $user->getRoleNames()->first() ?: $user->role) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="is_active" :value="__('Status')" />
                        <select id="is_active" name="is_active" required class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="1" @selected(old('is_active', (int) $user->is_active) == 1)>{{ __('Active') }}</option>
                            <option value="0" @selected(old('is_active', (int) $user->is_active) == 0)>{{ __('Inactive') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('New Password')" />
                        <x-text-input id="password" name="password" type="password" class="mt-2 block w-full" autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full" autocomplete="new-password" />
                    </div>

                    <div class="flex justify-end gap-3 lg:col-span-2">
                        <a href="{{ route('register') }}" class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Save User') }}</x-primary-button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
