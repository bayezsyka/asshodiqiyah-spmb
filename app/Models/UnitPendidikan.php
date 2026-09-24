<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitPendidikan extends Model
{
    protected $table = 'unit_pendidikan';

    protected $fillable = ['kode', 'nama', 'urutan', 'status_aktif'];

    protected function casts(): array
    {
        return ['status_aktif' => 'boolean'];
    }

    public function jenjangPendaftaran(): HasMany
    {
        return $this->hasMany(JenjangPendaftaran::class);
    }

    public function pengguna(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
