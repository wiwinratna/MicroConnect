<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Ganti Password | Sistem KADIN</title>

    <link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: linear-gradient(145deg, #1e293b 0%, #0f172a 50%, #1e3a5f 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .change-pw-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 460px;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            padding: 28px 32px;
            text-align: center;
        }

        .card-header h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .card-header p {
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .card-body {
            padding: 32px;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.82rem;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            background: #f8fafc;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #f59e0b;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 13px 24px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
            margin-top: 8px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.45);
            transform: translateY(-1px);
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .change-pw-card {
            animation: fadeInUp 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <div class="change-pw-card">
        <div class="card-header">
            <h1>🔐 Ganti Password</h1>
            <p>Password Anda bersifat sementara dan harus diganti</p>
        </div>

        <div class="card-body">
            <div class="alert-warning">
                ⚠️ Akun Anda dibuat oleh Admin KADIN dengan password sementara.
                Demi keamanan, Anda <strong>wajib mengganti password</strong> sebelum menggunakan sistem.
            </div>

            @if($errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $e)
                        {{ $e }}<br>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.change.update') }}">
                @csrf

                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input class="form-input" type="password" name="password" required
                           placeholder="Minimal 6 karakter" minlength="6" autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input class="form-input" type="password" name="password_confirmation" required
                           placeholder="Ulangi password baru" minlength="6">
                </div>

                <button type="submit" class="btn-submit">
                    Ganti Password & Lanjutkan
                </button>
            </form>

            <div class="footer-text">
                © {{ date('Y') }} Sistem UMKM KADIN · MicroConnect
            </div>
        </div>
    </div>
</body>
</html>
