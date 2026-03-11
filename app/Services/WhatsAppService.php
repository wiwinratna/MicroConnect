<?php

namespace App\Services;

use App\Models\NotifikasiLog;
use App\Models\Umkm;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppService
 *
 * Wrapper untuk mengirim pesan WhatsApp via Fonnte API.
 * Fonnte: https://fonnte.com | Biaya ~Rp 150/pesan
 *
 * Config di .env:
 *   FONNTE_TOKEN=xxxxx
 *   FONNTE_DELAY=2        (detik jeda antar pesan, default 2)
 *
 * Untuk TA: jika belum punya token, set FONNTE_TOKEN=demo
 * dan service akan log saja tanpa benar-benar kirim.
 */
class WhatsAppService
{
    private string $token;
    private bool   $demoMode;

    public function __construct()
    {
        $this->token    = env('FONNTE_TOKEN', '');
        $this->demoMode = empty($this->token) || $this->token === 'demo';
    }

    /**
     * Kirim pesan WA ke nomor tujuan.
     *
     * @param string $to         Nomor WA tujuan (format: 628xxxxxxxx atau 08xxxxxxxx)
     * @param string $message    Isi pesan
     * @param Umkm   $umkm       UMKM pengirim (untuk log)
     * @param mixed  $notifiable  Model Piutang/IuranBulanan (polymorphic)
     * @param string $tipe       Tipe notifikasi
     * @return bool
     */
    public function send(
        string $to,
        string $message,
        Umkm $umkm,
        mixed $notifiable = null,
        string $tipe = 'manual'
    ): bool {
        // Normalisasi nomor: 08xxx → 628xxx
        $to = $this->normalizeNumber($to);

        // Buat log dulu dengan status pending
        $log = NotifikasiLog::create([
            'umkm_id'         => $umkm->id,
            'tipe'            => $tipe,
            'tujuan'          => $to,
            'pesan'           => $message,
            'status'          => 'pending',
            'notifiable_id'   => $notifiable?->id,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
        ]);

        // Demo mode: log saja, jangan kirim HTTP
        if ($this->demoMode) {
            Log::info('[WhatsApp DEMO] Kirim ke ' . $to . ': ' . $message);
            $log->update([
                'status'      => 'terkirim',
                'response'    => json_encode(['demo' => true]),
                'dikirim_pada' => now(),
            ]);
            return true;
        }

        // Kirim ke Fonnte API
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post('https://api.fonnte.com/send', [
                'target'      => $to,
                'message'     => $message,
                'countryCode' => '62',
                'delay'       => env('FONNTE_DELAY', 2),
            ]);

            $success = $response->successful() && ($response->json('status') !== false);

            $log->update([
                'status'      => $success ? 'terkirim' : 'gagal',
                'response'    => $response->body(),
                'dikirim_pada' => now(),
            ]);

            return $success;

        } catch (\Throwable $e) {
            Log::error('[WhatsApp] Gagal kirim: ' . $e->getMessage());
            $log->update(['status' => 'gagal', 'response' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Normalisasi nomor WA.
     * 08xxx     → 628xxx
     * +628xxx   → 628xxx
     * 628xxx    → tetap
     */
    private function normalizeNumber(string $number): string
    {
        $number = preg_replace('/\D/', '', $number); // hapus non-digit
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        } elseif (str_starts_with($number, '+62')) {
            $number = '62' . substr($number, 3);
        }
        return $number;
    }
}
