@extends('layouts.auth')

@section('title', 'Ganti Password | Sistem KADIN')

@section('content')
<main class="mn-center">
    <img class="mn-center-logo" src="{{ asset('assets/img/logo.png') }}" alt="MINECT Logo">

    <div class="mn-panel">
        <h1 class="mn-title">🔐 Ganti Password</h1>
        @if(auth()->user()->must_change_password)
            <p class="mn-lead">Password Anda bersifat sementara dan harus diganti</p>
            <div class="mn-notice mn-notice-warn">
                ⚠️ Akun Anda dibuat oleh Admin KADIN dengan password sementara.
                Demi keamanan, Anda <strong>wajib mengganti password</strong> sebelum menggunakan sistem.
            </div>
        @else
            <p class="mn-lead">Anda boleh mengganti password sekarang atau nanti</p>
            <div class="mn-notice mn-notice-warn">
                Akun Anda masih memakai password awal. Sebaiknya diganti, tetapi tidak wajib.
                Kalau dilewati, Anda bisa mengganti kapan saja lewat menu <strong>Profil UMKM</strong>.
            </div>
        @endif

        @if($errors->any())
            <div class="mn-notice mn-notice-error" role="alert">
                @foreach($errors->all() as $e)
                    {{ $e }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf

            <div class="mn-field">
                <label class="form-label" for="current_password">Password Lama</label>
                <div class="mn-password">
                    <input class="form-control" type="password" id="current_password" name="current_password" required
                           placeholder="Password yang Anda pakai untuk masuk" autofocus>
                    <button type="button" data-toggle-password="current_password">Lihat</button>
                </div>
            </div>

            <div class="mn-field">
                <label class="form-label" for="password">Password Baru</label>
                <div class="mn-password">
                    <input class="form-control" type="password" id="password" name="password" required
                           placeholder="Minimal 6 karakter" minlength="6">
                    <button type="button" data-toggle-password="password">Lihat</button>
                </div>
            </div>

            <div class="mn-field">
                <label class="form-label" for="password_confirmation">Konfirmasi Password Baru</label>
                <div class="mn-password">
                    <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required
                           placeholder="Ulangi password baru" minlength="6">
                    <button type="button" data-toggle-password="password_confirmation">Lihat</button>
                </div>
            </div>

            <button type="submit" class="mn-submit">Ganti Password &amp; Lanjutkan</button>
        </form>

        @unless(auth()->user()->must_change_password)
            <p class="mn-below">
                <a href="{{ auth()->user()->user_group === 'admin' ? route('admin.dashboard') : route('umkm.dashboard') }}">Nanti saja</a>
            </p>
        @endunless

        <p class="mn-copy">© {{ date('Y') }} Sistem UMKM KADIN · MicroConnect</p>
    </div>
</main>
@endsection
