<?php

namespace App\Services\Pendaftaran;

final class NormalisasiIdentitasPendaftaran
{
    public function namaIbu(string $nama): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', mb_strtolower($nama)));
    }

    public function angka(string $nilai): string
    {
        return (string) preg_replace('/\D/', '', $nilai);
    }
}
