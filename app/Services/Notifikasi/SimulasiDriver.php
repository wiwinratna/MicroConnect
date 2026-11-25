<?php

namespace App\Services\Notifikasi;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Log;

/**
 * Driver simulasi (bawaan): tidak mengirim apa pun ke luar. Pesan tersimpan di tabel
 * notifikasi dan terlihat di Kotak Notifikasi. Isi pesan tidak ditulis ke log karena
 * dapat memuat kata sandi awal.
 */
class SimulasiDriver implements NotifikasiDriver
{
    public function kirim(Notifikasi $notifikasi): void
    {
        Log::info('[notifikasi simulasi] pesan dicatat, tidak dikirim keluar', [
            'id'     => $notifikasi->id,
            'jenis'  => $notifikasi->jenis,
            'tujuan' => $notifikasi->no_tujuan,
        ]);
    }
}
