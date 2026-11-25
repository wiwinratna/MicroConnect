<?php

namespace App\Services\Notifikasi;

use App\Models\Notifikasi;
use RuntimeException;

/**
 * Kerangka driver gateway WhatsApp. Penyedia belum dipilih, jadi driver ini belum
 * mengirim apa pun dan selalu mencatat gagal; salinan aplikasi tetap tersedia.
 * Kunci dan alamat API nanti hanya dari .env. WhatsAppService v1.0 (Fonnte) tidak dipakai.
 */
class WhatsappDriver implements NotifikasiDriver
{
    public function kirim(Notifikasi $notifikasi): void
    {
        throw new RuntimeException('Driver WhatsApp belum dikonfigurasi.');
    }
}
