<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Umkm;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email informasi akun untuk UMKM yang baru didaftarkan oleh admin.
 * Berisi: email login, password sementara, info usaha, dan instruksi login.
 *
 * Password bersifat SEMENTARA — user diwajibkan ganti saat login pertama.
 */
class UmkmAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Umkm $umkm;
    public string $tempPassword;

    public function __construct(User $user, Umkm $umkm, string $tempPassword)
    {
        $this->user         = $user;
        $this->umkm         = $umkm;
        $this->tempPassword = $tempPassword;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Selamat Datang di MicroConnect KADIN — Informasi Akun Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.umkm_account_created',
            with: [
                'user'         => $this->user,
                'umkm'         => $this->umkm,
                'tempPassword' => $this->tempPassword,
                'loginUrl'     => route('umkm.login'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
