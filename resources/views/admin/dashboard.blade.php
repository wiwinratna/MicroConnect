@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/minect-theme/dashboard.css') }}">
@endpush

@section('content')
<div class="container-fluid p-0">
  <div class="dash-head">
    <div>
      <h1>Monitoring UMKM</h1>
      <p>Wawasan performa dan status kesehatan UMKM binaan KADIN.</p>
      <span class="badge bg-primary-subtle dash-period"><i data-lucide="calendar"></i> Periode: {{ $periodeString }}</span>
    </div>
    <form method="GET" action="{{ route('admin.dashboard') }}" class="dash-filter">
      <input type="month" name="start_month" class="form-control form-control-sm" aria-label="Bulan awal" value="{{ $startMonth }}" required>
      <i data-lucide="arrow-right"></i>
      <input type="month" name="end_month" class="form-control form-control-sm" aria-label="Bulan akhir" value="{{ $endMonth }}" required>
      <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
      <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-light" title="Reset Data" aria-label="Reset Data"><i data-lucide="rotate-ccw" style="width: 15px; height: 15px;"></i></a>
    </form>
  </div>

  {{-- 1. SUMMARY CARDS --}}
  <div class="stat-grid">
    <div class="card">
      <div class="card-body stat-tile">
        <div>
          <div class="stat-label">Total UMKM Aktif</div>
          <div class="stat-value">{{ $totalUmkmAktif }}</div>
          <div class="stat-note">
            @forelse($levelCounts as $kode => $total)
              @if($kode)
                <span class="badge bg-primary-subtle me-1">{{ $kode }}: {{ $total }}</span>
              @endif
            @empty
              —
            @endforelse
          </div>
        </div>
        <span class="stat-icon"><i data-lucide="users"></i></span>
      </div>
    </div>

    <div class="card">
      <div class="card-body stat-tile">
        <div>
          <div class="stat-label">UMKM Tanpa Transaksi</div>
          <div class="stat-value">{{ $umkmNolTrx }}</div>
          <div class="stat-note">0 Transaksi pada rentang waktu terpilih</div>
        </div>
        <span class="stat-icon is-warning"><i data-lucide="activity"></i></span>
      </div>
    </div>

    <div class="card">
      <div class="card-body stat-tile">
        <div>
          <div class="stat-label">Tiket Pengaduan Terbuka</div>
          <div class="stat-value">{{ $openTickets }}</div>
          <div class="stat-note">Butuh pantauan/tindak lanjut segera</div>
        </div>
        <span class="stat-icon is-danger"><i data-lucide="message-circle"></i></span>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    {{-- Perlu Perhatian --}}
    <div class="col-12 col-xl-6 d-flex">
      <div class="card flex-fill mb-0">
        <div class="card-header">
          <h2 class="panel-title">
            <span class="stat-icon is-danger"><i data-lucide="triangle-alert"></i></span>
            Perlu Pemantauan
          </h2>
        </div>
        @if(count($warningUmkm))
          <ul class="dash-list">
            @foreach($warningUmkm as $u)
              <li>
                <div>
                  <div class="name">{{ $u->nama_usaha }} <span class="badge bg-secondary-subtle ms-1">{{ $u->kode_level }}</span></div>
                  <span class="badge bg-danger-subtle dash-reason"><i data-lucide="info"></i>{{ $u->alasan_prioritas }}</span>
                </div>
                <div class="text-end">
                  <div class="fw-bold small mb-1 money">{{ rupiah($u->omzet) }}</div>
                  <span class="badge bg-{{ $u->badge_color }}-subtle">{{ $u->status_kesehatan }}</span>
                </div>
              </li>
            @endforeach
          </ul>
        @else
          <div class="dash-empty">
            <i data-lucide="circle-check"></i>
            <div>Luar biasa! Tidak ada UMKM yang butuh perhatian serius.</div>
          </div>
        @endif
        <div class="card-footer text-center mt-auto">
          <a href="{{ route('admin.umkm.index') }}" class="btn btn-sm btn-ghost">Lihat semua analitik <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i></a>
        </div>
      </div>
    </div>

    {{-- Performa Terbaik --}}
    <div class="col-12 col-xl-6 d-flex">
      <div class="card flex-fill mb-0">
        <div class="card-header">
          <h2 class="panel-title">
            <span class="stat-icon is-success"><i data-lucide="award"></i></span>
            UMKM Terbaik Periode Ini
          </h2>
        </div>
        @if(count($topUmkm))
          <ul class="dash-list">
            @foreach($topUmkm as $u)
              <li>
                <div class="d-flex align-items-center gap-3">
                  <span class="rank-number">#{{ $loop->iteration }}</span>
                  <div>
                    <div class="name">{{ $u->nama_usaha }} <span class="badge bg-primary-subtle ms-1">{{ $u->kode_level }}</span></div>
                    <div class="stat-note">
                      <span class="me-2">Estimasi Laba: <span class="text-success fw-bold">{{ rupiah($u->laba_bersih) }}</span></span>
                      <span>Margin Profit: {{ is_null($u->margin) ? '—' : $u->margin.'%' }}</span>
                    </div>
                  </div>
                </div>
                <div class="text-end ms-auto">
                  <div class="stat-note">Omzet</div>
                  <div class="fw-bold text-primary money">{{ rupiah($u->omzet) }}</div>
                </div>
              </li>
            @endforeach
          </ul>
        @else
          <div class="dash-empty">
            <i data-lucide="chart-column"></i>
            <div>Belum ada UMKM dengan performa "Sehat" pada periode ini.</div>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Tabel All UMKM --}}
  <div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
      <h2 class="panel-title">Daftar Pertumbuhan Seluruh UMKM</h2>
      <a href="{{ route('admin.umkm.index') }}" class="btn btn-sm btn-outline-primary">Lihat Master Data</a>
    </div>

    <div class="table-responsive">
      <table class="table table-hover my-0 align-middle">
        <thead>
          <tr>
            <th>Identitas Usaha</th>
            <th>Level</th>
            <th class="text-center">Jumlah Transaksi</th>
            <th class="text-end">Omzet (Periode Ini)</th>
            <th class="text-end">Estimasi Laba</th>
            <th>Margin (%)</th>
            <th>Status Kesehatan</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($allUmkm as $u)
            <tr>
              <td>
                <div class="fw-bold">{{ $u->nama_usaha }}</div>
                <div class="stat-note">Mulai bergabung: {{ \Carbon\Carbon::parse($u->created_at)->format('d M Y') }}</div>
              </td>
              <td><span class="badge bg-secondary-subtle">{{ $u->kode_level }}</span></td>
              <td class="text-center">{{ $u->trx }}</td>
              <td class="text-end fw-bold money">{{ rupiah($u->omzet) }}</td>
              <td class="text-end money">
                <span class="{{ $u->laba_bersih < 0 ? 'text-danger' : 'text-success fw-bold' }}">
                  {{ rupiah($u->laba_bersih) }}
                </span>
              </td>
              <td class="fw-bold {{ $u->margin !== null ? ($u->margin < 10 ? 'text-warning' : 'text-success') : '' }}">
                {{ is_null($u->margin) ? '—' : $u->margin.'%' }}
              </td>
              <td><span class="badge bg-{{ $u->badge_color }}-subtle">{{ $u->status_kesehatan }}</span></td>
              <td class="text-end">
                <a href="{{ route('admin.umkm.show', $u->id) }}" class="btn btn-sm btn-action btn-action-view" title="Detail"><i data-lucide="eye"></i> Telusuri</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="dash-empty">
              <i data-lucide="database"></i><br>
              Belum ada data rekaman UMKM yang valid.
            </td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($allUmkm->hasPages())
    <div class="card-footer">
      <div class="d-flex justify-content-center">
        {{ $allUmkm->links() }}
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
