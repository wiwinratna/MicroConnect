@extends('exports.pdf.layout')

@section('content')
@forelse($bukuBesar as $kode => $akunBuku)
    <div style="margin-bottom: 25px;">
        <table class="mb-2">
            <tr>
                <td style="border:none; padding:0;"><strong>Nama Akun:</strong> {{ $kode }} - {{ $akunBuku['nama_akun'] }}</td>
                <td style="border:none; padding:0;" class="text-right"><strong>Posisi Normal:</strong> {{ $akunBuku['posisi'] }}</td>
            </tr>
        </table>
        
        <table>
            <thead>
                <tr>
                    <th width="15%">Tanggal</th>
                    <th width="35%">Keterangan</th>
                    <th class="text-right" width="15%">Debit (Rp)</th>
                    <th class="text-right" width="15%">Kredit (Rp)</th>
                    <th class="text-right" width="20%">Saldo Berjalan</th>
                </tr>
            </thead>
            <tbody>
                @php $saldoRunning = 0; @endphp
                @foreach($akunBuku['items'] as $j)
                    @php
                        if ($akunBuku['posisi'] === 'Debit') {
                            $saldoRunning += ($j->debit - $j->kredit);
                        } else {
                            $saldoRunning += ($j->kredit - $j->debit);
                        }
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($j->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $j->keterangan }}</td>
                        <td class="text-right">{{ number_format($j->debit, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($j->kredit, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($saldoRunning, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-right">Total Akun {{ $kode }}</th>
                    <th class="text-right">{{ number_format($akunBuku['total_debit'], 0, ',', '.') }}</th>
                    <th class="text-right">{{ number_format($akunBuku['total_kredit'], 0, ',', '.') }}</th>
                    <th class="text-right">{{ number_format($akunBuku['saldo_akhir'], 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
@empty
    <p class="text-center">Tidak ada data transaksi di periode ini.</p>
@endforelse
@endsection
