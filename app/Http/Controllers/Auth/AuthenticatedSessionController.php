<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (! Auth::user()?->is_active) {
            ActivityLogger::log('auth', 'login_blocked', Auth::user()->name.' attempted login with an inactive account.');

            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => __('This user account is inactive. Please contact your administrator.'),
            ]);
        }

        $request->session()->regenerate();

        ActivityLogger::log('auth', 'login', Auth::user()->name.' logged in.');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()) {
            ActivityLogger::log('auth', 'logout', $request->user()->name.' logged out.');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
