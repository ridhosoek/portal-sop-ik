<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);
        unset($credentials['remember']);

        if (! Auth::attempt($credentials, $remember)) {
            AuditLogger::record('login.failed', null, [], ['email' => $request->string('email')->toString()]);

            throw ValidationException::withMessages([
                'email' => __('Email atau password tidak sesuai.'),
            ]);
        }

        $user = $request->user();

        if ($user->status !== 'active') {
            Auth::logout();
            AuditLogger::record('login.blocked', $user, [], ['status' => $user->status], $user);

            throw ValidationException::withMessages([
                'email' => __('Akun tidak aktif. Hubungi Super Admin.'),
            ]);
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();

        AuditLogger::record('login.success', $user, [], ['email' => $user->email], $user);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        AuditLogger::record('logout', $request->user());

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
