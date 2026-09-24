<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersyaratanPendaftaran extends Model
{
    protected $table = 'persyaratan_pendaftaran';
    protected $fillable = ['jenjang_pendaftaran_id', 'periode_ppdb_id', 'kode', 'nama', 'keterangan', 'wajib', 'urutan', 'status_aktif'];
    protected function casts(): array { return ['wajib' => 'boolean', 'status_aktif' => 'boolean']; }
    public function jenjang(): BelongsTo { return $this->belongsTo(JenjangPendaftaran::class, 'jenjang_pendaftaran_id'); }
    public function periode(): BelongsTo { return $this->belongsTo(PeriodePpdb::class, 'periode_ppdb_id'); }
}
