<?php

namespace App\Services\Pendaftaran;

use App\Enums\StatusPendaftaran;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PengubahStatusPendaftaran
{
    /** @param array{catatan_publik?: string|null,catatan_internal?: string|null} $catatan */
    public function ubah(Pendaftaran $pendaftaran, StatusPendaftaran $status, User $actor, array $catatan = []): Pendaftaran
    {
        if ($pendaftaran->status === $status) throw ValidationException::withMessages(['status' => 'Status pendaftaran sudah sama.']);
        if ($pendaftaran->status->isTerminal()) throw ValidationException::withMessages(['status' => 'Status pendaftaran final tidak dapat diubah.']);

        return DB::transaction(function () use ($pendaftaran, $status, $actor, $catatan) {
            $sebelumnya = $pendaftaran->status;
            $pendaftaran->status = $status;
            $pendaftaran->catatan_publik_terakhir = $catatan['catatan_publik'] ?? null;
            if ($status === StatusPendaftaran::Diverifikasi) $pendaftaran->verified_at ??= now();
            if ($status === StatusPendaftaran::Diterima) $pendaftaran->accepted_at ??= now();
            if ($status === StatusPendaftaran::DaftarUlang) $pendaftaran->re_registered_at ??= now();
            $pendaftaran->save();
            $pendaftaran->riwayatStatus()->create([
                'user_id' => $actor->id, 'status_sebelumnya' => $sebelumnya, 'status_sesudahnya' => $status,
                'catatan_publik' => $catatan['catatan_publik'] ?? null, 'catatan_internal' => $catatan['catatan_internal'] ?? null,
            ]);
            return $pendaftaran;
        });
    }
}
