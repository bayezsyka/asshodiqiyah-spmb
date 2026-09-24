<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenjangPendaftaran extends Model
{
    protected $table = 'jenjang_pendaftaran';
    protected $fillable = ['kode', 'nama', 'kelompok', 'urutan', 'status_aktif'];
    protected function casts(): array { return ['status_aktif' => 'boolean']; }
    public function pendaftaran(): HasMany { return $this->hasMany(Pendaftaran::class); }
    public function persyaratan(): HasMany { return $this->hasMany(PersyaratanPendaftaran::class); }
}
