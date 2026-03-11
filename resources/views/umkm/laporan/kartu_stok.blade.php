@extends('layouts.umkm')

@section('title', 'Kartu Stok')

@section('content')
@php
    use Carbon\Carbon;
    $methodStr = strtoupper($umkm->inventory_method ?? 'AVERAGE');
    $namaBulan = Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><strong>Kartu Stok</strong> Bahan Baku</h1>
        <p class="text-muted mb-0">Lacak riwayat masuk, keluar, dan nilai persediaan dengan metode <strong>{{ $methodStr }}</strong>.</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="GET" action="{{ route('umkm.laporan.kartu_stok') }}" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small text-muted mb-1">Pilih Bahan Baku</label>
                        <select name="bahan_id" class="form-select select2" required>
                            <option value="">-- Cari / Pilih Bahan Baku --</option>
                            @foreach($bahanList as $b)
                                <option value="{{ $b->id }}" {{ $bahanId == $b->id ? 'selected' : '' }}>
                                    {{ $b->nama_bahan }} ({{ $b->satuan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Bulan</label>
                        <input type="month" name="bulan" class="form-control" value="{{ $bulan }}" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Tampilkan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if($selectedBahan)
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">{{ $selectedBahan->nama_bahan }}</h5>
            <small class="text-muted">Satuan: {{ $selectedBahan->satuan }} | Periode: {{ $namaBulan }}</small>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6 mb-1">Metode Aktif: {{ $methodStr }}</span>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.9rem;">
            <thead class="table-light align-middle text-center">
                <tr>
                    <th rowspan="2" style="width: 100px;">Tanggal</th>
                    <th rowspan="2">Referensi Mutasi</th>
                    <th colspan="2" class="text-success w-25">MASUK (IN)</th>
                    <th colspan="2" class="text-danger w-25">KELUAR (OUT)</th>
                    <th colspan="2" class="text-primary w-25">SALDO (BALANCE)</th>
                </tr>
                <tr>
                    <th class="text-success">Qty</th>
                    <th class="text-success">Harga Unit</th>
                    <th class="text-danger">Qty</th>
                    <th class="text-danger">Harga Terpakai</th>
                    <th class="text-primary">Qty</th>
                    <th class="text-primary">Nilai Total</th>
                </tr>
            </thead>
            
            <tbody>
                {{-- BARIS SALDO AWAL --}}
                <tr class="table-info font-monospace text-muted text-end">
                    <td colspan="6" class="text-center fw-bold text-dark">SALDO AWAL (Per 1 {{ $namaBulan }})</td>
                    <td class="fw-bold text-dark">{{ number_format($saldoAwalQty, 3, ',', '.') }}</td>
                    <td class="fw-bold text-dark">Rp {{ number_format($saldoAwalNilai, 0, ',', '.') }}</td>
                </tr>

                {{-- LOOPING LEDGER BULAN INI --}}
                @forelse($ledger as $row)
                    @php
                        $isMasuk = $row['jenis'] === 'MASUK';
                        $tgl = Carbon::parse($row['tanggal'])->format('d/m/Y');
                        $refText = strtoupper($row['ref_tipe']) . ' #' . $row['ref_id'];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $tgl }}</td>
                        <td>
                            <span class="badge {{ $isMasuk ? 'bg-success' : 'bg-danger' }} bg-opacity-10 text-{{ $isMasuk ? 'success' : 'danger' }} mb-1">
                                {{ $isMasuk ? 'IN' : 'OUT' }}
                            </span>
                            <br>
                            <small class="text-muted font-monospace">{{ $refText }}</small>
                        </td>
                        
                        {{-- KOLOM MASUK --}}
                        <td class="text-end text-success fw-semibold">
                            {{ $isMasuk ? number_format($row['masuk_qty'], 3, ',', '.') : '' }}
                        </td>
                        <td class="text-end text-success">
                            {{ $isMasuk ? 'Rp '.number_format($row['masuk_harga'], 0, ',', '.') : '' }}
                        </td>

                        {{-- KOLOM KELUAR --}}
                        <td class="text-end text-danger fw-semibold">
                            @if(!$isMasuk)
                                {{ number_format($row['keluar_qty'], 3, ',', '.') }}
                            @endif
                        </td>
                        <td class="text-end text-danger lh-sm align-top">
                            @if(!$isMasuk)
                                @if(count($row['keluar_detail']) === 0)
                                    -
                                @elseif(count($row['keluar_detail']) === 1)
                                    @php $det = $row['keluar_detail'][0]; @endphp
                                    <small>Rp {{ number_format($det['harga'], 0, ',', '.') }}</small>
                                    @if(isset($det['is_avg']) && $det['is_avg'])
                                        <br><span style="font-size:0.7rem; color:#aaa;">(Avg)</span>
                                    @elseif(isset($det['batch']))
                                        <br><span style="font-size:0.75rem;" class="text-muted" title="Tgl Batch: {{ \Carbon\Carbon::parse($det['tanggal'])->format('d/m/Y') }}">sumber: {{ $det['batch'] }}</span>
                                    @endif
                                @else
                                    <ul class="list-unstyled mb-0 text-start" style="font-size: 0.8rem;">
                                    @foreach($row['keluar_detail'] as $det)
                                        <li class="border-bottom border-light pb-1 mb-1">
                                            <span class="text-nowrap">{{ $det['qty'] }}&times; <span class="fw-semibold">Rp {{ number_format($det['harga'], 0, ',', '.') }}</span></span>
                                            @if(isset($det['batch']))
                                                <br><span class="text-muted" style="font-size: 0.7rem;" title="Tgl Batch: {{ \Carbon\Carbon::parse($det['tanggal'])->format('d/m/Y') }}">&rdsh; dr: {{ $det['batch'] }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                    </ul>
                                @endif
                            @endif
                        </td>

                        {{-- KOLOM SALDO --}}
                        <td class="text-end text-primary fw-bold font-monospace bg-light">
                            {{ number_format($row['saldo_qty'], 3, ',', '.') }}
                        </td>
                        <td class="text-end text-primary fw-bold font-monospace bg-light">
                            Rp {{ number_format($row['saldo_nilai'], 0, ',', '.') }}
                            @if($methodStr === 'AVERAGE' && $row['saldo_qty'] > 0)
                                <br><span class="fw-normal text-muted" style="font-size: 0.75rem;">(@ Rp {{ number_format($row['avg_price'], 0, ',', '.') }})</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            Tidak ada pergerakan / transaksi untuk bahan baku ini di bulan <strong>{{ $namaBulan }}</strong>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- SECTION ACTIVE BATCHES --}}
