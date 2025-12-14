<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UmkmProfileController;
use App\Http\Controllers\UmkmLevelController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\UmkmAnggaranController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\UmkmDashboardController;
use App\Http\Controllers\Umkm\LaporanKeuanganController;

// ROOT
Route::get('/', function () {
    if (auth()->check()) {
        // pakai user_group (bukan role) sesuai AuthController-mu
        if (auth()->user()->user_group === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('umkm.dashboard');
    }

    return redirect()->route('login');
});

// ================== GUEST (belum login) ==================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');
});

// ================== LOGOUT ==================
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ================== ADMIN AREA ==================
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // route khusus admin lain taruh di sini
});

// ================== UMKM AREA ==================
Route::middleware(['auth', 'pelakuusaha'])->group(function () {

    // 1) HALAMAN 3 CARD PILIH LEVEL
    Route::get('/umkm/pilih-level', [UmkmLevelController::class, 'index'])
        ->name('umkm.level.choose');

    Route::post('/umkm/pilih-level', [UmkmLevelController::class, 'store'])
        ->name('umkm.level.store');

    // 2) DASHBOARD UMKM
    Route::get('/umkm/dashboard', function () {
        return view('umkm.dashboard');
    })->name('umkm.dashboard');

    // 3) PROFIL UMKM
    Route::get('/umkm/profile', [UmkmProfileController::class, 'edit'])
        ->name('umkm.profile');

    Route::put('/umkm/profile', [UmkmProfileController::class, 'update'])
        ->name('umkm.profile.update');

    // 4) MASTER DATA BAHAN BAKU
    // URL:   /umkm/bahan-baku
    // Route: umkm.bahan.index, umkm.bahan.create, umkm.bahan.edit, ...
    // 4) MASTER DATA BAHAN BAKU
    Route::get('/umkm/bahan-baku', [BahanBakuController::class, 'index'])
        ->name('umkm.bahan.index');

    Route::get('/umkm/bahan-baku/create', [BahanBakuController::class, 'create'])
        ->name('umkm.bahan.create');

    Route::post('/umkm/bahan-baku', [BahanBakuController::class, 'store'])
        ->name('umkm.bahan.store');

    Route::get('/umkm/bahan-baku/{bahan}/edit', [BahanBakuController::class, 'edit'])
        ->name('umkm.bahan.edit');

    Route::put('/umkm/bahan-baku/{bahan}', [BahanBakuController::class, 'update'])
        ->name('umkm.bahan.update');

    Route::delete('/umkm/bahan-baku/{bahan}', [BahanBakuController::class, 'destroy'])
        ->name('umkm.bahan.destroy');

    // 5) MASTER DATA PRODUK JADI
    // URL dasar: /umkm/produk
    // Nama route: umkm.produk.index, umkm.produk.create, dst.
    Route::resource('umkm/produk', ProdukController::class)
        ->names('umkm.produk')
        ->except(['show']);

        // PEMBELIAN BAHAN BAKU
    Route::get('/umkm/pembelian', [PembelianController::class, 'index'])
        ->name('umkm.pembelian.index');

    Route::get('/umkm/pembelian/create', [PembelianController::class, 'create'])
        ->name('umkm.pembelian.create');

    Route::post('/umkm/pembelian', [PembelianController::class, 'store'])
        ->name('umkm.pembelian.store');

    Route::post('/umkm/produk/{produk}/hitung-hpp', [ProdukController::class, 'hitungHpp'])
        ->name('umkm.produk.hitungHpp');

    Route::get('/umkm/anggaran', [UmkmAnggaranController::class, 'index'])->name('umkm.anggaran.index');
    Route::post('/umkm/anggaran', [UmkmAnggaranController::class, 'store'])->name('umkm.anggaran.store');

    Route::get('/umkm/produksi', [ProduksiController::class, 'index'])
    ->name('umkm.produksi.index');

    Route::get('/umkm/produksi/create', [ProduksiController::class, 'create'])
        ->name('umkm.produksi.create');

    Route::post('/umkm/produksi', [ProduksiController::class, 'store'])
        ->name('umkm.produksi.store');


    Route::get('/umkm/dashboard', [UmkmDashboardController::class, 'index'])->name('umkm.dashboard');
    // FORM PENJUALAN (dummy, cuma supaya route() nggak error)
    Route::prefix('umkm')->name('umkm.')->middleware('auth')->group(function () {
        Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
        Route::get('/penjualan/create', [PenjualanController::class, 'create'])->name('penjualan.create');
        Route::post('/penjualan', [PenjualanController::class, 'store'])->name('penjualan.store');
    });

    Route::prefix('umkm')->middleware('auth')->group(function () {
        Route::get('/laporan', [LaporanKeuanganController::class, 'index'])
            ->name('umkm.laporan.index');
    });
});
