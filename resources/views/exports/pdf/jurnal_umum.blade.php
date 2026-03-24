@extends('exports.pdf.layout')

@section('content')
<table>
    <thead class="table-light">
        <tr>
            <th>Tanggal</th>
            <th>No. Referensi</th>
            <th>Kode Akun</th>
            <th>Nama Akun</th>
            <th>Keterangan</th>
            <th class="text-right">Debit</th>
            <th class="text-right">Kredit</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $tDebit = 0; $tKredit = 0; 
        @endphp
        @forelse($jurnal as $j)
            @php 
                $tDebit += $j->debit; 
                $tKredit += $j->kredit; 
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($j->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $j->ref_tipe }}</td>
                <td>{{ $j->kode_akun }}</td>
                <td>{{ $j->nama_akun }}</td>
                <td>{{ $j->keterangan }}</td>
                <td  class="text-right text-end fw-medium">{{ format_angka($j->debit) }}</td>
                <td  class="text-right text-end fw-medium">{{ format_angka($j->kredit) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">Tidak ada data transaksi.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5" class="text-right">Total</th>
            <th class="text-right">{{ format_angka($tDebit) }}</th>
            <th class="text-right">{{ format_angka($tKredit) }}</th>
        </tr>
    </tfoot>
</table>
@endsection