@if($methodStr !== 'AVERAGE' && count($activeBatches) > 0)
<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 border-top border-3 border-success">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Ringkasan Sisa Batch Aktif ({{ $methodStr }})</h6>
                <p class="text-muted small mb-0">Rincian batch stok yang saat ini masih tersedia dan akan diprioritaskan untuk pemakaian selanjutnya.</p>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($activeBatches as $idx => $batch)
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $idx === 0 ? 'bg-success bg-opacity-10' : '' }}">
                        <div>
                            <span class="fw-semibold">Batch #{{ $batch['id'] }}</span>
                            <br>
                            <small class="text-muted">Sumber: {{ strtoupper($batch['ref_tipe']) }} #{{ $batch['ref_id'] }} | Tgl: {{ \Carbon\Carbon::parse($batch['tanggal'])->format('d/m/Y') }}</small>
                            @if($idx === 0 && $methodStr === 'FIFO')
                                <span class="badge bg-success ms-1">Next</span>
                            @endif
                            @if($idx === count($activeBatches)-1 && $methodStr === 'LIFO')
                                <span class="badge bg-danger ms-1">Next</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <span class="fs-6 fw-bold text-dark">{{ number_format($batch['qty'], 3, ',', '.') }}</span> <small class="text-muted">{{ $selectedBahan->satuan }}</small>
                            <br>
                            <small class="text-muted">@ Rp {{ number_format($batch['harga'], 0, ',', '.') }}</small>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer bg-light text-end">
                @php 
                    $totalQty = array_sum(array_column($activeBatches, 'qty'));
                    $totalNilai = array_sum(array_map(fn($b) => $b['qty'] * $b['harga'], $activeBatches));
                @endphp
                <small class="text-muted me-3">Total Qty: <strong>{{ number_format($totalQty, 3, ',', '.') }}</strong></small>
                <small class="text-muted">Total Nilai: <strong class="text-primary">Rp {{ number_format($totalNilai, 0, ',', '.') }}</strong></small>
            </div>
        </div>
    </div>
</div>
@endif

@endif

@endsection

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '-- Cari / Pilih Bahan Baku --'
        });
    });
</script>
<style>
/* Adjust select2 theme so it looks native */
.select2-container--bootstrap-5 .select2-selection {
    min-height: calc(1.5em + .75rem + 2px);
    border: 1px solid #ced4da;
}
</style>
@endpush
