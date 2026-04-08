@extends('layouts.admin')
@section('title', 'Detail Periode Iuran')

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                Iuran <strong>{{ $periode->periodeFormatted() }}</strong>
            </h1>
            <p class="text-muted mb-0">
                Nominal: {{ rupiah($periode->nominal_default) }} &bull;
                Jatuh Tempo: {{ $periode->jatuh_tempo->format('d/m/Y') }}
                @if($periode->keterangan)
                    &bull; {{ $periode->keterangan }}
                @endif
            </p>
        </div>
        <a href="{{ route('admin.iuran-periode.index') }}" class="btn btn-outline-secondary">← Kembali</a>
    </div>

    

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Total UMKM</h5>
                    <h2 class="mb-0 text-primary">{{ $totalUmkm }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Sudah Lunas</h5>
                    <h2 class="mb-0 text-success">{{ $totalLunas }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Belum Bayar</h5>
                    <h2 class="mb-0 text-warning">{{ $totalBelumBayar }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="text-muted mb-1">Total Pendapatan</h5>
                    <h2 class="mb-0 text-success" style="font-size: 1.3rem;">{{ rupiah($totalPendapatan) }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel UMKM --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold">Daftar Tagihan UMKM</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>UMKM</th>
                        <th>Nominal</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Dibayar Pada</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($iuranList as $iuran)
                        <tr>
                            <td>
                                @if($iuran->umkm)
                                    <a href="{{ route('admin.umkm.show', $iuran->umkm_id) }}" class="text-dark text-decoration-none fw-medium">
                                        {{ $iuran->umkm->nama_usaha ?? 'Tanpa Nama' }}
                                    </a>
                                    <br><small class="text-muted">{{ $iuran->umkm->kode_umkm }}</small>
                                @else
                                    <span class="text-muted">UMKM Dihapus</span>
                                @endif
                            </td>
                            <td>{{ rupiah($iuran->nominal) }}</td>
                            <td>{{ $iuran->jatuh_tempo ? $iuran->jatuh_tempo->format('d/m/Y') : '-' }}</td>
                            <td>
                                <span class="badge {{ $iuran->statusBadgeClass() }}">
                                    {{ $iuran->statusLabel() }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $iuran->dibayar_pada ? $iuran->dibayar_pada->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td>
                                @if($iuran->isBayarable() && $iuran->umkm)
                                    <form action="{{ route('admin.iuran-periode.konfirmasi', [$periode->id, $iuran->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success" onclick="return confirm('Konfirmasi pelunasan iuran UMKM ini?')">
                                            Konfirmasi Lunas
                                        </button>
                                    </form>
                                @elseif($iuran->isLunas())
                                    <span class="text-muted small">✅ Selesai</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada tagihan pada periode ini.
                            </td>
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
