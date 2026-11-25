<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodePenilaian;
use App\Services\PeriodePenilaianService;
use DomainException;
use Illuminate\Http\Request;

/**
 * Admin membuka dan menutup periode penilaian ulang (FR-21).
 */
class PeriodeController extends Controller
{
    public function __construct(private PeriodePenilaianService $layanan) {}

    public function index()
    {
        $periode = PeriodePenilaian::withCount([
            'penilaian',
            'penilaian as menunggu_count' => fn ($q) => $q->where('status_peninjauan', 'menunggu'),
        ])->latest('id')->get();

        return view('admin.periode.index', compact('periode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'            => ['required', 'string', 'max:150'],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'nama.required'                  => 'Nama periode wajib diisi.',
            'tanggal_mulai.required'         => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date'             => 'Tanggal mulai tidak valid.',
            'tanggal_selesai.required'       => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.date'           => 'Tanggal selesai tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        try {
            $hasil = $this->layanan->buka($data, $request->user());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', e($e->getMessage()));
        }

        return redirect()->route('admin.periode.index')
            ->with('success', e("Periode dibuka. {$hasil['jumlah_dinotifikasi']} UMKM aktif dinotifikasi."));
    }

    public function tutup(Request $request, $id)
    {
        try {
            $this->layanan->tutup((int) $id, $request->user());
        } catch (DomainException $e) {
            return back()->with('error', e($e->getMessage()));
        }

        return redirect()->route('admin.periode.index')->with('success', 'Periode ditutup.');
    }
}
