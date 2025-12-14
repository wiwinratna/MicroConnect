@extends('layouts.umkm')
@section('title','Tambah Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Tambah</strong> Penjualan</h1>
    <p class="text-muted mb-0">Simpan transaksi penjualan, stok bahan akan otomatis berkurang sesuai komposisi produk.</p>
  </div>
  <a href="{{ route('umkm.penjualan.index') }}" class="btn btn-outline-secondary">&larr; Kembali</a>
</div>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $e)
        <li>{{ $e }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('umkm.penjualan.store') }}">
@csrf

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control" required
               value="{{ old('tanggal', date('Y-m-d')) }}">
      </div>
      <div class="col-md-4">
        <label class="form-label">Pembeli (opsional)</label>
        <input type="text" name="pembeli" class="form-control"
               value="{{ old('pembeli') }}" placeholder="Nama pembeli">
      </div>
      <div class="col-md-4">
        <label class="form-label">Catatan (opsional)</label>
        <input type="text" name="catatan" class="form-control"
               value="{{ old('catatan') }}" placeholder="Catatan...">
      </div>
    </div>

    <hr class="my-4">

    <div id="rows">
      <div class="row g-2 mb-2">
        <div class="col-md-8">
          <label class="form-label small mb-1">Produk</label>
          <select name="produk_id[]" class="form-control">
            <option value="">- pilih produk -</option>
            @foreach($produk as $p)
              <option value="{{ $p->id }}">{{ $p->nama_produk }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Qty</label>
          <input type="number" step="0.001" name="qty[]" class="form-control" placeholder="Qty">
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <button type="button" class="btn btn-danger w-100" onclick="removeRow(this)">×</button>
        </div>
      </div>
    </div>

    <button type="button" class="btn btn-outline-primary mt-2" onclick="addRow()">+ Tambah Baris</button>

    <div class="d-flex justify-content-end mt-4">
      <button type="submit" class="btn btn-primary btn-lg">Simpan</button>
    </div>

  </div>
</div>

<script>
function addRow() {
  const rows = document.getElementById('rows');
  const div = document.createElement('div');
  div.className = 'row g-2 mb-2';
  div.innerHTML = `
    <div class="col-md-8">
      <select name="produk_id[]" class="form-control">
        <option value="">- pilih produk -</option>
        @foreach($produk as $p)
          <option value="{{ $p->id }}">{{ $p->nama_produk }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <input type="number" step="0.001" name="qty[]" class="form-control" placeholder="Qty">
    </div>
    <div class="col-md-1">
      <button type="button" class="btn btn-danger w-100" onclick="removeRow(this)">×</button>
    </div>
  `;
  rows.appendChild(div);
}

function removeRow(btn){
  const row = btn.closest('.row');
  const rows = document.querySelectorAll('#rows .row');
  if (rows.length === 1) {
    row.querySelectorAll('input,select').forEach(el => el.value = '');
    return;
  }
  row.remove();
}
</script>
@endsection
