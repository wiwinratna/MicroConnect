<?php

namespace App\Services;

use App\Models\StokMutasi;
use App\Models\Umkm;
use Illuminate\Support\Collection;

/**
 * InventoryService
 *
 * Menangani penghitungan stok, HPP, dan simulasi Kartu Stok (Ledger) 
 * berdasarkan konfigurasi per UMKM (FIFO / LIFO / Average).
 */
class InventoryService
{
    /**
     * Hitung stok saat ini (Global Qty Balance).
     */
    public function getStok(int $umkmId, string $itemType, int $itemId): float
    {
        $query = StokMutasi::where('umkm_id', $umkmId);

        if ($itemType === 'bahan') {
            $query->where('bahan_id', $itemId)->whereNull('produk_id');
        } else {
            $query->where('produk_id', $itemId)->whereNull('bahan_id');
        }

        $masuk  = (float) $query->clone()->where('jenis', 'MASUK')->sum('qty');
        $keluar = (float) $query->clone()->where('jenis', 'KELUAR')->sum('qty');

        return max(0, $masuk - $keluar);
    }

    /**
     * Hitung HPP (Harga Pokok) untuk qty tertentu YANG AKAN KELUAR.
     * Menggunakan simulasi ledger agar konsumsi batch sinkron dengan riwayat.
     */
    public function hitungHpp(Umkm $umkm, string $itemType, int $itemId, float $qtyKeluar): float
    {
        if ($qtyKeluar <= 0) return 0;
        
        // Simulasikan ledger sampai detik ini (tanpa transaksi baru ini)
        $ledgerResult = $this->buildLedger($umkm, $itemType, $itemId);
        
        $method = strtoupper($umkm->inventory_method ?? 'AVERAGE');
        $activeBatches = $ledgerResult['activeBatches']; // batch yang msh mpy saldo
        
        return match ($method) {
            'FIFO'    => $this->consumeFifo($activeBatches, $qtyKeluar)['totalHpp'],
            'LIFO'    => $this->consumeLifo($activeBatches, $qtyKeluar)['totalHpp'],
            default   => $this->consumeAverage(
                $activeBatches, 
                $qtyKeluar, 
                $ledgerResult['totalSaldoQty'] > 0 ? ($ledgerResult['totalSaldoNilai'] / $ledgerResult['totalSaldoQty']) : 0
            )['totalHpp'],
        };
    }

    /**
     * Membangun simulasi Ledger Buku Besar Stok (Kartu Stok).
     * Melakukan iterasi dari mutasi pertama hingga terakhir, mencatat masuk dan cara keluarnya.
     * 
     * @return array { ledger: array, activeBatches: array, totalSaldoQty: float, totalSaldoNilai: float }
     */
    public function buildLedger(Umkm $umkm, string $itemType, int $itemId, ?string $endDate = null): array
    {
        $method = strtoupper($umkm->inventory_method ?? 'AVERAGE');
        
        $query = StokMutasi::where('umkm_id', $umkm->id);
        if ($itemType === 'bahan') {
            $query->where('bahan_id', $itemId)->whereNull('produk_id');
        } else {
            $query->where('produk_id', $itemId)->whereNull('bahan_id');
        }
        
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }
        
        $mutasiAll = $query->orderBy('tanggal')->orderBy('id')->get();
        
        $ledger = [];
        $activeBatches = []; // Menampung batch MASUK yang qty-nya masih > 0
        
        $saldoQty   = 0;
        $saldoNilai = 0;

