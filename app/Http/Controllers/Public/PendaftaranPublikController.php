<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pendaftaran\SimpanPendaftaranRequest;
use App\Models\JenjangPendaftaran;
use App\Models\PeriodePpdb;
use App\Models\PersyaratanPendaftaran;
use App\Services\Pendaftaran\PenyimpanPendaftaran;
use Inertia\Inertia;
use Inertia\Response;

class PendaftaranPublikController extends Controller
{
    public function create(): Response
    {
        $periode = PeriodePpdb::query()->where('status_aktif', true)->orderByDesc('mulai_pada')->first();
        return Inertia::render('Public/Daftar', [
            'jenjang' => JenjangPendaftaran::query()->where('status_aktif', true)->orderBy('urutan')->get(['id', 'kode', 'nama', 'kelompok']),
            'periode' => $periode ? $periode->only(['id', 'nama', 'tahun_ajaran', 'informasi', 'instruksi_pembayaran']) : null,
            'persyaratan' => PersyaratanPendaftaran::query()->where('status_aktif', true)->orderBy('urutan')->get(['id', 'jenjang_pendaftaran_id', 'periode_ppdb_id', 'kode', 'nama', 'keterangan', 'wajib', 'urutan']),
        ]);
    }

    public function store(SimpanPendaftaranRequest $request, PenyimpanPendaftaran $penyimpan)
    {
        $penyimpan->simpan($request->validated());

        return redirect()->route('pendaftaran.berhasil');
    }

    public function berhasil(): Response
    {
        return Inertia::render('Public/PendaftaranBerhasil');
    }
}
