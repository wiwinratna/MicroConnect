<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MINECT | Micro Connect - KADIN Bengkalis</title>

    <link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/minect-theme/theme.css') }}" rel="stylesheet">
    <link href="{{ asset('css/landing.css') }}" rel="stylesheet">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar" id="navbar">
        <div class="container nav-inner">
            <a class="nav-brand" href="#home"><img src="{{ asset('assets/img/logo.png') }}" alt="MINECT"></a>
            <button type="button" class="nav-toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false" aria-controls="navLinks">
                <i data-lucide="menu"></i>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="#about" class="nav-link">Tentang</a>
                <a href="#fitur" class="nav-link">Fitur</a>
                <a href="#workflow" class="nav-link">Alur Kerja</a>
                <a href="{{ route('umkm.login') }}" class="btn btn-primary">Masuk Sistem</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero" id="home">
        <div class="container hero-inner">
            <div class="reveal">
                <span class="badge">Sistem Monitoring Terpadu</span>
                <h1 class="hero-title">Platform Pelaporan &amp; Pemantauan <span class="text-primary">UMKM Binaan</span></h1>
                <p class="hero-desc">MINECT (Micro Connect) mengintegrasikan proses pendataan, pelaporan aktivitas usaha, dan evaluasi capaian UMKM binaan KADIN Bengkalis secara terpusat, real-time, dan akurat.</p>
                <div class="hero-actions">
                    <a href="#fitur" class="btn btn-primary">Lihat Fitur</a>
                    <a href="#about" class="btn btn-outline">Jelajahi Sistem</a>
                </div>
            </div>
            <div class="preview-frame reveal delay-1">
                <div class="preview-bar"><span></span><span></span><span></span></div>
                <img src="{{ asset('assets/img/dashboard-preview.jpg') }}" alt="Tampilan dashboard UMKM di MINECT dengan data contoh">
            </div>
        </div>
    </header>

    <!-- About Section -->
    <section class="bg-alt" id="about">
        <div class="container about-grid">
            <div class="reveal">
                <div class="video-container">
                    <video id="heroVideo" autoplay loop muted playsinline src="{{ asset('assets/video/promo-hero.mp4') }}"></video>
                    <button type="button" id="muteToggle" class="mute-toggle" aria-label="Suara video">
                        <span id="iconMuted"><i data-lucide="volume-x"></i></span>
                        <span id="iconUnmuted" hidden><i data-lucide="volume-2"></i></span>
                    </button>
                </div>
                <div class="info-card audience">
                    <h3>Dirancang Khusus Untuk:</h3>
                    <div class="audience-row">
                        <div class="icon-box"><i data-lucide="users"></i></div>
                        <div><strong>KADIN Bengkalis</strong><small>Sebagai Instansi Pembina &amp; Monitor</small></div>
                    </div>
                    <div class="audience-row">
                        <div class="icon-box"><i data-lucide="briefcase"></i></div>
                        <div><strong>UMKM Binaan</strong><small>Sebagai Pelaku Usaha Aktif</small></div>
                    </div>
                </div>
            </div>
            <div class="about-text reveal delay-1">
                <span class="badge">Tentang MINECT</span>
                <h2>Latar Belakang Sistem</h2>
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
            <div class="card-grid">
                @foreach([
                    ['clock', 'Pelaporan Tidak Rutin', 'UMKM kesulitan menyampaikan rekap laporan operasional secara berkala kepada pihak instansi pembina.'],
                    ['layers', 'Data Tersebar', 'Informasi keuangan dan stok barang masih dicatat secara manual di berbagai media yang rentan hilang.'],
                    ['eye-off', 'Pemantauan Terpisah', 'KADIN Bengkalis kesulitan melihat progres dan profil kesehatan usaha UMKM secara terpadu dan real-time.'],
                    ['trending-down', 'Evaluasi Tidak Efisien', 'Proses analisis perkembangan usaha memakan waktu lama karena data historis yang tidak terekam otomatis.'],
                ] as [$ikon, $judul, $isi])
                    <div class="info-card reveal">
                        <div class="icon-box warn"><i data-lucide="{{ $ikon }}"></i></div>
                        <h3>{{ $judul }}</h3>
                        <p>{{ $isi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Solution Section -->
    <section style="padding-top: 0;">
        <div class="container">
            <div class="solution-box reveal">
                <h2>Sentralisasi dengan MINECT</h2>
                <p>Kami merestrukturisasi alur informasi melalui satu ekosistem cloud terpadu. MINECT memastikan setiap transaksi usaha, manajemen produk, hingga penyusunan laporan keuangan terotomatisasi—mendukung kemudahan rekapitulasi bagi UMKM dan keakuratan monitoring bagi KADIN Bengkalis.</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="bg-alt" id="fitur">
        <div class="container">
            <div class="center reveal">
                <span class="badge">Modul Sistem</span>
                <h2 class="section-title">Fitur Penunjang Pengelolaan</h2>
                <p class="section-subtitle">Struktur instrumen yang disediakan oleh MINECT mewakili siklus bisnis fungsional.</p>
            </div>
            <div class="card-grid">
                @foreach([
                    ['database', 'Manajemen Data UMKM', 'Penyimpanan profil entitas usaha secara mendetail, tingkat kelayakan, status keanggotaan, dan identifikasi kepemilikan.'],
                    ['file-text', 'Pelaporan Usaha', 'Kemudahan pengiriman rekap progres usaha bulanan kepada instansi terkait dengan format yang diakui standar pembukuan.'],
                    ['shopping-cart', 'Transaksi Jual & Beli', 'Pencatatan langsung (Point of Sale/E-Receipt) atas penjualan maupun kulakan bahan baku, terhubung langsung ke arus kas.'],
                    ['box', 'Pengelolaan Persediaan', 'Sistem pencatatan kartu persediaan barang secara otomatis menggunakan model akuntansi inventori (FIFO/Average) yang akurat.'],
                    ['clipboard', 'Pencatatan Beban', 'Otomasi pendataan pengeluaran operasional di luar HPP untuk memastikan perhitungan profit margin tanpa distorsi data.'],
                    ['chart-column', 'Dashboard Monitoring', 'Akses pemantauan indikator kinerja utama (KPI), status kesehatan omzet, dan grafik transaksi real-time secara komprehensif.'],
                    ['chart-pie', 'Laporan Keuangan', 'Penarikan data laporan laba rugi, jurnal umum, hingga ringkasan arus modal untuk analisis fundamental secara praktis.'],
                ] as [$ikon, $judul, $isi])
                    <div class="info-card reveal">
                        <div class="icon-box"><i data-lucide="{{ $ikon }}"></i></div>
                        <h3>{{ $judul }}</h3>
                        <p>{{ $isi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Workflow Section -->
    <section id="workflow">
        <div class="container">
            <h2 class="section-title reveal">Alur Kerja Sistem Integrasi</h2>
            <p class="section-subtitle reveal">Proses ujung-ke-ujung (end-to-end) pengelolaan informasi pada platform MINECT.</p>
            <ol class="workflow reveal delay-1">
                @foreach([
                    ['Pendataan', 'Registrasi & pemetaan UMKM baru'],
                    ['Input Transaksi', 'Rekam aktivitas jual-beli usaha'],
                    ['Integrasi Pelaporan', 'Otomasi data transaksi menjadi report'],
                    ['Pengolahan Sistem', 'Sistem menghitung rasio dan profil inventori'],
                    ['Evaluasi Pusat', 'Monitoring & pembinaan KADIN secara tajam'],
                ] as [$judul, $isi])
                    <li>
                        <div class="step-number">{{ $loop->iteration }}</div>
                        <h4>{{ $judul }}</h4>
                        <p>{{ $isi }}</p>
                    </li>
                @endforeach
            </ol>
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
                <div class="info-card reveal">
                    <h3><i data-lucide="award" class="text-primary"></i> Bagi UMKM Binaan</h3>
                    <ul class="benefit-list">
                        <li><i data-lucide="circle-check"></i> <span><strong>Proses Pelaporan Praktis:</strong> Mengeliminasi pelaporan manual dan rekap kertas.</span></li>
                        <li><i data-lucide="circle-check"></i> <span><strong>Keteraturan Pendataan:</strong> Semua historis transaksi, utang piutang, arsip stok terjaga aman di cloud.</span></li>
                        <li><i data-lucide="circle-check"></i> <span><strong>Evaluasi Mandiri:</strong> Fitur jurnal memungkinkan UMKM paham omzet dan laba bersih secara pasti.</span></li>
                    </ul>
                </div>
                <div class="info-card reveal delay-1">
                    <h3><i data-lucide="shield" class="text-primary"></i> Bagi KADIN Bengkalis</h3>
                    <ul class="benefit-list">
                        <li><i data-lucide="circle-check"></i> <span><strong>Pusat Kontrol Terpadu:</strong> Akses data populasi UMKM secara makro melalui antarmuka web representatif.</span></li>
                        <li><i data-lucide="circle-check"></i> <span><strong>Pengambilan Keputusan Cepat:</strong> Analisis visual KPI mempermudah pendistribusian program bantuan secara tepat sasaran.</span></li>
                        <li><i data-lucide="circle-check"></i> <span><strong>Pemantauan Keberlanjutan:</strong> Validasi perkembangan status usaha UMKM dari level Dasar ke Lanjut secara terstruktur.</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Preview / Screenshot Section -->
    <section id="preview">
        <div class="container center reveal">
            <h2 class="section-title">Antarmuka Pemantauan Cerdas</h2>
            <p class="section-subtitle">Gambaran platform internal MINECT yang dirancang minimalis demi kenyamanan manajemen UMKM harian.</p>
            <div class="preview-frame preview-wide">
                <div class="preview-bar"><span></span><span></span><span></span></div>
                <img src="{{ asset('assets/img/admin-preview.jpg') }}" alt="Tampilan dashboard pemantauan admin MINECT dengan data contoh" loading="lazy">
            </div>
            <div class="info-card togaf">
                <div class="icon-box"><i data-lucide="cpu"></i></div>
                <div>
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
            <a href="{{ route('umkm.login') }}" class="btn btn-invert">Masuk ke Platform <i data-lucide="arrow-right"></i></a>
        </div>
    </section>

    <div class="footer-bottom">
        &copy; {{ date('Y') }} Sistem Operasional MINECT - Terintegrasi Jaringan KADIN Bengkalis.
    </div>

    <script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
    <script src="{{ asset('js/landing.js') }}"></script>
</body>
</html>
