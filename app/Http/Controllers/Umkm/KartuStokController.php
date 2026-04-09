<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BahanBaku;
use App\Services\InventoryService;
use Carbon\Carbon;

class KartuStokController extends Controller
{
    public function index(Request $request)
    {
        $umkm = auth()->user()->umkm;
        $bahanId = $request->get('bahan_id');
        $bulan   = $request->get('bulan', now()->format('Y-m'));

        $bahanList = BahanBaku::where('umkm_id', $umkm->id)->orderBy('nama_bahan')->get();
        
        $ledger        = [];
        $saldoAwalQty     = 0;
        $saldoAwalNilai   = 0;
        $saldoAwalBatches = [];
        $selectedBahan    = null;
        $activeBatches    = [];

        if ($bahanId) {
            $selectedBahan = BahanBaku::where('umkm_id', $umkm->id)->find($bahanId);

            if ($selectedBahan) {
                // Gunakan InventoryService untuk mem-build ledger dari awal
                $invService = new InventoryService();
                $result     = $invService->buildLedger($umkm, 'bahan', $selectedBahan->id);
                $fullLedger = $result['ledger'];
                $activeBatches = $result['activeBatches'];

                // Filter hanya yang terjadi di bulan terpilih.
                // Tapi kita harus cari tahu "Saldo Awal" tepat sebelum tanggal 1 bulan terpilih.
                [$tahun, $bln] = explode('-', $bulan);
                $awalBulan  = "$tahun-$bln-01";
                $akhirBulan = date('Y-m-t', strtotime($awalBulan));

                foreach ($fullLedger as $row) {
                    $tglStr = Carbon::parse($row['tanggal'])->format('Y-m-d');

                    if ($tglStr < $awalBulan) {
                        // Terjadi sebelum bulan terpilih, catat ini untuk Saldo Awal terakhir
                        $saldoAwalQty     = $row['saldo_qty'];
                        $saldoAwalNilai   = $row['saldo_nilai'];
                        $saldoAwalBatches = $row['active_batches_snapshot'] ?? [];
                    } elseif ($tglStr <= $akhirBulan) {
                        // Terjadi DI DALAM bulan terpilih
                        $ledger[] = $row;
                    }
                }

                // Ambil activeBatches tepat di akhir waktu yang dipilih
                if (!empty($ledger)) {
                    $lastRow = end($ledger);
                    $activeBatches = $lastRow['active_batches_snapshot'] ?? [];
                } else {
                    $activeBatches = $saldoAwalBatches;
                }
            }
        }

        return view('umkm.laporan.kartu_stok', compact(
            'umkm',
            'bahanList',
            'bahanId',
            'bulan',
            'selectedBahan',
            'saldoAwalQty',
            'saldoAwalNilai',
            'saldoAwalBatches',
            'ledger',
            'activeBatches'
        ));
    }
}
