@extends('layouts.admin')
@section('title', $indikator->exists ? 'Ubah Pertanyaan' : 'Tambah Pertanyaan')

@php
    $sistem = $indikator->exists && \App\Services\IndikatorService::namaSistem($indikator->nama);
    $pilihan = old('pilihan', $indikator->exists
        ? $indikator->pilihan->map(fn ($p) => $p->only(['id', 'teks', 'contoh', 'skor', 'di_luar_batas_mikro'])
            + ['nilai_min' => $p->nilai_min === null ? null : (float) $p->nilai_min,
               'nilai_max' => $p->nilai_max === null ? null : (float) $p->nilai_max])->all()
        : [['skor' => 1], ['skor' => 2]]);
@endphp

@section('content')
<div class="container-fluid p-0">
  <h1 class="h3 mb-3">{{ $indikator->exists ? 'Ubah Pertanyaan' : 'Tambah Pertanyaan' }}</h1>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ $indikator->exists ? route('admin.indikator.update', $indikator->id) : route('admin.indikator.store') }}">
    @csrf
    @if($indikator->exists) @method('PUT') @endif

    <div class="card">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-9">
            <label class="form-label" for="nama">Nama indikator</label>
            <input type="text" id="nama" name="nama" class="form-control" maxlength="150" required
                   value="{{ old('nama', $indikator->nama) }}" placeholder="Contoh: Pemasaran" @readonly($sistem)>
            @if($sistem)<div class="form-text">Nama ini dipakai sistem untuk mengisi jawaban dari pembukuan, jadi tidak bisa diubah.</div>@endif
          </div>
          <div class="col-md-3">
            <label class="form-label" for="urutan">Urutan tampil</label>
            <input type="number" id="urutan" name="urutan" class="form-control" min="1" max="255" required
                   value="{{ old('urutan', $indikator->urutan) }}">
          </div>
          <div class="col-12">
            <label class="form-label" for="pertanyaan">Pertanyaan yang dibaca UMKM</label>
            <textarea id="pertanyaan" name="pertanyaan" rows="2" class="form-control" maxlength="1000" required>{{ old('pertanyaan', $indikator->pertanyaan) }}</textarea>
          </div>
          <div class="col-12">
            <label class="form-label" for="definisi">Keterangan untuk Admin</label>
            <textarea id="definisi" name="definisi" rows="2" class="form-control" maxlength="1000">{{ old('definisi', $indikator->definisi) }}</textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h2 class="card-title">Pilihan jawaban dan skor</h2>
        <button type="button" class="btn btn-sm btn-outline-primary" id="tambahPilihan">Tambah pilihan</button>
      </div>
      <div class="card-body" id="daftarPilihan" data-berikutnya="{{ count($pilihan) }}">
        @foreach(array_values($pilihan) as $i => $p)
          @include('admin.indikator.pilihan', ['i' => $i, 'p' => $p, 'sistem' => $sistem])
        @endforeach
      </div>
    </div>

    <template id="templatPilihan">
      @include('admin.indikator.pilihan', ['i' => '__i__', 'p' => ['skor' => 1], 'sistem' => $sistem])
    </template>

    <div class="d-flex justify-content-end gap-2">
      <a href="{{ route('admin.indikator.index') }}" class="btn btn-light">Batal</a>
      <button type="submit" class="btn btn-primary">Simpan pertanyaan</button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/indikator-form.js') }}"></script>
@endpush
