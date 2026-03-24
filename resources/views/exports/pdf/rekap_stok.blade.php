@extends('exports.pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Bahan Baku</th>
            <th>Satuan</th>
            <th class="text-right">Total Masuk</th>
            <th class="text-right">Total Keluar</th>
            <th class="text-right">Saldo Berjalan</th>
            <th class="text-right">Nilai Masuk (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; $totalNilaiMasuk = 0; @endphp
        @forelse($stokPerBahan as $s)
            @php $totalNilaiMasuk += $s->nilai_masuk; @endphp
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $s->nama_bahan }}</td>
                <td class="text-center">{{ $s->satuan }}</td>
                <td class="text-right text-bold" style="color:#2e7d32;">{{ $s->masuk }}</td>
                <td class="text-right text-bold" style="color:#d32f2f;">{{ $s->keluar }}</td>
                <td class="text-right text-bold">{{ $s->saldo }}</td>
                <td class="text-right">{{ number_format($s->nilai_masuk, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">Tidak ada mutasi stok di periode ini.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="6" class="text-right">Total Nilai Bahan Baku Masuk</th>
            <th class="text-right">{{ number_format($totalNilaiMasuk, 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>
@endsection
