<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BerkasPendaftaran extends Model
{
    protected $table = 'berkas_pendaftaran';
    protected $fillable = ['pendaftaran_id', 'persyaratan_pendaftaran_id', 'kode_berkas', 'nama_berkas', 'path', 'nama_asli', 'mime_type', 'ukuran', 'status_verifikasi', 'catatan_verifikasi'];
    protected function casts(): array { return ['ukuran' => 'integer']; }

    public function lokasiPenyimpanan(): ?string
    {
        $lokasi = is_string($this->path) ? trim($this->path) : '';

        if ($lokasi === '' || ! str_starts_with($lokasi, 'ppdb/') || str_contains($lokasi, '..') || str_contains($lokasi, '\\')) {
            return null;
        }

        return $lokasi;
    }

    public function pendaftaran(): BelongsTo { return $this->belongsTo(Pendaftaran::class); }
    public function persyaratan(): BelongsTo { return $this->belongsTo(PersyaratanPendaftaran::class, 'persyaratan_pendaftaran_id'); }
}
