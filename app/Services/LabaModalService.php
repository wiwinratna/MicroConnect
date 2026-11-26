<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\JurnalUmum;
use App\Models\StokMutasi;
use App\Models\Umkm;
use Illuminate\Support\Collection;

/**
 * Satu-satunya tempat menghitung Laba Rugi dan Perubahan Modal. Dipakai halaman Laporan,
 * ekspor PDF, dan isian modal pada penilaian ulang.
 *
 * Pembagian akun (disetujui pemilik, 2026-11-26):
 *  - Pendapatan : 4xx
 *  - Beban      : 503-508 dan 6xx
 *  - HPP        : akun 5xx selainnya (501 HPP, 502 Beban Bahan Baku, 510 Pembelian)
 *  - Modal      : 3xx; Prive = 312 (dan 32x)
 */
class LabaModalService
{
    private const BEBAN_5XX = ['503', '504', '505', '506', '507', '508'];

    /** Tanggal yang pasti mendahului semua transaksi, untuk hitungan kumulatif. */
    private const SEJAK_AWAL = '1900-01-01';

    public function __construct(private InventoryService $persediaan)
    {
    }

    /** Laba rugi untuk rentang tanggal (Y-m-d, inklusif). */
    public function labaRugi(Umkm $umkm, string $awal, string $akhir): array
    {
        $jurnal = JurnalUmum::where('umkm_id', $umkm->id)
            ->whereBetween('tanggal', [$awal, $akhir])
            ->get();

        $pendapatan = $jurnal->filter(fn ($j) => str_starts_with($j->kode_akun, '4'));
        $beban      = $jurnal->filter(fn ($j) => $this->beban($j->kode_akun));
        $hpp        = $jurnal->filter(
            fn ($j) => str_starts_with($j->kode_akun, '5') && !$this->beban($j->kode_akun)
        );

        $totalPendapatan = (float) ($pendapatan->sum('kredit') - $pendapatan->sum('debit'));
        $totalBeban      = $this->saldoDebit($beban);

        $isPeriodik = strtoupper($umkm->recording_method ?? 'PERIODIK') === 'PERIODIK';
        $persediaanAwal = $persediaanAkhir = $totalPembelianBulanIni = $saldoAwalBahan = $bahanLangsung = 0.0;

        if ($isPeriodik) {
            $sebelum = date('Y-m-d', strtotime($awal . ' -1 day'));
            $persediaanAwal  = $this->nilaiPersediaan($umkm, $sebelum);
            $persediaanAkhir = $this->nilaiPersediaan($umkm, $akhir);

            $totalPembelianBulanIni = $this->saldoDebit($hpp->where('kode_akun', '510'));
            $bahanLangsung          = $this->saldoDebit($hpp->where('kode_akun', '502'));
            // Saldo awal bahan yang dicatat pada periode ini ikut menjadi bahan tersedia.
            $saldoAwalBahan = (float) StokMutasi::where('umkm_id', $umkm->id)
                ->whereNotNull('bahan_id')
                ->where('ref_tipe', 'saldo_awal')
                ->whereBetween('tanggal', [$awal, $akhir])
                ->get()
                ->sum(fn ($m) => (float) $m->qty * (float) $m->harga_unit);

            $totalHpp = $persediaanAwal + $totalPembelianBulanIni + $saldoAwalBahan
                + $bahanLangsung - $persediaanAkhir;
        } else {
            $totalHpp = $this->saldoDebit($hpp);
        }

        $labaKotor = $totalPendapatan - $totalHpp;

        $bebanDetail = $beban->sortBy('kode_akun')->groupBy('kode_akun')->map(fn ($g) => [
            'nama'  => $g->first()->nama_akun,
            'total' => $this->saldoDebit($g),
        ]);

        $labaBersih = $labaKotor - $totalBeban;

        return compact(
            'pendapatan', 'totalPendapatan', 'hpp', 'totalHpp', 'labaKotor',
            'isPeriodik', 'persediaanAwal', 'persediaanAkhir', 'totalPembelianBulanIni',
            'saldoAwalBahan', 'bahanLangsung', 'beban', 'bebanDetail', 'totalBeban', 'labaBersih'
        );
    }

    /**
     * Modal akhir = modal awal + setoran modal + laba bersih - prive.
     *
     * @return array{modalAwal:float, setoranModal:float, labaBersih:float, prive:float, modalAkhir:float}
     */
    public function perubahanModal(Umkm $umkm, string $awal, string $akhir): array
    {
        $sebelum = date('Y-m-d', strtotime($awal . ' -1 day'));

        // Modal awal: seluruh akun modal sebelumnya ditambah laba/rugi yang terkumpul.
        $modalAwal = (float) $this->jurnalModal($umkm)->where('tanggal', '<', $awal)->get()
            ->sum(fn ($j) => $j->kredit - $j->debit)
            + $this->labaRugi($umkm, self::SEJAK_AWAL, $sebelum)['labaBersih'];

        [$barisPrive, $barisSetoran] = $this->jurnalModal($umkm)
            ->whereBetween('tanggal', [$awal, $akhir])->get()
            ->partition(fn ($j) => str_starts_with($j->kode_akun, '312') || str_starts_with($j->kode_akun, '32'));

        $setoranModal = (float) ($barisSetoran->sum('kredit') - $barisSetoran->sum('debit'));
        $prive        = $this->saldoDebit($barisPrive);
        $labaBersih   = $this->labaRugi($umkm, $awal, $akhir)['labaBersih'];
        $modalAkhir   = $modalAwal + $setoranModal + $labaBersih - $prive;

        return compact('modalAwal', 'setoranModal', 'labaBersih', 'prive', 'modalAkhir');
    }

    /** Modal akhir per tanggal tertentu, dihitung sejak transaksi pertama. */
    public function modalPer(Umkm $umkm, string $tanggal): float
    {
        return $this->perubahanModal($umkm, self::SEJAK_AWAL, $tanggal)['modalAkhir'];
    }

    private function jurnalModal(Umkm $umkm)
    {
        return JurnalUmum::where('umkm_id', $umkm->id)->where('kode_akun', 'like', '3%');
    }

    private function beban(string $kode): bool
    {
        return str_starts_with($kode, '6') || in_array($kode, self::BEBAN_5XX, true);
    }

    private function saldoDebit(Collection $baris): float
    {
        return (float) ($baris->sum('debit') - $baris->sum('kredit'));
    }

    private function nilaiPersediaan(Umkm $umkm, string $sampai): float
    {
        return (float) BahanBaku::where('umkm_id', $umkm->id)->get()
            ->sum(fn ($b) => $this->persediaan->buildLedger($umkm, 'bahan', $b->id, $sampai)['totalSaldoNilai']);
    }
}
