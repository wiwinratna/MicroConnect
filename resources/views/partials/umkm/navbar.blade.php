<nav class="navbar navbar-expand navbar-light navbar-bg shadow-none sticky-top" style="border-bottom: 1px solid rgba(0,0,0,0.06); top: 0; z-index: 1000; background: #fff !important;">
    <a class="sidebar-toggle js-sidebar-toggle">
        <i class="hamburger align-self-center"></i>
    </a>

    <div class="navbar-collapse collapse">
        <ul class="navbar-nav ms-auto navbar-align">
            <li class="nav-item dropdown">
                @php
                    $umkmNav = auth()->user()->umkm ?? null;
                    $methodBadge = $umkmNav && $umkmNav->inventory_method ? strtoupper($umkmNav->inventory_method) : 'AVERAGE';
                @endphp
                <a class="nav-link d-none d-sm-inline-flex align-items-center px-2 py-1 rounded-3 hover-bg-light transition-all" href="#" data-bs-toggle="dropdown" style="gap: 12px; cursor: pointer;">
                    <div class="d-none d-md-flex flex-column text-end" style="justify-content: center;">
                        <span class="text-dark fw-bold" style="font-size: 0.9rem; line-height: 1;">{{ $umkmNav->nama_usaha ?? auth()->user()->name ?? 'User' }}</span>
                    </div>
                    
                    <div class="d-flex align-items-center" style="gap: 6px;">
                        @if($umkmNav && $umkmNav->logo_path)
                            <img src="{{ asset('storage/' . $umkmNav->logo_path) }}" 
                                 class="avatar rounded-circle shadow-sm" style="object-fit:cover; width: 40px; height: 40px; border: 2px solid #fff;"
                                 alt="{{ $umkmNav->nama_usaha }}" />
                        @elseif($umkmNav && $umkmNav->nama_usaha)
                            <span class="avatar rounded-circle shadow-sm d-inline-flex align-items-center justify-content-center bg-primary text-white fw-bold" style="width: 40px; height: 40px; font-size: 0.9rem; border: 2px solid #fff;">
                                {{ strtoupper(substr($umkmNav->nama_usaha, 0, 2)) }}
                            </span>
                        @else
                            <img src="{{ asset('img/avatars/avatar.jpg') }}" 
                                 class="avatar rounded-circle shadow-sm" style="width: 40px; height: 40px; border: 2px solid #fff;"
                                 alt="User" />
                        @endif
                        <i data-feather="chevron-down" class="text-muted" style="stroke-width: 2px; width: 16px; height: 16px;"></i>
                    </div>
                </a>
                
                <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2 py-2" style="min-width: 200px;">
                    <div class="px-3 py-2 mb-1 d-md-none border-bottom">
                        <span class="d-block text-dark fw-bold">{{ $umkmNav->nama_usaha ?? auth()->user()->name ?? 'User' }}</span>
                        <span class="d-block text-muted small">Metode: {{ $methodBadge }}</span>
                    </div>
                    <a class="dropdown-item px-3 py-2 fw-medium d-flex align-items-center gap-2 text-secondary hover-primary" href="{{ route('umkm.profile') }}">
                        <i data-feather="user" style="width: 16px; height: 16px;"></i> Profil Usaha
                    </a>
                    <div class="dropdown-divider my-1 opacity-50"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item px-3 py-2 fw-medium d-flex align-items-center gap-2 text-danger hover-danger" type="submit">
                            <i data-feather="log-out" style="width: 16px; height: 16px;"></i> Keluar Panel
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>
