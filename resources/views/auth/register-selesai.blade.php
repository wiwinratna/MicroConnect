@extends('layouts.auth')

@section('title', 'Pendaftaran Diterima | MINECT')

@section('content')
<main class="mn-center">
    <img class="mn-center-logo" src="{{ asset('assets/img/logo.png') }}" alt="MINECT Logo">

    <div class="mn-panel" style="text-align: center;">
        <div class="mn-success-icon" aria-hidden="true">✓</div>
        <h1 class="mn-title">Pendaftaran diterima</h1>
        <p class="mn-lead">
            Terima kasih. Data dan jawaban Anda sudah kami terima dan sedang
            menunggu verifikasi dari Admin KADIN.
        </p>
        <p class="mn-lead">
            Setelah disetujui, akun Anda akan diaktifkan. Silakan masuk memakai
            ID login di bawah ini atau email yang Anda daftarkan.
        </p>
        @if(session('id_login'))
            <p class="mn-notice mn-notice-warn">ID login Anda: <strong>{{ session('id_login') }}</strong><br>Catat ID ini.</p>
        @endif
        <a href="{{ route('umkm.login') }}" class="mn-submit"
           style="display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none;">Ke halaman masuk</a>
    </div>
</main>
@endsection
