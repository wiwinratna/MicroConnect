<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\PeriksaProfilUsaha;
use Illuminate\Foundation\Http\FormRequest;

/** Validasi formulir Admin "Daftarkan UMKM Baru". */
class AdminUmkmRequest extends FormRequest
{
    use PeriksaProfilUsaha;

    public function authorize(): bool
    {
        return true; // rute sudah dibatasi middleware Admin
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return $this->aturanProfilUsaha() + [
            'name'             => ['required', 'string', 'max:100'],
            'email'            => ['required', 'email', 'max:150', 'unique:users,email'],
            'nama_usaha'       => ['required', 'string', 'max:150'],
            'nib'              => ['nullable', 'string', 'max:100'],
            'alamat'           => ['nullable', 'string', 'max:500'],
            'no_telepon'       => ['nullable', 'string', 'max:50'],
            'no_whatsapp'      => ['nullable', 'string', 'max:20'],
            'level_id'         => ['nullable', 'exists:umkm_level,id'],
            'alasan'           => ['nullable', 'string', 'max:1000'],
            'recording_method' => ['nullable', 'in:periodik,perpetual'],
            'inventory_method' => ['nullable', 'in:FIFO,LIFO,Average'],
        ];
    }

    public function messages(): array
    {
        return $this->pesanProfilUsaha() + [
            'required'     => ':attribute wajib diisi.',
            'max'          => ':attribute terlalu panjang (maksimal :max karakter).',
            'accepted'     => 'Centang persetujuan pemilik atas pemrosesan data pribadi.',
            'email.email'  => 'Format email tidak benar.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
        ];
    }

    public function attributes(): array
    {
        return $this->atributProfilUsaha() + [
            'name'       => 'Nama pemilik',
            'email'      => 'Email',
            'nama_usaha' => 'Nama usaha',
        ];
    }
}
