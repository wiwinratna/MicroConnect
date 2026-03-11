<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Admin</title>
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
                <h4 class="fw-bold text-center mb-1">Register Admin</h4>
                <p class="text-muted text-center mb-4">Khusus administrator (wajib kode)</p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.register.process') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Admin</label>
                        <input name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Admin</label>
                        <input name="admin_code" class="form-control" required placeholder="Masukkan kode rahasia admin">
                    </div>

                    <button class="btn btn-primary w-100">Buat Admin</button>
                </form>

                <hr class="my-4">
                <div class="text-center">
                    <small class="text-muted">Sudah punya akun admin? <a href="{{ route('admin.login') }}">Login Admin</a></small>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
