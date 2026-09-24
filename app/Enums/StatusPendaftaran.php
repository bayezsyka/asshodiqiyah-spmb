<?php

namespace App\Enums;

enum StatusPendaftaran: string
{
    case Diajukan = 'diajukan';
    case PerluPerbaikan = 'perlu_perbaikan';
    case Diverifikasi = 'diverifikasi';
    case Diterima = 'diterima';
    case TidakDiterima = 'tidak_diterima';
    case DaftarUlang = 'daftar_ulang';

    public function label(): string
    {
        return match ($this) {
            self::Diajukan => 'Diajukan',
            self::PerluPerbaikan => 'Perlu perbaikan',
            self::Diverifikasi => 'Terverifikasi',
            self::Diterima => 'Diterima',
            self::TidakDiterima => 'Tidak diterima',
            self::DaftarUlang => 'Daftar ulang',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::TidakDiterima, self::DaftarUlang], true);
    }
}
