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
        <div class="text-end d-flex flex-column align-items-end gap-2">
            <div class="d-flex gap-2 mt-1">
                <a href="{{ route('umkm.export.kartu_stok', ['bahan_id' => $selectedBahan->id, 'bulan' => $bulan, 'format' => 'excel']) }}" class="btn btn-sm btn-success"><i data-feather="file-text" class="align-middle" style="width:14px;"></i> Excel</a>
                <a href="{{ route('umkm.export.kartu_stok', ['bahan_id' => $selectedBahan->id, 'bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-danger"><i data-feather="file" class="align-middle" style="width:14px;"></i> PDF</a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        {{--
            Format kolom mendekati format kartu stok excel:
            Tanggal | Keterangan | MASUK(Qty, Harga, Nilai) | KELUAR(Qty, Harga, Nilai) | SALDO(Qty, Harga/Avg, Nilai)
            Untuk FIFO/LIFO multi-layer keluar: setiap layer = satu baris terpisah
        --}}
        <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-secondary align-middle text-center">
                <tr>
                    <th rowspan="2" class="align-middle" style="min-width:90px;">Tanggal</th>
                    <th rowspan="2" class="align-middle" style="min-width:140px;">Keterangan</th>
                    <th colspan="3" class="text-success">MASUK</th>
                    <th colspan="3" class="text-danger">KELUAR</th>
                    <th colspan="3" class="text-primary">SALDO</th>
                </tr>
                <tr>
                    <th class="text-success">Qty</th>
                    <th class="text-success">Harga (Rp)</th>
                    <th class="text-success">Nilai (Rp)</th>
                    <th class="text-danger">Qty</th>
                    <th class="text-danger">Harga (Rp)</th>
                    <th class="text-danger">Nilai (Rp)</th>
                    <th class="text-primary">Qty</th>
                    <th class="text-primary">Harga/Avg (Rp)</th>
                    <th class="text-primary">Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody>
                {{-- BARIS SALDO AWAL --}}
                <tr class="table-info fw-bold text-center">
                    <td class="text-start" colspan="2">SALDO AWAL — Per 1 {{ $namaBulan }}</td>
                    <td colspan="3" class="text-muted small">—</td>
                    <td colspan="3" class="text-muted small">—</td>
                    <td>{{ number_format($saldoAwalQty, 3, ',', '.') }}</td>
                    <td>
                        @if($saldoAwalQty > 0)
                            {{ rupiah($saldoAwalNilai / $saldoAwalQty) }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ rupiah($saldoAwalNilai) }}</td>
                </tr>

                {{-- LOOPING LEDGER BULAN INI --}}
                @forelse($ledger as $row)
                    @php
                        $isMasuk = $row['jenis'] === 'MASUK';
                        $tgl = Carbon::parse($row['tanggal'])->format('d/m/Y');
                        $refTipe = strtoupper($row['ref_tipe']);
                        $refId   = $row['ref_id'];

                        // Label keterangan yang informatif
                        $ketLabel = match(strtolower($row['ref_tipe'])) {
                            'saldo_awal'  => 'Saldo Awal',
                            'pembelian'   => 'Pembelian',
                            'penjualan'   => 'Penjualan',
                            'produksi'    => 'Produksi',
                            default       => $refTipe,
                        };

                        // Untuk MASUK: nilai = qty × harga
                        $masukNilai = $isMasuk ? ($row['masuk_qty'] * $row['masuk_harga']) : 0;

                        // Untuk KELUAR: hitung dari detail breakdown (FIFO/LIFO) atau langsung (Average)
                        $keluarDetails = $row['keluar_detail'] ?? [];
                        $keluarQtyTotal = $isMasuk ? 0 : $row['keluar_qty'];

                        // Harga avg saldo
                        $saldoHarga = $row['saldo_qty'] > 0 ? ($row['saldo_nilai'] / $row['saldo_qty']) : 0;

                        // Jumlah baris yang dibutuhkan untuk transaksi KELUAR multi-layer
                        $numRows = (!$isMasuk && count($keluarDetails) > 1) ? count($keluarDetails) : 1;
                    @endphp

                    @if($isMasuk)
                    {{-- BARIS MASUK: satu baris saja --}}
                    <tr>
                        <td class="text-center text-nowrap">{{ $tgl }}</td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success">IN</span>
                            <span class="ms-1">{{ $ketLabel }}</span>
                            <br><small class="text-muted font-monospace">{{ $refTipe }} #{{ $refId }}</small>
                        </td>
                        {{-- MASUK --}}
                        <td class="text-end text-success fw-semibold">{{ number_format($row['masuk_qty'], 3, ',', '.') }}</td>
                        <td class="text-end text-success">{{ rupiah($row['masuk_harga']) }}</td>
                        <td class="text-end text-success fw-semibold">{{ rupiah($masukNilai) }}</td>
                        {{-- KELUAR (kosong) --}}
                        <td class="text-center text-muted">—</td>
                        <td class="text-center text-muted">—</td>
                        <td class="text-center text-muted">—</td>
                        {{-- SALDO --}}
                        <td class="text-end text-primary fw-bold bg-light">{{ number_format($row['saldo_qty'], 3, ',', '.') }}</td>
                        <td class="text-end text-primary bg-light">
                            {{ rupiah($saldoHarga) }}
                            @if($methodStr === 'AVERAGE')
                                <br><span class="text-muted" style="font-size:0.75rem;">(Avg)</span>
                            @endif
                        </td>
                        <td class="text-end text-primary fw-bold bg-light">{{ rupiah($row['saldo_nilai']) }}</td>
                    </tr>

                    @elseif(count($keluarDetails) <= 1)
                    {{-- BARIS KELUAR: satu layer / Average --}}
                    @php
                        $det = $keluarDetails[0] ?? null;
                        $keluarHarga = $det ? $det['harga'] : 0;
                        $keluarNilai = $keluarQtyTotal * $keluarHarga;
                    @endphp
                    <tr>
                        <td class="text-center text-nowrap">{{ $tgl }}</td>
                        <td>
                            <span class="badge bg-danger bg-opacity-10 text-danger">OUT</span>
                            <span class="ms-1">{{ $ketLabel }}</span>
                            <br><small class="text-muted font-monospace">{{ $refTipe }} #{{ $refId }}</small>
                            @if($det && isset($det['is_avg']) && $det['is_avg'])
                                <br><small class="text-muted" style="font-size:0.75rem;">(Weighted Average)</small>
                            @endif
                        </td>
                        {{-- MASUK (kosong) --}}
                        <td class="text-center text-muted">—</td>
                        <td class="text-center text-muted">—</td>
                        <td class="text-center text-muted">—</td>
                        {{-- KELUAR --}}
                        <td class="text-end text-danger fw-semibold">{{ number_format($keluarQtyTotal, 3, ',', '.') }}</td>
                        <td class="text-end text-danger">{{ $det ? rupiah($keluarHarga) : '—' }}</td>
                        <td class="text-end text-danger fw-semibold">{{ $det ? rupiah($keluarNilai) : '—' }}</td>
                        {{-- SALDO --}}
                        <td class="text-end text-primary fw-bold bg-light">{{ number_format($row['saldo_qty'], 3, ',', '.') }}</td>
                        <td class="text-end text-primary bg-light">
                            {{ rupiah($saldoHarga) }}
                            @if($methodStr === 'AVERAGE')
                                <br><span class="text-muted" style="font-size:0.75rem;">(Avg)</span>
                            @endif
                        </td>
                        <td class="text-end text-primary fw-bold bg-light">{{ rupiah($row['saldo_nilai']) }}</td>
                    </tr>

                    @else
                    {{-- BARIS KELUAR MULTI-LAYER (FIFO/LIFO): setiap layer = satu baris --}}
                    {{-- Baris pertama: tampilkan tanggal, keterangan, dan saldo --}}
                    @foreach($keluarDetails as $li => $det)
                        @php
                            $detQty   = $det['qty'];
                            $detHarga = $det['harga'];
                            $detNilai = $detQty * $detHarga;
                            $isFirst  = ($li === 0);
                            $isLast   = ($li === count($keluarDetails) - 1);
                        @endphp
                        <tr class="{{ $isFirst ? '' : 'bg-light bg-opacity-50' }}">
                            @if($isFirst)
                            <td class="text-center text-nowrap" rowspan="{{ count($keluarDetails) }}">{{ $tgl }}</td>
                            <td rowspan="{{ count($keluarDetails) }}">
                                <span class="badge bg-danger bg-opacity-10 text-danger">OUT</span>
                                <span class="ms-1">{{ $ketLabel }}</span>
                                <br><small class="text-muted font-monospace">{{ $refTipe }} #{{ $refId }}</small>
                                <br><small class="text-muted" style="font-size:0.72rem;">{{ count($keluarDetails) }} layer {{ $methodStr }}</small>
                            </td>
                            @endif
                            {{-- MASUK (kosong) --}}
                            <td class="text-center text-muted">—</td>
                            <td class="text-center text-muted">—</td>
                            <td class="text-center text-muted">—</td>
                            {{-- KELUAR: detail tiap layer --}}
                            <td class="text-end text-danger fw-semibold">{{ number_format($detQty, 3, ',', '.') }}</td>
                            <td class="text-end text-danger">
                                {{ rupiah($detHarga) }}
                                @if(isset($det['batch']))
                                    <br><small class="text-muted" style="font-size:0.72rem;" title="Tgl: {{ isset($det['tanggal']) ? \Carbon\Carbon::parse($det['tanggal'])->format('d/m/Y') : '' }}">
                                        Batch: {{ $det['batch'] }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-end text-danger fw-semibold">{{ rupiah($detNilai) }}</td>
                            {{-- SALDO: hanya tampil di baris terakhir layer --}}
                            @if($isLast)
                            <td class="text-end text-primary fw-bold bg-light" rowspan="1">{{ number_format($row['saldo_qty'], 3, ',', '.') }}</td>
                            <td class="text-end text-primary bg-light" rowspan="1">{{ rupiah($saldoHarga) }}</td>
                            <td class="text-end text-primary fw-bold bg-light" rowspan="1">{{ rupiah($row['saldo_nilai']) }}</td>
                            @endif
                        </tr>
                    @endforeach
                    @endif

                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-5">
                            Tidak ada pergerakan / transaksi untuk bahan baku ini di bulan <strong>{{ $namaBulan }}</strong>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- SECTION ACTIVE BATCHES (hanya untuk FIFO/LIFO) --}}
