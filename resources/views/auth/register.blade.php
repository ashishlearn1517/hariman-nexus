<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('User Access') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $messages = [
                    'user-created' => 'User created successfully.',
                    'user-updated' => 'User updated successfully.',
                    'user-activated' => 'User activated successfully.',
                    'user-deactivated' => 'User deactivated successfully.',
                    'user-deleted' => 'User deleted successfully.',
                    'self-status-blocked' => 'You cannot deactivate your own account.',
                    'self-delete-blocked' => 'You cannot delete your own account.',
                    'self-role-blocked' => 'You cannot remove your own Super Admin role.',
                    'last-super-admin-blocked' => 'At least one active Super Admin must remain.',
                ];
            @endphp

            @if (session('status') && isset($messages[session('status')]))
                <div class="rounded-md {{ str_contains(session('status'), 'blocked') ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700' }} p-4 text-sm font-medium">
                    {{ __($messages[session('status')]) }}
                </div>
            @endif

            @can('create users')
            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-6 text-gray-900">
                    <div class="border-b border-slate-200 pb-4">
                        <h3 class="text-xl font-semibold text-slate-950">{{ __('Create User') }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Register a team member and assign their Hariman Nexus access role.') }}</p>
                    </div>

    <form method="POST" action="{{ route('register') }}" class="mt-5 grid gap-5 lg:grid-cols-2">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Role -->
        <div>
            <x-input-label for="role" :value="__('Role')" />
            <select id="role" name="role" required class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', \App\Models\User::ROLE_VIEWER) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end lg:col-span-2">
            <x-primary-button>
                {{ __('Create User') }}
            </x-primary-button>
        </div>
    </form>
                </div>
            </section>
            @endcan

            <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <h3 class="text-xl font-semibold text-slate-950">{{ __('Registered Users') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Review user access, status, and available account actions.') }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('User') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Role') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Status') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Created') }}</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-slate-950">{{ $user->name }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $user->email }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-700">{{ $user->getRoleNames()->first() ?: $user->role }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-md px-2.5 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                            {{ $user->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $user->created_at?->format('d M Y') }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            @can('edit users')
                                                <a href="{{ route('users.edit', $user) }}" class="rounded-md bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">{{ __('Edit') }}</a>
                                            @endcan
                                            @can('deactivate users')
                                                @if (! $user->hasRole(\App\Models\User::ROLE_SUPER_ADMIN) || auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN))
                                                    <form method="POST" action="{{ route('users.status', $user) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" @disabled($user->is(auth()->user())) class="rounded-md px-3 py-2 text-xs font-semibold {{ $user->is(auth()->user()) ? 'cursor-not-allowed bg-slate-100 text-slate-400' : ($user->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100') }}">
                                                            {{ $user->is_active ? __('Deactivate') : __('Activate') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                            @can('delete users')
                                                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Delete this user permanently?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" @disabled($user->is(auth()->user())) class="rounded-md px-3 py-2 text-xs font-semibold {{ $user->is(auth()->user()) ? 'cursor-not-allowed bg-slate-100 text-slate-400' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">{{ __('Delete') }}</button>
                                                </form>
                                            @endcan
                                            @cannot('edit users')
                                                @cannot('deactivate users')
                                                    <span class="text-xs text-slate-400">{{ __('Read only') }}</span>
                                                @endcannot
                                            @endcannot
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">{{ __('No users registered yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="border-t border-slate-200 px-5 py-4">{{ $users->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
