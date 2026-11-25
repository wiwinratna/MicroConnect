@extends('layouts.umkm')

@section('title', 'Penilaian Ulang')

@section('content')
	<h1 class="h3 mb-3"><strong>Penilaian</strong> Ulang</h1>

	@if($status === 'tutup')
		<div class="card">
			<div class="card-body text-muted">
				Saat ini tidak ada periode penilaian ulang yang dibuka. Anda akan mendapat pemberitahuan
				di menu Notifikasi saat periode dibuka.
			</div>
		</div>

	@elseif($status === 'sudah')
		<div class="card">
			<div class="card-body">
				Anda sudah mengisi penilaian pada periode <strong>{{ $periode->nama }}</strong>.
				Hasilnya dapat dilihat di menu <a href="{{ route('umkm.penilaian.index') }}">Hasil Penilaian</a>
				setelah ditinjau Admin.
			</div>
		</div>

	@else
		<p class="text-muted">
			Periode <strong>{{ $periode->nama }}</strong>
			({{ $periode->tanggal_mulai->format('d/m/Y') }} - {{ $periode->tanggal_selesai->format('d/m/Y') }}).
			Pilihan sudah terisi otomatis dari data Anda. Periksa dan ubah jika ada yang tidak sesuai.
		</p>

		@if($errors->any())
			<div class="alert alert-danger">
				<ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
			</div>
		@endif

		<form method="POST" action="{{ route('umkm.penilaian-ulang.store') }}" id="formPenilaianUlang">
			@csrf

			@foreach($indikator as $ind)
				@php
					$info      = $bawaan[$ind->id];
					$terpilih  = (string) old('jawaban.' . $ind->id, $info['pilihan_id']);
					$jenis     = $jenisSistem[$ind->id] ?? null;
				@endphp
				<div class="card mb-3">
					<div class="card-body">
						<fieldset>
							<legend class="h6">{{ $ind->urutan }}. {{ $ind->pertanyaan }}</legend>

							@if($jenis && $sistem['hari_data'] > 0 && $sistem[$jenis] !== null)
								<div class="alert alert-info py-2 small">
									Menurut pembukuan Anda, {{ $jenis === 'omzet' ? 'omzet 12 bulan terakhir' : 'modal usaha saat ini' }}
									<strong>{{ rupiah($sistem[$jenis]) }}</strong>
									(data tersedia {{ $sistem['hari_data'] }} hari).
									Ubah pilihan jika tidak sesuai.
								</div>
							@elseif($info['sumber'] === 'sebelumnya')
								<div class="text-muted small mb-2">Terisi dari jawaban Anda sebelumnya. Ubah jika sudah berbeda.</div>
							@endif

							@foreach($ind->pilihan as $p)
								<div class="form-check mb-2">
									<input class="form-check-input" type="radio"
										name="jawaban[{{ $ind->id }}]" id="p{{ $p->id }}" value="{{ $p->id }}"
										@checked($terpilih === (string) $p->id)
										@if($loop->first) required @endif>
									<label class="form-check-label" for="p{{ $p->id }}">
										{{ $p->teks }}
										@if($p->contoh)
											<span class="d-block text-muted small fst-italic">{{ $p->contoh }}</span>
										@endif
									</label>
								</div>
							@endforeach

							@isset($sebelumnya[$ind->id])
								<div class="text-muted small mt-2">Jawaban sebelumnya: {{ $sebelumnya[$ind->id] }}</div>
							@endisset
						</fieldset>
					</div>
				</div>
			@endforeach

			<button type="submit" class="btn btn-primary" id="btnKirim">Kirim penilaian ulang</button>
		</form>
	@endif
@endsection

@push('scripts')
<script>
	// Cegah klik ganda: tombol dikunci setelah formulir lolos pemeriksaan browser.
	var form = document.getElementById('formPenilaianUlang');
	if (form) {
		form.addEventListener('submit', function () {
			var t = document.getElementById('btnKirim');
			t.disabled = true; t.textContent = 'Mengirim...';
		});
	}
</script>
@endpush
