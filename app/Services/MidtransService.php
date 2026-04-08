<?php

namespace App\Services;

use App\Models\IuranBulanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * MidtransService
 *
 * Mengelola integrasi Midtrans SNAP:
 * - Membuat transaksi SNAP (popup)
 * - Menangani notification/webhook dari Midtrans
 * - Validasi signature key
 *
 * Semua konfigurasi terpusat di config/midtrans.php dan .env.
 * Untuk pindah dari Sandbox ke Production, hanya ubah .env.
 */
class MidtransService
{
    public function __construct()
    {
        // Set konfigurasi Midtrans dari config
        \Midtrans\Config::$serverKey    = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized  = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds        = config('midtrans.is_3ds');
    }

    /**
     * Buat transaksi SNAP untuk iuran bulanan.
     * Menghasilkan snap_token dan redirect_url.
     *
     * @return array{snap_token: string, redirect_url: string}
     */
    public function createSnapTransaction(IuranBulanan $iuran): array
    {
        $umkm = $iuran->umkm;
        $user  = $umkm->user;

        // Generate order_id unik
        $orderId = 'IURAN-' . $umkm->kode_umkm . '-' . $iuran->periode . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $iuran->nominal,
            ],
            'customer_details' => [
                'first_name' => $user->name ?? 'UMKM',
                'email'      => $user->email ?? '',
                'phone'      => $umkm->no_whatsapp ?? $umkm->no_telepon ?? '',
            ],
            'item_details' => [
                [
                    'id'       => 'IURAN-' . $iuran->id,
                    'price'    => (int) $iuran->nominal,
                    'quantity' => 1,
                    'name'     => 'Iuran Bulanan ' . $iuran->periode,
                ],
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // Simpan ke database
        $iuran->update([
            'midtrans_order_id'    => $orderId,
            'midtrans_snap_token'  => $snapToken,
            'midtrans_payment_url' => null, // reset jika ada sebelumnya
            'status'               => 'pending',
        ]);

        return [
            'snap_token'   => $snapToken,
            'redirect_url' => null,
        ];
    }

    /**
     * Handle notification/webhook dari Midtrans.
     * Validasi signature key, update status iuran.
     *
     * @return array{success: bool, message: string}
     */
    public function handleNotification(Request $request): array
    {
        try {
            $notification = new \Midtrans\Notification();
        } catch (\Exception $e) {
            Log::error('Midtrans notification error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Invalid notification'];
        }

        $orderId           = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus       = $notification->fraud_status ?? null;

        Log::info("Midtrans webhook: order={$orderId}, status={$transactionStatus}, fraud={$fraudStatus}");

        // Cari iuran berdasarkan order_id
        $iuran = IuranBulanan::where('midtrans_order_id', $orderId)->first();

        if (!$iuran) {
            Log::warning("Midtrans webhook: iuran not found for order_id={$orderId}");
            return ['success' => false, 'message' => 'Order not found'];
        }

        // Jangan proses ulang jika sudah lunas
        if ($iuran->isLunas()) {
            return ['success' => true, 'message' => 'Already settled'];
        }

        // Proses berdasarkan transaction_status
        match ($transactionStatus) {
            'capture' => $this->handleCapture($iuran, $fraudStatus),
            'settlement' => $iuran->markLunas(),
            'pending' => $iuran->update(['status' => 'pending']),
            'deny' => $iuran->update(['status' => 'deny']),
            'expire' => $iuran->update(['status' => 'expire']),
            'cancel' => $iuran->update(['status' => 'cancel']),
            default => Log::info("Midtrans webhook: unhandled status {$transactionStatus}"),
        };

        return ['success' => true, 'message' => "Processed: {$transactionStatus}"];
    }

    /**
     * Handle capture status (khusus kartu kredit).
     */
    private function handleCapture(IuranBulanan $iuran, ?string $fraudStatus): void
    {
        if ($fraudStatus === 'accept') {
            $iuran->markLunas();
        } elseif ($fraudStatus === 'challenge') {
            $iuran->update(['status' => 'pending']);
        } else {
            $iuran->update(['status' => 'deny']);
        }
    }
}