        foreach ($mutasiAll as $mutasi) {
            $row = [
                'id'            => $mutasi->id,
                'tanggal'       => $mutasi->tanggal,
                'jenis'         => $mutasi->jenis,
                'ref_tipe'      => $mutasi->ref_tipe,
                'ref_id'        => $mutasi->ref_id,
                'masuk_qty'     => 0,
                'masuk_harga'   => 0,
                'keluar_qty'    => 0,
                'keluar_detail' => [], // array breakdown konsumsi
                'saldo_qty'     => 0,
                'saldo_nilai'   => 0,
                'avg_price'     => 0,  // untuk Average
            ];

            $qtyT = (float) $mutasi->qty;
            $hargaT = (float) ($mutasi->harga_unit ?? 0);

            if ($mutasi->jenis === 'MASUK') {
                $row['masuk_qty']   = $qtyT;
                $row['masuk_harga'] = $hargaT;
                
                $saldoQty   += $qtyT;
                $saldoNilai += ($qtyT * $hargaT);
                
                // Tambah ke active batches
                array_push($activeBatches, [
                    'id'       => $mutasi->id,
                    'tanggal'  => $mutasi->tanggal,
                    'ref_tipe' => $mutasi->ref_tipe,
                    'ref_id'   => $mutasi->ref_id,
                    'qty'      => $qtyT,
                    'harga'    => $hargaT
                ]);
                
            } else { // KELUAR
                $row['keluar_qty'] = $qtyT;
                $row['masuk_harga'] = 0; // gak dipake
                
                // Proses konsumsi dari $activeBatches
                $consumeResult = match ($method) {
                    'FIFO'    => $this->consumeFifo($activeBatches, $qtyT),
                    'LIFO'    => $this->consumeLifo($activeBatches, $qtyT),
                    default   => $this->consumeAverage($activeBatches, $qtyT, $avgPrice ?? 0),
                };
                
                $row['keluar_detail'] = $consumeResult['details']; // riwayat batch mana yg diambil
                $totalHpp = $consumeResult['totalHpp'];
                $activeBatches = $consumeResult['remainingBatches'];
                
                $saldoQty   -= $qtyT;
                $saldoNilai -= $totalHpp;
                
                // Pencegahan saldo negatif (sanity check float)
                if ($saldoQty < 0.0001) { $saldoQty = 0; $saldoNilai = 0; }
            }
            
            // Recalculate Average Price jika method AVERAGE (hanya saat MASUK)
            if ($mutasi->jenis === 'MASUK') {
                $avgPrice = $saldoQty > 0 ? ($saldoNilai / $saldoQty) : 0;
            }
            
            $row['saldo_qty']   = $saldoQty;
            $row['saldo_nilai'] = $saldoNilai;
            $row['avg_price']   = round($avgPrice ?? 0, 2);
            
            $ledger[] = $row;
        }
        
