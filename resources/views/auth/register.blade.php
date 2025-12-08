<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Sign Up</title>

	<link rel="shortcut icon" href="{{ asset('img/icons/icon-48x48.png') }}" />
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body>
	<main class="d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row vh-100">
				<div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">

						<div class="text-center mt-4">
							<h1 class="h2">Create Account</h1>
							<p class="lead">Register as Pelaku UMKM</p>
						</div>

						<div class="card">
							<div class="card-body">
								<div class="m-sm-3">

									@if($errors->any())
										<div class="alert alert-danger">
											{{ $errors->first() }}
										</div>
									@endif

									<form method="POST" action="{{ route('register.process') }}">
										@csrf

										<div class="mb-3">
											<label class="form-label">Name</label>
											<input type="text" name="name" class="form-control form-control-lg"
												   value="{{ old('name') }}" required>
										</div>

										<div class="mb-3">
											<label class="form-label">Email</label>
											<input type="email" name="email" class="form-control form-control-lg"
												   value="{{ old('email') }}" required>
										</div>

										<div class="mb-3">
											<label class="form-label">Password</label>
											<input type="password" name="password" class="form-control form-control-lg" required>
										</div>

										<div class="mb-3">
											<label class="form-label">Confirm Password</label>
											<input type="password" name="password_confirmation" class="form-control form-control-lg" required>
										</div>

										<div class="d-grid gap-2 mt-3">
											<button type="submit" class="btn btn-lg btn-primary w-100">
												Sign Up
											</button>
										</div>
									</form>

								</div>
							</div>
						</div>

						<div class="text-center mt-3">
							Already have an account? <a href="{{ route('login') }}">Sign In</a>
						</div>

					</div>
				</div>
			</div>
		</div>
	</main>

	<script src="{{ asset('js/app.js') }}"></script>
</body>

</html>
