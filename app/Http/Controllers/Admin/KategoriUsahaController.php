<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisUsaha;
use App\Models\PengajuanPendaftaran;
use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Admin mengelola kategori usaha beserta jenisnya, barang atau jasa (FR-16).
 * Kategori tidak dihapus, hanya dinonaktifkan, agar data lama tetap utuh.
 */
class KategoriUsahaController extends Controller
{
    public function index()
    {
        return view('admin.kategori-usaha.index', [
            'kategori' => JenisUsaha::orderBy('id')->get(),
            'untuk'    => JenisUsaha::UNTUK,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        JenisUsaha::create($data + ['aktif' => true]);

        return redirect()->route('admin.kategori-usaha.index')->with('success', 'Kategori usaha ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kategori = JenisUsaha::findOrFail($id);
        $data = $this->validasi($request, $kategori->id);

        $this->pastikanJenisMasihCocok($kategori, $data['rumpun_bawaan']);

        DB::transaction(function () use ($kategori, $data) {
            $namaLama = $kategori->namaPendek();
            $kategori->update($data);

            // umkm.jenis_usaha menyimpan salinan nama; teks yang diketik sendiri tidak disentuh.
            Umkm::where('jenis_usaha_id', $kategori->id)
                ->where('jenis_usaha', $namaLama)
                ->update(['jenis_usaha' => $kategori->namaPendek()]);
        });

        return redirect()->route('admin.kategori-usaha.index')->with('success', 'Kategori usaha disimpan.');
    }

    public function ubahStatus($id)
    {
        $kategori = JenisUsaha::findOrFail($id);
        $kategori->update(['aktif' => !$kategori->aktif]);

        $pesan = $kategori->aktif
            ? 'Kategori diaktifkan kembali.'
            : 'Kategori dinonaktifkan dan tidak muncul lagi di pilihan.';

        return redirect()->route('admin.kategori-usaha.index')->with('success', $pesan);
    }

    private function validasi(Request $request, ?int $abaikanId = null): array
    {
        return $request->validate([
            'nama'          => ['required', 'string', 'max:150', Rule::unique('jenis_usaha', 'nama')->ignore($abaikanId)],
            'rumpun_bawaan' => ['required', Rule::in(array_keys(JenisUsaha::UNTUK))],
        ], [
            'nama.required'          => 'Nama kategori wajib diisi.',
            'nama.max'               => 'Nama kategori terlalu panjang (maksimal 150 karakter).',
            'nama.unique'            => 'Nama kategori ini sudah ada.',
            'rumpun_bawaan.required' => 'Pilih jenis usaha untuk kategori ini.',
            'rumpun_bawaan.in'       => 'Jenis usaha tidak valid.',
        ]);
    }

    /** Tolak perubahan jenis bila membuat UMKM atau pengajuan yang menunggu jadi tidak sesuai. */
    private function pastikanJenisMasihCocok(JenisUsaha $kategori, string $untuk): void
    {
        if ($untuk === 'ditentukan_umkm' || $untuk === $kategori->rumpun_bawaan) {
            return;
        }

        $lain = $untuk === 'barang' ? 'jasa' : 'barang';

        $dipakai = Umkm::where('jenis_usaha_id', $kategori->id)->where('rumpun', $lain)->exists()
            || PengajuanPendaftaran::where('jenis_usaha_id', $kategori->id)
                ->where('status', 'menunggu')->where('rumpun', $lain)->exists();

        if ($dipakai) {
            throw ValidationException::withMessages([
                'rumpun_bawaan' => "Kategori ini masih dipakai usaha berjenis {$lain}, jadi jenisnya tidak bisa diubah.",
            ]);
        }
    }
}
