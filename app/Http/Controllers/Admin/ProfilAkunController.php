<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ProfilAkunController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Admin/ProfilAkun/Edit', [
            'akun' => $request->user()->only(['name', 'username', 'email', 'google_id']),
        ]);
    }

    public function update(Request $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
            'password_saat_ini' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $perubahan = collect($data)->only(['name', 'email'])->filter(fn ($value, $key) => $request->user()->{$key} !== $value)->all();
        if (filled($data['password'] ?? null)) $perubahan['password'] = Hash::make($data['password']);
        if ($perubahan !== []) $request->user()->update($perubahan);
        $audit->record('akun.profile_updated', $request->user(), array_keys($perubahan), $request);

        return back()->with('success', 'Profil akun diperbarui.');
    }
}
