<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Login Sistem UMKM KADIN - Kelola usaha mikro kecil menengah Anda">
    <title>Login UMKM | Sistem KADIN</title>

    <link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@lottiefiles/lottie-player@2.0.8/dist/lottie-player.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: #f0f2f5;
            color: #1a1a2e;
        }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ============ LEFT PANEL ============ */
        .login-left {
            flex: 1;
            background: linear-gradient(145deg, #1e293b 0%, #0f172a 50%, #1e3a5f 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -20%;
            width: 60%;
            height: 60%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.08) 0%, transparent 70%);
            border-radius: 50%;
        }

        .login-left::after {
            content: '';
            position: absolute;
            bottom: -15%;
            left: -15%;
            width: 50%;
            height: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.06) 0%, transparent 70%);
            border-radius: 50%;
        }

        .brand-section {
            text-align: center;
            position: relative;
            z-index: 1;
            margin-bottom: 8px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #38bdf8, #6366f1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -1px;
        }

        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #e2e8f0;
            letter-spacing: -0.5px;
        }

        .brand-name span {
            color: #38bdf8;
        }

        .lottie-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
        }

        .tagline {
            position: relative;
            z-index: 1;
            text-align: center;
            margin-top: 12px;
        }

        .tagline h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .tagline p {
            font-size: 0.88rem;
            color: #64748b;
            max-width: 340px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .feature-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        .feature-pill {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            color: #94a3b8;
            backdrop-filter: blur(4px);
        }

        /* ============ RIGHT PANEL ============ */
        .login-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 48px 40px;
            background: #fff;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-header .greeting {
            font-size: 0.85rem;
            color: #6366f1;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }

        .form-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .form-header p {
            color: #64748b;
            font-size: 0.92rem;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
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
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 0.85rem;
            font-family: 'Inter', sans-serif;
            transition: color 0.15s;
        }

        .toggle-pw:hover { color: #6366f1; }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .remember-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #6366f1;
            cursor: pointer;
        }

        .remember-check label {
            font-size: 0.82rem;
            color: #64748b;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 13px 24px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.45);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 24px;
            font-size: 0.88rem;
            color: #64748b;
        }

        .register-link a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.15s;
        }

        .register-link a:hover {
            color: #4338ca;
            text-decoration: underline;
        }

        .footer-text {
            text-align: center;
            margin-top: 32px;
            font-size: 0.75rem;
            color: #cbd5e1;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 900px) {
            .login-wrapper {
                flex-direction: column;
            }

            .login-left {
                padding: 32px 24px 24px;
                min-height: auto;
            }

            .lottie-container {
                max-width: 260px;
            }

            .tagline h2 { font-size: 1.1rem; }

            .feature-pills { display: none; }

            .login-right {
                padding: 32px 24px 48px;
            }

            .form-header h1 { font-size: 1.4rem; }
        }

        @media (max-width: 480px) {
            .login-left { padding: 24px 20px 20px; }
            .login-right { padding: 28px 20px 40px; }
            .brand-name { font-size: 1.2rem; }
            .lottie-container { max-width: 200px; }
        }

        /* ============ ANIMATIONS ============ */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
    </style>
</head>

<body>
    <div class="login-wrapper">

        {{-- ========== LEFT: BRANDING + LOTTIE ========== --}}
        <div class="login-left">
            <div class="brand-section animate-in delay-1">
                <div class="brand-logo">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="MicroConnect Logo" style="height: 65px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3));">
                </div>
            </div>

            <div class="lottie-container animate-in delay-2">
                <lottie-player
                    src="{{ asset('assets/lottie/login-umkm.json') }}"
                    background="transparent"
                    speed="1"
                    loop
                    autoplay
                    style="width: 100%; height: auto;"
                ></lottie-player>
            </div>

            <div class="tagline animate-in delay-3">
                <h2>Kelola Usahamu<br>Lebih Mudah & Terstruktur</h2>
                <p>Sistem manajemen UMKM terintegrasi untuk pencatatan keuangan, inventori, dan pelaporan yang akurat.</p>
            </div>

            <div class="feature-pills animate-in delay-4">
                <span class="feature-pill">📊 Laporan Keuangan</span>
                <span class="feature-pill">📦 Manajemen Stok</span>
                <span class="feature-pill">💳 Pencatatan Penjualan</span>
                <span class="feature-pill">📈 Analisis UMKM</span>
            </div>
        </div>

        {{-- ========== RIGHT: LOGIN FORM ========== --}}
        <div class="login-right">
            <div class="login-form-wrapper">

                <div class="form-header animate-in delay-2">
                    <div class="greeting">Portal UMKM</div>
                    <h1>Selamat Datang Kembali</h1>
                    <p>Masuk ke akun UMKM Anda untuk melanjutkan</p>
                </div>

                {{-- Error --}}
                @if($errors->any())
                    <div class="alert-error animate-in delay-3">
                        ⚠️ {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ $formAction ?? route('umkm.login.process') }}">
                    @csrf

                    <div class="form-group animate-in delay-3">
                        <label class="form-label">Email</label>
                        <input class="form-input"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="nama@email.com"
                               required
                               autofocus />
                    </div>

                    <div class="form-group animate-in delay-4">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input class="form-input"
                                   type="password"
                                   name="password"
                                   id="passwordInput"
                                   placeholder="Masukkan password Anda"
                                   required />
                            <button type="button" class="toggle-pw" onclick="togglePassword()">Lihat</button>
                        </div>
                    </div>

                    <div class="form-options animate-in delay-4">
                        <div class="remember-check">
                            <input id="remember" type="checkbox" name="remember">
                            <label for="remember">Ingat saya</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-login animate-in delay-5">
                        Masuk
                    </button>
                </form>

                <div class="register-link animate-in delay-5">
                    Belum punya akun?
                    <a href="{{ route('register') }}">Daftar sekarang</a>
                </div>

                <div class="footer-text animate-in delay-5">
                    © {{ date('Y') }} Sistem UMKM KADIN · MicroConnect
                </div>
            </div>
        </div>

    </div>

    <script>
    function togglePassword() {
        const pw = document.getElementById('passwordInput');
        const btn = pw.parentElement.querySelector('.toggle-pw');
        if (pw.type === 'password') {
            pw.type = 'text';
            btn.textContent = 'Tutup';
        } else {
            pw.type = 'password';
            btn.textContent = 'Lihat';
        }
    }
    </script>
</body>
</html>
