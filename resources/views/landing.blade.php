<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MicroConnect | Bangun UMKM Indonesia</title>

    <link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@2.0.8/dist/lottie-player.js"></script>

    <style>
        :root {
            --primary: #0284c7; --primary-light: #38bdf8; --secondary: #10b981;
            --bg-main: #ffffff; --bg-alt: #f8fafc; --text-main: #0f172a; --text-muted: #64748b;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-main); color: var(--text-main); line-height: 1.6; overflow-x: hidden; }
        a { text-decoration: none; }

        @keyframes blobBounce {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .delay-1 { transition-delay: 0.1s; } .delay-2 { transition-delay: 0.2s; }

        .navbar {
            position: fixed; top: 0; width: 100%; padding: 20px 5%;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 1000; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(16px);
            transition: all 0.3s ease; border-bottom: 1px solid transparent;
        }
        .navbar.scrolled { padding: 15px 5%; background: rgba(255, 255, 255, 0.95); border-bottom: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .nav-brand img { height: 55px; width: auto; object-fit: contain; transition: transform 0.3s; }
        .nav-brand:hover img { transform: scale(1.05); }
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-links a.link { color: var(--text-muted); font-weight: 600; font-size: 0.95rem; transition: color 0.3s; position: relative; }
        .nav-links a.link:hover { color: var(--primary); }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-light), var(--primary)); color: #fff;
            padding: 12px 28px; border-radius: 12px; font-weight: 600; font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.25); display: inline-flex; align-items: center; gap: 8px;
            transition: all 0.3s;
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(2, 132, 199, 0.4); color: #fff; }

        .hero { position: relative; display: flex; align-items: center; min-height: 100vh; padding: 120px 5% 60px; }
        .hero-blob { position: absolute; filter: blur(80px); z-index: 0; opacity: 0.5; border-radius: 50%; animation: blobBounce 20s infinite alternate; pointer-events: none; }
        .blob-1 { top: -10%; right: -5%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(56, 189, 248, 0.4) 0%, transparent 70%); animation-delay: 0s; }
        .blob-2 { bottom: 10%; right: 20%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%); animation-delay: -5s; }
        .blob-3 { top: 20%; left: -10%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(2, 132, 199, 0.2) 0%, transparent 70%); animation-delay: -10s; }

        .hero-container { display: flex; align-items: center; justify-content: space-between; gap: 60px; width: 100%; max-width: 1400px; margin: 0 auto; position: relative; z-index: 10; }
        .hero-content { flex: 1; max-width: 650px; text-align: left; }
        
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.8); border: 1px solid rgba(2,132,199,0.15);
            padding: 8px 20px; border-radius: 30px; font-size: 0.85rem; font-weight: 700;
            color: var(--primary); text-transform: uppercase; margin-bottom: 24px; box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            backdrop-filter: blur(8px);
        }
        .hero-title { font-size: 3.8rem; font-weight: 800; line-height: 1.15; margin-bottom: 24px; color: var(--text-main); letter-spacing: -1.5px; }
        .hero-title span { background: linear-gradient(to right, #0284c7, #38bdf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-desc { font-size: 1.25rem; color: var(--text-muted); margin-bottom: 40px; line-height: 1.7; font-weight: 400; }
        
        .hero-btns { display: flex; gap: 16px; align-items: center; }
        .btn-outline {
            background: rgba(255,255,255,0.8); color: var(--text-main); padding: 12px 28px; border-radius: 12px; font-weight: 600;
            font-size: 0.95rem; border: 1px solid #cbd5e1; transition: all 0.3s ease; backdrop-filter: blur(8px);
        }
        .btn-outline:hover { background: #fff; border-color: var(--primary); transform: translateY(-3px); box-shadow: 0 8px 15px rgba(15,23,42,0.05); }

        .hero-visual { flex: 1; position: relative; display: flex; justify-content: center; animation: float 6s ease-in-out infinite; z-index: 50; }
        
        .video-wrapper { position: relative; width: 100%; max-width: 650px; aspect-ratio: 16 / 9; }
        /* Removed border-radius and box-shadow to restore seamless white background blending */
        .video-wrapper video { width: 100%; height: 100%; object-fit: cover; background: transparent; }
        
        .unmute-btn {
            position: absolute; bottom: 10%; right: 10%; z-index: 100; background: rgba(255,255,255,0.95);
            color: var(--primary); width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 10px 25px rgba(0,0,0,0.15); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); border: 2px solid var(--primary-light); outline: none;
        }
        .unmute-btn:hover { transform: scale(1.1); color: var(--primary-light); }

        .features { padding: 140px 5%; background: var(--bg-alt); position: relative; background-image: radial-gradient(rgba(15, 23, 42, 0.04) 1px, transparent 1px); background-size: 28px 28px; }
        .section-header { text-align: center; margin-bottom: 80px; }
        .section-header h2 { font-size: 2.85rem; font-weight: 800; margin-bottom: 24px; color: var(--text-main); letter-spacing: -1.2px; }
        .section-header p { color: var(--text-muted); max-width: 650px; margin: 0 auto; font-size: 1.15rem; line-height: 1.8; }

        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px; max-width: 1200px; margin: 0 auto; }
        .feature-card {
            background: linear-gradient(180deg, #ffffff 0%, rgba(255, 255, 255, 0.6) 100%);
            backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.8);
            padding: 48px 36px; border-radius: 32px; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative; overflow: hidden; z-index: 1;
            box-shadow: 0 10px 40px -10px rgba(15, 23, 42, 0.04), inset 0 2px 0 rgba(255,255,255,1);
            display: flex; flex-direction: column;
        }
        
        .feature-card::before {
            content: ''; position: absolute; bottom: -30px; right: -30px; width: 140px; height: 140px; border-radius: 50%;
            background: radial-gradient(circle, rgba(56,189,248,0.15) 0%, transparent 70%); transition: transform 0.5s; z-index: -1;
        }
        
        .feature-card:hover { transform: translateY(-12px); background: #ffffff; border-color: rgba(2, 132, 199, 0.15); box-shadow: 0 25px 50px -12px rgba(2, 132, 199, 0.15), inset 0 2px 0 rgba(255,255,255,1); }
        .feature-card:hover::before { transform: scale(1.5); }

        .feature-icon-wrapper { width: 72px; height: 72px; margin-bottom: 32px; position: relative; }
        .feature-icon-bg { position: absolute; inset: 0; border-radius: 22px; background: linear-gradient(135deg, var(--primary-light), var(--primary)); opacity: 0.1; transform: rotate(-5deg); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        .feature-card:hover .feature-icon-bg { transform: rotate(0deg) scale(1.15); opacity: 0.15; }
        .feature-icon { position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--primary); z-index: 1; transition: transform 0.4s; }
        .feature-card:hover .feature-icon { transform: scale(1.1); color: var(--primary-dark); }
        
        .feature-card h3 { font-size: 1.35rem; font-weight: 800; margin-bottom: 16px; color: var(--text-main); letter-spacing: -0.5px; }
        .feature-card p { color: var(--text-muted); font-size: 1.05rem; line-height: 1.6; }

        .cta { padding: 120px 5%; background: var(--bg-main); background-image: radial-gradient(rgba(15, 23, 42, 0.03) 1px, transparent 1px); background-size: 24px 24px; }
        .cta-box {
            max-width: 1200px; margin: 0 auto; background: linear-gradient(135deg, #0f172a, #1e293b);
            border-radius: 32px; padding: 60px 50px; position: relative; overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            display: flex; align-items: center; justify-content: space-between; gap: 40px;
        }
        .cta-content { position: relative; z-index: 2; flex: 1; text-align: left; }
        .cta-animation { flex: 1; display: flex; justify-content: center; position: relative; z-index: 2; }
        .cta-content h2 { font-size: 3rem; font-weight: 800; margin-bottom: 24px; color: #ffffff; letter-spacing: -1px; }
        .cta-content h2 span { color: var(--primary-light); }
        .cta-content p { font-size: 1.15rem; color: #cbd5e1; margin-bottom: 48px; max-width: 500px; margin-left: 0; line-height: 1.7; }
        .btn-white {
            background: #ffffff; color: #0f172a; padding: 18px 46px; border-radius: 12px; font-weight: 700;
            font-size: 1.05rem; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s;
        }
        .btn-white:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(255,255,255,0.2); color: var(--primary); }

        footer { padding: 40px 5%; border-top: 1px solid var(--border-color); text-align: center; color: var(--text-muted); font-size: 0.95rem; background: var(--bg-alt); font-weight: 500; }

        @media (max-width: 992px) {
            .hero-container { flex-direction: column; text-align: center; }
            .hero-content { align-items: center; }
            .hero-title { font-size: 3rem; }
            .hero-btns { justify-content: center; }
            .nav-links { display: none; }
            .cta-box { flex-direction: column; text-align: center; }
            .cta-content { text-align: center; }
            .cta-content p { margin-left: auto; margin-right: auto; }
        }
        @media (max-width: 576px) {
            .hero { padding: 120px 5% 60px; }
            .hero-title { font-size: 2.5rem; }
            .hero-btns { flex-direction: column; width: 100%; }
            .btn-primary, .btn-outline { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="#" class="nav-brand">
            <img src="{{ asset('assets/img/logo.png') }}" alt="MicroConnect Logo">
        </a>
        <div class="nav-links">
            <a href="#features" class="link">Fitur Unggulan</a>
            <a href="#promo" class="link">Portal UMKM</a>
            <a href="{{ route('umkm.login') }}" class="btn-primary">
                Masuk Sistem <i data-feather="arrow-right" style="width: 18px;"></i>
            </a>
        </div>
    </nav>

    <section class="hero" id="home">
        <div class="hero-blob blob-1"></div>
        <div class="hero-blob blob-2"></div>
        <div class="hero-blob blob-3"></div>

        <div class="hero-container">
            <div class="hero-content reveal">
                <div class="hero-badge">
                    <i data-feather="zap" style="width: 16px; height: 16px;"></i> Platform UMKM Modern
                </div>
                <h1 class="hero-title">Tingkatkan Bisnis UMKM Anda <span>Lebih Cepat</span></h1>
                <p class="hero-desc">Satu sistem cerdas yang mengintegrasikan pencatatan keuangan, manajemen gudang bahan baku hingga kasir elektronik (POS).</p>
                <div class="hero-btns">
                    <a href="{{ route('umkm.login') }}" class="btn-primary">
                        Mulai Sekarang
                    </a>
                    <a href="#features" class="btn-outline">
                        Eksplorasi Fitur
                    </a>
                </div>
            </div>

            <div class="hero-visual reveal fade-in-right">
                <div class="video-wrapper">
                    <video id="promoVideo" src="{{ asset('assets/video/promo-hero.mp4') }}" autoplay loop muted playsinline></video>
                    <button class="unmute-btn" id="unmuteBtn" title="Unmute Video">
                        <i data-feather="volume-x"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="section-header reveal">
            <span style="display: inline-block; padding: 8px 24px; background: rgba(56, 189, 248, 0.1); color: var(--primary-dark); border-radius: 30px; font-size: 0.9rem; font-weight: 800; text-transform: uppercase; margin-bottom: 24px; letter-spacing: 1.5px; border: 1px solid rgba(56, 189, 248, 0.2);">⚙️ Modul Terintegrasi</span>
            <h2>Fitur Unggulan Sistem Cloud</h2>
            <p>Instrumen yang sangat kuat untuk tata kelola bisnis UMKM yang lebih profesional, akurat, dan terstruktur.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card reveal delay-1">
                <div class="feature-icon-wrapper"><div class="feature-icon-bg"></div><div class="feature-icon"><i data-feather="box"></i></div></div>
                <h3>Manajemen Inventori Cerdas</h3>
                <p>Mendukung metode tercatat FIFO/Average untuk kontrol akurat atas sirkulasi stok, dari bahan mentah hingga produk jadi komersial.</p>
            </div>
            <div class="feature-card reveal delay-2">
                <div class="feature-icon-wrapper"><div class="feature-icon-bg"></div><div class="feature-icon"><i data-feather="bar-chart-2"></i></div></div>
                <h3>Laporan Akuntansi Otomatis</h3>
                <p>Hasilkan jurnal umum, ledger, sampai laporan Laba Rugi yang presisi tanpa menyita waktu. Semua transaksi terekam real-time.</p>
            </div>
            <div class="feature-card reveal delay-1">
                <div class="feature-icon-wrapper"><div class="feature-icon-bg"></div><div class="feature-icon"><i data-feather="monitor"></i></div></div>
                <h3>Point of Sale (Etalase Kasir)</h3>
                <p>Terima pembayaran harian lewat dashboard interaktif layaknya ritel modern, lengkapi dengan kalkulasi sisa stok live dan ringkasan kas.</p>
            </div>
            <div class="feature-card reveal delay-2">
                <div class="feature-icon-wrapper"><div class="feature-icon-bg"></div><div class="feature-icon"><i data-feather="target"></i></div></div>
                <h3>Simulasi Biaya & Pemetaan HPP</h3>
                <p>Tekan kerugian melalui modul simulasi harga produksi sebelum meluncurkan produk, amankan margin bersih di titik teraman.</p>
            </div>
        </div>
    </section>

    <section class="cta" id="promo">
        <div class="cta-box reveal">
            <div class="cta-content">
                <h2>Siap Mengelola Secara <span>Profesional?</span></h2>
                <p>Bergabunglah dengan ratusan wirausaha lain dalam ekosistem tersertifikasi untuk pendataan terpadu dan digitalisasi operasional tanpa ribet.</p>
                <a href="{{ route('umkm.login') }}" class="btn-white">
                    Masuk ke Platform <i data-feather="arrow-up-right"></i>
                </a>
            </div>
            <div class="cta-animation">
                <lottie-player
                    src="{{ asset('assets/lottie/login-umkm.json') }}"
                    background="transparent"
                    speed="1"
                    loop
                    autoplay
                    style="width: 100%; max-width: 450px; height: auto; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.3));"
                ></lottie-player>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; {{ date('Y') }} Sistem Operasional UMKM - Terintegrasi Jaringan KADIN.</p>
    </footer>

    <script>
        feather.replace();

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) document.getElementById('navbar').classList.add('scrolled');
            else document.getElementById('navbar').classList.remove('scrolled');
        });

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // Video Audio Toggle
        const video = document.getElementById('promoVideo');
        const unmuteBtn = document.getElementById('unmuteBtn');
        unmuteBtn.addEventListener('click', () => {
            video.muted = !video.muted;
            unmuteBtn.querySelector('i').setAttribute('data-feather', video.muted ? 'volume-x' : 'volume-2');
            feather.replace();
        });
    </script>
</body>
</html>
