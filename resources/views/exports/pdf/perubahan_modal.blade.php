@extends('exports.pdf.layout')

@section('content')
<table style="width: 70%; margin: 0 auto;">
    <tbody>
        <tr>
            <td>Modal Awal (per 1 {{ \Carbon\Carbon::parse($awal)->translatedFormat('F Y') }})</td>
            <td class="text-right text-bold">{{ number_format($modalAwal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Laba Bersih Bulan Ini</td>
            <td class="text-right text-bold" style="{{ $labaBersih < 0 ? 'color:#d32f2f;' : 'color:#2e7d32;' }}">
                {{ $labaBersih < 0 ? '(' . number_format(abs($labaBersih), 0, ',', '.') . ')' : number_format($labaBersih, 0, ',', '.') }}
            </td>
        </tr>
        <tr>
            <td>Prive (Penarikan Pribadi)</td>
            <td class="text-right" style="color:#d32f2f;">
                ({{ number_format($prive, 0, ',', '.') }})
            </td>
        </tr>
        <tr>
            <td class="text-bold bg-light" style="font-size:14px;">MODAL AKHIR</td>
            <td class="text-right text-bold bg-light" style="font-size:14px;">
                Rp {{ number_format($modalAkhir, 0, ',', '.') }}
            </td>
        </tr>
    </tbody>
</table>
@endsection
