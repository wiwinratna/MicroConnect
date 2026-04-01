<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Login Administrator KADIN - Panel Monitoring UMKM">
    <title>Login Admin | KADIN Panel</title>

    <link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@lottiefiles/lottie-player@2.0.8/dist/lottie-player.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: #0f172a;
            color: #e2e8f0;
        }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ============ LEFT PANEL ============ */
        .login-left {
            flex: 1;
            background: linear-gradient(160deg, #0f172a 0%, #1e293b 40%, #0c4a6e 100%);
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
            top: -25%;
            left: -20%;
            width: 65%;
            height: 65%;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.07) 0%, transparent 70%);
            border-radius: 50%;
        }

        .login-left::after {
            content: '';
            position: absolute;
            bottom: -20%;
            right: -15%;
            width: 55%;
            height: 55%;
            background: radial-gradient(circle, rgba(234, 179, 8, 0.05) 0%, transparent 70%);
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
            background: linear-gradient(135deg, #0ea5e9, #0369a1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
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
            color: #0ea5e9;
        }

        .brand-badge {
            display: inline-block;
            background: rgba(234, 179, 8, 0.15);
            border: 1px solid rgba(234, 179, 8, 0.3);
            color: #fbbf24;
            padding: 3px 12px;
            border-radius: 16px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .lottie-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 380px;
        }

        .tagline {
            position: relative;
            z-index: 1;
            text-align: center;
            margin-top: 12px;
        }

        .tagline h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .tagline p {
            font-size: 0.85rem;
            color: #64748b;
            max-width: 320px;
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
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.72rem;
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
            background: #1e293b;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 400px;
        }

        .form-header {
            margin-bottom: 32px;
        }

        .form-header .greeting {
            font-size: 0.8rem;
            color: #0ea5e9;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }

        .form-header h1 {
            font-size: 1.65rem;
            font-weight: 700;
            color: #f1f5f9;
            line-height: 1.3;
            margin-bottom: 6px;
        }

        .form-header p {
            color: #64748b;
            font-size: 0.9rem;
        }

        .alert-error {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.25);
            color: #fca5a5;
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
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #334155;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            color: #f1f5f9;
            background: #0f172a;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #0ea5e9;
            background: #0f172a;
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
        }

        .form-input::placeholder {
            color: #475569;
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
            color: #64748b;
            cursor: pointer;
            font-size: 0.82rem;
            font-family: 'Inter', sans-serif;
            transition: color 0.15s;
        }

        .toggle-pw:hover { color: #0ea5e9; }

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
            accent-color: #0ea5e9;
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
            background: linear-gradient(135deg, #0ea5e9, #0369a1);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #0284c7, #075985);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-top: 24px;
            font-size: 0.85rem;
            color: #64748b;
        }

        .register-link a {
            color: #0ea5e9;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.15s;
        }

        .register-link a:hover {
            color: #38bdf8;
            text-decoration: underline;
        }

        .security-note {
            text-align: center;
            margin-top: 28px;
            padding: 10px 16px;
            background: rgba(234, 179, 8, 0.06);
            border: 1px solid rgba(234, 179, 8, 0.12);
            border-radius: 8px;
            font-size: 0.72rem;
            color: #fbbf24;
            line-height: 1.4;
        }

        .footer-text {
            text-align: center;
            margin-top: 24px;
            font-size: 0.72rem;
            color: #475569;
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
                max-width: 240px;
            }

            .tagline h2 { font-size: 1.05rem; }
            .feature-pills { display: none; }

            .login-right {
                padding: 32px 24px 48px;
            }

            .form-header h1 { font-size: 1.35rem; }
        }

        @media (max-width: 480px) {
            .login-left { padding: 24px 20px 20px; }
            .login-right { padding: 28px 20px 40px; }
            .brand-name { font-size: 1.2rem; }
            .lottie-container { max-width: 180px; }
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
                    <img src="{{ asset('assets/img/logo.png') }}" alt="MicroConnect Logo" style="height: 65px; width: auto; object-fit: contain;">
                </div>
                <div><span class="brand-badge">🔒 Administrator Access</span></div>
            </div>

            <div class="lottie-container animate-in delay-2">
                <lottie-player
                    src="{{ asset('assets/lottie/login-admin.json') }}"
                    background="transparent"
                    speed="1"
                    loop
                    autoplay
                    style="width: 100%; height: auto;"
                ></lottie-player>
            </div>

            <div class="tagline animate-in delay-3">
                <h2>Dashboard Monitoring<br>UMKM Terintegrasi</h2>
                <p>Pantau performa, kesehatan, dan pertumbuhan seluruh UMKM binaan KADIN secara real-time.</p>
            </div>

            <div class="feature-pills animate-in delay-4">
                <span class="feature-pill">📊 Monitoring UMKM</span>
                <span class="feature-pill">🏥 Health Status</span>
                <span class="feature-pill">📈 Analisis Performa</span>
                <span class="feature-pill">⚙️ Kelola Level</span>
            </div>
        </div>

        {{-- ========== RIGHT: LOGIN FORM ========== --}}
        <div class="login-right">
            <div class="login-form-wrapper">

                <div class="form-header animate-in delay-2">
                    <div class="greeting">Panel Admin KADIN</div>
                    <h1>Login Administrator</h1>
                    <p>Masuk dengan akun admin untuk mengakses dashboard</p>
                </div>

                {{-- Error --}}
                @if($errors->any())
                    <div class="alert-error animate-in delay-3">
                        ⚠️ {{ $errors->first() }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert-error animate-in delay-3">
                        ⚠️ {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.process') }}">
                    @csrf

                    <div class="form-group animate-in delay-3">
                        <label class="form-label">Email Admin</label>
                        <input class="form-input"
                               type="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="admin@kadin.go.id"
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
                                   placeholder="Masukkan password admin"
                                   required />
                            <button type="button" class="toggle-pw" onclick="togglePassword()">Lihat</button>
                        </div>
                    </div>

                    <div class="form-options animate-in delay-4">
                        <div class="remember-check">
                            <input id="remember" type="checkbox" name="remember">
                            <label for="remember">Ingat sesi</label>
                        </div>
                    </div>

                    <button type="submit" class="btn-login animate-in delay-5">
                        Masuk ke Dashboard
                    </button>
                </form>

                <div class="register-link animate-in delay-5">
                    Belum punya akun admin?
                    <a href="{{ route('admin.register') }}">Daftar Admin</a>
                </div>

                <div class="security-note animate-in delay-5">
                    🔐 Halaman ini hanya untuk administrator KADIN yang berwenang.<br>
                    Segala aktivitas login tersimpan dalam sistem audit.
                </div>

                <div class="footer-text animate-in delay-5">
                    © {{ date('Y') }} KADIN Panel · MicroConnect Admin
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
