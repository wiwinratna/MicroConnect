<?php

namespace Database\Seeders\Demo;

use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\Umkm\BebanController;
use App\Http\Controllers\Umkm\JurnalController;
use App\Http\Controllers\Umkm\PiutangController;
use App\Models\BahanBaku;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Umkm;
use App\Services\PenjualanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Mengisi transaksi contoh lewat controller dan service aplikasi, sehingga stok dan
 * jurnalnya sama dengan input manual. Jam dibuat berurutan agar kode transaksi unik.
 */
class PembuatTransaksi
{
    private int $detik = 0;

    /** @param Carbon[] $bulan awal tiap bulan, urut dari yang terlama */
    public function isi(Umkm $umkm, array $data, array $bulan, Carbon $hariIni): void
    {
        auth()->setUser($umkm->user);

        $awal = $bulan[0];
        $this->pada($awal);
        $this->panggil(JurnalController::class, 'store', [
            'tanggal'    => $awal->toDateString(),
            'keterangan' => 'Setoran modal awal',
            'kode_akun'  => ['111', '311'],
            'debit'      => [$data['modal'], 0],
            'kredit'     => [0, $data['modal']],
        ]);

        $bahan  = $this->buatBahan($umkm, $data['bahan']);
        $produk = $this->buatProduk($umkm, $data['produk'], $bahan);

        foreach ($bulan as $i => $b) {
            $tanggal = fn (int $hari) => $b->copy()->day($hari)->min($hariIni);

            if ($data['bahan']) {
                $this->pada($tanggal(3));
                $this->panggil(PembelianController::class, 'store', [
                    'tanggal'    => $tanggal(3)->toDateString(),
                    'supplier'   => 'Pemasok Contoh',
                    'bahan_id'   => array_values($bahan),
                    'qty'        => array_column($data['bahan'], 3),
                    'harga_beli' => array_column($data['bahan'], 2),
                ]);
            }

            foreach ($data['jual'] as [$hari, $nama, $jumlah]) {
                $this->jual($umkm, $produk[$nama], $jumlah, $tanggal($hari));
            }

            if (isset($data['kredit']) && $i === array_key_last($bulan)) {
                $this->jualKredit($umkm, $data['kredit'], $produk, $tanggal(20));
            }

            foreach ($data['beban'] as [$kode, $keterangan, $nominal]) {
                $this->pada($tanggal(25));
                $this->panggil(BebanController::class, 'store', [
                    'tanggal' => $tanggal(25)->toDateString(), 'kode_beban' => $kode,
                    'nominal' => $nominal, 'keterangan' => $keterangan,
                ]);
            }
        }
    }

    /** @return array<string,int> nama => id */
    private function buatBahan(Umkm $umkm, array $daftar): array
    {
        $hasil = [];

        foreach ($daftar as $nama => [$satuan, $stokAwal, $harga]) {
            $this->detik++;
            $this->panggil(BahanBakuController::class, 'store', [
                'nama_bahan' => $nama, 'satuan' => $satuan,
                'stok_awal'  => $stokAwal, 'harga_unit_awal' => $harga,
            ]);
            $hasil[$nama] = BahanBaku::where('umkm_id', $umkm->id)->where('nama_bahan', $nama)->firstOrFail()->id;
            Carbon::setTestNow(Carbon::now()->addSecond());
        }

        return $hasil;
    }

    /** @return array<string,int> nama => id */
    private function buatProduk(Umkm $umkm, array $daftar, array $bahan): array
    {
        $hasil = [];

        foreach ($daftar as $nama => [$harga, $komposisi, $satuan]) {
            $this->panggil(ProdukController::class, 'store', [
                'nama_produk'   => $nama,
                'satuan_produk' => $satuan,
                'harga_jual'    => $harga,
                'bahan_id'    => array_map(fn ($n) => $bahan[$n], array_keys($komposisi)),
                'qty'         => array_values($komposisi),
            ]);
            $hasil[$nama] = Produk::where('umkm_id', $umkm->id)->where('nama_produk', $nama)->firstOrFail()->id;
        }

        return $hasil;
    }

    private function jual(Umkm $umkm, int $produkId, int $jumlah, Carbon $tanggal): void
    {
        $this->pada($tanggal);
        app(PenjualanService::class)->prosesTransaksi(
            $umkm, [['produk_id' => $produkId, 'qty' => $jumlah]], $tanggal->toDateString(), 'tunai'
        );
    }

    private function jualKredit(Umkm $umkm, array $kredit, array $produk, Carbon $tanggal): void
    {
        $this->pada($tanggal);
        $this->panggil(PiutangController::class, 'storePelanggan', ['nama_pelanggan' => $kredit['pelanggan']]);
        $pelanggan = Pelanggan::where('umkm_id', $umkm->id)->where('nama_pelanggan', $kredit['pelanggan'])->firstOrFail();

        app(PenjualanService::class)->prosesTransaksi(
            $umkm,
            [['produk_id' => $produk[$kredit['produk']], 'qty' => $kredit['jumlah']]],
            $tanggal->toDateString(), 'piutang', $pelanggan->id,
            $tanggal->copy()->addDays(14)->toDateString(), $kredit['pelanggan']
        );
    }

    /** Setel jam aplikasi ke tanggal transaksi, maju satu detik tiap transaksi. */
    private function pada(Carbon $tanggal): void
    {
        Carbon::setTestNow($tanggal->copy()->setTime(8, 0)->addSeconds(++$this->detik));
    }

    private function panggil(string $controller, string $aksi, array $data): void
    {
        $request = Request::create('/demo', 'POST', $data);
        app()->instance('request', $request);

        $respons = app($controller)->{$aksi}($request);

        // Controller mengembalikan back()->withErrors() bila gagal; jangan dilewatkan diam-diam.
        $galat = method_exists($respons, 'getSession') ? $respons->getSession()?->get('errors') : null;
        if ($galat) {
            throw new RuntimeException("{$controller}@{$aksi}: " . $galat->first());
        }
    }
}
