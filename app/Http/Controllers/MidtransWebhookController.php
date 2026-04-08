<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\Request;

/**
 * Controller untuk menerima webhook/notification dari Midtrans.
 * Route ini TIDAK menggunakan auth middleware (Midtrans mengirim POST langsung).
 * Validasi dilakukan via signature key di MidtransService.
 */
class MidtransWebhookController extends Controller
{
    public function __construct(private MidtransService $midtransService) {}

    /**
     * Handle Midtrans notification callback.
     * URL ini harus didaftarkan di Midtrans Dashboard:
     * Settings → Payment Notification URL → https://yourdomain.com/midtrans/notification
     */
    public function handle(Request $request)
    {
        $result = $this->midtransService->handleNotification($request);

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}
