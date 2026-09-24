<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function record(string $aksi, ?Model $subjek = null, array $metadata = [], ?Request $request = null): void
    {
        AuditLog::create([
            'user_id' => $request?->user()?->id ?? auth()->id(),
            'aksi' => $aksi,
            'subjek_tipe' => $subjek ? $subjek::class : null,
            'subjek_id' => $subjek?->getKey(),
            'metadata' => $metadata ?: null,
            'ip_address' => $request?->ip() ?? request()?->ip(),
        ]);
    }
}
