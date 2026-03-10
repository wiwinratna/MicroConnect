<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CoaController extends Controller
{
    private array $startKode = [
        'Aset'       => 111,
        'Kewajiban'  => 211,
        'Modal'      => 311,
        'Pendapatan' => 400,
        'Beban'      => 501,
    ];

    private function posisiDefault(string $header): string
    {
        // Normal balance
        return in_array($header, ['Aset', 'Beban'], true) ? 'Debit' : 'Kredit';
    }

    private function nextKodeAkun(int $umkmId, string $header): string
    {
        $start = $this->startKode[$header] ?? 900;

        // Ambil kode terbesar untuk header tsb (per UMKM)
        $last = Coa::where('umkm_id', $umkmId)
            ->where('header_akun', $header)
            ->selectRaw('MAX(CAST(kode_akun AS UNSIGNED)) as max_kode')
            ->value('max_kode');

        $next = $last ? ((int)$last + 1) : $start;
        return (string) $next;
    }

    // ====== INDEX ======
    public function index()
    {
        $umkm = auth()->user()->umkm;

        $data = Coa::where('umkm_id', $umkm->id)
            ->orderByRaw('CAST(kode_akun AS UNSIGNED) ASC')
            ->get();

        return view('umkm.coa.index', compact('data'));
    }

    // ====== CREATE ======
    public function create()
    {
        // Blade create kamu akan memanggil preview() untuk isi otomatis
        return view('umkm.coa.create');
    }

    // ====== PREVIEW (AJAX) ======
    public function preview(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $request->validate([
            'header' => 'required|string|max:50'
        ]);

        $header = $request->header;

        // kalau kamu mau batasi header valid:
        $allowed = ['Aset','Kewajiban','Modal','Pendapatan','Beban'];
        if (!in_array($header, $allowed, true)) {
            return response()->json([
                'error' => 'Header akun tidak valid.'
            ], 422);
        }

        $kode  = $this->nextKodeAkun($umkm->id, $header);
        $posisi = $this->posisiDefault($header);

        return response()->json([
            'kode_akun' => $kode,
            'posisi'    => $posisi,
            'note'      => 'Kode dibuat otomatis berdasarkan nomor terakhir di header ini.',
        ]);
    }

    // ====== STORE (AUTO CODE + AUTO POSISI) ======
    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $request->validate([
            'header_akun' => 'required|string|max:50',
            'nama_akun'   => 'required|string|max:100',
        ]);

        $header = $request->header_akun;
        $kode   = $this->nextKodeAkun($umkm->id, $header);
        $posisi = $this->posisiDefault($header);

        // validasi unik per UMKM (kombinasi umkm_id + kode_akun)
        // (walaupun sudah ada unique index gabungan, ini biar errornya bagus)
        $exists = Coa::where('umkm_id', $umkm->id)
            ->where('kode_akun', $kode)
            ->exists();

        if ($exists) {
            // fallback aman kalau ada race condition / input bersamaan
            $kode = $this->nextKodeAkun($umkm->id, $header);
        }

        Coa::create([
            'umkm_id'      => $umkm->id,
            'header_akun'  => $header,
            'kode_akun'    => $kode,
            'nama_akun'    => $request->nama_akun,
            'posisi_dr_cr' => $posisi,
        ]);

        return redirect()->route('umkm.coa.index')->with('success', 'COA berhasil ditambahkan.');
    }

    // ====== EDIT ======
    public function edit(Coa $coa)
    {
        $umkm = auth()->user()->umkm;
        abort_if($coa->umkm_id !== $umkm->id, 403);

        return view('umkm.coa.edit', compact('coa'));
    }

    // ====== UPDATE ======
    // Aku bikin update tetap boleh edit nama & header.
    // Kode akun & posisi bisa kamu kunci kalau mau (lebih aman).
    public function update(Request $request, Coa $coa)
    {
        $umkm = auth()->user()->umkm;
        abort_if($coa->umkm_id !== $umkm->id, 403);

        $request->validate([
            'header_akun' => 'required|string|max:50',
            'nama_akun'   => 'required|string|max:100',
        ]);

        $coa->update([
            'header_akun'  => $request->header_akun,
            'nama_akun'    => $request->nama_akun,
            'posisi_dr_cr' => $this->posisiDefault($request->header_akun),
            // kode_akun sengaja tidak diubah biar stabil
        ]);

        return redirect()->route('umkm.coa.index')->with('success', 'COA berhasil diperbarui.');
    }

    // ====== DESTROY ======
    public function destroy(Coa $coa)
    {
        $umkm = auth()->user()->umkm;
        abort_if($coa->umkm_id !== $umkm->id, 403);

        $coa->delete();

        return back()->with('success', 'COA berhasil dihapus.');
    }
}
