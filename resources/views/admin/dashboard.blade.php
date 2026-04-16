@extends('layouts.admin')

@section('content')
<div class="container-fluid p-0">
  <div class="row mb-3">
    <div class="col-auto">
      <h1 class="h3 mb-1">Monitoring <strong>UMKM</strong></h1>
      <p class="text-muted small">Wawasan performa dan status kesehatan UMKM binaan KADIN.</p>
    </div>
  </div>

  {{-- 1. SUMMARY CARDS --}}
  <div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted text-uppercase fw-bold x-small mb-1">Total UMKM Aktif</p>
                        <h2 class="fw-bold mb-1">{{ $totalUmkmAktif }}</h2>
                        <div class="text-muted small">
                          @forelse($levelCounts as $kode => $total)
                              @if($kode)
                                <span class="badge bg-primary-subtle text-primary fw-medium rounded-pill me-1" style="font-size: 10px;">{{ $kode }}: {{ $total }}</span>
                              @endif
                          @empty
                              —
                          @endforelse
                        </div>
                    </div>
                    <div class="ms-3">
                        <div class="p-3 bg-primary-subtle rounded-4 text-primary">
                            <i data-feather="users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted text-uppercase fw-bold x-small mb-1">UMKM Tanpa Transaksi</p>
                        <h2 class="fw-bold mb-1 text-warning">{{ $umkmNolTrx }}</h2>
                        <small class="text-muted">0 Transaksi dalam 30 hari terakhir</small>
                    </div>
                    <div class="ms-3">
                        <div class="p-3 bg-warning-subtle rounded-4 text-warning">
                            <i data-feather="activity"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted text-uppercase fw-bold x-small mb-1">Tiket Support Open</p>
                        <h2 class="fw-bold mb-1 text-danger">{{ $openTickets }}</h2>
                        <small class="text-muted">Butuh respon segera oleh Admin</small>
                    </div>
                    <div class="ms-3">
                        <div class="p-3 bg-danger-subtle rounded-4 text-danger">
                            <i data-feather="message-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>

  <div class="row g-4 mb-4">
    {{-- Perlu Perhatian --}}
    <div class="col-12 col-lg-6 d-flex">
      <div class="card border-0 shadow-sm flex-fill">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
          <div class="p-2 bg-danger-subtle text-danger rounded-3 me-3">
            <i data-feather="alert-triangle" style="width: 18px; height: 18px;"></i>
          </div>
          <h5 class="card-title mb-0 fw-bold">Prioritas Pendampingan</h5>
        </div>
        <div class="card-body px-0 py-0">
          <div class="list-group list-group-flush">
            @forelse($warningUmkm as $u)
              <div class="list-group-item border-0 py-3 px-4 bg-transparent border-bottom-faint">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-bold text-dark mb-1">
                      {{ $u->nama_usaha }} 
                      <span class="badge bg-secondary-subtle text-secondary ms-1 fw-normal">{{ $u->kode_level }}</span>
                    </div>
                    <div class="text-muted x-small">
                      <span class="me-2"><i data-feather="repeat" class="me-1" style="width: 10px;"></i>{{ $u->trx }} transaksi</span>
                      <span><i data-feather="pie-chart" class="me-1" style="width: 10px;"></i>Margin: {!! is_null($u->margin) ? '—' : ($u->margin < 10 ? '<span class="text-danger">'.$u->margin.'%</span>' : $u->margin.'%') !!}</span>
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="fw-bold text-dark small mb-1">{{ rupiah($u->omzet) }}</div>
                    <span class="badge bg-{{ $u->badge_color }}-subtle text-{{ $u->badge_color }} rounded-pill px-2">{{ $u->status_kesehatan }}</span>
                  </div>
                </div>
              </div>
            @empty
              <div class="p-5 text-center">
                <div class="text-success mb-2"><i data-feather="check-circle" style="width: 48px; height: 48px;"></i></div>
                <div class="text-muted">Luar biasa! Tidak ada UMKM yang butuh perhatian serius.</div>
              </div>
            @endforelse
          </div>
        </div>
        <div class="card-footer bg-white text-center border-0 py-3">
            <a href="{{ route('admin.umkm.index') }}" class="btn btn-sm btn-link text-primary p-0">Lihat semua analitik <i data-feather="arrow-right" class="ms-1" style="width: 14px;"></i></a>
        </div>
      </div>
    </div>

    {{-- Performa Terbaik --}}
    <div class="col-12 col-lg-6 d-flex">
      <div class="card border-0 shadow-sm flex-fill">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
          <div class="p-2 bg-success-subtle text-success rounded-3 me-3">
            <i data-feather="award" style="width: 18px; height: 18px;"></i>
          </div>
          <h5 class="card-title mb-0 fw-bold">UMKM Terbaik Bulan Ini</h5>
        </div>
        <div class="card-body px-0 py-0">
          <div class="list-group list-group-flush">
            @forelse($topUmkm as $i => $u)
              <div class="list-group-item border-0 py-3 px-4 bg-transparent border-bottom-faint">
                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                    <div class="rank-number me-3 text-muted fw-bold">#{{ $i+1 }}</div>
                    <div>
                      <div class="fw-bold text-dark mb-1">
                        {{ $u->nama_usaha }} 
                        <span class="badge bg-primary-subtle text-primary ms-1 fw-normal">{{ $u->kode_level }}</span>
                      </div>
                      <div class="text-muted x-small">
                        <span class="me-2">Profit: <span class="text-success fw-bold">{{ rupiah($u->laba_bersih) }}</span></span>
                        <span>Royalty: {{ is_null($u->margin) ? '—' : $u->margin.'%' }}</span>
                      </div>
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="text-muted x-small mb-1">Omzet</div>
                    <div class="fw-bold text-primary">{{ rupiah($u->omzet) }}</div>
                  </div>
                </div>
              </div>
            @empty
              <div class="p-5 text-center">
                <div class="text-muted mb-2"><i data-feather="bar-chart-2" style="width: 48px; height: 48px; opacity: 0.3;"></i></div>
                <div class="text-muted">Belum ada UMKM dengan performa "Sehat" bulan ini.</div>
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Tabel All UMKM --}}
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0 fw-bold text-dark">Daftar Pertumbuhan Seluruh UMKM</h5>
      <a href="{{ route('admin.umkm.index') }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill">Lihat Master Data</a>
    </div>

    <div class="table-responsive">
      <table class="table table-hover my-0 align-middle table-borderless">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Identitas Usaha</th>
            <th>Level</th>
            <th>Volume Trx</th>
            <th>Omzet (Rolling)</th>
            <th>Estimasi Laba</th>
            <th>Margin (%)</th>
            <th>Kesehatan</th>
            <th class="pe-4 text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($allUmkm as $u)
            <tr class="border-bottom-faint">
              <td class="ps-4">
                <div class="fw-bold text-dark">{{ $u->nama_usaha }}</div>
                <div class="text-muted small" style="font-size: 11px;">Mulai bergabung: {{ \Carbon\Carbon::parse($u->created_at)->format('d M Y') }}</div>
              </td>
              <td><span class="badge bg-secondary-subtle text-secondary">{{ $u->kode_level }}</span></td>
              <td class="text-center">{{ $u->trx }}</td>
              <td class="text-end fw-bold text-dark">{{ rupiah($u->omzet) }}</td>
              <td class="text-end">
                 <span class="{{ $u->laba_bersih < 0 ? 'text-danger' : 'text-success fw-bold' }}">
                    {{ rupiah($u->laba_bersih) }}
                 </span>
              </td>
              <td class="fw-bold {{ $u->margin !== null ? ($u->margin < 10 ? 'text-warning' : 'text-success') : '' }}">
                  {{ is_null($u->margin) ? '—' : $u->margin.'%' }}
              </td>
              <td>
                <span class="badge bg-{{ $u->badge_color }}-subtle text-{{ $u->badge_color }} rounded-pill">{{ $u->status_kesehatan }}</span>
              </td>
              <td class="pe-4 text-end">
                <a href="{{ route('admin.umkm.show', $u->id) }}" class="btn btn-sm btn-action btn-action-view" title="Detail"><i data-feather="eye"></i> Telusuri</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-muted text-center py-5">
              <i data-feather="database" class="mb-2" style="width: 32px; height: 32px;"></i><br>
              Belum ada data rekaman UMKM yang valid.
            </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($allUmkm->hasPages())
    <div class="card-footer bg-white border-0 py-3">
      <div class="d-flex justify-content-center">
        {{ $allUmkm->links() }}
      </div>
    </div>
    @endif
  </div>

</div>

<style>
    .x-small { font-size: 0.7rem; letter-spacing: 0.05em; }
    .bg-primary-subtle { background-color: rgba(59, 125, 221, 0.1) !important; }
    .bg-warning-subtle { background-color: rgba(252, 185, 44, 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }
    .bg-success-subtle { background-color: rgba(28, 187, 140, 0.1) !important; }
    .bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1) !important; }
    
    .text-primary { color: #3b7ddd !important; }
    .text-warning { color: #f59e0b !important; }
    .text-danger { color: #dc3545 !important; }
    .text-success { color: #1cbb8c !important; }
    .text-secondary { color: #6c757d !important; }
    
    .rounded-4 { border-radius: 12px !important; }
    .border-bottom-faint { border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important; }
    .rank-number { width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border-radius: 50%; font-size: 0.75rem; }
    
    .card-title { font-size: 0.95rem; }
    .list-group-item:last-child { border-bottom: none !important; }
</style>
@endsection
