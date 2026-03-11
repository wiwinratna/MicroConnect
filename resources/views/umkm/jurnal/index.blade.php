@extends('layouts.umkm')
@section('title','Jurnal Umum')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Jurnal</strong> Umum</h1>
    <p class="text-muted mb-0">Riwayat pencatatan akuntansi seluruh transaksi sistem dan jurnal manual.</p>
  </div>
  <a href="{{ route('umkm.jurnal.create') }}" class="btn btn-primary">+ Jurnal Manual</a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Tanggal</th>
            <th>No. Ref / Keterangan</th>
            <th>Kode Akun</th>
            <th>Nama Akun</th>
            <th class="text-end">Debit (Rp)</th>
            <th class="text-end">Kredit (Rp)</th>
          </tr>
        </thead>
        <tbody>
          @forelse($jurnal as $j)
            <tr>
              <td>{{ $j->tanggal }}</td>
              <td>{{ $j->keterangan }} <br> <small class="text-muted">Ref: {{ $j->ref_tipe ?: '-' }}</small></td>
              <td>{{ $j->kode_akun }}</td>
              <td>{{ $j->nama_akun }}</td>
              <td class="text-end">{{ number_format($j->debit,0,',','.') }}</td>
              <td class="text-end">{{ number_format($j->kredit,0,',','.') }}</td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted">Belum ada catatan jurnal.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($jurnal->hasPages())
  <div class="card-footer bg-white border-top">
    {{ $jurnal->links() }}
  </div>
  @endif
</div>
@endsection
