@extends('layouts.umkm')

@section('title', 'Hasil Penilaian')

@section('content')
	<h1 class="h3 mb-3"><strong>Hasil</strong> Penilaian</h1>

	@forelse($penilaian as $n)
		@php
			$berbeda = $n->level_akhir_id && $n->level_akhir_id !== $n->level_rekomendasi_id;
		@endphp

		<div class="card mb-3">
			<div class="card-header">
				<h5 class="card-title mb-0">
					Penilaian {{ $n->jenis === 'awal' ? 'awal' : 'ulang' }}
					<span class="text-muted fs-6">&middot; {{ $n->tanggal_penilaian?->format('d/m/Y') }}</span>
				</h5>
			</div>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-sm align-middle">
						<thead>
							<tr><th>Pertanyaan</th><th>Jawaban</th><th class="text-end">Skor</th></tr>
						</thead>
						<tbody>
							@foreach($n->detail as $d)
								<tr>
									<td>{{ $d->indikator?->nama }}</td>
									<td>{{ $d->teks_pilihan }}</td>
									<td class="text-end">{{ $d->skor }}</td>
								</tr>
							@endforeach
						</tbody>
						<tfoot>
							<tr class="fw-bold">
								<td colspan="2">Total skor</td>
								<td class="text-end">{{ $n->total_skor }}</td>
							</tr>
						</tfoot>
					</table>
				</div>

				<p class="mb-1">Level rekomendasi sistem: <strong>{{ $n->levelRekomendasi?->nama_level }}</strong></p>

				@if($n->jenis === 'ulang')
					<p class="mb-1">Arah rekomendasi dibanding level Anda saat itu: <strong>{{ ucfirst($n->arah_rekomendasi) }}</strong></p>
				@endif

				@if($n->levelAkhir)
					<p class="mb-1">Level yang ditetapkan Admin: <strong>{{ $n->levelAkhir->nama_level }}</strong></p>
				@else
					<p class="mb-1 text-muted">Level akhir belum ditetapkan Admin.</p>
				@endif

				{{-- BR-02: alasan hanya tampil jika level akhir berbeda dari rekomendasi --}}
				@if($berbeda)
					<div class="alert alert-info mt-2 mb-0">
						<strong>Alasan penetapan:</strong> {{ $n->alasan_penetapan }}
					</div>
				@endif
			</div>
		</div>
	@empty
		<div class="card">
			<div class="card-body text-muted">Belum ada penilaian untuk akun ini.</div>
		</div>
	@endforelse
@endsection
