@extends('layouts.admin')
@section('title', 'Tinjau Penilaian Ulang')

@php
  $menunggu = $penilaian->status_peninjauan === 'menunggu';
  $levelSaatIni = $penilaian->umkm?->level;
  $sebelumnyaPerIndikator = $sebelumnya ? $sebelumnya->detail->keyBy('indikator_id') : collect();
@endphp

@section('content')
<div class="container-fluid p-0">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1 class="h3 mb-1">Tinjau <strong>Penilaian Ulang</strong></h1>
      <p class="text-muted small mb-0">
        {{ $penilaian->umkm?->nama_usaha }} &middot; {{ $penilaian->periode?->nama }} &middot; {{ $penilaian->tanggal_penilaian?->format('d/m/Y') }}
        <span class="badge bg-{{ $menunggu ? 'secondary' : 'success' }} ms-1">{{ $menunggu ? 'Menunggu' : 'Selesai' }}</span>
      </p>
    </div>
    <a href="{{ route('admin.penilaian-ulang.index') }}" class="btn btn-outline-secondary">Kembali ke daftar</a>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h2 class="h5 mb-3">Jawaban</h2>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead class="table-light">
                <tr><th>Pertanyaan</th><th>Jawaban sekarang</th><th>Sebelumnya</th><th class="text-end">Skor</th></tr>
              </thead>
              <tbody>
                @foreach($penilaian->detail as $d)
                  <tr>
                    <td>{{ $d->indikator?->nama }}</td>
                    <td>
                      {{ $d->teks_pilihan }}
                      @if(in_array($d->pilihan_id, $luarBatas, true))
                        <span class="badge bg-warning text-dark ms-1">Di luar batas mikro</span>
                      @endif
                    </td>
                    <td class="text-muted small">{{ $sebelumnyaPerIndikator->get($d->indikator_id)?->teks_pilihan ?? '-' }}</td>
                    <td class="text-end">{{ $d->skor }}</td>
                  </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr class="fw-bold">
                  <td colspan="3">Total skor</td>
                  <td class="text-end">{{ $penilaian->total_skor }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <h2 class="h5 mb-3">Rekomendasi sistem</h2>
          <p class="mb-1">Level saat ini: <strong>{{ $levelSaatIni?->nama_level ?? '-' }}</strong></p>
          <p class="mb-1">Level rekomendasi: <strong>{{ $penilaian->levelRekomendasi?->nama_level }}</strong></p>
          <p class="mb-3">
            Arah:
            <span class="badge bg-{{ ['naik' => 'success', 'turun' => 'danger'][$penilaian->arah_rekomendasi] ?? 'secondary' }}">{{ ucfirst($penilaian->arah_rekomendasi) }}</span>
          </p>

          @if(count($perbandingan))
            <h3 class="h6 mt-3">Angka dari pembukuan sistem</h3>
            <p class="text-muted small mb-2">
              Data tersedia {{ $penilaian->data_sistem['hari_data'] ?? 0 }} hari saat dikirim.
            </p>
            @foreach($perbandingan as $p)
              <div class="border rounded p-2 mb-2 small">
                <div class="fw-semibold">{{ $p['label'] }}: {{ $p['nilai'] === null ? 'tidak ada data' : rupiah($p['nilai']) }}</div>
                <div>Saran sistem: {{ $p['disarankan'] ?? '-' }}</div>
                <div>Dipilih UMKM: {{ $p['dipilih'] ?? '-' }}</div>
                @if($p['diubah'])
                  <span class="badge bg-warning text-dark">Diubah UMKM dari saran sistem</span>
                @elseif($p['disarankan'])
                  <span class="badge bg-success">Sesuai saran sistem</span>
                @endif
              </div>
            @endforeach
          @endif
        </div>
      </div>
    </div>
  </div>

  @if($menunggu)
    <div class="card border-0 shadow-sm mt-3">
      <div class="card-body">
        <h2 class="h5 mb-3">Tetapkan level</h2>

        @if($errors->any())
          <div class="alert alert-danger small">
            <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
          </div>
        @endif

        <form method="POST" action="{{ route('admin.penilaian-ulang.tetapkan', $penilaian->id) }}" id="formTetapkan">
          @csrf
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" for="level_akhir_id">Level akhir</label>
              <select name="level_akhir_id" id="level_akhir_id" class="form-select" required>
                @foreach($levels as $l)
                  <option value="{{ $l->id }}" @selected((int) old('level_akhir_id', $penilaian->level_rekomendasi_id) === $l->id)>
                    {{ $l->nama_level }}{{ $l->id === $penilaian->level_rekomendasi_id ? ' (rekomendasi)' : '' }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label" for="alasan">Alasan <span class="text-muted small">(wajib jika level berbeda dari rekomendasi; terlihat oleh UMKM)</span></label>
              <textarea name="alasan" id="alasan" rows="2" class="form-control" maxlength="1000">{{ old('alasan') }}</textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="catatan_peninjauan">Catatan peninjauan</label>
              <textarea name="catatan_peninjauan" id="catatan_peninjauan" rows="2" class="form-control" maxlength="1000">{{ old('catatan_peninjauan') }}</textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="tindak_lanjut_pembinaan">Tindak lanjut pembinaan</label>
              <textarea name="tindak_lanjut_pembinaan" id="tindak_lanjut_pembinaan" rows="2" class="form-control" maxlength="1000">{{ old('tindak_lanjut_pembinaan') }}</textarea>
            </div>
          </div>
          <div class="mt-3">
            <button type="submit" class="btn btn-success">Simpan dan tetapkan level</button>
          </div>
        </form>
      </div>
    </div>
  @else
    <div class="card border-0 shadow-sm mt-3">
      <div class="card-body">
        <h2 class="h5 mb-2">Keputusan</h2>
        <p class="mb-1">Level akhir: <strong>{{ $penilaian->levelAkhir?->nama_level }}</strong></p>
        @if($penilaian->alasan_penetapan)<p class="mb-1">Alasan: {{ $penilaian->alasan_penetapan }}</p>@endif
        @if($penilaian->catatan_peninjauan)<p class="mb-1">Catatan: {{ $penilaian->catatan_peninjauan }}</p>@endif
        @if($penilaian->tindak_lanjut_pembinaan)<p class="mb-1">Tindak lanjut pembinaan: {{ $penilaian->tindak_lanjut_pembinaan }}</p>@endif
        <p class="text-muted small mb-0">Ditinjau {{ $penilaian->tanggal_peninjauan?->format('d/m/Y H:i') }}</p>
      </div>
    </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
  // Cegah klik ganda pada tombol simpan.
  var form = document.getElementById('formTetapkan');
  if (form) {
    form.addEventListener('submit', function () {
      form.querySelector('button[type="submit"]').disabled = true;
    });
  }
</script>
@endpush
