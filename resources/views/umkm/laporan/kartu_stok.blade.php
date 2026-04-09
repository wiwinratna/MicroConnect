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
                @if($methodStr !== 'AVERAGE' && isset($saldoAwalBatches) && count($saldoAwalBatches) > 0)
                    @foreach($saldoAwalBatches as $idx => $batch)
                    <tr class="fw-bold bg-white">
                        @if($idx === 0)
                        <td class="text-start text-dark border-end" colspan="2" rowspan="{{ count($saldoAwalBatches) }}">SALDO AWAL — Per 1 {{ $namaBulan }}</td>
                        @endif
                        <td colspan="3" class="text-muted text-center border-end">—</td>
                        <td colspan="3" class="text-muted text-center border-end">—</td>
                        <td class="text-end fw-bold text-dark">{{ format_angka($batch['qty']) }}</td>
                        <td class="text-end fw-bold text-dark">{{ format_angka($batch['harga']) }}</td>
                        <td class="text-end fw-bold text-dark">{{ format_angka($batch['qty'] * $batch['harga']) }}</td>
                    </tr>
                    @endforeach
                @else
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
                @endif

                @php
                    $sumMasukQty = 0;
                    $sumMasukNilai = 0;
                    $sumKeluarQty = 0;
                    $sumKeluarNilai = 0;
                    $finalSaldoQty = $saldoAwalQty;
                    $finalSaldoNilai = $saldoAwalNilai;
                @endphp

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

                        // Cek apakah saldo akhir dipecah jadi multiline
                        $isMultiActive = isset($row['active_batches_snapshot']) && count($row['active_batches_snapshot']) > 1;

                        // Hitung total nilai keluar untuk row ini
                        $keluarNilaiTotalThisRow = 0;
                        if (!$isMasuk) {
                            if (count($keluarDetails) > 0) {
                                foreach($keluarDetails as $kDet) {
                                    $keluarNilaiTotalThisRow += ($kDet['qty'] * $kDet['harga']);
                                }
                            } else {
                                $keluarNilaiTotalThisRow = $keluarQtyTotal * $saldoHarga;
                            }
                        }

                        // Akumulasi footer
                        $sumMasukQty += $isMasuk ? $row['masuk_qty'] : 0;
                        $sumMasukNilai += $masukNilai;
                        $sumKeluarQty += $isMasuk ? 0 : $keluarQtyTotal;
                        $sumKeluarNilai += $keluarNilaiTotalThisRow;
                        $finalSaldoQty = $row['saldo_qty'];
                        $finalSaldoNilai = $row['saldo_nilai'];

                        // PREPARE IN_OUT LAYERS
                        $inOutLayers = [];
                        if ($isMasuk) {
                            $inOutLayers[] = [
                                'jenis' => 'MASUK',
                                'qty' => $row['masuk_qty'],
                                'harga' => $row['masuk_harga'],
                                'nilai' => $masukNilai,
                                'info' => ''
                            ];
                        } else {
                            if (count($keluarDetails) > 0) {
                                foreach($keluarDetails as $kd) {
                                    $inOutLayers[] = [
                                        'jenis' => 'KELUAR',
                                        'qty' => $kd['qty'],
                                        'harga' => $kd['harga'],
                                        'nilai' => $kd['qty'] * $kd['harga'],
                                        'info' => $kd['batch'] ?? ''
                                    ];
                                }
                            } else {
                                $inOutLayers[] = [
                                    'jenis' => 'KELUAR',
                                    'qty' => $keluarQtyTotal,
                                    'harga' => $saldoHarga,
                                    'nilai' => $keluarQtyTotal * $saldoHarga,
                                    'info' => ''
                                ];
                            }
                        }

                        // PREPARE SALDO LAYERS
                        $saldoLayers = [];
                        if (isset($row['active_batches_snapshot']) && count($row['active_batches_snapshot']) > 0 && $methodStr !== 'AVERAGE') {
                            foreach($row['active_batches_snapshot'] as $b) {
                                $saldoLayers[] = [
                                    'qty' => $b['qty'],
                                    'harga' => $b['harga'],
                                    'nilai' => $b['qty'] * $b['harga']
                                ];
                            }
                        } else {
                            $saldoLayers[] = [
                                'qty' => $row['saldo_qty'],
                                'harga' => $saldoHarga,
                                'nilai' => $row['saldo_nilai']
                            ];
                        }

                        $maxRows = max(count($inOutLayers), count($saldoLayers));
                    @endphp

                    @for($i = 0; $i < $maxRows; $i++)
                        @php
                            $inOut = $inOutLayers[$i] ?? null;
                            $sdo   = $saldoLayers[$i] ?? null;
                        @endphp
                        <tr>
                            @if($i === 0)
                                <td class="text-start text-muted border-end" rowspan="{{ $maxRows }}">{{ $tgl }}</td>
                                <td class="border-end text-start" rowspan="{{ $maxRows }}">
                                    @if($isMasuk)
                                        <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill me-1" style="font-size:10px; padding: 3px 8px;">IN</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill me-1" style="font-size:10px; padding: 3px 8px;">OUT</span>
                                    @endif
                                    <span class="fw-medium text-dark">{{ $ketLabel }}</span>
                                    <br><small class="text-muted" style="font-size:0.75rem;">{{ $refTipe }} #{{ $refId }}</small>
                                </td>
                            @endif

                            {{-- KOLOM MASUK --}}
                            @if($inOut && $inOut['jenis'] === 'MASUK')
                                <td class="text-end text-success fw-medium">{{ format_angka($inOut['qty']) }}</td>
                                <td class="text-end text-success">{{ format_angka($inOut['harga']) }}</td>
                                <td class="text-end text-success fw-semibold border-end">{{ format_angka($inOut['nilai']) }}</td>
                            @elseif($inOut && $inOut['jenis'] === 'KELUAR')
                                <td class="text-center text-muted border-end" colspan="3">—</td>
                            @else
                                <td class="text-center border-end" colspan="3">&nbsp;</td>
                            @endif

                            {{-- KOLOM KELUAR --}}
                            @if($inOut && $inOut['jenis'] === 'KELUAR')
                                <td class="text-end text-danger fw-medium">{{ format_angka($inOut['qty']) }}</td>
                                <td class="text-end text-danger">
                                    {{ format_angka($inOut['harga']) }}
                                    @if(!empty($inOut['info']) && $methodStr !== 'AVERAGE')
                                        <br><small class="text-muted" style="font-size:0.65rem; opacity:0.8;">Batch {{ $inOut['info'] }}</small>
                                    @endif
                                    @if(empty($inOut['info']) && count($keluarDetails) <= 1 && $methodStr === 'AVERAGE')
                                        <br><small class="text-muted" style="font-size:0.65rem; opacity:0.8;">(Avg)</small>
                                    @endif
                                </td>
                                <td class="text-end text-danger fw-semibold border-end">{{ format_angka($inOut['nilai']) }}</td>
                            @elseif($inOut && $inOut['jenis'] === 'MASUK')
                                <td class="text-center text-muted border-end" colspan="3">—</td>
                            @else
                                <td class="text-center border-end" colspan="3">&nbsp;</td>
                            @endif

                            {{-- KOLOM SALDO --}}
                            @if($sdo)
                                <td class="text-end text-dark fw-bold bg-light" style="{{ $sdo['qty'] <= 0 ? 'opacity:0.5;' : '' }}">{{ format_angka($sdo['qty']) }}</td>
                                <td class="text-end text-dark fw-bold bg-light" style="{{ $sdo['qty'] <= 0 ? 'opacity:0.5;' : '' }}">
                                    {{ $sdo['qty'] > 0 ? format_angka($sdo['harga']) : '—' }}
                                    @if($methodStr === 'AVERAGE' && $i === 0 && count($saldoLayers) === 1 && $sdo['qty'] > 0)
                                        <br><span class="opacity-50" style="font-size:0.70rem;">(Avg)</span>
                                    @endif
                                </td>
                                <td class="text-end text-dark fw-bold bg-light fw-bold" style="{{ $sdo['qty'] <= 0 ? 'opacity:0.5;' : '' }}">{{ format_angka($sdo['nilai']) }}</td>
                            @else
                                <td colspan="3" class="text-center border-start-0 border-end-0 bg-light">&nbsp;</td>
                            @endif
                        </tr>
                    @endfor

                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-5 border-0">
                            <i data-feather="folder-minus" style="width: 40px; height: 40px; opacity: 0.3;" class="mb-2"></i>
                            <br>
                            Tidak ada pergerakan / transaksi untuk bahan baku ini di bulan <strong>{{ $namaBulan }}</strong>.
                        </td>
                    </tr>
                @endforelse

                {{-- TOTAL FOOTER --}}
                @if(count($ledger) > 0 || $saldoAwalQty > 0)
                    @if($methodStr !== 'AVERAGE' && count($activeBatches) > 0)
                        @foreach($activeBatches as $idx => $batch)
                        <tr class="fw-bold text-end bg-light" style="{{ $idx === 0 ? 'border-top: 2px solid #ccc;' : '' }}">
                            @if($idx === 0)
                            <td colspan="2" class="text-start text-dark" rowspan="{{ count($activeBatches) }}">TOTAL PERGERAKAN & SALDO AKHIR</td>
                            <td class="text-success" rowspan="{{ count($activeBatches) }}">{{ format_angka($sumMasukQty) }}</td>
                            <td class="text-success" rowspan="{{ count($activeBatches) }}">―</td>
                            <td class="text-success" rowspan="{{ count($activeBatches) }}">{{ format_angka($sumMasukNilai) }}</td>
                            <td class="text-danger" rowspan="{{ count($activeBatches) }}">{{ format_angka($sumKeluarQty) }}</td>
                            <td class="text-danger" rowspan="{{ count($activeBatches) }}">―</td>
                            <td class="text-danger" rowspan="{{ count($activeBatches) }}">{{ format_angka($sumKeluarNilai) }}</td>
                            @endif
                            <td class="text-dark">{{ format_angka($batch['qty']) }}</td>
                            <td class="text-dark">{{ format_angka($batch['harga']) }}</td>
                            <td class="text-dark">{{ format_angka($batch['qty'] * $batch['harga']) }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr class="fw-bold text-end bg-light" style="border-top: 2px solid #ccc;">
                            <td colspan="2" class="text-start text-dark">TOTAL PERGERAKAN & SALDO AKHIR</td>
                            <td class="text-success">{{ format_angka($sumMasukQty) }}</td>
                            <td class="text-success">―</td>
                            <td class="text-success">{{ format_angka($sumMasukNilai) }}</td>
                            <td class="text-danger">{{ format_angka($sumKeluarQty) }}</td>
                            <td class="text-danger">―</td>
                            <td class="text-danger">{{ format_angka($sumKeluarNilai) }}</td>
                            <td class="text-dark">{{ format_angka($finalSaldoQty) }}</td>
                            <td class="text-dark">
                                {{ $finalSaldoQty > 0 ? format_angka($finalSaldoNilai / $finalSaldoQty) : '―' }}
                            </td>
                            <td class="text-dark">{{ format_angka($finalSaldoNilai) }}</td>
                        </tr>
                    @endif
                @endif
            </tbody>
        </table>
    </div>
</div>



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

