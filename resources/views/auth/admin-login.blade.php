<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #0f172a;
        }
        .login-card {
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(0,0,0,.35);
        }
    </style>
</head>
<body>

<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="col-md-4">

        <div class="card login-card border-0">
            <div class="card-body p-4">

                <h4 class="fw-bold mb-1 text-center">Admin Panel</h4>
                <p class="text-muted text-center mb-4">
                    Login khusus administrator sistem
                </p>

                {{-- error --}}
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.process') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password"
                               name="password"
                               class="form-control"
                               required>
                    </div>

                    <button class="btn btn-primary w-100">
                        Login Admin
                    </button>
                </form>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        Sistem UMKM KADIN © 2025
                    </small>
                </div>

            </div>
        </div>

    </div>
</div>

</body>
</html>
