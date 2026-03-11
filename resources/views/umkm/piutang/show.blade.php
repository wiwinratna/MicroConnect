@extends('layouts.umkm')
@section('title', 'Detail Piutang ' . $piutang->kode_piutang)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Detail</strong> Piutang</h1>
    <p class="text-muted mb-0">{{ $piutang->kode_piutang }} &mdash; {{ $piutang->pelanggan->nama_pelanggan }}</p>
  </div>
  <a href="{{ route('umkm.piutang.index') }}" class="btn btn-outline-secondary">← Kembali</a>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">

  {{-- Info Piutang --}}
  <div class="col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold">Informasi Tagihan</div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted">Pelanggan</td><td><strong>{{ $piutang->pelanggan->nama_pelanggan }}</strong></td></tr>
          <tr><td class="text-muted">No WA</td><td>{{ $piutang->pelanggan->no_whatsapp ?? '-' }}</td></tr>
          <tr><td class="text-muted">Tanggal</td><td>{{ $piutang->tanggal->isoFormat('D MMMM Y') }}</td></tr>
          <tr><td class="text-muted">Jatuh Tempo</td>
            <td>
              {{ $piutang->jatuh_tempo->isoFormat('D MMMM Y') }}
              @if($piutang->status !== 'lunas' && $piutang->jatuh_tempo->isPast())
                <span class="badge bg-danger ms-1">Lewat</span>
              @endif
            </td>
          </tr>
          <tr><td class="text-muted">Nominal Awal</td><td>Rp {{ number_format($piutang->nominal_awal, 0, ',', '.') }}</td></tr>
          <tr><td class="text-muted">Sudah Dibayar</td><td class="text-success">Rp {{ number_format($piutang->sudah_dibayar, 0, ',', '.') }}</td></tr>
          <tr><td class="text-muted">Sisa</td>
            <td>
              @if($piutang->sisa > 0)
                <strong class="text-danger">Rp {{ number_format($piutang->sisa, 0, ',', '.') }}</strong>
              @else
                <strong class="text-success">Lunas</strong>
              @endif
            </td>
          </tr>
          <tr><td class="text-muted">Status</td>
            <td>
              @if($piutang->status === 'lunas')
                <span class="badge bg-success">Lunas</span>
              @elseif($piutang->status === 'sebagian')
                <span class="badge bg-warning text-dark">Sebagian</span>
              @else
                <span class="badge bg-danger">Belum Lunas</span>
              @endif
            </td>
          </tr>
          @if($piutang->catatan)
            <tr><td class="text-muted">Catatan</td><td>{{ $piutang->catatan }}</td></tr>
          @endif
        </table>
      </div>
    </div>
  </div>

  {{-- Catat Pembayaran + Riwayat --}}
  <div class="col-md-7">

    {{-- Form Bayar (hanya tampil jika belum lunas) --}}
    @if($piutang->status !== 'lunas')
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent fw-semibold">Catat Pembayaran</div>
      <div class="card-body">
        <form method="POST" action="{{ route('umkm.piutang.bayar', $piutang->id) }}">
          @csrf
          @if($errors->any())
            <div class="alert alert-danger py-2 small">
              @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
          @endif
          <div class="row g-2">
            <div class="col-md-4">
              <label class="form-label small">Tanggal Bayar</label>
              <input type="date" name="tanggal_bayar" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Jumlah (Rp)</label>
              <input type="number" name="jumlah_bayar" class="form-control form-control-sm" required
                     min="1" max="{{ $piutang->sisa }}" step="1000"
                     placeholder="Maks: {{ number_format($piutang->sisa, 0, ',', '.') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Metode</label>
              <select name="metode_bayar" class="form-select form-select-sm">
                <option value="">- pilih -</option>
                <option value="tunai">Tunai</option>
                <option value="transfer">Transfer</option>
                <option value="qris">QRIS</option>
              </select>
            </div>
            <div class="col-12">
              <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan (opsional)">
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-success btn-sm">Simpan Pembayaran</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    @endif

    {{-- Riwayat Pembayaran --}}
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">Riwayat Pembayaran</div>
      <div class="card-body p-0">
        @if($piutang->pembayaran->isEmpty())
          <p class="text-muted text-center py-3 mb-0">Belum ada pembayaran yang dicatat.</p>
        @else
          <table class="table table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($piutang->pembayaran->sortByDesc('tanggal_bayar') as $bayar)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($bayar->tanggal_bayar)->isoFormat('D MMM Y') }}</td>
                  <td class="text-success fw-semibold">Rp {{ number_format($bayar->jumlah_bayar, 0, ',', '.') }}</td>
                  <td>{{ $bayar->metode_bayar ?? '-' }}</td>
                  <td class="text-muted small">{{ $bayar->catatan ?? '-' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection
