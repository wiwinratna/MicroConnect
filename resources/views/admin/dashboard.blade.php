@extends('layouts.admin')

@section('content')
<div class="container-fluid p-0">
  <h1 class="h3 mb-2">Monitoring UMKM</h1>
  <p class="text-muted mb-4">Pengawasan performa dan tingkat kesehatan UMKM binaan KADIN.</p>

  {{-- 1. SUMMARY CARDS --}}
  <div class="row">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-4">
                <p class="text-muted small mb-1">Total UMKM Aktif</p>
                <h3 class="fw-bold text-primary mb-0">{{ $totalUmkmAktif }}</h3>
                <small class="text-muted">Level 1: {{ $levelCounts['LVL1'] ?? 0 }} | Level 2: {{ $levelCounts['LVL2'] ?? 0 }} | Level 3: {{ $levelCounts['LVL3'] ?? 0 }}</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-4">
                <p class="text-muted small mb-1">Total Omzet (Bulan Ini)</p>
                <h3 class="fw-bold text-success mb-0">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-4">
                <p class="text-muted small mb-1">UMKM Pasif</p>
                <h3 class="fw-bold text-secondary mb-0">{{ $umkmNolTrx }}</h3>
                <small class="text-muted">0 Transaksi bulan ini</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-4">
                <p class="text-muted small mb-1">Tiket Terbuka</p>
                <h3 class="fw-bold text-danger mb-0">{{ $openTickets }}</h3>
                <small class="text-muted">Pengaduan/Konsultasi Open</small>
            </div>
        </div>
    </div>
  </div>

  <div class="row">
    {{-- Perlu Perhatian --}}
    <div class="col-12 col-lg-6 d-flex">
      <div class="card flex-fill">
        <div class="card-header bg-soft-warning">
          <h5 class="card-title mb-0">🚨 Perlu Perhatian (Top 5)</h5>
          <small class="text-muted">Prioritas: Tidak aktif atau margin/omzet rendah</small>
        </div>
        <div class="card-body">
          @forelse($warningUmkm as $u)
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
              <div>
                <div class="fw-semibold">{{ $u->nama_usaha }} <span class="badge bg-secondary ms-1">{{ $u->kode_level }}</span></div>
                <small class="text-muted">
                  {{ $u->trx }} trx &bull; 
                  Margin: {!! is_null($u->margin) ? '-' : ($u->margin < 10 ? '<span class="text-danger">'.$u->margin.'%</span>' : $u->margin.'%') !!}
                </small>
              </div>
              <div class="text-end">
                <div class="fw-semibold">Rp {{ number_format($u->omzet,0,',','.') }}</div>
                <span class="badge bg-{{ $u->badge_color }}">{{ $u->status_kesehatan }}</span>
              </div>
            </div>
          @empty
            <div class="text-muted text-center">Bagus! Tidak ada UMKM yang masuk kategori kritis.</div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Performa Terbaik --}}
    <div class="col-12 col-lg-6 d-flex">
      <div class="card flex-fill">
        <div class="card-header bg-soft-success">
          <h5 class="card-title mb-0">⭐ Performa Terbaik (Top 5)</h5>
          <small class="text-muted">Sehat: Omzet stabil dan Margin Profit baik</small>
        </div>
        <div class="card-body">
          @forelse($topUmkm as $i => $u)
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
              <div>
                <div class="fw-semibold">#{{ $i+1 }} {{ $u->nama_usaha }} <span class="badge bg-secondary ms-1">{{ $u->kode_level }}</span></div>
                <small class="text-muted">
                  {{ $u->trx }} trx &bull; Laba: Rp {{ number_format($u->laba_bersih, 0, ',', '.') }} 
                  <span class="text-success fw-bold">({{ is_null($u->margin) ? '-' : $u->margin.'%' }})</span>
                </small>
              </div>
              <div class="text-end">
                <div class="text-muted small">Omzet</div>
                <div class="fw-bold text-dark">Rp {{ number_format($u->omzet,0,',','.') }}</div>
              </div>
            </div>
          @empty
            <div class="text-muted text-center">Belum ada UMKM dengan performa sehat bulan ini.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- Tabel All UMKM --}}
  <div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Daftar Seluruh UMKM</h5>
    </div>

    <div class="table-responsive">
      <table class="table table-hover my-0 align-middle">
        <thead class="bg-light">
          <tr>
            <th>Identitas UMKM</th>
            <th>Level</th>
            <th>Trx</th>
            <th>Omzet (Bulan Ini)</th>
            <th>Laba Bersih</th>
            <th>Profit (%)</th>
            <th>Status Kesehatan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($allUmkm as $u)
            <tr>
              <td>
                <div class="fw-semibold text-dark">{{ $u->nama_usaha }}</div>
              </td>
              <td><span class="badge bg-secondary">{{ $u->kode_level }}</span></td>
              <td>{{ $u->trx }}</td>
              <td>Rp {{ number_format($u->omzet, 0, ',', '.') }}</td>
              <td>
                 <span class="{{ $u->laba_bersih < 0 ? 'text-danger' : 'text-success' }}">
                    Rp {{ number_format($u->laba_bersih, 0, ',', '.') }}
                 </span>
              </td>
              <td class="fw-semibold {{ $u->margin !== null && $u->margin < 10 ? 'text-warning' : ($u->margin !== null && $u->margin >= 10 ? 'text-success' : '') }}">
                  {{ is_null($u->margin) ? '-' : $u->margin.'%' }}
              </td>
              <td><span class="badge bg-{{ $u->badge_color }}">{{ $u->status_kesehatan }}</span></td>
              <td class="text-end">
                <a href="{{ route('admin.umkm.show', $u->id) }}" class="btn btn-sm btn-outline-primary">Detail</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-muted text-center py-4">Belum ada UMKM terdaftar atau memiliki nama usaha.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-body">
      {{ $allUmkm->links() }}
    </div>
  </div>

</div>
@endsection
