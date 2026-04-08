<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f6f9; padding-bottom: 60px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 40px; }
        .header { background: linear-gradient(135deg, #6366f1, #4f46e5); color: #ffffff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .header p { margin: 8px 0 0; font-size: 14px; opacity: 0.9; }
        .content { padding: 30px; color: #333333; line-height: 1.6; }
        .content p { margin-top: 0; margin-bottom: 16px; font-size: 15px; }
        .info-box { background-color: #f0f0ff; border: 1px solid #e0e0ff; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { padding: 8px 0; font-size: 14px; border-bottom: 1px dashed #e0e0ff; vertical-align: top; }
        .info-box td:first-child { color: #666; width: 40%; }
        .info-box td:last-child { font-weight: 600; color: #1e293b; }
        .info-box tr:last-child td { border-bottom: none; }
        .credential-box { background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .credential-box h3 { margin: 0 0 12px; font-size: 15px; color: #92400e; }
        .credential-box table { width: 100%; border-collapse: collapse; }
        .credential-box td { padding: 6px 0; font-size: 14px; }
        .credential-box td:first-child { color: #92400e; width: 30%; }
        .credential-box td:last-child { font-weight: 700; color: #1e293b; font-family: monospace; font-size: 15px; }
        .warning-box { background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
        .warning-box p { margin: 0; font-size: 13px; color: #dc2626; }
        .btn-login { display: inline-block; background: linear-gradient(135deg, #6366f1, #4f46e5); color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-weight: 600; font-size: 15px; margin-top: 8px; }
        .steps { margin: 16px 0; padding-left: 20px; }
        .steps li { margin-bottom: 8px; font-size: 14px; color: #555; }
        .footer { padding: 25px 30px; text-align: center; font-size: 13px; color: #777777; background-color: #ffffff; border-top: 1px solid #eeeeee; }
        .footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <h1>🎉 Selamat Datang di MicroConnect!</h1>
                    <p>Akun UMKM Anda telah berhasil dibuat</p>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <p>Halo <strong>{{ $user->name }}</strong>,</p>

                    <p>Akun Anda telah didaftarkan oleh Admin KADIN pada sistem <strong>MicroConnect</strong> — platform manajemen UMKM terintegrasi.</p>

                    {{-- Info Usaha --}}
                    <div class="info-box">
                        <table>
                            <tr>
                                <td>Nama Usaha</td>
                                <td>{{ $umkm->nama_usaha ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Jenis Usaha</td>
                                <td>{{ $umkm->jenis_usaha ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Kode UMKM</td>
                                <td>{{ $umkm->kode_umkm }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Credential --}}
                    <div class="credential-box">
                        <h3>🔐 Informasi Login Anda</h3>
                        <table>
                            <tr>
                                <td>Email</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td>Password</td>
                                <td>{{ $tempPassword }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- Peringatan password sementara --}}
                    <div class="warning-box">
                        <p>⚠️ <strong>Password di atas bersifat SEMENTARA.</strong> Anda <strong>wajib mengganti password</strong> saat pertama kali login demi keamanan akun Anda.</p>
                    </div>

                    {{-- Instruksi Login --}}
                    <p><strong>Cara Login:</strong></p>
                    <ol class="steps">
                        <li>Buka halaman login: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></li>
                        <li>Masukkan email dan password sementara di atas</li>
                        <li>Anda akan diminta mengganti password</li>
                        <li>Setelah itu, Anda bisa mengakses seluruh fitur MicroConnect</li>
                    </ol>

                    <p style="text-align: center; margin-top: 24px;">
                        <a href="{{ $loginUrl }}" class="btn-login">Login Sekarang</a>
                    </p>

                    <p style="margin-top: 24px;">Jika Anda memiliki pertanyaan, silakan hubungi Admin KADIN melalui fitur <strong>Pengaduan & Konsultasi</strong> di dalam sistem.</p>

                    <p>Salam hangat,<br><strong>Tim MicroConnect KADIN</strong></p>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>Email ini dikirim secara otomatis oleh sistem <strong>MicroConnect KADIN</strong>.</p>
                    <p>Mohon jangan membalas email ini.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
