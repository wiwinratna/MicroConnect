@extends('layouts.admin')

@section('content')
<div class="container-fluid p-0">
  <h1 class="h3 mb-3">Detail UMKM</h1>

  <div class="card">
    <div class="card-body">
      <div class="mb-2"><strong>Kode:</strong> {{ $umkm->kode_umkm }}</div>
      <div class="mb-2"><strong>Nama Usaha:</strong> {{ $umkm->nama_usaha }}</div>
      <div class="mb-2"><strong>NIB:</strong> {{ $umkm->nib ?? '-' }}</div>
      <div class="mb-2"><strong>No. Telepon:</strong> {{ $umkm->no_telepon ?? '-' }}</div>
      <div class="mb-2"><strong>Alamat:</strong> {{ $umkm->alamat ?? '-' }}</div>
    </div>
  </div>

  <a href="{{ route('admin.umkm.index') }}" class="btn btn-secondary mt-3">Kembali</a>
</div>
@endsection
