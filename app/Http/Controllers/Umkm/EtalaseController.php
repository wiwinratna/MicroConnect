<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Services\PenjualanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EtalaseController extends Controller
{
    /**
     * Tampilkan halaman utama Mode Etalase.
     */
    public function index()
    {
        $umkm = Auth::user()->umkm;

        if (!$umkm) {
            return redirect()->route('umkm.profile')
                ->with('error', 'Lengkapi profil UMKM dulu sebelum mengakses Mode Etalase.');
        }

        // Ambil produk yang aktif dan punya resep (karena jual produk tanpa resep akan error stok)
        // Kita juga bisa cuma nampilin yang 'aktif' = 1 jika ada field tsb.
        $produk = Produk::where('umkm_id', $umkm->id)
            ->where('aktif', 1) 
            ->orderBy('nama_produk')
            ->get();

        return view('umkm.etalase.index', compact('umkm', 'produk'));
    }

    /**
     * Proses checkout dari Mode Etalase.
     * Menggunakan PenjualanService yang sama dengan transaksi back-office.
     */
    public function checkout(Request $request, PenjualanService $penjualanService)
    {
        $umkm = Auth::user()->umkm;

        $request->validate([
            'items'       => 'required|json', // Array of {id: int, qty: float}
            'pembeli'     => 'nullable|string|max:100',
            'uang_dibayar'=> 'nullable|numeric|min:0',
        ]);

        try {
            $itemsRaw = json_decode($request->items, true);
            
            if (!is_array($itemsRaw) || count($itemsRaw) === 0) {
                throw new \Exception("Keranjang belanja kosong.");
            }

            // Mapping format item dari Etalase ke format Service
            $serviceItems = [];
            foreach ($itemsRaw as $item) {
                if (isset($item['id']) && isset($item['qty']) && $item['qty'] > 0) {
                    $serviceItems[] = [
                        'produk_id' => (int) $item['id'],
                        'qty'       => (float) $item['qty'],
                    ];
                }
            }

            if (empty($serviceItems)) {
                throw new \Exception("Tidak ada produk valid di keranjang.");
            }

            // Mode Etalase selalu pakai metode 'tunai'. Tidak ada piutang via etalase.
            $result = $penjualanService->prosesTransaksi(
                $umkm,
                $serviceItems,
                now()->toDateString(), // Tanggal hari ini
                'tunai',               // Selalu tunai
                null,                  // No pelanggan ID
                null,                  // No jatuh tempo
                $request->pembeli,
                'Penjualan via Mode Etalase'
            );

            $penjualan = $result['penjualan'];

            // Simpan uang dibayar ke session (flash) jika ada untuk nota
            $uangDibayar = (float) ($request->uang_dibayar ?? 0);
            if ($uangDibayar > 0) {
                session()->flash('uang_dibayar', $uangDibayar);
            }

            return redirect()->route('umkm.etalase.nota', $penjualan->id)
                ->with('success', 'Transaksi berhasil.');

        } catch (\Exception $e) {
            Log::error('Etalase Checkout Error: ' . $e->getMessage(), [
                'umkm_id' => $umkm->id,
                'trace'   => $e->getTraceAsString(),
            ]);

            return redirect()->route('umkm.etalase.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan struk/nota setelah pembayaran.
     */
    public function nota($id)
    {
        $umkm = Auth::user()->umkm;

        $penjualan = Penjualan::where('umkm_id', $umkm->id)
            ->with(['details.produk'])
            ->findOrFail($id);

        $uangDibayar = session('uang_dibayar', $penjualan->total); // fallback ke total
        $kembalian = max(0, $uangDibayar - $penjualan->total);

        return view('umkm.etalase.nota', compact('umkm', 'penjualan', 'uangDibayar', 'kembalian'));
    }
}
