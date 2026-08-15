<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DokumenPersyaratanController;
use App\Http\Controllers\Admin\GelombangController;
use App\Http\Controllers\Admin\JalurController;
use App\Http\Controllers\Admin\KelasPerkuliahanController;
use App\Http\Controllers\Admin\KuotaController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PendaftarController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\ProdiController;
use App\Http\Controllers\Admin\PromoController;
use App\Http\Controllers\Admin\SettingProdiController;
use App\Http\Controllers\Admin\TahunPenerimaanController;
use App\Http\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Http\Controllers\Mahasiswa\PendaftaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = Auth::user();

    return $user->hasRole('mahasiswa')
        ? redirect()->route('mahasiswa.dashboard')
        : redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // JSON wilayah untuk cascading dropdown (provinsi → kota → kecamatan → kelurahan)
    Route::get('/wilayah', [WilayahController::class, 'index'])->name('wilayah.index');
});

// ===== Area Admin (Panitia) =====
Route::middleware(['auth', 'role:super-admin|admin-pmb'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');

    Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');

    Route::resource('tahun', TahunPenerimaanController::class)->except('show');
    Route::resource('jalur', JalurController::class)->except('show');
    Route::resource('prodi', ProdiController::class)->except('show');
    Route::resource('kelas', KelasPerkuliahanController::class)->except('show')->parameters(['kelas' => 'kelas']);
    Route::resource('kuota', KuotaController::class)->except('show')->parameters(['kuota' => 'kuota']);
    Route::resource('dokumen', DokumenPersyaratanController::class)->except('show')->parameters(['dokumen' => 'dokumen']);
    Route::resource('gelombang', GelombangController::class)->except('show');
    Route::resource('promo', PromoController::class)->except('show');

    Route::get('pendaftar', [PendaftarController::class, 'index'])->name('pendaftar.index');
    Route::get('pendaftar/{pendaftaran}', [PendaftarController::class, 'show'])->name('pendaftar.show');
    Route::patch('pendaftar/{pendaftaran}/pembayaran', [PendaftarController::class, 'updatePembayaran'])->name('pendaftar.pembayaran');
    Route::patch('pendaftar/{pendaftaran}/verifikasi-berkas', [PendaftarController::class, 'verifikasiBerkas'])->name('pendaftar.verifikasi-berkas');
    Route::patch('pendaftar/{pendaftaran}/nilai', [PendaftarController::class, 'inputNilai'])->name('pendaftar.nilai');
    Route::patch('pendaftar/{pendaftaran}/status', [PendaftarController::class, 'updateStatus'])->name('pendaftar.status');
    Route::patch('pendaftar/{pendaftaran}/reset-password', [PendaftarController::class, 'resetPassword'])->name('pendaftar.reset-password');
    Route::patch('pendaftar/{pendaftaran}/daftar-ulang', [PendaftarController::class, 'verifikasiDaftarUlang'])->name('pendaftar.daftar-ulang');
    Route::patch('pendaftar/dokumen/{dokumen}/verifikasi', [PendaftarController::class, 'verifikasiDokumen'])->name('pendaftar.dokumen-verifikasi');

    Route::get('setting-prodi', [SettingProdiController::class, 'index'])->name('setting-prodi.index');
    Route::get('setting-prodi/{prodi}/edit', [SettingProdiController::class, 'edit'])->name('setting-prodi.edit');
    Route::put('setting-prodi/{prodi}', [SettingProdiController::class, 'update'])->name('setting-prodi.update');
});

// ===== Area Mahasiswa =====
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
    Route::get('/pendaftaran/create', [PendaftaranController::class, 'create'])->name('pendaftaran.create');
    Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
    Route::get('/pendaftaran/{pendaftaran}', [PendaftaranController::class, 'show'])->name('pendaftaran.show');
    Route::post('/pendaftaran/{pendaftaran}/daftar-ulang', [PendaftaranController::class, 'daftarUlang'])->name('pendaftaran.daftar-ulang');
});

require __DIR__.'/auth.php';
