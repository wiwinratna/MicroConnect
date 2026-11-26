<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUmkmRequest;
use App\Models\Indikator;
use App\Models\JenisUsaha;
use App\Services\BuatUmkmAdminService;
use App\Services\UbahLevelService;
use App\Models\Umkm;
use App\Models\UmkmLevel;
use App\Models\User;
use App\Models\IuranBulanan;
use App\Services\IuranService;
use App\Mail\UmkmAccountCreatedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UmkmController extends Controller
{
    public function __construct(private IuranService $iuranService) {}

    // ===================== DAFTAR UMKM =====================

    public function index(Request $request)
    {
        $query = Umkm::with(['user', 'level'])
                     ->withCount(['penjualan', 'piutang']);

        // Filter
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($keyword) {
                $q->where('nama_usaha', 'like', "%{$keyword}%")
                  ->orWhere('kode_umkm', 'like', "%{$keyword}%")
                  ->orWhere('jenis_usaha', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('level')) {
            $query->where('level_id', $request->level);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $umkmList   = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $levels     = UmkmLevel::all();

        // KPI untuk header
        $totalUmkm  = Umkm::count();
        $totalAktif = Umkm::where('status', 'aktif')->count();
        $iuranBelumBayar = IuranBulanan::where('periode', now()->format('Y-m'))
                                       ->where('status', 'belum_bayar')
                                       ->count();

        return view('admin.umkm.index', compact(
            'umkmList', 'levels', 'totalUmkm', 'totalAktif', 'iuranBelumBayar'
        ));
    }

    // ===================== FORM TAMBAH =====================

    public function create()
    {
        return view('admin.umkm.create', [
            'levels'     => UmkmLevel::orderBy('urutan')->get(),
            'jenisUsaha' => JenisUsaha::aktif()->orderBy('id')->get(),
            'indikator'  => Indikator::aktif()->with('pilihan')->orderBy('urutan')->get(),
        ]);
    }

    /** Simpan UMKM baru dari formulir Admin, lalu kirim email informasi akun. */
    public function store(AdminUmkmRequest $request, BuatUmkmAdminService $layanan)
    {
        [$user, $umkm, $sandiAwal] = $layanan->buat($request->validated(), $request->flagUsaha(), $request->user());
        $pesan = "UMKM berhasil didaftarkan dengan level {$umkm->level->nama_level}.";

        // Kirim email informasi akun ke UMKM
        try {
            Mail::to($user->email)->send(new UmkmAccountCreatedMail(
                user: $user,
                umkm: $umkm,
                tempPassword: $sandiAwal,
            ));
        } catch (\Exception $e) {
            // Jangan gagalkan pembuatan akun jika email gagal kirim
            report($e);

            return redirect()->route('admin.umkm.index')
                ->with('success', $pesan . ' Email informasi akun gagal dikirim.');
        }

        return redirect()->route('admin.umkm.index')
            ->with('success', $pesan . ' Email informasi akun telah dikirim.');
    }

    // ===================== DETAIL UMKM =====================

    public function show($id)
    {
        $umkm = Umkm::with(['user', 'level'])->findOrFail($id);

        // Ringkasan aktivitas
        $bulanIni = now()->startOfMonth()->toDateString();
        $bulanAkhir = now()->endOfMonth()->toDateString();

        $penjualanBulanIni = $umkm->penjualan()
            ->whereBetween('tanggal', [$bulanIni, $bulanAkhir])
            ->sum('total');

        $totalPiutangAktif = $umkm->piutang()
            ->whereIn('status', ['belum_lunas', 'sebagian'])
            ->sum('sisa');

        // Riwayat iuran 6 bulan terakhir
        $iuranList = $umkm->iuranBulanan()
            ->orderByDesc('periode')
            ->limit(6)
            ->get();

        $levels = UmkmLevel::all();
        $jenisUsaha = JenisUsaha::aktif()->orderBy('id')->get();

        return view('admin.umkm.show', compact(
            'umkm', 'penjualanBulanIni', 'totalPiutangAktif', 'iuranList', 'levels', 'jenisUsaha'
        ));
    }

    // ===================== UPDATE LEVEL & KONFIRMASI IURAN =====================

    public function updateLevel(Request $request, $id, UbahLevelService $layanan)
    {
        $data = $request->validate([
            'level_id' => 'required|exists:umkm_level,id',
            'alasan'   => 'required|string|max:1000',
        ], [
            'alasan.required' => 'Alasan perubahan level wajib diisi.',
        ]);

        $layanan->ubah((int) $id, (int) $data['level_id'], trim($data['alasan']), $request->user());

        return back()->with('success', 'Level UMKM diperbarui dan UMKM diberi tahu.');
    }

    /**
     * FR-27: Admin melengkapi jenis usaha, kategori, persediaan, dan produksi UMKM
     * (terutama UMKM lama). Setelah terisi, aturan modul berlaku untuk UMKM itu.
     * Jenis barang: persediaan otomatis ya, produksi diisi. Jenis jasa: produksi tidak,
     * persediaan diisi (Bagian 4A).
     */
    public function updateProfilUsaha(Request $request, $id)
    {
        $data = $request->validate([
            'jenis_usaha_id'      => ['nullable', 'exists:jenis_usaha,id'],
            'rumpun'              => ['required', Rule::in(['barang', 'jasa'])],
            'melakukan_produksi'  => ['nullable', 'required_if:rumpun,barang', Rule::in(['0', '1'])],
            'memiliki_persediaan' => ['nullable', 'required_if:rumpun,jasa', Rule::in(['0', '1'])],
        ], [
            'rumpun.required'              => 'Pilih jenis usaha (barang atau jasa).',
            'rumpun.in'                    => 'Jenis usaha tidak valid.',
            'jenis_usaha_id.exists'        => 'Kategori usaha yang dipilih tidak tersedia.',
            'melakukan_produksi.required_if'  => 'Jawab apakah usaha ini mengolah bahan menjadi produk.',
            'memiliki_persediaan.required_if' => 'Jawab apakah usaha ini memiliki persediaan barang.',
        ]);

        $umkm = Umkm::findOrFail($id);

        // Kategori baru, atau kategori lama bila tidak diubah, harus sesuai jenis usaha.
        $kategori = JenisUsaha::find($data['jenis_usaha_id'] ?? $umkm->jenis_usaha_id);
        if ($kategori && !$kategori->cocokDengan($data['rumpun'])) {
            throw ValidationException::withMessages([
                'jenis_usaha_id' => 'Pilih kategori usaha yang sesuai dengan jenis usaha.',
            ]);
        }

        $barang = $data['rumpun'] === 'barang';
        $baru = [
            'rumpun'              => $data['rumpun'],
            'memiliki_persediaan' => $barang ? true : ($data['memiliki_persediaan'] === '1'),
            'melakukan_produksi'  => $barang ? ($data['melakukan_produksi'] === '1') : false,
        ];

        if (!empty($data['jenis_usaha_id'])) {
            $baru['jenis_usaha_id'] = (int) $data['jenis_usaha_id'];
            $baru['jenis_usaha']    = $kategori->namaPendek();
        }

        $lama = $umkm->only(['jenis_usaha_id', 'rumpun', 'memiliki_persediaan', 'melakukan_produksi']);

        DB::transaction(function () use ($umkm, $baru, $lama, $request) {
            $umkm->update($baru);

            DB::table('log_audit')->insert([
                'pengguna_id' => $request->user()->id,
                'aksi'        => 'umkm.profil_usaha',
                'entitas'     => 'umkm',
                'entitas_id'  => $umkm->id,
                'data_lama'   => json_encode($lama),
                'data_baru'   => json_encode(Arr::only($baru, array_keys($lama))),
                'waktu'       => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        });

        return back()->with('success', 'Profil usaha UMKM diperbarui. Aturan modul kini berlaku untuk UMKM ini.');
    }

    public function konfirmasiIuran(Request $request, $id)
    {
        $request->validate([
            'iuran_id' => 'required|exists:iuran_bulanan,id',
        ]);

        $iuran = IuranBulanan::where('id', $request->iuran_id)
                              ->where('umkm_id', $id)
                              ->firstOrFail();

        $iuran->markLunas();

        return back()->with('success', 'Iuran berhasil dikonfirmasi sebagai lunas.');
    }

    public function toggleStatus($id)
    {
        $umkm = Umkm::findOrFail($id);
        $umkm->update([
            'status' => $umkm->status === 'aktif' ? 'nonaktif' : 'aktif',
        ]);

        return back()->with('success', 'Status UMKM diperbarui menjadi ' . $umkm->fresh()->status . '.');
    }
}
