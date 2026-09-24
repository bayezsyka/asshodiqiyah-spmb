<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PeranUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $this->pastikanSuperadmin($request);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            'peran' => ['nullable', Rule::enum(PeranUser::class)],
        ]);

        $users = User::query()
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status_aktif', $status === 'aktif'))
            ->when($filters['peran'] ?? null, fn ($query, string $peran) => $query->where('peran', $peran))
            ->orderByDesc('status_aktif')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'peran' => $user->peran->value,
                'peranLabel' => $user->peran->label(),
                'statusAktif' => $user->status_aktif,
                'statusLabel' => $user->status_aktif ? 'Aktif' : 'Nonaktif',
                'googleTerhubung' => filled($user->google_id),
                'lastLoginAt' => $user->last_login_at?->toIso8601String(),
                'createdAt' => $user->created_at?->toIso8601String(),
                'isCurrentUser' => $user->is($request->user()),
                'canBeManaged' => ! $user->is($request->user()) && ! $user->isSuperadmin(),
                'updateUrl' => route('admin.users.update', $user, false),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'peran' => $filters['peran'] ?? '',
            ],
            'options' => [
                'peran' => array_map(
                    fn (PeranUser $peran) => ['value' => $peran->value, 'label' => $peran->label()],
                    PeranUser::cases(),
                ),
                'status' => [
                    ['value' => 'aktif', 'label' => 'Aktif'],
                    ['value' => 'nonaktif', 'label' => 'Nonaktif'],
                ],
            ],
            'storeUrl' => route('admin.users.store', absolute: false),
        ]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $this->pastikanSuperadmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'peran' => ['required', Rule::in([PeranUser::AdminSpmb->value])],
        ], [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, strip, dan garis bawah.',
            'username.unique' => 'Username ini sudah digunakan.',
            'email.unique' => 'Email ini sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            ...$data,
            'username' => strtolower(trim($data['username'])),
            'peran' => PeranUser::AdminSpmb,
            'status_aktif' => true,
        ]);

        $audit->record('akun.created', $user, [
            'username' => $user->username,
            'peran' => $user->peran->value,
        ], $request);

        return back()->with('success', "Akun '{$user->username}' berhasil dibuat.");
    }

    public function update(Request $request, User $user, AuditLogger $audit): RedirectResponse
    {
        $this->pastikanSuperadmin($request);

        $data = $request->validate([
            'action' => ['required', Rule::in(['update_role', 'suspend', 'reactivate'])],
            'peran' => ['nullable', Rule::enum(PeranUser::class)],
        ]);

        if ($user->is($request->user()) || $user->isSuperadmin()) {
            throw ValidationException::withMessages([
                'action' => 'Akun sendiri dan akun superadmin tidak dapat diubah dari halaman ini.',
            ]);
        }

        $sebelum = ['peran' => $user->peran->value, 'status_aktif' => $user->status_aktif];

        $aksi = match ($data['action']) {
            'update_role' => $this->ubahPeran($user, $data['peran'] ?? null),
            'suspend' => $this->ubahStatus($user, false),
            'reactivate' => $this->ubahStatus($user, true),
        };

        $audit->record($aksi, $user, [
            'sebelum' => $sebelum,
            'sesudah' => ['peran' => $user->peran->value, 'status_aktif' => $user->status_aktif],
        ], $request);

        return back()->with('success', 'Perubahan akun berhasil disimpan.');
    }

    private function ubahPeran(User $user, ?string $peran): string
    {
        if (! $user->status_aktif || $peran !== PeranUser::AdminSpmb->value) {
            throw ValidationException::withMessages(['action' => 'Peran akun tidak dapat diubah.']);
        }

        $user->update(['peran' => PeranUser::AdminSpmb]);

        return 'akun.role_changed';
    }

    private function ubahStatus(User $user, bool $aktif): string
    {
        if ($user->status_aktif === $aktif) {
            throw ValidationException::withMessages(['action' => 'Status akun sudah sesuai.']);
        }

        $user->update(['status_aktif' => $aktif]);

        return $aktif ? 'akun.reactivated' : 'akun.suspended';
    }

    private function pastikanSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->isSuperadmin(), 403);
    }
}
