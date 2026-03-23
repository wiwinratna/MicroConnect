@extends('exports.pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="12%">Tanggal</th>
            <th width="18%">No. Transaksi</th>
            <th width="20%">Pelanggan/Pembeli</th>
            <th width="20%">Item Terjual</th>
            <th width="10%">Catatan</th>
            <th width="15%" class="text-right">Total (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; $grandTotal = 0; @endphp
        @forelse($penjualan as $p)
            @php $grandTotal += $p->total; @endphp
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $p->kode_penjualan }}</td>
                <td>{{ $p->pembeli ?: 'Tunai / Umum' }}</td>
                <td style="font-size:10px;">
                    @foreach($p->details as $d)
                        <div>- {{ $d->produk->nama_produk ?? 'Dihapus' }} ({{ $d->qty }})</div>
                    @endforeach
                </td>
                <td style="font-size:10px;">{{ $p->catatan ?: '-' }}</td>
                <td class="text-right">{{ number_format($p->total, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">Tidak ada transaksi penjualan.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="6" class="text-right">Total Penjualan</th>
            <th class="text-right text-bold" style="font-size:12px;">{{ number_format($grandTotal, 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>
@endsection
