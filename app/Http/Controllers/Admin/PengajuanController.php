<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanPendaftaran;
use App\Models\UmkmLevel;
use App\Services\PersetujuanPengajuanService;
use DomainException;
use Illuminate\Http\Request;

/**
 * Verifikasi Admin atas pengajuan pendaftaran (FR-08 s.d. FR-10).
 * Hanya Admin yang menetapkan level (BR-01); sistem hanya merekomendasikan.
 */
class PengajuanController extends Controller
{
    public function __construct(private PersetujuanPengajuanService $layanan) {}

    /** FR-08: hanya pengajuan berstatus menunggu. */
    public function index()
    {
        $pengajuan = PengajuanPendaftaran::with(['jenisUsaha', 'penilaian.levelRekomendasi'])
            ->where('status', 'menunggu')
            ->orderBy('tanggal_pengajuan')
            ->get();

        return view('admin.pengajuan.index', compact('pengajuan'));
    }

    /** FR-09: data usaha, jawaban, skor per indikator, total, dan rekomendasi. */
    public function show($id)
    {
        $pengajuan = PengajuanPendaftaran::with([
            'jenisUsaha', 'penilaian.levelRekomendasi', 'penilaian.detail.indikator',
        ])->findOrFail($id);

        return view('admin.pengajuan.show', [
            'pengajuan' => $pengajuan,
            'levels'    => UmkmLevel::orderBy('urutan')->get(),
        ]);
    }

    /** FR-10: setujui sesuai rekomendasi, atau setujui dengan level berbeda (alasan wajib). */
    public function setujui(Request $request, $id)
    {
        $data = $request->validate([
            'level_akhir_id' => ['required', 'exists:umkm_level,id'],
            'alasan'         => ['nullable', 'string', 'max:1000'],
        ], [
            'level_akhir_id.required' => 'Pilih level akhir.',
            'level_akhir_id.exists'   => 'Level yang dipilih tidak tersedia.',
            'alasan.max'              => 'Alasan terlalu panjang (maksimal 1000 karakter).',
        ]);

        try {
            $umkm = $this->layanan->setujui((int) $id, (int) $data['level_akhir_id'], $data['alasan'] ?? null, $request->user());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', e($e->getMessage()));
        }

        return redirect()->route('admin.pengajuan.index')
            ->with('success', e('Pengajuan disetujui. Akun ' . $umkm->user->email . ' aktif dengan level ' . $umkm->level->nama_level . '.'));
    }

    /** FR-10: tolak dengan alasan wajib. */
    public function tolak(Request $request, $id)
    {
        $data = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:1000'],
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'alasan_penolakan.max'      => 'Alasan terlalu panjang (maksimal 1000 karakter).',
        ]);

        try {
            $this->layanan->tolak((int) $id, $data['alasan_penolakan'], $request->user());
        } catch (DomainException $e) {
            return back()->withInput()->with('error', e($e->getMessage()));
        }

        return redirect()->route('admin.pengajuan.index')->with('success', 'Pengajuan ditolak.');
    }
}
