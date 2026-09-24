<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Auth/Login', [
            'googleConfigured' => filled(config('services.google.client_id')) && filled(config('services.google.client_secret')),
        ]);
    }
    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate(['username' => ['required', 'string'], 'password' => ['required', 'string'], 'remember' => ['nullable', 'boolean']]);
        $user = \App\Models\User::query()->whereRaw('LOWER(username) = ?', [strtolower(trim($data['username']))])->first();
        if (! $user || ! $user->isAktif() || ! Hash::check($data['password'], $user->password)) return back()->withErrors(['username' => 'Username atau password tidak sesuai.'])->onlyInput('username');
        Auth::login($user, (bool) ($data['remember'] ?? false)); $request->session()->regenerate(); $user->update(['last_login_at' => now()]); $audit->record('auth.login', $user, [], $request);
        return redirect()->intended(route('admin.dashboard'));
    }
    public function destroy(Request $request, AuditLogger $audit): RedirectResponse { $audit->record('auth.logout', $request->user(), [], $request); Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken(); return redirect()->route('login')->with('success', 'Anda telah keluar.'); }
}
