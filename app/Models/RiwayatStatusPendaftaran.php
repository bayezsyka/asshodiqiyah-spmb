<?php

namespace App\Models;

use App\Enums\StatusPendaftaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatusPendaftaran extends Model
{
    protected $table = 'riwayat_status_pendaftaran';
    protected $fillable = ['pendaftaran_id', 'user_id', 'status_sebelumnya', 'status_sesudahnya', 'catatan_publik', 'catatan_internal'];
    protected function casts(): array { return ['status_sebelumnya' => StatusPendaftaran::class, 'status_sesudahnya' => StatusPendaftaran::class]; }
    public function pendaftaran(): BelongsTo { return $this->belongsTo(Pendaftaran::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
