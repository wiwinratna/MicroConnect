@extends('layouts.admin')
@section('title', 'Indikator Penilaian')

@section('content')
<div class="container-fluid p-0">

  <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
    <div>
      <h1 class="h3 mb-1">Indikator Penilaian</h1>
      <p class="text-muted small mb-0">
        Pertanyaan kuesioner untuk menentukan rekomendasi level. Perubahan hanya berlaku untuk kuesioner
        yang dikirim setelahnya; penilaian lama tidak berubah.
      </p>
    </div>
    <a href="{{ route('admin.indikator.create') }}" class="btn btn-primary">Tambah pertanyaan</a>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- BATAS SKOR LEVEL --}}
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Batas skor level</h2>
    </div>
    <div class="card-body">
      <p class="small mb-3">
        Dengan pertanyaan yang aktif sekarang, total skor berkisar <strong>{{ $rentang[0] }} sampai {{ $rentang[1] }}</strong>.
        Batas level harus bersambung dan mencakup seluruh rentang itu.
      </p>
      <form method="POST" action="{{ route('admin.indikator.batas') }}">
        @csrf @method('PUT')
        <div class="row g-3">
          @foreach($levels as $l)
            <fieldset class="col-md-4">
              <legend class="form-label">{{ $l->nama_level }}</legend>
              <div class="d-flex align-items-center gap-2">
                <input type="number" name="batas[{{ $l->id }}][min]" class="form-control" min="0" max="255" required
                       value="{{ old("batas.{$l->id}.min", $l->skor_min) }}" aria-label="Skor terendah {{ $l->nama_level }}">
                <span class="text-muted small">sampai</span>
                <input type="number" name="batas[{{ $l->id }}][max]" class="form-control" min="0" max="255" required
                       value="{{ old("batas.{$l->id}.max", $l->skor_max) }}" aria-label="Skor tertinggi {{ $l->nama_level }}">
              </div>
            </fieldset>
          @endforeach
        </div>
        <div class="mt-3 text-end">
          <button type="submit" class="btn btn-primary">Simpan batas skor</button>
        </div>
      </form>
    </div>
  </div>

  {{-- DAFTAR PERTANYAAN: pilihan jawaban dibuka saat diklik --}}
  @forelse($indikator as $ind)
    <div class="card">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
          <div style="flex: 1 1 300px;">
            <div class="mb-1">
              <span class="badge {{ $ind->aktif ? 'bg-success-subtle' : 'bg-secondary-subtle' }}">{{ $ind->aktif ? 'Aktif' : 'Nonaktif' }}</span>
              <span class="text-muted small ms-1">Urutan {{ $ind->urutan }} · {{ $ind->nama }}</span>
            </div>
            <div class="fw-bold">{{ $ind->pertanyaan }}</div>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.indikator.edit', $ind->id) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
            <form method="POST" action="{{ route('admin.indikator.status', $ind->id) }}">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $ind->aktif ? 'Nonaktifkan' : 'Aktifkan' }}</button>
            </form>
            @if(!$dijawab->has($ind->id) && !\App\Services\IndikatorService::namaSistem($ind->nama))
              <form method="POST" action="{{ route('admin.indikator.destroy', $ind->id) }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger btn-delete">Hapus</button>
              </form>
            @endif
          </div>
        </div>

        <details class="mt-2">
          <summary class="small fw-semibold" style="cursor: pointer;">Lihat {{ $ind->pilihan->count() }} pilihan jawaban dan skor</summary>
          <div class="table-responsive mt-2">
            <table class="table table-sm mb-0">
              <thead><tr><th>Pilihan jawaban</th><th style="width: 90px;">Skor</th></tr></thead>
              <tbody>
                @foreach($ind->pilihan as $p)
                  <tr>
                    <td>
                      {{ $p->teks }}
                      @if($p->contoh)<div class="text-muted small">{{ $p->contoh }}</div>@endif
                      @if($p->di_luar_batas_mikro)<span class="badge bg-warning-subtle">Di luar batas usaha mikro</span>@endif
                    </td>
                    <td class="fw-bold">{{ $p->skor }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </details>
      </div>
    </div>
  @empty
    <div class="card"><div class="card-body text-center text-muted">Belum ada pertanyaan.</div></div>
  @endforelse

</div>
@endsection
