<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 16)->unique();
            $table->string('nama');
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('status_aktif')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unit_pendidikan_id')->nullable()->after('peran')->constrained('unit_pendidikan')->nullOnDelete();
        });

        Schema::table('jenjang_pendaftaran', function (Blueprint $table) {
            $table->foreignId('unit_pendidikan_id')->nullable()->after('kelompok')->constrained('unit_pendidikan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jenjang_pendaftaran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_pendidikan_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_pendidikan_id');
        });
        Schema::dropIfExists('unit_pendidikan');
    }
};
