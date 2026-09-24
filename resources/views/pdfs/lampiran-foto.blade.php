<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Lampiran Foto</title>
    <style>
        @page {
            margin: 0;
            size: a4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        /* Kontainer terpusat rapi untuk cetak pas foto studio */
        .photo-sheet-center {
            width: 100%;
            padding-top: 40mm;
            text-align: center;
        }

        .photo-cluster {
            margin: 0 auto;
            border-collapse: collapse;
        }

        /* Foto Pas 3x4 cm (30mm x 40mm) Presisi */
        .photo-frame-3x4 {
            width: 30mm;
            height: 40mm;
            border: 0.6px solid #64748b;
            background: #f8fafc;
            overflow: hidden;
            text-align: center;
            display: inline-block;
        }
        .photo-frame-3x4 img {
            width: 100%;
            height: 100%;
            display: block;
        }

        .cell-gap {
            padding: 0 3mm;
            vertical-align: middle;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $fotoSiswa = $fotoBase64['pas_foto_calon'] ?? null;
        $fotoAyah = $fotoBase64['foto_ayah'] ?? null;
        $fotoIbu = $fotoBase64['foto_ibu'] ?? null;
    @endphp

    <div class="photo-sheet-center">
        <!-- Baris 1: 3 Lembar Pas Foto Calon Siswa (3x4 cm) Berjajar Rapat & Rapi -->
        <table class="photo-cluster" style="margin-bottom: 8mm;">
            <tr>
                <td class="cell-gap">
                    <div class="photo-frame-3x4">
                        @if ($fotoSiswa)
                            <img src="{{ $fotoSiswa }}" alt="Foto Siswa 1">
                        @endif
                    </div>
                </td>
                <td class="cell-gap">
                    <div class="photo-frame-3x4">
                        @if ($fotoSiswa)
                            <img src="{{ $fotoSiswa }}" alt="Foto Siswa 2">
                        @endif
                    </div>
                </td>
                <td class="cell-gap">
                    <div class="photo-frame-3x4">
                        @if ($fotoSiswa)
                            <img src="{{ $fotoSiswa }}" alt="Foto Siswa 3">
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- Baris 2: 2 Lembar Pas Foto Orang Tua (Ayah & Ibu) (3x4 cm) Berjajar Rapat & Rapi -->
        <table class="photo-cluster">
            <tr>
                <td class="cell-gap">
                    <div class="photo-frame-3x4">
                        @if ($fotoAyah)
                            <img src="{{ $fotoAyah }}" alt="Foto Ayah">
                        @endif
                    </div>
                </td>
                <td class="cell-gap">
                    <div class="photo-frame-3x4">
                        @if ($fotoIbu)
                            <img src="{{ $fotoIbu }}" alt="Foto Ibu">
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
