@extends('layouts.umkm')
@section('title', 'Tambah Piutang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Tambah</strong> Piutang</h1>
    <p class="text-muted mb-0">Catat tagihan pelanggan yang belum lunas.</p>
  </div>
  <a href="{{ route('umkm.piutang.index') }}" class="btn btn-outline-secondary">← Kembali</a>
</div>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

@if($pelanggan->isEmpty())
  <div class="alert alert-warning">
    Belum ada data pelanggan. <a href="{{ route('umkm.piutang.pelanggan.index') }}">Tambah pelanggan dulu</a> sebelum mencatat piutang.
  </div>
@endif

<form method="POST" action="{{ route('umkm.piutang.store') }}">
@csrf
<div class="card border-0 shadow-sm">
  <div class="card-body">

    <div class="row g-3">
      {{-- Pelanggan --}}
      <div class="col-md-6">
        <label class="form-label">Pelanggan <span class="text-danger">*</span></label>
        <select name="pelanggan_id" class="form-select" required>
          <option value="">-- Pilih Pelanggan --</option>
          @foreach($pelanggan as $pl)
            <option value="{{ $pl->id }}" {{ old('pelanggan_id') == $pl->id ? 'selected' : '' }}>
              {{ $pl->nama_pelanggan }} @if($pl->no_whatsapp) (WA: {{ $pl->no_whatsapp }}) @endif
            </option>
          @endforeach
        </select>
        <div class="form-text">
          Pelanggan belum ada? <a href="{{ route('umkm.piutang.pelanggan.index') }}" target="_blank">Tambah di sini</a>
        </div>
      </div>

      {{-- Tanggal --}}
      <div class="col-md-3">
        <label class="form-label">Tanggal Piutang <span class="text-danger">*</span></label>
        <input type="date" name="tanggal" class="form-control" required value="{{ old('tanggal', date('Y-m-d')) }}">
      </div>

      {{-- Jatuh Tempo --}}
      <div class="col-md-3">
        <label class="form-label">Jatuh Tempo <span class="text-danger">*</span></label>
        <input type="date" name="jatuh_tempo" class="form-control" required value="{{ old('jatuh_tempo') }}">
        <div class="form-text">Reminder WA dikirim H-3 & H-0 jatuh tempo.</div>
      </div>

      {{-- Nominal --}}
      <div class="col-md-4">
        <label class="form-label">Nominal Piutang (Rp) <span class="text-danger">*</span></label>
        <input type="number" name="nominal_awal" class="form-control" required min="1" step="1000"
               value="{{ old('nominal_awal') }}" placeholder="Contoh: 150000">
      </div>

      {{-- Catatan --}}
      <div class="col-md-8">
        <label class="form-label">Catatan (opsional)</label>
        <input type="text" name="catatan" class="form-control" value="{{ old('catatan') }}"
               placeholder="Misal: hutang makanan tgl 10 Maret...">
      </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
      <button type="submit" class="btn btn-primary btn-lg" {{ $pelanggan->isEmpty() ? 'disabled' : '' }}>
        Simpan Piutang
      </button>
    </div>

  </div>
</div>
</form>
@endsection
