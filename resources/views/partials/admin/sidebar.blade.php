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

            <li class="sidebar-item {{ request()->routeIs('admin.umkm.*') ? 'active' : '' }}">
                <a class="sidebar-link" href="{{ route('admin.umkm.index') }}">
                    <i class="align-middle" data-feather="users"></i>
                    <span class="align-middle">Daftar UMKM</span>
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
