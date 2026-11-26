<?php

namespace App\Http\Requests\Concerns;

use App\Models\Indikator;
use App\Models\JenisUsaha;
use App\Models\PilihanJawaban;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Aturan jenis usaha, kategori, pertanyaan lanjutan, dan kuesioner (FR-02, FR-03).
 * Dipakai pendaftaran calon UMKM dan penambahan UMKM oleh Admin.
 */
trait PeriksaProfilUsaha
{
    protected function aturanProfilUsaha(): array
    {
        return [
            'rumpun'           => ['required', Rule::in(['barang', 'jasa'])],
            'jenis_usaha_id'   => ['required', Rule::exists('jenis_usaha', 'id')->where('aktif', true)],
            'mengolah_bahan'   => ['nullable', Rule::in(['0', '1'])],
            'punya_persediaan' => ['nullable', Rule::in(['0', '1'])],

            'jawaban'                  => ['required', 'array'],
            'persetujuan_data_pribadi' => ['accepted'],
        ];
    }

    protected function pesanProfilUsaha(): array
    {
        return [
            'rumpun.in'             => 'Jenis usaha harus Barang atau Jasa.',
            'jenis_usaha_id.exists' => 'Kategori usaha yang dipilih tidak tersedia.',
            'jawaban.required'      => 'Kuesioner belum diisi.',
        ];
    }

    protected function atributProfilUsaha(): array
    {
        return ['rumpun' => 'Jenis usaha', 'jenis_usaha_id' => 'Kategori usaha'];
    }

    /** Pemeriksaan lintas kolom: kategori, pertanyaan lanjutan, dan kuesioner. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->hasAny(['rumpun', 'jenis_usaha_id'])) {
                return;
            }

            $this->periksaJenisUsaha($validator);
            $this->periksaKuesioner($validator);
        }];
    }

    private function periksaJenisUsaha(Validator $validator): void
    {
        $jenis = $this->input('rumpun');

        if (!JenisUsaha::find($this->input('jenis_usaha_id'))?->cocokDengan($jenis)) {
            $validator->errors()->add('jenis_usaha_id', 'Kategori usaha tidak sesuai dengan jenis usaha yang dipilih.');
            return;
        }

        if ($jenis === 'barang' && !in_array($this->input('mengolah_bahan'), ['0', '1'], true)) {
            $validator->errors()->add('mengolah_bahan', 'Jawab apakah usaha ini mengolah bahan menjadi produk.');
        }

        if ($jenis === 'jasa' && !in_array($this->input('punya_persediaan'), ['0', '1'], true)) {
            $validator->errors()->add('punya_persediaan', 'Jawab apakah usaha ini memiliki persediaan barang.');
        }
    }

    private function periksaKuesioner(Validator $validator): void
    {
        $jawaban = (array) $this->input('jawaban', []);

        foreach (Indikator::aktif()->orderBy('urutan')->get() as $indikator) {
            $pilihanId = $jawaban[$indikator->id] ?? null;

            $valid = $pilihanId
                && PilihanJawaban::where('id', $pilihanId)->where('indikator_id', $indikator->id)->exists();

            if (!$valid) {
                $validator->errors()->add(
                    "jawaban.{$indikator->id}",
                    "Pertanyaan {$indikator->urutan} belum dijawab."
                );
            }
        }
    }

    /**
     * [memiliki_persediaan, melakukan_produksi] (Bagian 4A):
     * barang: persediaan otomatis true, produksi dari jawaban;
     * jasa: produksi false, persediaan dari jawaban.
     *
     * @return array{0:bool,1:bool}
     */
    public function flagUsaha(): array
    {
        if ($this->input('rumpun') === 'barang') {
            return [true, $this->input('mengolah_bahan') === '1'];
        }

        return [$this->input('punya_persediaan') === '1', false];
    }
}
