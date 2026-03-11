@extends('layouts.umkm')
@section('title','Tambah COA')

@section('content')
<h3 class="mb-3">Tambah Akun COA</h3>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<div class="card shadow-sm border-0">
  <div class="card-body">
    <form method="POST" action="{{ route('umkm.coa.store') }}">
      @csrf

      <div class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Header Akun</label>
          <select name="header_akun" id="header_akun" class="form-select" required>
            @foreach(['Aset','Modal','Pendapatan','Beban'] as $h)
              <option value="{{ $h }}" @selected(old('header_akun')==$h)>{{ $h }}</option>
            @endforeach
          </select>
          <small class="text-muted">Pilih kelompok akun, sistem akan isi kode & posisi otomatis.</small>
        </div>

        <div class="col-md-3">
          <label class="form-label">Kode Akun (Auto)</label>
          <input
            name="kode_akun"
            id="kode_akun"
            class="form-control"
            value="{{ old('kode_akun') }}"
            readonly
            placeholder="Otomatis">
          <small class="text-muted" id="kode_hint">-</small>
        </div>

        <div class="col-md-4">
          <label class="form-label">Nama Akun</label>
          <input
            name="nama_akun"
            id="nama_akun"
            class="form-control"
            value="{{ old('nama_akun') }}"
            required
            placeholder="contoh: Kas">
        </div>

        <div class="col-md-2">
          <label class="form-label">Posisi (Auto)</label>
          <input
            name="posisi_dr_cr"
            id="posisi_dr_cr"
            class="form-control"
            value="{{ old('posisi_dr_cr') }}"
            readonly
            placeholder="Otomatis">
        </div>
      </div>

      <div class="mt-3">
        <div class="alert alert-info py-2 mb-0">
          <div class="fw-semibold">Info</div>
          <div class="small" id="info_text">
            Pilih header akun untuk mendapatkan kode akun berikutnya dan posisi normal (Debit/Kredit).
          </div>
        </div>
      </div>

      <div class="mt-3 d-flex justify-content-end gap-2">
        <a href="{{ route('umkm.coa.index') }}" class="btn btn-light">Batal</a>
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const header = document.getElementById('header_akun');
  const kode   = document.getElementById('kode_akun');
  const posisi = document.getElementById('posisi_dr_cr');

  const infoText = document.getElementById('info_text');
  const kodeHint = document.getElementById('kode_hint');

  async function preview() {
    const h = header.value;

    infoText.textContent = 'Mengambil kode akun berikutnya...';
    kodeHint.textContent = '';

    try {
      const res = await fetch(`{{ route('umkm.coa.preview') }}?header=${encodeURIComponent(h)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const data = await res.json();

      if (!res.ok || data.error) {
        kode.value = '';
        posisi.value = '';
        infoText.textContent = data.error || 'Gagal mengambil preview.';
        return;
      }

      kode.value   = data.kode_akun || '';
      posisi.value = data.posisi || '';

      infoText.textContent = `Header: ${h}. Kode berikutnya: ${data.kode_akun}. Posisi normal: ${data.posisi}.`;
      kodeHint.textContent = data.note ? data.note : '';
    } catch (e) {
      kode.value = '';
      posisi.value = '';
      infoText.textContent = 'Gagal konek ke server (preview COA).';
    }
  }

  header.addEventListener('change', preview);

  // auto load pertama kali
  preview();
});
</script>
@endpush
