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
use App\Http\Controllers\Admin\IuranPeriodeController;
use App\Http\Controllers\MidtransWebhookController;

// ================== ROOT ==================
Route::get('/', function () {
    return view('landing');
})->name('landing');

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

// ================== GANTI PASSWORD (WAJIB) ==================
Route::middleware('auth')->group(function () {
    Route::get('/ganti-password', [AuthController::class, 'changePassword'])->name('password.change');
    Route::post('/ganti-password', [AuthController::class, 'updatePassword'])->name('password.change.update');
});

// ================== MIDTRANS WEBHOOK ==================
// Tanpa auth — Midtrans mengirim POST langsung ke URL ini
// Daftarkan di Midtrans Dashboard → Settings → Payment Notification URL
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.notification');

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

    // ==================== IURAN PERIODE ====================
    Route::prefix('iuran-periode')->name('iuran-periode.')->group(function () {
        Route::get('/', [IuranPeriodeController::class, 'index'])->name('index');
        Route::get('/create', [IuranPeriodeController::class, 'create'])->name('create');
        Route::post('/', [IuranPeriodeController::class, 'store'])->name('store');
        Route::get('/{id}', [IuranPeriodeController::class, 'show'])->name('show');
        Route::post('/{periodeId}/konfirmasi/{iuranId}', [IuranPeriodeController::class, 'konfirmasiLunas'])->name('konfirmasi');
    });

    // Ticketing / Pengaduan UMKM
    Route::get('/tickets', [\App\Http\Controllers\Admin\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\Admin\TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\Admin\TicketController::class, 'reply'])->name('tickets.reply');
    Route::patch('/tickets/{ticket}/status', [\App\Http\Controllers\Admin\TicketController::class, 'updateStatus'])->name('tickets.status');
});


