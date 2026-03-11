<?php

namespace App\Services;

use App\Models\Umkm;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\PiutangPembayaran;
use App\Models\JurnalUmum;
use Illuminate\Support\Facades\DB;

/**
 * AccountingService
 * 
 * Mengatur logic penjurnalan transaksi berdasarkan metode pencatatan
 * (Periodik / Perpetual).
 */
class AccountingService
{
    /**
     * Penjurnalan transaksi Penjualan.
     * 
     * @param Umkm $umkm UMKM profile untuk cek metode
     * @param Penjualan $penjualan Header penjualan
     * @param float $total nominal total penjualan
     * @param float $totalHpp total HPP produk yang terjual
     * @param bool $isKredit tunai atau piutang
     * @param Piutang|null $piutang record piutang (jika kredit)
     */
    public function jurnalPenjualan(Umkm $umkm, Penjualan $penjualan, float $total, float $totalHpp, bool $isKredit, ?Piutang $piutang = null)
    {
        // 1. Dapatkan COA
        $akunKas = $this->getAkun($umkm->id, '111', 'Kas');
        $akunPiutang = $this->getAkun($umkm->id, '112', 'Piutang Usaha');
        $akunPendapatan = $this->getAkun($umkm->id, '400', 'Pendapatan Penjualan');
        $akunHpp = $this->getAkun($umkm->id, '500', 'Harga Pokok Penjualan');
        $akunPersediaan = $this->getAkun($umkm->id, '113', 'Persediaan Produk');

        $ketPenjualan = 'Penjualan ' . $penjualan->kode_penjualan . ($penjualan->pembeli ? ' - ' . $penjualan->pembeli : '');

        $base = [
            'umkm_id'    => $umkm->id,
            'tanggal'    => $penjualan->tanggal,
            'ref_tipe'   => 'penjualan',
            'ref_id'   => $penjualan->id
        ];

        // Bersihkan jurnal penjualan sebelumnya jika edit/update
        JurnalUmum::where('umkm_id', $umkm->id)->where('ref_tipe', 'penjualan')->where('ref_id', $penjualan->id)->delete();

        // JURNAL PENDAPATAN
        if ($isKredit) {
            // Dr. Piutang Usaha
            $this->catat($base, $akunPiutang, $total, 0, $ketPenjualan . ' (Kredit)');
            // Cr. Pendapatan Penjualan
            $this->catat($base, $akunPendapatan, 0, $total, $ketPenjualan . ' (Kredit)');
        } else {
            // Dr. Kas
            $this->catat($base, $akunKas, $total, 0, $ketPenjualan . ' (Tunai)');
            // Cr. Pendapatan Penjualan
            $this->catat($base, $akunPendapatan, 0, $total, $ketPenjualan . ' (Tunai)');
        }

        // JURNAL HPP (Jika Perpetual)
        if (strtoupper($umkm->recording_method ?? 'PERIODIK') === 'PERPETUAL' && $totalHpp > 0) {
            $ketHpp = 'HPP atas ' . $penjualan->kode_penjualan;
            // Dr. HPP
            $this->catat($base, $akunHpp, $totalHpp, 0, $ketHpp);
            // Cr. Persediaan Produk
            $this->catat($base, $akunPersediaan, 0, $totalHpp, $ketHpp);
        }
    }

    /**
     * Helper untuk mengambil data COA
     */
    private function getAkun(int $umkmId, string $kode, string $defaultNama)
    {
        $akun = DB::table('coa')
            ->where('umkm_id', $umkmId)
            ->where('kode_akun', $kode)
            ->first();

        if (!$akun) {
            // Auto create jika belum ada (opsional, tapi disarankan agar jurnal tidak error)
            $id = DB::table('coa')->insertGetId([
                'umkm_id' => $umkmId,
                'kode_akun' => $kode,
                'nama_akun' => $defaultNama,
                'header_akun' => substr($kode, 0, 1),
                'posisi_dr_cr' => in_array(substr($kode, 0, 1), ['1','5','6']) ? 'debit' : 'kredit',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return DB::table('coa')->where('id', $id)->first();
        }

        return $akun;
    }

    /**
     * Penjurnalan transaksi Pembelian Bahan Baku.
     */
    public function jurnalPembelian(Umkm $umkm, \App\Models\Pembelian $pembelian, float $total)
    {
        $akunKas = $this->getAkun($umkm->id, '111', 'Kas');
        
        $base = [
            'umkm_id'    => $umkm->id,
            'tanggal'    => $pembelian->tanggal,
            'ref_tipe'   => 'pembelian',
            'ref_id'     => $pembelian->id
        ];

        JurnalUmum::where('umkm_id', $umkm->id)->where('ref_tipe', 'pembelian')->where('ref_id', $pembelian->id)->delete();

        $ketBeli = 'Pembelian ' . $pembelian->kode_pembelian . ($pembelian->supplier ? ' dari ' . $pembelian->supplier : '');

        if (strtoupper($umkm->recording_method ?? 'PERIODIK') === 'PERPETUAL') {
            $akunPersediaanBahan = $this->getAkun($umkm->id, '114', 'Persediaan Bahan');
            // Dr. Persediaan Bahan
            $this->catat($base, $akunPersediaanBahan, $total, 0, $ketBeli);
            // Cr. Kas
            $this->catat($base, $akunKas, 0, $total, $ketBeli);
        } else {
            // Periodik
            $akunPembelian = $this->getAkun($umkm->id, '510', 'Pembelian Bahan/Barang');
            // Dr. Pembelian
            $this->catat($base, $akunPembelian, $total, 0, $ketBeli);
            // Cr. Kas
            $this->catat($base, $akunKas, 0, $total, $ketBeli);
        }
    }

    /**
     * Penjurnalan saat pelanggan membayar piutang.
     */
    public function jurnalPembayaranPiutang(Umkm $umkm, \App\Models\PiutangPembayaran $pembayaran)
    {
        $akunKas = $this->getAkun($umkm->id, '111', 'Kas');
        $akunPiutang = $this->getAkun($umkm->id, '112', 'Piutang Usaha');

        $base = [
            'umkm_id'    => $umkm->id,
            'tanggal'    => $pembayaran->tanggal_bayar,
            'ref_tipe'   => 'pembayaran_piutang',
            'ref_id'     => $pembayaran->id
        ];

        JurnalUmum::where('umkm_id', $umkm->id)->where('ref_tipe', 'pembayaran_piutang')->where('ref_id', $pembayaran->id)->delete();

        $piutang = $pembayaran->piutang;
        $ket = 'Terima Piutang ' . ($piutang ? $piutang->kode_piutang : '') . ' - ' . $pembayaran->keterangan;

        // Dr. Kas
        $this->catat($base, $akunKas, $pembayaran->nominal_dibayar, 0, $ket);
        
        // Cr. Piutang Usaha
        $this->catat($base, $akunPiutang, 0, $pembayaran->nominal_dibayar, $ket);
    }

    /**
     * Helper untuk insert ke tabel Jurnal Umum
     */
    private function catat(array $base, $akun, float $debit, float $kredit, string $keterangan)
    {
        JurnalUmum::create(array_merge($base, [
            'kode_akun' => $akun->kode_akun,
            'nama_akun' => $akun->nama_akun,
            'debit'     => $debit,
            'kredit'    => $kredit,
            'keterangan'=> $keterangan
        ]));
    }
}
