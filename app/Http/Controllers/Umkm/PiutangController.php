<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Piutang;
use App\Models\PiutangPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PiutangController extends Controller
{
    private function getUmkm()
    {
        return auth()->user()->umkm;
    }

    // ======================== DAFTAR PELANGGAN ========================

    public function indexPelanggan()
    {
        $umkm      = $this->getUmkm();
        $pelanggan = Pelanggan::where('umkm_id', $umkm->id)
                              ->withCount('piutang')
                              ->orderBy('nama_pelanggan')
                              ->paginate(15);

        return view('umkm.piutang.pelanggan', compact('pelanggan'));
    }

    public function storePelanggan(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:100',
            'no_whatsapp'    => 'nullable|string|max:20',
            'alamat'         => 'nullable|string|max:255',
            'catatan'        => 'nullable|string',
        ]);

        Pelanggan::create([
            'umkm_id'        => $this->getUmkm()->id,
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_whatsapp'    => $request->no_whatsapp,
            'alamat'         => $request->alamat,
            'catatan'        => $request->catatan,
        ]);

        return back()->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function destroyPelanggan(Pelanggan $pelanggan)
    {
        abort_if($pelanggan->umkm_id !== $this->getUmkm()->id, 403);

        $pelanggan->delete();
        return back()->with('success', 'Pelanggan dihapus.');
    }

    // ======================== DAFTAR PIUTANG ========================

    public function index(Request $request)
    {
        $umkm   = $this->getUmkm();
        $status = $request->get('status', 'aktif');

        $query = Piutang::where('umkm_id', $umkm->id)->with('pelanggan');

        if ($status === 'lunas') {
            $query->where('status', 'lunas');
        } elseif ($status === 'semua') {
            // tampilkan semua
        } else {
            // default: aktif (belum_lunas + sebagian)
            $query->whereIn('status', ['belum_lunas', 'sebagian']);
        }

        $piutang = $query->orderByDesc('jatuh_tempo')->paginate(20)->withQueryString();

        $totalSisa = Piutang::where('umkm_id', $umkm->id)
                            ->aktif()
                            ->sum('sisa');

        return view('umkm.piutang.index', compact('piutang', 'totalSisa'));
    }

    public function create()
    {
        $umkm      = $this->getUmkm();
        $pelanggan = Pelanggan::where('umkm_id', $umkm->id)
                              ->orderBy('nama_pelanggan')
                              ->get();

        return view('umkm.piutang.create', compact('pelanggan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pelanggan_id' => 'required|exists:pelanggan,id',
            'tanggal'      => 'required|date',
            'jatuh_tempo'  => 'required|date|after_or_equal:tanggal',
            'nominal_awal' => 'required|numeric|min:1',
            'catatan'      => 'nullable|string',
        ]);

        $umkm = $this->getUmkm();

        // Pastikan pelanggan milik UMKM ini
        abort_if(
            !Pelanggan::where('id', $request->pelanggan_id)
                      ->where('umkm_id', $umkm->id)
                      ->exists(),
            403
        );

        Piutang::create([
            'umkm_id'       => $umkm->id,
            'pelanggan_id'  => $request->pelanggan_id,
            'kode_piutang'  => Piutang::generateKode(),
            'tanggal'       => $request->tanggal,
            'jatuh_tempo'   => $request->jatuh_tempo,
            'nominal_awal'  => $request->nominal_awal,
            'sudah_dibayar' => 0,
            'sisa'          => $request->nominal_awal,
            'status'        => 'belum_lunas',
            'catatan'       => $request->catatan,
        ]);

        return redirect()->route('umkm.piutang.index')
                         ->with('success', 'Piutang berhasil dicatat.');
    }

    public function show(Piutang $piutang)
    {
        abort_if($piutang->umkm_id !== $this->getUmkm()->id, 403);

        $piutang->load('pelanggan', 'pembayaran');
        return view('umkm.piutang.show', compact('piutang'));
    }

    // ======================== CATAT PEMBAYARAN ========================

    public function bayar(Request $request, Piutang $piutang)
    {
        abort_if($piutang->umkm_id !== $this->getUmkm()->id, 403);

        $request->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar'  => 'required|numeric|min:1|max:' . $piutang->sisa,
            'metode_bayar'  => 'nullable|string|max:50',
            'catatan'       => 'nullable|string',
        ]);

        $pembayaran = $piutang->catatPembayaran(
            (float) $request->jumlah_bayar,
            $request->tanggal_bayar,
            $request->metode_bayar,
            $request->catatan
        );

        // ======================
        // (E) DELEGATE POSTING JURNAL KE ACCOUNTING SERVICE
        // ======================
        $accService = new \App\Services\AccountingService();
        $accService->jurnalPembayaranPiutang($this->getUmkm(), $pembayaran);

        return back()->with('success', 'Pembayaran berhasil dicatat dan jurnal penerimaan kas terbentuk.');
    }
}
