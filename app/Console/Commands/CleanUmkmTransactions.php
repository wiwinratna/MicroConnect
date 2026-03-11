<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Umkm;
use Illuminate\Support\Facades\DB;
use App\Models\JurnalUmum;
use App\Models\StokMutasi;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\AnggaranBulanan;
use App\Models\BahanBaku;
use App\Models\Produk;

class CleanUmkmTransactions extends Command
{
    protected $signature = 'umkm:clean-transactions {email} {--execute}';
    protected $description = 'Bersihkan transaksi UMKM berdasarkan email tanpa menghapus profil UMKM, User, Master Bahan/Produk, dan COA.';

    public function handle()
    {
        $email = $this->argument('email');
        $execute = $this->option('execute');

        // 1. Cari user & UMKM
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User dengan email {$email} tidak ditemukan.");
            return;
        }

        $umkm = Umkm::where('user_id', $user->id)->first();
        if (!$umkm) {
            $this->error("UMKM untuk user {$email} tidak ditemukan.");
            return;
        }

        $this->info("Ditemukan UMKM: {$umkm->nama_perusahaan} (ID: {$umkm->id})");

        // 2. Hitung jumlah data transaksi yang akan dihapus
        $counts = [
            'Jurnal Umum' => JurnalUmum::where('umkm_id', $umkm->id)->count(),
            'Stok Mutasi' => StokMutasi::where('umkm_id', $umkm->id)->count(),
            'Pembelian / Kulakan' => Pembelian::where('umkm_id', $umkm->id)->count(),
            'Penjualan'   => Penjualan::where('umkm_id', $umkm->id)->count(),
            'Piutang'     => Piutang::where('umkm_id', $umkm->id)->count(),
            'Anggaran Bulanan'  => AnggaranBulanan::where('umkm_id', $umkm->id)->count(),
        ];

        $this->table(['Tabel / Modul', 'Jumlah Baris Dihapus'], collect($counts)->map(fn($val, $key) => [$key, $val]));

        if (!$execute) {
            $this->warn("\nIni hanya PREVIEW.");
            $this->warn("Gunakan flag --execute untuk benar-benar menghapus data transaksi di atas.");
            $this->warn("Contoh: php artisan umkm:clean-transactions {$email} --execute");
            return;
        }

        if (!$this->confirm("Apakah kamu yakin ingin MENGHAPUS SEMUA TRANSAKSI di atas secara PERMANEN?")) {
            $this->info("Dibatalkan.");
            return;
        }

        // 3. Eksekusi Penghapusan dengan Transaction
        DB::beginTransaction();
        try {
            $this->info("Sedang menghapus data...");

            // Jurnal Umum
            JurnalUmum::where('umkm_id', $umkm->id)->delete();
            
            // Stok Mutasi
            StokMutasi::where('umkm_id', $umkm->id)->delete();
            
            // Penjualan & Details
            $penjualanIds = Penjualan::where('umkm_id', $umkm->id)->pluck('id');
            if ($penjualanIds->isNotEmpty()) {
                DB::table('penjualan_detail')->whereIn('penjualan_id', $penjualanIds)->delete();
                Penjualan::where('umkm_id', $umkm->id)->delete();
            }

            // Pembelian & Details
            $pembelianIds = Pembelian::where('umkm_id', $umkm->id)->pluck('id');
            if ($pembelianIds->isNotEmpty()) {
                DB::table('pembelian_detail')->whereIn('pembelian_id', $pembelianIds)->delete();
                Pembelian::where('umkm_id', $umkm->id)->delete();
            }

            // Piutang & Pembayaran
            $piutangIds = Piutang::where('umkm_id', $umkm->id)->pluck('id');
            if ($piutangIds->isNotEmpty()) {
                DB::table('piutang_pembayaran')->whereIn('piutang_id', $piutangIds)->delete();
                Piutang::where('umkm_id', $umkm->id)->delete();
            }
            
            // Anggaran
            AnggaranBulanan::where('umkm_id', $umkm->id)->delete();

            // Reset Stok Master Bahan dan Produk menjadi 0 agar siap input awal lagi
            BahanBaku::where('umkm_id', $umkm->id)->update(['stok_awal' => 0]);
            Produk::where('umkm_id', $umkm->id)->update(['stok' => 0]);

            DB::commit();
            $this->info("Semua transaksi untuk UMKM {$umkm->nama_perusahaan} BERHASIL DIHAPUS.");
            $this->info("Master Data (User, Profil UMKM, Level, COA, Komposisi Produk) TETAP AMAN.");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Terjadi kesalahan: " . $e->getMessage());
        }
    }
}
