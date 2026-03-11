<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\JurnalUmum;
use App\Models\Coa;
use Illuminate\Http\Request;

class JurnalController extends Controller
{
    private function getUmkm()
    {
        return auth()->user()->umkm;
    }

    public function index(Request $request)
    {
        $umkm = $this->getUmkm();
        
        $jurnal = JurnalUmum::where('umkm_id', $umkm->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(50);

        return view('umkm.jurnal.index', compact('jurnal'));
    }

    public function create()
    {
        $umkm = $this->getUmkm();
        $coa = Coa::where('umkm_id', $umkm->id)->orderBy('kode_akun')->get();

        return view('umkm.jurnal.create', compact('coa'));
    }

    public function store(Request $request)
    {
        $umkm = $this->getUmkm();

        $request->validate([
            'tanggal'      => 'required|date',
            'keterangan'   => 'required|string|max:255',
            'kode_akun.*'  => 'required|string',
            'debit.*'      => 'required|numeric|min:0',
            'kredit.*'     => 'required|numeric|min:0',
        ]);

        $totalDebit = collect($request->debit)->sum();
        $totalKredit = collect($request->kredit)->sum();

        if ($totalDebit !== $totalKredit) {
            return back()->withInput()->withErrors(['Total Debit dan Total Kredit harus seimbang (Balance)!']);
        }

        foreach ($request->kode_akun as $index => $kodeAkun) {
            $akun = Coa::where('umkm_id', $umkm->id)->where('kode_akun', $kodeAkun)->first();
            
            if (!$akun) continue;

            $debit = $request->debit[$index] ?? 0;
            $kredit = $request->kredit[$index] ?? 0;

            if ($debit == 0 && $kredit == 0) continue;

            JurnalUmum::create([
                'umkm_id'    => $umkm->id,
                'tanggal'    => $request->tanggal,
                'kode_akun'  => $akun->kode_akun,
                'nama_akun'  => $akun->nama_akun,
                'keterangan' => 'Jurnal Manual: ' . $request->keterangan,
                'debit'      => $debit,
                'kredit'     => $kredit,
                'ref_tipe'   => 'manual',
            ]);
        }

        return redirect()->route('umkm.jurnal.index')->with('success', 'Jurnal manual berhasil ditambahkan.');
    }
}
