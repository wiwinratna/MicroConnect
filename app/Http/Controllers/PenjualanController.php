<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\StokMutasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\UnitConverter;

class PenjualanController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;

        $data = Penjualan::where('umkm_id', $umkm->id)
            ->with('details.produk')
            ->orderByDesc('tanggal')
            ->get();

        return view('umkm.penjualan.index', compact('data'));
    }

    public function create()
    {
        $umkm = auth()->user()->umkm;

        $produk = Produk::where('umkm_id', $umkm->id)
            ->with('komposisi.bahan')
            ->orderBy('nama_produk')
            ->get();

        $pelanggan = \App\Models\Pelanggan::where('umkm_id', $umkm->id)
            ->orderBy('nama_pelanggan')
            ->get();

        return view('umkm.penjualan.create', compact('produk', 'pelanggan'));
    }

    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $request->validate([
            'tanggal'           => 'required|date',
            'metode_pembayaran' => 'required|in:tunai,piutang',
            'pembeli'           => 'nullable|string|max:100',
            'catatan'           => 'nullable|string',
            'produk_id.*'       => 'nullable|integer',
            'qty.*'             => 'nullable|numeric|min:0.001',
            // Khusus piutang
            'pelanggan_id'      => 'required_if:metode_pembayaran,piutang|nullable|exists:pelanggan,id',
            'jatuh_tempo'       => 'required_if:metode_pembayaran,piutang|nullable|date',
        ]);

        try {
            // Struktur data mapping untuk service
            $items = [];
            foreach ($request->produk_id ?? [] as $i => $produkId) {
                if (!$produkId) continue;
                $items[] = [
                    'produk_id' => $produkId,
                    'qty'       => $request->qty[$i] ?? 0,
                ];
            }

            $penjualanService = new \App\Services\PenjualanService();
            $result = $penjualanService->prosesTransaksi(
                $umkm,
                $items,
                $request->tanggal,
                $request->metode_pembayaran,
                $request->pelanggan_id,
                $request->jatuh_tempo,
                $request->pembeli,
                $request->catatan
            );

            $penjualan = $result['penjualan'];
            $piutangCreated = $result['piutang'];

            $msg = 'Penjualan berhasil disimpan. Stok bahan baku otomatis berkurang sesuai resep produk dan jurnal tercatat.';
            if ($request->metode_pembayaran === 'piutang') {
                $msg .= ' Piutang baru telah dibuat.';
            }

            return redirect()
                ->route('umkm.penjualan.index')
                ->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('Penjualan Error: ' . $e->getMessage(), [
                'umkm_id' => $umkm->id,
                'user_id' => auth()->id(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }
    }
}
