<?php

namespace App\Services\Ekspor;

use App\Models\BerkasPendaftaran;
use App\Models\Pendaftaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Throwable;

final class PaketPendaftaranPdfService
{
    private const KODE_FOTO_KELUARGA = ['pas_foto_calon', 'foto_ayah', 'foto_ibu'];
    private const SISI_MAKS_DOKUMEN_PDF = 1800; // Optimal 200-250 DPI untuk halaman penuh A4

    public function buat(Pendaftaran $pendaftaran): string
    {
        $pendaftaran->loadMissing(['jenjang', 'periode', 'berkas']);
        $formulirSementara = tempnam(sys_get_temp_dir(), 'spmb_formulir_');

        if ($formulirSementara === false) {
            throw new \RuntimeException('Paket PDF tidak dapat disiapkan.');
        }

        try {
            file_put_contents($formulirSementara, $this->buatFormulir($pendaftaran));

            $pdf = new Fpdi('P', 'mm', 'A4');
            $pdf->SetAutoPageBreak(false);
            $this->tambahkanPdf($pdf, $formulirSementara);

            $berkas = $pendaftaran->berkas->sortBy('id');
            $this->tambahkanFotoKeluarga($pdf, $berkas->whereIn('kode_berkas', self::KODE_FOTO_KELUARGA), $pendaftaran);

            foreach ($berkas->reject(fn (BerkasPendaftaran $berkas) => in_array($berkas->kode_berkas, self::KODE_FOTO_KELUARGA, true)) as $berkas) {
                $this->tambahkanBerkas($pdf, $berkas);
            }

            return $pdf->Output('S');
        } finally {
            @unlink($formulirSementara);
        }
    }

    public function buatFormulir(Pendaftaran $pendaftaran): string
    {
        $pendaftaran->loadMissing(['jenjang', 'periode', 'berkas']);
        $fotoBase64 = $this->siapkanSemuaFotoBase64($pendaftaran);

        return Pdf::loadView('pdfs.formulir-pendaftaran', [
            'pendaftaran' => $pendaftaran,
            'fotoBase64' => $fotoBase64,
        ])
            ->setPaper('a4')
            ->output();
    }

    public function buatLampiranFoto(Pendaftaran $pendaftaran): string
    {
        $pendaftaran->loadMissing(['jenjang', 'periode', 'berkas']);
        $fotoBase64 = $this->siapkanSemuaFotoBase64($pendaftaran);

        return Pdf::loadView('pdfs.lampiran-foto', [
            'pendaftaran' => $pendaftaran,
            'fotoBase64' => $fotoBase64,
        ])
            ->setPaper('a4')
            ->output();
    }

    /** @return array<string, string|null> */
    public function siapkanSemuaFotoBase64(Pendaftaran $pendaftaran): array
    {
        $hasil = [];
        foreach (self::KODE_FOTO_KELUARGA as $kode) {
            $hasil[$kode] = $this->siapkanFotoBase64($pendaftaran, $kode);
        }

        return $hasil;
    }

    public function siapkanFotoBase64(Pendaftaran $pendaftaran, string $kodeBerkas): ?string
    {
        $berkas = $pendaftaran->berkas->firstWhere('kode_berkas', $kodeBerkas);
        if (! $berkas || ! $berkas->lokasiPenyimpanan()) {
            return null;
        }

        $fullPath = Storage::disk('local')->path($berkas->lokasiPenyimpanan());
        if (! is_file($fullPath)) {
            return null;
        }

        $raw = @file_get_contents($fullPath);
        if ($raw === false) {
            return null;
        }

        $sumber = @imagecreatefromstring($raw);
        if ($sumber === false) {
            return null;
        }

        // Koreksi orientasi EXIF jika ada
        if (function_exists('exif_read_data')) {
            try {
                $exif = @exif_read_data($fullPath);
                if (! empty($exif['Orientation'])) {
                    switch ((int) $exif['Orientation']) {
                        case 3:
                            $rotated = imagerotate($sumber, 180, 0);
                            if ($rotated !== false) {
                                imagedestroy($sumber);
                                $sumber = $rotated;
                            }
                            break;
                        case 6:
                            $rotated = imagerotate($sumber, -90, 0);
                            if ($rotated !== false) {
                                imagedestroy($sumber);
                                $sumber = $rotated;
                            }
                            break;
                        case 8:
                            $rotated = imagerotate($sumber, 90, 0);
                            if ($rotated !== false) {
                                imagedestroy($sumber);
                                $sumber = $rotated;
                            }
                            break;
                    }
                }
            } catch (Throwable) {
                // Abaikan jika EXIF tidak tersedia
            }
        }

        $lebarAwal = imagesx($sumber);
        $tinggiAwal = imagesy($sumber);

        // Optimal 450px lebar (setara 380 DPI untuk cetak tajam 3x4 cm)
        $targetLebar = 450;
        $rasio = min(1, $targetLebar / $lebarAwal);
        $lebar = max(1, (int) round($lebarAwal * $rasio));
        $tinggi = max(1, (int) round($tinggiAwal * $rasio));

        $kanvas = imagecreatetruecolor($lebar, $tinggi);
        $putih = imagecolorallocate($kanvas, 255, 255, 255);
        if ($putih !== false) {
            imagefill($kanvas, 0, 0, $putih);
        }
        imagecopyresampled($kanvas, $sumber, 0, 0, 0, 0, $lebar, $tinggi, $lebarAwal, $tinggiAwal);
        imagedestroy($sumber);

        ob_start();
        imagejpeg($kanvas, null, 92);
        $biner = (string) ob_get_clean();
        imagedestroy($kanvas);

        return 'data:image/jpeg;base64,'.base64_encode($biner);
    }

