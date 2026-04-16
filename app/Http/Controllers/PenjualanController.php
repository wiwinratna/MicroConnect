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
    public function index(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $query = Penjualan::where('umkm_id', $umkm->id)->with('details.produk');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_penjualan', 'like', "%{$search}%")
                  ->orWhere('pembeli', 'like', "%{$search}%");
            });
        }

        $data = $query->orderByDesc('tanggal')->paginate(15)->withQueryString();

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

    public function edit($id)
    {
        $umkm = auth()->user()->umkm;
        $penjualan = Penjualan::where('umkm_id', $umkm->id)->with('details', 'piutang')->findOrFail($id);

        if ($penjualan->isLocked()) {
            return redirect()->route('umkm.penjualan.index')->with('error', 'Penjualan ini tidak bisa diedit karena piutang sudah ada pembayaran.');
        }

        $produk = Produk::where('umkm_id', $umkm->id)
            ->with('komposisi.bahan')
            ->orderBy('nama_produk')
            ->get();

        $pelanggan = \App\Models\Pelanggan::where('umkm_id', $umkm->id)
            ->orderBy('nama_pelanggan')
            ->get();

        return view('umkm.penjualan.edit', compact('penjualan', 'produk', 'pelanggan'));
    }

    public function update(Request $request, $id)
    {
        $umkm = auth()->user()->umkm;
        $penjualan = Penjualan::where('umkm_id', $umkm->id)->findOrFail($id);

        if ($penjualan->isLocked()) {
            return redirect()->route('umkm.penjualan.index')->with('error', 'Gagal update: Penjualan ini dikunci karena piutang sudah mulai dibayar.');
        }

        $request->validate([
            'tanggal'           => 'required|date',
            'metode_pembayaran' => 'required|in:tunai,piutang',
            'pembeli'           => 'nullable|string|max:100',
            'catatan'           => 'nullable|string',
            'produk_id.*'       => 'nullable|integer',
            'qty.*'             => 'nullable|numeric|min:0.001',
            'pelanggan_id'      => 'required_if:metode_pembayaran,piutang|nullable|exists:pelanggan,id',
            'jatuh_tempo'       => 'required_if:metode_pembayaran,piutang|nullable|date',
        ]);

        try {
            DB::transaction(function () use ($penjualan, $umkm, $request) {
                $penjualanService = new \App\Services\PenjualanService();

                // 1. Revert impacts (stock, journals, piutang)
                $penjualanService->revertImpacts($penjualan);

                // 2. Prepare Items
                $items = [];
                foreach ($request->produk_id ?? [] as $i => $produkId) {
                    if (!$produkId) continue;
                    $items[] = [
                        'produk_id' => $produkId,
                        'qty'       => $request->qty[$i] ?? 0,
                    ];
                }

                // 3. Update Header
                $penjualan->update([
                    'tanggal' => $request->tanggal,
                    'pembeli' => $request->pembeli,
                    'catatan' => $request->catatan,
                ]);

                // 4. Apply New Impacts
                $penjualanService->applyImpacts(
                    $penjualan,
                    $umkm,
                    $items,
                    $request->tanggal,
                    $request->metode_pembayaran,
                    $request->pelanggan_id,
                    $request->jatuh_tempo,
                    $request->catatan
                );
            });

            return redirect()->route('umkm.penjualan.index')->with('success', 'Penjualan berhasil diperbarui. Stok dan jurnal telah disesuaikan.');

        } catch (\Exception $e) {
            Log::error('Penjualan Update Error: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $umkm = auth()->user()->umkm;
        $penjualan = Penjualan::where('umkm_id', $umkm->id)->findOrFail($id);

        if ($penjualan->isLocked()) {
            return redirect()->back()->with('error', 'Gagal menghapus: Penjualan ini dikunci karena piutang sudah mulai dibayar.');
        }

        try {
            DB::transaction(function () use ($penjualan, $umkm) {
                // 1. Revert stok awal visual cache di BahanBaku
                // Ambil semua mutasi keluar terkait penjualan ini
                $mutasiKeluar = StokMutasi::where('umkm_id', $umkm->id)
                    ->where('ref_tipe', 'penjualan')
                    ->where('ref_id', $penjualan->id)
                    ->where('jenis', 'KELUAR')
                    ->get();

                foreach ($mutasiKeluar as $mutasi) {
                    $bahan = BahanBaku::find($mutasi->bahan_id);
                    if ($bahan) {
                        $bahan->update([
                            'stok_awal' => ($bahan->stok_awal ?? 0) + $mutasi->qty
                        ]);
                    }
                }

                // 2. Hapus Jurnal Umum
                \App\Models\JurnalUmum::where('umkm_id', $umkm->id)
                    ->where('ref_tipe', 'penjualan')
                    ->where('ref_id', $penjualan->id)
                    ->delete();

                // 3. Hapus Stok Mutasi (Keluar)
                StokMutasi::where('umkm_id', $umkm->id)
                    ->where('ref_tipe', 'penjualan')
                    ->where('ref_id', $penjualan->id)
                    ->delete();

                // 4. Hapus Piutang (Jika Ada)
                \App\Models\Piutang::where('penjualan_id', $penjualan->id)->delete();

                // 5. Hapus Detail Penjualan
                $penjualan->details()->delete();

                // 6. Hapus Header Penjualan
                $penjualan->delete();
            });

            return redirect()->route('umkm.penjualan.index')->with('success', 'Riwayat penjualan berhasil dihapus. Stok bahan otomatis dikembalikan dan jurnal dihapus.');

        } catch (\Exception $e) {
            Log::error('Penjualan Delete Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus penjualan: ' . $e->getMessage());
        }
    }
}
