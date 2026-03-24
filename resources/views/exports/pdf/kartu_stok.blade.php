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
            <th colspan="2" class="text-center">Saldo</th>
        </tr>
        <tr>
            <th class="text-center">Qty</th>
            <th class="text-right">Harga</th>
            <th class="text-right">Total</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Harga</th>
            <th class="text-right">Total</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Total (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($filteredLedger as $row)
            @php
                $isMasuk = $row['jenis'] === 'MASUK';
                $masukNilai = $isMasuk ? ($row['masuk_qty'] * $row['masuk_harga']) : 0;
                
                $keluarQtyTotal = $isMasuk ? 0 : $row['keluar_qty'];
                $keluarDetails = $row['keluar_detail'] ?? [];
                
                $keluarHargaAvg = 0;
                $keluarNilaiTotal = 0;
                if (!$isMasuk && count($keluarDetails) > 0) {
                    foreach($keluarDetails as $det) {
                        $keluarNilaiTotal += ($det['qty'] * $det['harga']);
                    }
                    $keluarHargaAvg = $keluarQtyTotal > 0 ? ($keluarNilaiTotal / $keluarQtyTotal) : 0;
                }
            @endphp
            <tr>
                <td class="text-center">{{ $row['tanggal'] ? \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') : '-' }}</td>
                <td>{{ $row['jenis'] }} {!! $row['ref_tipe'] ? '<br><small>('.$row['ref_tipe'].')</small>' : '' !!}</td>
                
                {{-- Masuk --}}
                <td class="text-center">{{ $isMasuk ? $row['masuk_qty'] : '' }}</td>
                <td class="text-right">{{ $isMasuk ? format_angka($row['masuk_harga']) : '' }}</td>
                <td class="text-right">{{ $isMasuk ? format_angka($masukNilai) : '' }}</td>
                
                {{-- Keluar --}}
                <td class="text-center">{{ !$isMasuk ? $keluarQtyTotal : '' }}</td>
                <td class="text-right">{{ !$isMasuk ? format_angka($keluarHargaAvg) : '' }}</td>
                <td class="text-right">{{ !$isMasuk ? format_angka($keluarNilaiTotal) : '' }}</td>
                
                {{-- Saldo --}}
                <td class="text-center text-bold bg-light">{{ $row['saldo_qty'] }}</td>
                <td style="font-size:11px;" class="text-right text-bold bg-light fw-medium">{{ format_angka($row['saldo_nilai']) }}</td>
            </tr>
        @empty
            <tr><td colspan="10" class="text-center">Belum ada mutasi historis atau tidak ada data pada rentang ini.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
