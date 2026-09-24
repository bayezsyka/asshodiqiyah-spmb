<?php

namespace App\Models;

use App\Enums\StatusPendaftaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pendaftaran extends Model
{
    protected $table = 'pendaftaran';
    protected $fillable = [
        'periode_ppdb_id', 'jenjang_pendaftaran_id', 'status', 'nisn', 'nik', 'nama_ibu_pencarian',
        'nama_lengkap', 'nama_panggilan', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'agama_calon', 'suku_bangsa_calon', 'kewarganegaraan',
        'anak_ke', 'jumlah_saudara_kandung', 'jumlah_saudara_tiri', 'jumlah_saudara_angkat', 'jumlah_bersaudara', 'alamat_domisili', 'nomor_telepon_calon',
        'tinggal_bersama', 'jarak_rumah_km', 'jarak_rumah_meter', 'waktu_tempuh_jam', 'waktu_tempuh_menit', 'asal_sekolah', 'alamat_sekolah_asal', 'nomor_telepon_darurat_1', 'nomor_telepon_darurat_2',
        'nama_ayah', 'tempat_lahir_ayah', 'tanggal_lahir_ayah', 'agama_ayah', 'suku_bangsa_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'alamat_ayah', 'nomor_telepon_ayah', 'penghasilan_ayah',
        'nama_ibu', 'tempat_lahir_ibu', 'tanggal_lahir_ibu', 'agama_ibu', 'suku_bangsa_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'alamat_ibu', 'nomor_telepon_ibu', 'penghasilan_ibu',
        'catatan_publik_terakhir', 'submitted_at', 'verified_at', 'accepted_at', 're_registered_at',
    ];
    protected function casts(): array
    {
        return ['status' => StatusPendaftaran::class, 'tanggal_lahir' => 'date', 'tanggal_lahir_ayah' => 'date', 'tanggal_lahir_ibu' => 'date', 'submitted_at' => 'datetime', 'verified_at' => 'datetime', 'accepted_at' => 'datetime', 're_registered_at' => 'datetime'];
    }
    public function jenjang(): BelongsTo { return $this->belongsTo(JenjangPendaftaran::class, 'jenjang_pendaftaran_id'); }
    public function periode(): BelongsTo { return $this->belongsTo(PeriodePpdb::class, 'periode_ppdb_id'); }
    public function berkas(): HasMany { return $this->hasMany(BerkasPendaftaran::class); }
    public function riwayatStatus(): HasMany { return $this->hasMany(RiwayatStatusPendaftaran::class)->latest(); }
    public function catatan(): HasMany { return $this->hasMany(CatatanPendaftaran::class)->latest(); }
}
