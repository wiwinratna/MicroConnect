{{-- resources/views/partials/umkm/navbar.blade.php --}}
@php
    $umkmNav = auth()->user()->umkm ?? null;
    $namaNav = $umkmNav->nama_usaha ?? auth()->user()->name ?? 'User';
    $methodBadge = $umkmNav && $umkmNav->inventory_method ? strtoupper($umkmNav->inventory_method) : 'AVERAGE';
@endphp
<nav class="navbar mn-topbar">
    <button type="button" class="mn-icon-btn js-sidebar-toggle" aria-label="Buka atau tutup menu">
        <i data-lucide="menu"></i>
    </button>

    <div class="mn-page-title">@yield('title', 'Dashboard UMKM')</div>

    <a class="mn-icon-btn" href="{{ route('umkm.notifikasi.index') }}" aria-label="Notifikasi" title="Notifikasi">
        <i data-lucide="bell"></i>
    </a>

    <div class="dropdown">
        <button type="button" class="mn-user" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu akun">
            @if($umkmNav && $umkmNav->logo_path)
                <img src="{{ asset('storage/' . $umkmNav->logo_path) }}" class="mn-avatar" alt="">
            @else
                <span class="mn-avatar">{{ strtoupper(substr($namaNav, 0, 2)) }}</span>
            @endif
            <span class="mn-user-name text-truncate d-none d-md-block">{{ $namaNav }}</span>
            <i data-lucide="chevron-down" class="d-none d-sm-block"></i>
        </button>

        <div class="dropdown-menu dropdown-menu-end mt-2 py-2" style="min-width: 220px; right: 0;">
            <div class="px-3 py-2 mb-1 d-md-none border-bottom">
                <span class="d-block fw-bold">{{ $namaNav }}</span>
                <span class="d-block text-muted small">Metode: {{ $methodBadge }}</span>
            </div>
            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('umkm.profile') }}">
                <i data-lucide="user" style="width: 16px; height: 16px;"></i> Profil Usaha
            </a>
            <div class="dropdown-divider my-1"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="dropdown-item d-flex align-items-center gap-2 text-danger" type="submit">
                    <i data-lucide="log-out" style="width: 16px; height: 16px;"></i> Keluar Panel
                </button>
            </form>
        </div>
    </div>
</nav>
