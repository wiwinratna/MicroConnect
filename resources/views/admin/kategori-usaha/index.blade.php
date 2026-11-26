@extends('layouts.admin')
@section('title', 'Kategori Usaha')

@section('content')
<div class="container-fluid p-0">

  <div class="mb-4">
    <h1 class="h3 mb-1">Kategori <strong>Usaha</strong></h1>
    <p class="text-muted small mb-0">
      Pilihan kategori pada formulir pendaftaran. Kategori muncul sesuai jenis usaha yang dipilih (Barang atau Jasa).
      Kategori yang dinonaktifkan tidak muncul lagi, sedangkan data UMKM yang sudah memakainya tetap utuh.
    </p>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <h5 class="card-title mb-3">Tambah kategori</h5>
      <form method="POST" action="{{ route('admin.kategori-usaha.store') }}" class="row g-2 align-items-end">
        @csrf
        <div class="col-md-6">
          <label class="form-label" for="nama">Nama kategori</label>
          <input type="text" name="nama" id="nama" class="form-control" maxlength="150"
                 placeholder="Contoh: Manufaktur" value="{{ old('nama') }}" required>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="rumpun_bawaan">Jenis usaha</label>
          <select name="rumpun_bawaan" id="rumpun_bawaan" class="form-select" required>
            <option value="">-- Pilih --</option>
            @foreach($untuk as $nilai => $label)
              <option value="{{ $nilai }}" @selected(old('rumpun_bawaan') === $nilai)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 d-grid">
          <button type="submit" class="btn btn-primary">Tambah</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Nama kategori</th>
            <th style="width:26%; min-width:190px">Jenis usaha</th>
            <th style="width:10%">Status</th>
            <th class="text-end" style="width:22%">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($kategori as $k)
            <tr>
              <td>
                <input type="text" name="nama" form="ubah{{ $k->id }}" class="form-control form-control-sm"
                       maxlength="150" value="{{ $k->nama }}" required aria-label="Nama kategori">
              </td>
              <td>
                <select name="rumpun_bawaan" form="ubah{{ $k->id }}" class="form-select form-select-sm" aria-label="Jenis usaha">
                  @foreach($untuk as $nilai => $label)
                    <option value="{{ $nilai }}" @selected($k->rumpun_bawaan === $nilai)>{{ $label }}</option>
                  @endforeach
                </select>
              </td>
              <td>
                <span class="badge {{ $k->aktif ? 'bg-success' : 'bg-secondary' }}">{{ $k->aktif ? 'Aktif' : 'Nonaktif' }}</span>
              </td>
              <td class="text-end text-nowrap">
                <form method="POST" action="{{ route('admin.kategori-usaha.update', $k->id) }}" id="ubah{{ $k->id }}" class="d-inline">
                  @csrf @method('PUT')
                  <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                </form>
                <form method="POST" action="{{ route('admin.kategori-usaha.status', $k->id) }}" class="d-inline">
                  @csrf @method('PATCH')
                  <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $k->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kategori usaha.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
