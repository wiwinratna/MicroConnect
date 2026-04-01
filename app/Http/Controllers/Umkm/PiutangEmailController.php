<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Piutang;
use App\Models\NotifikasiLog;
use App\Mail\PiutangReminderMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class PiutangEmailController extends Controller
{
    private function getUmkm()
    {
        return auth()->user()->umkm;
    }

    /**
     * Tampilkan preview email di browser
     */
    public function preview(Piutang $piutang)
    {
        abort_if($piutang->umkm_id !== $this->getUmkm()->id, 403);

        $tipe = request()->get('tipe', 'manual');
        return new PiutangReminderMail($piutang, $tipe);
    }

    /**
     * Kirim email secara manual via tombol UI
     */
    public function sendManual(Request $request, Piutang $piutang)
    {
        abort_if($piutang->umkm_id !== $this->getUmkm()->id, 403);

        if (!$piutang->pelanggan->email) {
            return back()->with('error', 'Gagal mengirim. Pelanggan ini belum memiliki alamat email yang valid.');
        }

        try {
            // Kita gunakan send() biasa agar error bisa langsung tertangkap di try-catch jika SMTP gagal
            // Bila production bisa diganti ke queue()
            Mail::to($piutang->pelanggan->email)
                ->send(new PiutangReminderMail($piutang, 'manual'));

            // Update stats
            $piutang->update([
                'last_email_reminder_sent_at' => now(),
                'email_reminder_count' => $piutang->email_reminder_count + 1
            ]);

            // Catat log
            NotifikasiLog::create([
                'umkm_id' => $this->getUmkm()->id,
                'tujuan'  => $piutang->pelanggan->email,
                'tipe'    => 'email',
                'status'  => 'terkirim',
                'pesan'   => "Berhasil mengirim pengingat manual via email.",
                'notifiable_id' => $piutang->id,
                'notifiable_type' => \App\Models\Piutang::class,
                'dikirim_pada' => now(),
            ]);

            return back()->with('success', 'Email pengingat berhasil dikirim ke ' . $piutang->pelanggan->email);

        } catch (\Exception $e) {
            Log::error('Gagal kirim email reminder manual: ' . $e->getMessage());
            
            NotifikasiLog::create([
                'umkm_id' => $this->getUmkm()->id,
                'tujuan'  => $piutang->pelanggan->email,
                'tipe'    => 'email',
                'status'  => 'gagal',
                'pesan'   => "Gagal kirim: " . substr($e->getMessage(), 0, 200),
                'notifiable_id' => $piutang->id,
                'notifiable_type' => \App\Models\Piutang::class,
                'dikirim_pada' => now(),
            ]);

            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }
}
