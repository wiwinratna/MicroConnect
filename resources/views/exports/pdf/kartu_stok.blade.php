@extends('exports.pdf.layout')

@section('content')
<div style="margin-bottom: 20px;">
    <strong>Bahan Baku:</strong> {{ $bahan->nama_bahan }}<br>
    <strong>Satuan:</strong> {{ $bahan->satuan }}
</div>

<table class="table-sm">
    <thead>
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
            <tr>
                <td class="text-center">{{ $row['tanggal'] ? \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') : '-' }}</td>
                <td>{{ $row['jenis'] }} {!! $row['ref_tipe'] ? '<br><small>('.$row['ref_tipe'].')</small>' : '' !!}</td>
                
                {{-- Masuk --}}
                <td class="text-center">{{ $row['qty_masuk'] ?: '' }}</td>
                <td class="text-right">{{ $row['harga_masuk'] ? number_format($row['harga_masuk'], 0, ',', '.') : '' }}</td>
                <td class="text-right">{{ $row['total_masuk'] ? number_format($row['total_masuk'], 0, ',', '.') : '' }}</td>
                
                {{-- Keluar --}}
                <td class="text-center">{{ $row['qty_keluar'] ?: '' }}</td>
                <td class="text-right">{{ $row['harga_keluar'] ? number_format($row['harga_keluar'], 0, ',', '.') : '' }}</td>
                <td class="text-right">{{ $row['total_keluar'] ? number_format($row['total_keluar'], 0, ',', '.') : '' }}</td>
                
                {{-- Saldo --}}
                <td class="text-center text-bold bg-light">{{ $row['qty_saldo'] }}</td>
                <td class="text-right text-bold bg-light" style="font-size:11px;">{{ number_format($row['total_saldo'], 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="10" class="text-center">Belum ada mutasi historis atau tidak ada data pada rentang ini.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
