@extends('layouts.umkm')
@section('title', 'Edit Pelanggan')

@push('styles')
<style>
.form-body { padding: 1.75rem; background: #fff; }

.section-label {
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .1em; color: #94a3b8; margin-bottom: 1.25rem;
    display: flex; align-items: center; gap: .5rem;
}
.section-label::after { content:''; flex:1; height:1px; background:#e2e8f0; }

.form-label { font-size: .8rem; font-weight: 600; color: #475569; margin-bottom: .4rem; }
.req { color: #ef4444; }

.action-footer { display: flex; align-items: center; justify-content: flex-start; gap: .75rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9; margin-top: 1rem; }
.input-icon-wrap { position: relative; }
.input-icon-wrap .form-control { padding-left: 2.25rem !important; }
.input-icon-wrap .icon { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; width: 14px; height: 14px; z-index: 5; }

/* ── Upload Zone ── */
.upload-zone { border: 2px dashed #cbd5e1; border-radius: 8px; padding: 1.5rem; text-align: center; cursor: pointer; transition: all .2s; background: #f8fafc; position: relative; overflow: hidden; }
.upload-zone:hover { border-color: #94a3b8; background: #f1f5f9; }
.upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
.upload-zone .preview-img { max-height: 120px; object-fit: contain; }
.upload-zone:not(.has-file) .preview-img { display: none; }
.upload-zone.has-file .placeholder-content { display: none; }
.upload-zone.has-file .preview-img { display: block; margin: 0 auto; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="mb-4">
    <a href="{{ route('umkm.etalase.pelanggan.index') }}"
       style="display:inline-flex;align-items:center;gap:.35rem;font-size:.775rem;font-weight:500;color:#64748b;text-decoration:none;margin-bottom:.7rem;transition:color .15s;"
       onmouseover="this.style.color='#334155'" onmouseout="this.style.color='#64748b'">
        <i data-feather="arrow-left" style="width:13px;height:13px;"></i>
        Kembali ke Daftar Pelanggan
    </a>
    <h1 class="h3 mb-0">Edit <strong>Pelanggan</strong></h1>
    <p class="text-muted" style="font-size:.78rem;margin-top:2px;">Perbarui data profil dan kontak pelanggan.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger rounded-3 mb-4" style="font-size:.825rem;">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="form-body">
                <form method="POST" action="{{ route('umkm.etalase.pelanggan.update', $pelanggan->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- SECTION 1: PROFIL DASAR --}}
                    <p class="section-label">Profil & Identitas Pelanggan</p>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nama Pelanggan <span class="req">*</span></label>
                            <input type="text" name="nama_pelanggan" class="form-control"
                                   value="{{ old('nama_pelanggan', $pelanggan->nama_pelanggan) }}"
                                   required placeholder="Nama Individu / Panggilan" autofocus>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Instansi / Toko / Perusahaan</label>
                            <input type="text" name="nama_instansi" class="form-control"
                                   value="{{ old('nama_instansi', $pelanggan->nama_instansi) }}"
                                   placeholder="Contoh: PT. Maju Bersama (Opsional)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama PIC / Penanggung Jawab</label>
                            <input type="text" name="nama_pic" class="form-control"
                                   value="{{ old('nama_pic', $pelanggan->nama_pic) }}"
                                   placeholder="Bila perlu (Opsional)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Identitas (KTP)</label>
                            <input type="text" name="no_ktp" class="form-control"
                                   value="{{ old('no_ktp', $pelanggan->no_ktp) }}"
                                   placeholder="Untuk administrasi (Opsional)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload Foto KTP <span class="text-muted small fw-normal">(Opsional)</span></label>
                            <div class="upload-zone {{ $pelanggan->foto_ktp ? 'has-file' : '' }}" id="ktpZone">
                                <input type="file" name="foto_ktp" id="foto_ktp" accept="image/*">
                                <div class="placeholder-content">
                                    <i data-feather="upload-cloud" style="width:24px;height:24px;color:#94a3b8;margin-bottom:.5rem;"></i>
                                    <div style="font-size:.78rem;font-weight:600;color:#475569;">Klik / Seret foto baru</div>
                                    <div style="font-size:.7rem;color:#94a3b8;">Abaikan jika tidak ingin mengubah</div>
                                </div>
                                <img src="{{ $pelanggan->foto_ktp ? asset('storage/'.$pelanggan->foto_ktp) : '' }}" class="preview-img" id="ktpPreview" alt="Preview KTP">
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: KONTAK & ALAMAT --}}
                    <p class="section-label mt-2">Kontak & Alamat Pengiriman</p>
                    
                    <div class="row g-4 mb-2">
                        <div class="col-md-4">
                            <label class="form-label">No. WhatsApp</label>
                            <div class="input-icon-wrap">
                                <i data-feather="phone" class="icon"></i>
                                <input type="text" name="no_whatsapp" class="form-control"
                                       value="{{ old('no_whatsapp', $pelanggan->no_whatsapp) }}"
                                       placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <div class="input-icon-wrap">
                                <i data-feather="mail" class="icon"></i>
                                <input type="email" name="email" class="form-control"
                                       value="{{ old('email', $pelanggan->email) }}"
                                       placeholder="contoh@email.com (Opsional)">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kontak Alternatif / No. HP 2</label>
                            <div class="input-icon-wrap">
                                <i data-feather="phone-call" class="icon"></i>
                                <input type="text" name="kontak_alternatif" class="form-control"
                                       value="{{ old('kontak_alternatif', $pelanggan->kontak_alternatif) }}"
                                       placeholder="08xxxxxxxxxx (Opsional)">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control" rows="2"
                                      placeholder="Alamat domisili atau pengiriman...">{{ old('alamat', $pelanggan->alamat) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan Tambahan</label>
                            <input type="text" name="catatan" class="form-control"
                                   value="{{ old('catatan', $pelanggan->catatan) }}"
                                   placeholder="Info opsional tentang pelanggan ini...">
                        </div>
                    </div>

                    <div class="action-footer">
                        <button type="submit" class="btn btn-primary px-4">
                            <i data-feather="save" style="width:14px;height:14px;margin-right:5px;"></i>
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('umkm.etalase.pelanggan.index') }}" class="btn btn-outline-secondary px-4">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();

    // Image Preview
    const ktpInput = document.getElementById('foto_ktp');
    const ktpZone = document.getElementById('ktpZone');
    const ktpPreview = document.getElementById('ktpPreview');

    ktpInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                ktpPreview.src = e.target.result;
                ktpZone.classList.add('has-file');
            }
            reader.readAsDataURL(this.files[0]);
        } else {
            // Restore proper state if cancelled (does not revert to original if they cancel, but that's fine for simple input)
            if(!ktpPreview.src || ktpPreview.src.endsWith('/')) {
                ktpZone.classList.remove('has-file');
            }
        }
    });
});
</script>
@endpush
