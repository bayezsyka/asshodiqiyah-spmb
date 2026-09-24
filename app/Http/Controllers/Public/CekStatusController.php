<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Services\Pendaftaran\NormalisasiIdentitasPendaftaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CekStatusController extends Controller
{
    public function form(): Response { return Inertia::render('Public/CekStatus'); }
    public function cari(Request $request, NormalisasiIdentitasPendaftaran $normalisasi): RedirectResponse
    {
        $data = $request->validate([
            'identitas' => ['required', 'string', 'regex:/^\d{10}(\d{6})?$/'],
            'tanggal_lahir' => ['required', 'date'],
            'nama_ibu' => ['required', 'string', 'max:150'],
        ], ['identitas.regex' => 'NISN harus 10 digit atau NIK harus 16 digit.']);

        $identitas = $normalisasi->angka($data['identitas']);
        $pendaftaran = Pendaftaran::query()
            ->whereDate('tanggal_lahir', $data['tanggal_lahir'])
            ->where('nama_ibu_pencarian', $normalisasi->namaIbu($data['nama_ibu']))
            ->where(fn ($query) => $query->where('nisn', $identitas)->orWhere('nik', $identitas))
            ->first();

        if (! $pendaftaran) {
            return back()->withErrors(['identitas' => 'Data pendaftaran tidak ditemukan. Periksa kembali identitas, tanggal lahir, dan nama ibu.']);
        }

        $request->session()->put('akses_status_pendaftaran', [
            'pendaftaran_id' => $pendaftaran->id,
            'berlaku_sampai' => now()->addMinutes(15)->timestamp,
        ]);

        return redirect()->route('status.show');
    }
    public function show(Request $request): Response|RedirectResponse
    {
        $akses = $request->session()->get('akses_status_pendaftaran', []);
        abort_unless((int) ($akses['berlaku_sampai'] ?? 0) >= now()->timestamp, 403);
        $pendaftaran = Pendaftaran::query()->with(['jenjang:id,nama', 'periode:id,nama,tahun_ajaran', 'riwayatStatus' => fn ($q) => $q->select(['id','pendaftaran_id','status_sesudahnya','catatan_publik','created_at'])->whereNotNull('catatan_publik')])->findOrFail($akses['pendaftaran_id'] ?? 0);

        return Inertia::render('Public/StatusPendaftaran', ['pendaftaran' => [
            'nama_lengkap' => $pendaftaran->nama_lengkap,
            'status' => $pendaftaran->status->value,
            'catatan_publik_terakhir' => $pendaftaran->catatan_publik_terakhir,
            'jenjang' => $pendaftaran->jenjang?->only(['nama']),
            'periode' => $pendaftaran->periode?->only(['nama', 'tahun_ajaran']),
            'riwayat_status' => $pendaftaran->riwayatStatus->map(fn ($riwayat) => [
                'id' => $riwayat->id,
                'status_sesudahnya' => $riwayat->status_sesudahnya->value,
                'catatan_publik' => $riwayat->catatan_publik,
                'created_at' => $riwayat->created_at,
            ])->values(),
        ]]);
    }
}
