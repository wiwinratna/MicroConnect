@extends('layouts.admin') 

@section('content')
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Dashboard UMKM</h1>
    <p>Selamat datang di Sistem Monitoring UMKM KADIN Bengkalis.</p>

    {{-- contoh card kecil aja biar kerasa "dashboard" --}}
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-1">Status Akun</h5>
                    <p class="mb-0">Terdaftar sebagai <strong>Pelaku Usaha (UMKM)</strong>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
