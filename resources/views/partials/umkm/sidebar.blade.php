{{-- resources/views/partials/umkm/sidebar.blade.php --}}
<nav id="sidebar" class="sidebar js-sidebar">
	<div class="sidebar-content js-simplebar">

		<a class="sidebar-brand" href="{{ route('umkm.dashboard') }}">
			<span class="align-middle">Sistem UMKM</span>
		</a>

		<ul class="sidebar-nav">

			<li class="sidebar-header">Menu Utama</li>

			{{-- DASHBOARD --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.dashboard') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.dashboard') }}">
					<i class="align-middle" data-feather="sliders"></i>
					<span class="align-middle">Dashboard</span>
				</a>
			</li>

			{{-- PROFIL UMKM --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.profile*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.profile') }}">
					<i class="align-middle" data-feather="user"></i>
					<span class="align-middle">Profil UMKM</span>
				</a>
			</li>


			<li class="sidebar-header">Master Data</li>

			{{-- BAHAN BAKU --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.bahan*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.bahan.index') }}">
					<i class="align-middle" data-feather="archive"></i>
					<span class="align-middle">Bahan Baku</span>
				</a>
			</li>

			{{-- PRODUK JADI --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.produk*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.produk.index') }}">
					<i class="align-middle" data-feather="shopping-bag"></i>
					<span class="align-middle">Produk Jadi</span>
				</a>
			</li>

			<li class="sidebar-item {{ request()->routeIs('umkm.coa.*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.coa.index') }}">
					<i class="align-middle" data-feather="book"></i>
					<span class="align-middle">COA</span>
				</a>
			</li>


			<li class="sidebar-header">Transaksi</li>
			<li class="sidebar-item {{ request()->routeIs('umkm.anggaran*') ? 'active' : '' }}">
			<a class="sidebar-link" href="{{ route('umkm.anggaran.index') }}">
				<i class="align-middle" data-feather="clipboard"></i>
				<span class="align-middle">Anggaran Bulanan</span>
			</a>
			</li>

			
			<li class="sidebar-item {{ request()->routeIs('umkm.produksi*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.produksi.index') }}">
					<i class="align-middle" data-feather="tool"></i>
					<span class="align-middle">Produksi</span>
				</a>
			</li>

			{{-- PEMBELIAN BAHAN --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.pembelian*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.pembelian.index') }}">
					<i class="align-middle" data-feather="shopping-cart"></i>
					<span class="align-middle">Pembelian Bahan</span>
				</a>
			</li>

			{{-- TRANSAKSI PENJUALAN / KAS (next) --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.penjualan*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.penjualan.index') }}">
					<i class="align-middle" data-feather="dollar-sign"></i>
					<span class="align-middle">Penjualan / Kas</span>
				</a>
			</li>


			<li class="sidebar-header">Laporan</li>

			{{-- LAPORAN --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.laporan*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.laporan.index') }}">
					<i class="align-middle" data-feather="bar-chart-2"></i>
					<span class="align-middle">Laporan Keuangan</span>
				</a>
			</li>

		</ul>

	</div>
</nav>
