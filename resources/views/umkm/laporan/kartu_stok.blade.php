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
                        {{-- Hidden id yang dikirim sebagai parameter --}}
                        <input type="hidden" name="bahan_id" id="ks_bahan_id" value="{{ $bahanId ?? '' }}">
                        <div class="position-relative">
                            <input type="text"
                                   id="ks_bahan_input"
                                   class="form-control"
                                   placeholder="Ketik nama atau kode bahan..."
                                   value="{{ $selectedBahan ? $selectedBahan->nama_bahan . ' (' . $selectedBahan->satuan . ')' : '' }}"
                                   autocomplete="off" required>
                            <div id="ks_bahan_panel"
                                 class="list-group shadow-sm"
                                 style="position:absolute;z-index:50;top:100%;left:0;right:0;max-height:240px;overflow:auto;display:none;">
                            </div>
                        </div>
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
                <a href="{{ route('umkm.export.kartu_stok', ['bahan_id' => $selectedBahan->id, 'bulan' => $bulan, 'format' => 'excel']) }}" class="btn btn-sm btn-action btn-action-excel" title="Unduh Excel"><i data-feather="file-text"></i> Excel</a>
                <a href="{{ route('umkm.export.kartu_stok', ['bahan_id' => $selectedBahan->id, 'bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        {{--
            Format kolom mendekati format kartu stok excel:
            Tanggal | Keterangan | MASUK(Qty, Harga, Nilai) | KELUAR(Qty, Harga, Nilai) | SALDO(Qty, Harga/Avg, Nilai)
            Untuk FIFO/LIFO multi-layer keluar: setiap layer = satu baris terpisah
        --}}
        <table class="table table-hover table-bordered border-light align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light text-secondary text-uppercase" style="letter-spacing: 0.5px;">
                <tr>
                    <th rowspan="2" class="align-middle text-start border-end" style="min-width:100px;">TANGGAL</th>
                    <th rowspan="2" class="align-middle text-start border-end" style="min-width:200px;">KETERANGAN</th>
                    <th colspan="3" class="text-center border-end">MASUK (IN)</th>
                    <th colspan="3" class="text-center border-end">KELUAR (OUT)</th>
                    <th colspan="3" class="text-center border-end">SISA SALDO</th>
                </tr>
                <tr>
                    {{-- MASUK --}}
                    <th class="text-end fw-semibold" style="width:70px;">QTY</th>
                    <th class="text-end fw-semibold">HARGA</th>
                    <th class="text-end fw-semibold border-end">NILAI</th>
                    {{-- KELUAR --}}
                    <th class="text-end fw-semibold" style="width:70px;">QTY</th>
                    <th class="text-end fw-semibold">HARGA</th>
                    <th class="text-end fw-semibold border-end">NILAI</th>
                    {{-- SALDO --}}
                    <th class="text-end fw-bold text-dark" style="width:70px;">QTY</th>
                    <th class="text-end fw-bold text-dark">HARGA</th>
                    <th class="text-end fw-bold text-dark">NILAI</th>
                </tr>
            </thead>
            <tbody>
                {{-- BARIS SALDO AWAL --}}
                <tr class="fw-bold bg-white">
                    <td class="text-start text-dark border-end" colspan="2">SALDO AWAL — Per 1 {{ $namaBulan }}</td>
                    <td colspan="3" class="text-muted text-center border-end">—</td>
                    <td colspan="3" class="text-muted text-center border-end">—</td>
                    <td class="text-end fw-bold text-dark">{{ format_angka($saldoAwalQty) }}</td>
                    <td class="text-end fw-bold text-dark">
                        @if($saldoAwalQty > 0)
                            {{ rupiah($saldoAwalNilai / $saldoAwalQty) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-end fw-bold text-dark">{{ rupiah($saldoAwalNilai) }}</td>
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
                        <td class="text-start text-muted border-end">{{ $tgl }}</td>
                        <td class="border-end text-start">
                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill me-1" style="font-size:10px; padding: 3px 8px;">IN</span>
                            <span class="fw-medium text-dark">{{ $ketLabel }}</span>
                            <br><small class="text-muted" style="font-size:0.75rem;">{{ $refTipe }} #{{ $refId }}</small>
                        </td>
                        {{-- MASUK --}}
                        <td class="text-end text-success fw-medium">{{ format_angka($row['masuk_qty']) }}</td>
                        <td class="text-end text-success">{{ format_angka($row['masuk_harga']) }}</td>
                        <td class="text-end text-success fw-semibold border-end">{{ format_angka($masukNilai) }}</td>
                        {{-- KELUAR (kosong) --}}
                        <td class="text-center text-muted" colspan="3" class="border-end">—</td>
                        {{-- SALDO --}}
                        <td class="text-end text-dark fw-bold bg-light fw-bold">{{ format_angka($row['saldo_qty']) }}</td>
                        <td class="text-end text-dark fw-bold bg-light">
                            {{ format_angka($saldoHarga) }}
                            @if($methodStr === 'AVERAGE')
                                <br><span class="opacity-50" style="font-size:0.70rem;">(Avg)</span>
                            @endif
                        </td>
                        <td class="text-end text-dark fw-bold bg-light fw-bold">{{ format_angka($row['saldo_nilai']) }}</td>
                    </tr>

                    @elseif(count($keluarDetails) <= 1)
                    {{-- BARIS KELUAR: satu layer / Average --}}
                    @php
                        $det = $keluarDetails[0] ?? null;
                        $keluarHarga = $det ? $det['harga'] : 0;
                        $keluarNilai = $keluarQtyTotal * $keluarHarga;
                    @endphp
                    <tr>
                        <td class="text-start text-muted border-end">{{ $tgl }}</td>
                        <td class="border-end text-start">
                            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill me-1" style="font-size:10px; padding: 3px 8px;">OUT</span>
                            <span class="fw-medium text-dark">{{ $ketLabel }}</span>
                            <br><small class="text-muted" style="font-size:0.75rem;">{{ $refTipe }} #{{ $refId }}</small>
                            @if($det && isset($det['is_avg']) && $det['is_avg'])
                                <br><small class="text-muted" style="font-size:0.70rem;">(Weighted Average)</small>
                            @endif
                        </td>
                        {{-- MASUK (kosong) --}}
                        <td class="text-center text-muted border-end" colspan="3">—</td>
                        {{-- KELUAR --}}
                        <td class="text-end text-danger fw-medium">{{ format_angka($keluarQtyTotal) }}</td>
                        <td class="text-end text-danger">{{ $det ? format_angka($keluarHarga) : '—' }}</td>
                        <td class="text-end text-danger fw-semibold border-end">{{ $det ? format_angka($keluarNilai) : '—' }}</td>
                        {{-- SALDO --}}
                        <td class="text-end text-dark fw-bold bg-light fw-bold">{{ format_angka($row['saldo_qty']) }}</td>
                        <td class="text-end text-dark fw-bold bg-light">
                            {{ format_angka($saldoHarga) }}
                            @if($methodStr === 'AVERAGE')
                                <br><span class="opacity-50" style="font-size:0.70rem;">(Avg)</span>
                            @endif
                        </td>
                        <td class="text-end text-dark fw-bold bg-light fw-bold">{{ format_angka($row['saldo_nilai']) }}</td>
                    </tr>

                    @else
                    {{-- BARIS KELUAR MULTI-LAYER (FIFO/LIFO): setiap layer = satu baris --}}
                    @foreach($keluarDetails as $li => $det)
                        @php
                            $detQty   = $det['qty'];
                            $detHarga = $det['harga'];
                            $detNilai = $detQty * $detHarga;
                            $isFirst  = ($li === 0);
                            $isLast   = ($li === count($keluarDetails) - 1);
                        @endphp
                        <tr>
                            @if($isFirst)
                            <td class="text-start text-muted border-end" rowspan="{{ count($keluarDetails) }}">{{ $tgl }}</td>
                            <td class="border-end text-start" rowspan="{{ count($keluarDetails) }}">
                                <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill me-1" style="font-size:10px; padding: 3px 8px;">OUT</span>
                                <span class="fw-medium text-dark">{{ $ketLabel }}</span>
                                <br><small class="text-muted" style="font-size:0.75rem;">{{ $refTipe }} #{{ $refId }}</small>
                                <br><span class="badge bg-light text-dark fw-normal mt-1 border border-secondary" style="font-size:0.70rem;">{{ count($keluarDetails) }} layer {{ $methodStr }}</span>
                            </td>
                            @endif
                            
                            {{-- MASUK (kosong) --}}
                            <td class="text-center text-muted border-end" colspan="3">—</td>
                            
                            {{-- KELUAR: detail tiap layer --}}
                            <td class="text-end text-danger fw-medium">{{ format_angka($detQty) }}</td>
                            <td class="text-end text-danger">
                                {{ format_angka($detHarga) }}
                                @if(isset($det['batch']))
                                    <br><small class="text-muted" style="font-size:0.70rem; opacity: 0.7;">
                                        Batch: {{ $det['batch'] }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-end text-danger fw-semibold border-end">{{ format_angka($detNilai) }}</td>
                            
                            {{-- SALDO: hanya tampil di baris terakhir layer --}}
                            @if($isFirst)
                                <td rowspan="{{ count($keluarDetails) }}" class="text-end text-dark fw-bold bg-light fw-bold">{{ format_angka($row['saldo_qty']) }}</td>
                                <td rowspan="{{ count($keluarDetails) }}" class="text-end text-dark fw-bold bg-light">{{ format_angka($saldoHarga) }}</td>
                                <td rowspan="{{ count($keluarDetails) }}" class="text-end text-dark fw-bold bg-light fw-bold">{{ format_angka($row['saldo_nilai']) }}</td>
                            @endif
                        </tr>
                    @endforeach
                    @endif

                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-5 border-0">
                            <i data-feather="folder-minus" style="width: 40px; height: 40px; opacity: 0.3;" class="mb-2"></i>
                            <br>
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
                            <span class="fs-6 fw-bold text-dark">{{ format_angka($batch['qty']) }}</span>
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
                <small class="text-muted me-3">Total Qty: <strong>{{ format_angka($totalQty) }}</strong></small>
                <small class="text-muted">Total Nilai: <strong class="text-primary">{{ rupiah($totalNilai) }}</strong></small>
            </div>
        </div>
    </div>
</div>
@endif

@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input  = document.getElementById('ks_bahan_input');
    const hidden = document.getElementById('ks_bahan_id');
    const panel  = document.getElementById('ks_bahan_panel');
    const searchUrl = '{{ route("umkm.bahan.search") }}';
    let debounceTimer;

    input?.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const q = this.value.trim();
        if (!q) { panel.style.display = 'none'; hidden.value = ''; return; }
        debounceTimer = setTimeout(() => {
            fetch(searchUrl + '?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    panel.innerHTML = '';
                    if (!data.length) { panel.style.display = 'none'; return; }
                    data.forEach(b => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action border-0 py-2 px-3';
                        btn.innerHTML = `<strong>${b.nama}</strong> <small class="text-muted">${b.satuan}</small>`;
                        btn.addEventListener('click', () => {
                            input.value  = b.nama + ' (' + b.satuan + ')';
                            hidden.value = b.id;
                            panel.style.display = 'none';
                        });
                        panel.appendChild(btn);
                    });
                    panel.style.display = 'block';
                });
        }, 280);
    });

    document.addEventListener('click', e => {
        if (!input?.closest('.position-relative')?.contains(e.target)) panel.style.display = 'none';
    });

    if (typeof feather !== 'undefined') feather.replace();
});
</script>
<style>
.table th, .table td { white-space: nowrap; }
.table td small { white-space: normal; }
</style>
@endpush

