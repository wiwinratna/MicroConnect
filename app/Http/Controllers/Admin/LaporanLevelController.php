<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\FeatureAccess;
use App\Helpers\KatalogLaporan;
use App\Http\Controllers\Controller;
use App\Models\UmkmLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Admin mengatur laporan yang tersedia per level (FR-18, Tabel 3.9).
 * Disimpan di umkm_level.fitur (konfigurasi v1.0 dipakai ulang); hanya kunci
 * laporan dalam KatalogLaporan yang diubah, fitur lain tidak tersentuh.
 */
class LaporanLevelController extends Controller
{
    public function index()
    {
        return view('admin.laporan-level.index', [
            'levels'  => UmkmLevel::orderBy('urutan')->get(),
            'laporan' => KatalogLaporan::DAFTAR,
        ]);
    }

    public function update(Request $request)
    {
        $levels = UmkmLevel::orderBy('urutan')->get();

        $request->validate([
            'matriks'     => ['nullable', 'array'],
            'matriks.*'   => ['nullable', 'array'],
            'matriks.*.*' => ['string', Rule::in(array_keys(KatalogLaporan::DAFTAR))],
        ], [
            'matriks.*.*.in' => 'Laporan yang dipilih tidak dikenal.',
        ]);

        DB::transaction(function () use ($levels, $request) {
            foreach ($levels as $level) {
                $lama = (array) ($level->fitur ?? []);

                // Kolom kosong: mulai dari konfigurasi v1.0 agar fitur non-laporan tidak hilang.
                if ($lama === []) {
                    $lama = FeatureAccess::forLevel((int) preg_replace('/\D/', '', $level->kode));
                }

                $baru = KatalogLaporan::terapkan($lama, (array) $request->input("matriks.{$level->id}", []));

                if ($this->samaIsi($lama, $baru)) {
                    continue;
                }

                $level->update(['fitur' => $baru]);

                DB::table('log_audit')->insert([
                    'pengguna_id' => $request->user()->id,
                    'aksi'        => 'laporan_level.ubah',
                    'entitas'     => 'umkm_level',
                    'entitas_id'  => $level->id,
                    'data_lama'   => json_encode(array_values(array_intersect($lama, KatalogLaporan::semuaKunci()))),
                    'data_baru'   => json_encode(array_values(array_intersect($baru, KatalogLaporan::semuaKunci()))),
                    'waktu'       => now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        });

        return redirect()->route('admin.laporan-level.index')->with('success', 'Pengaturan laporan per level disimpan.');
    }

    private function samaIsi(array $a, array $b): bool
    {
        sort($a);
        sort($b);

        return $a === $b;
    }
}
