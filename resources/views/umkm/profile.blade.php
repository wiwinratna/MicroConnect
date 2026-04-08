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

        <form method="POST" action="{{ route('umkm.profile.update') }}" enctype="multipart/form-data">
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
                            <div class="mb-3">
                                <label class="form-label">Alamat Usaha</label>
                                <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $umkm->alamat) }}</textarea>
                            </div>

                            {{-- JENIS USAHA --}}
                            <div class="mb-3">
                                <label class="form-label">Jenis Usaha</label>
                                <input type="text" name="jenis_usaha" class="form-control"
                                       value="{{ old('jenis_usaha', $umkm->jenis_usaha) }}"
                                       placeholder="Contoh: Kuliner, Jasa, Perdagangan...">
                            </div>

                            {{-- NO WHATSAPP --}}
                            <div class="mb-3">
                                <label class="form-label">No. WhatsApp Pemilik</label>
                                <input type="text" name="no_whatsapp" class="form-control"
                                       value="{{ old('no_whatsapp', $umkm->no_whatsapp) }}"
                                       placeholder="08xxxxxxxxxx">
                                <div class="form-text">Digunakan untuk menerima notifikasi sistem.</div>
                            </div>

                            <p class="small text-muted mt-2 mb-0">
                                Data akan digunakan untuk pendataan UMKM oleh KADIN.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================
                LOGO & WARNA USAHA
            ========================== --}}
            <div class="mt-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header fw-semibold" style="background: linear-gradient(120deg, #fefce8, #f9fafb);">
                        🎨 Logo & Tampilan Usaha
                    </div>
                    <div class="card-body">
                        <div class="row g-4 align-items-center">
                            {{-- Logo Upload --}}
                            <div class="col-md-7">
                                <label class="form-label fw-semibold">Logo Usaha</label>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    @if($umkm->logo_path)
                                        <img src="{{ asset('storage/' . $umkm->logo_path) }}"
                                             alt="Logo" class="rounded-circle shadow-sm"
                                             style="width:64px; height:64px; object-fit:cover; border:2px solid #e5e7eb;"
                                             id="logoPreview">
                                    @else
                                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                             style="width:64px; height:64px; background:#dbeafe; color:#1d4ed8; font-weight:700; font-size:1.5rem; border:2px solid #e5e7eb;"
                                             id="logoPreview">
                                            {{ strtoupper(substr($umkm->nama_usaha ?? 'U', 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <input type="file" name="logo" id="logoInput" class="form-control form-control-sm" accept="image/jpeg,image/png">
                                        <div class="form-text">Format: JPG/PNG, maks 1MB. Logo akan tampil di sidebar, etalase, dan nota.</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Warna Tema --}}
                            <div class="col-md-5">
                                <label class="form-label fw-semibold">Warna Tema Mode Etalase</label>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" name="warna_tema" id="warnaTemaInput"
                                           class="form-control form-control-color shadow-sm"
                                           value="{{ old('warna_tema', $umkm->warna_tema ?? '#0d6efd') }}"
                                           title="Pilih warna tema">
                                    <div>
                                        <span class="badge rounded-pill px-3 py-2" id="warnaTemaPreview"
                                              style="background-color: {{ $umkm->warna_tema ?? '#0d6efd' }}; color: #fff; font-size: 0.85rem;">
                                            {{ $umkm->warna_tema ?? '#0d6efd' }}
                                        </span>
                                        <div class="form-text">Hanya untuk Mode Etalase & nota.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- =========================
                KONFIGURASI INVENTORI
            ========================== --}}
            <div class="mt-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header fw-semibold" style="background: linear-gradient(120deg, #f0fdf4, #f9fafb);">
                        ⚙️ Konfigurasi Metode Pencatatan Persediaan
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Pilihan metode ini mempengaruhi cara sistem menghitung nilai stok dan HPP (Harga Pokok Produksi).
                            Setelah disimpan, semua perhitungan baru akan menggunakan metode yang dipilih.
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Metode Pencatatan Stok</label>
                                <select name="recording_method" class="form-select">
                                    <option value="periodik" {{ ($umkm->recording_method ?? 'periodik') === 'periodik' ? 'selected' : '' }}>
                                        Periodik — stok dihitung di akhir periode
                                    </option>
                                    <option value="perpetual" {{ $umkm->recording_method === 'perpetual' ? 'selected' : '' }}>
                                        Perpetual — stok dicatat setiap transaksi
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Metode Penilaian Persediaan</label>
                                <select name="inventory_method" class="form-select">
                                    <option value="Average" {{ ($umkm->inventory_method ?? 'Average') === 'Average' ? 'selected' : '' }}>
                                        Average (Rata-rata) — direkomendasikan untuk UMKM
                                    </option>
                                    <option value="FIFO" {{ $umkm->inventory_method === 'FIFO' ? 'selected' : '' }}>
                                        FIFO — barang masuk pertama, keluar pertama
                                    </option>
                                    <option value="LIFO" {{ $umkm->inventory_method === 'LIFO' ? 'selected' : '' }}>
                                        LIFO — barang masuk terakhir, keluar pertama
                                    </option>
                                </select>
                            </div>
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
                                {{ rupiah($umkm->level->iuran_bulanan) }}/bulan
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live logo file preview
    const logoInput = document.getElementById('logoInput');
    const logoPreview = document.getElementById('logoPreview');
    if (logoInput && logoPreview) {
        logoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Replace initials div with img
                    if (logoPreview.tagName === 'DIV') {
                        const img = document.createElement('img');
                        img.id = 'logoPreview';
                        img.className = 'rounded-circle shadow-sm';
                        img.style.cssText = 'width:64px; height:64px; object-fit:cover; border:2px solid #e5e7eb;';
                        img.src = e.target.result;
                        logoPreview.parentNode.replaceChild(img, logoPreview);
                    } else {
                        logoPreview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Live warna tema preview
    const warnaInput = document.getElementById('warnaTemaInput');
    const warnaPreview = document.getElementById('warnaTemaPreview');
    if (warnaInput && warnaPreview) {
        warnaInput.addEventListener('input', function() {
            warnaPreview.style.backgroundColor = this.value;
            warnaPreview.textContent = this.value;
        });
    }
});
</script>
@endpush

