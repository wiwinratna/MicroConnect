<?php

namespace App\Http\Requests;

use App\Helpers\NomorWa;
use App\Models\Indikator;
use App\Models\JenisUsaha;
use App\Models\PilihanJawaban;
use App\Models\Umkm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validasi formulir pendaftaran calon UMKM (FR-01, FR-02, FR-03).
 * Semua pesan berbahasa Indonesia.
 */
class PendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email'    => strtolower(trim((string) $this->input('email'))),
            'username' => strtolower(trim((string) $this->input('username'))),
            'no_wa'    => NomorWa::normalisasi($this->input('no_wa')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_pemilik' => ['required', 'string', 'max:150'],
            'email'        => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email'),
                Rule::unique('pengajuan_pendaftaran', 'email')->where('status', 'menunggu'),
            ],
            'username'     => [
                'required', 'regex:/^[a-z0-9_]{4,30}$/',
                Rule::unique('users', 'username'),
                Rule::unique('pengajuan_pendaftaran', 'username')->where('status', 'menunggu'),
            ],
            'no_wa'        => [
                'required', 'regex:/^62[0-9]{8,13}$/',
                Rule::unique('users', 'no_wa'),
                Rule::unique('pengajuan_pendaftaran', 'no_wa')->where('status', 'menunggu'),
                function (string $attribute, mixed $value, \Closure $fail) {
                    // UMKM lama menyimpan nomor di umkm.no_whatsapp (format bebas).
                    $dipakai = Umkm::whereNotNull('no_whatsapp')->pluck('no_whatsapp')
                        ->contains(fn ($n) => NomorWa::normalisasi($n) === $value);
                    if ($dipakai) {
                        $fail('Nomor WhatsApp ini sudah dipakai akun yang ada.');
                    }
                },
            ],
            'nama_usaha'   => ['required', 'string', 'max:150'],
            'alamat'       => ['required', 'string', 'max:500'],
            'no_telepon'   => ['nullable', 'regex:/^[0-9+\-\s]{6,20}$/'],

            'jenis_usaha_id' => ['required', Rule::exists('jenis_usaha', 'id')->where('aktif', true)],
            'rumpun'         => ['nullable', Rule::in(['barang', 'jasa'])],
            'mengolah_bahan' => ['nullable', Rule::in(['0', '1'])],
            'punya_persediaan' => ['nullable', Rule::in(['0', '1'])],

            'jawaban'                  => ['required', 'array'],
            'persetujuan_data_pribadi' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'  => ':attribute wajib diisi.',
            'max'       => ':attribute terlalu panjang (maksimal :max karakter).',
            'accepted'  => 'Anda harus menyetujui pemrosesan data pribadi untuk melanjutkan.',
            'email.email'      => 'Format email tidak benar.',
            'email.unique'     => 'Email ini sudah dipakai akun yang ada atau pendaftaran yang sedang menunggu.',
            'username.regex'   => 'Username hanya boleh huruf kecil, angka, dan garis bawah (4-30 karakter).',
            'username.unique'  => 'Username ini sudah dipakai akun yang ada atau pendaftaran yang sedang menunggu.',
            'no_wa.regex'      => 'Nomor WhatsApp tidak benar. Contoh: 081234567890.',
            'no_wa.unique'     => 'Nomor WhatsApp ini sudah dipakai akun yang ada atau pendaftaran yang sedang menunggu.',
            'no_telepon.regex' => 'Nomor telepon usaha tidak benar.',
            'jenis_usaha_id.exists' => 'Jenis usaha yang dipilih tidak tersedia.',
            'jawaban.required' => 'Kuesioner belum diisi.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_pemilik'   => 'Nama pemilik',
            'email'          => 'Email',
            'username'       => 'Username',
            'no_wa'          => 'Nomor WhatsApp',
            'nama_usaha'     => 'Nama usaha',
            'alamat'         => 'Alamat',
            'jenis_usaha_id' => 'Jenis usaha',
        ];
    }

    /** Pemeriksaan lintas kolom: rumpun, pertanyaan lanjutan, dan kuesioner. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->has('jenis_usaha_id')) {
                return;
            }

            $this->periksaRumpun($validator);
            $this->periksaKuesioner($validator);
        }];
    }

    private function periksaRumpun(Validator $validator): void
    {
        $rumpun = $this->rumpunEfektif();

        if ($rumpun === null) {
            $validator->errors()->add('rumpun', 'Pilih apakah usaha kreatif Anda termasuk barang atau jasa.');
            return;
        }

        if ($rumpun === 'barang' && !in_array($this->input('mengolah_bahan'), ['0', '1'], true)) {
            $validator->errors()->add('mengolah_bahan', 'Jawab apakah usaha Anda mengolah bahan menjadi produk.');
        }

        if ($rumpun === 'jasa' && !in_array($this->input('punya_persediaan'), ['0', '1'], true)) {
            $validator->errors()->add('punya_persediaan', 'Jawab apakah usaha Anda memiliki persediaan barang.');
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

    /** Rumpun dari jenis usaha; untuk usaha kreatif dari pilihan calon. */
    public function rumpunEfektif(): ?string
    {
        $jenis = JenisUsaha::find($this->input('jenis_usaha_id'));

        if (!$jenis) {
            return null;
        }

        if (in_array($jenis->rumpun_bawaan, ['barang', 'jasa'], true)) {
            return $jenis->rumpun_bawaan;
        }

        return in_array($this->input('rumpun'), ['barang', 'jasa'], true) ? $this->input('rumpun') : null;
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
        if ($this->rumpunEfektif() === 'barang') {
            return [true, $this->input('mengolah_bahan') === '1'];
        }

        return [$this->input('punya_persediaan') === '1', false];
    }
}
