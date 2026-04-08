<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IuranBulanan;
use App\Models\IuranPeriode;
use App\Services\IuranService;
use Illuminate\Http\Request;

class IuranPeriodeController extends Controller
{
    public function __construct(private IuranService $iuranService) {}

    // ===================== INDEX: DAFTAR PERIODE =====================

    /**
     * Halaman admin: daftar periode iuran.
     * Menampilkan ringkasan per periode (total UMKM, lunas, belum bayar).
     */
    public function index()
    {
        $periodes = IuranPeriode::withCount([
                'iuranBulanan as total_umkm',
                'iuranBulanan as total_lunas' => function ($q) {
                    $q->where('status', 'lunas');
                },
                'iuranBulanan as total_belum_bayar' => function ($q) {
                    $q->whereIn('status', ['belum_bayar', 'pending']);
                },
            ])
            ->orderByDesc('periode')
            ->paginate(15);

        return view('admin.iuran.index', compact('periodes'));
    }

    // ===================== CREATE: FORM BUAT PERIODE =====================

    public function create()
    {
        return view('admin.iuran.create');
    }

    // ===================== STORE: SIMPAN + GENERATE TAGIHAN =====================

    /**
     * Simpan periode baru dan generate tagihan ke semua UMKM aktif.
     */
    public function store(Request $request)
    {
        $request->validate([
            'periode'         => 'required|string|size:7|unique:iuran_periode,periode',
            'nominal_default' => 'required|numeric|min:1000',
            'jatuh_tempo'     => 'required|date',
            'keterangan'      => 'nullable|string|max:500',
        ], [
            'periode.unique'        => 'Periode ini sudah ada.',
            'periode.size'          => 'Format periode harus YYYY-MM (contoh: 2026-03).',
            'nominal_default.min'   => 'Nominal minimal Rp 1.000.',
        ]);

        $periode = IuranPeriode::create([
            'periode'         => $request->periode,
            'nominal_default' => $request->nominal_default,
            'jatuh_tempo'     => $request->jatuh_tempo,
            'status'          => 'terbit',
            'keterangan'      => $request->keterangan,
            'created_by'      => auth()->id(),
        ]);

        // Generate tagihan ke semua UMKM aktif
        $generated = $this->iuranService->generateFromPeriode($periode);

        return redirect()->route('admin.iuran-periode.index')
            ->with('success', "Periode {$periode->periodeFormatted()} berhasil dibuat. {$generated} tagihan digenerate.");
    }

    // ===================== SHOW: DETAIL PERIODE =====================

    /**
     * Detail periode: daftar UMKM dan status bayar.
     */
    public function show($id)
    {
        $periode = IuranPeriode::findOrFail($id);

        $iuranList = IuranBulanan::with('umkm')
            ->where('iuran_periode_id', $id)
            ->orderByRaw("FIELD(status, 'belum_bayar', 'pending', 'lunas', 'expire', 'cancel', 'deny')")
            ->paginate(20);

        // Statistik
        $totalUmkm      = IuranBulanan::where('iuran_periode_id', $id)->count();
        $totalLunas      = IuranBulanan::where('iuran_periode_id', $id)->where('status', 'lunas')->count();
        $totalBelumBayar = IuranBulanan::where('iuran_periode_id', $id)->whereIn('status', ['belum_bayar', 'pending'])->count();
        $totalPendapatan = IuranBulanan::where('iuran_periode_id', $id)->where('status', 'lunas')->sum('nominal');

        return view('admin.iuran.show', compact(
            'periode', 'iuranList', 'totalUmkm', 'totalLunas', 'totalBelumBayar', 'totalPendapatan'
        ));
    }

    // ===================== KONFIRMASI MANUAL =====================

    /**
     * Admin konfirmasi manual iuran menjadi lunas.
     */
    public function konfirmasiLunas($periodeId, $iuranId)
    {
        $iuran = IuranBulanan::where('id', $iuranId)
                             ->where('iuran_periode_id', $periodeId)
                             ->firstOrFail();

        $iuran->markLunas();

        return back()->with('success', 'Iuran berhasil dikonfirmasi sebagai lunas.');
    }
}
