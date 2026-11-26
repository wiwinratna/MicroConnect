@extends('layouts.umkm')
@section('title','Edit COA')

@section('content')
<h1 class="h3 mb-3">Edit Akun COA</h1>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ route('umkm.coa.update', $coa->id) }}">
      @csrf
      @method('PUT')

      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label" for="header_akun">Header Akun</label>
          <select name="header_akun" id="header_akun" class="form-select" required>
            @foreach([1=>'Aset', 2=>'Kewajiban', 3=>'Modal', 4=>'Pendapatan', 5=>'Beban'] as $v => $l)
              <option value="{{ $v }}" @selected((string) old('header_akun', $coa->header_akun) === (string) $v)>{{ $l }}</option>
            @endforeach
          </select>
          <div class="form-text">Posisi normal mengikuti header akun.</div>
        </div>

        <div class="col-md-3">
          <label class="form-label" for="kode_akun">Kode Akun</label>
          <input id="kode_akun" class="form-control" value="{{ $coa->kode_akun }}" readonly>
          <div class="form-text">Kode akun tidak dapat diubah.</div>
        </div>

        <div class="col-md-4">
          <label class="form-label" for="nama_akun">Nama Akun</label>
          <input name="nama_akun" id="nama_akun" class="form-control" maxlength="100" required
                 value="{{ old('nama_akun', $coa->nama_akun) }}">
        </div>

        <div class="col-md-2">
          <label class="form-label" for="posisi">Posisi (Auto)</label>
          <input id="posisi" class="form-control" value="{{ $coa->posisi_dr_cr }}" readonly>
        </div>
      </div>

      <div class="mt-3 d-flex justify-content-end gap-2">
        <a href="{{ route('umkm.coa.index') }}" class="btn btn-light">Batal</a>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
// Sama dengan aturan di server: Aset dan Beban bersaldo normal Debit.
document.getElementById('header_akun').addEventListener('change', function () {
  document.getElementById('posisi').value = ['1', '5'].includes(this.value) ? 'Debit' : 'Kredit';
});
</script>
@endpush
