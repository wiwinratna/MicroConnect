@extends('layouts.umkm')
@section('title','Tambah Jurnal Manual')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Tambah</strong> Jurnal Manual</h1>
    <p class="text-muted mb-0">Catat transaksi beban operasional atau penyesuaian manual.</p>
  </div>
  <a href="{{ route('umkm.jurnal.index') }}" class="btn btn-outline-secondary">&larr; Kembali</a>
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

<form method="POST" action="{{ route('umkm.jurnal.store') }}">
@csrf
<div class="card border-0 shadow-sm">
  <div class="card-body">
    
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control" required value="{{ old('tanggal', date('Y-m-d')) }}">
      </div>
      <div class="col-md-9">
        <label class="form-label">Keterangan Transaksi</label>
        <input type="text" name="keterangan" class="form-control" required placeholder="Contoh: Bayar listrik bulan ini" value="{{ old('keterangan') }}">
      </div>
    </div>

    <hr class="my-4">

    <div id="rows">
      {{-- Baris 1: Debit --}}
      <div class="row g-2 mb-2 align-items-center">
        <div class="col-md-5">
          <label class="form-label small mb-1">Akun (COA)</label>
          <select name="kode_akun[]" class="form-select" required>
            <option value="">- pilih akun -</option>
            @foreach($coa as $c)
              <option value="{{ $c->kode_akun }}">{{ $c->kode_akun }} - {{ $c->nama_akun }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Debit (Rp)</label>
          <input type="number" name="debit[]" class="form-control input-dr" placeholder="0" min="0" oninput="calcTotal()" required>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Kredit (Rp)</label>
          <input type="number" name="kredit[]" class="form-control input-cr" placeholder="0" min="0" oninput="calcTotal()" value="0" required>
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <button type="button" class="btn btn-danger w-100" onclick="removeRow(this)">×</button>
        </div>
      </div>

      {{-- Baris 2: Kredit --}}
      <div class="row g-2 mb-2 align-items-center">
        <div class="col-md-5">
          <select name="kode_akun[]" class="form-select" required>
            <option value="">- pilih akun -</option>
            @foreach($coa as $c)
              <option value="{{ $c->kode_akun }}">{{ $c->kode_akun }} - {{ $c->nama_akun }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <input type="number" name="debit[]" class="form-control input-dr" placeholder="0" min="0" oninput="calcTotal()" value="0" required>
        </div>
        <div class="col-md-3">
          <input type="number" name="kredit[]" class="form-control input-cr" placeholder="0" min="0" oninput="calcTotal()" required>
        </div>
        <div class="col-md-1 d-flex">
          <button type="button" class="btn btn-danger w-100" onclick="removeRow(this)">×</button>
        </div>
      </div>

    </div>

    <button type="button" class="btn btn-outline-primary mt-2" onclick="addRow()">+ Tambah Baris Akun</button>

    <div class="row mt-4 bg-light p-3 rounded">
        <div class="col-md-5 text-end fw-bold">TOTAL:</div>
        <div class="col-md-3 fs-5 fw-bold text-primary" id="tot-dr">0</div>
        <div class="col-md-3 fs-5 fw-bold text-danger" id="tot-cr">0</div>
        <div class="col-md-1"></div>
        <div class="col-12 mt-2 text-end text-danger fw-bold" id="status-balance" style="display:none;">TOTAL BELUM BALANCE!</div>
    </div>

    <div class="d-flex justify-content-end mt-4">
      <button type="submit" class="btn btn-primary btn-lg" id="btn-submit">Simpan Jurnal</button>
    </div>

  </div>
</div>
</form>

<script>
function addRow() {
  const rows = document.getElementById('rows');
  const div = document.createElement('div');
  div.className = 'row g-2 mb-2 align-items-center';
  div.innerHTML = `
    <div class="col-md-5">
        <select name="kode_akun[]" class="form-select" required>
        <option value="">- pilih akun -</option>
        @foreach($coa as $c)
            <option value="{{ $c->kode_akun }}">{{ $c->kode_akun }} - {{ $c->nama_akun }}</option>
        @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <input type="number" name="debit[]" class="form-control input-dr" placeholder="0" min="0" value="0" oninput="calcTotal()" required>
    </div>
    <div class="col-md-3">
        <input type="number" name="kredit[]" class="form-control input-cr" placeholder="0" min="0" value="0" oninput="calcTotal()" required>
    </div>
    <div class="col-md-1 d-flex">
        <button type="button" class="btn btn-danger w-100" onclick="removeRow(this)">×</button>
    </div>
  `;
  rows.appendChild(div);
}

function removeRow(btn){
  const row = btn.closest('.row');
  const rows = document.querySelectorAll('#rows .row');
  if (rows.length <= 2) {
    alert('Minimal 2 baris akun (Debit dan Kredit)!');
    return;
  }
  row.remove();
  calcTotal();
}

function calcTotal() {
    let totDr = 0;
    let totCr = 0;

    document.querySelectorAll('.input-dr').forEach(el => totDr += Number(el.value));
    document.querySelectorAll('.input-cr').forEach(el => totCr += Number(el.value));

    document.getElementById('tot-dr').innerText = new Intl.NumberFormat('id-ID').format(totDr);
    document.getElementById('tot-cr').innerText = new Intl.NumberFormat('id-ID').format(totCr);

    const isBalance = totDr === totCr && totDr > 0;
    
    document.getElementById('status-balance').style.display = isBalance ? 'none' : 'block';
    document.getElementById('btn-submit').disabled = !isBalance;
}

window.onload = calcTotal;
</script>
@endsection
