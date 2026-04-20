<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\StokMutasi;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    /**
     * API Autocomplete: GET /bahan-baku/search?q=tepung
     * Returns JSON [{id, kode_bahan, nama_bahan, satuan, harga}...]
     */
    public function search(Request $request)
    {
        $umkm = auth()->user()->umkm;
        if (!$umkm) return response()->json([]);

        $q = trim($request->get('q', ''));
        $query = BahanBaku::where('umkm_id', $umkm->id)
                          ->where('is_archived', false);

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_bahan', 'like', "%{$q}%")
                    ->orWhere('kode_bahan', 'like', "%{$q}%");
            });
        }

        $results = $query->orderBy('nama_bahan')->limit(15)->get(['id','kode_bahan','nama_bahan','satuan']);

        // Enrich with latest price from stok mutasi saldo awal
        return response()->json($results->map(function ($b) {
            $mutasi = StokMutasi::where('bahan_id', $b->id)
                                ->where('ref_tipe', 'saldo_awal')
                                ->first();
            return [
                'id'         => $b->id,
                'kode'       => $b->kode_bahan,
                'nama'       => $b->nama_bahan,
                'satuan'     => $b->satuan,
                'harga'      => $mutasi ? (float)$mutasi->harga_unit : 0,
            ];
        }));
    }
    public function index(Request $request)
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm) {
            return redirect()->route('umkm.profile')
                ->with('error', 'Lengkapi profil UMKM dulu sebelum mengakses bahan baku.');
        }

        $q = trim($request->get('q', ''));
        $query = BahanBaku::where('umkm_id', $umkm->id);

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_bahan', 'like', "%{$q}%")
                    ->orWhere('kode_bahan', 'like', "%{$q}%");
            });
        }

        $bahan = $query->orderBy('nama_bahan')
            ->select('bahan_baku.*', \Illuminate\Support\Facades\DB::raw('(COALESCE((SELECT SUM(qty) FROM stok_mutasi WHERE bahan_id = bahan_baku.id AND jenis = "MASUK"), 0) - COALESCE((SELECT SUM(qty) FROM stok_mutasi WHERE bahan_id = bahan_baku.id AND jenis = "KELUAR"), 0)) as current_stok'))
            ->get();

        return view('umkm.bahan.index', compact('bahan', 'q'));
    }

    public function create()
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm) {
            return redirect()->route('umkm.profile')
                ->with('error', 'Lengkapi profil UMKM dulu sebelum mengakses bahan baku.');
        }

        // kode baru untuk ditampilkan di form (readonly)
        $kodeBaru = BahanBaku::getKodeBahan();

        return view('umkm.bahan.create', compact('kodeBaru'));
    }

    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm) {
            return redirect()->route('umkm.profile')
                ->with('error', 'Lengkapi profil UMKM dulu sebelum mengakses bahan baku.');
        }

        $request->validate([
            'nama_bahan'      => 'required|string|max:100',
            'satuan'          => 'required|string|max:30',
            'stok_awal'       => 'nullable|numeric|min:0',
            'harga_unit_awal' => 'nullable|numeric|min:0',
            'keterangan'      => 'nullable|string',
        ]);

        $stokAwal       = (float) ($request->stok_awal ?? 0);
        $hargaUnitAwal  = (float) ($request->harga_unit_awal ?? 0);

        $bahan = BahanBaku::create([
            'kode_bahan' => $request->kode_bahan ?? BahanBaku::getKodeBahan(),
            'umkm_id'    => $umkm->id,
            'nama_bahan' => $request->nama_bahan,
            'satuan'     => $request->satuan,
            'stok_awal'  => $stokAwal,
            'keterangan' => $request->keterangan,
        ]);

        // Catat saldo awal sebagai mutasi MASUK agar muncul di kartu stok
        if ($stokAwal > 0) {
            StokMutasi::create([
                'umkm_id'    => $umkm->id,
                'bahan_id'   => $bahan->id,
                'tanggal'    => now()->toDateString(),
                'jenis'      => 'MASUK',
                'qty'        => $stokAwal,
                'harga_unit' => $hargaUnitAwal, // Rp 0 jika user tidak mengisi
                'ref_tipe'   => 'saldo_awal',
                'ref_id'     => $bahan->id,
            ]);
        }

        $nilaiAwal = $stokAwal * $hargaUnitAwal;
        
        // Jurnal Akuntansi Saldo Awal
        $accService = new \App\Services\AccountingService();
        $accService->jurnalSaldoAwal($umkm, $bahan->id, now()->toDateString(), $nilaiAwal);

        $msgNilai  = ($stokAwal > 0 && $hargaUnitAwal > 0)
            ? ' (Nilai: ' . rupiah($nilaiAwal) . ')'
            : '';

        return redirect()->route('umkm.bahan.index')
            ->with('success', 'Bahan baku berhasil ditambahkan. Saldo awal' . $msgNilai . ' tercatat di kartu stok & jurnal.');

    }

    public function edit(BahanBaku $bahan)
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm || $bahan->umkm_id !== $umkm->id) {
            abort(403);
        }

        // Cek apakah bahan sudah punya transaksi real (selain saldo_awal)
        $hasTransaksi = StokMutasi::where('bahan_id', $bahan->id)
            ->where('ref_tipe', '!=', 'saldo_awal')
            ->exists();

        // Ambil mutasi saldo awal yang ada (untuk pre-fill harga_unit di form)
        $mutasiSaldoAwal = StokMutasi::where('bahan_id', $bahan->id)
            ->where('ref_tipe', 'saldo_awal')
            ->first();

        return view('umkm.bahan.edit', compact('bahan', 'hasTransaksi', 'mutasiSaldoAwal'));
    }

    public function update(Request $request, BahanBaku $bahan)
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm || $bahan->umkm_id !== $umkm->id) {
            abort(403);
        }

        // Poin 5: Cek apakah bahan sudah punya transaksi real
        $hasTransaksi = StokMutasi::where('bahan_id', $bahan->id)
            ->where('ref_tipe', '!=', 'saldo_awal')
            ->exists();

        $request->validate([
            'nama_bahan'      => 'required|string|max:100',
            'satuan'          => 'required|string|max:30',
            // stok_awal dan harga_unit_awal hanya bisa diubah jika belum ada transaksi berjalan
            'stok_awal'       => $hasTransaksi ? 'prohibited' : 'nullable|numeric|min:0',
            'harga_unit_awal' => $hasTransaksi ? 'prohibited' : 'nullable|numeric|min:0',
            'keterangan'      => 'nullable|string',
        ]);

        $dataUpdate = [
            // kode_bahan TIDAK diubah
            'nama_bahan' => $request->nama_bahan,
            'satuan'     => $request->satuan,
            'keterangan' => $request->keterangan,
        ];

        // Hanya update stok_awal jika bahan belum punya transaksi berjalan
        if (!$hasTransaksi) {
            $stokAwalBaru  = (float) ($request->stok_awal ?? 0);
            $hargaUnitBaru = (float) ($request->harga_unit_awal ?? 0);
            $dataUpdate['stok_awal'] = $stokAwalBaru;

            // Update atau replace mutasi saldo_awal (qty + harga_unit)
            $mutasiLama = StokMutasi::where('bahan_id', $bahan->id)
                ->where('ref_tipe', 'saldo_awal')
                ->first();

            if ($stokAwalBaru > 0) {
                if ($mutasiLama) {
                    $mutasiLama->update([
                        'qty'        => $stokAwalBaru,
                        'harga_unit' => $hargaUnitBaru,
                    ]);
                } else {
                    StokMutasi::create([
                        'umkm_id'    => $umkm->id,
                        'bahan_id'   => $bahan->id,
                        'tanggal'    => $bahan->created_at->toDateString(),
                        'jenis'      => 'MASUK',
                        'qty'        => $stokAwalBaru,
                        'harga_unit' => $hargaUnitBaru,
                        'ref_tipe'   => 'saldo_awal',
                        'ref_id'     => $bahan->id,
                    ]);
                }
            } elseif ($mutasiLama) {
                // Stok awal di-set 0 → hapus mutasi saldo awal
                $mutasiLama->delete();
            }

            // Sync Jurnal Akuntansi Saldo Awal
            $nilaiAwalBaru = $stokAwalBaru * $hargaUnitBaru;
            $accService = new \App\Services\AccountingService();
            $accService->jurnalSaldoAwal($umkm, $bahan->id, $bahan->created_at->toDateString(), $nilaiAwalBaru);
        }

        $bahan->update($dataUpdate);

        return redirect()->route('umkm.bahan.index')
            ->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy(BahanBaku $bahan)
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm || $bahan->umkm_id !== $umkm->id) {
            abort(403);
        }

        // Cek apakah bahan sudah pernah dipakai di transaksi apa pun
        $hasTransaksi = StokMutasi::where('bahan_id', $bahan->id)
            ->where('ref_tipe', '!=', 'saldo_awal')
            ->exists();

        if ($hasTransaksi) {
            // Arsipkan saja — jangan hapus permanen agar histori laporan tetap valid
            $bahan->update(['is_archived' => true]);
            return redirect()->route('umkm.bahan.index')
                ->with('warning', "Bahan baku '{$bahan->nama_bahan}' sudah pernah digunakan dalam transaksi. Status diubah menjadi \"Tidak Aktif\" agar histori laporan tetap aman.");
        }

        $bahan->delete();

        return redirect()->route('umkm.bahan.index')
            ->with('success', "Bahan baku '{$bahan->nama_bahan}' berhasil dihapus.");
    }
}
