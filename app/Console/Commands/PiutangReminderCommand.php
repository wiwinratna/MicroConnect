<?php

namespace App\Console\Commands;

use App\Models\Piutang;
use App\Models\Umkm;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PiutangReminderCommand extends Command
{
    protected $signature   = 'piutang:reminder';
    protected $description = 'Kirim reminder WhatsApp untuk piutang yang jatuh tempo H-3 dan H-0';

    public function __construct(private WhatsAppService $wa)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $today  = now()->toDateString();
        $h3date = now()->addDays(3)->toDateString();

        $this->info("Cek piutang reminder — hari ini: {$today}");

        // ===== H-3: yang jatuh tempo 3 hari lagi =====
        $h3 = Piutang::aktif()
                     ->whereDate('jatuh_tempo', $h3date)
                     ->where('reminder_h3_terkirim', false)
                     ->with(['pelanggan', 'umkm'])
                     ->get();

        foreach ($h3 as $p) {
            $this->kirimReminder($p, 'h3');
        }

        $this->info("H-3: {$h3->count()} reminder dikirim.");

        // ===== H-0: yang jatuh tempo hari ini =====
        $h0 = Piutang::aktif()
                     ->whereDate('jatuh_tempo', $today)
                     ->where('reminder_h0_terkirim', false)
                     ->with(['pelanggan', 'umkm'])
                     ->get();

        foreach ($h0 as $p) {
            $this->kirimReminder($p, 'h0');
        }

        $this->info("H-0: {$h0->count()} reminder dikirim.");

        return Command::SUCCESS;
    }

    private function kirimReminder(Piutang $piutang, string $hari): void
    {
        $pelanggan = $piutang->pelanggan;
        $umkm      = $piutang->umkm;

        if (!$pelanggan->no_whatsapp) {
            $this->warn("Piutang {$piutang->kode_piutang}: pelanggan tidak punya no WA.");
            return;
        }

        $sisa       = number_format($piutang->sisa, 0, ',', '.');
        $jatuhTempo = Carbon::parse($piutang->jatuh_tempo)->isoFormat('D MMMM Y');

        if ($hari === 'h3') {
            $pesan = "Halo *{$pelanggan->nama_pelanggan}*, kami dari *{$umkm->nama_usaha}* ingin mengingatkan bahwa Anda memiliki tagihan sebesar *Rp {$sisa}* yang jatuh tempo pada *{$jatuhTempo}* (3 hari lagi).\n\nMohon segera melakukan pembayaran. Terima kasih 🙏";
            $tipe  = 'piutang_reminder_h3';
            $flag  = 'reminder_h3_terkirim';
        } else {
            $pesan = "Halo *{$pelanggan->nama_pelanggan}*, ini pengingat dari *{$umkm->nama_usaha}* bahwa tagihan Anda sebesar *Rp {$sisa}* *jatuh tempo hari ini* ({$jatuhTempo}).\n\nSilakan segera melunasi. Terima kasih 🙏";
            $tipe  = 'piutang_reminder_h0';
            $flag  = 'reminder_h0_terkirim';
        }

        $sukses = $this->wa->send(
            $pelanggan->no_whatsapp,
            $pesan,
            $umkm,
            $piutang,
            $tipe
        );

        if ($sukses) {
            $piutang->update([$flag => true]);
        }
    }
}
