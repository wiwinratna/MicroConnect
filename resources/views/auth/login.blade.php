@extends('layouts.auth')

@section('title', 'Login UMKM | Sistem KADIN')
@section('description', 'Login Sistem UMKM KADIN - Kelola usaha mikro kecil menengah Anda')

@push('scripts')
    <script src="https://unpkg.com/@lottiefiles/lottie-player@2.0.8/dist/lottie-player.js"></script>
@endpush

@section('content')
<div class="mn-split">
    <aside class="mn-brand">
        <img class="mn-brand-logo" src="{{ asset('assets/img/logo-white.png') }}" alt="MINECT Logo">
        <div class="mn-brand-art">
            <lottie-player src="{{ asset('assets/lottie/login-umkm.json') }}" background="transparent" speed="1" loop autoplay></lottie-player>
        </div>
        <h2>Portal UMKM Binaan</h2>
        <p>Kelola pencatatan dan pelaporan usaha Anda dalam satu pintu yang terintegrasi.</p>
    </aside>

    <main class="mn-pane">
        <div class="mn-panel">
            <span class="mn-eyebrow">UMKM Access</span>
            <h1 class="mn-title">Masuk ke Akun</h1>
            <p class="mn-lead">Silakan login untuk mengelola bisnis Anda.</p>

            @if($errors->any())
                <div class="mn-notice mn-notice-error" role="alert">⚠️ {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ $formAction ?? route('umkm.login.process') }}">
                @csrf
                <div class="mn-field">
                    <label class="form-label" for="email">Email Anda</label>
                    <input class="form-control" type="email" id="email" name="email" autocomplete="username" value="{{ old('email') }}" placeholder="contoh@email.com" required autofocus />
                </div>

                <div class="mn-field">
                    <label class="form-label" for="passwordInput">Password</label>
                    <div class="mn-password">
                        <input class="form-control" type="password" name="password" id="passwordInput" placeholder="Masukkan password" required />
                        <button type="button" data-toggle-password="passwordInput">Lihat</button>
                    </div>
                </div>

                <label class="mn-check">
                    <input type="checkbox" name="remember">
                    <span>Ingat login saya</span>
                </label>

                <button type="submit" class="mn-submit">Masuk ke Sistem</button>
            </form>

            <p class="mn-below">Belum mendaftar? <a href="{{ route('register') }}">Buat akun UMKM</a></p>
            <p class="mn-copy">© {{ date('Y') }} MINECT · MicroConnect</p>
        </div>
    </main>
</div>
@endsection
