<?php

namespace App\Services\Media;

use App\Models\BerkasPendaftaran;
use App\Models\Pendaftaran;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class UnggahBerkasPendaftaranService
{
    private const KODE_PAS_FOTO = ['pas_foto_calon', 'foto_ayah', 'foto_ibu'];
    private const MIME_DOKUMEN_DIIZINKAN = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];
    private const MIME_FOTO_DIIZINKAN = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function __construct(private readonly PengolahGambarPendaftaranService $pengolahGambar) {}

    public function simpan(
        Pendaftaran $pendaftaran,
        string $kodeBerkas,
        string $namaBerkas,
        UploadedFile $file,
        ?int $persyaratanId = null
    ): BerkasPendaftaran {
        // ASVS V5.2: Verifikasi magic-byte MIME type biner secara independen
        $realMime = $this->deteksiMimeBiner($file);
        $isPasFoto = in_array($kodeBerkas, self::KODE_PAS_FOTO, true);

        if ($isPasFoto) {
            if (! in_array($realMime, self::MIME_FOTO_DIIZINKAN, true)) {
                throw ValidationException::withMessages([
                    "berkas.{$kodeBerkas}" => "Berkas {$namaBerkas} harus berupa file foto gambar (JPG, PNG, atau WEBP).",
                ]);
            }
        } else {
            if (! in_array($realMime, self::MIME_DOKUMEN_DIIZINKAN, true)) {
                throw ValidationException::withMessages([
                    "berkas.{$kodeBerkas}" => "Berkas {$namaBerkas} harus berformat PDF, JPG, atau PNG.",
                ]);
            }
        }

        // ASVS V5.2: Perlakuan Kategori Khusus (Opsi 3)
        // 1. Dokumen PDF: Validasi struktur header dan simpan biner asli 100% tanpa alterasi
        if ($realMime === 'application/pdf') {
            $this->validasiStrukturPdf($file);
            $extension = 'pdf';
            $mimeType = 'application/pdf';
            $ukuran = $file->getSize() ?: 0;
            $isi = null; // disimpan via putFileAs
        } else {
            // 2. Foto / Gambar: Proses dengan kualitas tinggi tanpa kompresi agresif & bersihkan injeksi
            $hasil = $this->pengolahGambar->proses($file, $isPasFoto);
            $extension = $hasil['ekstensi'];
            $mimeType = $hasil['mime_type'];
            $ukuran = $hasil['ukuran'];
            $isi = $hasil['isi'];
        }

        // ASVS V5.3: Simpan file dengan nama UUID acak di storage privat (terisolasi dari web root)
        $folder = sprintf('ppdb/%s/%d/%s', $pendaftaran->periode?->tahun_ajaran ?: now()->format('Y'), $pendaftaran->getKey(), $kodeBerkas);
        $path = $folder . '/' . Str::uuid() . '.' . $extension;

        if ($isi !== null) {
            Storage::disk('local')->put($path, $isi);
        } else {
            Storage::disk('local')->putFileAs($folder, $file, basename($path));
        }

        // Bersihkan berkas lama jika sebelumnya sudah ada
        $existing = $pendaftaran->berkas()->where('kode_berkas', $kodeBerkas)->first();
        if ($existing) {
            if ($lokasiLama = $existing->lokasiPenyimpanan()) {
                Storage::disk('local')->delete($lokasiLama);
            }
            $existing->delete();
        }

        return $pendaftaran->berkas()->create([
            'persyaratan_pendaftaran_id' => $persyaratanId,
            'kode_berkas' => $kodeBerkas,
            'nama_berkas' => $namaBerkas,
            'path' => $path,
            'nama_asli' => $this->namaAsliAman($file->getClientOriginalName(), $extension),
            'mime_type' => $mimeType,
            'ukuran' => $ukuran,
        ]);
    }

    public function hapus(BerkasPendaftaran $berkas): void
    {
        if ($lokasi = $berkas->lokasiPenyimpanan()) {
            Storage::disk('local')->delete($lokasi);
        }
        $berkas->delete();
    }

    private function deteksiMimeBiner(UploadedFile $file): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? @finfo_file($finfo, $file->getRealPath()) : null;
        if ($finfo) {
            finfo_close($finfo);
        }

        if ($mime && ! in_array($mime, ['application/octet-stream', 'application/x-empty', 'text/plain'], true)) {
            return $mime;
        }

        return $file->getMimeType() ?: 'application/octet-stream';
    }

    private function validasiStrukturPdf(UploadedFile $file): void
    {
        $handle = @fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            throw new RuntimeException('File PDF tidak dapat dibaca.');
        }

        $header = (string) fread($handle, 5);
        fclose($handle);

        if ($file->getSize() > 0 && strlen($header) >= 4 && ! str_starts_with($header, '%PDF')) {
            throw ValidationException::withMessages([
                'berkas' => 'File PDF yang diunggah tidak memiliki struktur PDF yang valid.',
            ]);
        }
    }

    private function namaAsliAman(string $namaAsli, string $extension): string
    {
        $nama = basename($namaAsli);
        $nama = preg_replace('/[^\pL\pN .()_-]/u', ' ', $nama) ?? '';
        $nama = trim(preg_replace('/\s+/', ' ', $nama) ?? '');

        return Str::limit($nama !== '' ? $nama : "berkas.{$extension}", 150, '');
    }
}
