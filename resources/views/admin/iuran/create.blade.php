@extends('layouts.admin')
@section('title', 'Buat Periode Iuran Baru')

@section('content')
<div class="container-fluid p-0" style="max-width: 700px;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Buat <strong>Periode Iuran</strong> Baru</h1>
            <p class="text-muted mb-0">Tentukan periode, nominal, dan jatuh tempo. Tagihan akan di-generate ke semua UMKM aktif.</p>
        </div>
        <a href="{{ route('admin.iuran-periode.index') }}" class="btn btn-outline-secondary">← Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.iuran-periode.store') }}">
        @csrf

        <div class="card border-0 shadow-sm">
            <div class="card-header fw-semibold">📅 Detail Periode Iuran</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Periode <span class="text-danger">*</span></label>
                        <input type="month" name="periode" class="form-control" required
                               value="{{ old('periode') }}"
                               placeholder="Pilih bulan/tahun">
                        <small class="text-muted">Contoh: 2026-03 (Maret 2026)</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nominal Default <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="nominal_default" class="form-control" required
                                   value="{{ old('nominal_default', 50000) }}"
                                   min="1000" step="1000"
                                   placeholder="50000">
                        </div>
                        <small class="text-muted">Nominal iuran yang dikenakan ke setiap UMKM.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jatuh Tempo <span class="text-danger">*</span></label>
                        <input type="date" name="jatuh_tempo" class="form-control" required
                               value="{{ old('jatuh_tempo') }}">
                        <small class="text-muted">Batas akhir pembayaran iuran periode ini.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan <small class="text-muted">(opsional)</small></label>
                        <textarea name="keterangan" class="form-control" rows="2"
                                  placeholder="Catatan untuk periode ini...">{{ old('keterangan') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <strong>ℹ️ Info:</strong> Setelah submit, sistem akan otomatis membuat tagihan iuran untuk semua UMKM aktif pada periode ini.
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Yakin buat periode iuran baru? Tagihan akan langsung di-generate ke semua UMKM aktif.')">
                Buat Periode & Generate Tagihan
            </button>
        </div>
    </form>

</div>
@endsection
