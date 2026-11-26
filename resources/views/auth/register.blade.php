@extends('layouts.auth')

@section('title', 'Daftar UMKM | MINECT')

@push('styles')
	<link href="{{ asset('css/minect-theme/register.css') }}" rel="stylesheet">
@endpush

@php
	// Jika ada galat hanya di kuesioner/persetujuan, buka langkah 2; selain itu langkah 1.
	$kunciGalat   = collect($errors->keys());
	$bukaLangkah2 = $kunciGalat->isNotEmpty()
		&& $kunciGalat->every(fn ($k) => str_starts_with($k, 'jawaban') || $k === 'persetujuan_data_pribadi');
@endphp

@section('content')
<main class="mn-center">
	<img class="mn-center-logo" src="{{ asset('assets/img/logo.png') }}" alt="MINECT Logo">
	<h1 class="mn-title">Daftar UMKM</h1>
	<p class="mn-lead">Isi data usaha, lalu jawab kuesioner singkat.</p>

	<ol class="mn-steps" aria-label="Langkah pendaftaran">
		<li id="tanda1" @unless($bukaLangkah2) aria-current="step" @endunless><b>1</b><span>Data pemilik dan usaha</span></li>
		<li id="tanda2" @if($bukaLangkah2) aria-current="step" @endif><b>2</b><span>Kuesioner</span></li>
	</ol>

	@if($errors->any())
		<div class="mn-notice mn-notice-error mn-panel-wide" role="alert" style="width: 100%;">
			<ul style="margin: 0; padding-left: 1.1rem;">
				@foreach($errors->all() as $pesan)
					<li>{{ $pesan }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<form id="formDaftar" method="POST" action="{{ route('register.process') }}" class="mn-panel mn-panel-wide">
		@csrf

		{{-- ============ LANGKAH 1: DATA ============ --}}
		<div id="langkah1" @if($bukaLangkah2) hidden @endif>
			<h2 class="mn-section-title">Data pemilik</h2>
			<div class="mn-grid">
				<div class="mn-field">
					<label class="form-label" for="nama_pemilik">Nama pemilik</label>
					<input type="text" id="nama_pemilik" name="nama_pemilik" class="form-control"
						value="{{ old('nama_pemilik') }}" maxlength="150" required>
				</div>

				<div class="mn-field">
					<label class="form-label" for="email">Email (dipakai untuk login)</label>
					<input type="email" id="email" name="email" class="form-control"
						value="{{ old('email') }}" maxlength="150" required>
				</div>

				<div class="mn-field">
					<label class="form-label" for="username">Username</label>
					<input type="text" id="username" name="username" class="form-control"
						value="{{ old('username') }}" pattern="[a-z0-9_]{4,30}" maxlength="30" required>
					<div class="form-text">Huruf kecil, angka, dan garis bawah, 4-30 karakter. Contoh: warung_budi</div>
				</div>

				<div class="mn-field">
					<label class="form-label" for="no_wa">Nomor WhatsApp</label>
					<input type="tel" id="no_wa" name="no_wa" class="form-control"
						value="{{ old('no_wa') }}" placeholder="081234567890" maxlength="20" required>
				</div>
			</div>

			<h2 class="mn-section-title">Data usaha</h2>
			<div class="mn-grid">
				<div class="mn-field">
					<label class="form-label" for="nama_usaha">Nama usaha</label>
					<input type="text" id="nama_usaha" name="nama_usaha" class="form-control"
						value="{{ old('nama_usaha') }}" maxlength="150" required>
				</div>

				<div class="mn-field">
					<label class="form-label" for="no_telepon">Telepon usaha (boleh dikosongkan)</label>
					<input type="tel" id="no_telepon" name="no_telepon" class="form-control"
						value="{{ old('no_telepon') }}" maxlength="20">
				</div>

				<div class="mn-field mn-span">
					<label class="form-label" for="alamat">Alamat usaha</label>
					<textarea id="alamat" name="alamat" rows="2" class="form-control"
						maxlength="500" required>{{ old('alamat') }}</textarea>
				</div>
			</div>

			<div class="mn-field">
				<span class="form-label" style="display: block;">Jenis usaha</span>
				<div class="mn-choices">
					<label class="mn-choice" for="rumpunBarang">
						<input type="radio" name="rumpun" id="rumpunBarang" value="barang" @checked(old('rumpun') === 'barang') required>
						<span>Barang (menjual produk)</span>
					</label>
					<label class="mn-choice" for="rumpunJasa">
						<input type="radio" name="rumpun" id="rumpunJasa" value="jasa" @checked(old('rumpun') === 'jasa')>
						<span>Jasa (memberi layanan)</span>
					</label>
				</div>
			</div>

			<div class="mn-field">
				<label class="form-label" for="jenis_usaha_id">Kategori usaha</label>
				<select id="jenis_usaha_id" name="jenis_usaha_id" class="form-select" required>
					<option value="">-- Pilih kategori usaha --</option>
					@foreach($jenisUsaha as $j)
						<option value="{{ $j->id }}" data-untuk="{{ $j->rumpun_bawaan }}"
							@selected(old('jenis_usaha_id') == $j->id)>{{ $j->nama }}</option>
					@endforeach
				</select>
				<div class="form-text">Pilih jenis usaha lebih dulu.</div>
			</div>

			{{-- Jenis barang: tanya produksi --}}
			<div id="kotakBarang" class="mn-field" hidden>
				<span class="form-label" style="display: block;">Apakah usaha Anda mengolah bahan menjadi produk?</span>
				<div class="mn-choices">
					<label class="mn-choice" for="olahYa">
						<input type="radio" name="mengolah_bahan" id="olahYa" value="1" @checked(old('mengolah_bahan') === '1')>
						<span>Ya</span>
					</label>
					<label class="mn-choice" for="olahTidak">
						<input type="radio" name="mengolah_bahan" id="olahTidak" value="0" @checked(old('mengolah_bahan') === '0')>
						<span>Tidak</span>
					</label>
				</div>
			</div>

			{{-- Jenis jasa: tanya persediaan --}}
			<div id="kotakJasa" class="mn-field" hidden>
				<span class="form-label" style="display: block;">Apakah usaha Anda memiliki persediaan barang?</span>
				<div class="mn-choices">
					<label class="mn-choice" for="stokYa">
						<input type="radio" name="punya_persediaan" id="stokYa" value="1" @checked(old('punya_persediaan') === '1')>
						<span>Ya, ada</span>
					</label>
					<label class="mn-choice" for="stokTidak">
						<input type="radio" name="punya_persediaan" id="stokTidak" value="0" @checked(old('punya_persediaan') === '0')>
						<span>Tidak ada</span>
					</label>
				</div>
			</div>

			<div class="mn-actions">
				<button type="button" id="btnLanjut" class="mn-submit">Lanjut ke kuesioner</button>
			</div>
		</div>

		{{-- ============ LANGKAH 2: KUESIONER ============ --}}
		<div id="langkah2" @unless($bukaLangkah2) hidden @endunless>
			<h2 class="mn-section-title">Langkah 2 dari 2: Kuesioner</h2>

			@foreach($indikator as $ind)
				<fieldset class="mn-question">
					<legend>{{ $ind->urutan }}. {{ $ind->pertanyaan }}</legend>
					<div class="mn-choices mn-choices-column">
						@foreach($ind->pilihan as $p)
							<label class="mn-choice" for="p{{ $p->id }}">
								<input type="radio" name="jawaban[{{ $ind->id }}]" id="p{{ $p->id }}" value="{{ $p->id }}"
									@checked((string) old('jawaban.' . $ind->id) === (string) $p->id)
									@if($loop->first) required @endif>
								<span>
									{{ $p->teks }}
									@if($p->contoh)
										<small>{{ $p->contoh }}</small>
									@endif
								</span>
							</label>
						@endforeach
					</div>
				</fieldset>
			@endforeach

			<label class="mn-choice" for="persetujuan_data_pribadi">
				<input type="checkbox" id="persetujuan_data_pribadi"
					name="persetujuan_data_pribadi" value="1" @checked(old('persetujuan_data_pribadi')) required>
				<span>Saya setuju data saya diproses oleh Admin KADIN untuk keperluan pendaftaran dan pembinaan UMKM.</span>
			</label>

			<div class="mn-actions">
				<button type="submit" id="btnKirim" class="mn-submit">Kirim pendaftaran</button>
				<button type="button" id="btnKembali" class="mn-secondary">Kembali ke data</button>
			</div>
		</div>
	</form>

	<p class="mn-below">Sudah punya akun? <a href="{{ route('umkm.login') }}">Masuk</a></p>
</main>
@endsection

@push('scripts')
	<script src="{{ asset('js/filter-kategori.js') }}"></script>
	<script src="{{ asset('js/register.js') }}"></script>
@endpush
