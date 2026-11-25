<?php

namespace App\Services\Notifikasi;

use App\Models\Notifikasi;

/**
 * Satu antarmuka untuk semua cara kirim notifikasi (dipilih lewat NOTIF_DRIVER).
 * Driver melempar exception jika gagal; NotificationService mencatatnya sebagai status gagal.
 */
interface NotifikasiDriver
{
    /** @throws \Throwable jika pengiriman gagal */
    public function kirim(Notifikasi $notifikasi): void;
}
