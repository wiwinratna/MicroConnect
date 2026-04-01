@extends('exports.pdf.layout')

@section('content')
<table style="width: 80%; margin: 0 auto;">
    <tbody>
        {{-- PENDAPATAN --}}
        <tr><td colspan="2" class="text-bold bg-light">PENDAPATAN USAHA</td></tr>
        @forelse($pendapatan as $p)
            <tr>
                <td style="padding-left: 20px;">{{ $p->nama_akun }}</td>
                <td  class="text-right text-end fw-medium">{{ format_angka($p->kredit - $p->debit) }}</td>
            </tr>
        @empty
            <tr><td colspan="2" style="padding-left: 20px;"><em>Tidak ada pendapatan</em></td></tr>
        @endforelse
        <tr>
            <td class="text-bold text-right">TOTAL PENDAPATAN</td>
            <td  class="text-right text-bold text-end fw-medium">{{ format_angka($totalPendapatan) }}</td>
        </tr>

        {{-- HPP --}}
        <tr><td colspan="2" class="text-bold bg-light">HARGA POKOK PENJUALAN (HPP)</td></tr>
        @if($isPeriodik)
            <tr>
                <td style="padding-left: 20px;">Persediaan Awal Bahan Baku</td>
                <td  class="text-right text-end fw-medium">{{ format_angka($persediaanAwal) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Pembelian Bahan Baku (Bulan Ini)</td>
                <td  class="text-right text-end fw-medium">{{ format_angka($totalPembelianBulanIni) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">(-) Persediaan Akhir Bahan Baku</td>
                <td class="text-right">({{ format_angka($persediaanAkhir) }})</td>
            </tr>
        @else
            <tr>
                <td style="padding-left: 20px;">HPP (Perpetual / Jurnal)</td>
                <td  class="text-right text-end fw-medium">{{ format_angka($totalHpp) }}</td>
            </tr>
        @endif
        <tr>
            <td class="text-bold text-right" style="color:#d32f2f;">TOTAL HPP</td>
            <td class="text-right text-bold" style="color:#d32f2f;">({{ format_angka($totalHpp) }})</td>
        </tr>

        {{-- LABA KOTOR --}}
        <tr>
            <td class="text-bold text-right bg-light" style="font-size:12px;">LABA KOTOR</td>
            <td style="font-size:12px;"  class="text-right text-bold bg-light text-end fw-medium">{{ format_angka($labaKotor) }}</td>
        </tr>

        {{-- BEBAN OPERASIONAL --}}
        <tr><td colspan="2" class="text-bold bg-light">BEBAN OPERASIONAL</td></tr>
        @forelse($bebanDetail as $kode => $b)
            <tr>
                <td style="padding-left: 20px;">{{ $kode }} - {{ $b['nama'] }}</td>
                <td  class="text-right text-end fw-medium">{{ format_angka($b['total']) }}</td>
            </tr>
        @empty
            <tr><td colspan="2" style="padding-left: 20px;"><em>Tidak ada beban</em></td></tr>
        @endforelse
        <tr>
            <td class="text-bold text-right" style="color:#d32f2f;">TOTAL BEBAN</td>
            <td class="text-right text-bold" style="color:#d32f2f;">({{ format_angka($totalBeban) }})</td>
        </tr>

        {{-- LABA BERSIH --}}
        <tr>
            <td class="text-bold text-right bg-light" style="font-size:14px;">LABA BERSIH</td>
            <td style="font-size:14px;"  class="text-right text-bold bg-light text-end fw-medium">{{ rupiah($labaBersih) }}</td>
        </tr>
    </tbody>
</table>
@endsection
