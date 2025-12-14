@extends('layouts.umkm')

@section('title', 'Profil UMKM')

@section('content')
    <div class="profile-wrapper">

        <div class="text-center mb-4">
            <h1 class="h3 mb-1"><strong>Profil</strong> UMKM</h1>
            <p class="text-muted mb-0">
                Lengkapi data akun & informasi usaha kamu.
            </p>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- ERROR MESSAGE --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('umkm.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="row g-4 align-items-stretch">
                {{-- =========================
                    KOLOM KIRI — DATA AKUN
                ========================== --}}
                <div class="col-lg-6">
                    <div class="profile-card h-100">
                        <div class="profile-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <div class="header-icon">
                                    <i data-feather="user"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Data Akun</h5>
                                    <small class="text-muted">Informasi pemilik akun</small>
                                </div>
                            </div>
                        </div>

                        <div class="profile-card-body">
                            {{-- NAMA --}}
                            <div class="mb-3">
                                <label class="form-label">Nama Pengguna</label>
                                <input type="text" name="name"
                                       class="form-control"
                                       value="{{ old('name', $user->name) }}" required>
                            </div>

                            {{-- EMAIL --}}
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email"
                                       class="form-control"
                                       value="{{ old('email', $user->email) }}" required>
                            </div>

                            <hr>

                            <p class="text-muted small mb-2">
                                Ubah password (opsional).
                            </p>

                            {{-- PASSWORD --}}
                            <div class="mb-3">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            {{-- PASSWORD CONFIRM --}}
                            <div class="mb-0">
                                <label class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =========================
                    KOLOM KANAN — DATA USAHA
                ========================== --}}
                <div class="col-lg-6">
                    <div class="profile-card h-100">
                        <div class="profile-card-header d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-2">
                                <div class="header-icon">
                                    <i data-feather="briefcase"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Data Usaha</h5>
                                    <small class="text-muted">Informasi usaha yang terdaftar</small>
                                </div>
                            </div>

                            @if($umkm->kode_umkm)
                                <span class="badge kode-badge">
                                    {{ $umkm->kode_umkm }}
                                </span>
                            @endif
                        </div>

                        <div class="profile-card-body">
                            {{-- NAMA USAHA --}}
                            <div class="mb-3">
                                <label class="form-label">Nama Usaha</label>
                                <input type="text" name="nama_usaha"
                                       class="form-control"
                                       value="{{ old('nama_usaha', $umkm->nama_usaha) }}">
                            </div>

                            {{-- NIB --}}
                            <div class="mb-3">
                                <label class="form-label">NIB</label>
                                <input type="text" name="nib"
                                       class="form-control"
                                       value="{{ old('nib', $umkm->nib) }}">
                            </div>

                            {{-- TELEPON --}}
                            <div class="mb-3">
                                <label class="form-label">No. Telepon</label>
                                <input type="text" name="no_telepon"
                                       class="form-control"
                                       value="{{ old('no_telepon', $umkm->no_telepon) }}">
                            </div>

                            {{-- ALAMAT --}}
                            <div>
                                <label class="form-label">Alamat Usaha</label>
                                <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $umkm->alamat) }}</textarea>
                            </div>

                            <p class="small text-muted mt-2 mb-0">
                                Data akan digunakan untuk pendataan UMKM oleh KADIN.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================
                BAR — LEVEL UMKM SAAT INI
            ========================== --}}
            <div class="mt-4">
                <div class="level-info-card d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="level-chip-icon">⭐</div>
                        <div>
                            <div class="small text-muted mb-0">Level UMKM Saat Ini</div>
                            <div class="fw-semibold">
                                @if($umkm->level)
                                    {{ $umkm->level->kode }} — {{ $umkm->level->nama_level }}
                                @else
                                    Belum memilih level
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($umkm->level)
                        <span class="badge level-fee-badge">
                            @if($umkm->level->iuran_bulanan > 0)
                                Rp {{ number_format($umkm->level->iuran_bulanan, 0, ',', '.') }}/bulan
                            @else
                                Gratis
                            @endif
                        </span>
                    @endif
                </div>
            </div>

            {{-- BUTTON SIMPAN --}}
            <div class="mt-4 mb-2">
                <button type="submit" class="btn btn-primary btn-lg profile-save-btn">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div> {{-- /.profile-wrapper --}}
@endsection

@push('styles')
<style>
    .profile-wrapper {
        max-width: 1100px;
        margin: 0 auto;
    }

    .profile-card {
        border-radius: 22px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
    }

    /* HEADER CARD – dibikin mirip level-info-card */
    .profile-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(120deg, #eff6ff, #f9fafb);
        border-radius: 22px 22px 0 0;
    }

    .profile-card-body {
        padding: 20px 22px 22px;
    }

    .header-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #dbeafe;
        border-radius: 999px;
    }

    .header-icon i {
        width: 18px;
        height: 18px;
        stroke-width: 2.2;
    }

    .kode-badge {
        background: #1d4ed8;
        color: #fff;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
    }

    /* Level bar tetap sama, biar konsisten */
    .level-info-card {
        border-radius: 18px;
        padding: 14px 18px;
        border: 1px solid #dbeafe;
        background: linear-gradient(120deg, #eff6ff, #f9fafb);
    }

    .level-chip-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #dbeafe;
        border-radius: 50%;
        font-size: 16px;
    }

    .level-fee-badge {
        background: #3b82f6;
        color: #fff;
        padding: 6px 14px;
        font-size: 11px;
        border-radius: 999px;
    }

    .profile-save-btn {
        border-radius: 14px;
        padding-inline: 28px;
        font-weight: 600;
    }
</style>
@endpush
