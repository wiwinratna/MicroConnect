{{-- resources/views/layouts/umkm.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<title>@yield('title', 'Dashboard UMKM')</title>

	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />

	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/custom-polish.css') }}" rel="stylesheet">
    <link href="{{ asset('css/minect-theme/main.css') }}" rel="stylesheet">


	@stack('styles')
</head>
<body class="mn-app">
	@php
		$umkmLevel = feature_level();
	@endphp
	<div class="wrapper">

		{{-- SIDEBAR UMKM --}}
		@include('partials.umkm.sidebar')

		<div class="main">

			{{-- NAVBAR UMKM --}}
			@include('partials.umkm.navbar')

			<main class="content">
				<div class="container-fluid p-0">
					@yield('content')
				</div>
			</main>

			{{-- FOOTER UMKM --}}
			@include('partials.umkm.footer')

		</div>
	</div>

	<script src="{{ asset('js/app.js') }}"></script>
	<script src="https://unpkg.com/lucide@0.469.0/dist/umd/lucide.min.js"></script>
	<script src="{{ asset('js/layout.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script>
		// Global Elegant Toast / Alert
		const Toast = Swal.mixin({
			toast: true,
			position: 'top-end',
			showConfirmButton: false,
			timer: 6000,
			timerProgressBar: true,
			background: '#fff',
			color: '#26364D',
			customClass: {
				popup: 'rounded-4 shadow-sm border border-light'
			},
			didOpen: (toast) => {
				toast.addEventListener('mouseenter', Swal.stopTimer)
				toast.addEventListener('mouseleave', Swal.resumeTimer)
			}
		});

		@if(session('success'))
			Toast.fire({
				icon: 'success',
				title: "{!! session('success') !!}"
			});
		@endif

		@if(session('error'))
			Toast.fire({
				icon: 'error',
				title: "{!! session('error') !!}"
			});
		@endif

		@if(session('saran_ganti_sandi'))
			// Saran ganti kata sandi awal: tidak wajib, boleh dilewati.
			Swal.fire({
				icon: 'info',
				title: 'Ganti kata sandi?',
				html: 'Akun Anda masih memakai kata sandi awal. Sebaiknya diganti agar lebih aman, tetapi Anda boleh melewatinya.',
				showCancelButton: true,
				confirmButtonText: 'Ganti sekarang',
				cancelButtonText: 'Lewati',
				reverseButtons: true
			}).then((hasil) => {
				if (hasil.isConfirmed) {
					window.location.href = "{{ route('password.change') }}";
				} else {
					Swal.fire({
						icon: 'info',
						title: 'Tidak apa-apa',
						html: 'Kalau nanti ingin mengganti kata sandi, buka menu <b>Profil UMKM</b> di sidebar kiri, lalu isi bagian <b>Ubah password</b>.',
						confirmButtonText: 'Mengerti'
					});
				}
			});
		@endif

		// Konfirmasi hapus global
		document.addEventListener('DOMContentLoaded', function() {
			const deleteButtons = document.querySelectorAll('.btn-delete, form[method="POST"] button.text-danger');
			deleteButtons.forEach(btn => {
				const form = btn.closest('form');
				if(form && form.querySelector('input[name="_method"][value="DELETE"]')) {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						Swal.fire({
							title: 'Kamu yakin?',
							text: "Tindakan ini tidak dapat dibatalkan!",
							icon: 'warning',
							showCancelButton: true,
							confirmButtonColor: 'var(--mn-danger-bg)',
							cancelButtonColor: '#5C6A80',
							confirmButtonText: 'Ya, Hapus!',
							cancelButtonText: 'Batal',
							customClass: {
								confirmButton: 'btn btn-danger text-danger border-danger fw-bold px-4 py-2 rounded-3',
								cancelButton: 'btn btn-outline-secondary fw-bold px-4 py-2 rounded-3 ms-2',
								popup: 'rounded-4'
							}
						}).then((result) => {
							if (result.isConfirmed) {
								form.submit();
							}
						});
					});
				}
			});
		});
	</script>
	@stack('scripts')
</body>
</html>
