<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PendaftaranExport;
use App\Http\Controllers\Controller;
use App\Models\JenjangPendaftaran;
use App\Models\Pendaftaran;
use App\Models\PeriodePpdb;
use App\Services\AuditLogger;
use App\Services\Ekspor\NamaArsipPendaftaran;
use App\Services\Ekspor\PaketPendaftaranPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class EksporPpdbController extends Controller
{
    public function excel(Request $request, AuditLogger $audit)
    {
        $filter = $request->only(['status', 'jenjang', 'periode']);
        $audit->record('ekspor.excel', null, $filter, $request);

        return Excel::download(new PendaftaranExport($filter, $request->user()), 'Data Pendaftaran '.now()->format('Ymd His').'.xlsx');
    }

    public function pdf(Request $request, Pendaftaran $pendaftaran, AuditLogger $audit, PaketPendaftaranPdfService $paketPdf, NamaArsipPendaftaran $namaArsip)
    {
        $this->pastikanDalamUnit($request, $pendaftaran);
        $isi = $paketPdf->buat($pendaftaran);
        $audit->record('ekspor.pdf_lengkap', $pendaftaran, [], $request);

        return response($isi, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$namaArsip->paketPdf($pendaftaran).'"',
        ]);
    }

    public function zip(Request $request, AuditLogger $audit, PaketPendaftaranPdfService $paketPdf, NamaArsipPendaftaran $namaArsip)
    {
        $filter = $request->validate([
            'status' => ['nullable', 'string'],
            'jenjang' => ['required', 'integer', 'exists:jenjang_pendaftaran,id'],
            'periode' => ['nullable', 'string'],
        ]);
        $jenjang = JenjangPendaftaran::query()->untukPengelola($request->user())->findOrFail($filter['jenjang']);

        if (blank($filter['periode'] ?? null) || $filter['periode'] === 'tanpa_periode') {
            throw ValidationException::withMessages(['periode' => 'Pilih periode pendaftaran untuk export jenjang ini.']);
        }

        $periode = (filled($filter['periode'] ?? null) && is_numeric($filter['periode']))
            ? PeriodePpdb::find($filter['periode'])
            : null;

        $items = Pendaftaran::query()->untukPengelola($request->user())
            ->with(['jenjang', 'periode', 'berkas'])
            ->when($filter['status'] ?? null, fn ($query, $nilai) => $query->where('status', $nilai))
            ->where('jenjang_pendaftaran_id', $jenjang->id)
            ->when($periode, fn ($query) => $query->where('periode_ppdb_id', $periode->id))
            ->orderBy('nama_lengkap')
            ->get();
        $path = tempnam(sys_get_temp_dir(), 'spmb_zip_');

        if ($path === false) {
            abort(500, 'Arsip pendaftaran tidak dapat disiapkan.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            abort(500, 'Arsip pendaftaran tidak dapat dibuat.');
        }

        try {
            foreach ($items as $pendaftaran) {
                $folder = $namaArsip->folderPendaftar($pendaftaran);
                $zip->addFromString($folder.'/'.$namaArsip->formulir(), $paketPdf->buatFormulir($pendaftaran));
                $berkasDipakai = [];

                foreach ($pendaftaran->berkas->sortBy('id') as $berkas) {
                    $lokasi = $berkas->lokasiPenyimpanan();
                    if ($lokasi === null) {
                        continue;
                    }

                    $pathBerkas = Storage::disk('local')->path($lokasi);

                    if (! is_file($pathBerkas)) {
                        continue;
                    }

                    $namaBerkas = $this->namaUnik($namaArsip->berkas($berkas), $berkasDipakai);
                    $zip->addFile($pathBerkas, $folder.'/'.$namaBerkas);
                }
            }
        } finally {
            $zip->close();
        }

        $audit->record('ekspor.zip_berkas', null, [...$filter, 'jumlah_pendaftar' => $items->count()], $request);

        return response()->download($path, $namaArsip->zip($periode?->tahun_ajaran, $jenjang->nama))->deleteFileAfterSend();
    }

    /** @param array<string, true> $namaDipakai */
    private function namaUnik(string $nama, array &$namaDipakai): string
    {
        $asal = $nama;
        $urutan = 2;

        while (isset($namaDipakai[$nama])) {
            $ekstensi = pathinfo($asal, PATHINFO_EXTENSION);
            $namaDasar = pathinfo($asal, PATHINFO_FILENAME);
            $nama = "{$namaDasar} ({$urutan}).{$ekstensi}";
            $urutan++;
        }

        $namaDipakai[$nama] = true;

        return $nama;
    }

    private function pastikanDalamUnit(Request $request, Pendaftaran $pendaftaran): void
    {
        abort_unless(Pendaftaran::query()->untukPengelola($request->user())->whereKey($pendaftaran->id)->exists(), 403);
    }
}
