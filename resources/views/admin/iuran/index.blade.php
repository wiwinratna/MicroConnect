@extends('layouts.admin')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Daftar Iuran UMKM</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white pb-0 pt-3">
            <form action="{{ route('admin.iuran.index') }}" method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Filter Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="belum_bayar" {{ request('status') == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Periode</th>
                        <th>UMKM</th>
                        <th>Nominal</th>
                        <th>Jatuh Tempo</th>
                        <th>Dibayar Pada</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($iuranList as $iuran)
                        <tr>
                            <td class="fw-semibold">{{ \Carbon\Carbon::parse($iuran->periode)->translatedFormat('F Y') }}</td>
                            <td>
                                @if($iuran->umkm)
                                    <a href="{{ route('admin.umkm.show', $iuran->umkm_id) }}" class="text-dark text-decoration-none fw-medium">
                                        {{ $iuran->umkm->nama_usaha ?? 'Tanpa Nama' }}
                                    </a>
                                @else
                                    <span class="text-muted">UMKM Dihapus</span>
                                @endif
                            </td>
                            <td>{{ rupiah($iuran->nominal) }}</td>
                            <td>{{ $iuran->jatuh_tempo ? $iuran->jatuh_tempo->format('d/m/Y') : '-' }}</td>
                            <td>{{ $iuran->dibayar_pada ? $iuran->dibayar_pada->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                @if($iuran->status === 'lunas')
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-warning text-dark">Belum Bayar</span>
                                @endif
                            </td>
                            <td>
                                @if($iuran->status === 'belum_bayar' && $iuran->umkm)
                                   <form action="{{ route('admin.umkm.konfirmasiIuran', $iuran->umkm_id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="iuran_id" value="{{ $iuran->id }}">
                                        <button class="btn btn-sm btn-success" onclick="return confirm('Konfirmasi pelunasan iuran ini?')">
                                            Konfirmasi Lunas
                                        </button>
                                   </form>
                                @else
                                   <span class="text-muted small">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada catatan iuran.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($iuranList->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $iuranList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
