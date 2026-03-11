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
use App\Http\Controllers\Umkm\PiutangController;
use App\Http\Controllers\Umkm\IuranController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UmkmController;

// ================== ROOT ==================
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

// ================== ADMIN AREA ==================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'adminonly'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('umkm')->name('umkm.')->group(function () {
        Route::get('/', [UmkmController::class, 'index'])->name('index');
        Route::get('/create', [UmkmController::class, 'create'])->name('create');
        Route::post('/', [UmkmController::class, 'store'])->name('store');
        Route::get('/{id}', [UmkmController::class, 'show'])->name('show');
        Route::put('/{id}/level', [UmkmController::class, 'updateLevel'])->name('updateLevel');
        Route::post('/{id}/iuran', [UmkmController::class, 'konfirmasiIuran'])->name('konfirmasiIuran');
        Route::patch('/{id}/toggle-status', [UmkmController::class, 'toggleStatus'])->name('toggleStatus');
    });

    Route::get('/iuran', fn () => view('admin.iuran.index'))->name('iuran.index');

    // Ticketing / Pengaduan UMKM
    Route::get('/tickets', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\Admin\TicketController::class, 'reply'])->name('tickets.reply');
    Route::patch('/tickets/{ticket}/status', [\App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('tickets.status');
});


// ================== UMKM AREA ==================
Route::prefix('umkm')->name('umkm.')->middleware(['auth', 'pelakuusaha'])->group(function () {

    // Pilih Level
    Route::get('/pilih-level', [UmkmLevelController::class, 'index'])->name('level.choose');
    Route::post('/pilih-level', [UmkmLevelController::class, 'store'])->name('level.store');

    // Dashboard
    Route::get('/dashboard', [UmkmDashboardController::class, 'index'])->name('dashboard');

    // Profil (termasuk konfigurasi recording_method & inventory_method)
    Route::get('/profile', [UmkmProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [UmkmProfileController::class, 'update'])->name('profile.update');

    // Bahan Baku
    Route::get('/bahan-baku', [BahanBakuController::class, 'index'])->name('bahan.index');
    Route::get('/bahan-baku/create', [BahanBakuController::class, 'create'])->name('bahan.create');
    Route::post('/bahan-baku', [BahanBakuController::class, 'store'])->name('bahan.store');
    Route::get('/bahan-baku/{bahan}/edit', [BahanBakuController::class, 'edit'])->name('bahan.edit');
    Route::put('/bahan-baku/{bahan}', [BahanBakuController::class, 'update'])->name('bahan.update');
    Route::delete('/bahan-baku/{bahan}', [BahanBakuController::class, 'destroy'])->name('bahan.destroy');

    // Produk
    Route::resource('/produk', ProdukController::class)->names('produk')->except(['show']);
    Route::post('/produk/{produk}/hitung-hpp', [ProdukController::class, 'hitungHpp'])->name('produk.hitungHpp');

    // Pembelian
    Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
    Route::get('/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
    Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');

    // Anggaran
    Route::get('/anggaran', [UmkmAnggaranController::class, 'index'])->name('anggaran.index');
    Route::post('/anggaran', [UmkmAnggaranController::class, 'store'])->name('anggaran.store');

    // Produksi
    Route::get('/produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::get('/produksi/create', [ProduksiController::class, 'create'])->name('produksi.create');
    Route::post('/produksi', [ProduksiController::class, 'store'])->name('produksi.store');

    // Penjualan
    Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::get('/penjualan/create', [PenjualanController::class, 'create'])->name('penjualan.create');
    Route::post('/penjualan', [PenjualanController::class, 'store'])->name('penjualan.store');

    // Laporan
    Route::get('/laporan', [LaporanKeuanganController::class, 'index'])->name('laporan.index');
    Route::get('/kartu-stok', [\App\Http\Controllers\Umkm\KartuStokController::class, 'index'])->name('laporan.kartu_stok');

    // Beban Operasional
    Route::get('/beban', [\App\Http\Controllers\Umkm\BebanController::class, 'index'])->name('beban.index');
    Route::get('/beban/create', [\App\Http\Controllers\Umkm\BebanController::class, 'create'])->name('beban.create');
    Route::post('/beban', [\App\Http\Controllers\Umkm\BebanController::class, 'store'])->name('beban.store');

    // Jurnal Umum
    Route::get('/jurnal', [\App\Http\Controllers\Umkm\JurnalController::class, 'index'])->name('jurnal.index');
    Route::get('/jurnal/create', [\App\Http\Controllers\Umkm\JurnalController::class, 'create'])->name('jurnal.create');
    Route::post('/jurnal', [\App\Http\Controllers\Umkm\JurnalController::class, 'store'])->name('jurnal.store');

    // COA
    Route::resource('/coa', CoaController::class)->names('coa')->except(['show']);
    Route::get('/coa/preview', [CoaController::class, 'preview'])->name('coa.preview');

    // Ticketing / Pengaduan
    Route::get('/tickets', [\App\Http\Controllers\Umkm\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [\App\Http\Controllers\Umkm\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [\App\Http\Controllers\Umkm\TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\Umkm\TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\Umkm\TicketController::class, 'reply'])->name('tickets.reply');

    // ==================== IURAN BULANAN ====================
    Route::get('/iuran', [IuranController::class, 'index'])->name('iuran.index');

    // ==================== PIUTANG & PELANGGAN ====================
    Route::prefix('piutang')->name('piutang.')->group(function () {
        // Pelanggan
        Route::get('/pelanggan', [PiutangController::class, 'indexPelanggan'])->name('pelanggan.index');
        Route::post('/pelanggan', [PiutangController::class, 'storePelanggan'])->name('pelanggan.store');
        Route::delete('/pelanggan/{pelanggan}', [PiutangController::class, 'destroyPelanggan'])->name('pelanggan.destroy');

        // Piutang
        Route::get('/', [PiutangController::class, 'index'])->name('index');
        Route::get('/create', [PiutangController::class, 'create'])->name('create');
        Route::post('/', [PiutangController::class, 'store'])->name('store');
        Route::get('/{piutang}', [PiutangController::class, 'show'])->name('show');
        Route::post('/{piutang}/bayar', [PiutangController::class, 'bayar'])->name('bayar');
    });
});
