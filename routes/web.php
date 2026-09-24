<?php

use App\Http\Controllers\Admin\BerkasPendaftaranController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EksporPpdbController;
use App\Http\Controllers\Admin\PendaftaranController as AdminPendaftaranController;
use App\Http\Controllers\Admin\ProfilAkunController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Public\BerandaSpmbController;
use App\Http\Controllers\Public\CekStatusController;
use App\Http\Controllers\Public\PendaftaranPublikController;
use Illuminate\Support\Facades\Route;

Route::get('/', BerandaSpmbController::class)->name('home');
Route::get('/daftar', [PendaftaranPublikController::class, 'create'])->name('pendaftaran.create');
Route::post('/daftar', [PendaftaranPublikController::class, 'store'])->middleware('throttle:5,1')->name('pendaftaran.store');
Route::get('/pendaftaran/berhasil', [PendaftaranPublikController::class, 'berhasil'])->name('pendaftaran.berhasil');
Route::get('/cek-status', [CekStatusController::class, 'form'])->name('status.form');
Route::post('/cek-status', [CekStatusController::class, 'cari'])->middleware('throttle:10,1')->name('status.cari');
Route::get('/cek-status/hasil', [CekStatusController::class, 'show'])->name('status.show');

Route::get('/sitemap.xml', function () {
    $url = rtrim((string) config('app.url'), '/').'/';

    return response(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>{$url}</loc>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
</urlset>
XML, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
})->name('sitemap');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/lupa-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

Route::middleware(['auth', 'admin.spmb'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/pendaftaran', [AdminPendaftaranController::class, 'index'])->name('pendaftaran.index');
    Route::get('/pendaftaran/{pendaftaran}', [AdminPendaftaranController::class, 'show'])->name('pendaftaran.show');
    Route::put('/pendaftaran/{pendaftaran}/status', [AdminPendaftaranController::class, 'ubahStatus'])->name('pendaftaran.status');
    Route::post('/pendaftaran/{pendaftaran}/catatan', [AdminPendaftaranController::class, 'catatan'])->name('pendaftaran.catatan');
    Route::get('/berkas/{berkas}/download', [BerkasPendaftaranController::class, 'download'])->name('berkas.download');
    Route::put('/berkas/{berkas}/verifikasi', [BerkasPendaftaranController::class, 'verifikasi'])->name('berkas.verifikasi');
    Route::get('/ekspor/excel', [EksporPpdbController::class, 'excel'])->name('ekspor.excel');
    Route::get('/ekspor/zip', [EksporPpdbController::class, 'zip'])->name('ekspor.zip');
    Route::get('/pendaftaran/{pendaftaran}/pdf', [EksporPpdbController::class, 'pdf'])->name('ekspor.pdf');
    Route::get('/periode', [App\Http\Controllers\Admin\PeriodePpdbController::class, 'index'])->name('periode.index');
    Route::post('/periode', [App\Http\Controllers\Admin\PeriodePpdbController::class, 'store'])->name('periode.store');
    Route::put('/periode/{periode}', [App\Http\Controllers\Admin\PeriodePpdbController::class, 'update'])->name('periode.update');
    Route::put('/periode/{periode}/aktifkan', [App\Http\Controllers\Admin\PeriodePpdbController::class, 'aktifkan'])->name('periode.aktifkan');
    Route::delete('/periode/{periode}', [App\Http\Controllers\Admin\PeriodePpdbController::class, 'destroy'])->name('periode.destroy');
    Route::get('/profil', [ProfilAkunController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilAkunController::class, 'update'])->name('profil.update');
    Route::delete('/profil/google', [GoogleAuthController::class, 'unlink'])->name('profil.google.unlink');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
