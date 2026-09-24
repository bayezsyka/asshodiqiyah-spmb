<?php

namespace Tests\Feature;

use App\Enums\StatusPendaftaran;
use App\Exports\PendaftaranExport;
use App\Models\JenjangPendaftaran;
use App\Models\PeriodePpdb;
use App\Models\Pendaftaran;
use App\Models\PersyaratanPendaftaran;
use App\Models\User;
use App\Services\Pendaftaran\PenyimpanPendaftaran;
use App\Services\Pendaftaran\PengubahStatusPendaftaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use ZipArchive;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed();
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('SPMB Lenterahati IBS | Pendaftaran Santri Baru', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('https://schema.org', false)
            ->assertHeaderMissing('X-Robots-Tag')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');
    }

    public function test_only_the_public_homepage_is_indexable(): void
    {
        $this->get('/daftar')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow, noarchive, nosnippet"', false)
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>'.rtrim((string) config('app.url'), '/').'/</loc>', false);
    }

    public function test_paud_registration_has_no_period_and_is_year_round(): void
    {
        $this->seed();
        Storage::fake('local');

        $paud = JenjangPendaftaran::where('kode', 'TK')->firstOrFail();
        $periode = PeriodePpdb::firstOrFail();
        $berkas = PersyaratanPendaftaran::where('wajib', true)->get()->mapWithKeys(fn ($persyaratan) => [$persyaratan->kode => str_starts_with($persyaratan->kode, 'foto_') || $persyaratan->kode === 'pas_foto_calon' ? UploadedFile::fake()->image("{$persyaratan->kode}.jpg", 2600, 1800) : UploadedFile::fake()->create("{$persyaratan->kode}.pdf", 80, 'application/pdf')])->all();

        $pendaftaran = app(PenyimpanPendaftaran::class)->simpan([
            'jenjang_pendaftaran_id' => $paud->id, 'periode_ppdb_id' => $periode->id, 'nama_lengkap' => 'Calon Peserta', 'nama_panggilan' => 'Calon', 'tempat_lahir' => 'Mataram', 'tanggal_lahir' => '2020-01-01', 'jenis_kelamin' => 'L', 'agama_calon' => 'Islam', 'suku_bangsa_calon' => 'Sasak', 'kewarganegaraan' => 'WNI', 'anak_ke' => 1, 'jumlah_saudara_kandung' => 1, 'jumlah_saudara_tiri' => 0, 'jumlah_saudara_angkat' => 0, 'jumlah_bersaudara' => 1, 'alamat_domisili' => 'Mataram', 'nomor_telepon_calon' => '081234567890', 'tinggal_bersama' => 'orang_tua', 'jarak_rumah_km' => 1.5, 'waktu_tempuh_menit' => 15, 'asal_sekolah' => 'Rumah', 'alamat_sekolah_asal' => 'Mataram', 'nama_ayah' => 'Ayah Calon', 'nomor_telepon_ayah' => '081200000001', 'nama_ibu' => 'Ibu Calon', 'nomor_telepon_ibu' => '081200000002', 'berkas' => $berkas,
        ]);

        $this->assertDatabaseHas('pendaftaran', ['id' => $pendaftaran->id, 'periode_ppdb_id' => null, 'nama_panggilan' => 'Calon']);
        $this->assertNull($pendaftaran->periode_ppdb_id);
        $this->assertSame(6, $pendaftaran->berkas()->count());
        $foto = $pendaftaran->berkas()->where('kode_berkas', 'pas_foto_calon')->firstOrFail();
        $this->assertSame('image/jpeg', $foto->mime_type);
        $this->assertStringEndsWith('.jpg', $foto->path);
        Storage::disk('local')->assertExists($foto->path);
    }

    public function test_paud_registration_succeeds_even_when_no_active_period_exists(): void
    {
        $this->seed();
        Storage::fake('local');
        PeriodePpdb::query()->update(['status_aktif' => false]);

        $paud = JenjangPendaftaran::where('kode', 'TK')->firstOrFail();
        $berkas = PersyaratanPendaftaran::where('wajib', true)->whereNull('periode_ppdb_id')->get()->mapWithKeys(fn ($persyaratan) => [$persyaratan->kode => str_starts_with($persyaratan->kode, 'foto_') || $persyaratan->kode === 'pas_foto_calon' ? UploadedFile::fake()->image("{$persyaratan->kode}.jpg", 2600, 1800) : UploadedFile::fake()->create("{$persyaratan->kode}.pdf", 80, 'application/pdf')])->all();

        $pendaftaran = app(PenyimpanPendaftaran::class)->simpan([
            'jenjang_pendaftaran_id' => $paud->id, 'nama_lengkap' => 'Calon Peserta PAUD Mandiri', 'nama_panggilan' => 'Calon', 'tempat_lahir' => 'Mataram', 'tanggal_lahir' => '2020-01-01', 'jenis_kelamin' => 'L', 'agama_calon' => 'Islam', 'suku_bangsa_calon' => 'Sasak', 'kewarganegaraan' => 'WNI', 'anak_ke' => 1, 'jumlah_saudara_kandung' => 1, 'jumlah_saudara_tiri' => 0, 'jumlah_saudara_angkat' => 0, 'jumlah_bersaudara' => 1, 'alamat_domisili' => 'Mataram', 'nomor_telepon_calon' => '081234567890', 'tinggal_bersama' => 'orang_tua', 'jarak_rumah_km' => 1.5, 'waktu_tempuh_menit' => 15, 'asal_sekolah' => 'Rumah', 'alamat_sekolah_asal' => 'Mataram', 'nama_ayah' => 'Ayah Calon', 'nomor_telepon_ayah' => '081200000001', 'nama_ibu' => 'Ibu Calon', 'nomor_telepon_ibu' => '081200000002', 'berkas' => $berkas,
        ]);

        $this->assertDatabaseHas('pendaftaran', ['id' => $pendaftaran->id, 'periode_ppdb_id' => null]);
    }

    public function test_admin_can_export_paud_zip_without_period(): void
    {
        $this->seed();
        Storage::fake('local');
        $paud = JenjangPendaftaran::where('kode', 'TK')->firstOrFail();

        $this->buatPendaftaran([
            'jenjang_pendaftaran_id' => $paud->id,
            'periode_ppdb_id' => null,
            'nama_lengkap' => 'Santri PAUD Cilik',
            'nik' => '5201010101200001',
            'nisn' => null,
        ]);

        $response = $this->actingAs(User::firstOrFail())->get('/admin/ekspor/zip?jenjang='.$paud->id);
        $response->assertOk()->assertDownload('Berkas Pendaftaran Taman Kanak kanak.zip');
    }

    public function test_registration_validation_messages_are_indonesian(): void
    {
        $this->seed();

        $this->from('/daftar')->post('/daftar', ['anak_ke' => 'satu'])
            ->assertRedirect('/daftar')
            ->assertSessionHasErrors([
            'anak_ke' => 'Anak ke harus berupa angka bulat.',
            ]);
    }

    public function test_nisn_is_required_for_formal_levels_and_nik_is_required_for_paud(): void
    {
        $this->seed();

        $sd = JenjangPendaftaran::where('kode', 'SD')->firstOrFail();
        $paud = JenjangPendaftaran::where('kode', 'TK')->firstOrFail();

        $this->from('/daftar')->post('/daftar', ['jenjang_pendaftaran_id' => $sd->id])
            ->assertSessionHasErrors('nisn');
        $this->from('/daftar')->post('/daftar', ['jenjang_pendaftaran_id' => $paud->id])
            ->assertSessionHasErrors('nik');
    }

    public function test_status_can_be_checked_with_nisn_or_nik_and_mother_name(): void
    {
        $this->seed();
        $pendaftaran = $this->buatPendaftaran(['nisn' => '0012345678', 'nik' => '3271010101010001']);

        $this->from('/cek-status')->post('/cek-status', [
            'identitas' => $pendaftaran->nisn,
            'tanggal_lahir' => '2016-01-01',
            'nama_ibu' => '  IBU   CALON ',
        ])->assertRedirect('/cek-status/hasil');

        $this->get('/cek-status/hasil')
            ->assertOk()
            ->assertSee('Calon Peserta', false);

        $this->from('/cek-status')->post('/cek-status', [
            'identitas' => $pendaftaran->nik,
            'tanggal_lahir' => '2016-01-01',
            'nama_ibu' => 'Ibu Calon',
        ])->assertRedirect('/cek-status/hasil');
    }

    public function test_admin_can_stream_complete_pdf_package(): void
    {
        $this->seed();
        Storage::fake('local');
        $pendaftaran = $this->buatPendaftaran(['nisn' => '0012345678', 'nik' => '3271010101010001']);
        $file = UploadedFile::fake()->image('kartu-keluarga.jpg', 1200, 900);
        $path = 'ppdb/test/kartu-keluarga.webp';
        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));
        $pendaftaran->berkas()->create([
            'kode_berkas' => 'kartu_keluarga',
            'nama_berkas' => 'Kartu Keluarga',
            'path' => $path,
            'nama_asli' => 'kartu-keluarga.jpg',
            'mime_type' => 'image/webp',
            'ukuran' => Storage::disk('local')->size($path),
        ]);

        $this->actingAs(User::firstOrFail())
            ->get("/admin/pendaftaran/{$pendaftaran->id}/pdf")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename="Paket Pendaftaran Calon Peserta (NISN 0012345678).pdf"');
    }

    public function test_admin_can_stream_individual_pdf_documents(): void
    {
        $this->seed();
        Storage::fake('local');
        $pendaftaran = $this->buatPendaftaran();
        $path = 'ppdb/test/akta-kelahiran.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 dokumen contoh');
        $berkas = $pendaftaran->berkas()->create([
            'kode_berkas' => 'akta_kelahiran',
            'nama_berkas' => 'Akta Kelahiran',
            'path' => $path,
            'nama_asli' => 'akta-kelahiran.pdf',
            'mime_type' => 'application/pdf',
            'ukuran' => Storage::disk('local')->size($path),
        ]);

        $this->actingAs(User::firstOrFail())
            ->get("/admin/berkas/{$berkas->id}/download")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename="akta-kelahiran.pdf"');
    }

    public function test_admin_can_export_original_documents_in_named_zip_folders(): void
    {
        $this->seed();
        Storage::fake('local');
        $pendaftaran = $this->buatPendaftaran(['nisn' => '0012345678', 'nik' => '3271010101010001']);
        $path = 'ppdb/test/kartu-keluarga.webp';
        Storage::disk('local')->put($path, 'dokumen contoh');
        $pendaftaran->berkas()->create([
            'kode_berkas' => 'kartu_keluarga',
            'nama_berkas' => 'Kartu Keluarga',
            'path' => $path,
            'nama_asli' => 'kartu-keluarga.jpg',
            'mime_type' => 'image/webp',
            'ukuran' => Storage::disk('local')->size($path),
        ]);

        $response = $this->actingAs(User::firstOrFail())->get('/admin/ekspor/zip?jenjang='.$pendaftaran->jenjang_pendaftaran_id.'&periode='.$pendaftaran->periode_ppdb_id);
        $response->assertOk()->assertDownload('Berkas Pendaftaran 2026 2027 SMA.zip');

        $arsipSementara = tempnam(sys_get_temp_dir(), 'spmb_test_zip_');
        file_put_contents($arsipSementara, $response->streamedContent());
        $zip = new ZipArchive;
        $zip->open($arsipSementara);

        $this->assertSame('Calon Peserta (NISN 0012345678)/Formulir Pendaftaran.pdf', $zip->getNameIndex(0));
        $this->assertSame('Calon Peserta (NISN 0012345678)/Kartu Keluarga.webp', $zip->getNameIndex(1));

        $zip->close();
        @unlink($arsipSementara);
    }

    public function test_admin_can_manage_periods_and_switch_sessions(): void
    {
        $this->seed();
        $admin = User::firstOrFail();

        // 1. Cek halaman kelola periode
        $this->actingAs($admin)->get('/admin/periode')->assertOk();

        // 2. Buat periode baru
        $this->actingAs($admin)->post('/admin/periode', [
            'nama' => 'Pendaftaran 2027/2028',
            'tahun_ajaran' => '2027/2028',
            'mulai_pada' => '2027-01-01',
            'selesai_pada' => '2027-07-01',
            'status_aktif' => true,
            'informasi' => 'Pendaftaran gelombang baru',
            'instruksi_pembayaran' => null,
        ])->assertRedirect();

        $periodeBaru = PeriodePpdb::where('tahun_ajaran', '2027/2028')->firstOrFail();
        $this->assertTrue($periodeBaru->status_aktif);

        // Periode lama otomatis tidak aktif
        $periodeLama = PeriodePpdb::where('tahun_ajaran', '2026/2027')->firstOrFail();
        $this->assertFalse($periodeLama->status_aktif);

        // 3. Switch periode aktif kembali ke periode lama
        $this->actingAs($admin)->put("/admin/periode/{$periodeLama->id}/aktifkan")->assertRedirect();
        $this->assertTrue($periodeLama->fresh()->status_aktif);
        $this->assertFalse($periodeBaru->fresh()->status_aktif);

        // 4. Dashboard dengan filter periode
        $this->actingAs($admin)->get("/admin?periode={$periodeLama->id}")->assertOk();
        $this->actingAs($admin)->get("/admin?periode={$periodeBaru->id}")->assertOk();
        $this->actingAs($admin)->get('/admin?periode=all')->assertOk();
        $this->actingAs($admin)->get('/admin?periode=tanpa_periode')->assertOk();
        $this->actingAs($admin)->get('/admin/pendaftaran?periode=tanpa_periode')->assertOk();
    }

    public function test_inactive_user_cannot_access_admin_spmb(): void
    {
        $this->seed();
        $user = User::firstOrFail();
        $user->update(['status_aktif' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_authenticated_user_is_redirected_from_login_to_admin_dashboard(): void
    {
        $this->seed();

        $this->actingAs(User::firstOrFail())
            ->get('/login')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_registration_rejects_unknown_document_codes_before_creating_data(): void
    {
        $this->seed();
        Storage::fake('local');
        $paud = JenjangPendaftaran::where('kode', 'TK')->firstOrFail();
        $berkas = PersyaratanPendaftaran::where('wajib', true)->get()->mapWithKeys(fn ($persyaratan) => [
            $persyaratan->kode => UploadedFile::fake()->create("{$persyaratan->kode}.pdf", 80, 'application/pdf'),
        ])->all();
        $berkas['dokumen_tidak_diizinkan'] = UploadedFile::fake()->create('lainnya.pdf', 80, 'application/pdf');

        try {
            app(PenyimpanPendaftaran::class)->simpan($this->dataPendaftaran($paud, $berkas));
            $this->fail('Berkas di luar persyaratan aktif harus ditolak.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'Terdapat berkas yang tidak termasuk persyaratan pendaftaran aktif.',
                $exception->errors()['berkas'][0],
            );
        }

        $this->assertDatabaseCount('pendaftaran', 0);
    }

    public function test_final_registration_status_cannot_be_changed(): void
    {
        $this->seed();
        $pendaftaran = $this->buatPendaftaran(['status' => StatusPendaftaran::DaftarUlang]);

        $this->expectException(ValidationException::class);
        app(PengubahStatusPendaftaran::class)->ubah(
            $pendaftaran,
            StatusPendaftaran::Diajukan,
            User::firstOrFail(),
        );
    }

    public function test_excel_export_neutralizes_formula_prefixes(): void
    {
        $this->seed();
        $this->buatPendaftaran(['nama_lengkap' => '=HYPERLINK("https://example.test")']);

        $baris = (new PendaftaranExport)->collection()->first();

        $this->assertSame('\'=HYPERLINK("https://example.test")', $baris[0]);
    }

    public function test_invalid_document_storage_locations_are_not_passed_to_filesystem(): void
    {
        $berkasKosong = new \App\Models\BerkasPendaftaran(['path' => null]);
        $berkasTraversal = new \App\Models\BerkasPendaftaran(['path' => 'ppdb/../../.env']);
        $berkasValid = new \App\Models\BerkasPendaftaran(['path' => 'ppdb/2026/LH-26-ABC123/kartu_keluarga/berkas.pdf']);

        $this->assertNull($berkasKosong->lokasiPenyimpanan());
        $this->assertNull($berkasTraversal->lokasiPenyimpanan());
        $this->assertSame('ppdb/2026/LH-26-ABC123/kartu_keluarga/berkas.pdf', $berkasValid->lokasiPenyimpanan());
    }

    private function buatPendaftaran(array $tambahan = []): Pendaftaran
    {
        return Pendaftaran::create([
            'jenjang_pendaftaran_id' => JenjangPendaftaran::where('kode', 'SMA')->firstOrFail()->id,
            'periode_ppdb_id' => PeriodePpdb::firstOrFail()->id,
            'nama_lengkap' => 'Calon Peserta',
            'nama_panggilan' => 'Calon',
            'tempat_lahir' => 'Mataram',
            'tanggal_lahir' => '2016-01-01',
            'jenis_kelamin' => 'L',
            'agama_calon' => 'Islam',
            'kewarganegaraan' => 'WNI',
            'alamat_domisili' => 'Mataram',
            'nomor_telepon_calon' => '081234567890',
            'nama_ayah' => 'Ayah Calon',
            'nomor_telepon_ayah' => '081200000001',
            'nama_ibu' => 'Ibu Calon',
            'nama_ibu_pencarian' => 'ibu calon',
            'nomor_telepon_ibu' => '081200000002',
            'submitted_at' => now(),
            ...$tambahan,
        ]);
    }

    /** @param array<string, \Illuminate\Http\UploadedFile> $berkas */
    private function dataPendaftaran(JenjangPendaftaran $jenjang, array $berkas): array
    {
        return [
            'jenjang_pendaftaran_id' => $jenjang->id,
            'periode_ppdb_id' => PeriodePpdb::firstOrFail()->id,
            'nik' => '3271010101010001',
            'nama_lengkap' => 'Calon Peserta',
            'nama_panggilan' => 'Calon',
            'tempat_lahir' => 'Mataram',
            'tanggal_lahir' => '2020-01-01',
            'jenis_kelamin' => 'L',
            'agama_calon' => 'Islam',
            'kewarganegaraan' => 'WNI',
            'alamat_domisili' => 'Mataram',
            'nomor_telepon_calon' => '081234567890',
            'nama_ayah' => 'Ayah Calon',
            'nomor_telepon_ayah' => '081200000001',
            'nama_ibu' => 'Ibu Calon',
            'nomor_telepon_ibu' => '081200000002',
            'berkas' => $berkas,
        ];
    }
}
