@extends('layouts.admin')
@section('title', 'Kotak Notifikasi')

@php
  $labelJenis = [
    'registrasi_diterima' => 'Registrasi diterima',
    'akun_aktif'          => 'Akun aktif',
    'pengajuan_ditolak'   => 'Pengajuan ditolak',
    'periode_dibuka'      => 'Periode dibuka',
    'hasil_penilaian'     => 'Hasil penilaian',
  ];
  $warnaStatus = ['terkirim' => 'success', 'gagal' => 'danger', 'antri' => 'secondary'];
@endphp

@section('content')
<div class="container-fluid p-0">

  <div class="mb-4">
    <h1 class="h3 mb-1">Kotak <strong>Notifikasi</strong></h1>
    <p class="text-muted small mb-0">
      Semua notifikasi sistem. Driver simulasi tidak mengirim apa pun ke luar; pesan WhatsApp hanya tercatat di sini.
    </p>
  </div>

  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
      <select name="jenis" class="form-select form-select-sm">
        <option value="">Semua jenis</option>
        @foreach($jenisList as $j)
          <option value="{{ $j }}" @selected(request('jenis') === $j)>{{ $labelJenis[$j] ?? $j }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3">
      <select name="saluran" class="form-select form-select-sm">
        <option value="">Semua saluran</option>
        <option value="whatsapp" @selected(request('saluran') === 'whatsapp')>WhatsApp</option>
        <option value="aplikasi" @selected(request('saluran') === 'aplikasi')>Aplikasi</option>
      </select>
    </div>
    <div class="col-md-3">
      <select name="status" class="form-select form-select-sm">
        <option value="">Semua status</option>
        @foreach(['antri', 'terkirim', 'gagal'] as $s)
          <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
    </div>
  </form>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-top mb-0">
        <thead class="table-light">
          <tr>
            <th>Waktu</th>
            <th>Jenis</th>
            <th>Saluran</th>
            <th>Tujuan</th>
            <th>Pesan</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($notifikasi as $n)
            <tr>
              <td class="text-nowrap">{{ $n->created_at?->format('d/m/Y H:i') }}</td>
              <td>{{ $labelJenis[$n->jenis] ?? $n->jenis }}</td>
              <td class="text-capitalize">{{ $n->saluran }}</td>
              <td class="text-nowrap">{{ $n->no_tujuan }}</td>
              <td style="min-width: 280px;">{{ $n->isi }}</td>
              <td>
                <span class="badge bg-{{ $warnaStatus[$n->status] ?? 'secondary' }}">{{ ucfirst($n->status) }}</span>
                @if($n->pesan_galat)
                  <div class="text-danger small mt-1">{{ $n->pesan_galat }}</div>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada notifikasi.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $notifikasi->links() }}</div>

</div>
@endsection
