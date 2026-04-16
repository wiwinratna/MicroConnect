@extends('layouts.admin')
@section('title', 'Buat Periode Iuran Baru')

@section('content')
<div class="container-fluid p-0 d-flex justify-content-center">
    <div style="max-width: 650px; width: 100%;">

        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('admin.iuran-periode.index') }}" class="btn bg-white shadow-sm border rounded-circle p-2 me-3" title="Kembali">
                <i data-feather="arrow-left" class="text-dark"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">Buat <strong>Periode Iuran</strong></h1>
                <p class="text-muted small mb-0">System akan otomatis membuat tagihan ke seluruh peserta UMKM aktif.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3">
                <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.iuran-periode.store') }}">
            @csrf

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i data-feather="calendar" class="me-2 text-primary" style="width: 18px;"></i> Konfigurasi Penagihan
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Periode Tagihan <span class="text-danger">*</span></label>
                            <input type="month" name="periode" class="form-control form-control-lg border-0 bg-light rounded-3 shadow-none custom-input" required
                                   value="{{ old('periode') }}">
                            <div class="text-muted mt-1" style="font-size: 10px;">Format: Bulan / Tahun Pajak</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark small">Nominal Standar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3 text-muted">Rp</span>
                                <input type="number" name="nominal_default" class="form-control form-control-lg border-0 bg-light rounded-end-3 shadow-none custom-input" required
                                       value="{{ old('nominal_default', 50000) }}" min="1000" step="1000">
                            </div>
                            <div class="text-muted mt-1" style="font-size: 10px;">Berlaku untuk seluruh UMKM</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark small">Batas Akhir Pelunasan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 rounded-start-3 text-muted"><i data-feather="clock" style="width: 14px;"></i></span>
                                <input type="date" name="jatuh_tempo" class="form-control form-control-lg border-0 bg-light rounded-end-3 shadow-none custom-input" required
                                       value="{{ old('jatuh_tempo') }}">
                            </div>
                            <div class="text-muted mt-1" style="font-size: 10px;">Setelah melewati tanggal ini, status akan otomatis "Terlambat"</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-dark small">Keterangan Tambahan <small class="text-muted fw-normal">(opsional)</small></label>
                            <textarea name="keterangan" class="form-control border-0 bg-light rounded-3 shadow-none custom-input" rows="3"
                                      placeholder="Tambahkan informasi pendukung iuran jika diperlukan...">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 bg-primary-subtle rounded-4 mb-4 d-flex align-items-start border border-primary border-opacity-10">
                <i data-feather="info" class="text-primary me-3 mt-1" style="flex-shrink: 0; width: 20px;"></i>
                <div class="small text-primary">
                    <strong>Konfirmasi Sistem:</strong> Dengan melanjutkan, data iuran akan dikirimkan secara massal ke dashboard masing-masing UMKM. Proses ini tidak dapat dibatalkan.
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm py-3 fw-bold" 
                        onclick="return confirm('Generate tagihan iuran massal?')">
                    Buat Periode & Kirim Tagihan <i data-feather="send" class="ms-2" style="width: 18px;"></i>
                </button>
            </div>
        </form>

    </div>
</div>

<style>
    .bg-primary-subtle { background-color: rgba(59, 125, 221, 0.08) !important; }
    .custom-input { transition: all 0.2s ease-in-out; }
    .custom-input:focus { background-color: #f1f5f9 !important; transform: translateY(-1px); }
</style>
@endsection
