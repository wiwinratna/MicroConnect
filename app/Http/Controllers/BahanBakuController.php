<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\StokMutasi;
use Illuminate\Http\Request;

class BahanBakuController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;

        if (!$umkm) {
            return redirect()->route('umkm.profile')
                ->with('error', 'Lengkapi profil UMKM dulu sebelum mengakses bahan baku.');
        }

        $bahan = BahanBaku::where('umkm_id', $umkm->id)
            ->orderBy('nama_bahan')
            ->get();

        return view('umkm.bahan.index', compact('bahan'));
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

        $bahan->delete();

        return redirect()->route('umkm.bahan.index')
            ->with('success', 'Bahan baku berhasil dihapus.');
    }
}
