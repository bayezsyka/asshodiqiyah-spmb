<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique()->after('name');
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('peran', 24)->default('admin_spmb')->index()->after('google_id');
            $table->boolean('status_aktif')->default(true)->index()->after('peran');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']); $table->dropUnique(['google_id']);
            $table->dropIndex(['peran']); $table->dropIndex(['status_aktif']);
            $table->dropColumn(['username', 'google_id', 'peran', 'status_aktif', 'last_login_at']);
        });
    }
};
