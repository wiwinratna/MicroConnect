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

	@stack('styles')
</head>
<body>
	@php
		$user = auth()->user();
		$level = 1; // Default
		if ($user && $user->umkm && $user->umkm->level) {
			
			// Misal kode level 'LVL1' atau id 1. Kita mapping simple ID ke angka level
			// karena data realnya di UmkmLevel ID 1, 2, 3 biasanya merepresentasikan Level.
			$level = (int) preg_replace('/[^0-9]/', '', $user->umkm->level->kode) ?: $user->umkm->level_id;
			
			// Fallback proteksi
			if ($level < 1) $level = 1;
		}
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
	@stack('scripts')
</body>
</html>
