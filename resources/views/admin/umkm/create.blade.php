@extends('layouts.admin')
@section('title', 'Daftarkan UMKM Baru')

@section('content')
<div class="container-fluid p-0 d-flex justify-content-center">
    <div style="max-width: 900px; width: 100%;">

        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('admin.umkm.index') }}" class="btn bg-white shadow-sm border rounded-circle p-2 me-3" title="Kembali">
                <i data-feather="arrow-left" class="text-dark"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">Daftarkan <strong>UMKM Baru</strong></h1>
                <p class="text-muted small mb-0">Lengkapi data akun dan profil usaha UMKM binaan dalam satu langkah.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.umkm.store') }}">
            @csrf
            <div class="row g-4 mb-4">

                {{-- Akun --}}
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <i data-feather="lock" class="me-2 text-primary" style="width: 16px;"></i> Akun Pengguna
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Nama Pemilik <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control border-0 bg-light rounded-3 shadow-none custom-input" required value="{{ old('name') }}" placeholder="Nama sesuai KTP">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark">Email Login <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control border-0 bg-light rounded-3 shadow-none custom-input" required value="{{ old('email') }}" placeholder="email@contoh.com">
                            </div>
                            <div class="p-3 bg-primary-subtle rounded-3 mt-4 border border-primary border-opacity-10">
                                <div class="d-flex small text-primary">
                                    <i data-feather="key" class="me-2" style="width: 14px; flex-shrink: 0;"></i>
                                    <div>Akun memakai kata sandi awal yang sama dengan akun dari pendaftaran, dan dikirim ke email di atas.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data UMKM --}}
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <i data-feather="briefcase" class="me-2 text-primary" style="width: 16px;"></i> Informasi Usaha
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-dark">Nama Brand / Usaha <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_usaha" required class="form-control border-0 bg-light rounded-3 shadow-none custom-input" value="{{ old('nama_usaha') }}" placeholder="Contoh: Kedai Kopi Makmur">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-dark">Kontak WhatsApp</label>
                                    <input type="text" name="no_whatsapp" class="form-control border-0 bg-light rounded-3 shadow-none custom-input" value="{{ old('no_whatsapp') }}" placeholder="08XXXXXXXXXX">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-dark">Nomor Induk Berusaha (NIB)</label>
                                    <input type="text" name="nib" class="form-control border-0 bg-light rounded-3 shadow-none custom-input" value="{{ old('nib') }}" placeholder="Masukkan 13 digit NIB">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-dark">Alamat Operasional</label>
                                    <input type="text" name="alamat" class="form-control border-0 bg-light rounded-3 shadow-none custom-input" value="{{ old('alamat') }}" placeholder="Jl. Raya Utama No. 123">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @include('admin.umkm.partials.profil-kuesioner')

                {{-- Konfigurasi --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden border-top border-primary border-4">
                        <div class="card-body p-4">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-4">
                                    <h5 class="fw-bold mb-1 text-dark">Konfigurasi</h5>
                                    <p class="text-muted small mb-0">Pengaturan akuntansi & inventori</p>
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-1">Pencatatan Stok</label>
                                    <select name="recording_method" class="form-select border-0 bg-light rounded-3 shadow-none custom-input">
                                        <option value="periodik" {{ old('recording_method', 'periodik') === 'periodik' ? 'selected' : '' }}>Periodik</option>
                                        <option value="perpetual" {{ old('recording_method') === 'perpetual' ? 'selected' : '' }}>Perpetual</option>
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-1">Penilaian Persediaan</label>
                                    <select name="inventory_method" class="form-select border-0 bg-light rounded-3 shadow-none custom-input">
                                        <option value="Average" {{ old('inventory_method', 'Average') === 'Average' ? 'selected' : '' }}>Average</option>
                                        <option value="FIFO" {{ old('inventory_method') === 'FIFO' ? 'selected' : '' }}>FIFO</option>
                                        <option value="LIFO" {{ old('inventory_method') === 'LIFO' ? 'selected' : '' }}>LIFO</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm py-3 fw-bold">
                    Konfirmasi & Daftarkan UMKM <i data-feather="user-plus" class="ms-2" style="width: 18px;"></i>
                </button>
            </div>
        </form>

    </div>
</div>

<style>
    .bg-primary-subtle { background-color: rgba(23, 107, 101, 0.08) !important; }
    .custom-input { transition: all 0.2s ease-in-out; }
    .custom-input:focus { background-color: var(--mn-bg-soft) !important; transform: translateY(-1px); }
    .card { border: none !important; }
</style>
@endsection
