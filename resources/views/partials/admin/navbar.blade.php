{{-- resources/views/partials/umkm/navbar.blade.php --}}
<nav class="navbar navbar-expand navbar-light navbar-bg">
	<a class="sidebar-toggle js-sidebar-toggle">
    <i class="hamburger align-self-center"></i>
  </a>

	<div class="navbar-collapse collapse">
		<ul class="navbar-nav ms-auto navbar-align">
			<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
          <img src="{{ asset('img/avatars/avatar.jpg') }}" 
               class="avatar img-fluid rounded me-1" 
               alt="{{ auth()->user()->name ?? 'User' }}" />
          <span class="text-dark">{{ auth()->user()->name ?? 'User' }}</span>
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
