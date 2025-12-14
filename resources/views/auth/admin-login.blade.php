<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:#0f172a}
        .cardx{border-radius:14px;box-shadow:0 20px 40px rgba(0,0,0,.35)}
        a{text-decoration:none}
    </style>
</head>
<body>
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card cardx border-0">
            <div class="card-body p-4">
                <h4 class="fw-bold text-center mb-1">Login Admin</h4>
                <p class="text-muted text-center mb-4">Masuk sebagai administrator</p>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.login.process') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button class="btn btn-primary w-100">Login Admin</button>
                </form>

                <hr class="my-4">
                <div class="text-center">
                    <small class="text-muted">
                        Belum punya akun admin?
                        <a href="{{ route('admin.register') }}">Register Admin</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
