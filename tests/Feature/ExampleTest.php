<?php

namespace Tests\Feature;

use App\Enums\PeranUser;
use App\Models\JenjangPendaftaran;
use App\Models\Pendaftaran;
use App\Models\UnitPendidikan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_uses_asshodiqiyah_identity_and_five_units(): void
    {
        $this->seed();

        $this->get('/')
            ->assertOk()
            ->assertSee('SPMB Asshodiqiyah | Pendaftaran Santri dan Siswa Baru', false)
            ->assertSee('Pondok Pesantren Asshodiqiyah Kaligawe', false)
            ->assertInertia(fn (Assert $page) => $page->component('Public/Home', false)->has('jenjang', 5));

        foreach (['SDIT', 'SMPIT', 'MTS', 'MA', 'SMK'] as $kode) {
            $this->assertDatabaseHas('jenjang_pendaftaran', ['kode' => $kode, 'status_aktif' => true]);
        }
    }

    public function test_superadmin_can_create_a_unit_admin(): void
    {
        $this->seed();
        $superadmin = $this->superadmin();
        $unit = UnitPendidikan::where('kode', 'MTS')->firstOrFail();

        $this->actingAs($superadmin)->post('/admin/users', [
            'name' => 'Admin MTs', 'username' => 'admin.mts', 'email' => 'admin.mts@example.test',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            'peran' => 'admin_spmb', 'unit_pendidikan_id' => $unit->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['username' => 'admin.mts', 'unit_pendidikan_id' => $unit->id]);
    }

    public function test_unit_admin_only_sees_its_own_registration_data(): void
    {
        $this->seed();
        $sd = UnitPendidikan::where('kode', 'SDIT')->firstOrFail();
        $sdJenjang = JenjangPendaftaran::where('kode', 'SDIT')->firstOrFail();
        $mtsJenjang = JenjangPendaftaran::where('kode', 'MTS')->firstOrFail();
        $adminSd = User::factory()->create(['peran' => PeranUser::AdminSpmb, 'unit_pendidikan_id' => $sd->id, 'status_aktif' => true]);
        $pendaftaranSd = $this->pendaftaran($sdJenjang->id, 'Calon SD IT');
        $pendaftaranMts = $this->pendaftaran($mtsJenjang->id, 'Calon MTs');

        $this->actingAs($adminSd)->get('/admin/pendaftaran')->assertOk()->assertSee($pendaftaranSd->nama_lengkap)->assertDontSee($pendaftaranMts->nama_lengkap);
        $this->actingAs($adminSd)->get("/admin/pendaftaran/{$pendaftaranMts->id}")->assertForbidden();
        $this->actingAs($adminSd)->get('/admin/periode')->assertForbidden();
    }

    public function test_an_admin_without_unit_cannot_enter_admin_panel(): void
    {
        $admin = User::factory()->create(['peran' => PeranUser::AdminSpmb, 'unit_pendidikan_id' => null, 'status_aktif' => true]);

        $this->actingAs($admin)->get('/admin')->assertForbidden();
    }

    private function superadmin(): User
    {
        return User::factory()->create(['peran' => PeranUser::Superadmin, 'status_aktif' => true]);
    }

    private function pendaftaran(int $jenjangId, string $nama): Pendaftaran
    {
        return Pendaftaran::create([
            'periode_ppdb_id' => 1, 'jenjang_pendaftaran_id' => $jenjangId, 'status' => 'diajukan',
            'nama_lengkap' => $nama, 'nama_panggilan' => 'Calon', 'tempat_lahir' => 'Semarang', 'tanggal_lahir' => '2015-01-01', 'jenis_kelamin' => 'L', 'agama_calon' => 'Islam',
            'alamat_domisili' => 'Semarang', 'nomor_telepon_calon' => '081234567890', 'nama_ayah' => 'Ayah', 'nomor_telepon_ayah' => '081234567891', 'nama_ibu' => 'Ibu', 'nomor_telepon_ibu' => '081234567892',
            'submitted_at' => now(),
        ]);
    }
}
