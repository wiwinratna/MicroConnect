@php
    $jumlahMenunggu = \App\Models\PengajuanPendaftaran::where('status', 'menunggu')->count();
    $jumlahTinjau = \App\Models\PenilaianLevel::where('jenis', 'ulang')->where('status_peninjauan', 'menunggu')->count();

    $link = fn (string $label, string $icon, string $route, string $active, int $badge = 0) => [
        'label' => $label, 'icon' => $icon, 'url' => route($route),
        'active' => request()->routeIs($active), 'badge' => $badge ?: null,
    ];

    $menu = [
        $link('Dashboard', 'layout-dashboard', 'admin.dashboard', 'admin.dashboard'),
        ['label' => 'Pendaftaran & Penilaian', 'icon' => 'clipboard-check', 'items' => [
            $link('Pengajuan', 'user-plus', 'admin.pengajuan.index', 'admin.pengajuan.*', $jumlahMenunggu),
            $link('Periode Penilaian', 'calendar', 'admin.periode.index', 'admin.periode.*'),
            $link('Tinjau Penilaian', 'clipboard-list', 'admin.penilaian-ulang.index', 'admin.penilaian-ulang.*', $jumlahTinjau),
            $link('Indikator Penilaian', 'list-checks', 'admin.indikator.index', 'admin.indikator.*'),
        ]],
        ['label' => 'Data UMKM', 'icon' => 'database', 'items' => [
            $link('Daftar UMKM', 'users', 'admin.umkm.index', 'admin.umkm.*'),
            $link('Kategori Usaha', 'tag', 'admin.kategori-usaha.index', 'admin.kategori-usaha.*'),
            $link('Aturan Modul', 'sliders-horizontal', 'admin.aturan-modul.index', 'admin.aturan-modul.*'),
            $link('Laporan per Level', 'file-text', 'admin.laporan-level.index', 'admin.laporan-level.*'),
        ]],
        ['label' => 'Keuangan', 'icon' => 'wallet', 'items' => [
            $link('Iuran Periode', 'credit-card', 'admin.iuran-periode.index', 'admin.iuran-periode.*'),
        ]],
        ['label' => 'Komunikasi', 'icon' => 'messages-square', 'items' => [
            $link('Kotak Notifikasi', 'bell', 'admin.notifikasi.index', 'admin.notifikasi.*'),
            $link('Ticketing UMKM', 'message-square', 'admin.tickets.index', 'admin.tickets.*'),
        ]],
    ];
@endphp
<nav id="sidebar" class="sidebar js-sidebar" aria-label="Menu utama">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/img/logo-white.png') }}" alt="KADIN Panel Logo">
        </a>

        @include('partials.sidebar-menu', ['menu' => $menu])
    </div>
</nav>
<button type="button" class="sidebar-backdrop" aria-label="Tutup menu"></button>
