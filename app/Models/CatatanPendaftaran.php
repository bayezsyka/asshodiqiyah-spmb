<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatatanPendaftaran extends Model
{
    protected $table = 'catatan_pendaftaran';
    protected $fillable = ['pendaftaran_id', 'user_id', 'isi', 'tampil_publik'];
    protected function casts(): array { return ['tampil_publik' => 'boolean']; }
    public function pendaftaran(): BelongsTo { return $this->belongsTo(Pendaftaran::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
