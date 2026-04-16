@extends('layouts.admin')
@section('title', 'Kelola UMKM')

@section('content')
<div class="container-fluid p-0">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Kelola <strong>UMKM</strong></h1>
      <p class="text-muted small mb-0">Manajemen data dan status operasional UMKM binaan.</p>
    </div>
    <a href="{{ route('admin.umkm.create') }}" class="btn btn-primary px-3 rounded-pill">
        <i data-feather="plus" class="me-1" style="width: 16px;"></i> Daftarkan UMKM Baru
    </a>
  </div>

  {{-- KPI Row --}}
  <div class="row g-3 mb-4">
    <div class="col-sm-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body py-3 d-flex align-items-center">
          <div class="p-2 bg-primary-subtle text-primary rounded-3 me-3">
            <i data-feather="users" style="width: 20px; height: 20px;"></i>
          </div>
          <div>
            <div class="text-muted x-small text-uppercase fw-bold">Total UMKM</div>
            <div class="h4 fw-bold mb-0">{{ $totalUmkm }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body py-3 d-flex align-items-center">
          <div class="p-2 bg-success-subtle text-success rounded-3 me-3">
            <i data-feather="user-check" style="width: 20px; height: 20px;"></i>
          </div>
          <div>
            <div class="text-muted x-small text-uppercase fw-bold">UMKM Aktif</div>
            <div class="h4 fw-bold mb-0 text-success">{{ $totalAktif }}</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body py-3 d-flex align-items-center">
          <div class="p-2 bg-danger-subtle text-danger rounded-3 me-3">
            <i data-feather="clock" style="width: 20px; height: 20px;"></i>
          </div>
          <div>
            <div class="text-muted x-small text-uppercase fw-bold">Belum Bayar Iuran</div>
            <div class="h4 fw-bold mb-0 text-danger">{{ $iuranBelumBayar }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Filter & Table Card --}}
  <div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom">
        <form method="GET" class="d-flex align-items-center gap-2">
            <div class="flex-grow-1">
              <div class="input-group input-group-sm border rounded-3 overflow-hidden" style="border-color: #e2e8f0 !important;">
                <span class="input-group-text bg-light border-0"><i data-feather="search" style="width: 14px;"></i></span>
                <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Cari nama usaha / kode..." value="{{ request('search') }}" style="box-shadow: none;">
              </div>
            </div>
            <div style="width: 150px;">
              <select name="level" class="form-select form-select-sm border-0 bg-light rounded-3 shadow-none">
                <option value="">Semua Level</option>
                @foreach($levels as $lv)
                  <option value="{{ $lv->id }}" {{ request('level') == $lv->id ? 'selected' : '' }}>{{ $lv->nama_level }}</option>
                @endforeach
              </select>
            </div>
            <div style="width: 140px;">
              <select name="status" class="form-select form-select-sm border-0 bg-light rounded-3 shadow-none">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
              </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm rounded-3 px-3 py-2 d-flex align-items-center justify-content-center" title="Filter Data">
                <i data-feather="filter" style="width: 14px; height: 14px;"></i>
            </button>
            @if(request()->anyFilled(['search', 'level', 'status']))
                <a href="{{ route('admin.umkm.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-2 py-2 d-flex align-items-center justify-content-center" title="Reset Filter">
                    <i data-feather="refresh-cw" style="width: 14px; height: 14px;"></i>
                </a>
            @endif
        </form>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0 table-borderless align-middle">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Kode</th>
            <th>Nama Usaha</th>
            <th>Level</th>
            <th>Jenis Usaha</th>
            <th>Aktivitas</th>
            <th>Metode Inv</th>
            <th>Status</th>
            <th class="pe-4"></th>
          </tr>
        </thead>
        <tbody>
          @forelse($umkmList as $u)
            <tr class="border-bottom-faint">
              <td class="ps-4"><code>{{ $u->kode_umkm }}</code></td>
              <td>
                <div class="fw-bold text-dark">{{ $u->nama_usaha ?? '—' }}</div>
                <div class="text-muted x-small">{{ $u->user->email }}</div>
              </td>
              <td>
                @if($u->level)
                  <span class="badge bg-primary-subtle text-primary fw-medium">{{ $u->level->kode }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td>{{ $u->jenis_usaha ?? '—' }}</td>
              <td>
                <span class="badge bg-light text-dark fw-normal border">
                    <i data-feather="shopping-cart" class="me-1" style="width: 10px;"></i>{{ $u->penjualan_count }} trx
                </span>
              </td>
              <td><span class="text-uppercase x-small fw-bold text-muted">{{ $u->inventory_method }}</span></td>
              <td>
                @if($u->status === 'aktif')
                  <span class="badge bg-success-subtle text-success rounded-pill px-2">Aktif</span>
                @else
                  <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2">Nonaktif</span>
                @endif
              </td>
              <td class="text-end pe-4">
                <a href="{{ route('admin.umkm.show', $u->id) }}" class="btn btn-sm btn-action btn-action-view px-3" title="Detail">
                    <i data-feather="eye" class="me-1"></i> Detail
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted py-5">
                <i data-feather="info" class="mb-2" style="width: 32px; height: 32px; opacity: 0.3;"></i><br>
                Belum ada data UMKM yang sesuai dengan kriteria.
            </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    
    @if($umkmList->hasPages())
    <div class="card-footer bg-white border-0 py-3">
        <div class="d-flex justify-content-center">
            {{ $umkmList->links() }}
        </div>
    </div>
    @endif
  </div>

</div>

<style>
    .x-small { font-size: 0.7rem; }
    .bg-primary-subtle { background-color: rgba(59, 125, 221, 0.1) !important; }
    .bg-success-subtle { background-color: rgba(28, 187, 140, 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }
    .bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1) !important; }
    .text-primary { color: #3b7ddd !important; }
    .text-success { color: #1cbb8c !important; }
    .text-danger { color: #dc3545 !important; }
    .text-secondary { color: #6c757d !important; }
    .border-bottom-faint { border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important; }
</style>
@endsection
