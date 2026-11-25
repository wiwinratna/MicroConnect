@extends('layouts.admin')
@section('title', 'Laporan per Level')

@php
  use App\Helpers\KatalogLaporan;
@endphp

@section('content')
<div class="container-fluid p-0">

  <div class="mb-4">
    <h1 class="h3 mb-1">Laporan per <strong>Level</strong></h1>
    <p class="text-muted small mb-0">
      Centang laporan yang tersedia untuk tiap level. Perubahan langsung memengaruhi halaman laporan dan ekspor UMKM.
    </p>
  </div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.laporan-level.update') }}">
    @csrf
    @method('PUT')

    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Laporan</th>
              @foreach($levels as $level)
                <th class="text-center">{{ $level->nama_level }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @foreach($laporan as $kode => [$nama, $kunci])
              <tr>
                <td>{{ $nama }}</td>
                @foreach($levels as $level)
                  <td class="text-center">
                    <input type="checkbox" class="form-check-input"
                           name="matriks[{{ $level->id }}][]" value="{{ $kode }}"
                           aria-label="{{ $nama }} untuk {{ $level->nama_level }}"
                           @checked(KatalogLaporan::aktifDi($kode, (array) ($level->fitur ?? [])))>
                  </td>
                @endforeach
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-3">
      <button type="submit" class="btn btn-primary">Simpan pengaturan</button>
    </div>
  </form>

</div>
@endsection
