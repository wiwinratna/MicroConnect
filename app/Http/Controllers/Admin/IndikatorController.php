<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Indikator;
use App\Models\UmkmLevel;
use App\Services\IndikatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** FR-15: layar Admin untuk indikator penilaian dan batas skor level. */
class IndikatorController extends Controller
{
    public function __construct(private IndikatorService $layanan)
    {
    }

    public function index()
    {
        return view('admin.indikator.index', [
            'indikator' => Indikator::with('pilihan')->orderByDesc('aktif')->orderBy('urutan')->orderBy('id')->get(),
            'dijawab'   => DB::table('detail_penilaian')->distinct()->pluck('indikator_id')->flip(),
            'levels'    => UmkmLevel::orderBy('urutan')->get(),
            'rentang'   => $this->layanan->rentangSkor(),
        ]);
    }

    public function create()
    {
        return view('admin.indikator.form', [
            'indikator' => new Indikator(['urutan' => (int) Indikator::max('urutan') + 1]),
        ]);
    }

    public function store(Request $request)
    {
        $this->layanan->simpan($this->validasi($request), $request->user());

        return redirect()->route('admin.indikator.index')->with('success',
            'Pertanyaan disimpan dalam keadaan nonaktif. Periksa batas skor level, lalu aktifkan.');
    }

    public function edit($id)
    {
        return view('admin.indikator.form', ['indikator' => Indikator::with('pilihan')->findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $this->layanan->simpan($this->validasi($request), $request->user(), Indikator::findOrFail($id));

        return redirect()->route('admin.indikator.index')->with('success', 'Pertanyaan disimpan.');
    }

    public function ubahStatus(Request $request, $id)
    {
        $indikator = $this->layanan->ubahStatus(Indikator::findOrFail($id), $request->user());

        return redirect()->route('admin.indikator.index')->with('success', $indikator->aktif
            ? 'Pertanyaan diaktifkan dan muncul di kuesioner.'
            : 'Pertanyaan dinonaktifkan dan tidak muncul lagi di kuesioner.');
    }

    public function destroy(Request $request, $id)
    {
        $this->layanan->hapus(Indikator::findOrFail($id), $request->user());

        return redirect()->route('admin.indikator.index')->with('success', 'Pertanyaan dihapus.');
    }

    public function simpanBatas(Request $request)
    {
        $data = $request->validate([
            'batas'       => ['required', 'array'],
            'batas.*.min' => ['required', 'integer', 'min:0', 'max:255'],
            'batas.*.max' => ['required', 'integer', 'min:0', 'max:255'],
        ], [
            'required' => 'Semua batas skor wajib diisi.',
            'integer'  => 'Batas skor harus berupa angka bulat.',
            'min'      => 'Batas skor paling kecil 0.',
            'max'      => 'Batas skor paling besar 255.',
        ]);

        $this->layanan->simpanBatas($data['batas'], $request->user());

        return redirect()->route('admin.indikator.index')->with('success', 'Batas skor level disimpan.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama'                         => ['required', 'string', 'max:150'],
            'definisi'                     => ['nullable', 'string', 'max:1000'],
            'pertanyaan'                   => ['required', 'string', 'max:1000'],
            'urutan'                       => ['required', 'integer', 'min:1', 'max:255'],
            'pilihan'                      => ['required', 'array', 'min:2', 'max:10'],
            'pilihan.*.id'                 => ['nullable', 'integer'],
            'pilihan.*.teks'               => ['required', 'string', 'max:255'],
            'pilihan.*.contoh'             => ['nullable', 'string', 'max:1000'],
            'pilihan.*.skor'               => ['required', 'integer', 'min:0', 'max:25'],
            'pilihan.*.di_luar_batas_mikro' => ['nullable', 'boolean'],
            'pilihan.*.nilai_min'          => ['nullable', 'numeric', 'min:0'],
            'pilihan.*.nilai_max'          => ['nullable', 'numeric', 'min:0'],
        ], [
            'required'     => ':attribute wajib diisi.',
            'integer'      => ':attribute harus berupa angka bulat.',
            'numeric'      => ':attribute harus berupa angka.',
            'pilihan.min'  => 'Isi paling sedikit dua pilihan jawaban.',
            'pilihan.max'  => 'Pilihan jawaban paling banyak :max.',
            'max.string'   => ':attribute terlalu panjang (maksimal :max karakter).',
            'max.numeric'  => ':attribute paling besar :max.',
            'min.numeric'  => ':attribute paling kecil :min.',
        ], [
            'nama'                => 'Nama indikator',
            'definisi'            => 'Keterangan',
            'pertanyaan'          => 'Pertanyaan',
            'urutan'              => 'Urutan',
            'pilihan'             => 'Pilihan jawaban',
            'pilihan.*.teks'      => 'Teks pilihan',
            'pilihan.*.contoh'    => 'Contoh',
            'pilihan.*.skor'      => 'Skor',
            'pilihan.*.nilai_min' => 'Angka batas bawah',
            'pilihan.*.nilai_max' => 'Angka batas atas',
        ]);
    }
}
