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
use App\Http\Controllers\Umkm\CoaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UmkmController;




// ROOT
Route::get('/', function () {
    if (!auth()->check()) return redirect()->route('umkm.login');

    return auth()->user()->user_group === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('umkm.dashboard');
});

// ================== GUEST ==================
Route::middleware('guest')->group(function () {
    Route::get('/umkm/login', [AuthController::class, 'showUmkmLoginForm'])->name('umkm.login');
    Route::post('/umkm/login', [AuthController::class, 'loginUmkm'])->name('umkm.login.process');

    Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'loginAdmin'])->name('admin.login.process');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');

    Route::get('/admin/register', [AuthController::class, 'showAdminRegisterForm'])->name('admin.register');
    Route::post('/admin/register', [AuthController::class, 'registerAdmin'])->name('admin.register.process');

    Route::get('/login', fn () => redirect()->route('umkm.login'))->name('login');

});

// ================== LOGOUT ==================
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ================== ADMIN AREA (3 menu) ==================
 Route::prefix('admin')->name('admin.')->middleware(['auth','adminonly'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('umkm')->name('umkm.')->group(function () {
        Route::get('/', [UmkmController::class, 'index'])->name('index');
        Route::get('/create', [UmkmController::class, 'create'])->name('create');
        Route::get('/{id}', [UmkmController::class, 'show'])->name('show');
    });

    // iuran (kalau masih dummy gapapa)
    Route::get('/iuran', fn() => view('admin.iuran.index'))->name('iuran.index');
});


// ================== UMKM AREA ==================
Route::prefix('umkm')->name('umkm.')->middleware(['auth','pelakuusaha'])->group(function () {

    // pilih level
    Route::get('/pilih-level', [UmkmLevelController::class, 'index'])->name('level.choose');
    Route::post('/pilih-level', [UmkmLevelController::class, 'store'])->name('level.store');

    // dashboard (CUMA 1!)
    Route::get('/dashboard', [UmkmDashboardController::class, 'index'])->name('dashboard');

    // profil
    Route::get('/profile', [UmkmProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [UmkmProfileController::class, 'update'])->name('profile.update');

    // bahan baku
    Route::get('/bahan-baku', [BahanBakuController::class, 'index'])->name('bahan.index');
    Route::get('/bahan-baku/create', [BahanBakuController::class, 'create'])->name('bahan.create');
    Route::post('/bahan-baku', [BahanBakuController::class, 'store'])->name('bahan.store');
    Route::get('/bahan-baku/{bahan}/edit', [BahanBakuController::class, 'edit'])->name('bahan.edit');
    Route::put('/bahan-baku/{bahan}', [BahanBakuController::class, 'update'])->name('bahan.update');
    Route::delete('/bahan-baku/{bahan}', [BahanBakuController::class, 'destroy'])->name('bahan.destroy');

    // produk
    Route::resource('/produk', ProdukController::class)->names('produk')->except(['show']);
    Route::post('/produk/{produk}/hitung-hpp', [ProdukController::class, 'hitungHpp'])->name('produk.hitungHpp');

    // pembelian
    Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
    Route::get('/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
    Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');

    // anggaran
    Route::get('/anggaran', [UmkmAnggaranController::class, 'index'])->name('anggaran.index');
    Route::post('/anggaran', [UmkmAnggaranController::class, 'store'])->name('anggaran.store');

    // produksi
    Route::get('/produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::get('/produksi/create', [ProduksiController::class, 'create'])->name('produksi.create');
    Route::post('/produksi', [ProduksiController::class, 'store'])->name('produksi.store');

    // penjualan
    Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::get('/penjualan/create', [PenjualanController::class, 'create'])->name('penjualan.create');
    Route::post('/penjualan', [PenjualanController::class, 'store'])->name('penjualan.store');

    // laporan
    Route::get('/laporan', [LaporanKeuanganController::class, 'index'])->name('laporan.index');

    // coa (JANGAN DOBEL)
    Route::resource('/coa', CoaController::class)->names('coa')->except(['show']);
    Route::get('/coa/preview', [CoaController::class, 'preview'])->name('coa.preview');
});
