<?php

use App\Http\Controllers\Admin\AlatController as AdminAlatController;
use App\Http\Controllers\Admin\KategoriController as AdminKategoriController;
use App\Http\Controllers\Admin\LogAktivitasController as AdminLogAktivitasController;
use App\Http\Controllers\Admin\PeminjamanController as AdminPeminjamanController;
use App\Http\Controllers\Admin\PengembalianController as AdminPengembalianController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Peminjam\DaftarPinjamController;
use App\Http\Controllers\Peminjam\KatalogController;
use App\Http\Controllers\Peminjam\PeminjamanSayaController;
use App\Http\Controllers\Petugas\LaporanController as PetugasLaporanController;
use App\Http\Controllers\Petugas\PengembalianController as PetugasPengembalianController;
use App\Http\Controllers\Petugas\PersetujuanController as PetugasPersetujuanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return match (Auth::user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'petugas' => redirect()->route('petugas.dashboard'),
        default => redirect()->route('peminjam.dashboard'),
    };
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Grup Admin (Modul lengkap TAHAP 4B)
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->as('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::resource('kategori', AdminKategoriController::class)->except(['show']);
        Route::resource('alat', AdminAlatController::class)->except(['show']);
        Route::resource('peminjaman', AdminPeminjamanController::class)->except(['show']);
        Route::resource('pengembalian', AdminPengembalianController::class)->except(['show']);

        Route::get('/log-aktivitas', [AdminLogAktivitasController::class, 'index'])->name('log-aktivitas.index');
    });

// Grup Petugas (Modul lengkap TAHAP 4A)
Route::prefix('petugas')
    ->middleware(['auth', 'role:petugas'])
    ->as('petugas.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/peminjaman', [PetugasPersetujuanController::class, 'index'])->name('peminjaman');
        Route::get('/persetujuan', [PetugasPersetujuanController::class, 'index'])->name('persetujuan.index');
        Route::post('/persetujuan/{peminjaman}/setujui', [PetugasPersetujuanController::class, 'setujui'])->name('persetujuan.setujui');
        Route::post('/persetujuan/{peminjaman}/tolak', [PetugasPersetujuanController::class, 'tolak'])->name('persetujuan.tolak');

        Route::get('/pengembalian', [PetugasPengembalianController::class, 'index'])->name('pengembalian.index');
        Route::post('/pengembalian/{peminjaman}/konfirmasi', [PetugasPengembalianController::class, 'konfirmasi'])->name('pengembalian.konfirmasi');

        Route::get('/laporan', [PetugasLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/cetak', [PetugasLaporanController::class, 'cetak'])->name('laporan.cetak');
        Route::get('/laporan/export-csv', [PetugasLaporanController::class, 'exportCsv'])->name('laporan.export-csv');
    });

// Grup Peminjam (Modul lengkap TAHAP 3 + Dashboard Peminjam)
Route::middleware(['auth', 'role:peminjam'])
    ->as('peminjam.')
    ->group(function () {
        Route::get('/peminjam/dashboard', [DashboardController::class, 'peminjam'])->name('dashboard');

        Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog.index');
        Route::get('/katalog/{alat}', [KatalogController::class, 'show'])->name('katalog.show');

        Route::get('/daftar-pinjam', [DaftarPinjamController::class, 'index'])->name('daftar-pinjam.index');
        Route::post('/daftar-pinjam/tambah', [DaftarPinjamController::class, 'tambah'])->name('daftar-pinjam.tambah');
        Route::patch('/daftar-pinjam/update/{alat}', [DaftarPinjamController::class, 'update'])->name('daftar-pinjam.update');
        Route::delete('/daftar-pinjam/hapus/{alat}', [DaftarPinjamController::class, 'hapus'])->name('daftar-pinjam.hapus');
        Route::post('/daftar-pinjam/ajukan', [DaftarPinjamController::class, 'ajukan'])->name('daftar-pinjam.ajukan');

        Route::get('/peminjaman-saya', [PeminjamanSayaController::class, 'index'])->name('peminjaman-saya.index');
        Route::post('/peminjaman-saya/{peminjaman}/kembalikan', [PeminjamanSayaController::class, 'kembalikan'])->name('peminjaman-saya.kembalikan');
    });
