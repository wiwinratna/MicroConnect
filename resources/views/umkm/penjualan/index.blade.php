@extends('layouts.umkm')
@section('title','Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Penjualan</h1>
  <a href="{{ route('umkm.penjualan.create') }}" class="btn btn-primary">+ Tambah</a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-bordered bg-white">
  <thead>
    <tr>
      <th>Tanggal</th>
      <th>Kode</th>
      <th>Pembeli</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
    @forelse($data as $d)
      <tr>
        <td>{{ $d->tanggal }}</td>
        <td>{{ $d->kode_penjualan }}</td>
        <td>{{ $d->pembeli ?? '-' }}</td>
        <td>Rp {{ number_format($d->total, 0, ',', '.') }}</td>
      </tr>
    @empty
      <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
    @endforelse
  </tbody>
</table>
@endsection
