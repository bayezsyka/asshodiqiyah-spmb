<?php

namespace Database\Seeders;

use App\Models\JenjangPendaftaran;
use App\Models\PeriodePpdb;
use App\Models\PersyaratanPendaftaran;
use App\Models\UnitPendidikan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $unit = collect([
            ['kode' => 'SDIT', 'nama' => 'SD IT Asshodiqiyah', 'urutan' => 1],
            ['kode' => 'SMPIT', 'nama' => 'SMP IT Asshodiqiyah', 'urutan' => 2],
            ['kode' => 'MTS', 'nama' => 'MTs Asshodiqiyah', 'urutan' => 3],
            ['kode' => 'MA', 'nama' => 'MA Asshodiqiyah', 'urutan' => 4],
            ['kode' => 'SMK', 'nama' => 'SMK Asshodiqiyah', 'urutan' => 5],
        ])->mapWithKeys(fn (array $item) => [$item['kode'] => UnitPendidikan::updateOrCreate(['kode' => $item['kode']], [...$item, 'status_aktif' => true])]);

        JenjangPendaftaran::query()->whereIn('kode', ['TPA', 'KB', 'TK', 'SD', 'SMP', 'SMA'])->update(['status_aktif' => false]);
        collect([
            ['kode' => 'SDIT', 'nama' => 'SD IT Asshodiqiyah', 'kelompok' => 'formal', 'urutan' => 1],
            ['kode' => 'SMPIT', 'nama' => 'SMP IT Asshodiqiyah', 'kelompok' => 'formal', 'urutan' => 2],
            ['kode' => 'MTS', 'nama' => 'MTs Asshodiqiyah', 'kelompok' => 'formal', 'urutan' => 3],
            ['kode' => 'MA', 'nama' => 'MA Asshodiqiyah', 'kelompok' => 'formal', 'urutan' => 4],
            ['kode' => 'SMK', 'nama' => 'SMK Asshodiqiyah', 'kelompok' => 'formal', 'urutan' => 5],
        ])->each(fn (array $item) => JenjangPendaftaran::updateOrCreate(['kode' => $item['kode']], [...$item, 'unit_pendidikan_id' => $unit[$item['kode']]->id, 'status_aktif' => true]));
        PeriodePpdb::firstOrCreate(['tahun_ajaran' => '2026/2027'], ['nama' => 'Pendaftaran 2026/2027', 'status_aktif' => true, 'informasi' => 'Informasi pendaftaran formal dapat diperbarui oleh admin.']);
        foreach ([
            ['pas_foto_calon', 'Pas foto calon siswa', 'Foto 3 x 4 berwarna.'],
            ['foto_ayah', 'Foto ayah', 'Foto 3 x 4 berwarna.'],
            ['foto_ibu', 'Foto ibu', 'Foto 3 x 4 berwarna.'],
            ['akte_kelahiran', 'Akta kelahiran calon siswa', 'Salinan akta kelahiran.'],
            ['surat_nikah_orang_tua', 'Surat nikah orang tua', 'Salinan surat nikah orang tua.'],
            ['kartu_keluarga', 'Kartu keluarga', 'Salinan kartu keluarga.'],
        ] as $i => $item) {
            PersyaratanPendaftaran::firstOrCreate(
                ['kode' => $item[0], 'periode_ppdb_id' => null, 'jenjang_pendaftaran_id' => null],
                ['nama' => $item[1], 'keterangan' => $item[2], 'wajib' => true, 'urutan' => $i + 1, 'status_aktif' => true],
            );
        }
        $this->call([
            AdminUserSeeder::class,
        ]);
    }
}
