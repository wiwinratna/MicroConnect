<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Responsive Admin & Dashboard Template">
	<meta name="author" content="AdminKit">

	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />

	<title>Sign In</title>

	{{-- CSS ADMINKIT --}}
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">

	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
	<main class="d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row vh-100">
				<div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">

						<div class="text-center mt-4">
							<h1 class="h2">Welcome back!</h1>
							<p class="lead">
								Sign in to your account to continue
							</p>
						</div>

						<div class="card">
							<div class="card-body">
								<div class="m-sm-3">

									{{-- ERROR MESSAGE --}}
									@if($errors->any())
										<div class="alert alert-danger">
											{{ $errors->first() }}
										</div>
									@endif

									<form method="POST" action="{{ $formAction ?? route('umkm.login.process') }}">
										@csrf

										<div class="mb-3">
											<label class="form-label">Email</label>
											<input class="form-control form-control-lg" 
												   type="email" 
												   name="email"
												   value="{{ old('email') }}" 
												   placeholder="Enter your email" 
												   required 
											/>
										</div>

										<div class="mb-3">
											<label class="form-label">Password</label>
											<input class="form-control form-control-lg" 
												   type="password"
												   name="password"
												   placeholder="Enter your password" 
												   required
											/>
										</div>

										<div class="form-check align-items-center">
											<input id="customControlInline"
											       type="checkbox" 
											       class="form-check-input" 
											       name="remember">
											<label class="form-check-label text-small" 
											       for="customControlInline">
												Remember me
											</label>
										</div>

										<div class="d-grid gap-2 mt-3">
											<button type="submit" class="btn btn-lg btn-primary">
												Sign in
											</button>
										</div>

									</form>

								</div>
							</div>
						</div>

						<div class="text-center mb-3">
							Don't have an account? 
							<a href="{{ route('register') }}">Sign up</a>
						</div>

					</div>
				</div>
			</div>
		</div>
	</main>

	{{-- JS ADMINKIT --}}
	<script src="{{ asset('js/app.js') }}"></script>

</body>
</html>
