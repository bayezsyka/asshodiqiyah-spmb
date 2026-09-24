<?php

namespace App\Services\Ekspor;

use App\Models\BerkasPendaftaran;
use App\Models\Pendaftaran;
use Illuminate\Support\Str;

final class NamaArsipPendaftaran
{
    public function folderPendaftar(Pendaftaran $pendaftaran): string
    {
        $identitas = $pendaftaran->nisn
            ? "NISN {$pendaftaran->nisn}"
            : ($pendaftaran->nik ? "NIK {$pendaftaran->nik}" : "Pendaftar {$pendaftaran->id}");

        return $this->bersihkan("{$pendaftaran->nama_lengkap} ({$identitas})");
    }

    public function formulir(): string
    {
        return 'Formulir Pendaftaran.pdf';
    }

    public function paketPdf(Pendaftaran $pendaftaran): string
    {
        return $this->bersihkan('Paket Pendaftaran '.$this->folderPendaftar($pendaftaran)).'.pdf';
    }

    public function berkas(BerkasPendaftaran $berkas): string
    {
        $ekstensi = strtolower(pathinfo($berkas->lokasiPenyimpanan() ?? $berkas->nama_asli, PATHINFO_EXTENSION)) ?: 'bin';

        return $this->bersihkan($berkas->nama_berkas).'.'.$ekstensi;
    }

    public function zip(?string $periode, ?string $jenjang): string
    {
        $bagian = array_filter([$periode, $jenjang]);

        return $this->bersihkan('Berkas Pendaftaran '.implode(' ', $bagian ?: ['SPMB'])).'.zip';
    }

    private function bersihkan(string $nama): string
    {
        $nama = preg_replace('/[^\pL\pN\s()]/u', ' ', $nama) ?? '';
        $nama = Str::squish($nama);

        return Str::limit($nama !== '' ? $nama : 'Berkas Pendaftaran', 120, '');
    }
}
