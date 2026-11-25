<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenilaianLevel;
use App\Models\PilihanJawaban;
use App\Models\UmkmLevel;
use App\Services\PenilaianUlangService;
use DomainException;
use Illuminate\Http\Request;

/**
 * Tinjauan Admin atas penilaian ulang (FR-24, FR-25). Hanya Admin yang menetapkan level (BR-01).
 */
class PenilaianUlangController extends Controller
{
    public function __construct(private PenilaianUlangService $layanan) {}

    public function index(Request $request)
    {
        $status = in_array($request->status, ['menunggu', 'selesai'], true) ? $request->status : 'menunggu';

        $penilaian = PenilaianLevel::with(['umkm', 'periode', 'levelRekomendasi', 'levelAkhir'])
            ->where('jenis', 'ulang')
            ->where('status_peninjauan', $status)
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.penilaian-ulang.index', compact('penilaian', 'status'));
    }

    public function show($id)
    {
        $penilaian = PenilaianLevel::with([
            'umkm.level', 'periode', 'levelRekomendasi', 'levelAkhir', 'detail.indikator',
        ])->where('jenis', 'ulang')->findOrFail($id);

        // Pembanding: penilaian terakhir UMKM yang sama sebelum penilaian ini.
        $sebelumnya = PenilaianLevel::with(['detail', 'levelAkhir'])
            ->where('umkm_id', $penilaian->umkm_id)
            ->where('id', '<', $penilaian->id)
            ->orderByDesc('id')->first();

        $dipilih = $penilaian->detail->keyBy('indikator_id');
        $luarBatas = PilihanJawaban::whereIn('id', $penilaian->detail->pluck('pilihan_id'))
            ->where('di_luar_batas_mikro', true)->pluck('id')->all();

        // Angka sistem yang direkam saat dikirim, dibandingkan dengan pilihan UMKM.
        $perbandingan = [];
        foreach (['omzet' => 'Omzet 12 bulan terakhir', 'modal' => 'Modal usaha'] as $kunci => $label) {
            $rekam = $penilaian->data_sistem[$kunci] ?? null;

            if (!$rekam) {
                continue;
            }

            $disarankanId = $rekam['pilihan_disarankan'] ?? null;
            $pilihUmkm = $dipilih->get($rekam['indikator_id']);

            $perbandingan[] = [
                'label'       => $label,
                'nilai'       => $rekam['nilai'],
                'disarankan'  => $disarankanId ? PilihanJawaban::find($disarankanId)?->teks : null,
                'dipilih'     => $pilihUmkm?->teks_pilihan,
                'diubah'      => $disarankanId !== null && $pilihUmkm && (int) $pilihUmkm->pilihan_id !== (int) $disarankanId,
            ];
        }

        return view('admin.penilaian-ulang.show', [
            'penilaian'    => $penilaian,
            'sebelumnya'   => $sebelumnya,
            'luarBatas'    => $luarBatas,
            'perbandingan' => $perbandingan,
            'levels'       => UmkmLevel::orderBy('urutan')->get(),
        ]);
    }

    public function tetapkan(Request $request, $id)
    {
        $data = $request->validate([
            'level_akhir_id'          => ['required', 'exists:umkm_level,id'],
            'alasan'                  => ['nullable', 'string', 'max:1000'],
            'catatan_peninjauan'      => ['nullable', 'string', 'max:1000'],
            'tindak_lanjut_pembinaan' => ['nullable', 'string', 'max:1000'],
        ], [
            'level_akhir_id.required' => 'Pilih level akhir.',
            'level_akhir_id.exists'   => 'Level yang dipilih tidak tersedia.',
            '*.max'                   => 'Isian terlalu panjang (maksimal 1000 karakter).',
        ]);

        try {
            $penilaian = $this->layanan->tetapkan(
                (int) $id, (int) $data['level_akhir_id'], $data['alasan'] ?? null,
                $data['catatan_peninjauan'] ?? null, $data['tindak_lanjut_pembinaan'] ?? null, $request->user()
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('error', e($e->getMessage()));
        }

        return redirect()->route('admin.penilaian-ulang.index')
            ->with('success', e('Level ' . $penilaian->umkm->nama_usaha . ' ditetapkan: ' . $penilaian->levelAkhir->nama_level . '.'));
    }
}
