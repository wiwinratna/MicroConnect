@extends('layouts.admin')
@section('title', 'Tinjau Pengajuan')

@php
  $penilaian  = $pengajuan->penilaian;
  $rekomendasi = $penilaian?->levelRekomendasi;
  $menunggu   = $pengajuan->status === 'menunggu';
  $ya = fn ($v) => $v ? 'Ya' : 'Tidak';
@endphp

@section('content')
<div class="container-fluid p-0">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Tinjau <strong>Pengajuan</strong></h1>
      <p class="text-muted small mb-0">
        Status:
        <span class="badge bg-{{ $menunggu ? 'secondary' : ($pengajuan->status === 'disetujui' ? 'success' : 'danger') }}">
          {{ ucfirst($pengajuan->status) }}
        </span>
      </p>
    </div>
    <a href="{{ route('admin.pengajuan.index') }}" class="btn btn-outline-secondary">Kembali ke daftar</a>
  </div>

  @if($pengajuan->perlu_keputusan_admin)
    <div class="alert alert-warning">
      Calon memilih jawaban di luar batas usaha mikro. Pengajuan tidak ditolak otomatis; keputusan ada pada Anda.
    </div>
  @endif

  <div class="row g-3">
    {{-- DATA --}}
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h2 class="h5 mb-3">Data pemilik dan usaha</h2>
          <dl class="row mb-0">
            <dt class="col-sm-5">Nama pemilik</dt><dd class="col-sm-7">{{ $pengajuan->nama_pemilik }}</dd>
            <dt class="col-sm-5">Email</dt><dd class="col-sm-7">{{ $pengajuan->email }}</dd>
            <dt class="col-sm-5">Username</dt><dd class="col-sm-7">{{ $pengajuan->username }}</dd>
            <dt class="col-sm-5">WhatsApp</dt><dd class="col-sm-7">{{ $pengajuan->no_wa }}</dd>
            <dt class="col-sm-5">Nama usaha</dt><dd class="col-sm-7">{{ $pengajuan->nama_usaha }}</dd>
            <dt class="col-sm-5">Alamat</dt><dd class="col-sm-7">{{ $pengajuan->alamat }}</dd>
            <dt class="col-sm-5">Telepon usaha</dt><dd class="col-sm-7">{{ $pengajuan->no_telepon ?: '-' }}</dd>
            <dt class="col-sm-5">Jenis usaha</dt><dd class="col-sm-7">{{ $pengajuan->jenisUsaha?->nama }}</dd>
            <dt class="col-sm-5">Rumpun</dt><dd class="col-sm-7 text-capitalize">{{ $pengajuan->rumpun }}</dd>
            <dt class="col-sm-5">Memiliki persediaan</dt><dd class="col-sm-7">{{ $ya($pengajuan->memiliki_persediaan) }}</dd>
            <dt class="col-sm-5">Melakukan produksi</dt><dd class="col-sm-7">{{ $ya($pengajuan->melakukan_produksi) }}</dd>
            <dt class="col-sm-5">Setuju data pribadi</dt><dd class="col-sm-7">{{ $ya($pengajuan->persetujuan_data_pribadi) }}</dd>
            <dt class="col-sm-5">Tanggal pengajuan</dt><dd class="col-sm-7">{{ $pengajuan->tanggal_pengajuan?->format('d/m/Y H:i') }}</dd>
          </dl>
        </div>
      </div>
    </div>

    {{-- JAWABAN --}}
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h2 class="h5 mb-3">Jawaban kuesioner</h2>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead class="table-light">
                <tr><th>Pertanyaan</th><th>Jawaban</th><th class="text-end">Skor</th></tr>
              </thead>
              <tbody>
                @foreach($penilaian?->detail ?? [] as $d)
                  <tr>
                    <td>{{ $d->indikator?->nama }}</td>
                    <td>{{ $d->teks_pilihan }}</td>
                    <td class="text-end">{{ $d->skor }}</td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr class="fw-bold">
                  <td colspan="2">Total skor</td>
                  <td class="text-end">{{ $penilaian?->total_skor }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
          <p class="mb-0">
            Level rekomendasi sistem: <strong>{{ $rekomendasi?->nama_level }}</strong>
            <span class="text-muted small d-block">Rekomendasi saja. Level ditetapkan oleh Admin.</span>
          </p>
        </div>
      </div>
    </div>
  </div>

  {{-- KEPUTUSAN --}}
  @if($menunggu)
    <div class="row g-3 mt-1">
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 mb-3">Setujui</h2>
            <form method="POST" action="{{ route('admin.pengajuan.setujui', $pengajuan->id) }}" class="form-keputusan">
              @csrf
              <div class="mb-3">
                <label class="form-label" for="level_akhir_id">Level akhir</label>
                <select name="level_akhir_id" id="level_akhir_id" class="form-select" required>
                  @foreach($levels as $l)
                    <option value="{{ $l->id }}" @selected((int) old('level_akhir_id', $rekomendasi?->id) === $l->id)>
                      {{ $l->nama_level }}{{ $l->id === $rekomendasi?->id ? ' (rekomendasi)' : '' }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="alasan">Alasan <span class="text-muted small">(wajib jika level berbeda dari rekomendasi)</span></label>
                <textarea name="alasan" id="alasan" rows="3" class="form-control" maxlength="1000">{{ old('alasan') }}</textarea>
                @error('alasan')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <button type="submit" class="btn btn-success">Setujui dan buat akun</button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h2 class="h5 mb-3">Tolak</h2>
            <form method="POST" action="{{ route('admin.pengajuan.tolak', $pengajuan->id) }}" class="form-keputusan"
                  onsubmit="return confirm('Tolak pengajuan ini?');">
              @csrf
              <div class="mb-3">
                <label class="form-label" for="alasan_penolakan">Alasan penolakan <span class="text-muted small">(wajib)</span></label>
                <textarea name="alasan_penolakan" id="alasan_penolakan" rows="3" class="form-control" maxlength="1000" required>{{ old('alasan_penolakan') }}</textarea>
                @error('alasan_penolakan')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
              <button type="submit" class="btn btn-outline-danger">Tolak pengajuan</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @else
    <div class="card border-0 shadow-sm mt-3">
      <div class="card-body">
        <h2 class="h5 mb-2">Keputusan</h2>
        <p class="mb-1">Diputuskan pada {{ $pengajuan->tanggal_verifikasi?->format('d/m/Y H:i') }}.</p>
        @if($pengajuan->status === 'ditolak')
          <p class="mb-0">Alasan penolakan: {{ $pengajuan->alasan_penolakan }}</p>
        @endif
      </div>
    </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
  // Cegah klik ganda pada tombol keputusan.
  document.querySelectorAll('.form-keputusan').forEach(function (f) {
    f.addEventListener('submit', function () {
      f.querySelectorAll('button[type="submit"]').forEach(function (b) { b.disabled = true; });
    });
  });
</script>
@endpush
