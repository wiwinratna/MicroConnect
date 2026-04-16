<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MINECT | Micro Connect - KADIN Bengkalis</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    
    <style>
        :root {
            --primary: #0284c7; 
            --primary-light: #e0f2fe; 
            --primary-dark: #0369a1;
            --text-main: #0f172a; 
            --text-muted: #475569;
            --bg-main: #ffffff; 
            --bg-alt: #f4f4f9; 
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-main); color: var(--text-main); line-height: 1.6; overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 0 5%; }
        
        /* Typography */
        h1, h2, h3 { font-weight: 700; letter-spacing: -0.02em; line-height: 1.25; }
        .section-title { font-size: clamp(1.75rem, 3.5vw, 2.15rem); text-align: center; margin-bottom: 0.75rem; color: var(--text-main); }
        .section-subtitle { text-align: center; color: var(--text-muted); font-size: 1rem; max-width: 700px; margin: 0 auto 3rem auto; }
        
        /* Utilities */
        .badge { display: inline-block; padding: 0.4rem 0.85rem; background: var(--primary-light); color: var(--primary-dark); border-radius: 50px; font-size: 0.85rem; font-weight: 600; margin-bottom: 1.25rem; }
        .text-primary { color: var(--primary); }
        
        /* Animations */
        .reveal { opacity: 0; transform: translateY(20px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal.active { opacity: 1; transform: translateY(0); }
        .delay-1 { transition-delay: 0.1s; } .delay-2 { transition-delay: 0.2s; }
        
        /* Navbar */
        .navbar { position: fixed; top: 0; left: 0; width: 100%; padding: 1rem 5%; display: flex; justify-content: space-between; align-items: center; z-index: 1000; background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); border-bottom: 1px solid transparent; transition: all 0.3s; }
        .navbar.scrolled { padding: 0.75rem 5%; border-bottom-color: var(--border); box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .nav-brand { font-size: 1.25rem; font-weight: 800; color: var(--primary-dark); display: flex; align-items: center; gap: 0.5rem; }
        .nav-links { display: flex; gap: 1.5rem; align-items: center; }
        .nav-link { font-size: 0.875rem; font-weight: 500; color: var(--text-muted); transition: color 0.3s; }
        .nav-link:hover { color: var(--primary); }
        
        /* Buttons */
        .btn { padding: 0.6rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s; cursor: pointer; border: none; }
        .btn-primary { background: var(--primary); color: white; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(2, 132, 199, 0.35); }
        .btn-outline { background: transparent; color: var(--primary); border: 1px solid var(--primary); }
        .btn-outline:hover { background: var(--primary-light); transform: translateY(-2px); }
        
        /* Hero Section */
        .hero { min-height: 80vh; display: flex; align-items: center; padding: 100px 0 40px; position: relative; overflow: hidden; }
        .hero::before { content: ''; position: absolute; top: -20%; right: -5%; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, var(--primary-light) 0%, transparent 70%); z-index: -1; opacity: 0.6; }
        .hero-inner { display: flex; align-items: center; justify-content: space-between; gap: 3rem; }
        .hero-content { flex: 1; max-width: 550px; }
        .hero-title { font-size: clamp(2rem, 3.8vw, 2.75rem); margin-bottom: 1.25rem; color: var(--text-main); }
        .hero-desc { font-size: 1rem; color: var(--text-muted); margin-bottom: 2rem; line-height: 1.6; }
        .hero-actions { display: flex; gap: 1rem; }
        
        .hero-visual { flex: 1; position: relative; width: 100%; max-width: 480px; }
        .video-container { position: relative; width: 100%; aspect-ratio: 16/9; border-radius: 12px; overflow: hidden; box-shadow: 0 20px 40px -10px rgba(2, 132, 199, 0.15); border: 1px solid var(--border); background: #ffffff; }
        .video-container video { width: 100%; height: 100%; object-fit: cover; }
        
        /* Sections Shared */
        section { padding: 70px 0; }
        .bg-alt { background-color: var(--bg-alt); }
        
        /* About Section */
        .about-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center; }
        .about-text p { font-size: 1rem; color: var(--text-muted); margin-bottom: 1.25rem; }
        .about-highlight { background: white; padding: 1.5rem; border-radius: 12px; border-left: 4px solid var(--primary); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        
        /* Problems Section */
        .problems-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; }
        .problem-card { background: white; padding: 1.75rem 1.5rem; border-radius: 14px; border: 1px solid var(--border); transition: all 0.3s; }
        .problem-card:hover { transform: translateY(-4px); box-shadow: 0 10px 30px rgba(0,0,0,0.05); border-color: var(--primary-light); }
        .problem-icon { width: 42px; height: 42px; background: #fee2e2; color: #ef4444; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; }
        .problem-card h3 { font-size: 1.05rem; margin-bottom: 0.5rem; }
        .problem-card p { font-size: 0.9rem; color: var(--text-muted); }
        
        /* Solution Section */
        .solution-box { background: var(--primary-dark); border-radius: 20px; padding: 3rem 2rem; color: white; text-align: center; position: relative; overflow: hidden; }
        .solution-box::after { content: ''; position: absolute; inset: 0; background: url('data:image/svg+xml;utf8,<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><circle cx="2" cy="2" r="2" fill="rgba(255,255,255,0.05)"/></svg>') repeat; z-index: 0; }
        .solution-content { position: relative; z-index: 1; }
        .solution-box h2 { font-size: clamp(1.75rem, 3vw, 2.15rem); margin-bottom: 1rem; }
        .solution-box p { font-size: 1.05rem; color: var(--primary-light); max-width: 800px; margin: 0 auto; line-height: 1.7; }
        
        /* Features Section */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .feature-card { background: white; padding: 1.75rem 1.5rem; border-radius: 14px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid transparent; transition: all 0.3s; }
        .feature-card:hover { border-color: var(--primary-light); box-shadow: 0 20px 40px -10px rgba(2, 132, 199, 0.1); transform: translateY(-4px); }
        .feature-icon { width: 42px; height: 42px; background: var(--primary-light); color: var(--primary-dark); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; }
        .feature-card h3 { font-size: 1.1rem; margin-bottom: 0.75rem; }
        .feature-card p { font-size: 0.9rem; color: var(--text-muted); }
        
        /* Workflow Section */
        .workflow-container { display: flex; justify-content: space-between; align-items: flex-start; position: relative; padding: 1.5rem 0; }
        .workflow-line { position: absolute; top: 30px; left: 5%; right: 5%; height: 2px; background: var(--primary-light); z-index: 0; }
        .workflow-step { flex: 1; text-align: center; position: relative; z-index: 1; padding: 0 0.5rem; }
        .workflow-step-icon { width: 60px; height: 60px; background: white; border: 2px solid var(--primary-light); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto; font-size: 1.25rem; font-weight: bold; transition: all 0.3s; }
        .workflow-step:hover .workflow-step-icon { background: var(--primary); color: white; border-color: var(--primary); transform: scale(1.05); box-shadow: 0 10px 20px rgba(2, 132, 199, 0.2); }
        .workflow-step h4 { font-size: 1rem; margin-bottom: 0.5rem; }
        .workflow-step p { font-size: 0.85rem; color: var(--text-muted); }
        
        @media (max-width: 768px) {
            .workflow-container { flex-direction: column; gap: 1.5rem; }
            .workflow-line { display: none; }
            .workflow-step { display: flex; align-items: center; text-align: left; gap: 1rem; width: 100%; }
            .workflow-step-icon { margin: 0; flex-shrink: 0; width: 50px; height: 50px; font-size: 1.1rem;}
        }
        
        /* Benefits Section */
        .benefits-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .benefit-panel { background: white; border-radius: 16px; border: 1px solid var(--border); overflow: hidden; }
        .benefit-header { padding: 1.25rem 1.5rem; background: var(--bg-alt); border-bottom: 1px solid var(--border); }
        .benefit-header h3 { font-size: 1.15rem; display: flex; align-items: center; gap: 0.75rem; }
        .benefit-body { padding: 1.5rem; }
        .benefit-list { list-style: none; }
        .benefit-list li { display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: flex-start; font-size: 0.95rem; }
        .benefit-list li i { color: var(--primary); flex-shrink: 0; margin-top: 0.15rem; width: 18px; height: 18px; }
        
        /* Preview Section */
        .preview-wrapper { max-width: 900px; margin: 0 auto; border-radius: 12px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1); border: 1px solid var(--border); background: #fff; }
        .preview-header { height: 28px; background: #f1f5f9; display: flex; align-items: center; padding: 0 1rem; gap: 6px; border-bottom: 1px solid var(--border); }
        .preview-dot { width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; }
        .preview-image { width: 100%; height: auto; display: block; background: #e2e8f0; max-height: 480px; object-fit: cover; object-position: top; }
        
        /* TOGAF Section */
        .togaf-section { background: white; padding: 2rem; border-radius: 16px; border: 1px dashed #cbd5e1; display: flex; gap: 1.5rem; align-items: center; }
        .togaf-icon { width: 56px; height: 56px; background: var(--bg-alt); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); flex-shrink: 0; }
        .togaf-content h3 { font-size: 1.1rem; margin-bottom: 0.5rem; }
        .togaf-content p { color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; }
        
        /* Footer/Closing */
        .closing { background: var(--text-main); color: white; padding: 60px 0; text-align: center; }
        .closing h2 { font-size: clamp(1.5rem, 3vw, 1.8rem); margin-bottom: 1rem; }
        .closing p { color: #94a3b8; max-width: 600px; margin: 0 auto 2rem auto; line-height: 1.7; font-size: 0.95rem; }
        .footer-bottom { padding: 1.25rem 5%; background: #0b1120; color: #64748b; text-align: center; font-size: 0.85rem; }

        @media (max-width: 992px) {
            .hero-inner { flex-direction: column; text-align: center; }
            .hero-content { align-items: center; display: flex; flex-direction: column; }
            .about-grid, .benefits-grid, .togaf-section { flex-direction: column; grid-template-columns: 1fr; }
            .hero-title { font-size: 2.8rem; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="nav-brand">
            <i data-feather="hexagon" class="text-primary"></i> MINECT
        </div>
        <div class="nav-links">
            <a href="#about" class="nav-link">Tentang</a>
            <a href="#fitur" class="nav-link">Fitur</a>
            <a href="#workflow" class="nav-link">Alur Kerja</a>
            <a href="{{ route('umkm.login') }}" class="btn btn-primary">Masuk Sistem</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero container" id="home">
        <div class="hero-inner">
            <div class="hero-content reveal">
                <span class="badge">Sistem Monitoring Terpadu</span>
                <h1 class="hero-title">Platform Pelaporan & Pemantauan <span class="text-primary">UMKM Binaan</span></h1>
                <p class="hero-desc">MINECT (Micro Connect) mengintegrasikan proses pendataan, pelaporan aktivitas usaha, dan evaluasi capaian UMKM binaan KADIN Bengkalis secara terpusat, real-time, dan akurat.</p>
                <div class="hero-actions">
                    <a href="#fitur" class="btn btn-primary">Lihat Fitur</a>
                    <a href="#about" class="btn btn-outline">Jelajahi Sistem</a>
                </div>
            </div>
            <div class="hero-visual reveal delay-1">
                <div class="video-container" style="position: relative;">
                    <!-- Sesuai permintaan: video logo MINECT -->
                    <video id="heroVideo" autoplay loop muted playsinline src="{{ asset('assets/video/promo-hero.mp4') }}"></video>
                    <button id="muteToggle" class="btn" style="position: absolute; bottom: 15px; right: 15px; background: rgba(255,255,255,0.85); border: 1px solid var(--border); padding: 0.5rem; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 10;">
                        <span id="iconMuted"><i data-feather="volume-x" class="text-primary"></i></span>
                        <span id="iconUnmuted" style="display: none;"><i data-feather="volume-2" class="text-primary"></i></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- About Section -->
    <section class="bg-alt" id="about">
        <div class="container about-grid">
            <div class="about-visual reveal">
                <div class="about-highlight">
                    <h3 style="margin-bottom: 1rem; color: var(--text-main);">Dirancang Khusus Untuk:</h3>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem; align-items: center;">
                        <div style="width: 40px; height: 40px; background: var(--primary-light); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary);"><i data-feather="users"></i></div>
                        <div>
                            <strong style="display: block;">KADIN Bengkalis</strong>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">Sebagai Instansi Pembina & Monitor</span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div style="width: 40px; height: 40px; background: var(--primary-light); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary);"><i data-feather="briefcase"></i></div>
                        <div>
                            <strong style="display: block;">UMKM Binaan</strong>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">Sebagai Pelaku Usaha Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="about-text reveal delay-1">
                <span class="badge">Tentang MINECT</span>
                <h2>Latar Belakang Sistem</h2>
                <br>
                <p>MINECT dikembangkan untuk menjembatani kesenjangan informasi antara instansi pembina dan pelaku UMKM. Sebelumnya, proses pendataan dan pelaporan UMKM bersifat konvensional, tidak terpusat, dan evaluasi menjadi kurang efisien.</p>
                <p>Kini, MINECT bertransformasi menjadi buku besar digital yang memastikan transparansi data operasional, inventori, hingga laporan keuangan, selaras dengan kebutuhan monitoring tingkat enterprise.</p>
            </div>
        </div>
    </section>

    <!-- Problems Section -->
    <section id="masalah">
        <div class="container">
            <div class="reveal">
                <h2 class="section-title">Permasalahan Tata Kelola Konvensional</h2>
                <p class="section-subtitle">Tantangan utama yang memicu kebutuhan akan digitalisasi sistem monitoring dan pelaporan terpusat.</p>
            </div>
            <div class="problems-grid">
                <div class="problem-card reveal">
                    <div class="problem-icon"><i data-feather="clock"></i></div>
                    <h3>Pelaporan Tidak Rutin</h3>
                    <p>UMKM kesulitan menyampaikan rekap laporan operasional secara berkala kepada pihak instansi pembina.</p>
                </div>
                <div class="problem-card reveal delay-1">
                    <div class="problem-icon"><i data-feather="layers"></i></div>
                    <h3>Data Tersebar</h3>
                    <p>Informasi keuangan dan stok barang masih dicatat secara manual di berbagai media yang rentan hilang.</p>
                </div>
                <div class="problem-card reveal delay-2">
                    <div class="problem-icon"><i data-feather="eye-off"></i></div>
                    <h3>Pemantauan Terpisah</h3>
                    <p>KADIN Bengkalis kesulitan melihat progres dan profil kesehatan usaha UMKM secara terpadu dan real-time.</p>
                </div>
                <div class="problem-card reveal delay-2">
                    <div class="problem-icon"><i data-feather="trending-down"></i></div>
                    <h3>Evaluasi Tidak Efisien</h3>
                    <p>Proses analisis perkembangan usaha memakan waktu lama karena data historis yang tidak terekam otomatis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Solution Section -->
    <section style="padding-top: 0;">
        <div class="container">
            <div class="solution-box reveal">
                <div class="solution-content">
                    <h2>Sentralisasi dengan MINECT</h2>
                    <p>Kami merestrukturisasi alur informasi melalui satu ekosistem cloud terpadu. MINECT memastikan setiap transaksi usaha, manajemen produk, hingga penyusunan laporan keuangan terotomatisasi—mendukung kemudahan rekapitulasi bagi UMKM dan keakuratan monitoring bagi KADIN Bengkalis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-alt" id="fitur">
        <div class="container">
            <div class="reveal">
                <span class="badge" style="display: block; width: fit-content; margin: 0 auto 1rem auto;">Modul Sistem</span>
                <h2 class="section-title">Fitur Penunjang Pengelolaan</h2>
                <p class="section-subtitle">Struktur instrumen yang disediakan oleh MINECT mewakili siklus bisnis fungsional.</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card reveal">
                    <div class="feature-icon"><i data-feather="database"></i></div>
                    <h3>Manajemen Data UMKM</h3>
                    <p>Penyimpanan profil entitas usaha secara mendetail, tingkat kelayakan, status keanggotaan, dan identifikasi kepemilikan.</p>
                </div>
                <div class="feature-card reveal delay-1">
                    <div class="feature-icon"><i data-feather="file-text"></i></div>
                    <h3>Pelaporan Usaha</h3>
                    <p>Kemudahan pengiriman rekap progres usaha bulanan kepada instansi terkait dengan format yang diakui standar pembukuan.</p>
                </div>
                <div class="feature-card reveal delay-2">
                    <div class="feature-icon"><i data-feather="shopping-cart"></i></div>
                    <h3>Transaksi Jual & Beli</h3>
                    <p>Pencatatan langsung (Point of Sale/E-Receipt) atas penjualan maupun kulakan bahan baku, terhubung langsung ke arus kas.</p>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon"><i data-feather="box"></i></div>
                    <h3>Pengelolaan Persediaan</h3>
                    <p>Sistem pencatatan kartu persediaan barang secara otomatis menggunakan model akuntansi inventori (FIFO/Average) yang akurat.</p>
                </div>
                <div class="feature-card reveal delay-1">
                    <div class="feature-icon"><i data-feather="clipboard"></i></div>
                    <h3>Pencatatan Beban</h3>
                    <p>Otomasi pendataan pengeluaran operasional di luar HPP untuk memastikan perhitungan profit margin tanpa distorsi data.</p>
                </div>
                <div class="feature-card reveal delay-2">
                    <div class="feature-icon"><i data-feather="bar-chart-2"></i></div>
                    <h3>Dashboard Monitoring</h3>
                    <p>Akses pemantauan indikator kinerja utama (KPI), status kesehatan omzet, dan grafik transaksi real-time secara komprehensif.</p>
                </div>
                <div class="feature-card reveal">
                    <div class="feature-icon"><i data-feather="pie-chart"></i></div>
                    <h3>Laporan Keuangan</h3>
                    <p>Penarikan data laporan laba rugi, jurnal umum, hingga ringkasan arus modal untuk analisis fundamental secara praktis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Workflow Section -->
    <section id="workflow">
        <div class="container">
            <h2 class="section-title reveal">Alur Kerja Sistem Integrasi</h2>
            <p class="section-subtitle reveal">Proses ujung-ke-ujung (end-to-end) pengelolaan informasi pada platform MINECT.</p>
            
            <div class="workflow-container reveal delay-1">
                <div class="workflow-line"></div>
                <div class="workflow-step">
                    <div class="workflow-step-icon">1</div>
                    <h4>Pendataan</h4>
                    <p>Registrasi & pemetaan UMKM baru</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-step-icon">2</div>
                    <h4>Input Transaksi</h4>
                    <p>Rekam aktivitas jual-beli usaha</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-step-icon">3</div>
                    <h4>Integrasi Pelaporan</h4>
                    <p>Otomasi data transaksi menjadi report</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-step-icon">4</div>
                    <h4>Pengolahan Sistem</h4>
                    <p>Sistem menghitung rasio dan profil inventori</p>
                </div>
                <div class="workflow-step">
                    <div class="workflow-step-icon">5</div>
                    <h4>Evaluasi Pusat</h4>
                    <p>Monitoring & pembinaan KADIN secara tajam</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="bg-alt" id="manfaat">
        <div class="container">
            <div class="reveal">
                <h2 class="section-title">Nilai Tambah Implementasi</h2>
                <p class="section-subtitle">Keuntungan strategis dari sistem berbasis data.</p>
            </div>
            
            <div class="benefits-grid">
                <!-- Benefit UMKM -->
                <div class="benefit-panel reveal">
                    <div class="benefit-header">
                        <h3><i data-feather="award" class="text-primary"></i> Bagi UMKM Binaan</h3>
                    </div>
                    <div class="benefit-body">
                        <ul class="benefit-list">
                            <li><i data-feather="check-circle"></i> <span><strong>Proses Pelaporan Praktis:</strong> Mengeliminasi pelaporan manual dan rekap kertas.</span></li>
                            <li><i data-feather="check-circle"></i> <span><strong>Keteraturan Pendataan:</strong> Semua historis transaksi, utang piutang, arsip stok terjaga aman di cloud.</span></li>
                            <li><i data-feather="check-circle"></i> <span><strong>Evaluasi Mandiri:</strong> Fitur jurnal memungkinkan UMKM paham omzet dan laba bersih secara pasti.</span></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Benefit KADIN -->
                <div class="benefit-panel reveal delay-1">
                    <div class="benefit-header">
                        <h3><i data-feather="shield" class="text-primary"></i> Bagi KADIN Bengkalis</h3>
                    </div>
                    <div class="benefit-body">
                        <ul class="benefit-list">
                            <li><i data-feather="check-circle"></i> <span><strong>Pusat Kontrol Terpadu:</strong> Akses data populasi UMKM secara makro melalui antarmuka web representatif.</span></li>
                            <li><i data-feather="check-circle"></i> <span><strong>Pengambilan Keputusan Cepat:</strong> Analisis visual KPI mempermudah pendistribusian program bantuan secara tepat sasaran.</span></li>
                            <li><i data-feather="check-circle"></i> <span><strong>Pemantauan Keberlanjutan:</strong> Validasi perkembangan status usaha UMKM dari level Dasar ke Lanjut secara terstruktur.</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Preview / Screenshot Section -->
    <section id="preview">
        <div class="container text-center reveal">
            <h2 class="section-title">Antarmuka Pemantauan Cerdas</h2>
            <p class="section-subtitle">Gambaran platform internal MINECT yang dirancang minimalis demi kenyamanan manajemen UMKM harian.</p>
            
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-dot" style="background:#fca5a5;"></div>
                    <div class="preview-dot" style="background:#fde047;"></div>
                    <div class="preview-dot" style="background:#86efac;"></div>
                </div>
                <!-- Gunakan asset gambar screenshot jika sudah ada. Sebaiknya ukuran direkayasa.  -->
                <img src="{{ asset('assets/img/dashboard-preview.jpg') }}" alt="MINECT Dashboard Preview" class="preview-image" onerror="this.src='https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1200&q=80'">
            </div>
            
            <div class="togaf-section mt-5" style="margin-top: 4rem; text-align: left;">
                <div class="togaf-icon">
                    <i data-feather="cpu"></i>
                </div>
                <div class="togaf-content">
                    <h3>Pendekatan Perancangan ADM TOGAF</h3>
                    <p>Arsitektur informasi MINECT disusun berdasarkan kerangka kerja <strong>TOGAF ADM</strong> (The Open Group Architecture Framework). Hal ini menjamin setiap entitas data, logika aplikasi, dan landasan teknologi beroperasi selaras, memberikan skalabilitas nyata untuk menjawab dinamika kebutuhan bisnis KADIN di masa mendatang.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Closing Section -->
    <section class="closing">
        <div class="container reveal">
            <h2>Lebih Efektif, Terintegrasi, dan Berkelanjutan.</h2>
            <p>MINECT hadir sebagai fondasi digital untuk mewujudkan tata kelola bisnis kecil dan menengah yang transparan. Bersama membangun pertumbuhan kewirausahaan di Bengkalis.</p>
            <a href="{{ route('umkm.login') }}" class="btn" style="background: white; color: var(--text-main); padding: 1rem 2rem; font-size: 1.1rem; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                Masuk ke Platform <i data-feather="arrow-right"></i>
            </a>
        </div>
    </section>

    <div class="footer-bottom">
        &copy; {{ date('Y') }} Sistem Operasional MINECT - Terintegrasi Jaringan KADIN Bengkalis.
    </div>

    <script>
        // Initialize Icons
        feather.replace();

        // Navbar Scroll Effect
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                document.getElementById('navbar').classList.add('scrolled');
            } else {
                document.getElementById('navbar').classList.remove('scrolled');
            }
        });

        // Intersection Observer for Reveal Animations
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.15
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.reveal').forEach(el => {
            observer.observe(el);
        });

        // Video Sound Toggle
        const heroVideo = document.getElementById('heroVideo');
        const muteToggle = document.getElementById('muteToggle');
        const iconMuted = document.getElementById('iconMuted');
        const iconUnmuted = document.getElementById('iconUnmuted');
        
        if(heroVideo && muteToggle) {
            muteToggle.addEventListener('click', () => {
                heroVideo.muted = !heroVideo.muted;
                if (heroVideo.muted) {
                    iconMuted.style.display = 'block';
                    iconUnmuted.style.display = 'none';
                } else {
                    iconMuted.style.display = 'none';
                    iconUnmuted.style.display = 'block';
                }
            });
        }
    </script>
</body>
</html>
