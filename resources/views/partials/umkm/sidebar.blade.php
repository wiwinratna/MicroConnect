{{-- resources/views/partials/umkm/sidebar.blade.php --}}
{{-- Sidebar nav dikontrol oleh FeatureAccess helper (feature_can / feature_level) --}}
<nav id="sidebar" class="sidebar js-sidebar">
	<div class="sidebar-content js-simplebar">

		<a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('umkm.dashboard') }}">
			<img src="{{ asset('assets/img/logo-white.png') }}" alt="Sistem UMKM Logo"
				style="height: 50px; width: auto; object-fit: contain;">
		</a>

		<ul class="sidebar-nav">

			<li class="sidebar-header">Menu Utama</li>

			{{-- DASHBOARD — semua level --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.dashboard') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.dashboard') }}">
					<i class="align-middle" data-feather="sliders"></i>
					<span class="align-middle">Dashboard</span>
				</a>
			</li>

			{{-- PROFIL UMKM — semua level --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.profile*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.profile') }}">
					<i class="align-middle" data-feather="user"></i>
					<span class="align-middle">Profil UMKM</span>
				</a>
			</li>


			{{-- ============================================================ --}}
			{{-- MASTER DATA — Level 1+ --}}
			{{-- ============================================================ --}}
			<li class="sidebar-header">Master Data</li>

			{{-- BAHAN BAKU — Level 1+ --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.bahan*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.bahan.index') }}">
					<i class="align-middle" data-feather="archive"></i>
					<span class="align-middle">Bahan Baku</span>
				</a>
			</li>

			{{-- PRODUK JADI — Level 1+ --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.produk*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.produk.index') }}">
					<i class="align-middle" data-feather="shopping-bag"></i>
					<span class="align-middle">Produk Jadi</span>
				</a>
			</li>

			{{-- PELANGGAN — Level 1+ --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.etalase.pelanggan*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.etalase.pelanggan.index') }}">
					<i class="align-middle" data-feather="users"></i>
					<span class="align-middle">Data Pelanggan</span>
				</a>
			</li>

			{{-- COA — Level 3 only --}}
			@if(feature_can('coa'))
				<li class="sidebar-item {{ request()->routeIs('umkm.coa.*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('umkm.coa.index') }}">
						<i class="align-middle" data-feather="book"></i>
						<span class="align-middle">COA</span>
					</a>
				</li>
			@endif


			{{-- ============================================================ --}}
			{{-- TRANSAKSI UTAMA — Level 1+ --}}
			{{-- ============================================================ --}}
			<li class="sidebar-header">Transaksi Utama</li>

			{{-- PEMBELIAN — Level 1+ --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.pembelian*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.pembelian.index') }}">
					<i class="align-middle" data-feather="shopping-cart"></i>
					<span class="align-middle">Pembelian</span>
				</a>
			</li>

			{{-- PENJUALAN — Level 1+ --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.penjualan*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.penjualan.index') }}">
					<i class="align-middle" data-feather="dollar-sign"></i>
					<span class="align-middle">Penjualan / Kas</span>
				</a>
			</li>

			{{-- MODE KASIR — Level 1+ --}}
			<li
				class="sidebar-item {{ request()->routeIs('umkm.etalase*') && !request()->routeIs('umkm.etalase.pelanggan*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.etalase.index') }}">
					<i class="align-middle" data-feather="monitor"></i>
					<span class="align-middle">Kasir (POS)</span>
				</a>
			</li>

			{{-- PIUTANG PELANGGAN — Level 2+ --}}
			@if(feature_can('piutang'))
				<li class="sidebar-item {{ request()->routeIs('umkm.piutang*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('umkm.piutang.index') }}">
						<i class="align-middle" data-feather="credit-card"></i>
						<span class="align-middle">Piutang Pelanggan</span>
					</a>
				</li>
			@endif


			{{-- ============================================================ --}}
			{{-- KEUANGAN & IURAN — Level 1+ --}}
			{{-- ============================================================ --}}
			<li class="sidebar-header">Keuangan & Iuran</li>

			{{-- BEBAN OPERASIONAL AKTUAL — Level 1+ --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.beban*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.beban.index') }}">
					<i class="align-middle" data-feather="trending-down"></i>
					<span class="align-middle">Beban Aktual (Pengeluaran)</span>
				</a>
			</li>

			{{-- IURAN BULANAN — Level 1+ --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.iuran*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.iuran.index') }}">
					<i class="align-middle" data-feather="calendar"></i>
					<span class="align-middle">Iuran Bulanan</span>
				</a>
			</li>


			{{-- ============================================================ --}}
			{{-- AKUNTANSI & LAPORAN --}}
			{{-- ============================================================ --}}
			<li class="sidebar-header">Akuntansi & Laporan</li>

			{{-- JURNAL UMUM — Level 1+ --}}
			@if(feature_can('jurnal_umum'))
				<li class="sidebar-item {{ request()->routeIs('umkm.jurnal*') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('umkm.jurnal.index') }}">
						<i class="align-middle" data-feather="book-open"></i>
						<span class="align-middle">Jurnal Umum</span>
					</a>
				</li>
			@endif

			{{-- KARTU STOK — Level 2+ --}}
			@if(feature_can('kartu_stok'))
				<li class="sidebar-item {{ request()->routeIs('umkm.laporan.kartu_stok') ? 'active' : '' }}">
					<a class="sidebar-link" href="{{ route('umkm.laporan.kartu_stok') }}">
						<i class="align-middle" data-feather="layers"></i>
						<span class="align-middle">Kartu Stok</span>
					</a>
				</li>
			@endif

			{{-- LAPORAN KEUANGAN — Level 1+ --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.laporan.index') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.laporan.index') }}">
					<i class="align-middle" data-feather="bar-chart-2"></i>
					<span class="align-middle">Laporan Keuangan</span>
				</a>
			</li>


			{{-- ============================================================ --}}
			{{-- PENGATURAN & BANTUAN --}}
			{{-- ============================================================ --}}
			<li class="sidebar-header">Pengaturan & Bantuan</li>

			{{-- TICKETING — Level 1+ --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.tickets*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.tickets.index') }}">
					<i class="align-middle" data-feather="message-circle"></i>
					<span class="align-middle">Pengaduan & Konsultasi</span>
				</a>
			</li>

		</ul>


	</div>
</nav>