        return [
            'ledger'           => $ledger,
            'activeBatches'    => $activeBatches,
            'totalSaldoQty'    => $saldoQty,
            'totalSaldoNilai'  => $saldoNilai
        ];
    }
    
    // =====================================================================
    // KONSUMSI BATCH METHODS (Pure Functions - return modified arrays)
    // =====================================================================

    private function consumeFifo(array $batches, float $qtyKeluar): array
    {
        $remaining = $qtyKeluar;
        $totalHpp  = 0;
        $details   = []; // info batch mana yg dikonsumsi
        $newBatches = [];

        foreach ($batches as $index => $b) {
            $bQty   = $b['qty'];
            $bHarga = $b['harga'];

            if ($remaining > 0 && $bQty > 0) {
                if ($bQty <= $remaining) {
                    // Habiskan batch ini
                    $totalHpp += ($bQty * $bHarga);
                    $remaining -= $bQty;
                    $details[] = [
                        'qty'     => $bQty, 
                        'harga'   => $bHarga,
                        'batch'   => strtoupper($b['ref_tipe']) . ' #' . $b['ref_id'],
                        'tanggal' => $b['tanggal']
                    ];
                    // Skip push ke newBatches karena sudah nol
                } else {
                    // Sisa batch masih ada
                    $dipakai = $remaining;
                    $totalHpp += ($dipakai * $bHarga);
                    $remaining = 0;
                    $details[] = [
                        'qty'     => $dipakai, 
                        'harga'   => $bHarga,
                        'batch'   => strtoupper($b['ref_tipe']) . ' #' . $b['ref_id'],
                        'tanggal' => $b['tanggal']
                    ];
                    
                    $newBatches[] = [
                        'id'       => $b['id'],
                        'tanggal'  => $b['tanggal'],
                        'ref_tipe' => $b['ref_tipe'],
                        'ref_id'   => $b['ref_id'],
                        'qty'      => $bQty - $dipakai,
                        'harga'    => $bHarga
                    ];
                }
            } else {
                // Batch yang tidak tersentuh
                $newBatches[] = $b;
            }
        }

        return [
            'totalHpp'         => round($totalHpp, 2),
            'remainingBatches' => $newBatches,
            'details'          => $details
        ];
    }

    private function consumeLifo(array $batches, float $qtyKeluar): array
    {
        // Reverse array untuk LIFO (ambil dari belakang)
        $reversedBatches = array_reverse($batches, true);
        
        $remaining = $qtyKeluar;
        $totalHpp  = 0;
        $details   = [];
        $newBatchesMap = []; // Pakai key index asal untuk merestore susunan

        foreach ($reversedBatches as $index => $b) {
            $bQty   = $b['qty'];
            $bHarga = $b['harga'];

            if ($remaining > 0 && $bQty > 0) {
                if ($bQty <= $remaining) {
                    // Habiskan batch ini
                    $totalHpp += ($bQty * $bHarga);
                    $remaining -= $bQty;
                    $details[] = [
                        'qty'     => $bQty, 
                        'harga'   => $bHarga,
                        'batch'   => strtoupper($b['ref_tipe']) . ' #' . $b['ref_id'],
                        'tanggal' => $b['tanggal']
                    ];
                    $newBatchesMap[$index] = null; // Terhapus
                } else {
                    // Sisa batch
                    $dipakai = $remaining;
                    $totalHpp += ($dipakai * $bHarga);
                    $remaining = 0;
                    $details[] = [
                        'qty'     => $dipakai, 
                        'harga'   => $bHarga,
                        'batch'   => strtoupper($b['ref_tipe']) . ' #' . $b['ref_id'],
                        'tanggal' => $b['tanggal']
                    ];
                    $newBatchesMap[$index] = [
                        'id'       => $b['id'],
                        'tanggal'  => $b['tanggal'],
                        'ref_tipe' => $b['ref_tipe'],
                        'ref_id'   => $b['ref_id'],
                        'qty'      => $bQty - $dipakai,
                        'harga'    => $bHarga
                    ];
                }
            } else {
                $newBatchesMap[$index] = $b; // Tidak tersentuh
            }
        }

        // Restore urutan awal (FIFO order) untuk $activeBatches
        $newBatches = [];
        foreach ($batches as $origIndex => $origVal) {
            if (isset($newBatchesMap[$origIndex])) {
                $newBatches[] = $newBatchesMap[$origIndex];
            }
        }

        return [
            'totalHpp'         => round($totalHpp, 2),
            'remainingBatches' => $newBatches,
            'details'          => $details
        ];
    }

    private function consumeAverage(array $batches, float $qtyKeluar, float $runningAvgPrice): array
    {
        // Total HPP uses the strictly running average price, NOT recalculating from remaining physical batches
        $totalHpp = round($qtyKeluar * $runningAvgPrice, 2);

        $details = [['qty' => $qtyKeluar, 'harga' => $runningAvgPrice, 'is_avg' => true]];

        // Kurangi qty dari batches secara proporsional atau dari depan
        // Kita cukup pakai FIFO order untuk mengurangi qty fisik-nya saja.
        $resFifo = $this->consumeFifo($batches, $qtyKeluar);
        
        return [
            'totalHpp'         => $totalHpp, 
            'remainingBatches' => $resFifo['remainingBatches'], 
            'details'          => $details
        ];
    }
}
