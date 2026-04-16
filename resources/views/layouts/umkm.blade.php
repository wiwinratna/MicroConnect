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
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/custom-polish.css') }}" rel="stylesheet">
    <link href="{{ asset('css/minect-theme/main.css') }}" rel="stylesheet">

    <style>
        /* Modern App Layout — Fixed Sidebar & Navbar */
        html, body { height: 100%; overflow: hidden; }
        .wrapper { height: 100vh; overflow: hidden; display: flex; width: 100%; }
        
        /* Sidebar stays fixed by nature of flex */
        .sidebar, .sidebar-content { min-width: 220px !important; max-width: 220px !important; height: 100vh; }
        .sidebar.collapsed { margin-left: -220px !important; }
        
        /* Main container is the vertical flex parent */
        .main { 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
            height: 100vh; 
            overflow: hidden; 
            min-width: 0; /* Prevents flex children from pushing width */
        }
        
        /* This makes the navbar stick to the top of the .main container */
        .navbar { flex-shrink: 0; }
        
        /* The content area is the one that actually scrolls */
        .content { 
            flex: 1; 
            overflow-y: auto; 
            padding: 1.25rem 1.25rem 2rem !important; 
            background: #f8fafc;
        }
        
        .hamburger { padding: 0; }
    </style>

	@stack('styles')
</head>
<body>
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
			color: '#1F2937',
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
							cancelButtonColor: '#6B7280',
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
