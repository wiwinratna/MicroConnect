{{-- resources/views/umkm/dashboard.blade.php --}}
@extends('layouts.umkm')

@section('title', 'Dashboard UMKM')

@section('content')
	<h1 class="h3 mb-3">
		<strong>Dashboard</strong> UMKM
	</h1>

	<div class="row">
		<div class="col-sm-6 col-xl-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title mb-3">Total Penjualan Bulan Ini</h5>
					<h1 class="mt-1 mb-3">Rp 0</h1>
					<p class="text-muted mb-0">Nanti diisi dari data transaksi.</p>
				</div>
			</div>
		</div>

		{{-- Tambah card lain kalau mau --}}
	</div>
@endsection
