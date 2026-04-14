<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Login Sistem UMKM KADIN - Kelola usaha mikro kecil menengah Anda">
    <title>Login UMKM | Sistem KADIN</title>

    <link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@lottiefiles/lottie-player@2.0.8/dist/lottie-player.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; background: #fff; color: #1e293b; overflow-x: hidden; }

        .login-wrapper { display: flex; min-height: 100vh; }

        /* ============ LEFT PANEL (BRANDING) ============ */
        .login-left {
            flex: 0 0 45%;
            max-width: 550px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            overflow: hidden;
            border-right: 1px solid #e2e8f0;
        }
        
        .login-left::before {
            content: ''; position: absolute; top: -10%; left: -10%; width: 50%; height: 50%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.05) 0%, transparent 60%); border-radius: 50%; pointer-events: none;
        }

        .brand-logo { margin-bottom: 24px; text-align: center; position: relative; z-index: 2; }
        .brand-logo img { height: 45px; width: auto; object-fit: contain; }

        .lottie-container { width: 100%; max-width: 260px; margin: 0 auto; position: relative; z-index: 2; }

        .tagline { text-align: center; position: relative; z-index: 2; margin-top: 24px; }
        .tagline h2 { font-size: 1.25rem; font-weight: 600; color: #f8fafc; margin-bottom: 8px; letter-spacing: -0.02em; }
        .tagline p { font-size: 0.85rem; color: #94a3b8; max-width: 280px; margin: 0 auto; line-height: 1.5; }

        /* ============ RIGHT PANEL (FORM) ============ */
        .login-right {
            flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 40px; background: #fafafa;
        }

        .login-form-wrapper { width: 100%; max-width: 360px; }

        .form-header { margin-bottom: 28px; }
        .greeting-badge {
            display: inline-block; background: #e0e7ff; color: #4338ca; padding: 4px 10px; border-radius: 6px;
            font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;
        }
        .form-header h1 { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; letter-spacing: -0.02em; }
        .form-header p { color: #64748b; font-size: 0.85rem; }

        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 10px 14px; border-radius: 8px; font-size: 0.8rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 0.8rem; font-weight: 500; color: #475569; margin-bottom: 6px; }
        
        .form-input {
            width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem;
            color: #1e293b; background: #fff; transition: all 0.2s ease; outline: none; box-shadow: 0 1px 2px rgba(0,0,0,0.02);
        }
        .form-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1); }
        .form-input::placeholder { color: #94a3b8; }

        .password-wrapper { position: relative; }
        .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer; font-size: 0.8rem; font-weight: 500; transition: color 0.15s; }
        .toggle-pw:hover { color: #4338ca; }

        .form-options { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .remember-check { display: flex; align-items: center; gap: 6px; cursor: pointer; }
        .remember-check input[type="checkbox"] { width: 14px; height: 14px; accent-color: #6366f1; cursor: pointer; }
        .remember-check label { font-size: 0.8rem; color: #64748b; cursor: pointer; }

        .btn-login {
            width: 100%; padding: 11px 20px; background: #4f46e5; color: #fff; border: none; border-radius: 8px;
            font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2);
        }
        .btn-login:hover { background: #4338ca; box-shadow: 0 4px 8px rgba(79, 70, 229, 0.3); transform: translateY(-1px); }

        .register-link { text-align: center; margin-top: 20px; font-size: 0.8rem; color: #64748b; }
        .register-link a { color: #4f46e5; text-decoration: none; font-weight: 600; transition: color 0.15s; }
        .register-link a:hover { color: #3730a3; text-decoration: underline; }

        .footer-text { text-align: center; margin-top: 40px; font-size: 0.75rem; color: #94a3b8; }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 900px) {
            .login-wrapper { flex-direction: column; }
            .login-left { flex: none; width: 100%; max-width: 100%; padding: 40px 20px; border-right: none; border-bottom: 1px solid #e2e8f0; }
            .lottie-container { max-width: 200px; }
            .login-right { padding: 40px 20px; }
        }

        /* Animations */
        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .anim { animation: fadeUp 0.5s ease forwards; opacity: 0; }
        .d-1 { animation-delay: 0.1s; } .d-2 { animation-delay: 0.2s; } .d-3 { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        {{-- LEFT PANEL --}}
        <div class="login-left">
            <div class="brand-logo anim">
                <img src="{{ asset('assets/img/logo.png') }}" alt="MINECT Logo">
            </div>
            <div class="lottie-container anim d-1">
                <lottie-player src="{{ asset('assets/lottie/login-umkm.json') }}" background="transparent" speed="1" loop autoplay></lottie-player>
            </div>
            <div class="tagline anim d-2">
                <h2>Portal UMKM Binaan</h2>
                <p>Kelola pencatatan dan pelaporan usaha Anda dalam satu pintu yang terintegrasi.</p>
            </div>
        </div>

        {{-- RIGHT PANEL --}}
        <div class="login-right">
            <div class="login-form-wrapper">
                <div class="form-header anim">
                    <span class="greeting-badge">UMKM Access</span>
                    <h1>Masuk ke Akun</h1>
                    <p>Silakan login untuk mengelola bisnis Anda.</p>
                </div>

                @if($errors->any())
                    <div class="alert-error anim d-1">
                        ⚠️ {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ $formAction ?? route('umkm.login.process') }}">
                    @csrf
                    <div class="form-group anim d-1">
                        <label class="form-label">Email Anda</label>
                        <input class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="contoh@email.com" required autofocus />
                    </div>

                    <div class="form-group anim d-2">
                        <label class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input class="form-input" type="password" name="password" id="passwordInput" placeholder="Masukkan password" required />
                            <button type="button" class="toggle-pw" onclick="togglePassword()">Lihat</button>
                        </div>
                    </div>

                    <div class="form-options anim d-2">
                        <label class="remember-check">
                            <input type="checkbox" name="remember">
                            <span>Ingat login saya</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-login anim d-3">Masuk ke Sistem</button>
                </form>

                <div class="register-link anim d-3">
                    Belum mendaftar? <a href="{{ route('register') }}">Buat akun UMKM</a>
                </div>

                <div class="footer-text anim d-3">
                    © {{ date('Y') }} MINECT · MicroConnect
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePassword() {
        const pw = document.getElementById('passwordInput');
        const btn = pw.parentElement.querySelector('.toggle-pw');
        if (pw.type === 'password') { pw.type = 'text'; btn.textContent = 'Tutup'; } 
        else { pw.type = 'password'; btn.textContent = 'Lihat'; }
    }
    </script>
</body>
</html>
