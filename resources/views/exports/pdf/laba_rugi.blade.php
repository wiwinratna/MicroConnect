@extends('exports.pdf.layout')

@section('content')
<table style="width: 80%; margin: 0 auto;">
    <tbody>
        {{-- PENDAPATAN --}}
        <tr><td colspan="2" class="text-bold bg-light">PENDAPATAN USAHA</td></tr>
        @forelse($pendapatan as $p)
            <tr>
                <td style="padding-left: 20px;">{{ $p->nama_akun }}</td>
                <td class="text-right">{{ number_format($p->kredit - $p->debit, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="2" style="padding-left: 20px;"><em>Tidak ada pendapatan</em></td></tr>
        @endforelse
        <tr>
            <td class="text-bold text-right">TOTAL PENDAPATAN</td>
            <td class="text-right text-bold">{{ number_format($totalPendapatan, 0, ',', '.') }}</td>
        </tr>

        {{-- HPP --}}
        <tr><td colspan="2" class="text-bold bg-light">HARGA POKOK PENJUALAN (HPP)</td></tr>
        @if($isPeriodik)
            <tr>
                <td style="padding-left: 20px;">Persediaan Awal Bahan Baku</td>
                <td class="text-right">{{ number_format($persediaanAwal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Pembelian Bahan Baku (Bulan Ini)</td>
                <td class="text-right">{{ number_format($totalPembelianBulanIni, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">(-) Persediaan Akhir Bahan Baku</td>
                <td class="text-right">({{ number_format($persediaanAkhir, 0, ',', '.') }})</td>
            </tr>
        @else
            <tr>
                <td style="padding-left: 20px;">HPP (Perpetual / Jurnal)</td>
                <td class="text-right">{{ number_format($totalHpp, 0, ',', '.') }}</td>
            </tr>
        @endif
        <tr>
            <td class="text-bold text-right" style="color:#d32f2f;">TOTAL HPP</td>
            <td class="text-right text-bold" style="color:#d32f2f;">({{ number_format($totalHpp, 0, ',', '.') }})</td>
        </tr>

        {{-- LABA KOTOR --}}
        <tr>
            <td class="text-bold text-right bg-light" style="font-size:12px;">LABA KOTOR</td>
            <td class="text-right text-bold bg-light" style="font-size:12px;">{{ number_format($labaKotor, 0, ',', '.') }}</td>
        </tr>

        {{-- BEBAN OPERASIONAL --}}
        <tr><td colspan="2" class="text-bold bg-light">BEBAN OPERASIONAL</td></tr>
        @forelse($bebanDetail as $kode => $b)
            <tr>
                <td style="padding-left: 20px;">{{ $kode }} - {{ $b['nama'] }}</td>
                <td class="text-right">{{ number_format($b['total'], 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="2" style="padding-left: 20px;"><em>Tidak ada beban</em></td></tr>
        @endforelse
        <tr>
            <td class="text-bold text-right" style="color:#d32f2f;">TOTAL BEBAN</td>
            <td class="text-right text-bold" style="color:#d32f2f;">({{ number_format($totalBeban, 0, ',', '.') }})</td>
        </tr>

        {{-- LABA BERSIH --}}
        <tr>
            <td class="text-bold text-right bg-light" style="font-size:14px;">LABA BERSIH</td>
            <td class="text-right text-bold bg-light" style="font-size:14px;">Rp {{ number_format($labaBersih, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>
@endsection
