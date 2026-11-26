<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">

        <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('assets/img/logo-white.png') }}" alt="KADIN Panel Logo" style="height: 50px; width: auto; object-fit: contain;">
        </a>

        <ul class="sidebar-nav">

            <li class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.dashboard') }}">
                    <i class="align-middle" data-feather="home"></i>
                    <span class="align-middle">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.pengajuan.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.pengajuan.index') }}">
                    <i class="align-middle" data-feather="user-plus"></i>
                    <span class="align-middle">Pengajuan</span>
                    @php $jumlahMenunggu = \App\Models\PengajuanPendaftaran::where('status', 'menunggu')->count(); @endphp
                    @if($jumlahMenunggu > 0)
                        <span class="sidebar-badge badge bg-warning text-dark">{{ $jumlahMenunggu }}</span>
                    @endif
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.periode.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.periode.index') }}">
                    <i class="align-middle" data-feather="calendar"></i>
                    <span class="align-middle">Periode Penilaian</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.penilaian-ulang.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.penilaian-ulang.index') }}">
                    <i class="align-middle" data-feather="clipboard"></i>
                    <span class="align-middle">Tinjau Penilaian</span>
                    @php $jumlahTinjau = \App\Models\PenilaianLevel::where('jenis', 'ulang')->where('status_peninjauan', 'menunggu')->count(); @endphp
                    @if($jumlahTinjau > 0)
                        <span class="sidebar-badge badge bg-warning text-dark">{{ $jumlahTinjau }}</span>
                    @endif
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.notifikasi.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.notifikasi.index') }}">
                    <i class="align-middle" data-feather="bell"></i>
                    <span class="align-middle">Kotak Notifikasi</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.umkm.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.umkm.index') }}">
                    <i class="align-middle" data-feather="users"></i>
                    <span class="align-middle">Daftar UMKM</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.kategori-usaha.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.kategori-usaha.index') }}">
                    <i class="align-middle" data-feather="tag"></i>
                    <span class="align-middle">Kategori Usaha</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.aturan-modul.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.aturan-modul.index') }}">
                    <i class="align-middle" data-feather="sliders"></i>
                    <span class="align-middle">Aturan Modul</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.laporan-level.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.laporan-level.index') }}">
                    <i class="align-middle" data-feather="file-text"></i>
                    <span class="align-middle">Laporan per Level</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.iuran-periode.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.iuran-periode.index') }}">
                    <i class="align-middle" data-feather="credit-card"></i>
                    <span class="align-middle">Iuran Periode</span>
                </a>
            </li>

            <li class="sidebar-item {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.tickets.index') }}">
                    <i class="align-middle" data-feather="message-square"></i>
                    <span class="align-middle">Ticketing UMKM</span>
                </a>
            </li>

        </ul>
    </div>
</nav>
