<?php

namespace App\Http\Controllers;

use App\Models\AnggaranBulanan;
use App\Models\AnggaranBulananItem;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UmkmAnggaranController extends Controller
{
    private function normalizePeriode($value): string
    {
        // amanin kalau yang masuk: "2025-12" / "2025-12-01" / "2025-12-01 00:00:00"
        $raw = (string) $value;
        $raw = trim($raw);

        // ambil 7 char pertama biar pasti "YYYY-MM"
        $raw7 = substr($raw, 0, 7);

        // validasi format kasar
        if (!preg_match('/^\d{4}-\d{2}$/', $raw7)) {
            // fallback: parse pakai Carbon lalu format Y-m
            return Carbon::parse($raw)->format('Y-m');
        }

        return $raw7;
    }

    public function index(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $periode = $this->normalizePeriode(
            $request->get('periode', now()->format('Y-m'))
        );

        $anggaran = AnggaranBulanan::with('items')
            ->where('umkm_id', $umkm->id)
            ->where('periode', $periode)
            ->first();

        return view('umkm.anggaran.index', compact('anggaran', 'periode'));
    }

    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        // normalize dulu sebelum validate
        $periode = $this->normalizePeriode($request->periode);

        $request->merge(['periode' => $periode]);

        $request->validate([
            'periode' => 'required|date_format:Y-m', // lebih aman dari size:7
            'target_unit' => 'required|numeric|min:0',
            'nama_biaya.*' => 'nullable|string|max:100',
            'nominal.*' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
        ]);

        $anggaran = AnggaranBulanan::updateOrCreate(
            ['umkm_id' => $umkm->id, 'periode' => $periode],
            ['target_unit' => $request->target_unit, 'catatan' => $request->catatan, 'total' => 0]
        );

        // reset item lama
        $anggaran->items()->delete();

        $total = 0;
        if ($request->nama_biaya) {
            foreach ($request->nama_biaya as $i => $nama) {
                $nom = $request->nominal[$i] ?? 0;

                if (!$nama || $nom <= 0) continue;

                AnggaranBulananItem::create([
                    'anggaran_id' => $anggaran->id,
                    'nama_biaya' => $nama,
                    'nominal' => $nom,
                ]);

                $total += (float) $nom;
            }
        }

        $anggaran->update(['total' => $total]);

        return redirect()
            ->route('umkm.anggaran.index', ['periode' => $periode])
            ->with('success', 'Anggaran bulanan berhasil disimpan.');
    }
}
