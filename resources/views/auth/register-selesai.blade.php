<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Pendaftaran Diterima | MINECT</title>

	<link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
	<main class="d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row vh-100">
				<div class="col-sm-10 col-md-8 col-lg-6 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">

						<div class="card">
							<div class="card-body text-center p-4">
								<h1 class="h3 mb-3">Pendaftaran diterima</h1>
								<p class="mb-2">
									Terima kasih. Data dan jawaban Anda sudah kami terima dan sedang
									menunggu verifikasi dari Admin KADIN.
								</p>
								<p class="mb-4">
									Setelah disetujui, akun Anda akan diaktifkan. Silakan masuk memakai
									email yang Anda daftarkan.
								</p>
								<a href="{{ route('umkm.login') }}" class="btn btn-lg btn-primary">Ke halaman masuk</a>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</main>
</body>

</html>
