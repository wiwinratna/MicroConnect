@extends('exports.pdf.layout')

@section('content')
<div style="margin-bottom: 20px;">
    <strong>Bahan Baku:</strong> {{ $bahan->nama_bahan }}<br>
    <strong>Satuan:</strong> {{ $bahan->satuan }}
</div>

<table class="table-sm table table-hover table-borderless align-middle">
    <thead class="table-light">
        <tr>
            <th rowspan="2" class="text-center" style="vertical-align:middle; width:8%;">Tanggal</th>
            <th rowspan="2" style="vertical-align:middle; width:20%;">Transaksi</th>
            <th colspan="3" class="text-center">Stok Masuk</th>
            <th colspan="3" class="text-center">Stok Keluar</th>
            <th colspan="3" class="text-center">Saldo</th>
        </tr>
        <tr>
            <th class="text-center">Qty</th>
            <th class="text-right">Harga</th>
            <th class="text-right">Total</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Harga</th>
            <th class="text-right">Total</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Harga</th>
            <th class="text-right">Total (Rp)</th>
        </tr>
    </thead>
    <tbody>
        {{-- BARIS SALDO AWAL --}}
        @if($methodStr !== 'AVERAGE' && isset($saldoAwalBatches) && count($saldoAwalBatches) > 0)
            @foreach($saldoAwalBatches as $idx => $batch)
            <tr>
                @if($idx === 0)
                <td class="text-left" colspan="2" rowspan="{{ count($saldoAwalBatches) }}"><strong>SALDO AWAL</strong> — Per 1 {{ \Carbon\Carbon::parse($awal)->translatedFormat('F Y') }}</td>
                @endif
                <td colspan="3" class="text-center">—</td>
                <td colspan="3" class="text-center">—</td>
                <td class="text-center text-bold">{{ format_angka($batch['qty']) }}</td>
                <td class="text-right text-bold">{{ format_angka($batch['harga']) }}</td>
                <td class="text-right text-bold">{{ format_angka($batch['qty'] * $batch['harga']) }}</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td class="text-left" colspan="2"><strong>SALDO AWAL</strong> — Per 1 {{ \Carbon\Carbon::parse($awal)->translatedFormat('F Y') }}</td>
                <td colspan="3" class="text-center">—</td>
                <td colspan="3" class="text-center">—</td>
                <td class="text-center text-bold">{{ format_angka($saldoAwalQty) }}</td>
                <td class="text-right text-bold">
                    @if($saldoAwalQty > 0)
                        {{ format_angka($saldoAwalNilai / $saldoAwalQty) }}
                    @else
                        —
                    @endif
                </td>
                <td class="text-right text-bold">{{ format_angka($saldoAwalNilai) }}</td>
            </tr>
        @endif

        @php
            $sumMasukQty = 0; $sumMasukNilai = 0;
            $sumKeluarQty = 0; $sumKeluarNilai = 0;
            $finalSaldoQty = $saldoAwalQty; $finalSaldoNilai = $saldoAwalNilai;
        @endphp

        @forelse($filteredLedger as $row)
            @php
                $isMasuk = $row['jenis'] === 'MASUK';
                $masukNilai = $isMasuk ? ($row['masuk_qty'] * $row['masuk_harga']) : 0;
                $keluarQtyTotal = $isMasuk ? 0 : $row['keluar_qty'];
                $keluarDetails = $row['keluar_detail'] ?? [];
                $saldoHarga = $row['saldo_qty'] > 0 ? ($row['saldo_nilai'] / $row['saldo_qty']) : 0;

                $keluarNilaiTotalRun = 0;
                if (!$isMasuk) {
                    if (count($keluarDetails) > 0) {
                        foreach($keluarDetails as $kDet) { $keluarNilaiTotalRun += ($kDet['qty'] * $kDet['harga']); }
                    } else {
                        $keluarNilaiTotalRun = $keluarQtyTotal * $saldoHarga;
                    }
                }

                $sumMasukQty += $isMasuk ? $row['masuk_qty'] : 0;
                $sumMasukNilai += $masukNilai;
                $sumKeluarQty += $keluarQtyTotal;
                $sumKeluarNilai += $keluarNilaiTotalRun;
                $finalSaldoQty = $row['saldo_qty'];
                $finalSaldoNilai = $row['saldo_nilai'];

                $inOutLayers = [];
                if ($isMasuk) {
                    $inOutLayers[] = ['jenis' => 'MASUK', 'qty' => $row['masuk_qty'], 'harga' => $row['masuk_harga'], 'nilai' => $masukNilai, 'info' => ''];
                } else {
                    if (count($keluarDetails) > 0) {
                        foreach($keluarDetails as $kd) {
                            $inOutLayers[] = ['jenis' => 'KELUAR', 'qty' => $kd['qty'], 'harga' => $kd['harga'], 'nilai' => $kd['qty'] * $kd['harga'], 'info' => $kd['batch'] ?? ''];
                        }
                    } else {
                        $inOutLayers[] = ['jenis' => 'KELUAR', 'qty' => $keluarQtyTotal, 'harga' => $saldoHarga, 'nilai' => $keluarQtyTotal * $saldoHarga, 'info' => ''];
                    }
                }

                $saldoLayers = [];
                if (isset($row['active_batches_snapshot']) && count($row['active_batches_snapshot']) > 0 && $methodStr !== 'AVERAGE') {
                    foreach($row['active_batches_snapshot'] as $b) {
                        $saldoLayers[] = ['qty' => $b['qty'], 'harga' => $b['harga'], 'nilai' => $b['qty'] * $b['harga']];
                    }
                } else {
                    $saldoLayers[] = ['qty' => $row['saldo_qty'], 'harga' => $saldoHarga, 'nilai' => $row['saldo_nilai']];
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
                        <td class="text-center" rowspan="{{ $maxRows }}">{{ $row['tanggal'] ? \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') : '-' }}</td>
                        <td rowspan="{{ $maxRows }}">{{ $row['jenis'] }} {!! $row['ref_tipe'] ? '<br><small>('.$row['ref_tipe'].' #'.$row['ref_id'].')</small>' : '' !!}</td>
                    @endif
                    
                    {{-- Masuk --}}
                    @if($inOut && $inOut['jenis'] === 'MASUK')
                        <td class="text-center">{{ format_angka($inOut['qty']) }}</td>
                        <td class="text-right">{{ format_angka($inOut['harga']) }}</td>
                        <td class="text-right">{{ format_angka($inOut['nilai']) }}</td>
                    @elseif($inOut && $inOut['jenis'] === 'KELUAR')
                        <td class="text-center" colspan="3">—</td>
                    @else
                        <td class="text-center" colspan="3">&nbsp;</td>
                    @endif
                    
                    {{-- Keluar --}}
                    @if($inOut && $inOut['jenis'] === 'KELUAR')
                        <td class="text-center">{{ format_angka($inOut['qty']) }}</td>
                        <td class="text-right">{{ format_angka($inOut['harga']) }}</td>
                        <td class="text-right">{{ format_angka($inOut['nilai']) }}</td>
                    @elseif($inOut && $inOut['jenis'] === 'MASUK')
                        <td class="text-center" colspan="3">—</td>
                    @else
                        <td class="text-center" colspan="3">&nbsp;</td>
                    @endif
                    
                    {{-- Saldo --}}
                    @if($sdo)
                        <td class="text-center text-bold bg-light">{{ format_angka($sdo['qty']) }}</td>
                        <td class="text-right text-bold bg-light">{{ format_angka($sdo['harga']) }}</td>
                        <td class="text-right text-bold bg-light">{{ format_angka($sdo['nilai']) }}</td>
                    @else
                        <td class="text-center bg-light" colspan="3">&nbsp;</td>
                    @endif
                </tr>
            @endfor
        @empty
            <tr><td colspan="11" class="text-center">Belum ada mutasi historis atau tidak ada data pada rentang ini.</td></tr>
        @endforelse

        {{-- TOTAL FOOTER --}}
        @if(count($filteredLedger) > 0 || $saldoAwalQty > 0)
            @php
                $activeBatchesAtEnd = [];
                if (!empty($filteredLedger)) {
                    $activeBatchesAtEnd = end($filteredLedger)['active_batches_snapshot'] ?? [];
                } else {
                    $activeBatchesAtEnd = $saldoAwalBatches ?? [];
                }
            @endphp
            @if($methodStr !== 'AVERAGE' && count($activeBatchesAtEnd) > 0)
                @foreach($activeBatchesAtEnd as $idx => $batch)
                <tr style="{{ $idx === 0 ? 'border-top: 2px solid #555;' : '' }}">
                    @if($idx === 0)
                    <td colspan="2" class="text-left" rowspan="{{ count($activeBatchesAtEnd) }}"><strong>TOTAL PERGERAKAN & SALDO AKHIR</strong></td>
                    <td class="text-center" rowspan="{{ count($activeBatchesAtEnd) }}"><strong>{{ format_angka($sumMasukQty) }}</strong></td>
                    <td class="text-center" rowspan="{{ count($activeBatchesAtEnd) }}"><strong>—</strong></td>
                    <td class="text-right" rowspan="{{ count($activeBatchesAtEnd) }}"><strong>{{ format_angka($sumMasukNilai) }}</strong></td>
                    <td class="text-center" rowspan="{{ count($activeBatchesAtEnd) }}"><strong>{{ format_angka($sumKeluarQty) }}</strong></td>
                    <td class="text-center" rowspan="{{ count($activeBatchesAtEnd) }}"><strong>—</strong></td>
                    <td class="text-right" rowspan="{{ count($activeBatchesAtEnd) }}"><strong>{{ format_angka($sumKeluarNilai) }}</strong></td>
                    @endif
                    <td class="text-center text-bold">{{ format_angka($batch['qty']) }}</td>
                    <td class="text-right text-bold">{{ format_angka($batch['harga']) }}</td>
                    <td class="text-right text-bold">{{ format_angka($batch['qty'] * $batch['harga']) }}</td>
                </tr>
                @endforeach
            @else
                <tr style="border-top: 2px solid #555;">
                    <td colspan="2" class="text-left"><strong>TOTAL PERGERAKAN & SALDO AKHIR</strong></td>
                    <td class="text-center"><strong>{{ format_angka($sumMasukQty) }}</strong></td>
                    <td class="text-center"><strong>—</strong></td>
                    <td class="text-right"><strong>{{ format_angka($sumMasukNilai) }}</strong></td>
                    <td class="text-center"><strong>{{ format_angka($sumKeluarQty) }}</strong></td>
                    <td class="text-center"><strong>—</strong></td>
                    <td class="text-right"><strong>{{ format_angka($sumKeluarNilai) }}</strong></td>
                    <td class="text-center text-bold">{{ format_angka($finalSaldoQty) }}</td>
                    <td class="text-right text-bold">
                        {{ $finalSaldoQty > 0 ? format_angka($finalSaldoNilai / $finalSaldoQty) : '—' }}
                    </td>
                    <td class="text-right text-bold">{{ format_angka($finalSaldoNilai) }}</td>
                </tr>
            @endif
        @endif
    </tbody>
</table>
@endsection
