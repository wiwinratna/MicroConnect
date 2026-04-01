<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Piutang;
use App\Models\NotifikasiLog;
use App\Mail\PiutangReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailPiutangReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piutang:email-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email reminder tagihan otomatis berdasarkan jadwal H-3, H-0, dan Telat.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan Email Pengingat...');

        $now = Carbon::now();
        $todayStr = $now->toDateString();
        $currentTimeStr = $now->format('H:i') . ':00'; // Detik diset 00 untuk perbandingan

        // 1. Ambil semua piutang yang aktif, email_reminder_enabled = 1, dan memiliki pelanggan dengan email
        // Serta belum dikirimi email hari ini
        $piutangList = Piutang::with(['pelanggan', 'umkm'])
            ->aktif() // Scope aktif (belum_lunas, sebagian)
            ->where('email_reminder_enabled', true)
            ->whereTime('reminder_send_time', '<=', $currentTimeStr)
            ->whereHas('pelanggan', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            })
            // Belum dikirim hari ini
            ->where(function ($q) use ($todayStr) {
                $q->whereNull('last_email_reminder_sent_at')
                  ->orWhereDate('last_email_reminder_sent_at', '!=', $todayStr);
            })
            ->get();

        $countSent = 0;

        foreach ($piutangList as $piutang) {
            $jatuhTempo = Carbon::parse($piutang->jatuh_tempo);
            
            // False parameter makes it return negative if date is in future
            $sisaHari = $now->copy()->startOfDay()->diffInDays($jatuhTempo->copy()->startOfDay(), false);

            $tipe = null;
            if ($sisaHari == 3) {
                $tipe = 'h3'; 
            } elseif ($sisaHari == 0) {
                $tipe = 'h0'; 
            } elseif ($sisaHari < 0) {
                $tipe = 'lewat'; 
            }

            // Jika ada event reminder yang cocok hari ini
            if ($tipe) {
                $this->info("Mengirim {$tipe} ke {$piutang->pelanggan->email} (Piutang: {$piutang->kode_piutang})...");

                try {
                    // Queue mailable. Jika worker tidak ada otomatis jalan synchronous
                    Mail::to($piutang->pelanggan->email)
                        ->send(new PiutangReminderMail($piutang, $tipe));

                    // Update timestamp & counter
                    $piutang->update([
                        'last_email_reminder_sent_at' => now(),
                        'email_reminder_count' => $piutang->email_reminder_count + 1
                    ]);

                    // Catat ke Notifikasi Log
                    NotifikasiLog::create([
                        'umkm_id' => $piutang->umkm_id,
                        'tujuan'  => $piutang->pelanggan->email,
                        'tipe'    => 'email',
                        'status'  => 'terkirim',
                        'pesan'   => "Auto-reminder [{$tipe}] via email terkirim.",
                        'notifiable_id' => $piutang->id,
                        'notifiable_type' => Piutang::class,
                        'dikirim_pada' => now(),
                    ]);
                    
                    $countSent++;

                } catch (\Exception $e) {
                    Log::error("Gagal mengirim auto-email reminder ke {$piutang->pelanggan->email}: " . $e->getMessage());

                    NotifikasiLog::create([
                        'umkm_id' => $piutang->umkm_id,
                        'tujuan'  => $piutang->pelanggan->email,
                        'tipe'    => 'email',
                        'status'  => 'gagal',
                        'pesan'   => "Auto-reminder [{$tipe}] Gagal: " . substr($e->getMessage(), 0, 200),
                        'notifiable_id' => $piutang->id,
                        'notifiable_type' => Piutang::class,
                        'dikirim_pada' => now(),
                    ]);
                }
            }
        }

        $this->info("Selesai. Total email terkirim otomatis: {$countSent}");
    }
}
