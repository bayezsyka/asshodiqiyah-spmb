<?php

namespace Database\Seeders;

use App\Enums\PeranUser;
use App\Models\JenjangPendaftaran;
use App\Models\PeriodePpdb;
use App\Models\PersyaratanPendaftaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        collect([
            ['kode'=>'TPA','nama'=>'Tempat Penitipan Anak','kelompok'=>'paud','urutan'=>1], ['kode'=>'KB','nama'=>'Kelompok Bermain','kelompok'=>'paud','urutan'=>2], ['kode'=>'TK','nama'=>'Taman Kanak-kanak','kelompok'=>'paud','urutan'=>3],
            ['kode'=>'SD','nama'=>'SD','kelompok'=>'formal','urutan'=>4], ['kode'=>'SMP','nama'=>'SMP','kelompok'=>'formal','urutan'=>5], ['kode'=>'SMA','nama'=>'SMA','kelompok'=>'formal','urutan'=>6],
        ])->each(fn($x)=>JenjangPendaftaran::updateOrCreate(['kode'=>$x['kode']],$x));
        PeriodePpdb::firstOrCreate(['tahun_ajaran'=>'2026/2027'],['nama'=>'Pendaftaran 2026/2027','status_aktif'=>true,'informasi'=>'Informasi pendaftaran formal dapat diperbarui oleh admin.']);
        foreach ([
            ['pas_foto_calon', 'Pas foto calon siswa', 'Foto 3 x 4 berwarna.'],
            ['foto_ayah', 'Foto ayah', 'Foto 3 x 4 berwarna.'],
            ['foto_ibu', 'Foto ibu', 'Foto 3 x 4 berwarna.'],
            ['akte_kelahiran', 'Akta kelahiran calon siswa', 'Salinan akta kelahiran.'],
            ['surat_nikah_orang_tua', 'Surat nikah orang tua', 'Salinan surat nikah orang tua.'],
            ['kartu_keluarga', 'Kartu keluarga', 'Salinan kartu keluarga.'],
        ] as $i => $item) PersyaratanPendaftaran::firstOrCreate(
            ['kode' => $item[0], 'periode_ppdb_id' => null, 'jenjang_pendaftaran_id' => null],
            ['nama' => $item[1], 'keterangan' => $item[2], 'wajib' => true, 'urutan' => $i + 1, 'status_aktif' => true],
        );
        $this->call([
            AdminUserSeeder::class,
        ]);
    }
}
