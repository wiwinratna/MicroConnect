<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Daftar UMKM | MINECT</title>

	<link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

@php
	// Jika ada galat hanya di kuesioner/persetujuan, buka langkah 2; selain itu langkah 1.
	$kunciGalat   = collect($errors->keys());
	$bukaLangkah2 = $kunciGalat->isNotEmpty()
		&& $kunciGalat->every(fn ($k) => str_starts_with($k, 'jawaban') || $k === 'persetujuan_data_pribadi');
@endphp

<body>
	<main class="d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row">
				<div class="col-sm-12 col-md-10 col-lg-8 col-xl-7 mx-auto py-4">

					<div class="text-center mt-2 mb-3">
						<h1 class="h2">Daftar UMKM</h1>
						<p class="lead mb-0">Isi data usaha, lalu jawab kuesioner singkat.</p>
					</div>

					@if($errors->any())
						<div class="alert alert-danger">
							<ul class="mb-0 ps-3">
								@foreach($errors->all() as $pesan)
									<li>{{ $pesan }}</li>
								@endforeach
							</ul>
						</div>
					@endif

					<form id="formDaftar" method="POST" action="{{ route('register.process') }}">
						@csrf

						{{-- ============ LANGKAH 1: DATA ============ --}}
						<div id="langkah1" class="card" @if($bukaLangkah2) hidden @endif>
							<div class="card-body">
								<h2 class="h4 mb-3">Langkah 1 dari 2: Data pemilik dan usaha</h2>

								<div class="mb-3">
									<label class="form-label" for="nama_pemilik">Nama pemilik</label>
									<input type="text" id="nama_pemilik" name="nama_pemilik" class="form-control form-control-lg"
										value="{{ old('nama_pemilik') }}" maxlength="150" required>
								</div>

								<div class="mb-3">
									<label class="form-label" for="email">Email (dipakai untuk login)</label>
									<input type="email" id="email" name="email" class="form-control form-control-lg"
										value="{{ old('email') }}" maxlength="150" required>
								</div>

								<div class="mb-3">
									<label class="form-label" for="username">Username</label>
									<input type="text" id="username" name="username" class="form-control form-control-lg"
										value="{{ old('username') }}" pattern="[a-z0-9_]{4,30}" maxlength="30" required>
									<div class="form-text">Huruf kecil, angka, dan garis bawah, 4-30 karakter. Contoh: warung_budi</div>
								</div>

								<div class="mb-3">
									<label class="form-label" for="no_wa">Nomor WhatsApp</label>
									<input type="tel" id="no_wa" name="no_wa" class="form-control form-control-lg"
										value="{{ old('no_wa') }}" placeholder="081234567890" maxlength="20" required>
								</div>

								<div class="mb-3">
									<label class="form-label" for="nama_usaha">Nama usaha</label>
									<input type="text" id="nama_usaha" name="nama_usaha" class="form-control form-control-lg"
										value="{{ old('nama_usaha') }}" maxlength="150" required>
								</div>

								<div class="mb-3">
									<label class="form-label" for="alamat">Alamat usaha</label>
									<textarea id="alamat" name="alamat" rows="2" class="form-control form-control-lg"
										maxlength="500" required>{{ old('alamat') }}</textarea>
								</div>

								<div class="mb-3">
									<label class="form-label" for="no_telepon">Telepon usaha (boleh dikosongkan)</label>
									<input type="tel" id="no_telepon" name="no_telepon" class="form-control form-control-lg"
										value="{{ old('no_telepon') }}" maxlength="20">
								</div>

								<div class="mb-3">
									<label class="form-label" for="jenis_usaha_id">Jenis usaha</label>
									<select id="jenis_usaha_id" name="jenis_usaha_id" class="form-select form-select-lg" required>
										<option value="">-- Pilih jenis usaha --</option>
										@foreach($jenisUsaha as $j)
											<option value="{{ $j->id }}" data-bawaan="{{ $j->rumpun_bawaan }}"
												@selected(old('jenis_usaha_id') == $j->id)>{{ $j->nama }}</option>
										@endforeach
									</select>
								</div>

								{{-- Usaha kreatif: calon memilih barang atau jasa --}}
								<div id="kotakRumpun" class="mb-3" hidden>
									<label class="form-label d-block">Hasil usaha Anda berupa apa?</label>
									<div class="form-check">
										<input class="form-check-input" type="radio" name="rumpun" id="rumpunBarang" value="barang" @checked(old('rumpun') === 'barang')>
										<label class="form-check-label" for="rumpunBarang">Barang (produk)</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="radio" name="rumpun" id="rumpunJasa" value="jasa" @checked(old('rumpun') === 'jasa')>
										<label class="form-check-label" for="rumpunJasa">Jasa (layanan)</label>
									</div>
								</div>

								{{-- Rumpun barang: tanya produksi --}}
								<div id="kotakBarang" class="mb-3" hidden>
									<label class="form-label d-block">Apakah usaha Anda mengolah bahan menjadi produk?</label>
									<div class="form-check">
										<input class="form-check-input" type="radio" name="mengolah_bahan" id="olahYa" value="1" @checked(old('mengolah_bahan') === '1')>
										<label class="form-check-label" for="olahYa">Ya</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="radio" name="mengolah_bahan" id="olahTidak" value="0" @checked(old('mengolah_bahan') === '0')>
										<label class="form-check-label" for="olahTidak">Tidak</label>
									</div>
								</div>

								{{-- Rumpun jasa: tanya persediaan --}}
								<div id="kotakJasa" class="mb-3" hidden>
									<label class="form-label d-block">Apakah usaha Anda memiliki persediaan barang?</label>
									<div class="form-check">
										<input class="form-check-input" type="radio" name="punya_persediaan" id="stokYa" value="1" @checked(old('punya_persediaan') === '1')>
										<label class="form-check-label" for="stokYa">Ya, ada</label>
									</div>
									<div class="form-check">
										<input class="form-check-input" type="radio" name="punya_persediaan" id="stokTidak" value="0" @checked(old('punya_persediaan') === '0')>
										<label class="form-check-label" for="stokTidak">Tidak ada</label>
									</div>
								</div>

								<div class="d-grid mt-4">
									<button type="button" id="btnLanjut" class="btn btn-lg btn-primary">Lanjut ke kuesioner</button>
								</div>
							</div>
						</div>

						{{-- ============ LANGKAH 2: KUESIONER ============ --}}
						<div id="langkah2" class="card" @unless($bukaLangkah2) hidden @endunless>
							<div class="card-body">
								<h2 class="h4 mb-3">Langkah 2 dari 2: Kuesioner</h2>

								@foreach($indikator as $ind)
									<fieldset class="mb-4">
										<legend class="h6">{{ $ind->urutan }}. {{ $ind->pertanyaan }}</legend>
										@foreach($ind->pilihan as $p)
											<div class="form-check mb-2">
												<input class="form-check-input" type="radio"
													name="jawaban[{{ $ind->id }}]" id="p{{ $p->id }}" value="{{ $p->id }}"
													@checked((string) old('jawaban.' . $ind->id) === (string) $p->id)
													@if($loop->first) required @endif>
												<label class="form-check-label" for="p{{ $p->id }}">
													{{ $p->teks }}
													@if($p->contoh)
														<span class="d-block text-muted small fst-italic">{{ $p->contoh }}</span>
													@endif
												</label>
											</div>
										@endforeach
									</fieldset>
								@endforeach

								<div class="form-check mb-3">
									<input class="form-check-input" type="checkbox" id="persetujuan_data_pribadi"
										name="persetujuan_data_pribadi" value="1" @checked(old('persetujuan_data_pribadi')) required>
									<label class="form-check-label" for="persetujuan_data_pribadi">
										Saya setuju data saya diproses oleh Admin KADIN untuk keperluan pendaftaran dan pembinaan UMKM.
									</label>
								</div>

								<div class="d-grid gap-2 mt-4">
									<button type="submit" id="btnKirim" class="btn btn-lg btn-primary">Kirim pendaftaran</button>
									<button type="button" id="btnKembali" class="btn btn-lg btn-outline-secondary">Kembali ke data</button>
								</div>
							</div>
						</div>
					</form>

					<div class="text-center mt-3">
						Sudah punya akun? <a href="{{ route('umkm.login') }}">Masuk</a>
					</div>

				</div>
			</div>
		</div>
	</main>

	<script src="{{ asset('js/app.js') }}"></script>
	<script>
	(function () {
		const form = document.getElementById('formDaftar');
		const l1 = document.getElementById('langkah1'), l2 = document.getElementById('langkah2');
		const jenis = document.getElementById('jenis_usaha_id');
		const kotak = {
			rumpun: document.getElementById('kotakRumpun'),
			barang: document.getElementById('kotakBarang'),
			jasa: document.getElementById('kotakJasa'),
		};

		// Tampilkan hanya kotak yang relevan; yang tersembunyi dinonaktifkan agar tidak ikut terkirim.
		function atur(box, tampil) {
			box.hidden = !tampil;
			box.querySelectorAll('input').forEach(function (el) {
				el.disabled = !tampil;
				el.required = tampil && el.type === 'radio' && el === box.querySelector('input');
			});
		}

		function segarkan() {
			const opsi = jenis.options[jenis.selectedIndex];
			const bawaan = opsi ? opsi.dataset.bawaan : '';
			let rumpun = (bawaan === 'barang' || bawaan === 'jasa') ? bawaan : '';

			if (bawaan === 'ditentukan_umkm') {
				const dipilih = form.querySelector('input[name="rumpun"]:checked');
				rumpun = dipilih ? dipilih.value : '';
			}

			atur(kotak.rumpun, bawaan === 'ditentukan_umkm');
			atur(kotak.barang, rumpun === 'barang');
			atur(kotak.jasa, rumpun === 'jasa');
		}

		jenis.addEventListener('change', segarkan);
		form.querySelectorAll('input[name="rumpun"]').forEach(function (r) { r.addEventListener('change', segarkan); });
		segarkan();

		document.getElementById('btnLanjut').addEventListener('click', function () {
			const isian = l1.querySelectorAll('input, select, textarea');
			for (const el of isian) {
				if (!el.disabled && !el.reportValidity()) { return; }
			}
			l1.hidden = true; l2.hidden = false; window.scrollTo(0, 0);
		});

		document.getElementById('btnKembali').addEventListener('click', function () {
			l2.hidden = true; l1.hidden = false; window.scrollTo(0, 0);
		});

		// Cegah klik ganda: tombol dikunci setelah formulir lolos pemeriksaan browser.
		form.addEventListener('submit', function () {
			const t = document.getElementById('btnKirim');
			t.disabled = true; t.textContent = 'Mengirim...';
		});
	})();
	</script>
</body>

</html>
