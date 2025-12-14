@extends('layouts.admin')

@section('content')
<div class="container-fluid p-0">
  <h1 class="h3 mb-2">Dashboard</h1>
  <p class="text-muted mb-4">Ringkasan performa UMKM bulan ini (per UMKM).</p>

  <div class="row">
    {{-- Perlu Perhatian --}}
    <div class="col-12 col-lg-7 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0">Perlu Perhatian (5 UMKM)</h5>
          <small class="text-muted">Prioritas pembinaan: tanpa transaksi atau omzet rendah</small>
        </div>
        <div class="card-body">
          @forelse($warningUmkm as $u)
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <div class="fw-semibold">{{ $u->nama_usaha }}</div>
                <small class="text-muted">
                  {{ $u->trx }} trx • {{ number_format($u->qty_terjual) }} item
                  • Margin: {{ is_null($u->margin) ? '-' : $u->margin.'%' }}
                </small>
              </div>
              <div class="text-end">
                <div class="fw-semibold">Rp {{ number_format($u->omzet,0,',','.') }}</div>
                <span class="badge bg-warning">{{ $u->trx == 0 ? 'No Transaction' : 'Low Revenue' }}</span>
              </div>
            </div>
          @empty
            <div class="text-muted">Tidak ada UMKM yang masuk kategori perhatian bulan ini.</div>
          @endforelse

          @if(!$hppColumn)
            <small class="text-muted d-block mt-2">
              Catatan: Profitabilitas/margin tampil jika data HPP per produk tersedia.
            </small>
          @endif
        </div>
      </div>
    </div>

    {{-- Performa Bagus --}}
    <div class="col-12 col-lg-5 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0">Performa Bagus (5 UMKM)</h5>
          <small class="text-muted">UMKM dengan omzet tertinggi bulan ini</small>
        </div>
        <div class="card-body">
          @forelse($topUmkm as $i => $u)
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <div class="fw-semibold">#{{ $i+1 }} {{ $u->nama_usaha }}</div>
                <small class="text-muted">
                  {{ $u->trx }} trx • {{ number_format($u->qty_terjual) }} item
                  • Margin: {{ is_null($u->margin) ? '-' : $u->margin.'%' }}
                </small>
              </div>
              <div class="fw-semibold">Rp {{ number_format($u->omzet,0,',','.') }}</div>
            </div>
          @empty
            <div class="text-muted">Belum ada transaksi bulan ini.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- Tabel All UMKM (per UMKM) --}}
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">All UMKM</h5>
      <a href="{{ route('admin.umkm.create') }}" class="btn btn-primary">Add New UMKM</a>
    </div>

    <div class="table-responsive">
      <table class="table table-hover my-0">
        <thead>
          <tr>
            <th>UMKM</th>
            <th>Omzet (Bulan Ini)</th>
            <th>Transaksi</th>
            <th>Item Terjual</th>
            <th>Profitabilitas</th>
          </tr>
        </thead>
        <tbody>
          @forelse($allUmkm as $u)
            <tr>
              <td>{{ $u->nama_usaha }}</td>
              <td>Rp {{ number_format($u->omzet,0,',','.') }}</td>
              <td>{{ $u->trx }}</td>
              <td>{{ number_format($u->qty_terjual) }}</td>
              <td>{{ is_null($u->margin) ? '-' : $u->margin.'%' }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-muted">Belum ada data.</td></tr>
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
