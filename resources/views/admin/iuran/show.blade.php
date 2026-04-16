@extends('layouts.admin')
@section('title', 'Detail Monitoring Iuran')

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.iuran-periode.index') }}" class="btn bg-white shadow-sm border rounded-circle p-2 me-3" title="Kembali">
                <i data-feather="chevron-left" class="text-dark"></i>
            </a>
            <div>
                <h1 class="h3 mb-1">Monitoring Iuran <strong>{{ $periode->periodeFormatted() }}</strong></h1>
                <p class="text-muted small mb-0">
                    <i data-feather="dollar-sign" class="me-1" style="width: 12px;"></i>{{ rupiah($periode->nominal_default) }} &bull;
                    <i data-feather="calendar" class="me-1" style="width: 12px;"></i>Tempo: {{ $periode->jatuh_tempo->format('d M Y') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="p-2 bg-primary-subtle text-primary rounded-3 me-3">
                        <i data-feather="users" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <p class="text-muted x-small text-uppercase fw-bold mb-1">Peserta UMKM</p>
                        <h3 class="fw-bold mb-0 text-dark">{{ $totalUmkm }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="p-2 bg-success-subtle text-success rounded-3 me-3">
                        <i data-feather="check-circle" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <p class="text-muted x-small text-uppercase fw-bold mb-1">Terbayar</p>
                        <h3 class="fw-bold mb-0 text-success">{{ $totalLunas }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="p-2 bg-danger-subtle text-danger rounded-3 me-3">
                        <i data-feather="alert-circle" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <p class="text-muted x-small text-uppercase fw-bold mb-1">Tunggakan</p>
                        <h3 class="fw-bold mb-0 text-danger">{{ $totalBelumBayar }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="p-2 bg-warning-subtle text-warning rounded-3 me-3">
                        <i data-feather="trending-up" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <p class="text-muted x-small text-uppercase fw-bold mb-1">Dana Terkumpul</p>
                        <h4 class="fw-bold mb-0 text-dark">{{ rupiah($totalPendapatan) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel UMKM --}}
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">Daftar Tagihan Berjalan</h5>
            <div class="badge bg-light text-dark fw-normal border">Unit: KADIN Bengkalis</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-borderless">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Identitas UMKM</th>
                        <th>Nominal Tagihan</th>
                        <th>Batas Waktu</th>
                        <th class="text-center">Status</th>
                        <th>Verifikasi Pelunasan</th>
                        <th class="pe-4 text-end">Aksi Konfirmasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($iuranList as $iuran)
                        <tr class="border-bottom-faint">
                            <td class="ps-4">
                                @if($iuran->umkm)
                                    <a href="{{ route('admin.umkm.show', $iuran->umkm_id) }}" class="text-dark text-decoration-none fw-bold">
                                        {{ $iuran->umkm->nama_usaha ?? 'Tanpa Nama' }}
                                    </a>
                                    <div class="text-muted x-small">ID: {{ $iuran->umkm->kode_umkm }}</div>
                                @else
                                    <span class="text-danger fw-bold"><i data-feather="trash-2" class="me-1" style="width: 12px;"></i>UMKM Telah Dihapus</span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">{{ rupiah($iuran->nominal) }}</td>
                            <td>
                                <span class="text-dark small">{{ $iuran->jatuh_tempo ? $iuran->jatuh_tempo->format('d M Y') : '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $iuran->status === 'lunas' ? 'success' : 'danger' }}-subtle text-{{ $iuran->status === 'lunas' ? 'success' : 'danger' }} rounded-pill px-3">
                                    {{ $iuran->statusLabel() }}
                                </span>
                            </td>
                            <td>
                                @if($iuran->dibayar_pada)
                                    <div class="text-dark fw-medium small"><i data-feather="check" class="text-success me-1" style="width: 14px;"></i> {{ $iuran->dibayar_pada->format('d M Y') }}</div>
                                    <div class="text-muted x-small ms-3 ps-1">{{ $iuran->dibayar_pada->format('H:i') }} WIB</div>
                                @else
                                    <span class="text-muted x-small italic">— Belum diverifikasi</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                @if($iuran->isBayarable() && $iuran->umkm)
                                    <form action="{{ route('admin.iuran-periode.konfirmasi', [$periode->id, $iuran->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success rounded-pill px-3 shadow-none border-0" onclick="return confirm('Konfirmasi pelunasan iuran UMKM ini?')">
                                            Konfirmasi Lunas
                                        </button>
                                    </form>
                                @elseif($iuran->isLunas())
                                    <div class="p-1 bg-success-subtle text-success d-inline-block rounded-pill px-2 x-small fw-bold">
                                        <i data-feather="shield-check" class="me-1" style="width: 12px;"></i> TERVERIFIKASI
                                    </div>
                                @else
                                    <span class="text-muted x-small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i data-feather="file-text" class="mb-2" style="width: 32px; height: 32px; opacity: 0.2;"></i><br>
                                Belum ada tagihan yang dibangkitkan pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($iuranList->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-center">
                    {{ $iuranList->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .x-small { font-size: 0.7rem; }
    .bg-primary-subtle { background-color: rgba(59, 125, 221, 0.1) !important; }
    .bg-success-subtle { background-color: rgba(28, 187, 140, 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }
    .bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1) !important; }
    .bg-warning-subtle { background-color: rgba(252, 185, 44, 0.1) !important; }
    .text-primary { color: #3b7ddd !important; }
    .text-success { color: #1cbb8c !important; }
    .text-danger { color: #dc3545 !important; }
    .text-secondary { color: #6c757d !important; }
    .text-warning { color: #f59e0b !important; }
    .border-bottom-faint { border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important; }
    .italic { font-style: italic; }
</style>
@endsection
