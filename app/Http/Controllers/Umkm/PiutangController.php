<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\Piutang;
use App\Models\PiutangPembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PiutangController extends Controller
{
    private function getUmkm()
    {
        return auth()->user()->umkm;
    }

    // ======================== DAFTAR PELANGGAN ========================

    public function indexPelanggan(Request $request)
    {
        $umkm = $this->getUmkm();
        
        $query = Pelanggan::where('umkm_id', $umkm->id);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_pelanggan', 'like', "%{$search}%")
                  ->orWhere('nama_instansi', 'like', "%{$search}%")
                  ->orWhere('no_whatsapp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $pelanggan = $query->withCount('piutang')
                           ->orderBy('nama_pelanggan')
                           ->paginate(15)
                           ->withQueryString();

        return view('umkm.piutang.pelanggan', compact('pelanggan'));
    }

    public function createPelanggan()
    {
        return view('umkm.piutang.create_pelanggan');
    }

    public function storePelanggan(Request $request)
    {
        $request->validate([
            'nama_pelanggan'         => 'required|string|max:100',
            'nama_instansi'          => 'nullable|string|max:100',
            'no_ktp'                 => 'nullable|string|max:50',
            'no_whatsapp'            => 'nullable|string|max:20',
            'kontak_alternatif'      => 'nullable|string|max:20',
            'email'                  => 'nullable|email|max:150',
            'alamat'                 => 'nullable|string|max:255',
            'nama_pic'               => 'nullable|string|max:100',
            'catatan'                => 'nullable|string',
            'batas_maksimal_piutang' => 'nullable|numeric|min:0',
            'jatuh_tempo_default'    => 'nullable|integer|min:0',
            'catatan_penagihan'      => 'nullable|string',
            'foto_ktp'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $fotoKtpPath = null;
        if ($request->hasFile('foto_ktp')) {
            $fotoKtpPath = $request->file('foto_ktp')->store('pelanggan/ktp', 'public');
        }

        Pelanggan::create([
            'umkm_id'                => $this->getUmkm()->id,
            'nama_pelanggan'         => $request->nama_pelanggan,
            'nama_instansi'          => $request->nama_instansi,
            'no_ktp'                 => $request->no_ktp,
            'no_whatsapp'            => $request->no_whatsapp,
            'kontak_alternatif'      => $request->kontak_alternatif,
            'email'                  => $request->email,
            'alamat'                 => $request->alamat,
            'nama_pic'               => $request->nama_pic,
            'catatan'                => $request->catatan,
            'batas_maksimal_piutang' => $request->batas_maksimal_piutang,
            'jatuh_tempo_default'    => $request->jatuh_tempo_default,
            'catatan_penagihan'      => $request->catatan_penagihan,
            'foto_ktp'               => $fotoKtpPath,
        ]);

        return redirect()->route('umkm.etalase.pelanggan.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function editPelanggan(Pelanggan $pelanggan)
    {
        abort_if($pelanggan->umkm_id !== $this->getUmkm()->id, 403);
        return view('umkm.piutang.edit_pelanggan', compact('pelanggan'));
    }

    public function updatePelanggan(Request $request, Pelanggan $pelanggan)
    {
        abort_if($pelanggan->umkm_id !== $this->getUmkm()->id, 403);

        $request->validate([
            'nama_pelanggan'         => 'required|string|max:100',
            'nama_instansi'          => 'nullable|string|max:100',
            'no_ktp'                 => 'nullable|string|max:50',
            'no_whatsapp'            => 'nullable|string|max:20',
            'kontak_alternatif'      => 'nullable|string|max:20',
            'email'                  => 'nullable|email|max:150',
            'alamat'                 => 'nullable|string|max:255',
            'nama_pic'               => 'nullable|string|max:100',
            'catatan'                => 'nullable|string',
            'batas_maksimal_piutang' => 'nullable|numeric|min:0',
            'jatuh_tempo_default'    => 'nullable|integer|min:0',
            'catatan_penagihan'      => 'nullable|string',
            'foto_ktp'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $dataUpdate = [
            'nama_pelanggan'         => $request->nama_pelanggan,
            'nama_instansi'          => $request->nama_instansi,
            'no_ktp'                 => $request->no_ktp,
            'no_whatsapp'            => $request->no_whatsapp,
            'kontak_alternatif'      => $request->kontak_alternatif,
            'email'                  => $request->email,
            'alamat'                 => $request->alamat,
            'nama_pic'               => $request->nama_pic,
            'catatan'                => $request->catatan,
            'batas_maksimal_piutang' => $request->batas_maksimal_piutang,
            'jatuh_tempo_default'    => $request->jatuh_tempo_default,
            'catatan_penagihan'      => $request->catatan_penagihan,
        ];

        if ($request->hasFile('foto_ktp')) {
            if ($pelanggan->foto_ktp) {
                Storage::disk('public')->delete($pelanggan->foto_ktp);
            }
            $dataUpdate['foto_ktp'] = $request->file('foto_ktp')->store('pelanggan/ktp', 'public');
        }

        $pelanggan->update($dataUpdate);

        return redirect()->route('umkm.etalase.pelanggan.index')->with('success', 'Data pelanggan diperbarui.');
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
            'email_reminder_enabled' => 'nullable|boolean',
            'reminder_send_time'     => 'nullable|date_format:H:i',
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
            'email_reminder_enabled' => $request->boolean('email_reminder_enabled'),
            'reminder_send_time'     => $request->reminder_send_time ?? '09:00:00',
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

        $sisa = (float) $piutang->sisa;

        // Validasi dasar
        $rules = [
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar'  => 'required|numeric|min:1|max:' . $sisa,
            'metode_bayar'  => 'nullable|string|max:50',
            'catatan'       => 'nullable|string',
        ];

        // Jika pembayaran parsial (< sisa), jatuh tempo baru WAJIB diisi
        $jumlahBayar = (float) $request->jumlah_bayar;
        if ($jumlahBayar > 0 && $jumlahBayar < $sisa) {
            $rules['jatuh_tempo_baru'] = 'required|date|after:today';
        }

        $request->validate($rules, [
            'jatuh_tempo_baru.required' => 'Jatuh tempo baru wajib diisi karena pembayaran belum lunas.',
            'jatuh_tempo_baru.after'    => 'Jatuh tempo baru harus setelah hari ini.',
            'jumlah_bayar.max'          => 'Jumlah bayar tidak boleh melebihi sisa piutang (Rp ' . number_format($sisa, 0, ',', '.') . ').',
            'jumlah_bayar.min'          => 'Jumlah bayar harus lebih dari 0.',
        ]);

        $pembayaran = DB::transaction(function () use ($request, $piutang, $jumlahBayar) {
            $pembayaran = $piutang->catatPembayaran(
                $jumlahBayar,
                $request->tanggal_bayar,
                $request->metode_bayar,
                $request->catatan,
                $request->jatuh_tempo_baru  // null jika lunas penuh
            );

            // Delegate posting jurnal ke AccountingService
            $accService = new \App\Services\AccountingService();
            $accService->jurnalPembayaranPiutang($this->getUmkm(), $pembayaran);

            return $pembayaran;
        });

        $isPelunasan = $pembayaran->is_pelunasan;
        $msg = $isPelunasan
            ? 'Pelunasan piutang berhasil dicatat. Jurnal penerimaan kas terbentuk.'
            : 'Pembayaran parsial berhasil dicatat. Sisa piutang: Rp ' . number_format($piutang->fresh()->sisa, 0, ',', '.') . '. Jatuh tempo diperbarui.';

        return back()->with('success', $msg);
    }
}
