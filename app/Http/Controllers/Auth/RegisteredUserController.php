<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\AccessControl;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'roles' => $this->rolesForActor(request()->user()),
            'users' => User::query()
                ->with('roles')
                ->latest()
                ->paginate(10),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'string', 'in:'.implode(',', array_keys($this->rolesForActor($request->user())))],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => true,
            'password' => Hash::make($request->password),
        ]);

        $rolePermissions = AccessControl::rolePermissions()[$request->role] ?? [];

        foreach ($rolePermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $role = Role::findOrCreate($request->role);
        $role->syncPermissions($rolePermissions);

        $user->assignRole($role);
        ActivityLogger::log('users', 'created', $request->user()->name.' created user '.$user->email.' as '.$request->role.'.');

        event(new Registered($user));

        return redirect(route('register', absolute: false))->with('status', 'user-created');
    }

    public function edit(User $user): View
    {
        return view('auth.users.edit', [
            'user' => $user,
            'roles' => User::roleOptions(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureEditable($request->user(), $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'role' => ['required', 'string', 'in:'.implode(',', array_keys(User::roleOptions()))],
            'is_active' => ['required', 'boolean'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($user->is($request->user()) && $validated['role'] !== User::ROLE_SUPER_ADMIN) {
            return back()->with('status', 'self-role-blocked');
        }

        if ($user->hasRole(User::ROLE_SUPER_ADMIN) && ! (bool) $validated['is_active'] && $this->isLastSuperAdmin($user)) {
            return back()->with('status', 'last-super-admin-blocked');
        }

        $user->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => (bool) $validated['is_active'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $this->syncUserRole($user, $validated['role']);
        ActivityLogger::log('users', 'updated', $request->user()->name.' updated user '.$user->email.'.');

        return redirect(route('register', absolute: false))->with('status', 'user-updated');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $this->ensureStatusEditable($request->user(), $user);

        if ($user->is($request->user())) {
            return back()->with('status', 'self-status-blocked');
        }

        if ($user->hasRole(User::ROLE_SUPER_ADMIN) && $user->is_active && $this->isLastSuperAdmin($user)) {
            return back()->with('status', 'last-super-admin-blocked');
        }

        $user->forceFill(['is_active' => ! $user->is_active])->save();
        ActivityLogger::log(
            'users',
            $user->is_active ? 'activated' : 'deactivated',
            $request->user()->name.' '.($user->is_active ? 'activated ' : 'deactivated ').$user->email.'.',
        );

        return back()->with('status', $user->is_active ? 'user-activated' : 'user-deactivated');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureEditable($request->user(), $user);

        if ($user->is($request->user())) {
            return back()->with('status', 'self-delete-blocked');
        }

        if ($user->hasRole(User::ROLE_SUPER_ADMIN) && $this->isLastSuperAdmin($user)) {
            return back()->with('status', 'last-super-admin-blocked');
        }

        $email = $user->email;
        $user->delete();
        ActivityLogger::log('users', 'deleted', $request->user()->name.' deleted user '.$email.'.');

        return back()->with('status', 'user-deleted');
    }

    private function syncUserRole(User $user, string $roleName): void
    {
        $rolePermissions = AccessControl::rolePermissions()[$roleName] ?? [];

        foreach ($rolePermissions as $permission) {
            Permission::findOrCreate($permission);
        }

        $role = Role::findOrCreate($roleName);
        $role->syncPermissions($rolePermissions);
        $user->syncRoles($role);
    }

    private function ensureEditable(User $actor, User $target): void
    {
        if (! $actor->hasRole(User::ROLE_SUPER_ADMIN)) {
            abort(403);
        }
    }

    private function ensureStatusEditable(User $actor, User $target): void
    {
        if ($target->hasRole(User::ROLE_SUPER_ADMIN) && ! $actor->hasRole(User::ROLE_SUPER_ADMIN)) {
            abort(403);
        }
    }

    private function isLastSuperAdmin(User $user): bool
    {
        return $user->hasRole(User::ROLE_SUPER_ADMIN)
            && User::role(User::ROLE_SUPER_ADMIN)->where('is_active', true)->count() <= 1;
    }

    /**
     * @return array<string, string>
     */
    private function rolesForActor(User $actor): array
    {
        $roles = User::roleOptions();

        if (! $actor->hasRole(User::ROLE_SUPER_ADMIN)) {
            unset($roles[User::ROLE_SUPER_ADMIN]);
        }

        return $roles;
    }
}
