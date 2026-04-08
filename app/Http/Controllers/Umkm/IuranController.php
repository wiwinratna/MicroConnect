<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\IuranBulanan;
use App\Services\IuranService;
use App\Services\MidtransService;
use Illuminate\Http\Request;

class IuranController extends Controller
{
    public function __construct(
        private IuranService $iuranService,
        private MidtransService $midtransService,
    ) {}

    /**
     * Halaman iuran bulanan UMKM.
     * Tampilkan daftar tagihan iuran milik UMKM yang sedang login.
     */
    public function index()
    {
        $umkm = auth()->user()->umkm;

        // Pastikan iuran bulan ini sudah ada
        $this->iuranService->getOrCreate($umkm->id);

        $iuranList = IuranBulanan::where('umkm_id', $umkm->id)
                                 ->orderByDesc('periode')
                                 ->limit(12)
                                 ->get();

        $midtransClientKey = config('midtrans.client_key');
        $midtransSnapUrl   = config('midtrans.snap_url');

        return view('umkm.iuran.index', compact('iuranList', 'midtransClientKey', 'midtransSnapUrl'));
    }

    /**
     * UMKM klik bayar — buat SNAP token Midtrans.
     * Mengembalikan JSON dengan snap_token untuk popup.
     */
    public function bayar($id)
    {
        $umkm = auth()->user()->umkm;

        $iuran = IuranBulanan::where('id', $id)
                              ->where('umkm_id', $umkm->id)
                              ->firstOrFail();

        // Validasi: hanya bisa bayar jika belum lunas
        if (!$iuran->isBayarable()) {
            return response()->json([
                'success' => false,
                'message' => 'Tagihan ini tidak bisa dibayar (status: ' . $iuran->statusLabel() . ').',
            ], 422);
        }

        try {
            $result = $this->midtransService->createSnapTransaction($iuran);

            return response()->json([
                'success'    => true,
                'snap_token' => $result['snap_token'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
