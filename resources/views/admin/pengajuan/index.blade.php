@extends('layouts.admin')
@section('title', 'Pengajuan Pendaftaran')

@section('content')
<div class="container-fluid p-0">

  <div class="mb-4">
    <h1 class="h3 mb-1">Pengajuan <strong>Pendaftaran</strong></h1>
    <p class="text-muted small mb-0">Calon UMKM yang menunggu verifikasi. Akun dibuat setelah Anda menyetujui.</p>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Tanggal</th>
            <th>Pemilik</th>
            <th>Usaha</th>
            <th>Kategori usaha</th>
            <th>Jenis usaha</th>
            <th>Rekomendasi</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pengajuan as $p)
            <tr>
              <td class="text-nowrap">{{ $p->tanggal_pengajuan?->format('d/m/Y H:i') }}</td>
              <td>{{ $p->nama_pemilik }}<div class="text-muted small">{{ $p->email }}</div></td>
              <td>{{ $p->nama_usaha }}</td>
              <td>{{ $p->jenisUsaha?->nama }}</td>
              <td class="text-capitalize">{{ $p->rumpun }}</td>
              <td>
                {{ $p->penilaian?->levelRekomendasi?->nama_level }}
                @if($p->perlu_keputusan_admin)
                  <span class="badge bg-warning text-dark ms-1">Perlu keputusan Admin</span>
                @endif
              </td>
              <td class="text-end">
                <a href="{{ route('admin.pengajuan.show', $p->id) }}" class="btn btn-sm btn-primary">Tinjau</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="text-center text-muted py-4">Tidak ada pengajuan yang menunggu.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
