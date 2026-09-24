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
        $isPaud = $jenjang->kelompok === 'paud';

        return [
            'kelompok' => $jenjang->kelompok,
            'nisn_wajib' => ! $isPaud,
            'nik_wajib' => $isPaud,
            'asal_sekolah_wajib' => ! $isPaud,
            'periode_wajib' => ! $isPaud,
            'label_identitas_utama' => $isPaud ? 'NIK Calon Peserta' : 'NISN Calon Peserta',
            'bantuan_identitas' => $isPaud
                ? 'NIK 16 digit sesuai Kartu Keluarga (KK).'
                : 'NISN 10 digit resmi dari Dapodik / Kemendikbud.',
        ];
    }

    /**
     * Menentukan periode SPMB yang tepat untuk pendaftaran.
     * Untuk jenjang PAUD, pendaftaran dibuka sepanjang tahun tanpa periode.
     * Untuk jenjang formal, pendaftaran dikaitkan ke periode aktif.
     */
    public function tentukanPeriode(?JenjangPendaftaran $jenjang = null, ?int $periodeIdInput = null): ?int
    {
        if ($jenjang?->kelompok === 'paud') {
            return null;
        }

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
        $isPaud = $jenjang->kelompok === 'paud';

        return PersyaratanPendaftaran::query()
            ->where('status_aktif', true)
            ->where(function ($query) use ($jenjang) {
                $query->whereNull('jenjang_pendaftaran_id')
                    ->orWhere('jenjang_pendaftaran_id', $jenjang->id);
            })
            ->where(function ($query) use ($isPaud, $periodeId) {
                if ($isPaud) {
                    $query->whereNull('periode_ppdb_id');
                } else {
                    $query->whereNull('periode_ppdb_id')
                        ->orWhere('periode_ppdb_id', $periodeId);
                }
            })
            ->orderBy('urutan')
            ->get();
    }
}
