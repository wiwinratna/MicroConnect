{{-- resources/views/partials/admin/navbar.blade.php --}}
@php $namaNav = auth()->user()->name ?? 'User'; @endphp
<nav class="navbar mn-topbar">
    <button type="button" class="mn-icon-btn js-sidebar-toggle" aria-label="Buka atau tutup menu">
        <i data-lucide="menu"></i>
    </button>

    <div class="mn-page-title">@yield('title', 'Dashboard Admin')</div>

    <a class="mn-icon-btn" href="{{ route('admin.notifikasi.index') }}" aria-label="Kotak notifikasi" title="Kotak notifikasi">
        <i data-lucide="bell"></i>
    </a>

    <div class="dropdown">
        <button type="button" class="mn-user" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu akun">
            <span class="mn-avatar">{{ strtoupper(substr($namaNav, 0, 2)) }}</span>
            <span class="mn-user-name text-truncate d-none d-md-block">{{ $namaNav }}</span>
            <i data-lucide="chevron-down" class="d-none d-sm-block"></i>
        </button>

        <div class="dropdown-menu dropdown-menu-end mt-2 py-2" style="min-width: 220px; right: 0;">
            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('umkm.profile') }}">
                <i data-lucide="user" style="width: 16px; height: 16px;"></i> Profil
            </a>
            <div class="dropdown-divider my-1"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="dropdown-item d-flex align-items-center gap-2 text-danger" type="submit">
                    <i data-lucide="log-out" style="width: 16px; height: 16px;"></i> Log out
                </button>
            </form>
        </div>
    </div>
</nav>
