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


			@if($level >= 3)
			<li class="sidebar-header">Master Data</li>

			{{-- MODE KASIR (ETALASE) --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.etalase*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.etalase.index') }}" target="_blank">
					<i class="align-middle" data-feather="monitor"></i>
					<span class="align-middle">Mode Kasir</span>
				</a>
			</li>

			{{-- BAHAN BAKU --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.bahan*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.bahan.index') }}">
					<i class="align-middle" data-feather="archive"></i>
					<span class="align-middle">Bahan Baku</span>
				</a>
			</li>

			{{-- KARTU STOK --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.laporan.kartu_stok') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.laporan.kartu_stok') }}">
					<i class="align-middle" data-feather="layers"></i>
					<span class="align-middle">Kartu Stok</span>
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
			@endif


			{{-- [NONAKTIF SEMENTARA] Anggaran Estimasi - dinonaktifkan per permintaan revisi
		@if($level >= 3)
			<li class="sidebar-header">Anggaran & Produksi</li>
			<li class="sidebar-item {{ request()->routeIs('umkm.anggaran*') ? 'active' : '' }}">
			<a class="sidebar-link" href="{{ route('umkm.anggaran.index') }}">
				<i class="align-middle" data-feather="clipboard"></i>
				<span class="align-middle">Anggaran (Estimasi Harga)</span>
			</a>
			</li>
			@endif
		--}}

			<li class="sidebar-header">Transaksi Utama</li>
			
			{{-- PEMBELIAN --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.pembelian*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.pembelian.index') }}">
					<i class="align-middle" data-feather="shopping-cart"></i>
					<span class="align-middle">Pembelian</span>
				</a>
			</li>

			{{-- TRANSAKSI PENJUALAN / KAS (next) --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.penjualan*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.penjualan.index') }}">
					<i class="align-middle" data-feather="dollar-sign"></i>
					<span class="align-middle">Penjualan / Kas</span>
				</a>
			</li>

			{{-- MODE ETALASE --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.etalase*') ? 'active' : '' }}">
				<a class="sidebar-link text-primary fw-semibold" href="{{ route('umkm.etalase.index') }}">
					<i class="align-middle text-primary" data-feather="monitor"></i>
					<span class="align-middle">Mode Etalase (POS)</span>
				</a>
			</li>

			@if($level >= 2)
			{{-- PIUTANG PELANGGAN --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.piutang*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.piutang.index') }}">
					<i class="align-middle" data-feather="credit-card"></i>
					<span class="align-middle">Piutang Pelanggan</span>
				</a>
			</li>
			@endif


			<li class="sidebar-header">Keuangan & Iuran</li>

			@if($level >= 2)
			{{-- BEBAN OPERASIONAL AKTUAL --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.beban*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.beban.index') }}">
					<i class="align-middle" data-feather="trending-down"></i>
					<span class="align-middle">Beban Aktual (Pengeluaran)</span>
				</a>
			</li>
			@endif

			{{-- IURAN BULANAN --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.iuran*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.iuran.index') }}">
					<i class="align-middle" data-feather="calendar"></i>
					<span class="align-middle">Iuran Bulanan</span>
				</a>
			</li>


			<li class="sidebar-header">Akuntansi & Laporan</li>

			{{-- JURNAL UMUM --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.jurnal*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.jurnal.index') }}">
					<i class="align-middle" data-feather="book-open"></i>
					<span class="align-middle">Jurnal Umum</span>
				</a>
			</li>

			{{-- LAPORAN --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.laporan*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.laporan.index') }}">
					<i class="align-middle" data-feather="bar-chart-2"></i>
					<span class="align-middle">Laporan Keuangan</span>
				</a>
			</li>

			<li class="sidebar-header">Pengaturan & Bantuan</li>

			{{-- TICKETING --}}
			<li class="sidebar-item {{ request()->routeIs('umkm.tickets*') ? 'active' : '' }}">
				<a class="sidebar-link" href="{{ route('umkm.tickets.index') }}">
					<i class="align-middle" data-feather="message-circle"></i>
					<span class="align-middle">Pengaduan & Konsultasi</span>
				</a>
			</li>

		</ul>


	</div>
</nav>
