@extends('layouts.admin')
@section('title', 'Kelola UMKM')

@section('content')
<div class="container-fluid p-0">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h3 mb-1">Kelola <strong>UMKM</strong></h1>
      <p class="text-muted mb-0">Daftar UMKM binaan KADIN Bengkalis.</p>
    </div>
    <a href="{{ route('admin.umkm.create') }}" class="btn btn-primary">+ Daftarkan UMKM Baru</a>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif

  {{-- KPI Row --}}
  <div class="row g-3 mb-4">
    <div class="col-sm-4">
      <div class="card border-0 shadow-sm text-center">
        <div class="card-body py-3">
          <div class="text-muted small">Total UMKM</div>
          <div class="h4 fw-bold">{{ $totalUmkm }}</div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card border-0 shadow-sm text-center">
        <div class="card-body py-3">
          <div class="text-muted small">UMKM Aktif</div>
          <div class="h4 fw-bold text-success">{{ $totalAktif }}</div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card border-0 shadow-sm text-center">
        <div class="card-body py-3">
          <div class="text-muted small">Iuran Belum Bayar (Bulan Ini)</div>
          <div class="h4 fw-bold text-danger">{{ $iuranBelumBayar }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Filter --}}
  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-5">
      <input type="text" name="search" class="form-control" placeholder="Cari nama usaha / kode UMKM..." value="{{ request('search') }}">
    </div>
    <div class="col-md-3">
      <select name="level" class="form-select">
        <option value="">Semua Level</option>
        @foreach($levels as $lv)
          <option value="{{ $lv->id }}" {{ request('level') == $lv->id ? 'selected' : '' }}>{{ $lv->nama_level }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2">
      <select name="status" class="form-select">
        <option value="">Semua Status</option>
        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-secondary w-100">Filter</button>
    </div>
  </form>

  {{-- Tabel --}}
  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <table class="table table-hover mb-0 table-borderless align-middle">
        <thead class="table-light">
          <tr>
            <th>Kode</th>
            <th>Nama Usaha</th>
            <th>Level</th>
            <th>Jenis Usaha</th>
            <th>Transaksi</th>
            <th>Metode Inv</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($umkmList as $u)
            <tr>
              <td><code>{{ $u->kode_umkm }}</code></td>
              <td>
                <strong>{{ $u->nama_usaha ?? '—' }}</strong>
                <br><small class="text-muted">{{ $u->user->email }}</small>
              </td>
              <td>
                @if($u->level)
                  <span class="badge bg-primary">{{ $u->level->kode }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>{{ $u->jenis_usaha ?? '—' }}</td>
              <td><span class="badge bg-light text-dark">{{ $u->penjualan_count }} penjualan</span></td>
              <td><small>{{ $u->inventory_method }}</small></td>
              <td>
                @if($u->status === 'aktif')
                  <span class="badge bg-success">Aktif</span>
                @else
                  <span class="badge bg-secondary">Nonaktif</span>
                @endif
              </td>
              <td class="text-end">
                <a href="{{ route('admin.umkm.show', $u->id) }}" class="btn btn-sm btn-action btn-action-view" title="Detail"><i data-feather="eye"></i> Detail</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data UMKM.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @if($umkmList->hasPages())
    <div class="mt-3">{{ $umkmList->links() }}</div>
  @endif

</div>
@endsection
