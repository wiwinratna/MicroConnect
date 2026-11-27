@extends('layouts.admin')
@section('title', 'Aturan Modul')

@section('content')
<div class="container-fluid p-0">

  <div class="mb-4">
    <h1 class="h3 mb-1">Aturan <strong>Modul</strong></h1>
    <p class="text-muted small mb-0">
      Menentukan modul yang tampil untuk UMKM menurut jenis usaha. Perubahan langsung memengaruhi menu dan akses UMKM.
      UMKM lama yang jenis usaha atau datanya belum dilengkapi tidak disembunyikan fitur apa pun.
    </p>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.aturan-modul.update') }}">
    @csrf
    @method('PUT')

    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:18%">Modul</th>
              <th>Jenis barang</th>
              <th>Jenis jasa</th>
            </tr>
          </thead>
          <tbody>
            @foreach($modul as $m)
              <tr>
                <td class="fw-semibold">{{ $m->nama }}<div class="text-muted small"><code>{{ $m->kode }}</code></div></td>
                @foreach(['barang', 'jasa'] as $rumpun)
                  @php $a = ($aturan[$m->id] ?? collect())->firstWhere('rumpun', $rumpun); @endphp
                  <td>
                    <select name="aturan[{{ $m->id }}][{{ $rumpun }}][kondisi]" class="form-select form-select-sm mb-1">
                      @foreach($kondisi as $nilai => $nama)
                        <option value="{{ $nilai }}" @selected(old("aturan.{$m->id}.{$rumpun}.kondisi", $a?->kondisi) === $nilai)>{{ $nama }}</option>
                      @endforeach
                    </select>
                    <input type="text" name="aturan[{{ $m->id }}][{{ $rumpun }}][label_tampil]" maxlength="150"
                           class="form-control form-control-sm" placeholder="Label khusus"
                           value="{{ old("aturan.{$m->id}.{$rumpun}.label_tampil", $a?->label_tampil) }}">
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-3">
      <button type="submit" class="btn btn-primary">Simpan aturan</button>
    </div>
  </form>

</div>
@endsection
