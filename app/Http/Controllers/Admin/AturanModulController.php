<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Admin mengatur aturan tampilan modul per rumpun (FR-17, Tabel 3.8).
 * Perubahan di tabel aturan_modul langsung memengaruhi menu dan akses UMKM.
 * Satu modul hanya punya satu aturan per rumpun (unik di basis data).
 */
class AturanModulController extends Controller
{
    public const KONDISI = [
        'selalu'                   => 'Selalu ditampilkan',
        'jika_memiliki_persediaan' => 'Jika memiliki persediaan',
        'jika_melakukan_produksi'  => 'Jika melakukan produksi',
        'tidak_ditampilkan'        => 'Tidak ditampilkan',
        'sesuai_level'             => 'Sesuai level',
    ];

    public function index()
    {
        $modul  = DB::table('modul')->orderBy('id')->get();
        $aturan = DB::table('aturan_modul')->get()->groupBy('modul_id');

        return view('admin.aturan-modul.index', [
            'modul'   => $modul,
            'aturan'  => $aturan,
            'kondisi' => self::KONDISI,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'aturan'                     => ['required', 'array'],
            'aturan.*.*'                 => ['array'],
            'aturan.*.*.kondisi'         => ['required', Rule::in(array_keys(self::KONDISI))],
            'aturan.*.*.label_tampil'    => ['nullable', 'string', 'max:150'],
        ], [
            'aturan.*.*.kondisi.required' => 'Kondisi aturan wajib dipilih.',
            'aturan.*.*.kondisi.in'       => 'Kondisi aturan tidak valid.',
            'aturan.*.*.label_tampil.max' => 'Label terlalu panjang (maksimal 150 karakter).',
        ]);

        $moduls = DB::table('modul')->pluck('id')->all();

        DB::transaction(function () use ($data, $moduls, $request) {
            foreach ($data['aturan'] as $modulId => $perRumpun) {
                abort_unless(in_array((int) $modulId, $moduls, true), 422, 'Modul tidak dikenal.');

                foreach ($perRumpun as $rumpun => $isi) {
                    abort_unless(in_array($rumpun, ['barang', 'jasa'], true), 422, 'Jenis usaha tidak dikenal.');

                    $lama  = DB::table('aturan_modul')->where('modul_id', $modulId)->where('rumpun', $rumpun)->first();
                    $label = filled($isi['label_tampil'] ?? null) ? trim($isi['label_tampil']) : null;

                    if ($lama && $lama->kondisi === $isi['kondisi'] && $lama->label_tampil === $label) {
                        continue; // tidak berubah
                    }

                    DB::table('aturan_modul')->updateOrInsert(
                        ['modul_id' => $modulId, 'rumpun' => $rumpun],
                        ['kondisi' => $isi['kondisi'], 'label_tampil' => $label, 'updated_at' => now()]
                    );

                    $this->catat($request, 'aturan_modul.ubah', (int) $modulId, [
                        'rumpun' => $rumpun, 'kondisi' => $lama?->kondisi, 'label_tampil' => $lama?->label_tampil,
                    ], [
                        'rumpun' => $rumpun, 'kondisi' => $isi['kondisi'], 'label_tampil' => $label,
                    ]);
                }
            }
        });

        return redirect()->route('admin.aturan-modul.index')->with('success', 'Aturan modul disimpan.');
    }

    private function catat(Request $request, string $aksi, int $modulId, array $lama, array $baru): void
    {
        DB::table('log_audit')->insert([
            'pengguna_id' => $request->user()->id,
            'aksi'        => $aksi,
            'entitas'     => 'modul',
            'entitas_id'  => $modulId,
            'data_lama'   => json_encode($lama),
            'data_baru'   => json_encode($baru),
            'waktu'       => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
