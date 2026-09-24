<?php

namespace App\Services\Pendaftaran;

use App\Enums\StatusPendaftaran;
use App\Models\JenjangPendaftaran;
use App\Models\Pendaftaran;
use App\Models\PeriodePpdb;
use App\Models\PersyaratanPendaftaran;
use App\Services\Media\UnggahBerkasPendaftaranService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PenyimpanPendaftaran
{
    public function __construct(
        private readonly UnggahBerkasPendaftaranService $unggahBerkas,
        private readonly NormalisasiIdentitasPendaftaran $normalisasiIdentitas,
    ) {}

    /** @param array<string, mixed> $data */
    public function simpan(array $data): Pendaftaran
    {
        $jenjang = JenjangPendaftaran::query()->whereKey($data['jenjang_pendaftaran_id'])->where('status_aktif', true)->firstOrFail();
        $periode = isset($data['periode_ppdb_id'])
            ? PeriodePpdb::query()->whereKey($data['periode_ppdb_id'])->where('status_aktif', true)->first()
            : PeriodePpdb::query()->where('status_aktif', true)->orderByDesc('mulai_pada')->first();

        if ($periode === null) {
            throw ValidationException::withMessages(['periode_ppdb_id' => 'Periode pendaftaran aktif belum tersedia untuk unit ini.']);
        }

        $persyaratan = PersyaratanPendaftaran::query()
            ->where('status_aktif', true)
            ->where(function ($query) use ($jenjang) {
                $query->whereNull('jenjang_pendaftaran_id')->orWhere('jenjang_pendaftaran_id', $jenjang->id);
            })
            ->where(fn ($query) => $query->whereNull('periode_ppdb_id')->orWhere('periode_ppdb_id', $periode->id))
            ->orderBy('urutan')
            ->get()
            ->sortBy(fn (PersyaratanPendaftaran $persyaratan) => ($persyaratan->jenjang_pendaftaran_id !== null ? 1 : 0) + ($persyaratan->periode_ppdb_id !== null ? 1 : 0))
            ->keyBy('kode');

        $berkas = $data['berkas'] ?? [];
        $berkasTidakDiizinkan = array_diff(array_keys($berkas), $persyaratan->keys()->all());
        if ($berkasTidakDiizinkan !== []) {
            throw ValidationException::withMessages([
                'berkas' => 'Terdapat berkas yang tidak termasuk persyaratan pendaftaran aktif.',
            ]);
        }

        $berkasKurang = $persyaratan
            ->where('wajib', true)
            ->keys()
            ->filter(fn (string $kode) => ! ($berkas[$kode] ?? null))
            ->mapWithKeys(fn (string $kode) => ["berkas.$kode" => 'Berkas ini wajib diunggah.'])
            ->all();
        if ($berkasKurang !== []) {
            throw ValidationException::withMessages($berkasKurang);
        }

        $periodeFinalId = $periode->id;

        return DB::transaction(function () use ($data, $jenjang, $periodeFinalId, $persyaratan): Pendaftaran {
            $pendaftaran = Pendaftaran::create([
                ...Arr::except($data, ['berkas', 'periode_ppdb_id', 'nama_ibu_pencarian']),
                'periode_ppdb_id' => $periodeFinalId,
                'jenjang_pendaftaran_id' => $jenjang->id,
                'nisn' => isset($data['nisn']) ? $this->normalisasiIdentitas->angka((string) $data['nisn']) : null,
                'nik' => isset($data['nik']) ? $this->normalisasiIdentitas->angka((string) $data['nik']) : null,
                'nama_ibu_pencarian' => $this->normalisasiIdentitas->namaIbu((string) $data['nama_ibu']),
                'status' => StatusPendaftaran::Diajukan,
                'submitted_at' => now(),
            ]);

            foreach (($data['berkas'] ?? []) as $kode => $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }
                $aturan = $persyaratan->get($kode);
                $this->unggahBerkas->simpan($pendaftaran, $kode, $aturan->nama, $file, $aturan->id);
            }

            $pendaftaran->riwayatStatus()->create(['status_sesudahnya' => StatusPendaftaran::Diajukan]);

            return $pendaftaran;
        });
    }
}
