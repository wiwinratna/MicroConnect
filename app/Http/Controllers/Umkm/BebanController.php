<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\JurnalUmum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BebanController extends Controller
{
    /**
     * Kategori beban operasional yang tersedia.
     * Format: ['kode_akun' => '6xx', 'label' => 'Nama Beban']
     * Kode mengikuti standar COA MINECT (6xx = Beban Operasional)
     */
    public static function kategoriBeban(): array
    {
        return [
            ['kode' => '610', 'nama' => 'Beban Listrik',       'icon' => 'zap'],
            ['kode' => '611', 'nama' => 'Beban Air',           'icon' => 'droplet'],
            ['kode' => '612', 'nama' => 'Beban Sewa',          'icon' => 'home'],
            ['kode' => '613', 'nama' => 'Beban Gaji/Upah',     'icon' => 'users'],
            ['kode' => '614', 'nama' => 'Beban Transport',     'icon' => 'truck'],
            ['kode' => '615', 'nama' => 'Beban Internet/Telp', 'icon' => 'smartphone'],
            ['kode' => '616', 'nama' => 'Beban Kemasan',       'icon' => 'package'],
            ['kode' => '699', 'nama' => 'Beban Lain-lain',     'icon' => 'file-text'],
        ];
    }

    public function index(Request $request)
    {
        $umkm  = auth()->user()->umkm;
        $bulan = $request->get('bulan', now()->format('Y-m'));
        [$year, $month] = explode('-', $bulan);

        $bebanList = JurnalUmum::where('umkm_id', $umkm->id)
            ->where('kode_akun', 'like', '6%')
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $totalBeban = $bebanList->sum('debit');

        return view('umkm.beban.index', compact('bebanList', 'totalBeban', 'bulan'));
    }

    public function create()
    {
        $kategori = self::kategoriBeban();
        return view('umkm.beban.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        $umkm = auth()->user()->umkm;

        $request->validate([
            'tanggal'   => 'required|date',
            'kode_beban'=> 'required|string',
            'nominal'   => 'required|numeric|min:1',
            'keterangan'=> 'required|string|max:200',
        ]);

        // Cari akun beban dari kategori
        $kategori = collect(self::kategoriBeban())->firstWhere('kode', $request->kode_beban);
        if (!$kategori) {
            return back()->withErrors(['kode_beban' => 'Kategori beban tidak valid.'])->withInput();
        }

        DB::transaction(function () use ($request, $umkm, $kategori) {
            $catArr      = (array) $kategori;
            $nominal     = (float) $request->nominal;
            $namaBeban   = $catArr['nama'] ?? '';
            $kodeBeban   = $catArr['kode'] ?? '';
            $keterangan  = $request->keterangan;

            // Auto-create COA beban jika belum ada
            $this->ensureCoa($umkm->id, $kodeBeban, $namaBeban);

            // Auto-create COA Kas jika belum ada
            $this->ensureCoa($umkm->id, '111', 'Kas');

            // Jurnal: Dr. Beban 6xx / Cr. Kas 111
            JurnalUmum::create([
                'umkm_id'    => $umkm->id,
                'tanggal'    => $request->tanggal,
                'kode_akun'  => $kodeBeban,
                'nama_akun'  => $namaBeban,
                'keterangan' => $keterangan,
                'debit'      => $nominal,
                'kredit'     => 0,
                'ref_tipe'   => 'beban',
            ]);

            JurnalUmum::create([
                'umkm_id'    => $umkm->id,
                'tanggal'    => $request->tanggal,
                'kode_akun'  => '111',
                'nama_akun'  => 'Kas',
                'keterangan' => $keterangan,
                'debit'      => 0,
                'kredit'     => $nominal,
                'ref_tipe'   => 'beban',
            ]);
        });

        return redirect()
            ->route('umkm.beban.index')
            ->with('success', 'Beban operasional berhasil dicatat dan jurnal otomatis terbentuk.');
    }

    /**
     * Pastikan COA sudah ada untuk UMKM ini, buat otomatis jika belum.
     */
    private function ensureCoa(int $umkmId, string $kode, string $nama): void
    {
        Coa::firstOrCreate(
            ['umkm_id' => $umkmId, 'kode_akun' => $kode],
            [
                'header_akun' => $this->headerAkun($kode),
                'nama_akun'   => $nama,
                'posisi_dr_cr'=> str_starts_with($kode, '1') ? 'Debit' : (str_starts_with($kode, '6') ? 'Debit' : 'Kredit'),
            ]
        );
    }

    private function headerAkun(string $kode): string
    {
        return match(true) {
            str_starts_with($kode, '1') => 'Aset',
            str_starts_with($kode, '2') => 'Kewajiban',
            str_starts_with($kode, '3') => 'Modal',
            str_starts_with($kode, '4') => 'Pendapatan',
            str_starts_with($kode, '5') => 'HPP',
            str_starts_with($kode, '6') => 'Beban',
            default                     => 'Lain-lain',
        };
    }
}
