@extends('layouts.admin')
@section('title', 'Daftar Periode Iuran')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><strong>Periode</strong> Iuran</h1>
            <p class="text-muted small mb-0">Kelola distribusi dan pemantauan iuran bulanan UMKM.</p>
        </div>
        <a href="{{ route('admin.iuran-periode.create') }}" class="btn btn-primary px-3 rounded-pill shadow-sm">
            <i data-feather="plus-circle" class="me-1" style="width: 16px;"></i> Buat Periode Baru
        </a>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-borderless">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Periode Tagihan</th>
                        <th>Nominal Default</th>
                        <th>Batas Waktu</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Peserta (UMKM)</th>
                        <th class="text-center">Realisasi Lunas</th>
                        <th class="text-center">Tunggakan</th>
                        <th class="pe-4 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodes as $periode)
                        <tr class="border-bottom-faint">
                            <td class="ps-4">
                                <div class="fw-bold text-dark">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $periode->periode)->translatedFormat('F Y') }}
                                </div>
                                <div class="text-muted small x-small text-uppercase">Tax Period</div>
                            </td>
                            <td class="fw-bold">{{ rupiah($periode->nominal_default) }}</td>
                            <td>
                                <div class="text-dark small"><i data-feather="calendar" class="me-1 text-muted" style="width: 12px;"></i>{{ $periode->jatuh_tempo->format('d M Y') }}</div>
                            </td>
                            <td class="text-center">
                                @if($periode->status === 'terbit')
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Terbit</span>
                                @elseif($periode->status === 'selesai')
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">Selesai</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3">Draft</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-dark rounded-pill">{{ $periode->total_umkm }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success text-white rounded-pill">{{ $periode->total_lunas }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger text-white rounded-pill">{{ $periode->total_belum_bayar }}</span>
                            </td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('admin.iuran-periode.show', $periode->id) }}" class="btn btn-sm btn-action btn-action-view">
                                    <i data-feather="eye"></i> Monitoring
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i data-feather="list" class="mb-2" style="width: 32px; height: 32px; opacity: 0.2;"></i><br>
                                Belum ada periode iuran yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($periodes->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-center">
                    {{ $periodes->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    .x-small { font-size: 0.7rem; }
    .bg-primary-subtle { background-color: rgba(59, 125, 221, 0.1) !important; }
    .bg-success-subtle { background-color: rgba(28, 187, 140, 0.1) !important; }
    .bg-warning-subtle { background-color: rgba(252, 185, 44, 0.1) !important; }
    .bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1) !important; }
    .text-primary { color: #3b7ddd !important; }
    .text-success { color: #1cbb8c !important; }
    .text-warning { color: #f59e0b !important; }
    .border-bottom-faint { border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important; }
</style>
@endsection
