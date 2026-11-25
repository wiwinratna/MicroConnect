@extends('layouts.admin')
@section('title', 'Periode Penilaian')

@section('content')
<div class="container-fluid p-0">

  <div class="mb-4">
    <h1 class="h3 mb-1">Periode <strong>Penilaian Ulang</strong></h1>
    <p class="text-muted small mb-0">
      Membuka periode memberi tahu semua UMKM aktif. Hanya satu periode yang boleh dibuka pada satu waktu.
      Penilaian dapat diisi UMKM selama periode berstatus dibuka dan hari ini berada di antara tanggal mulai dan selesai.
    </p>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <h2 class="h5 mb-3">Buka periode baru</h2>
      <form method="POST" action="{{ route('admin.periode.store') }}" class="row g-3 align-items-end" id="formBukaPeriode">
        @csrf
        <div class="col-md-5">
          <label class="form-label" for="nama">Nama periode</label>
          <input type="text" id="nama" name="nama" class="form-control" maxlength="150"
                 value="{{ old('nama') }}" placeholder="Penilaian ulang 2026" required>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="tanggal_mulai">Tanggal mulai</label>
          <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai') }}" required>
        </div>
        <div class="col-md-3">
          <label class="form-label" for="tanggal_selesai">Tanggal selesai</label>
          <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai') }}" required>
        </div>
        <div class="col-md-1 d-grid">
          <button type="submit" class="btn btn-primary">Buka</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Periode</th>
            <th>Rentang</th>
            <th>Status</th>
            <th class="text-center">Terkirim</th>
            <th class="text-center">Menunggu tinjauan</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($periode as $p)
            <tr>
              <td class="fw-semibold">{{ $p->nama }}</td>
              <td class="text-nowrap">{{ $p->tanggal_mulai->format('d/m/Y') }} - {{ $p->tanggal_selesai->format('d/m/Y') }}</td>
              <td>
                <span class="badge bg-{{ $p->status === 'dibuka' ? 'success' : 'secondary' }}">{{ ucfirst($p->status) }}</span>
                @if($p->status === 'dibuka' && !$p->berlaku())
                  <div class="text-muted small">Di luar rentang tanggal</div>
                @endif
              </td>
              <td class="text-center">{{ $p->penilaian_count }}</td>
              <td class="text-center">{{ $p->menunggu_count }}</td>
              <td class="text-end">
                @if($p->status === 'dibuka')
                  <form method="POST" action="{{ route('admin.periode.tutup', $p->id) }}" class="d-inline"
                        onsubmit="return confirm('Tutup periode ini?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">Tutup</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada periode.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  // Cegah klik ganda pada tombol Buka.
  document.getElementById('formBukaPeriode').addEventListener('submit', function (e) {
    e.target.querySelector('button[type="submit"]').disabled = true;
  });
</script>
@endpush
