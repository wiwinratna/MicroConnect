<?php

namespace App\Http\Controllers;

use App\Models\Produksi;
use App\Models\ProduksiDetail;
use App\Models\Produk;
use App\Models\BahanBaku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduksiController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;

        $data = Produksi::where('umkm_id', $umkm->id)
            ->with('details.produk')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return view('umkm.produksi.index', compact('data'));
    }

    public function create()
    {
        $umkm = auth()->user()->umkm;

        $kode = Produksi::generateKode();
        $produk = Produk::where('umkm_id', $umkm->id)
            ->orderBy('nama_produk')
            ->get();

        return view('umkm.produksi.create', compact('kode', 'produk'));
    }

    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $request->validate([
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string',

            'produk_id.*' => 'nullable|exists:produk,id',
            'qty_hasil.*' => 'nullable|numeric|min:0.001',
        ]);

        // minimal ada 1 baris valid
        $hasValid = false;
        if ($request->produk_id) {
            foreach ($request->produk_id as $i => $pid) {
                $qty = $request->qty_hasil[$i] ?? 0;
                if ($pid && $qty > 0) { $hasValid = true; break; }
            }
        }
        if (!$hasValid) {
            return back()->withErrors(['detail' => 'Minimal isi 1 produk & qty produksi.'])->withInput();
        }

        DB::transaction(function () use ($request, $umkm) {

            $produksi = Produksi::create([
                'umkm_id' => $umkm->id,
                'kode_produksi' => Produksi::generateKode(),
                'tanggal' => $request->tanggal,
                'catatan' => $request->catatan,
            ]);

            foreach ($request->produk_id as $i => $produkId) {
                $qtyHasil = (float) ($request->qty_hasil[$i] ?? 0);

                if (!$produkId || $qtyHasil <= 0) continue;

                $produk = Produk::where('umkm_id', $umkm->id)
                    ->with(['komposisi.bahan'])
                    ->findOrFail($produkId);

                // 1) hitung kebutuhan bahan = qty_hasil * qty_resep (qty_resep sudah dalam satuan bahan)
                $kebutuhan = []; // bahan_id => qty_total
                foreach ($produk->komposisi as $row) {
                    if (!$row->bahan) continue;
                    $bahanId = $row->bahan_id;
                    $kebutuhan[$bahanId] = ($kebutuhan[$bahanId] ?? 0) + ((float)$row->qty * $qtyHasil);
                }

                // 2) cek stok bahan cukup
                foreach ($kebutuhan as $bahanId => $qtyButuh) {
                    $bahan = BahanBaku::where('umkm_id', $umkm->id)->findOrFail($bahanId);

                    $stokSekarang = (float) (
                        $bahan->stok ?? 
                        $bahan->stok_awal ?? 
                        0
                    );

                    if ($stokSekarang < $qtyButuh) {
                        throw new \Exception("Stok bahan '{$bahan->nama_bahan}' tidak cukup. Dibutuhkan {$qtyButuh} {$bahan->satuan}, stok tersedia {$stokSekarang}.");
                    }
                }

                // 3) kurangi stok bahan
                foreach ($kebutuhan as $bahanId => $qtyButuh) {
                    $bahan = BahanBaku::where('umkm_id', $umkm->id)->findOrFail($bahanId);
                    $bahan->update([
                        'stok' => (float)($bahan->stok ?? 0) - $qtyButuh
                    ]);
                }

                // 4) tambah stok produk jadi
                $produk->update([
                    'stok' => (float)($produk->stok ?? 0) + $qtyHasil
                ]);

                // 5) simpan produksi detail + snapshot HPP produk (kalau sudah ada)
                $hppUnit = (float) ($produk->harga_pokok ?? 0);
                ProduksiDetail::create([
                    'produksi_id' => $produksi->id,
                    'produk_id' => $produk->id,
                    'qty_hasil' => $qtyHasil,
                    'hpp_per_unit' => $hppUnit,
                    'hpp_total' => $hppUnit * $qtyHasil,
                ]);
            }
        });

        return redirect()->route('umkm.produksi.index')
            ->with('success', 'Produksi berhasil disimpan. Stok bahan berkurang & stok produk bertambah.');
    }
}
