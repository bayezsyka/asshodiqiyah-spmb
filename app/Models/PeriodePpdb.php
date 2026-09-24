<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodePpdb extends Model
{
    protected $table = 'periode_ppdb';
    protected $fillable = ['nama', 'tahun_ajaran', 'mulai_pada', 'selesai_pada', 'status_aktif', 'informasi', 'instruksi_pembayaran'];
    protected function casts(): array { return ['mulai_pada' => 'date', 'selesai_pada' => 'date', 'status_aktif' => 'boolean']; }
    public function pendaftaran(): HasMany { return $this->hasMany(Pendaftaran::class); }
    public function persyaratan(): HasMany { return $this->hasMany(PersyaratanPendaftaran::class); }
}
