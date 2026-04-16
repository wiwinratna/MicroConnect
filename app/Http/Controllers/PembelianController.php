<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\BahanBaku;
use Illuminate\Http\Request;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $query = Pembelian::where('umkm_id', $umkm->id)->with('details.bahan');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_nota', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        $data = $query->orderByDesc('tanggal')->paginate(15)->withQueryString();

        return view('umkm.pembelian.index', compact('data'));
    }

    public function create()
    {
        $umkm = auth()->user()->umkm;

        $bahan = BahanBaku::where('umkm_id', $umkm->id)->get();
        $kode = Pembelian::generateKode();

        return view('umkm.pembelian.create', compact('bahan', 'kode'));
    }

    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        // VALiDASI
        $request->validate([
            'tanggal'   => 'required|date',
            'nomor_nota'=> 'nullable|string|max:100',
            'supplier'  => 'nullable|string|max:100',
            'catatan'   => 'nullable|string',
            'bukti_pembelian' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',

            'bahan_id.*'    => 'nullable|exists:bahan_baku,id',
            'qty.*'         => 'nullable|numeric|min:0.001',
            'harga_beli.*'  => 'nullable|numeric|min:0',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti_pembelian')) {
            $buktiPath = $request->file('bukti_pembelian')->store('bukti_pembelian', 'public');
        }

        // SIMPAN HEADER
        $pembelian = Pembelian::create([
            'umkm_id'        => $umkm->id,
            'kode_pembelian' => Pembelian::generateKode(),
            'nomor_nota'     => $request->nomor_nota,
            'tanggal'        => $request->tanggal,
            'supplier'       => $request->supplier,
            'catatan'        => $request->catatan,
            'bukti_pembelian'=> $buktiPath,
            'total'          => 0,
        ]);

        $total = 0;

        // SIMPAN DETAIL
        foreach ($request->bahan_id ?? [] as $i => $bahanId) {

            if (!$bahanId) continue;

            $qty = $request->qty[$i] ?? 0;
            $harga = $request->harga_beli[$i] ?? 0;

            if ($qty <= 0) continue;

            $subtotal = $qty * $harga;
            $total += $subtotal;

            PembelianDetail::create([
                'pembelian_id' => $pembelian->id,
                'bahan_id'     => $bahanId,
                'qty'          => $qty,
                'harga_beli'   => $harga,
                'subtotal'     => $subtotal,
            ]);

            // MENCATAT MUTASI STOK BAHAN BAKU 
            \App\Models\StokMutasi::create([
                'umkm_id'      => $umkm->id,
                'bahan_id'     => $bahanId,
                'tanggal'      => $pembelian->tanggal,
                'jenis'        => 'MASUK',
                'qty'          => $qty,
                'harga_unit'   => $harga,
                'ref_tipe'     => 'pembelian',
                'ref_id'       => $pembelian->id,
                'ref_detail_id'=> $pembelian->id // Sementara pakai ID header, kalau butuh detail ID nanti disesuaikan
            ]);

            // STOK AWAL BahanBaku BISA DIKOSONGKAN atau DIUPDATE untuk caching.
            // Sesuai sistem, kita akan gunakan $bahan->stok_awal sebagai cache visual saja.
            $bahan = BahanBaku::find($bahanId);
            $stokBaru = ($bahan->stok_awal ?? 0) + $qty;
            
            $bahan->update([
                'stok_awal' => $stokBaru,
            ]);
        }

        $pembelian->update(['total' => $total]);

        // ======================
        // (E) DELEGATE POSTING JURNAL KE ACCOUNTING SERVICE
        // ======================
        $accService = new \App\Services\AccountingService();
        $accService->jurnalPembelian($umkm, $pembelian, $total);

        return redirect()
            ->route('umkm.pembelian.index')
            ->with('success', 'Pembelian berhasil disimpan, mutasi stok tercatat, dan jurnal otomatis terbentuk.');
    }

    public function edit($id)
    {
        $umkm = auth()->user()->umkm;
        $pembelian = Pembelian::where('umkm_id', $umkm->id)->findOrFail($id);

        return view('umkm.pembelian.edit', compact('pembelian'));
    }

    public function destroy($id)
    {
        $umkm = auth()->user()->umkm;
        $pembelian = Pembelian::where('umkm_id', $umkm->id)->findOrFail($id);

        if ($pembelian->isUsed()) {
            return redirect()
                ->route('umkm.pembelian.index')
                ->with('error', 'Pembelian gagal dihapus. Stok bahan dari transaksi ini sudah dicatat penggunaannya (telah direstock keluar).');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($pembelian) {
            // Revert stok awal visual cache
            foreach ($pembelian->details as $detail) {
                $bahan = \App\Models\BahanBaku::find($detail->bahan_id);
                if ($bahan) {
                    $bahan->update([
                        'stok_awal' => max(0, ($bahan->stok_awal ?? 0) - $detail->qty)
                    ]);
                }
            }

            // Hapus Stok Mutasi (MASUK)
            \App\Models\StokMutasi::where('ref_tipe', 'pembelian')
                ->where('ref_id', $pembelian->id)
                ->delete();

            // Hapus Jurnal Umum otomatis ter-create by system
            \App\Models\JurnalUmum::where('umkm_id', $pembelian->umkm_id)
                ->where('ref_tipe', 'pembelian')
                ->where('ref_id', $pembelian->id)
                ->delete();

            // Hapus Detail (Jika tidak cascade)
            $pembelian->details()->delete();

            // Hapus file bukti
            if ($pembelian->bukti_pembelian) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pembelian->bukti_pembelian);
            }

            // Hapus Pembelian Utama
            $pembelian->delete();
        });

        return redirect()
            ->route('umkm.pembelian.index')
            ->with('success', 'Riwayat Pembelian berhasil dihapus, mutasi stok ditarik kembali.');
    }

    public function update(Request $request, $id)
    {
        $umkm = auth()->user()->umkm;
        $pembelian = Pembelian::where('umkm_id', $umkm->id)->findOrFail($id);

        if ($pembelian->isUsed()) {
            return redirect()
                ->route('umkm.pembelian.index')
                ->with('error', 'Pembelian gagal diedit. Stok bahan dari transaksi ini sudah dicatat penggunaannya (telah direstock keluar).');
        }

        $request->validate([
            'nomor_nota'=> 'nullable|string|max:100',
            'supplier'  => 'nullable|string|max:100',
            'catatan'   => 'nullable|string',
            'bukti_pembelian' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('bukti_pembelian')) {
            $buktiPath = $request->file('bukti_pembelian')->store('bukti_pembelian', 'public');
            $pembelian->bukti_pembelian = $buktiPath;
        }

        $pembelian->nomor_nota = $request->nomor_nota;
        $pembelian->supplier = $request->supplier;
        $pembelian->catatan = $request->catatan;
        $pembelian->save();

        return redirect()
            ->route('umkm.pembelian.index')
            ->with('success', 'Informasi Pembelian dan Bukti berhasil diperbarui (Isi barang tidak diubah demi menjaga integritas Kartu Stok).');
    }
}