    private function tambahkanFotoKeluarga(Fpdi $pdf, Collection $berkas, Pendaftaran $pendaftaran): void
    {
        $adaFoto = $berkas->contains(fn (BerkasPendaftaran $b) => in_array($b->kode_berkas, self::KODE_FOTO_KELUARGA, true));
        if (! $adaFoto) {
            return;
        }

        $lampiranSementara = tempnam(sys_get_temp_dir(), 'lampiran_foto_').'.pdf';

        try {
            file_put_contents($lampiranSementara, $this->buatLampiranFoto($pendaftaran));
            $this->tambahkanPdf($pdf, $lampiranSementara);
        } catch (Throwable) {
            // Jika lampiran foto gagal dirender, lewati agar ekspor berkas utama tetap berjalan
        } finally {
            @unlink($lampiranSementara);
        }
    }

    private function tambahkanBerkas(Fpdi $pdf, BerkasPendaftaran $berkas): void
    {
        $lokasi = $berkas->lokasiPenyimpanan();
        if ($lokasi === null) {
            return;
        }

        $path = Storage::disk('local')->path($lokasi);
        if (! is_file($path)) {
            return;
        }

        try {
            if ($berkas->mime_type === 'application/pdf') {
                $this->tambahkanPdf($pdf, $path);

                return;
            }

            $gambar = $this->siapkanGambar($berkas);
            try {
                $pdf->AddPage('P', 'A4');
                $this->tempatkanGambar($pdf, $gambar, 0, 0, 210, 297, true);
            } finally {
                @unlink($gambar['path']);
            }
        } catch (Throwable) {
            // Abaikan kegagalan berkas individual agar tidak menghentikan ekspor keseluruhan
        }
    }

