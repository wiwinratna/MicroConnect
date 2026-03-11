@extends('layouts.umkm')
@section('title', 'Piutang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Piutang</strong> Pelanggan</h1>
    <p class="text-muted mb-0">Catatan tagihan pelanggan yang belum lunas.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('umkm.piutang.pelanggan.index') }}" class="btn btn-outline-secondary">Data Pelanggan</a>
    <a href="{{ route('umkm.piutang.create') }}" class="btn btn-primary">+ Tambah Piutang</a>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Summary card --}}
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <p class="text-muted small mb-1">Total Sisa Piutang Aktif</p>
        <h4 class="fw-bold text-danger">Rp {{ number_format($totalSisa, 0, ',', '.') }}</h4>
      </div>
    </div>
  </div>
</div>

{{-- Filter tab --}}
<ul class="nav nav-tabs mb-3" id="filterTab">
  <li class="nav-item"><a class="nav-link {{ request('status', 'aktif') === 'aktif' ? 'active' : '' }}" href="?status=aktif">Belum Lunas</a></li>
  <li class="nav-item"><a class="nav-link {{ request('status') === 'lunas' ? 'active' : '' }}" href="?status=lunas">Lunas</a></li>
  <li class="nav-item"><a class="nav-link {{ request('status') === 'semua' ? 'active' : '' }}" href="?status=semua">Semua</a></li>
</ul>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th>Kode</th>
          <th>Pelanggan</th>
          <th>Nominal Awal</th>
          <th>Sisa</th>
          <th>Jatuh Tempo</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($piutang as $p)
          @php
            $isLewat = $p->status !== 'lunas' && $p->jatuh_tempo->isPast();
          @endphp
          <tr class="{{ $isLewat ? 'table-danger' : '' }}">
            <td><code>{{ $p->kode_piutang }}</code></td>
            <td><strong>{{ $p->pelanggan->nama_pelanggan }}</strong></td>
            <td>Rp {{ number_format($p->nominal_awal, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($p->sisa, 0, ',', '.') }}</td>
            <td>
              {{ $p->jatuh_tempo->isoFormat('D MMM Y') }}
              @if($isLewat)
                <span class="badge bg-danger ms-1">Lewat</span>
              @endif
            </td>
            <td>
              @if($p->status === 'lunas')
                <span class="badge bg-success">Lunas</span>
              @elseif($p->status === 'sebagian')
                <span class="badge bg-warning text-dark">Sebagian</span>
              @else
                <span class="badge bg-danger">Belum Lunas</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('umkm.piutang.show', $p->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data piutang.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if($piutang->hasPages())
  <div class="mt-3">{{ $piutang->links() }}</div>
@endif
@endsection
