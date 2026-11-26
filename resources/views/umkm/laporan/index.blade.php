@extends(auth()->user()->user_group === 'admin' ? 'layouts.admin' : 'layouts.umkm')

@section('title', 'Laporan Keuangan')

@section('content')
@php
    use Carbon\Carbon;
    $namaBulan = Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
@endphp

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><strong>Laporan</strong> Keuangan</h1>
        <p class="text-muted mb-0">Ringkasan pencatatan & laporan keuangan UMKM periode {{ $namaBulan }}.</p>
    </div>
    
    <form method="GET" action="{{ request()->url() }}" class="d-flex gap-2">
        <input type="month" name="bulan" class="form-control" value="{{ $bulan }}">
        <button type="submit" class="btn btn-primary">Terapkan</button>
    </form>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        {{-- ===================== TAB NAV ===================== --}}
        <ul class="nav nav-tabs border-bottom-0 gap-1 mb-3" id="laporanTab" role="tablist">
            {{-- Level 1+ : Jurnal Umum --}}
            @if(feature_can('jurnal_umum'))
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-jurnal">Jurnal Umum</button></li>
            @endif

            {{-- Level 1+ : Laporan Pembelian & Penjualan (via Lainnya dropdown) --}}

            {{-- Level 2+ : Buku Besar --}}
            @if(feature_can('buku_besar'))
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bukubesar">Buku Besar</button></li>
            @endif

            {{-- Level 2+ : Laba Rugi --}}
            @if(feature_can('laba_rugi'))
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-labarugi">Laba Rugi</button></li>
            @endif

            {{-- Level 3 : Perubahan Modal --}}
            @if(feature_can('perubahan_modal'))
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-modal">Perubahan Modal</button></li>
            @endif

            {{-- Level 3 : Arus Kas --}}
            @if(feature_can('arus_kas'))
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-kas">Arus Kas</button></li>
            @endif

            {{-- Level 2+ : Kartu Stok & Rekap Mutasi --}}
            @if(feature_can('kartu_stok'))
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Persediaan Barang</a>
                <ul class="dropdown-menu shadow-sm border-0">
                    <li><button class="dropdown-item" data-bs-toggle="tab" data-bs-target="#tab-stok">Rekap Mutasi Stok</button></li>
                    <li><a class="dropdown-item text-primary fw-semibold" href="{{ route('umkm.laporan.kartu_stok') }}">Lihat Kartu Stok (FIFO/LIFO/Avg)</a></li>
                </ul>
            </li>
            @endif

            {{-- Level 1+ : Laporan Penjualan, Pembelian — Level 2+ : tambah Piutang --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Lainnya</a>
                <ul class="dropdown-menu shadow-sm border-0">
                    @if(feature_can('laporan_pembelian'))
                    <li><button class="dropdown-item" data-bs-toggle="tab" data-bs-target="#tab-beli">Laporan Pembelian</button></li>
                    @endif
                    @if(feature_can('laporan_penjualan'))
                    <li><button class="dropdown-item" data-bs-toggle="tab" data-bs-target="#tab-jual">Laporan Penjualan</button></li>
                    @endif
                    @if(feature_can('piutang'))
                    <li><button class="dropdown-item" data-bs-toggle="tab" data-bs-target="#tab-piutang">Laporan Piutang</button></li>
                    @endif
                </ul>
            </li>
        </ul>

        {{-- ===================== TAB CONTENT ===================== --}}
        <div class="tab-content" id="laporanTabContent">

            {{-- 1. JURNAL UMUM --}}
            @include('umkm.laporan.partials.jurnal')

            {{-- 2. BUKU BESAR --}}
            @include('umkm.laporan.partials.buku-besar')

            {{-- 3. LABA RUGI --}}
            @include('umkm.laporan.partials.laba-rugi')

            {{-- 4. PERUBAHAN MODAL --}}
            @include('umkm.laporan.partials.perubahan-modal')

            {{-- 5. ARUS KAS --}}
            @include('umkm.laporan.partials.arus-kas')

            {{-- 6. STOK PERSEDIAAN --}}
            @include('umkm.laporan.partials.stok')

            {{-- 7. PEMBELIAN --}}
            @include('umkm.laporan.partials.pembelian')

            {{-- 8. PENJUALAN --}}
            @include('umkm.laporan.partials.penjualan')

            {{-- 9. PIUTANG --}}
            @include('umkm.laporan.partials.piutang')

        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
.nav-tabs .nav-link { border-radius: 8px 8px 0 0; color: #495057; font-weight: 500; }
.nav-tabs .nav-link.active { font-weight: 700; color: #0d6efd; border-bottom-color: transparent; }
.table-sm td, .table-sm th { padding: 0.5rem; }

@if(auth()->user()->user_group === 'admin')
/* Hide all export functionality and external UMKM routes that aren't mapped for Admin */
.btn-action-pdf, .btn-action-excel, a[href*="/export/"], a[href*="kartu_stok"] {
    display: none !important;
}
@endif
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // restore last active tab from hash
    let hash = window.location.hash;
    if (hash) {
        let triggerEl = document.querySelector(`.nav-tabs button[data-bs-target="${hash}"]`);
        if (triggerEl) new bootstrap.Tab(triggerEl).show();
    }
    
    // update hash on tab change
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', e => {
            history.replaceState(null, null, e.target.getAttribute('data-bs-target'));
        });
    });
});
</script>
@endpush
