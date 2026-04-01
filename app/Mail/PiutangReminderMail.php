<?php

namespace App\Mail;

use App\Models\Piutang;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PiutangReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Piutang $piutang;
    public string $tipe;

    /**
     * Create a new message instance.
     * $tipe bisa 'h3', 'h0', atau 'lewat'
     */
    public function __construct(Piutang $piutang, string $tipe = 'manual')
    {
        $this->piutang = $piutang;
        $this->tipe = $tipe;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $usaha = $this->piutang->umkm->nama_usaha ?? 'UMKM';
        $kode = $this->piutang->kode_piutang;
        
        // Subject Email dinamis
        $subject = "Pengingat Tagihan - {$usaha} - {$kode}";

        // Reply-To diarahkan ke email UMKM (user)
        $replyTo = [];
        if ($this->piutang->umkm->user && $this->piutang->umkm->user->email) {
            $replyTo[] = new Address($this->piutang->umkm->user->email, $usaha);
        }

        return new Envelope(
            subject: $subject,
            replyTo: $replyTo,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.piutang_reminder',
            with: [
                'piutang' => $this->piutang,
                'tipe' => $this->tipe,
                'pelanggan' => $this->piutang->pelanggan,
                'umkm' => $this->piutang->umkm
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
