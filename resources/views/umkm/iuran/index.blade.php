@extends('layouts.umkm')
@section('title', 'Iuran Bulanan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Iuran</strong> Bulanan</h1>
    <p class="text-muted mb-0">Status pembayaran iuran aplikasi MINECT per bulan.</p>
  </div>
</div>

{{-- Status bulan ini --}}
@php
  $bulanIni = $iuranList->firstWhere('periode', now()->format('Y-m'));
@endphp

@if($bulanIni)
  <div class="alert {{ $bulanIni->status === 'lunas' ? 'alert-success' : 'alert-warning' }} d-flex align-items-center gap-3 mb-4">
    <div>
      @if($bulanIni->status === 'lunas')
        <strong>✅ Iuran bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $bulanIni->periode)->isoFormat('MMMM Y') }} sudah dibayar.</strong>
        @if($bulanIni->dibayar_pada)
          <br><small class="text-muted">Dibayar pada: {{ $bulanIni->dibayar_pada->isoFormat('D MMMM Y, HH:mm') }}</small>
        @endif
      @else
        <strong>⚠️ Iuran bulan {{ \Carbon\Carbon::createFromFormat('Y-m', $bulanIni->periode)->isoFormat('MMMM Y') }} belum dibayar.</strong>
        <br>
        <small>Nominal: <strong>{{ rupiah($bulanIni->nominal) }}</strong>
        &bull; Jatuh tempo: <strong>{{ $bulanIni->jatuh_tempo?->isoFormat('D MMMM Y') ?? '-' }}</strong></small>
        <br>
        {{-- Tombol bayar — placeholder (Midtrans diintegrasikan di pengembangan lanjutan) --}}
        <small class="text-muted">Silakan hubungi admin KADIN untuk informasi pembayaran.</small>
      @endif
    </div>
  </div>
@endif

{{-- Riwayat Iuran --}}
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold">Riwayat Iuran</div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0 table-borderless align-middle">
      <thead class="table-light">
        <tr>
          <th>Periode</th>
          <th>Nominal</th>
          <th>Jatuh Tempo</th>
          <th>Status</th>
          <th>Dibayar Pada</th>
        </tr>
      </thead>
      <tbody>
        @forelse($iuranList as $iuran)
          <tr>
            <td>
              <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $iuran->periode)->isoFormat('MMMM Y') }}</strong>
              @if($iuran->periode === now()->format('Y-m'))
                <span class="badge bg-primary ms-1">Bulan Ini</span>
              @endif
            </td>
            <td class="text-end fw-medium">{{ rupiah($iuran->nominal) }}</td>
            <td>{{ $iuran->jatuh_tempo?->isoFormat('D MMM Y') ?? '-' }}</td>
            <td>
              @if($iuran->status === 'lunas')
                <span class="badge bg-success">Lunas</span>
              @elseif($iuran->status === 'terlambat')
                <span class="badge bg-danger">Terlambat</span>
              @else
                <span class="badge bg-warning text-dark">Belum Bayar</span>
              @endif
            </td>
            <td class="text-muted small">{{ $iuran->dibayar_pada?->isoFormat('D MMM Y') ?? '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data iuran.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">
  <small class="text-muted">
    💡 Iuran dikenakan sebesar <strong>{{ rupiah(env('IURAN_DEFAULT', 50000)) }}</strong>/bulan untuk semua level UMKM.
    Integrasi pembayaran digital (Midtrans) tersedia di pengembangan lanjutan.
  </small>
</div>
@endsection
