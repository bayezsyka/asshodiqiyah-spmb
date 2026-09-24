<?php

namespace App\Exports;

use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendaftaranExport implements FromCollection, WithHeadings
{
    /** @param array<string, mixed> $filter */
    public function __construct(private readonly array $filter = [], private readonly ?User $pengelola = null) {}

    public function collection(): Enumerable
    {
        return Pendaftaran::query()
            ->when($this->pengelola, fn ($query, User $user) => $query->untukPengelola($user))
            ->with(['jenjang', 'periode'])
            ->when($this->filter['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($this->filter['jenjang'] ?? null, fn ($query, $value) => $query->where('jenjang_pendaftaran_id', $value))
            ->when($this->filter['periode'] ?? null, function ($query, $value) {
                if ($value === 'tanpa_periode') {
                    $query->whereNull('periode_ppdb_id');
                } elseif ($value !== 'all' && is_numeric($value)) {
                    $query->where('periode_ppdb_id', (int) $value);
                }
            })
            ->orderByDesc('submitted_at')
            ->get()
            ->map(fn (Pendaftaran $pendaftaran) => array_map($this->nilaiAman(...), [
                $pendaftaran->nama_lengkap, $pendaftaran->nisn, $pendaftaran->nik, $pendaftaran->nama_panggilan, $pendaftaran->jenjang->nama,
                $pendaftaran->periode?->tahun_ajaran ?? 'Tanpa Periode', $pendaftaran->status->label(),
                $pendaftaran->tempat_lahir, $pendaftaran->tanggal_lahir?->format('d/m/Y'), $pendaftaran->jenis_kelamin, $pendaftaran->agama_calon,
                $pendaftaran->suku_bangsa_calon, $pendaftaran->kewarganegaraan, $pendaftaran->alamat_domisili, $pendaftaran->nomor_telepon_calon,
                $pendaftaran->tinggal_bersama, $pendaftaran->asal_sekolah, $pendaftaran->alamat_sekolah_asal, $pendaftaran->nama_ayah,
                $pendaftaran->nomor_telepon_ayah, $pendaftaran->pendidikan_ayah, $pendaftaran->pekerjaan_ayah, $pendaftaran->penghasilan_ayah,
                $pendaftaran->nama_ibu, $pendaftaran->nomor_telepon_ibu, $pendaftaran->pendidikan_ibu, $pendaftaran->pekerjaan_ibu,
                $pendaftaran->penghasilan_ibu, $pendaftaran->nomor_telepon_darurat_1, $pendaftaran->nomor_telepon_darurat_2,
                $pendaftaran->submitted_at?->format('d/m/Y H:i'),
            ]));
    }

    public function headings(): array
    {
        return ['Nama lengkap', 'NISN', 'NIK', 'Nama panggilan', 'Jenjang', 'Tahun ajaran', 'Status', 'Tempat lahir', 'Tanggal lahir', 'Jenis kelamin', 'Agama', 'Suku bangsa', 'Kewarganegaraan', 'Alamat domisili', 'Telepon calon', 'Tinggal bersama', 'Asal sekolah', 'Alamat sekolah asal', 'Nama ayah', 'Telepon ayah', 'Pendidikan ayah', 'Pekerjaan ayah', 'Penghasilan ayah', 'Nama ibu', 'Telepon ibu', 'Pendidikan ibu', 'Pekerjaan ibu', 'Penghasilan ibu', 'Telepon darurat 1', 'Telepon darurat 2', 'Dikirim pada'];
    }

    private function nilaiAman(mixed $nilai): mixed
    {
        if (is_string($nilai) && preg_match('/^[=+@-]/', $nilai) === 1) {
            return "'{$nilai}";
        }

        return $nilai;
    }
}
