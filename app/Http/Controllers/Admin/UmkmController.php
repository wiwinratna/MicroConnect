<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Umkm;
use App\Models\UmkmLevel;
use App\Models\User;
use App\Models\IuranBulanan;
use App\Services\IuranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        $levels = UmkmLevel::all();
        return view('admin.umkm.create', compact('levels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:100',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6',
            'nama_usaha'       => 'nullable|string|max:255',
            'nib'              => 'nullable|string|max:100',
            'alamat'           => 'nullable|string|max:500',
            'no_telepon'       => 'nullable|string|max:50',
            'jenis_usaha'      => 'nullable|string|max:50',
            'no_whatsapp'      => 'nullable|string|max:20',
            'level_id'         => 'nullable|exists:umkm_level,id',
            'recording_method' => 'nullable|in:periodik,perpetual',
            'inventory_method' => 'nullable|in:FIFO,LIFO,Average',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'user_group' => 'pelakuusaha',
            ]);

            Umkm::create([
                'kode_umkm'        => Umkm::getKodeUmkm(),
                'user_id'          => $user->id,
                'level_id'         => $request->level_id,
                'nama_usaha'       => $request->nama_usaha,
                'nib'              => $request->nib,
                'alamat'           => $request->alamat,
                'no_telepon'       => $request->no_telepon,
                'jenis_usaha'      => $request->jenis_usaha,
                'no_whatsapp'      => $request->no_whatsapp,
                'recording_method' => $request->recording_method ?? 'periodik',
                'inventory_method' => $request->inventory_method ?? 'Average',
                'status'           => 'aktif',
            ]);
        });

        return redirect()->route('admin.umkm.index')
                         ->with('success', 'UMKM berhasil didaftarkan.');
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

        return view('admin.umkm.show', compact(
            'umkm', 'penjualanBulanIni', 'totalPiutangAktif', 'iuranList', 'levels'
        ));
    }

    // ===================== UPDATE LEVEL & KONFIRMASI IURAN =====================

    public function updateLevel(Request $request, $id)
    {
        $request->validate([
            'level_id' => 'required|exists:umkm_level,id',
        ]);

        $umkm = Umkm::findOrFail($id);
        $umkm->update(['level_id' => $request->level_id]);

        return back()->with('success', 'Level UMKM berhasil diperbarui.');
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

    // ===================== DAFTAR IURAN =====================
    public function iuranIndex(Request $request)
    {
        $query = IuranBulanan::with('umkm')
            ->orderByDesc('periode')
            ->orderByDesc('created_at');
            
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $iuranList = $query->paginate(20)->withQueryString();

        return view('admin.iuran.index', compact('iuranList'));
    }
}
