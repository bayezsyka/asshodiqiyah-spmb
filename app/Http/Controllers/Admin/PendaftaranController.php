<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusPendaftaran;
use App\Http\Controllers\Controller;
use App\Models\JenjangPendaftaran;
use App\Models\Pendaftaran;
use App\Models\PeriodePpdb;
use App\Services\AuditLogger;
use App\Services\Pendaftaran\PengubahStatusPendaftaran;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PendaftaranController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->validate([
            'cari' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'jenjang' => ['nullable', 'integer'],
            'periode' => ['nullable', 'string'],
        ]);

        $periodeList = PeriodePpdb::orderByDesc('status_aktif')->orderByDesc('mulai_pada')->get(['id', 'nama', 'tahun_ajaran', 'status_aktif']);
        $periodeAktif = $periodeList->firstWhere('status_aktif', true);

        $pendaftaran = Pendaftaran::query()->untukPengelola($request->user())->with(['jenjang:id,nama', 'periode:id,nama,tahun_ajaran'])
            ->when($filter['cari'] ?? null, fn ($query, $nilai) => $query->where(fn ($pencarian) => $pencarian->where('nama_lengkap', 'like', "%{$nilai}%")->orWhere('nisn', 'like', "%{$nilai}%")->orWhere('nik', 'like', "%{$nilai}%")->orWhere('nomor_telepon_calon', 'like', "%{$nilai}%")->orWhere('nomor_telepon_ayah', 'like', "%{$nilai}%")->orWhere('nomor_telepon_ibu', 'like', "%{$nilai}%")))
            ->when($filter['status'] ?? null, fn ($query, $nilai) => $query->where('status', $nilai))
            ->when($filter['jenjang'] ?? null, fn ($query, $nilai) => $query->where('jenjang_pendaftaran_id', $nilai))
            ->when($filter['periode'] ?? null, function ($query, $nilai) {
                if ($nilai === 'tanpa_periode') {
                    $query->whereNull('periode_ppdb_id');
                } elseif ($nilai !== 'all' && is_numeric($nilai)) {
                    $query->where('periode_ppdb_id', (int) $nilai);
                }
            })
            ->latest('submitted_at')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Pendaftaran/Index', [
            'pendaftaran' => $pendaftaran,
            'filter' => $filter,
            'jenjang' => JenjangPendaftaran::query()->untukPengelola($request->user())->where('status_aktif', true)->orderBy('urutan')->get(['id', 'nama', 'kelompok']),
            'periode' => $periodeList,
            'periodeAktif' => $periodeAktif,
            'statusPilihan' => collect(StatusPendaftaran::cases())->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()]),
        ]);
    }

    public function show(Request $request, Pendaftaran $pendaftaran)
    {
        $this->pastikanDalamUnit($request, $pendaftaran);
        $pendaftaran->load(['jenjang', 'periode', 'berkas.persyaratan', 'riwayatStatus.user:id,name', 'catatan.user:id,name']);

        return Inertia::render('Admin/Pendaftaran/Show', ['pendaftaran' => $pendaftaran, 'statusPilihan' => collect(StatusPendaftaran::cases())->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])]);
    }

    public function ubahStatus(Request $request, Pendaftaran $pendaftaran, PengubahStatusPendaftaran $pengubahStatus, AuditLogger $audit)
    {
        $this->pastikanDalamUnit($request, $pendaftaran);
        $data = $request->validate(['status' => ['required', 'in:'.collect(StatusPendaftaran::cases())->pluck('value')->join(',')], 'catatan_publik' => ['nullable', 'string', 'max:3000'], 'catatan_internal' => ['nullable', 'string', 'max:3000']]);
        $hasil = $pengubahStatus->ubah($pendaftaran, StatusPendaftaran::from($data['status']), $request->user(), $data);
        $audit->record('pendaftaran.status_changed', $hasil, ['status' => $data['status']], $request);

        return back()->with('success', 'Status pendaftaran diperbarui.');
    }

    public function catatan(Request $request, Pendaftaran $pendaftaran, AuditLogger $audit)
    {
        $this->pastikanDalamUnit($request, $pendaftaran);
        $data = $request->validate(['isi' => ['required', 'string', 'max:3000'], 'tampil_publik' => ['required', 'boolean']]);
        $catatan = $pendaftaran->catatan()->create(['user_id' => $request->user()->id, ...$data]);
        $audit->record('pendaftaran.note_added', $catatan, [], $request);

        return back()->with('success', 'Catatan ditambahkan.');
    }

    private function pastikanDalamUnit(Request $request, Pendaftaran $pendaftaran): void
    {
        abort_unless(Pendaftaran::query()->untukPengelola($request->user())->whereKey($pendaftaran->id)->exists(), 403);
    }
}
