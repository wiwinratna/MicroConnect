<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\ProdukBahan;
use Illuminate\Http\Request;
use App\Helpers\UnitConverter;
use Illuminate\Support\Facades\Storage;
use App\Models\AnggaranBulanan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;

        $data = Produk::where('umkm_id', $umkm->id)
                      ->with('komposisi.bahan') // optional, kalau mau tampil di tabel
                      ->orderBy('nama_produk')
                      ->get();

        return view('umkm.produk.index', compact('data'));
    }

public function create()
{
    $umkm = auth()->user()->umkm;
    $kode = Produk::generateKode();

    // [NONAKTIF SEMENTARA] Overhead dari AnggaranBulanan dinonaktifkan
    // $periode = now()->format('Y-m');
    // $anggaran = AnggaranBulanan::where('umkm_id', $umkm->id)
    //     ->where('periode', $periode)
    //     ->first();
    // $overheadPerUnit = 0;
    // if ($anggaran && (float)$anggaran->target_unit > 0) {
    //     $overheadPerUnit = (float)$anggaran->total / (float)$anggaran->target_unit;
    // }
    $overheadPerUnit = 0; // sementara 0 selama fitur Anggaran dinonaktifkan

    // === bahan baku + harga beli terakhir (dari pembelian_detail) ===
    $bahanBaku = BahanBaku::query()
        ->where('umkm_id', $umkm->id)
        ->select('bahan_baku.*')
        ->selectSub(function ($q) use ($umkm) {
            $q->from('pembelian_detail')
              ->join('pembelian', 'pembelian.id', '=', 'pembelian_detail.pembelian_id')
              ->whereColumn('pembelian_detail.bahan_id', 'bahan_baku.id')
              ->where('pembelian.umkm_id', $umkm->id)
              ->orderByDesc('pembelian.tanggal')
              ->orderByDesc('pembelian_detail.id')
              ->limit(1)
              ->select('pembelian_detail.harga_beli');
        }, 'harga_last')
        ->orderBy('nama_bahan')
        ->get();

    $satuanOptions = \App\Helpers\UnitConverter::getUiOptions();

    return view('umkm.produk.create', compact('kode', 'bahanBaku', 'overheadPerUnit', 'satuanOptions'));
}

    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $request->validate([
            'nama_produk'  => 'required|string|max:255',
            'kategori'     => 'nullable|string|max:100',
            'harga_jual'   => 'nullable|numeric|min:0',
            'keterangan'   => 'nullable|string',
            'foto'         => 'nullable|image|max:2048',

            // komposisi
            'bahan_id.*'   => 'nullable|exists:bahan_baku,id',
            'qty.*'        => 'nullable|numeric|min:0',
            'satuan.*'     => 'nullable|string|max:50',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('produk', 'public');
        }

        $produk = Produk::create([
        'umkm_id'     => $umkm->id,
        'kode_produk' => Produk::generateKode(),
        'nama_produk' => $request->nama_produk,
        'kategori'    => $request->kategori,
        'satuan'      => $request->satuan_produk,   // kalau kamu punya kolom satuan di produk
        'harga_pokok' => $request->harga_pokok ?? 0, // <-- patokan HPP tersimpan
        'harga_jual'  => $request->harga_jual ?? 0,
        'aktif'       => true,
        'keterangan'  => $request->keterangan,
        'stok'        => 0,
        'foto_path'   => $fotoPath,
    ]);

        // 2) simpan komposisi (jika ada)
        // 2) simpan komposisi (jika ada)
        if ($request->bahan_id) {
            foreach ($request->bahan_id as $i => $bahanId) {
                $qtyInput   = $request->qty[$i]    ?? null;
                $unitInput  = $request->satuan[$i] ?? null; // satuan yang dipilih user di form

                if (!$bahanId || !$qtyInput) {
                    continue; // skip baris kosong
                }

                // ambil data bahan, termasuk satuan dasarnya (misal: 'ml', 'gram', 'pcs')
                $bahan = BahanBaku::find($bahanId);
                if (!$bahan) {
                    continue;
                }

                // kalau user gak pilih satuan → anggap satuannya sama dengan satuan bahan
                if (!$unitInput) {
                    $qtyFinal   = (float) $qtyInput;
                    $satuanFinal = $bahan->satuan;   // misal 'ml'
                } else {
                    // convert dari satuan input → satuan dasar bahan
                    $qtyFinal = UnitConverter::convert(
                        (float) $qtyInput,
                        $unitInput,      // dari form
                        $bahan->satuan   // satuan bahan di tabel bahan_baku
                    );
                    $satuanFinal = $bahan->satuan;
                }

                ProdukBahan::create([
                    'produk_id' => $produk->id,
                    'bahan_id'  => $bahan->id,
                    'qty'       => $qtyFinal,    // SUDAH dalam satuan bahan
                    'satuan'    => $satuanFinal, // simpan satuan bahan, biar konsisten
                ]);
            }
        }


        return redirect()
            ->route('umkm.produk.index')
            ->with('success', 'Produk & komposisinya berhasil ditambahkan.');
    }

    public function edit(Produk $produk)
    {
        $umkm = auth()->user()->umkm;
        if ($produk->umkm_id !== $umkm->id) {
            abort(403);
        }

        // bahan baku untuk dropdown
        $bahanBaku = BahanBaku::where('umkm_id', $umkm->id)
                        ->orderBy('nama_bahan')
                        ->get();

        // komposisi yang sudah ada
        $komposisi = $produk->komposisi()->with('bahan')->get();
        $satuanOptions = \App\Helpers\UnitConverter::getUiOptions();

        return view('umkm.produk.edit', compact('produk', 'bahanBaku', 'komposisi', 'satuanOptions'));
    }

    public function update(Request $request, Produk $produk)
    {
        $umkm = auth()->user()->umkm;
        if ($produk->umkm_id !== $umkm->id) {
            abort(403);
        }

        $request->validate([
            'nama_produk'  => 'required|string|max:255',
            'kategori'     => 'nullable|string|max:100',
            'harga_jual'   => 'nullable|numeric|min:0',
            'keterangan'   => 'nullable|string',
            'foto'         => 'nullable|image|max:2048',

            'bahan_id.*'   => 'nullable|exists:bahan_baku,id',
            'qty.*'        => 'nullable|numeric|min:0',
            'satuan.*'     => 'nullable|string|max:50',
        ]);

        $dataUpdate = [
            'nama_produk' => $request->nama_produk,
            'kategori'    => $request->kategori,
            'harga_jual'  => $request->harga_jual ?? 0,
            'keterangan'  => $request->keterangan,
        ];

        if ($request->hasFile('foto')) {
            if ($produk->foto_path && Storage::disk('public')->exists($produk->foto_path)) {
                Storage::disk('public')->delete($produk->foto_path);
            }

            $dataUpdate['foto_path'] = $request->file('foto')->store('produk', 'public');
        }

        // 1) update data produk
        $produk->update($dataUpdate);

        // 2) hapus komposisi lama
        $produk->komposisi()->delete();

        // 3) simpan komposisi baru
        if ($request->bahan_id) {
            foreach ($request->bahan_id as $i => $bahanId) {
                $qtyInput   = $request->qty[$i]    ?? null;
                $unitInput  = $request->satuan[$i] ?? null;

                if (!$bahanId || !$qtyInput) {
                    continue;
                }

                $bahan = BahanBaku::find($bahanId);
                if (!$bahan) {
                    continue;
                }

                if (!$unitInput) {
                    $qtyFinal    = (float) $qtyInput;
                    $satuanFinal = $bahan->satuan;
                } else {
                    $qtyFinal = UnitConverter::convert(
                        (float) $qtyInput,
                        $unitInput,
                        $bahan->satuan
                    );
                    $satuanFinal = $bahan->satuan;
                }

                ProdukBahan::create([
                    'produk_id' => $produk->id,
                    'bahan_id'  => $bahan->id,
                    'qty'       => $qtyFinal,
                    'satuan'    => $satuanFinal,
                ]);
            }
        }

        return redirect()
            ->route('umkm.produk.index')
            ->with('success', 'Produk & komposisinya berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        $umkm = auth()->user()->umkm;
        if ($produk->umkm_id !== $umkm->id) {
            abort(403);
        }

        if ($produk->foto_path && Storage::disk('public')->exists($produk->foto_path)) {
            Storage::disk('public')->delete($produk->foto_path);
        }

        // komposisi akan ikut terhapus kalau foreign key onDelete('cascade')
        $produk->delete();

        return redirect()
            ->route('umkm.produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

public function hitungHpp(Produk $produk, Request $request)
{
    $umkm = auth()->user()->umkm;
    if ($produk->umkm_id !== $umkm->id) abort(403);

    // === 1. Detail biaya bahan dari resep ===
    $detailBahan = [];
    $biayaBahan  = 0.0;

    $komposisi = $produk->komposisi()->with('bahan')->get();

    foreach ($komposisi as $row) {
        $bahan = $row->bahan;
        if (!$bahan) continue;

        $satuanResep   = $row->satuan ?? $bahan->satuan;
        $satuanDasar   = $bahan->satuan;
        $qtyResep      = (float) $row->qty;

        // Konversi qty resep ke satuan dasar bahan
        $qtyBase = \App\Helpers\UnitConverter::isCompatible($satuanResep, $satuanDasar)
            ? \App\Helpers\UnitConverter::convert($qtyResep, $satuanResep, $satuanDasar)
            : $qtyResep;

        // Harga beli terakhir (per satuan dasar)
        $hargaUnit = (float) $bahan->hargaBeliTerakhir();
        $biaya     = $qtyBase * $hargaUnit;
        $biayaBahan += $biaya;

        $detailBahan[] = [
            'nama_bahan'    => $bahan->nama_bahan,
            'qty_resep'     => $qtyResep,
            'satuan_resep'  => $satuanResep,
            'qty_base'      => round($qtyBase, 3),
            'satuan_dasar'  => $satuanDasar,
            'harga_unit'    => $hargaUnit,
            'biaya'         => round($biaya, 2),
        ];
    }

    // === 2. [NONAKTIF SEMENTARA] Overhead dari AnggaranBulanan dinonaktifkan ===
    // $periode = now()->format('Y-m');
    // $anggaran = AnggaranBulanan::where('umkm_id', $umkm->id)
    //     ->where('periode', $periode)->first();
    // $overheadPerUnit = 0.0;
    // if ($anggaran) {
    //     $target = (float) ($anggaran->target_unit ?? 0);
    //     $total  = (float) ($anggaran->total ?? 0);
    //     $overheadPerUnit = $target > 0 ? ($total / $target) : 0.0;
    // }
    $overheadPerUnit = 0.0; // sementara 0 selama fitur Anggaran dinonaktifkan

    // === 3. Estimasi HPP total (berdasarkan histori biaya bahan, tanpa overhead sementara) ===
    $hpp = round($biayaBahan + $overheadPerUnit, 2);

    // === 4. Margin & Saran Harga Jual ===
    $marginTarget = (float) ($request->input('margin', 0));
    $saranHarga   = $hpp > 0 ? round($hpp * (1 + $marginTarget / 100)) : 0;

    $hargaJualNow  = (float) ($produk->harga_jual ?? 0);
    $marginAktual  = ($hpp > 0 && $hargaJualNow > 0)
        ? round((($hargaJualNow - $hpp) / $hpp) * 100, 1)
        : null;

    // === 5. Simpan HPP estimasi ke produk ===
    $produk->update(['harga_pokok' => $hpp]);

    // === 6. Response ===
    if ($request->expectsJson()) {
        return response()->json([
            'hpp'             => $hpp,
            'biaya_bahan'     => round($biayaBahan, 2),
            'overhead'        => round($overheadPerUnit, 2),
            'detail_bahan'    => $detailBahan,
            'margin_target'   => $marginTarget,
            'saran_harga'     => $saranHarga,
            'margin_aktual'   => $marginAktual,
            'harga_jual_now'  => $hargaJualNow,
            'periode_overhead'=> 'n/a (anggaran nonaktif)',

        ]);
    }

    return redirect()
        ->route('umkm.produk.edit', $produk->id)
        ->with('success', 'HPP Estimasi: Rp ' . number_format($hpp, 0, ',', '.') .
            ($overheadPerUnit > 0 ? ' (termasuk overhead Rp ' . number_format($overheadPerUnit, 0, ',', '.') . '/unit)' : ''));
}

}
