@extends('exports.pdf.layout')

@section('content')
<table style="width: 70%; margin: 0 auto;">
    <tbody>
        <tr>
            <td>Modal Awal (per 1 {{ \Carbon\Carbon::parse($awal)->translatedFormat('F Y') }})</td>
            <td  class="text-right text-bold text-end fw-medium">{{ format_angka($modalAwal) }}</td>
        </tr>
        <tr>
            <td>Laba Bersih Bulan Ini</td>
            <td class="text-right text-bold" style="{{ $labaBersih < 0 ? 'color:#d32f2f;' : 'color:#2e7d32;' }}">
                {{ $labaBersih < 0 ? '(' . format_angka(abs($labaBersih)) . ')' : format_angka($labaBersih) }}
            </td>
        </tr>
        <tr>
            <td>Prive (Penarikan Pribadi)</td>
            <td class="text-right" style="color:#d32f2f;">
                ({{ format_angka($prive) }})
            </td>
        </tr>
        <tr>
            <td class="text-bold bg-light" style="font-size:14px;">MODAL AKHIR</td>
            <td style="font-size:14px;"  class="text-right text-bold bg-light text-end fw-medium">{{ rupiah($modalAkhir) }}
            </td>
        </tr>
    </tbody>
</table>
@endsection
