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
      <div class="col-md-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control" required
               value="{{ old('tanggal', date('Y-m-d')) }}">
      </div>
      
      <div class="col-md-3">
        <label class="form-label">Metode Pembayaran</label>
        <select name="metode_pembayaran" id="metode_pembayaran" class="form-select" onchange="togglePiutangFields()">
          <option value="tunai" {{ old('metode_pembayaran') == 'tunai' ? 'selected' : '' }}>Tunai</option>
          <option value="piutang" {{ old('metode_pembayaran') == 'piutang' ? 'selected' : '' }}>Piutang (Kredit)</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Catatan (opsional)</label>
        <input type="text" name="catatan" class="form-control"
               value="{{ old('catatan') }}" placeholder="Catatan...">
      </div>
    </div>

    {{-- Form Tambahan Khusus Piutang --}}
    <div class="row g-3 mt-1" id="piutang_fields" style="display: none; background: #fdfbf7; padding: 15px; border-radius: 8px; border: 1px dashed #f59e0b;">
      <div class="col-md-6">
        <label class="form-label text-warning fw-bold">Pelanggan (Wajib untuk Piutang)</label>
        <select name="pelanggan_id" id="pelanggan_id" class="form-select">
          <option value="">-- Pilih Pelanggan --</option>
          {{-- Diisi via controller kalau ada data pelanggan --}}
          @if(isset($pelanggan))
            @foreach($pelanggan as $plg)
              <option value="{{ $plg->id }}" {{ old('pelanggan_id') == $plg->id ? 'selected' : '' }}>{{ $plg->nama_pelanggan }} ({{ $plg->no_whatsapp ?? '-' }})</option>
            @endforeach
          @endif
        </select>
        <div class="form-text">Pelanggan harus didaftarkan dulu di menu Piutang > Referensi Pelanggan.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label text-warning fw-bold">Tanggal Jatuh Tempo</label>
        <input type="date" name="jatuh_tempo" id="jatuh_tempo" class="form-control" value="{{ old('jatuh_tempo') }}">
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
</form>

<script>
function togglePiutangFields() {
  const method = document.getElementById('metode_pembayaran').value;
  const fields = document.getElementById('piutang_fields');
  
  if (method === 'piutang') {
    fields.style.display = 'flex';
  } else {
    fields.style.display = 'none';
  }
}

// Call on load in case of validation errors
window.addEventListener('DOMContentLoaded', () => {
    togglePiutangFields();
});

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
    <div class="col-md-1 d-flex align-items-end">
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
