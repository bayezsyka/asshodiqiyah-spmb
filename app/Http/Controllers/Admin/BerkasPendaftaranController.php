<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BerkasPendaftaran;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BerkasPendaftaranController extends Controller
{
    public function download(Request $request, BerkasPendaftaran $berkas, AuditLogger $audit)
    {
        $lokasi = $berkas->lokasiPenyimpanan();
        abort_unless($lokasi !== null && Storage::disk('local')->exists($lokasi), 404);

        $audit->record('berkas.downloaded', $berkas, [], $request);

        if ($berkas->mime_type === 'application/pdf') {
            return response()->file(Storage::disk('local')->path($lokasi), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$berkas->nama_asli.'"',
            ]);
        }

        return Storage::disk('local')->download($lokasi, $berkas->nama_asli);
    }

    public function verifikasi(Request $request, BerkasPendaftaran $berkas, AuditLogger $audit)
    {
        $data = $request->validate([
            'status_verifikasi' => ['required', 'in:valid,perlu_perbaikan'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:1500'],
        ]);

        $berkas->update($data);
        $audit->record('berkas.verified', $berkas, $data, $request);

        return back()->with('success', 'Status berkas diperbarui.');
    }
}
