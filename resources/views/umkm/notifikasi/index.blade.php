@extends('layouts.umkm')

@section('title', 'Notifikasi')

@php
	$labelJenis = [
		'registrasi_diterima' => 'Registrasi diterima',
		'akun_aktif'          => 'Akun aktif',
		'pengajuan_ditolak'   => 'Pengajuan ditolak',
		'periode_dibuka'      => 'Periode penilaian dibuka',
		'hasil_penilaian'     => 'Hasil penilaian',
	];
@endphp

@section('content')
	<h1 class="h3 mb-3"><strong>Notifikasi</strong></h1>

	@forelse($notifikasi as $n)
		<div class="card mb-2">
			<div class="card-body py-3">
				<div class="d-flex justify-content-between flex-wrap">
					<strong>{{ $labelJenis[$n->jenis] ?? $n->jenis }}</strong>
					<span class="text-muted small">{{ $n->created_at?->format('d/m/Y H:i') }}</span>
				</div>
				<div class="mt-1">{{ $n->isi }}</div>
			</div>
		</div>
	@empty
		<div class="card">
			<div class="card-body text-muted">Belum ada notifikasi.</div>
		</div>
	@endforelse

	<div class="mt-3">{{ $notifikasi->links() }}</div>
@endsection
