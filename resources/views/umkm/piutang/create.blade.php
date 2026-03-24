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
    Belum ada data pelanggan. <a href="{{ route('umkm.etalase.pelanggan.index') }}">Tambah pelanggan dulu</a> sebelum mencatat piutang.
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
          Pelanggan belum ada? <a href="{{ route('umkm.etalase.pelanggan.index') }}" target="_blank">Tambah di sini</a>
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
      <div class="col-md-12">
        <label class="form-label">Catatan (opsional)</label>
        <input type="text" name="catatan" class="form-control" value="{{ old('catatan') }}"
               placeholder="Misal: hutang makanan tgl 10 Maret...">
      </div>
      
      {{-- Pengaturan Email Reminder --}}
      <div class="col-12 mt-4">
        <div class="card bg-light border-0">
          <div class="card-body">
            <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
              <i data-feather="mail" style="width:18px;"></i> Pengaturan Pengingat Email
            </h6>
            
            <div class="row g-3">
              <div class="col-md-6 d-flex align-items-center">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" role="switch" id="emailReminder" name="email_reminder_enabled" value="1" {{ old('email_reminder_enabled') ? 'checked' : '' }}>
                  <label class="form-check-label fw-semibold" for="emailReminder">Aktifkan Pengingat Otomatis via Email</label>
                  <div class="form-text small mt-0">Pastikan pelanggan memiliki email yang terdaftar.</div>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label small mb-1">Jam Pengiriman (WIB)</label>
                <input type="time" name="reminder_send_time" class="form-control form-control-sm w-auto" value="{{ old('reminder_send_time', '09:00') }}">
                <div class="form-text small">Email H-3, H-0, dan Telat akan dikirim pada jam ini.</div>
              </div>
            </div>
          </div>
        </div>
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
