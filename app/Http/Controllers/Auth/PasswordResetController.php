<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetController extends Controller
{
    public function requestForm(): Response { return Inertia::render('Auth/LupaPassword'); }
    public function send(Request $request): RedirectResponse { $request->validate(['email' => ['required','email']]); $status = Password::sendResetLink($request->only('email')); return back()->with($status === Password::RESET_LINK_SENT ? ['success' => 'Tautan reset password telah dikirim.'] : ['error' => 'Tautan reset password tidak dapat dikirim.']); }
    public function resetForm(string $token): Response { return Inertia::render('Auth/ResetPassword', ['token' => $token, 'email' => request('email')]); }
    public function reset(Request $request): RedirectResponse {
        $data = $request->validate(['token' => ['required'], 'email' => ['required','email'], 'password' => ['required','confirmed','min:8']]);
        $status = Password::reset($data, function ($user, $password) { $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save(); event(new PasswordReset($user)); });
        return $status === Password::PASSWORD_RESET ? redirect()->route('login')->with('success', 'Password berhasil diperbarui.') : back()->withErrors(['email' => 'Tautan reset tidak valid atau telah kedaluwarsa.']);
    }
}