    /** @return array{path: string, lebar: int, tinggi: int} */
    private function siapkanGambar(BerkasPendaftaran $berkas): array
    {
        $lokasi = $berkas->lokasiPenyimpanan();
        if ($lokasi === null) {
            throw new \RuntimeException('Lokasi gambar tidak tersedia.');
        }

        $fullPath = Storage::disk('local')->path($lokasi);
        $raw = @file_get_contents($fullPath);
        if ($raw === false) {
            throw new \RuntimeException('Berkas tidak dapat dibaca.');
        }

        $gambar = @imagecreatefromstring($raw);
        if ($gambar === false) {
            throw new \RuntimeException('Gambar tidak dapat diproses.');
        }

        // Koreksi orientasi EXIF jika tersedia
        if (function_exists('exif_read_data')) {
            try {
                $exif = @exif_read_data($fullPath);
                if (! empty($exif['Orientation'])) {
                    switch ((int) $exif['Orientation']) {
                        case 3:
                            $rotated = imagerotate($gambar, 180, 0);
                            if ($rotated !== false) {
                                imagedestroy($gambar);
                                $gambar = $rotated;
                            }
                            break;
                        case 6:
                            $rotated = imagerotate($gambar, -90, 0);
                            if ($rotated !== false) {
                                imagedestroy($gambar);
                                $gambar = $rotated;
                            }
                            break;
                        case 8:
                            $rotated = imagerotate($gambar, 90, 0);
                            if ($rotated !== false) {
                                imagedestroy($gambar);
                                $gambar = $rotated;
                            }
                            break;
                    }
                }
            } catch (Throwable) {
                // Abaikan kesalahan pembacaan EXIF
            }
        }

        $lebarAwal = imagesx($gambar);
        $tinggiAwal = imagesy($gambar);

        // Resample ke resolusi A4 optimal (maks 1800px) agar PDF stream ringan namun tetap tajam
        $rasio = min(1, self::SISI_MAKS_DOKUMEN_PDF / max($lebarAwal, $tinggiAwal));
        $lebar = max(1, (int) round($lebarAwal * $rasio));
        $tinggi = max(1, (int) round($tinggiAwal * $rasio));

        $kanvas = imagecreatetruecolor($lebar, $tinggi);
        $putih = imagecolorallocate($kanvas, 255, 255, 255);
        if ($putih !== false) {
            imagefill($kanvas, 0, 0, $putih);
        }
        imagecopyresampled($kanvas, $gambar, 0, 0, 0, 0, $lebar, $tinggi, $lebarAwal, $tinggiAwal);
        imagedestroy($gambar);

        $path = tempnam(sys_get_temp_dir(), 'spmb_gambar_');
        if ($path === false) {
            imagedestroy($kanvas);
            throw new \RuntimeException('Gambar tidak dapat disiapkan.');
        }

        try {
            imagejpeg($kanvas, $path, 90);

            return ['path' => $path, 'lebar' => $lebar, 'tinggi' => $tinggi];
        } catch (Throwable $exception) {
            @unlink($path);
            throw $exception;
        } finally {
            imagedestroy($kanvas);
        }
    }

    /** @param array{path: string, lebar: int, tinggi: int} $gambar */
    private function tempatkanGambar(Fpdi $pdf, array $gambar, float $x, float $y, float $lebarArea, float $tinggiArea, bool $izinkanRotasiOtomatis = true): void
    {
        $rasioNormal = min($lebarArea / $gambar['lebar'], $tinggiArea / $gambar['tinggi']);
        $rasioPutar = min($lebarArea / $gambar['tinggi'], $tinggiArea / $gambar['lebar']);

        $putar = $izinkanRotasiOtomatis && ($rasioPutar > $rasioNormal);

        $lebarSumber = $putar ? $gambar['tinggi'] : $gambar['lebar'];
        $tinggiSumber = $putar ? $gambar['lebar'] : $gambar['tinggi'];
        $rasio = $putar ? $rasioPutar : $rasioNormal;
        $lebar = $lebarSumber * $rasio;
        $tinggi = $tinggiSumber * $rasio;
        $path = $gambar['path'];
        $hasilPutar = null;

        try {
            if ($putar) {
                $sumber = imagecreatefromjpeg($gambar['path']);
                if ($sumber !== false) {
                    try {
                        $putarGambar = imagerotate($sumber, 90, imagecolorallocate($sumber, 255, 255, 255) ?: 0);
                        if ($putarGambar !== false) {
                            $hasilPutar = tempnam(sys_get_temp_dir(), 'spmb_putar_');
                            if ($hasilPutar !== false) {
                                imagejpeg($putarGambar, $hasilPutar, 90);
                                $path = $hasilPutar;
                            }
                            imagedestroy($putarGambar);
                        }
                    } finally {
                        imagedestroy($sumber);
                    }
                }
            }

            $posX = $x + (($lebarArea - $lebar) / 2);
            $posY = $y + (($tinggiArea - $tinggi) / 2);
            $pdf->Image($path, $posX, $posY, $lebar, $tinggi, 'JPG');
        } finally {
            if ($hasilPutar !== null) {
                @unlink($hasilPutar);
            }
        }
    }

    private function tambahkanPdf(Fpdi $pdf, string $path): void
    {
        $jumlahHalaman = $pdf->setSourceFile($path);

        for ($halaman = 1; $halaman <= $jumlahHalaman; $halaman++) {
            $template = $pdf->importPage($halaman);
            $ukuran = $pdf->getTemplateSize($template);
            $orientasi = $ukuran['width'] > $ukuran['height'] ? 'L' : 'P';

            $pdf->AddPage($orientasi, [$ukuran['width'], $ukuran['height']]);
            $pdf->useTemplate($template);
        }
    }
}
