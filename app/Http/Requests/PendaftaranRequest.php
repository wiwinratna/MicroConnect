<?php

namespace App\Http\Requests;

use App\Helpers\NomorWa;
use App\Http\Requests\Concerns\PeriksaProfilUsaha;
use App\Models\Umkm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi formulir pendaftaran calon UMKM (FR-01, FR-02, FR-03).
 * Semua pesan berbahasa Indonesia.
 */
class PendaftaranRequest extends FormRequest
{
    use PeriksaProfilUsaha;

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
        return $this->aturanProfilUsaha() + [
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
            'nib'          => ['nullable', 'digits:13'],
            'no_telepon'   => ['nullable', 'regex:/^[0-9+\-\s]{6,20}$/'],
        ];
    }

    public function messages(): array
    {
        return $this->pesanProfilUsaha() + [
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
            'nib.digits'       => 'NIB harus berisi 13 angka. Kosongkan bila belum punya.',
        ];
    }

    public function attributes(): array
    {
        return $this->atributProfilUsaha() + [
            'nama_pemilik' => 'Nama pemilik',
            'email'        => 'Email',
            'username'     => 'Username',
            'no_wa'        => 'Nomor WhatsApp',
            'nama_usaha'   => 'Nama usaha',
            'alamat'       => 'Alamat',
        ];
    }
}
