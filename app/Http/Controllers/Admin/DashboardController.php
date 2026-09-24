<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPendaftaran;
use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\PeriodePpdb;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $periodeList = PeriodePpdb::query()
            ->orderByDesc('status_aktif')
            ->orderByDesc('mulai_pada')
            ->orderByDesc('id')
            ->get(['id', 'nama', 'tahun_ajaran', 'status_aktif']);

        $periodeAktif = $periodeList->firstWhere('status_aktif', true);

        // Jika tidak ada query 'periode', default ke periode aktif (jika ada), atau 'all'
        $defaultPeriode = $periodeAktif ? (string) $periodeAktif->id : 'all';
        $periodeTerpilih = $request->query('periode', $defaultPeriode);

        $q = Pendaftaran::query();

        if ($periodeTerpilih !== 'all' && $periodeTerpilih !== '') {
            if ($periodeTerpilih === 'tanpa_periode') {
                $q->whereNull('periode_ppdb_id');
            } elseif (is_numeric($periodeTerpilih)) {
                $q->where('periode_ppdb_id', (int) $periodeTerpilih);
            }
        }

        $ringkasan = [
            'total' => (clone $q)->count(),
            'diajukan' => (clone $q)->where('status', StatusPendaftaran::Diajukan)->count(),
            'perluPerbaikan' => (clone $q)->where('status', StatusPendaftaran::PerluPerbaikan)->count(),
            'diterima' => (clone $q)->where('status', StatusPendaftaran::Diterima)->count(),
        ];

        $terbaru = (clone $q)
            ->with(['jenjang:id,nama', 'periode:id,tahun_ajaran'])
            ->latest('submitted_at')
            ->limit(8)
            ->get(['id', 'nama_lengkap', 'jenjang_pendaftaran_id', 'periode_ppdb_id', 'status', 'submitted_at']);

        return Inertia::render('Admin/Dashboard', [
            'ringkasan' => $ringkasan,
            'terbaru' => $terbaru,
            'periodeList' => $periodeList,
            'periodeTerpilih' => $periodeTerpilih,
            'periodeAktif' => $periodeAktif,
            'totalTanpaPeriode' => Pendaftaran::query()->whereNull('periode_ppdb_id')->count(),
        ]);
    }
}
