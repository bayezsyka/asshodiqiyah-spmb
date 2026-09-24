<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class PengolahGambarPendaftaranService
{
    // ASVS V5.2: Batas resolusi tinggi yang aman untuk cetak fisik & arsip buku induk
    private const SISI_TERPANJANG_FOTO_MAKSIMAL = 2400; // Cukup untuk cetak foto tajam 300 DPI
    private const SISI_TERPANJANG_DOKUMEN_MAKSIMAL = 2560; // 2.5K untuk keterbacaan teks kecil Akta/KK
    private const KUALITAS_JPEG_FOTO = 94; // Kualitas tinggi menjaga detail pori & warna kulit natural
    private const KUALITAS_JPEG_DOKUMEN = 92; // Kualitas tinggi menjaga ketajaman huruf dokumen

    /**
     * Memproses gambar dengan mempertahankan format universal (PNG lossless atau High-Quality JPEG)
     * tanpa melakukan kompresi agresif atau konversi paksa ke WebP.
     *
     * @return array{isi: string, ekstensi: string, mime_type: string, ukuran: int}
     */
    public function proses(UploadedFile $file, bool $isPasFoto = false): array
    {
        $realPath = $file->getRealPath();
        $binary = (string) @file_get_contents($realPath);

        $sumber = @imagecreatefromstring($binary);
        if ($sumber === false) {
            throw new RuntimeException('File gambar tidak valid atau tidak dapat dibaca.');
        }

        // 1. Perbaiki orientasi gambar secara otomatis berdasarkan EXIF kamera ponsel
        $sumber = $this->perbaikiOrientasiExif($sumber, $realPath);

        $lebarAwal = imagesx($sumber);
        $tinggiAwal = imagesy($sumber);

        $maksSisi = $isPasFoto ? self::SISI_TERPANJANG_FOTO_MAKSIMAL : self::SISI_TERPANJANG_DOKUMEN_MAKSIMAL;
        $rasio = min(1, $maksSisi / max($lebarAwal, $tinggiAwal));

        $lebar = max(1, (int) round($lebarAwal * $rasio));
        $tinggi = max(1, (int) round($tinggiAwal * $rasio));

        // 2. ASVS V5.2 & V1.2: Render ulang canvas gambar untuk membersihkan script polyglot/payload EXIF
        $gambar = imagecreatetruecolor($lebar, $tinggi);

        $isPng = strtolower((string) $file->getClientOriginalExtension()) === 'png' || $file->getMimeType() === 'image/png';

        if ($isPng) {
            imagealphablending($gambar, false);
            imagesavealpha($gambar, true);
            $transparent = imagecolorallocatealpha($gambar, 0, 0, 0, 127);
            if ($transparent !== false) {
                imagefill($gambar, 0, 0, $transparent);
            }
        } else {
            // Latar belakang netral putih untuk foto/dokumen JPEG
            $putih = imagecolorallocate($gambar, 255, 255, 255);
            if ($putih !== false) {
                imagefill($gambar, 0, 0, $putih);
            }
        }

        imagecopyresampled($gambar, $sumber, 0, 0, 0, 0, $lebar, $tinggi, $lebarAwal, $tinggiAwal);
        imagedestroy($sumber);

        // 3. Simpan ke buffer dengan format universal berkualitas tinggi
        ob_start();
        if ($isPng) {
            imagepng($gambar, null, 6);
            $ekstensi = 'png';
            $mimeType = 'image/png';
        } else {
            $kualitas = $isPasFoto ? self::KUALITAS_JPEG_FOTO : self::KUALITAS_JPEG_DOKUMEN;
            imagejpeg($gambar, null, $kualitas);
            $ekstensi = 'jpg';
            $mimeType = 'image/jpeg';
        }
        $hasil = (string) ob_get_clean();
        imagedestroy($gambar);

        return [
            'isi' => $hasil,
            'ekstensi' => $ekstensi,
            'mime_type' => $mimeType,
            'ukuran' => strlen($hasil),
        ];
    }

    /**
     * @param resource|\GdImage $gambar
     * @return resource|\GdImage
     */
    private function perbaikiOrientasiExif($gambar, string $filePath)
    {
        if (! function_exists('exif_read_data')) {
            return $gambar;
        }

        try {
            $exif = @exif_read_data($filePath);
            if (! empty($exif['Orientation'])) {
                switch ((int) $exif['Orientation']) {
                    case 3:
                        $rotated = imagerotate($gambar, 180, 0);
                        if ($rotated !== false) {
                            imagedestroy($gambar);
                            return $rotated;
                        }
                        break;
                    case 6:
                        $rotated = imagerotate($gambar, -90, 0);
                        if ($rotated !== false) {
                            imagedestroy($gambar);
                            return $rotated;
                        }
                        break;
                    case 8:
                        $rotated = imagerotate($gambar, 90, 0);
                        if ($rotated !== false) {
                            imagedestroy($gambar);
                            return $rotated;
                        }
                        break;
                }
            }
        } catch (\Throwable) {
            // Abaikan jika EXIF tidak dapat dibaca
        }

        return $gambar;
    }
}
