<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        abort_unless(filled(config('services.google.client_id')) && filled(config('services.google.client_secret')), 404);
        $mode = $request->user() ? 'link' : 'login';
        if ($mode === 'link') $request->session()->put('google_link_user_id', $request->user()->id);
        $request->session()->put('google_mode', $mode);
        return Socialite::driver('google')->scopes(['openid', 'profile', 'email'])->redirect();
    }
    public function callback(Request $request, AuditLogger $audit): RedirectResponse
    {
        try { $google = Socialite::driver('google')->user(); } catch (\Throwable) { return redirect()->route('login')->with('error', 'Login Google tidak dapat diselesaikan.'); }
        $id = trim((string) $google->getId()); if ($id === '') return redirect()->route('login')->with('error', 'Identitas Google tidak tersedia.');
        if ($request->session()->pull('google_mode') === 'link') {
            $user = User::findOrFail($request->session()->pull('google_link_user_id'));
            if (User::query()->where('google_id', $id)->whereKeyNot($user->id)->exists()) return redirect()->route('admin.profil.edit')->with('error', 'Akun Google ini sudah terhubung ke pengguna lain.');
            $user->update(['google_id' => $id, 'email_verified_at' => $user->email_verified_at ?? now()]); $audit->record('auth.google_linked', $user, [], $request);
            return redirect()->route('admin.profil.edit')->with('success', 'Akun Google berhasil dihubungkan.');
        }
        $user = User::query()->where('google_id', $id)->where('status_aktif', true)->first();
        if (! $user) return redirect()->route('login')->with('error', 'Akun Google ini belum ditautkan ke akun SPMB yang aktif.');
        Auth::login($user, true); $request->session()->regenerate(); $user->update(['last_login_at' => now()]); $audit->record('auth.google_login', $user, [], $request);
        return redirect()->route('admin.dashboard');
    }
    public function unlink(Request $request, AuditLogger $audit): RedirectResponse
    {
        abort_if(blank($request->user()->password), 422, 'Password lokal wajib tersedia sebelum akun Google dilepas.');
        $request->user()->update(['google_id' => null]); $audit->record('auth.google_unlinked', $request->user(), [], $request);
        return back()->with('success', 'Akun Google dilepas dari profil.');
    }
}
