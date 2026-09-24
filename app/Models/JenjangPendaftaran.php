<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenjangPendaftaran extends Model
{
    protected $table = 'jenjang_pendaftaran';

    protected $fillable = ['kode', 'nama', 'kelompok', 'unit_pendidikan_id', 'urutan', 'status_aktif'];

    protected function casts(): array
    {
        return ['status_aktif' => 'boolean'];
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class);
    }

    public function persyaratan(): HasMany
    {
        return $this->hasMany(PersyaratanPendaftaran::class);
    }

    public function unitPendidikan(): BelongsTo
    {
        return $this->belongsTo(UnitPendidikan::class);
    }

    public function scopeUntukPengelola(Builder $query, User $user): Builder
    {
        return $user->isSuperadmin() ? $query : $query->where('unit_pendidikan_id', $user->unit_pendidikan_id);
    }
}
