@extends('exports.pdf.layout')

@section('content')
<table>
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="15%">Kode Piutang</th>
            <th width="15%">Tanggal</th>
            <th width="20%">Pelanggan</th>
            <th width="15%" class="text-right">Nominal Awal</th>
            <th width="15%" class="text-right">Dibayar</th>
            <th width="15%" class="text-right">Sisa / Saldo</th>
        </tr>
    </thead>
    <tbody>
        @php 
            $no = 1; 
            $tAwal = 0; $tDibayar = 0; $tSisa = 0;
        @endphp
        @forelse($piutangList as $p)
            @php 
                $tAwal += $p->nominal_awal; 
                $tDibayar += $p->sudah_dibayar; 
                $tSisa += $p->sisa;
            @endphp
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $p->kode_piutang }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}<br><small>JT: {{ $p->jatuh_tempo ? \Carbon\Carbon::parse($p->jatuh_tempo)->format('d/m/Y') : '-' }}</small></td>
                <td>
                    <b>{{ $p->pelanggan->nama_pelanggan ?? 'Dihapus' }}</b>
                    <br><small>{{ $p->status === 'lunas' ? '(Lunas)' : '(Belum Lunas)' }}</small>
                </td>
                <td class="text-right">{{ number_format($p->nominal_awal, 0, ',', '.') }}</td>
                <td class="text-right" style="color:#2e7d32;">{{ number_format($p->sudah_dibayar, 0, ',', '.') }}</td>
                <td class="text-right text-bold" style="color:#d32f2f;">{{ number_format($p->sisa, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">Tidak ada catatan piutang.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" class="text-right">Total Akumulasi</th>
            <th class="text-right">{{ number_format($tAwal, 0, ',', '.') }}</th>
            <th class="text-right text-bold" style="color:#2e7d32;">{{ number_format($tDibayar, 0, ',', '.') }}</th>
            <th class="text-right text-bold" style="color:#d32f2f; font-size:12px;">{{ number_format($tSisa, 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>
@endsection
