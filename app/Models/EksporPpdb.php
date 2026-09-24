<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EksporPpdb extends Model
{
    protected $table = 'ekspor_ppdb';
    protected $fillable = ['user_id', 'jenis', 'filter', 'status', 'path', 'error_message', 'selesai_pada'];
    protected function casts(): array { return ['filter' => 'array', 'selesai_pada' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
