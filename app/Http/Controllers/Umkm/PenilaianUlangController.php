<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Services\DataSistemService;
use App\Services\PenilaianUlangService;
use DomainException;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * UMKM mengisi penilaian ulang (FR-22). Hanya saat periode berlaku, satu kali per periode.
 * Pilihan terisi otomatis dari pembukuan dan jawaban terakhir, dan boleh diubah.
 */
class PenilaianUlangController extends Controller
{
    public function __construct(
        private PenilaianUlangService $layanan,
        private DataSistemService $sistem,
    ) {}

    public function index()
    {
        $umkm    = auth()->user()->umkm;
        $periode = $umkm ? $this->layanan->periodeBerlaku() : null;

        if (!$umkm || !$periode) {
            return view('umkm.penilaian-ulang.index', ['status' => 'tutup']);
        }

        if ($this->layanan->sudahMengisi($umkm, $periode)) {
            return view('umkm.penilaian-ulang.index', ['status' => 'sudah', 'periode' => $periode]);
        }

        $data = $this->layanan->bawaan($umkm);

        return view('umkm.penilaian-ulang.index', [
            'status'      => 'isi',
            'periode'     => $periode,
            'indikator'   => $data['indikator'],
            'bawaan'      => $data['bawaan'],
            'sebelumnya'  => $data['sebelumnya'],
            'sistem'      => $data['sistem'],
            'jenisSistem' => $data['indikator']->mapWithKeys(fn ($i) => [$i->id => $this->sistem->jenisSistem($i)])->all(),
        ]);
    }

    public function store(Request $request)
    {
        $umkm    = auth()->user()->umkm;
        $periode = $umkm ? $this->layanan->periodeBerlaku() : null;

        if (!$periode) {
            return redirect()->route('umkm.penilaian-ulang.index')->with('error', 'Periode penilaian sedang tidak dibuka.');
        }

        $request->validate(['jawaban' => ['required', 'array']], ['jawaban.required' => 'Kuesioner belum diisi.']);

        try {
            $this->layanan->kirim($umkm, $periode->id, (array) $request->input('jawaban'));
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['jawaban' => $e->getMessage()]);
        } catch (DomainException $e) {
            return redirect()->route('umkm.penilaian-ulang.index')->with('error', e($e->getMessage()));
        }

        return redirect()->route('umkm.penilaian.index')
            ->with('success', 'Penilaian ulang terkirim. Menunggu tinjauan Admin.');
    }
}
