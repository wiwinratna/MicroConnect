{{-- resources/views/partials/umkm/navbar.blade.php --}}
<nav class="navbar navbar-expand navbar-light navbar-bg">
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
				<a class="nav-link dropdown-toggle d-none d-sm-inline-flex align-items-center" href="#" data-bs-toggle="dropdown">
					@if($umkmNav && $umkmNav->logo_path)
						<img src="{{ asset('storage/' . $umkmNav->logo_path) }}" 
							 class="avatar img-fluid rounded me-2" style="object-fit:cover;"
							 alt="{{ $umkmNav->nama_usaha }}" />
					@elseif($umkmNav && $umkmNav->nama_usaha)
						<span class="avatar rounded me-2 d-inline-flex align-items-center justify-content-center bg-primary text-white fw-bold" style="font-size:0.8rem;">
							{{ strtoupper(substr($umkmNav->nama_usaha, 0, 2)) }}
						</span>
					@else
						<img src="{{ asset('img/avatars/avatar.jpg') }}" 
							 class="avatar img-fluid rounded me-2" 
							 alt="User" />
					@endif
					
					<div class="d-flex flex-column text-start me-1">
						<span class="text-dark fw-bold" style="line-height: 1.2;">{{ $umkmNav->nama_usaha ?? auth()->user()->name ?? 'User' }}</span>
						<span class="text-muted" style="font-size: 0.70rem; line-height: 1.2; letter-spacing: 0.2px;">{{ auth()->user()->name ?? 'User' }} &bull; {{ $methodBadge }}</span>
					</div>
                </a>
				<div class="dropdown-menu dropdown-menu-end">
					<a class="dropdown-item" href="{{ route('umkm.profile') }}">
            <i class="align-middle me-1" data-feather="user"></i> Profil
          </a>
					<div class="dropdown-divider"></div>
					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<button class="dropdown-item" type="submit">
							<i class="align-middle me-1" data-feather="log-out"></i> Log out
						</button>
					</form>
				</div>
			</li>
		</ul>
	</div>
</nav>
