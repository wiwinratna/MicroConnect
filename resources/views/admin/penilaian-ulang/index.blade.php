@extends('layouts.admin')
@section('title', 'Tinjau Penilaian Ulang')

@section('content')
<div class="container-fluid p-0">

  <div class="mb-4">
    <h1 class="h3 mb-1">Tinjau <strong>Penilaian Ulang</strong></h1>
    <p class="text-muted small mb-0">Sistem hanya merekomendasikan. Level baru berlaku setelah Anda menetapkannya.</p>
  </div>

  <ul class="nav nav-pills mb-3">
    <li class="nav-item">
      <a class="nav-link {{ $status === 'menunggu' ? 'active' : '' }}" href="{{ route('admin.penilaian-ulang.index', ['status' => 'menunggu']) }}">Menunggu</a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $status === 'selesai' ? 'active' : '' }}" href="{{ route('admin.penilaian-ulang.index', ['status' => 'selesai']) }}">Selesai</a>
    </li>
  </ul>

  <div class="card border-0 shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Tanggal</th>
            <th>UMKM</th>
            <th>Periode</th>
            <th class="text-center">Skor</th>
            <th>Rekomendasi</th>
            @if($status === 'selesai') <th>Level akhir</th> @endif
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($penilaian as $n)
            <tr>
              <td class="text-nowrap">{{ $n->tanggal_penilaian?->format('d/m/Y') }}</td>
              <td>{{ $n->umkm?->nama_usaha ?? '-' }}</td>
              <td>{{ $n->periode?->nama }}</td>
              <td class="text-center">{{ $n->total_skor }}</td>
              <td>
                {{ $n->levelRekomendasi?->nama_level }}
                <span class="badge bg-{{ ['naik' => 'success', 'turun' => 'danger'][$n->arah_rekomendasi] ?? 'secondary' }} ms-1">{{ ucfirst($n->arah_rekomendasi) }}</span>
              </td>
              @if($status === 'selesai') <td>{{ $n->levelAkhir?->nama_level }}</td> @endif
              <td class="text-end">
                <a href="{{ route('admin.penilaian-ulang.show', $n->id) }}" class="btn btn-sm btn-primary">
                  {{ $status === 'menunggu' ? 'Tinjau' : 'Lihat' }}
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="{{ $status === 'selesai' ? 7 : 6 }}" class="text-center text-muted py-4">Tidak ada penilaian ulang pada daftar ini.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $penilaian->links() }}</div>

</div>
@endsection
