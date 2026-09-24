<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran', function (Blueprint $table): void {
            $table->string('nisn', 10)->nullable()->unique()->after('status');
            $table->string('nik', 16)->nullable()->unique()->after('nisn');
            $table->string('nama_ibu_pencarian', 150)->nullable()->index()->after('nama_ibu');
        });

        DB::table('pendaftaran')->orderBy('id')->eachById(function (object $pendaftaran): void {
            $namaIbu = trim((string) preg_replace('/\s+/u', ' ', mb_strtolower((string) $pendaftaran->nama_ibu)));

            DB::table('pendaftaran')->where('id', $pendaftaran->id)->update(['nama_ibu_pencarian' => $namaIbu]);
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran', function (Blueprint $table): void {
            $table->dropUnique(['nisn']);
            $table->dropUnique(['nik']);
            $table->dropIndex(['nama_ibu_pencarian']);
            $table->dropColumn(['nisn', 'nik', 'nama_ibu_pencarian']);
        });
    }
};
