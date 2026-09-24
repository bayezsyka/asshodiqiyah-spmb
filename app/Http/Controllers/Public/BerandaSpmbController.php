<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JenjangPendaftaran;
use App\Models\PeriodePpdb;
use Inertia\Inertia;
use Inertia\Response;

class BerandaSpmbController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Public/Home', [
            'jenjang' => JenjangPendaftaran::query()->where('status_aktif', true)->orderBy('urutan')->get(['id', 'kode', 'nama', 'kelompok']),
            'periodeAktif' => PeriodePpdb::query()->where('status_aktif', true)->orderByDesc('mulai_pada')->first(['id', 'nama', 'tahun_ajaran', 'informasi']),
        ]);
    }
}
