@extends('layouts.auth')

@section('title', 'Login Admin | KADIN Panel')
@section('description', 'Login Administrator KADIN - Panel Monitoring UMKM')

@push('scripts')
    <script src="https://unpkg.com/@lottiefiles/lottie-player@2.0.8/dist/lottie-player.js"></script>
@endpush

@section('content')
<div class="mn-split">
    <aside class="mn-brand">
        <img class="mn-brand-logo" src="{{ asset('assets/img/logo-white.png') }}" alt="MINECT Logo">
        <div class="mn-brand-art">
            <lottie-player src="{{ asset('assets/lottie/login-admin.json') }}" background="transparent" speed="1" loop autoplay></lottie-player>
        </div>
        <h2>Panel KADIN</h2>
        <p>Pantau performa, kesehatan, dan pertumbuhan UMKM binaan secara komprehensif.</p>
    </aside>

    <main class="mn-pane">
        <div class="mn-panel">
            <span class="mn-eyebrow">Administrator Access</span>
            <h1 class="mn-title">Login Dashboard</h1>
            <p class="mn-lead">Masuk dengan kredensial admin Anda.</p>

            @if($errors->any())
                <div class="mn-notice mn-notice-error" role="alert">⚠️ {{ $errors->first() }}</div>
            @endif

            @if(session('error'))
                <div class="mn-notice mn-notice-error" role="alert">⚠️ {{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.process') }}">
                @csrf
                <div class="mn-field">
                    <label class="form-label" for="email">Email Admin</label>
                    <input class="form-control" type="email" id="email" name="email" autocomplete="username" value="{{ old('email') }}" placeholder="admin@kadin.bengkalis" required autofocus />
                </div>

                <div class="mn-field">
                    <label class="form-label" for="passwordInput">Kata Sandi</label>
                    <div class="mn-password">
                        <input class="form-control" type="password" name="password" id="passwordInput" placeholder="Masukkan kata sandi" required />
                        <button type="button" data-toggle-password="passwordInput">Lihat</button>
                    </div>
                </div>

                <label class="mn-check">
                    <input type="checkbox" name="remember">
                    <span>Biarkan saya tetap login</span>
                </label>

                <button type="submit" class="mn-submit">Masuk ke Panel</button>
            </form>

            <div class="mn-notice mn-notice-warn" style="margin: 1.25rem 0 0; text-align: center;">
                🔐 Akses terbatas hanya untuk pengurus sah KADIN.<br>Setiap aktivitas akan dicatat dalam log sistem.
            </div>

            <p class="mn-copy">© {{ date('Y') }} MINECT · Hak Cipta Dilindungi</p>
        </div>
    </main>
</div>
@endsection
