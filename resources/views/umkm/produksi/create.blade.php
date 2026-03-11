@extends('layouts.umkm')

@section('title', 'Tambah Produksi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><strong>Tambah</strong> Produksi</h1>
        <p class="text-muted mb-0">Pilih produk & jumlah hasil. Sistem akan mengurangi bahan sesuai resep (BOM).</p>
    </div>
    <a href="{{ route('umkm.produksi.index') }}" class="btn btn-outline-secondary">&larr; Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('umkm.produksi.store') }}">
            @csrf

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Kode Produksi (Auto)</label>
                    <input type="text" class="form-control" value="{{ $kode ?? '-' }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', now()->toDateString()) }}" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea name="catatan" class="form-control" rows="2">{{ old('catatan') }}</textarea>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="mb-2">Detail Produksi</h5>
            <p class="text-muted small mb-3">Isi produk apa yang diproduksi dan jumlah hasilnya. Bisa tambah banyak baris.</p>

            <div id="detail-wrapper">
                <div class="row g-2 detail-row mb-2">
                    <div class="col-md-8">
                        <label class="form-label small mb-1">Produk</label>
                        <select name="produk_id[]" class="form-select">
                            <option value="">-- pilih produk --</option>
                            @foreach($produk as $pr)
                                <option value="{{ $pr->id }}">{{ $pr->nama_produk }} ({{ $pr->kode_produk }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small mb-1">Qty Hasil</label>
                        <input type="number" step="0.001" name="qty_hasil[]" class="form-control" placeholder="contoh: 50">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger w-100 remove-row">×</button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-outline-primary mt-2" id="add-row">+ Tambah Baris</button>

            <div class="mt-4 d-flex justify-content-end">
                <button class="btn btn-primary btn-lg">Simpan Produksi</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const wrapper = document.getElementById('detail-wrapper');
    const addBtn  = document.getElementById('add-row');

    function newRow(){
        const div = document.createElement('div');
        div.className = 'row g-2 detail-row mb-2';
        div.innerHTML = `
            <div class="col-md-8">
                <label class="form-label small mb-1">Produk</label>
                <select name="produk_id[]" class="form-select">
                    <option value="">-- pilih produk --</option>
                    @foreach($produk as $pr)
                        <option value="{{ $pr->id }}">{{ $pr->nama_produk }} ({{ $pr->kode_produk }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Qty Hasil</label>
                <input type="number" step="0.001" name="qty_hasil[]" class="form-control" placeholder="contoh: 50">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger w-100 remove-row">×</button>
            </div>
        `;
        return div;
    }

    addBtn.addEventListener('click', () => wrapper.appendChild(newRow()));

    wrapper.addEventListener('click', (e) => {
        if (!e.target.classList.contains('remove-row')) return;
        const rows = wrapper.querySelectorAll('.detail-row');
        if (rows.length === 1) {
            rows[0].querySelectorAll('input,select').forEach(el => el.value = '');
            return;
        }
        e.target.closest('.detail-row').remove();
    });
});
</script>
@endpush
