<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('jenjang_pendaftaran', function (Blueprint $table) {
            $table->id(); $table->string('kode', 16)->unique(); $table->string('nama'); $table->string('kelompok', 24);
            $table->unsignedSmallInteger('urutan')->default(0); $table->boolean('status_aktif')->default(true); $table->timestamps();
        });
        Schema::create('periode_ppdb', function (Blueprint $table) {
            $table->id(); $table->string('nama'); $table->string('tahun_ajaran', 20)->index(); $table->date('mulai_pada')->nullable(); $table->date('selesai_pada')->nullable();
            $table->boolean('status_aktif')->default(false)->index(); $table->text('informasi')->nullable(); $table->text('instruksi_pembayaran')->nullable(); $table->timestamps();
        });
        Schema::create('persyaratan_pendaftaran', function (Blueprint $table) {
            $table->id(); $table->foreignId('jenjang_pendaftaran_id')->nullable()->constrained('jenjang_pendaftaran')->nullOnDelete(); $table->foreignId('periode_ppdb_id')->nullable()->constrained('periode_ppdb')->nullOnDelete();
            $table->string('kode', 64); $table->string('nama'); $table->text('keterangan')->nullable(); $table->boolean('wajib')->default(true); $table->unsignedSmallInteger('urutan')->default(0); $table->boolean('status_aktif')->default(true); $table->timestamps();
            $table->unique(['jenjang_pendaftaran_id', 'periode_ppdb_id', 'kode'], 'persyaratan_konteks_kode_unique');
        });
        Schema::create('pendaftaran', function (Blueprint $table) {
            $table->id(); $table->foreignId('periode_ppdb_id')->nullable()->constrained('periode_ppdb')->nullOnDelete(); $table->foreignId('jenjang_pendaftaran_id')->constrained('jenjang_pendaftaran')->restrictOnDelete();
            $table->string('status', 32)->default('diajukan')->index();

            // Data calon siswa untuk formulir pendaftaran SPMB.
            $table->string('nama_lengkap'); $table->string('nama_panggilan', 100);
            $table->string('tempat_lahir'); $table->date('tanggal_lahir'); $table->string('jenis_kelamin', 16); $table->string('agama_calon', 64); $table->string('suku_bangsa_calon', 100)->nullable(); $table->string('kewarganegaraan', 32)->default('WNI');
            $table->unsignedTinyInteger('anak_ke')->nullable(); $table->unsignedTinyInteger('jumlah_saudara_kandung')->nullable(); $table->unsignedTinyInteger('jumlah_saudara_tiri')->nullable(); $table->unsignedTinyInteger('jumlah_saudara_angkat')->nullable(); $table->unsignedTinyInteger('jumlah_bersaudara')->nullable();
            $table->text('alamat_domisili'); $table->string('nomor_telepon_calon', 24);
            $table->string('tinggal_bersama', 32)->nullable(); $table->decimal('jarak_rumah_km', 6, 2)->nullable(); $table->unsignedSmallInteger('jarak_rumah_meter')->nullable(); $table->unsignedTinyInteger('waktu_tempuh_jam')->nullable(); $table->unsignedSmallInteger('waktu_tempuh_menit')->nullable();
            $table->string('asal_sekolah')->nullable(); $table->text('alamat_sekolah_asal')->nullable();
            $table->string('nomor_telepon_darurat_1', 24)->nullable(); $table->string('nomor_telepon_darurat_2', 24)->nullable();

            // Data ayah - mengikuti halaman pertama formulir resmi.
            $table->string('nama_ayah'); $table->string('tempat_lahir_ayah')->nullable(); $table->date('tanggal_lahir_ayah')->nullable(); $table->string('agama_ayah', 64)->nullable(); $table->string('suku_bangsa_ayah', 100)->nullable();
            $table->string('pendidikan_ayah')->nullable(); $table->string('pekerjaan_ayah')->nullable(); $table->text('alamat_ayah')->nullable(); $table->string('nomor_telepon_ayah', 24); $table->string('penghasilan_ayah')->nullable();

            // Data ibu - mengikuti halaman kedua formulir resmi.
            $table->string('nama_ibu'); $table->string('tempat_lahir_ibu')->nullable(); $table->date('tanggal_lahir_ibu')->nullable(); $table->string('agama_ibu', 64)->nullable(); $table->string('suku_bangsa_ibu', 100)->nullable();
            $table->string('pendidikan_ibu')->nullable(); $table->string('pekerjaan_ibu')->nullable(); $table->text('alamat_ibu')->nullable(); $table->string('nomor_telepon_ibu', 24); $table->string('penghasilan_ibu')->nullable();

            $table->text('catatan_publik_terakhir')->nullable();
            $table->timestamp('submitted_at')->nullable()->index(); $table->timestamp('verified_at')->nullable(); $table->timestamp('accepted_at')->nullable(); $table->timestamp('re_registered_at')->nullable(); $table->timestamps();
        });
        Schema::create('berkas_pendaftaran', function (Blueprint $table) {
            $table->id(); $table->foreignId('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete(); $table->foreignId('persyaratan_pendaftaran_id')->nullable()->constrained('persyaratan_pendaftaran')->nullOnDelete();
            $table->string('kode_berkas', 64); $table->string('nama_berkas'); $table->string('path'); $table->string('nama_asli'); $table->string('mime_type', 128); $table->unsignedBigInteger('ukuran'); $table->string('status_verifikasi', 24)->default('menunggu'); $table->text('catatan_verifikasi')->nullable(); $table->timestamps();
            $table->unique(['pendaftaran_id', 'kode_berkas']);
        });
        Schema::create('riwayat_status_pendaftaran', function (Blueprint $table) {
            $table->id(); $table->foreignId('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('status_sebelumnya', 32)->nullable(); $table->string('status_sesudahnya', 32); $table->text('catatan_publik')->nullable(); $table->text('catatan_internal')->nullable(); $table->timestamps();
        });
        Schema::create('catatan_pendaftaran', function (Blueprint $table) {
            $table->id(); $table->foreignId('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); $table->text('isi'); $table->boolean('tampil_publik')->default(false); $table->timestamps();
        });
        Schema::create('ekspor_ppdb', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); $table->string('jenis', 24); $table->json('filter')->nullable(); $table->string('status', 24)->default('menunggu')->index(); $table->string('path')->nullable(); $table->text('error_message')->nullable(); $table->timestamp('selesai_pada')->nullable(); $table->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); $table->string('aksi')->index(); $table->string('subjek_tipe')->nullable(); $table->unsignedBigInteger('subjek_id')->nullable(); $table->json('metadata')->nullable(); $table->string('ip_address', 45)->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('audit_logs'); Schema::dropIfExists('ekspor_ppdb'); Schema::dropIfExists('catatan_pendaftaran'); Schema::dropIfExists('riwayat_status_pendaftaran'); Schema::dropIfExists('berkas_pendaftaran'); Schema::dropIfExists('pendaftaran'); Schema::dropIfExists('persyaratan_pendaftaran'); Schema::dropIfExists('periode_ppdb'); Schema::dropIfExists('jenjang_pendaftaran'); }
};
