<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\IuranBulanan;
use App\Services\IuranService;
use Illuminate\Http\Request;

class IuranController extends Controller
{
    public function __construct(private IuranService $iuranService) {}

    /**
     * Halaman iuran bulanan UMKM.
     * Tampilkan 6 bulan terakhir + bulan ini.
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

        return view('umkm.iuran.index', compact('iuranList'));
    }
}
