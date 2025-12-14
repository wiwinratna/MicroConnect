@extends('layouts.umkm')
@section('title','COA')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Chart of Accounts (COA)</h3>
  <a href="{{ route('umkm.coa.create') }}" class="btn btn-primary">+ Tambah Akun</a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
  <div class="card-body">
    <table class="table table-bordered align-middle">
      <thead>
        <tr>
          <th>Header</th>
          <th>Kode</th>
          <th>Nama Akun</th>
          <th>Posisi</th>
          <th width="140">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($data as $r)
          <tr>
            <td>{{ $r->header_akun }}</td>
            <td>{{ $r->kode_akun }}</td>
            <td>{{ $r->nama_akun }}</td>
            <td>{{ $r->posisi_dr_cr }}</td>
            <td class="d-flex gap-1">
              <a class="btn btn-sm btn-outline-primary" href="{{ route('umkm.coa.edit',$r->id) }}">Edit</a>
              <form action="{{ route('umkm.coa.destroy',$r->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted">Belum ada COA.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
