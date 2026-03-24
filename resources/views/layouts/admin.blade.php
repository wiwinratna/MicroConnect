{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<title>@yield('title', 'Dashboard admin')</title>

	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />

	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/custom-polish.css') }}" rel="stylesheet">

	@stack('styles')
</head>
<body>
	<div class="wrapper">

		{{-- SIDEBAR admin --}}
		@include('partials.admin.sidebar')

		<div class="main">

			{{-- NAVBAR admin --}}
			@include('partials.admin.navbar')

			<main class="content">
				<div class="container-fluid p-0">
					@yield('content')
				</div>
			</main>

			{{-- FOOTER admin --}}
			@include('partials.admin.footer')

		</div>
	</div>

	<script src="{{ asset('js/app.js') }}"></script>
	@stack('scripts')
</body>
</html>
