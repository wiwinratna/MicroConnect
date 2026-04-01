@extends('exports.pdf.layout')

@section('content')
<table>
    <thead class="table-light">
        <tr>
            <th width="5%">No</th>
            <th width="12%">Tanggal</th>
            <th width="20%">No. Faktur / Ref</th>
            <th width="23%">Supplier</th>
            <th width="25%">Item Dibeli</th>
            <th width="15%" class="text-right">Total (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; $grandTotal = 0; @endphp
        @forelse($pembelian as $p)
            @php $grandTotal += $p->total; @endphp
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $p->kode_pembelian }}</td>
                <td>{{ $p->supplier ?: 'Umum (Tanpa Nama)' }}</td>
                <td style="font-size:10px;">
                    @foreach($p->details as $d)
                        <div>- {{ $d->bahan->nama_bahan ?? 'Bahan Dihapus' }} ({{ $d->qty }})</div>
                    @endforeach
                </td>
                <td  class="text-right text-end fw-medium">{{ format_angka($p->total) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">Tidak ada transaksi pembelian.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" class="text-right">Total Pembelian</th>
            <th class="text-right text-bold" style="font-size:12px;">{{ format_angka($grandTotal) }}</th>
        </tr>
    </tfoot>
</table>
@endsection
