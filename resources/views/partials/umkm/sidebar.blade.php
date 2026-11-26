{{-- resources/views/partials/umkm/sidebar.blade.php --}}
{{-- Sidebar nav dikontrol oleh FeatureAccess helper (feature_can / modul_boleh) --}}
@php
    $layananUlang = app(\App\Services\PenilaianUlangService::class);
    $periodeAktif = $layananUlang->periodeBerlaku();
    $perluIsi = $periodeAktif && auth()->user()->umkm && !$layananUlang->sudahMengisi(auth()->user()->umkm, $periodeAktif);

    // Returns null when the menu is not allowed, so it is never rendered.
    // The route is only resolved for allowed menus.
    $link = fn (bool $show, string $label, string $icon, string $route, $active, ?string $badge = null) => $show ? [
        'label' => $label, 'icon' => $icon, 'url' => route($route),
        'active' => is_bool($active) ? $active : request()->routeIs($active),
        'badge' => $badge,
    ] : null;

    $menu = [
        $link(true, 'Dashboard', 'layout-dashboard', 'umkm.dashboard', 'umkm.dashboard'),
        ['label' => 'Master Data', 'icon' => 'database', 'items' => [
            $link(modul_boleh('bahan_baku'), 'Bahan', 'archive', 'umkm.bahan.index', 'umkm.bahan*'),
            $link(modul_boleh('produk'), modul_label('produk', 'Produk Jadi'), 'shopping-bag', 'umkm.produk.index', 'umkm.produk*'),
            $link(true, 'Data Pelanggan', 'users', 'umkm.etalase.pelanggan.index', 'umkm.etalase.pelanggan*'),
            $link(feature_can('coa'), 'COA', 'book', 'umkm.coa.index', 'umkm.coa.*'),
        ]],
        ['label' => 'Transaksi', 'icon' => 'arrow-left-right', 'items' => [
            $link(modul_boleh('pembelian'), 'Pembelian', 'shopping-cart', 'umkm.pembelian.index', 'umkm.pembelian*'),
            $link(modul_boleh('penjualan'), 'Penjualan / Kas', 'banknote', 'umkm.penjualan.index', 'umkm.penjualan*'),
            $link(modul_boleh('penjualan'), 'Kasir', 'monitor', 'umkm.etalase.index',
                request()->routeIs('umkm.etalase*') && !request()->routeIs('umkm.etalase.pelanggan*')),
            $link(feature_can('piutang'), 'Piutang Pelanggan', 'credit-card', 'umkm.piutang.index', 'umkm.piutang*'),
        ]],
        ['label' => 'Keuangan & Iuran', 'icon' => 'wallet', 'items' => [
            $link(modul_boleh('beban'), 'Beban Operasional', 'trending-down', 'umkm.beban.index', 'umkm.beban*'),
            $link(true, 'Iuran Bulanan', 'calendar', 'umkm.iuran.index', 'umkm.iuran*'),
        ]],
        ['label' => 'Akuntansi & Laporan', 'icon' => 'chart-column', 'items' => [
            $link(feature_can('jurnal_umum'), 'Jurnal Umum', 'book-open', 'umkm.jurnal.index', 'umkm.jurnal*'),
            $link(modul_boleh('kartu_stok'), 'Kartu Stok', 'layers', 'umkm.laporan.kartu_stok', 'umkm.laporan.kartu_stok'),
            $link(true, 'Laporan Keuangan', 'file-chart-column', 'umkm.laporan.index', 'umkm.laporan.index'),
        ]],
        ['label' => 'Penilaian', 'icon' => 'award', 'items' => [
            $link(true, 'Penilaian Ulang', 'refresh-cw', 'umkm.penilaian-ulang.index', 'umkm.penilaian-ulang*', $perluIsi ? 'Baru' : null),
            $link(true, 'Hasil Penilaian', 'clipboard-check', 'umkm.penilaian.index', 'umkm.penilaian.*'),
        ]],
        ['label' => 'Akun & Bantuan', 'icon' => 'life-buoy', 'items' => [
            $link(true, 'Profil UMKM', 'user', 'umkm.profile', 'umkm.profile*'),
            $link(true, 'Notifikasi', 'bell', 'umkm.notifikasi.index', 'umkm.notifikasi*'),
            $link(true, 'Pengaduan & Konsultasi', 'message-circle', 'umkm.tickets.index', 'umkm.tickets*'),
        ]],
    ];
@endphp
<nav id="sidebar" class="sidebar js-sidebar" aria-label="Menu utama">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('umkm.dashboard') }}">
            <img src="{{ asset('assets/img/logo-white.png') }}" alt="Sistem UMKM Logo">
        </a>

        @include('partials.sidebar-menu', ['menu' => $menu])
    </div>
</nav>
<button type="button" class="sidebar-backdrop" aria-label="Tutup menu"></button>