// ================== UMKM AREA ==================
Route::prefix('umkm')->name('umkm.')->middleware(['auth', 'pelakuusaha', 'must_change_password'])->group(function () {

    // Pilih Level
    Route::get('/pilih-level', [UmkmLevelController::class, 'index'])->name('level.choose');
    Route::post('/pilih-level', [UmkmLevelController::class, 'store'])->name('level.store');

    // Dashboard
    Route::get('/dashboard', [UmkmDashboardController::class, 'index'])->name('dashboard');

    // Profil (termasuk konfigurasi recording_method & inventory_method)
    Route::get('/profile', [UmkmProfileController::class, 'edit'])->name('profile');
    Route::put('/profile', [UmkmProfileController::class, 'update'])->name('profile.update');

    // Bahan Baku
    Route::get('/bahan-baku/search', [BahanBakuController::class, 'search'])->name('bahan.search');
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
    Route::get('/pembelian/{id}/edit', [PembelianController::class, 'edit'])->name('pembelian.edit');
    Route::put('/pembelian/{id}', [PembelianController::class, 'update'])->name('pembelian.update');
    Route::delete('/pembelian/{id}', [PembelianController::class, 'destroy'])->name('pembelian.destroy');


    // [NONAKTIF SEMENTARA] Anggaran Estimasi - dinonaktifkan per permintaan revisi
    // Route::get('/anggaran', [UmkmAnggaranController::class, 'index'])->name('anggaran.index');
    // Route::post('/anggaran', [UmkmAnggaranController::class, 'store'])->name('anggaran.store');


    // Produksi
    Route::get('/produksi', [ProduksiController::class, 'index'])->name('produksi.index');
    Route::get('/produksi/create', [ProduksiController::class, 'create'])->name('produksi.create');
    Route::post('/produksi', [ProduksiController::class, 'store'])->name('produksi.store');

    // Penjualan
    Route::get('/penjualan', [PenjualanController::class, 'index'])->name('penjualan.index');
    Route::get('/penjualan/create', [PenjualanController::class, 'create'])->name('penjualan.create');
    Route::post('/penjualan', [PenjualanController::class, 'store'])->name('penjualan.store');
    Route::get('/penjualan/{id}/edit', [PenjualanController::class, 'edit'])->name('penjualan.edit');
    Route::put('/penjualan/{id}', [PenjualanController::class, 'update'])->name('penjualan.update');
    Route::delete('/penjualan/{id}', [PenjualanController::class, 'destroy'])->name('penjualan.destroy');

    // Mode Etalase
    Route::prefix('etalase')->name('etalase.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Umkm\EtalaseController::class, 'index'])->name('index');
        Route::post('/checkout', [\App\Http\Controllers\Umkm\EtalaseController::class, 'checkout'])->name('checkout');
        Route::get('/nota/{id}', [\App\Http\Controllers\Umkm\EtalaseController::class, 'nota'])->name('nota');
    });
    // Ekspor (PDF/Excel) — dilindungi middleware feature level
    Route::prefix('export')->name('export.')->group(function () {
        // Level 1: jurnal umum, laporan pembelian, laporan penjualan
        Route::get('/jurnal-umum', [\App\Http\Controllers\Umkm\ExportController::class, 'jurnalUmum'])->name('jurnal_umum')->middleware('umkm.feature:export_jurnal_umum');
        Route::get('/laporan-pembelian', [\App\Http\Controllers\Umkm\ExportController::class, 'laporanPembelian'])->name('laporan_pembelian')->middleware('umkm.feature:export_laporan_pembelian');
        Route::get('/laporan-penjualan', [\App\Http\Controllers\Umkm\ExportController::class, 'laporanPenjualan'])->name('laporan_penjualan')->middleware('umkm.feature:export_laporan_penjualan');

        // Level 2: buku besar, laba rugi, kartu stok, rekap stok, piutang
        Route::get('/buku-besar', [\App\Http\Controllers\Umkm\ExportController::class, 'bukuBesar'])->name('buku_besar')->middleware('umkm.feature:export_buku_besar');
        Route::get('/laba-rugi', [\App\Http\Controllers\Umkm\ExportController::class, 'labaRugi'])->name('laba_rugi')->middleware('umkm.feature:export_laba_rugi');
        Route::get('/rekap-stok', [\App\Http\Controllers\Umkm\ExportController::class, 'rekapStok'])->name('rekap_stok')->middleware('umkm.feature:export_rekap_stok');
        Route::get('/kartu-stok', [\App\Http\Controllers\Umkm\ExportController::class, 'kartuStokDetail'])->name('kartu_stok')->middleware('umkm.feature:export_kartu_stok');
        Route::get('/laporan-piutang', [\App\Http\Controllers\Umkm\ExportController::class, 'laporanPiutang'])->name('laporan_piutang')->middleware('umkm.feature:export_laporan_piutang');

        // Level 3: perubahan modal, arus kas
        Route::get('/perubahan-modal', [\App\Http\Controllers\Umkm\ExportController::class, 'perubahanModal'])->name('perubahan_modal')->middleware('umkm.feature:export_perubahan_modal');
        Route::get('/arus-kas', [\App\Http\Controllers\Umkm\ExportController::class, 'arusKas'])->name('arus_kas')->middleware('umkm.feature:export_arus_kas');
    });

    // Laporan (semua level bisa akses halaman laporan, tapi konten di-guard di view)
    Route::get('/laporan', [LaporanKeuanganController::class, 'index'])->name('laporan.index');

    // Kartu Stok — Level 2+
    Route::get('/kartu-stok', [\App\Http\Controllers\Umkm\KartuStokController::class, 'index'])->name('laporan.kartu_stok')->middleware('umkm.feature:kartu_stok');

    // Beban Operasional — Level 1+
    Route::get('/beban', [\App\Http\Controllers\Umkm\BebanController::class, 'index'])->name('beban.index');
    Route::get('/beban/create', [\App\Http\Controllers\Umkm\BebanController::class, 'create'])->name('beban.create');
    Route::post('/beban', [\App\Http\Controllers\Umkm\BebanController::class, 'store'])->name('beban.store');

    // Jurnal Umum — Level 1+
    Route::get('/jurnal', [\App\Http\Controllers\Umkm\JurnalController::class, 'index'])->name('jurnal.index')->middleware('umkm.feature:jurnal_umum');
    Route::get('/jurnal/create', [\App\Http\Controllers\Umkm\JurnalController::class, 'create'])->name('jurnal.create')->middleware('umkm.feature:jurnal_umum');
    Route::post('/jurnal', [\App\Http\Controllers\Umkm\JurnalController::class, 'store'])->name('jurnal.store')->middleware('umkm.feature:jurnal_umum');

    // COA — Level 3 only
    Route::resource('/coa', CoaController::class)->names('coa')->except(['show'])->middleware('umkm.feature:coa');
    Route::get('/coa/preview', [CoaController::class, 'preview'])->name('coa.preview')->middleware('umkm.feature:coa');

    // Ticketing / Pengaduan
    Route::get('/tickets', [\App\Http\Controllers\Umkm\TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [\App\Http\Controllers\Umkm\TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [\App\Http\Controllers\Umkm\TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [\App\Http\Controllers\Umkm\TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/reply', [\App\Http\Controllers\Umkm\TicketController::class, 'reply'])->name('tickets.reply');

    // ==================== IURAN BULANAN ====================
    Route::get('/iuran', [IuranController::class, 'index'])->name('iuran.index');
    Route::post('/iuran/{id}/bayar', [IuranController::class, 'bayar'])->name('iuran.bayar');

    // ==================== MODE ETALASE / KASIR ====================
    Route::prefix('kasir')->name('etalase.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Umkm\EtalaseController::class, 'index'])->name('index');
        Route::post('/checkout', [\App\Http\Controllers\Umkm\EtalaseController::class, 'checkout'])->name('checkout');
        Route::get('/nota/{id}', [\App\Http\Controllers\Umkm\EtalaseController::class, 'nota'])->name('nota');
        // Pelanggan
        Route::get('/pelanggan', [PiutangController::class, 'indexPelanggan'])->name('pelanggan.index');
        Route::get('/pelanggan/create', [PiutangController::class, 'createPelanggan'])->name('pelanggan.create');
        Route::post('/pelanggan', [PiutangController::class, 'storePelanggan'])->name('pelanggan.store');
        Route::get('/pelanggan/{pelanggan}/edit', [PiutangController::class, 'editPelanggan'])->name('pelanggan.edit');
        Route::put('/pelanggan/{pelanggan}', [PiutangController::class, 'updatePelanggan'])->name('pelanggan.update');
        Route::delete('/pelanggan/{pelanggan}', [PiutangController::class, 'destroyPelanggan'])->name('pelanggan.destroy');
    });

    // ==================== PIUTANG — Level 2+ ====================
    Route::prefix('piutang')->name('piutang.')->middleware('umkm.feature:piutang')->group(function () {
        Route::get('/', [PiutangController::class, 'index'])->name('index');
        Route::get('/create', [PiutangController::class, 'create'])->name('create');
        Route::post('/', [PiutangController::class, 'store'])->name('store');
        Route::get('/{piutang}', [PiutangController::class, 'show'])->name('show');
        Route::post('/{piutang}/bayar', [PiutangController::class, 'bayar'])->name('bayar');

        // Email Reminders
        Route::get('/{piutang}/email/preview', [\App\Http\Controllers\Umkm\PiutangEmailController::class, 'preview'])->name('email.preview');
        Route::post('/{piutang}/email/send', [\App\Http\Controllers\Umkm\PiutangEmailController::class, 'sendManual'])->name('email.send');
    });
});
