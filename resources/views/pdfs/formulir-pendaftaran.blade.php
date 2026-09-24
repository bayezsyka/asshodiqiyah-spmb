<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Formulir Pendaftaran - {{ $pendaftaran->nama_lengkap }}</title>
    <style>
        @page {
            margin: 6mm 10mm 5mm 10mm;
            size: a4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #0f172a;
            font-size: 7.2pt;
            line-height: 1.18;
            margin: 0;
            padding: 0;
        }

        /* Header / Kop */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-logo-cell {
            width: 84px;
            vertical-align: middle;
            text-align: center;
            padding-right: 4px;
        }
        .header-logo {
            height: 76px;
            width: auto;
            max-width: 80px;
            display: block;
            margin: 0 auto;
        }
        .header-info-cell {
            vertical-align: middle;
            padding-left: 8px;
            padding-right: 8px;
        }
        .header-box-cell {
            width: 146px;
            vertical-align: middle;
        }

        /* Titles */
        .title-main {
            font-size: 13.5pt;
            font-weight: bold;
            color: #0f3d64;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0;
            line-height: 1.15;
        }
        .title-school {
            font-size: 10.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 2px 0 0;
            line-height: 1.15;
        }
        .title-tags {
            font-size: 6.4pt;
            color: #64748b;
            font-weight: 500;
            margin: 3.5px 0 0;
            line-height: 1.3;
        }

        /* Jenjang & Meta Box */
        .jenjang-box {
            border: 1px solid #0f3d64;
            border-radius: 3px;
            padding: 2.5px 3.5px;
            background: #f8fafc;
        }
        .jenjang-title {
            font-weight: bold;
            text-transform: uppercase;
            color: #0f3d64;
            font-size: 6.2pt;
            text-align: center;
            letter-spacing: 0.3px;
            margin-bottom: 1.5px;
        }
        .jenjang-grid {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }
        .jenjang-grid th {
            border: 0.5px solid #0f3d64;
            background: #0f3d64;
            color: #ffffff;
            font-size: 5.8pt;
            padding: 1.2px 0;
            font-weight: bold;
        }
        .jenjang-grid td {
            border: 0.5px solid #0f3d64;
            font-size: 6.8pt;
            font-weight: bold;
            padding: 1px 0;
            background: #ffffff;
        }
        .jenjang-active {
            background: #dbeafe !important;
            color: #0f3d64 !important;
            font-size: 7.2pt !important;
        }

        .meta-mini-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
            font-size: 5.9pt;
        }
        .meta-mini-table td {
            padding: 1px 2.5px;
            border: 0.5px solid #cbd5e1;
        }
        .meta-mini-label {
            background: #f1f5f9;
            font-weight: bold;
            color: #475569;
            width: 48%;
        }
        .meta-mini-val {
            font-weight: bold;
            color: #0f172a;
        }

        .divider-thick {
            border-top: 1.8px solid #0f3d64;
            margin: 3px 0 1px 0;
        }
        .divider-thin {
            border-top: 0.6px solid #0f3d64;
            margin: 0 0 3px 0;
        }

        /* Section Styling */
        .section-header {
            background: #0f3d64;
            color: #ffffff;
            font-size: 7.3pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 2.2px 6px;
            margin-top: 3px;
            margin-bottom: 2px;
            border-radius: 2px;
        }

        /* Tables */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .form-table th {
            padding: 2.2px 4.5px;
            background: #f8fafc;
            border: 0.5px solid #cbd5e1;
            color: #334155;
            font-size: 6.9pt;
            text-align: left;
            vertical-align: middle;
            font-weight: bold;
        }
        .form-table td {
            padding: 2.2px 4.5px;
            border: 0.5px solid #cbd5e1;
            color: #0f172a;
            font-size: 7pt;
            vertical-align: middle;
        }

        /* Precision Checkbox styling */
        .cb-wrap {
            display: inline-block;
            vertical-align: middle;
            margin-right: 8px;
            white-space: nowrap;
        }
        .cb-img {
            width: 8.8px;
            height: 8.8px;
            vertical-align: -1px;
            margin-right: 3px;
            display: inline-block;
        }
        .cb-label {
            vertical-align: middle;
            font-size: 6.9pt;
            color: #0f172a;
        }

        /* Photo Box */
        .photo-frame {
            width: 30mm;
            height: 40mm;
            border: 0.8px solid #94a3b8;
            border-radius: 2px;
            background: #f8fafc;
            margin: 0 auto;
            overflow: hidden;
            text-align: center;
        }
        .photo-frame img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .photo-empty {
            padding-top: 14mm;
            font-size: 6.6pt;
            color: #94a3b8;
            line-height: 1.25;
        }
        .photo-title {
            font-size: 6.2pt;
            font-weight: bold;
            color: #475569;
            text-align: center;
            margin-top: 2px;
            text-transform: uppercase;
        }

        /* Side-by-side Parents Layout */
        .parents-table {
            width: 100%;
            border-collapse: collapse;
        }
        .parent-col {
            width: 50%;
            vertical-align: top;
        }
        .parent-col-left {
            padding-right: 3px;
        }
        .parent-col-right {
            padding-left: 3px;
        }

        /* Section A 2-Col Layout */
        .layout-two-col {
            width: 100%;
            border-collapse: collapse;
        }
        .layout-main-col {
            vertical-align: top;
            padding-right: 5px;
        }
        .layout-side-col {
            width: 31mm;
            vertical-align: top;
            text-align: center;
        }

        /* Signatures */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .sig-col {
            width: 50%;
            text-align: center;
            font-size: 7.6pt;
            vertical-align: top;
            padding: 0 10px;
        }
        .sig-space {
            height: 100px;
        }
        .sig-line {
            border-top: 1px solid #1e293b;
            font-weight: bold;
            padding-top: 3px;
            color: #0f172a;
            font-size: 7.6pt;
            width: 50%;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    @php
        $gabungkan = static fn (array $nilai) => implode(', ', array_filter($nilai, static fn ($isi) => filled($isi))) ?: '-';
        $tempatTanggal = static fn ($tempat, $nilai) => $gabungkan([$tempat, $nilai ? $nilai->format('d/m/Y') : null]);

        $jarak = $pendaftaran->jarak_rumah_km
            ? $pendaftaran->jarak_rumah_km.' km'
            : ($pendaftaran->jarak_rumah_meter ? $pendaftaran->jarak_rumah_meter.' m' : '-');

        $waktu = array_filter([
            $pendaftaran->waktu_tempuh_jam ? $pendaftaran->waktu_tempuh_jam.' jam' : null,
            $pendaftaran->waktu_tempuh_menit ? $pendaftaran->waktu_tempuh_menit.' mnt' : null,
        ]);
        $waktu = $waktu !== [] ? implode(', ', $waktu) : '-';

        // Precision Centered Checkbox Generator (Pixel-Perfect Alignment)
        $bikinCb = static function (bool $checked): string {
            $size = 28;
            $im = imagecreatetruecolor($size, $size);
            imagesavealpha($im, true);
            $trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
            imagefill($im, 0, 0, $trans);
            $border = imagecolorallocate($im, 71, 85, 105);
            $bg = imagecolorallocate($im, 255, 255, 255);
            imagefilledrectangle($im, 1, 1, $size - 2, $size - 2, $bg);
            imagerectangle($im, 1, 1, $size - 2, $size - 2, $border);
            imagerectangle($im, 2, 2, $size - 3, $size - 3, $border);
            if ($checked) {
                $navy = imagecolorallocate($im, 15, 61, 100);
                imagesetthickness($im, 3);
                imageline($im, 6, 14, 11, 20, $navy);
                imageline($im, 11, 20, 22, 7, $navy);
            }
            ob_start();
            imagepng($im);
            $res = 'data:image/png;base64,'.base64_encode((string) ob_get_clean());
            imagedestroy($im);
            return $res;
        };
        $cbChecked = $bikinCb(true);
        $cbUnchecked = $bikinCb(false);

        $cb = function (bool $checked, string $label) use ($cbChecked, $cbUnchecked) {
            $src = $checked ? $cbChecked : $cbUnchecked;
            return '<span class="cb-wrap"><img class="cb-img" src="'.$src.'" alt="'.($checked ? '[v]' : '[ ]').'"><span class="cb-label">'.$label.'</span></span>';
        };

        // Perhitungan Usia Saat Mendaftar (Safe Integer)
        $diffBulanTotal = $pendaftaran->tanggal_lahir ? (int) abs($pendaftaran->tanggal_lahir->diffInMonths($pendaftaran->created_at ?? now())) : null;
        $usiaTahun = $diffBulanTotal !== null ? (int) floor($diffBulanTotal / 12) : null;
        $usiaBulan = $diffBulanTotal !== null ? ($diffBulanTotal % 12) : null;
        $teksUsia = ($usiaTahun !== null) ? "{$usiaTahun} Thn {$usiaBulan} Bln" : '-';

        $fotoSiswaPath = $fotoBase64['pas_foto_calon'] ?? null;
        $kodeJenjangAktif = strtoupper((string) ($pendaftaran->jenjang?->kode ?? ''));
    @endphp

    <!-- Header & Kop -->
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                <img class="header-logo" src="{{ public_path('assets/asshodiqiyah/logo.webp') }}" alt="Logo Asshodiqiyah">
            </td>
            <td class="header-info-cell">
                <div class="title-main">FORMULIR PENDAFTARAN</div>
                <div class="title-school">PONDOK PESANTREN ASSHODIQIYAH KALIGAWE</div>
                <div class="title-tags">
                    #SD IT #SMP IT #MTs #MA #SMK
                </div>
            </td>
            <td class="header-box-cell">
                <div class="jenjang-box">
                    <div class="jenjang-title">Jenjang Pendidikan</div>
                    <table class="jenjang-grid">
                        <tr>
                            <th>SD IT</th>
                            <th>SMP IT</th>
                            <th>MTs</th>
                            <th>MA</th>
                            <th>SMK</th>
                        </tr>
                        <tr>
                            <td class="{{ $kodeJenjangAktif === 'SDIT' ? 'jenjang-active' : '' }}">{!! $kodeJenjangAktif === 'SDIT' ? '&#10003;' : '&nbsp;' !!}</td>
                            <td class="{{ $kodeJenjangAktif === 'SMPIT' ? 'jenjang-active' : '' }}">{!! $kodeJenjangAktif === 'SMPIT' ? '&#10003;' : '&nbsp;' !!}</td>
                            <td class="{{ $kodeJenjangAktif === 'MTS' ? 'jenjang-active' : '' }}">{!! $kodeJenjangAktif === 'MTS' ? '&#10003;' : '&nbsp;' !!}</td>
                            <td class="{{ $kodeJenjangAktif === 'MA' ? 'jenjang-active' : '' }}">{!! $kodeJenjangAktif === 'MA' ? '&#10003;' : '&nbsp;' !!}</td>
                            <td class="{{ $kodeJenjangAktif === 'SMK' ? 'jenjang-active' : '' }}">{!! $kodeJenjangAktif === 'SMK' ? '&#10003;' : '&nbsp;' !!}</td>
                        </tr>
                    </table>
                    <table class="meta-mini-table">
                        <tr>
                            <td class="meta-mini-label">Thn Pelajaran</td>
                            <td class="meta-mini-val">{{ $pendaftaran->periode?->tahun_ajaran ?? '2026/2027' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-mini-label">Emergency 1</td>
                            <td class="meta-mini-val">{{ $pendaftaran->nomor_telepon_darurat_1 ?: ($pendaftaran->nomor_telepon_ayah ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td class="meta-mini-label">Emergency 2</td>
                            <td class="meta-mini-val">{{ $pendaftaran->nomor_telepon_darurat_2 ?: ($pendaftaran->nomor_telepon_ibu ?: '-') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider-thick"></div>
    <div class="divider-thin"></div>

    <!-- A. DATA CALON SISWA -->
    <div class="section-header">A. Data Calon Siswa</div>
    <table class="layout-two-col">
        <tr>
            <td class="layout-main-col">
                <table class="form-table">
                    <tr>
                        <th style="width: 23%;">Nama Lengkap</th>
                        <td colspan="3" style="font-weight: bold; color: #0f3d64; font-size: 7.5pt;">{{ $pendaftaran->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <th>Nama Panggilan</th>
                        <td style="width: 27%;">{{ $pendaftaran->nama_panggilan ?: '-' }}</td>
                        <th style="width: 21%;">Jenis Kelamin</th>
                        <td>
                            {!! $cb($pendaftaran->jenis_kelamin === 'L', 'Laki-laki') !!}
                            {!! $cb($pendaftaran->jenis_kelamin === 'P', 'Perempuan') !!}
                        </td>
                    </tr>
                    <tr>
                        <th>Tempat, Tgl Lahir</th>
                        <td>{{ $tempatTanggal($pendaftaran->tempat_lahir, $pendaftaran->tanggal_lahir) }}</td>
                        <th>Usia Mendaftar</th>
                        <td>{{ $teksUsia }}</td>
                    </tr>
                    <tr>
                        <th>NIK</th>
                        <td>{{ $pendaftaran->nik ?: '-' }}</td>
                        <th>NISN</th>
                        <td>{{ $pendaftaran->nisn ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Agama</th>
                        <td>{{ $pendaftaran->agama_calon }}</td>
                        <th>Suku Bangsa</th>
                        <td>{{ $pendaftaran->suku_bangsa_calon ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kewarganegaraan</th>
                        <td colspan="3">
                            {!! $cb($pendaftaran->kewarganegaraan === 'WNI', 'WNI') !!}
                            {!! $cb($pendaftaran->kewarganegaraan === 'WNA', 'WNA') !!}
                            {!! $cb(!in_array($pendaftaran->kewarganegaraan, ['WNI', 'WNA']), 'Keturunan/Campuran') !!}
                        </td>
                    </tr>
                    <tr>
                        <th>Jumlah Saudara</th>
                        <td colspan="3">
                            Kdg: <b>{{ $pendaftaran->jumlah_saudara_kandung ?? 0 }}</b> | 
                            Tiri: <b>{{ $pendaftaran->jumlah_saudara_tiri ?? 0 }}</b> | 
                            Angkat: <b>{{ $pendaftaran->jumlah_saudara_angkat ?? 0 }}</b> &nbsp;&mdash;&nbsp;
                            Anak Ke: <b>{{ $pendaftaran->anak_ke ?: '-' }}</b> dari <b>{{ $pendaftaran->jumlah_bersaudara ?: '-' }}</b> Bersdr
                        </td>
                    </tr>
                    <tr>
                        <th>Alamat Domisili</th>
                        <td colspan="3">{{ $pendaftaran->alamat_domisili ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>No. Telepon / HP</th>
                        <td>{{ $pendaftaran->nomor_telepon_calon ?: '-' }}</td>
                        <th>Tinggal Bersama</th>
                        <td>
                            {!! $cb($pendaftaran->tinggal_bersama === 'orang_tua', 'Orangtua') !!}
                            {!! $cb($pendaftaran->tinggal_bersama === 'famili', 'Famili') !!}
                            {!! $cb($pendaftaran->tinggal_bersama === 'lainnya', 'Lain-lain') !!}
                        </td>
                    </tr>
                    <tr>
                        <th>Jarak ke Sekolah</th>
                        <td>{{ $jarak }}</td>
                        <th>Waktu Tempuh</th>
                        <td>{{ $waktu }}</td>
                    </tr>
                    <tr>
                        <th>Asal Sekolah</th>
                        <td colspan="3">{{ $pendaftaran->asal_sekolah ?: '-' }} {{ $pendaftaran->alamat_sekolah_asal ? '('.$pendaftaran->alamat_sekolah_asal.')' : '' }}</td>
                    </tr>
                </table>
            </td>
            <td class="layout-side-col">
                <div class="photo-frame">
                    @if ($fotoSiswaPath)
                        <img src="{{ $fotoSiswaPath }}" alt="Pas Foto Siswa">
                    @else
                        <div class="photo-empty">Pas Foto Calon<br>3 x 4 cm</div>
                    @endif
                </div>
                <div class="photo-title">Pas Foto 3x4</div>
            </td>
        </tr>
    </table>

    <!-- B & C: DATA ORANG TUA (SIDE BY SIDE) -->
    <table class="parents-table">
        <tr>
            <!-- DATA AYAH -->
            <td class="parent-col parent-col-left">
                <div class="section-header">B. Data Ayah Kandung / Wali</div>
                <table class="form-table">
                    <tr>
                        <th style="width: 32%;">Nama Ayah</th>
                        <td style="font-weight: bold; color: #0f3d64;">{{ $pendaftaran->nama_ayah ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tempat, Tgl Lahir</th>
                        <td>{{ $tempatTanggal($pendaftaran->tempat_lahir_ayah, $pendaftaran->tanggal_lahir_ayah) }}</td>
                    </tr>
                    <tr>
                        <th>Agama</th>
                        <td>{{ $pendaftaran->agama_ayah ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Suku Bangsa</th>
                        <td>{{ $pendaftaran->suku_bangsa_ayah ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Pendidikan</th>
                        <td>
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ayah), 'S3'), 'S3') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ayah), 'S2'), 'S2') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ayah), 'S1'), 'S1') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ayah), 'DIP'), 'Dip') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ayah), 'SM'), 'SMA') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ayah), 'SMP'), 'SMP') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ayah), 'SD'), 'SD') !!}
                        </td>
                    </tr>
                    <tr>
                        <th>Pekerjaan</th>
                        <td>{{ $pendaftaran->pekerjaan_ayah ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Penghasilan / Bln</th>
                        <td>{{ $pendaftaran->penghasilan_ayah ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>No. Telepon / HP</th>
                        <td>{{ $pendaftaran->nomor_telepon_ayah ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat Lengkap</th>
                        <td>{{ $pendaftaran->alamat_ayah ?: ($pendaftaran->alamat_domisili ?: '-') }}</td>
                    </tr>
                </table>
            </td>

            <!-- DATA IBU -->
            <td class="parent-col parent-col-right">
                <div class="section-header">C. Data Ibu Kandung / Wali</div>
                <table class="form-table">
                    <tr>
                        <th style="width: 32%;">Nama Ibu</th>
                        <td style="font-weight: bold; color: #0f3d64;">{{ $pendaftaran->nama_ibu ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tempat, Tgl Lahir</th>
                        <td>{{ $tempatTanggal($pendaftaran->tempat_lahir_ibu, $pendaftaran->tanggal_lahir_ibu) }}</td>
                    </tr>
                    <tr>
                        <th>Agama</th>
                        <td>{{ $pendaftaran->agama_ibu ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Suku Bangsa</th>
                        <td>{{ $pendaftaran->suku_bangsa_ibu ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Pendidikan</th>
                        <td>
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ibu), 'S3'), 'S3') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ibu), 'S2'), 'S2') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ibu), 'S1'), 'S1') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ibu), 'DIP'), 'Dip') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ibu), 'SM'), 'SMA') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ibu), 'SMP'), 'SMP') !!}
                            {!! $cb(str_contains(strtoupper((string)$pendaftaran->pendidikan_ibu), 'SD'), 'SD') !!}
                        </td>
                    </tr>
                    <tr>
                        <th>Pekerjaan</th>
                        <td>{{ $pendaftaran->pekerjaan_ibu ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Penghasilan / Bln</th>
                        <td>{{ $pendaftaran->penghasilan_ibu ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>No. Telepon / HP</th>
                        <td>{{ $pendaftaran->nomor_telepon_ibu ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Alamat Lengkap</th>
                        <td>{{ $pendaftaran->alamat_ibu ?: ($pendaftaran->alamat_domisili ?: '-') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tanda Tangan -->
    <div style="text-align: right; font-size: 7.2pt; color: #1e293b; margin-top: 6px;">
        Semarang, {{ ($pendaftaran->created_at ?? now())->isoFormat('D MMMM Y') }}
    </div>

    <table class="sig-table">
        <tr>
            <td class="sig-col">
                <span style="font-weight: bold; color: #334155;">Orang Tua / Wali (Ayah),</span>
                <div class="sig-space"></div>
                <div class="sig-line">{{ $pendaftaran->nama_ayah ?: 'Ayah Kandung / Wali' }}</div>
            </td>
            <td class="sig-col">
                <span style="font-weight: bold; color: #334155;">Orang Tua / Wali (Ibu),</span>
                <div class="sig-space"></div>
                <div class="sig-line">{{ $pendaftaran->nama_ibu ?: 'Ibu Kandung / Wali' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