@if($methodStr !== 'AVERAGE' && count($activeBatches) > 0)
<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 border-top border-3 border-success">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">Sisa Batch Aktif ({{ $methodStr }})</h6>
                <p class="text-muted small mb-0">Batch stok yang masih tersedia dan akan dipakai berikutnya.</p>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($activeBatches as $idx => $batch)
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $idx === 0 && $methodStr === 'FIFO' ? 'bg-success bg-opacity-10' : ($idx === count($activeBatches)-1 && $methodStr === 'LIFO' ? 'bg-warning bg-opacity-10' : '') }}">
                        <div>
                            <span class="fw-semibold">Batch #{{ $batch['id'] }}</span>
                            @if($idx === 0 && $methodStr === 'FIFO')
                                <span class="badge bg-success ms-1">Next (FIFO)</span>
                            @endif
                            @if($idx === count($activeBatches)-1 && $methodStr === 'LIFO')
                                <span class="badge bg-warning ms-1">Next (LIFO)</span>
                            @endif
                            <br>
                            <small class="text-muted">Sumber: {{ strtoupper($batch['ref_tipe']) }} #{{ $batch['ref_id'] }} | Tgl: {{ \Carbon\Carbon::parse($batch['tanggal'])->format('d/m/Y') }}</small>
                        </div>
                        <div class="text-end">
                            <span class="fs-6 fw-bold text-dark">{{ number_format($batch['qty'], 3, ',', '.') }}</span>
                            <small class="text-muted"> {{ $selectedBahan->satuan }}</small>
                            <br>
                            <small class="text-muted">@ {{ rupiah($batch['harga']) }}</small>
                            <br>
                            <small class="fw-semibold text-primary">Nilai: {{ rupiah($batch['qty'] * $batch['harga']) }}</small>
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
                <small class="text-muted">Total Nilai: <strong class="text-primary">{{ rupiah($totalNilai) }}</strong></small>
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
.select2-container--bootstrap-5 .select2-selection {
    min-height: calc(1.5em + .75rem + 2px);
    border: 1px solid #ced4da;
}
/* Pastikan tabel kartu stok tetap terbaca pada layar kecil */
.table th, .table td { white-space: nowrap; }
.table td small { white-space: normal; }
</style>
@endpush
