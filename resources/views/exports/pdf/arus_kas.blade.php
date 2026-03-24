@extends('exports.pdf.layout')

@section('content')
<table style="width: 80%; margin: 0 auto;">
    <tbody>
        <tr>
            <td class="text-bold bg-light">Saldo Kas Awal Periode</td>
            <td  class="text-right text-bold bg-light text-end fw-medium">{{ format_angka($kasAwalPeriode) }}</td>
        </tr>

        <tr><td colspan="2" class="text-bold bg-light">ARUS KAS MASUK</td></tr>
        @php $masukC = 0; @endphp
        @forelse($kasJurnal->where('debit','>',0) as $j)
            @php $masukC++; @endphp
            <tr>
                <td style="padding-left: 20px;">{{ \Carbon\Carbon::parse($j->tanggal)->format('d/m') }} - {{ $j->keterangan }}</td>
                <td  class="text-right text-end fw-medium">{{ format_angka($j->debit) }}</td>
            </tr>
        @empty
            <tr><td colspan="2" style="padding-left:20px;">Tidak ada kas masuk</td></tr>
        @endforelse
        <tr>
            <td class="text-right text-bold">TOTAL KAS MASUK</td>
            <td style="color:#2e7d32;"  class="text-right text-bold text-end fw-medium">{{ format_angka($kasIn) }}</td>
        </tr>

        <tr><td colspan="2" class="text-bold bg-light">ARUS KAS KELUAR</td></tr>
        @php $keluarC = 0; @endphp
        @forelse($kasJurnal->where('kredit','>',0) as $j)
            @php $keluarC++; @endphp
            <tr>
                <td style="padding-left: 20px;">{{ \Carbon\Carbon::parse($j->tanggal)->format('d/m') }} - {{ $j->keterangan }}</td>
                <td  class="text-right text-end fw-medium">{{ format_angka($j->kredit) }}</td>
            </tr>
        @empty
            <tr><td colspan="2" style="padding-left:20px;">Tidak ada kas keluar</td></tr>
        @endforelse
        <tr>
            <td class="text-right text-bold">TOTAL KAS KELUAR</td>
            <td class="text-right text-bold" style="color:#d32f2f;">({{ format_angka($kasOut) }})</td>
        </tr>

        <tr>
            <td class="text-bold text-right bg-light">KENAIKAN (PENURUNAN) KAS BERSIH</td>
            <td class="text-right text-bold bg-light">{{ $netKas < 0 ? '(' . format_angka(abs($netKas)) . ')' : format_angka($netKas) }}</td>
        </tr>
        
        <tr>
            <td class="text-bold bg-light" style="font-size:14px;">SALDO KAS AKHIR PERIODE</td>
            <td style="font-size:14px;"  class="text-right text-bold bg-light text-end fw-medium">{{ rupiah($kasAkhirPeriode) }}</td>
        </tr>
    </tbody>
</table>
@endsection
