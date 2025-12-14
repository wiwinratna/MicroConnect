@extends('layouts.admin')

@section('content')
<div class="container-fluid p-0">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h3 mb-1">Daftar UMKM</h1>
      <p class="text-muted mb-0">Kelola data UMKM binaan.</p>
    </div>
    <a href="{{ route('admin.umkm.create') }}" class="btn btn-primary">Add New UMKM</a>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover my-0">
        <thead>
          <tr>
            <th>Kode UMKM</th>
            <th>Nama Usaha</th>
            <th>NIB</th>
            <th>No. Telepon</th>
            <th>Alamat</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($umkm as $u)
            <tr>
              <td>{{ $u->kode_umkm }}</td>
              <td>{{ $u->nama_usaha }}</td>
              <td>{{ $u->nib ?? '-' }}</td>
              <td>{{ $u->no_telepon ?? '-' }}</td>
              <td class="text-truncate" style="max-width: 320px;">{{ $u->alamat ?? '-' }}</td>
              <td class="text-end">
                <a class="btn btn-link" href="{{ route('admin.umkm.show', $u->id) }}">View</a>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-muted">Belum ada data UMKM.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-body">
      {{ $umkm->links() }}
    </div>
  </div>

</div>
@endsection
