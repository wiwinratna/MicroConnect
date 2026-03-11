@extends('layouts.admin')
@section('title', 'Daftarkan UMKM Baru')

@section('content')
<div class="container-fluid p-0" style="max-width: 900px;">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h3 mb-1">Daftarkan <strong>UMKM Baru</strong></h1>
      <p class="text-muted mb-0">Buat akun pengguna dan data UMKM sekaligus.</p>
    </div>
    <a href="{{ route('admin.umkm.index') }}" class="btn btn-outline-secondary">← Kembali</a>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.umkm.store') }}">
  @csrf
  <div class="row g-4">

    {{-- Akun --}}
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header fw-semibold">👤 Data Akun Login</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="Nama pemilik UMKM">
          </div>
          <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control" required value="{{ old('email') }}" placeholder="email@domain.com">
          </div>
          <div class="mb-0">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control" required placeholder="Min. 6 karakter">
          </div>
        </div>
      </div>
    </div>

    {{-- Data UMKM --}}
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header fw-semibold">🏪 Data Usaha</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">Nama Usaha</label>
            <input type="text" name="nama_usaha" class="form-control" value="{{ old('nama_usaha') }}" placeholder="Nama usaha">
          </div>
          <div class="mb-3">
            <label class="form-label">Jenis Usaha</label>
            <input type="text" name="jenis_usaha" class="form-control" value="{{ old('jenis_usaha') }}" placeholder="Kuliner, Jasa, Perdagangan...">
          </div>
          <div class="mb-3">
            <label class="form-label">No. WhatsApp</label>
            <input type="text" name="no_whatsapp" class="form-control" value="{{ old('no_whatsapp') }}" placeholder="08xxxxxxxxxx">
          </div>
          <div class="mb-3">
            <label class="form-label">NIB</label>
            <input type="text" name="nib" class="form-control" value="{{ old('nib') }}" placeholder="Nomor Induk Berusaha">
          </div>
          <div class="mb-0">
            <label class="form-label">Alamat</label>
            <input type="text" name="alamat" class="form-control" value="{{ old('alamat') }}" placeholder="Alamat usaha">
          </div>
        </div>
      </div>
    </div>

    {{-- Konfigurasi --}}
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header fw-semibold">⚙️ Konfigurasi Level & Metode Inventori</div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Level UMKM</label>
              <select name="level_id" class="form-select">
                <option value="">— Pilih Level —</option>
                @foreach($levels as $lv)
                  <option value="{{ $lv->id }}" {{ old('level_id') == $lv->id ? 'selected' : '' }}>
                    {{ $lv->kode }} — {{ $lv->nama_level }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Metode Pencatatan Stok</label>
              <select name="recording_method" class="form-select">
                <option value="periodik" {{ old('recording_method', 'periodik') === 'periodik' ? 'selected' : '' }}>Periodik</option>
                <option value="perpetual" {{ old('recording_method') === 'perpetual' ? 'selected' : '' }}>Perpetual</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Metode Penilaian Persediaan</label>
              <select name="inventory_method" class="form-select">
                <option value="Average" {{ old('inventory_method', 'Average') === 'Average' ? 'selected' : '' }}>Average (Rata-rata)</option>
                <option value="FIFO" {{ old('inventory_method') === 'FIFO' ? 'selected' : '' }}>FIFO</option>
                <option value="LIFO" {{ old('inventory_method') === 'LIFO' ? 'selected' : '' }}>LIFO</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <div class="d-flex justify-content-end mt-4">
    <button type="submit" class="btn btn-primary btn-lg">Daftarkan UMKM</button>
  </div>
  </form>

</div>
@endsection
