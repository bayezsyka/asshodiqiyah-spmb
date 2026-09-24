<?php

namespace App\Services\Pendaftaran;

use App\Models\JenjangPendaftaran;
use App\Models\PeriodePpdb;
use App\Models\PersyaratanPendaftaran;
use Illuminate\Database\Eloquent\Collection;

class KonfigurasiAlurJenjang
{
    /**
     * @return array{
     *   kelompok: string,
     *   nisn_wajib: bool,
     *   nik_wajib: bool,
     *   asal_sekolah_wajib: bool,
     *   periode_wajib: bool,
     *   label_identitas_utama: string,
     *   bantuan_identitas: string
     * }
     */
    public function konfigurasi(JenjangPendaftaran $jenjang): array
    {
        return [
            'kelompok' => $jenjang->kelompok,
            'nisn_wajib' => true,
            'nik_wajib' => false,
            'asal_sekolah_wajib' => true,
            'periode_wajib' => true,
            'label_identitas_utama' => 'NISN Calon Peserta',
            'bantuan_identitas' => 'NISN 10 digit resmi dari Dapodik / Kemendikbud.',
        ];
    }

    /**
     * Menentukan periode SPMB yang tepat untuk pendaftaran.
     * Seluruh unit Asshodiqiyah mengikuti periode SPMB aktif.
     * Untuk jenjang formal, pendaftaran dikaitkan ke periode aktif.
     */
    public function tentukanPeriode(?JenjangPendaftaran $jenjang = null, ?int $periodeIdInput = null): ?int
    {
        if ($periodeIdInput !== null) {
            $periode = PeriodePpdb::query()->where('status_aktif', true)->find($periodeIdInput);
            if ($periode) {
                return $periode->id;
            }
        }

        $periodeAktif = PeriodePpdb::query()->where('status_aktif', true)->orderByDesc('mulai_pada')->first();

        return $periodeAktif?->id;
    }

    /**
     * Mengambil persyaratan berkas yang berlaku untuk jenjang dan periode yang ditentukan.
     *
     * @return Collection<int, PersyaratanPendaftaran>
     */
    public function ambilPersyaratan(JenjangPendaftaran $jenjang, ?int $periodeId = null): Collection
    {
        return PersyaratanPendaftaran::query()
            ->where('status_aktif', true)
            ->where(function ($query) use ($jenjang) {
                $query->whereNull('jenjang_pendaftaran_id')
                    ->orWhere('jenjang_pendaftaran_id', $jenjang->id);
            })
            ->where(fn ($query) => $query->whereNull('periode_ppdb_id')->orWhere('periode_ppdb_id', $periodeId))
            ->orderBy('urutan')
            ->get();
    }
}
