@extends('layouts.umkm')
@section('title', 'Beban Operasional')

@push('styles')
<style>
    /* ── Premium Table ── */
    .table-premium th { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; border-bottom: 1px solid #e2e8f0 !important; padding: 1rem 1.25rem; background: #fafbfc; }
    .table-premium td { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .table-premium tr:last-child td { border-bottom: none; }
    .table-premium tr:hover td { background: #f8fafc; }

    /* ── Summary Card ── */
    .summary-card { border-radius: 16px; transition: transform 0.2s; background: #fff; }
    .summary-card:hover { transform: translateY(-3px); }

    /* ── Icon Circle ── */
    .icon-circle { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
</style>
@endpush

@section('content')
{{-- Header Area - CLEAN --}}
<div class="mb-4">
    <h1 class="h3 mb-1"><strong>Beban</strong> Operasional</h1>
    <p class="text-muted mb-0" style="font-size: 0.85rem;">Monitor dan kelola pengeluaran rutin operasional usaha Anda.</p>
</div>

{{-- Summary Section --}}
<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm summary-card overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em; text-transform: uppercase;">Total Beban Bulan Ini</p>
                        <h3 class="fw-bold mb-0" style="color: #1e293b; letter-spacing: -0.02em;">{{ rupiah($totalBeban) }}</h3>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i data-feather="activity" class="text-warning" style="width: 28px; height: 28px;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 4px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 100%; border-radius: 2px;"></div>
                    </div>
                    <p class="text-muted mt-2 mb-0" style="font-size: 0.75rem;">Biaya operasional mencakup 6xx di Jurnal</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
    {{-- Card Header with Filter & Action --}}
    <div class="card-header bg-white border-bottom px-4 py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h5 class="fw-bold mb-0" style="font-size: 0.95rem;">Riwayat Pengeluaran</h5>
            
            <div class="d-flex gap-2">
                <form action="{{ route('umkm.beban.index') }}" method="GET" class="d-flex gap-2 align-items-center">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted" style="font-size: 0.75rem;"><i data-feather="calendar" style="width:12px; height:12px;"></i></span>
                        <input type="month" name="bulan" class="form-control form-control-sm border-start-0 ps-0 shadow-none" 
                               style="width:140px; border-radius: 0 8px 8px 0; font-size:0.8rem;" 
                               value="{{ $bulan }}" onchange="this.form.submit()">
                    </div>
                </form>
                <a href="{{ route('umkm.beban.create') }}" class="btn btn-primary btn-sm shadow-sm" style="border-radius:8px; padding: 0.4rem 0.8rem; font-size: 0.8125rem; font-weight: 600;">
                    <i data-feather="plus" style="width:13px; height:13px; margin-right:4px;"></i> Catat Beban
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @if($bebanList->isEmpty())
            <div class="text-center py-5">
                <div class="mb-3">
                    <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto;">
                        <i data-feather="file-text" style="width: 28px; height: 28px; color: #94a3b8;"></i>
                    </div>
                </div>
                <h5 class="text-dark fw-bold mb-1">Belum Ada Catatan</h5>
                <p class="text-muted mb-4 small">Tentukan bulan atau buat catatan beban baru untuk hari ini.</p>
                <a href="{{ route('umkm.beban.create') }}" class="btn btn-primary px-4 py-2" style="border-radius:8px; font-weight:600; font-size:0.85rem;">
                    Catat Beban Pertama
                </a>
            </div>
        @else
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-borderless table-premium">
                <thead>
                    <tr>
                        <th class="ps-4">Tanggal</th>
                        <th>Kategori Beban</th>
                        <th>Keterangan</th>
                        <th class="text-end pe-4">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bebanList as $b)
                        @php
                            $kategori = collect(\App\Http\Controllers\Umkm\BebanController::kategoriBeban())->firstWhere('kode', $b->kode_akun);
                            $icon = $kategori['icon'] ?? 'file';
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($b->tanggal)->format('d M') }}</div>
                                <div class="text-muted x-small" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($b->tanggal)->format('Y') }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle bg-light text-primary border shadow-sm" style="background: #fff !important;">
                                        <i data-feather="{{ $icon }}" style="width: 16px; height: 16px;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">{{ $b->nama_akun }}</div>
                                        <div class="text-muted x-small" style="font-size:0.7rem;">Kode {{ $b->kode_akun }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="small text-muted" style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $b->keterangan }}
                            </td>
                            <td class="text-end pe-4 fw-bold text-danger">
                                {{ rupiah($b->debit) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="background: #fafbfc;">
                    <tr>
                        <td colspan="3" class="ps-4 py-3 fw-bold text-dark">TOTAL PENGELUARAN</td>
                        <td class="text-end pe-4 py-3 fw-bold text-danger h5 mb-0">{{ rupiah($totalBeban) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="mt-4 p-3 bg-light rounded-3 d-flex align-items-center gap-2 border border-dashed">
    <i data-feather="info" class="text-muted" style="width:16px; height:16px;"></i>
    <p class="text-muted small mb-0">Semua riwayat beban otomatis d Posting ke Jurnal Umum sebagai pengurang kas.</p>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if(typeof feather !== 'undefined') feather.replace();
    })
</script>
@endpush
