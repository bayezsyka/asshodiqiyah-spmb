<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\PeriodePpdb;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PeriodePpdbController extends Controller
{
    public function index(Request $request): Response
    {
        $this->pastikanSuperadmin($request);
        $periode = PeriodePpdb::query()
            ->withCount('pendaftaran')
            ->orderByDesc('status_aktif')
            ->orderByDesc('mulai_pada')
            ->orderByDesc('id')
            ->get();

        $ringkasanJenjang = Pendaftaran::query()
            ->selectRaw('periode_ppdb_id, jenjang_pendaftaran_id, COUNT(*) as total')
            ->whereNotNull('periode_ppdb_id')
            ->with('jenjang:id,nama')
            ->groupBy('periode_ppdb_id', 'jenjang_pendaftaran_id')
            ->get()
            ->groupBy('periode_ppdb_id');

        $periode->each(function (PeriodePpdb $item) use ($ringkasanJenjang): void {
            $item->setAttribute('ringkasan_jenjang', ($ringkasanJenjang->get($item->id, collect()))
                ->map(fn (Pendaftaran $pendaftaran) => [
                    'nama' => $pendaftaran->jenjang?->nama ?? 'Tanpa jenjang',
                    'total' => (int) $pendaftaran->total,
                ])
                ->values());
        });

        $ringkasanTanpaPeriode = Pendaftaran::query()
            ->whereNull('periode_ppdb_id')
            ->with('jenjang:id,nama')
            ->selectRaw('jenjang_pendaftaran_id, COUNT(*) as total')
            ->groupBy('jenjang_pendaftaran_id')
            ->get()
            ->map(fn (Pendaftaran $pendaftaran) => [
                'nama' => $pendaftaran->jenjang?->nama ?? 'PAUD',
                'total' => (int) $pendaftaran->total,
            ])
            ->values();

        return Inertia::render('Admin/Periode/Index', [
            'periode' => $periode,
            'totalPendaftarTanpaPeriode' => Pendaftaran::query()
                ->whereNull('periode_ppdb_id')
                ->count(),
            'ringkasanTanpaPeriode' => $ringkasanTanpaPeriode,
        ]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $this->pastikanSuperadmin($request);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'mulai_pada' => ['nullable', 'date'],
            'selesai_pada' => ['nullable', 'date', 'after_or_equal:mulai_pada'],
            'status_aktif' => ['required', 'boolean'],
            'informasi' => ['nullable', 'string', 'max:3000'],
            'instruksi_pembayaran' => ['nullable', 'string', 'max:3000'],
        ]);

        if ($request->boolean('status_aktif')) {
            PeriodePpdb::query()->update(['status_aktif' => false]);
        }

        $periode = PeriodePpdb::create($data);
        $audit->record('periode.created', $periode, $data, $request);

        return back()->with('success', "Periode {$periode->tahun_ajaran} berhasil ditambahkan.");
    }

    public function update(Request $request, PeriodePpdb $periode, AuditLogger $audit): RedirectResponse
    {
        $this->pastikanSuperadmin($request);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'mulai_pada' => ['nullable', 'date'],
            'selesai_pada' => ['nullable', 'date', 'after_or_equal:mulai_pada'],
            'status_aktif' => ['required', 'boolean'],
            'informasi' => ['nullable', 'string', 'max:3000'],
            'instruksi_pembayaran' => ['nullable', 'string', 'max:3000'],
        ]);

        if ($request->boolean('status_aktif')) {
            PeriodePpdb::where('id', '!=', $periode->id)->update(['status_aktif' => false]);
        }

        $periode->update($data);
        $audit->record('periode.updated', $periode, $data, $request);

        return back()->with('success', "Periode {$periode->tahun_ajaran} berhasil diperbarui.");
    }

    public function aktifkan(Request $request, PeriodePpdb $periode, AuditLogger $audit): RedirectResponse
    {
        $this->pastikanSuperadmin($request);
        PeriodePpdb::where('id', '!=', $periode->id)->update(['status_aktif' => false]);
        $periode->update(['status_aktif' => true]);

        $audit->record('periode.activated', $periode, ['tahun_ajaran' => $periode->tahun_ajaran], $request);

        return back()->with('success', "Periode {$periode->tahun_ajaran} sekarang menjadi periode aktif.");
    }

    public function destroy(Request $request, PeriodePpdb $periode, AuditLogger $audit): RedirectResponse
    {
        $this->pastikanSuperadmin($request);
        if ($periode->pendaftaran()->exists()) {
            return back()->with('error', 'Periode tidak dapat dihapus karena sudah terdapat pendaftar yang terdaftar di periode ini.');
        }

        if ($periode->status_aktif) {
            return back()->with('error', 'Periode aktif tidak dapat dihapus. Silakan aktifkan periode lain terlebih dahulu.');
        }

        $tahunAjaran = $periode->tahun_ajaran;
        $periode->delete();

        $audit->record('periode.deleted', null, ['tahun_ajaran' => $tahunAjaran], $request);

        return back()->with('success', "Periode {$tahunAjaran} berhasil dihapus.");
    }

    private function pastikanSuperadmin(Request $request): void
    {
        abort_unless($request->user()?->isSuperadmin(), 403);
    }
}
