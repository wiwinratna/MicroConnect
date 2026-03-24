<?php

// 1. FIX EXPORT CONTROLLER (Excel)
$ecPath = 'c:/xampp/htdocs/proyek_kadin/app/Http/Controllers/Umkm/ExportController.php';
$ecContent = file_get_contents($ecPath);
$ecOld = <<<'OED'
            foreach ($filteredLedger as $row) {
                $data[] = [
                    $row['tanggal'] ? Carbon::parse($row['tanggal'])->format('d/m/Y') : '-',
                    $row['jenis'] . ($row['ref_tipe'] ? ' - ' . $row['ref_tipe'] : ''),
                    $row['masuk_qty'] ?: '',
                    $row['masuk_harga'] ?: '',
                    $row['masuk_nilai'] ?: '',
                    $row['keluar_qty'] ?: '',
                    $row['keluar_harga'] ?: '',
                    $row['keluar_nilai'] ?: '',
                    $row['saldo_qty'],
                    $row['saldo_nilai']
                ];
            }
OED;
$ecNew = <<<'NED'
            foreach ($filteredLedger as $row) {
                $isMasuk = $row['jenis'] === 'MASUK';
                $masukNilai = $isMasuk ? ($row['masuk_qty'] * $row['masuk_harga']) : 0;
                
                $keluarQtyTotal = $isMasuk ? 0 : $row['keluar_qty'];
                $keluarDetails = $row['keluar_detail'] ?? [];
                
                $keluarHargaAvg = 0;
                $keluarNilaiTotal = 0;
                if (!$isMasuk && count($keluarDetails) > 0) {
                    foreach($keluarDetails as $det) {
                        $keluarNilaiTotal += ($det['qty'] * $det['harga']);
                    }
                    $keluarHargaAvg = $keluarQtyTotal > 0 ? ($keluarNilaiTotal / $keluarQtyTotal) : 0;
                }

                $data[] = [
                    $row['tanggal'] ? Carbon::parse($row['tanggal'])->format('d/m/Y') : '-',
                    $row['jenis'] . ($row['ref_tipe'] ? ' - ' . $row['ref_tipe'] : ''),
                    $isMasuk ? $row['masuk_qty'] : '',
                    $isMasuk ? $row['masuk_harga'] : '',
                    $isMasuk ? $masukNilai : '',
                    !$isMasuk ? $keluarQtyTotal : '',
                    !$isMasuk ? $keluarHargaAvg : '',
                    !$isMasuk ? $keluarNilaiTotal : '',
                    $row['saldo_qty'],
                    $row['saldo_nilai']
                ];
            }
NED;
$ecContent = str_replace($ecOld, $ecNew, $ecContent);
file_put_contents($ecPath, $ecContent);

// 2. FIX EXPORT PDF (PDF blade)
$pdfPath = 'c:/xampp/htdocs/proyek_kadin/resources/views/exports/pdf/kartu_stok.blade.php';
$pdfContent = file_get_contents($pdfPath);
$pdfOld = <<<'OED'
        @forelse($filteredLedger as $row)
            <tr>
                <td class="text-center">{{ $row['tanggal'] ? \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') : '-' }}</td>
                <td>{{ $row['jenis'] }} {!! $row['ref_tipe'] ? '<br><small>('.$row['ref_tipe'].')</small>' : '' !!}</td>
                
                {{-- Masuk --}}
                <td class="text-center">{{ $row['masuk_qty'] ?: '' }}</td>
                <td class="text-right">{{ $row['masuk_harga'] ? format_angka($row['masuk_harga']) : '' }}</td>
                <td class="text-right">{{ $row['masuk_nilai'] ? format_angka($row['masuk_nilai']) : '' }}</td>
                
                {{-- Keluar --}}
                <td class="text-center">{{ $row['keluar_qty'] ?: '' }}</td>
                <td class="text-right">{{ $row['keluar_harga'] ? format_angka($row['keluar_harga']) : '' }}</td>
                <td class="text-right">{{ $row['keluar_nilai'] ? format_angka($row['keluar_nilai']) : '' }}</td>
                
                {{-- Saldo --}}
                <td class="text-center text-bold bg-light">{{ $row['saldo_qty'] }}</td>
                <td style="font-size:11px;"  class="text-right text-bold bg-light text-end fw-medium">{{ format_angka($row['saldo_nilai']) }}</td>
            </tr>
OED;
$pdfNew = <<<'NED'
        @forelse($filteredLedger as $row)
            @php
                $isMasuk = $row['jenis'] === 'MASUK';
                $masukNilai = $isMasuk ? ($row['masuk_qty'] * $row['masuk_harga']) : 0;
                
                $keluarQtyTotal = $isMasuk ? 0 : $row['keluar_qty'];
                $keluarDetails = $row['keluar_detail'] ?? [];
                
                $keluarHargaAvg = 0;
                $keluarNilaiTotal = 0;
                if (!$isMasuk && count($keluarDetails) > 0) {
                    foreach($keluarDetails as $det) {
                        $keluarNilaiTotal += ($det['qty'] * $det['harga']);
                    }
                    $keluarHargaAvg = $keluarQtyTotal > 0 ? ($keluarNilaiTotal / $keluarQtyTotal) : 0;
                }
            @endphp
            <tr>
                <td class="text-center">{{ $row['tanggal'] ? \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') : '-' }}</td>
                <td>{{ $row['jenis'] }} {!! $row['ref_tipe'] ? '<br><small>('.$row['ref_tipe'].')</small>' : '' !!}</td>
                
                {{-- Masuk --}}
                <td class="text-center">{{ $isMasuk ? $row['masuk_qty'] : '' }}</td>
                <td class="text-right">{{ $isMasuk ? format_angka($row['masuk_harga']) : '' }}</td>
                <td class="text-right">{{ $isMasuk ? format_angka($masukNilai) : '' }}</td>
                
                {{-- Keluar --}}
                <td class="text-center">{{ !$isMasuk ? $keluarQtyTotal : '' }}</td>
                <td class="text-right">{{ !$isMasuk ? format_angka($keluarHargaAvg) : '' }}</td>
                <td class="text-right">{{ !$isMasuk ? format_angka($keluarNilaiTotal) : '' }}</td>
                
                {{-- Saldo --}}
                <td class="text-center text-bold bg-light">{{ $row['saldo_qty'] }}</td>
                <td style="font-size:11px;" class="text-right text-bold bg-light fw-medium">{{ format_angka($row['saldo_nilai']) }}</td>
            </tr>
NED;
$pdfContent = str_replace($pdfOld, $pdfNew, $pdfContent);
file_put_contents($pdfPath, $pdfContent);


// 3. FIX WEB UI KARTU STOK
$uiPath = 'c:/xampp/htdocs/proyek_kadin/resources/views/umkm/laporan/kartu_stok.blade.php';
$uiContent = file_get_contents($uiPath);

// The `bg-primary bg-opacity-10 text-primary` logic from my previous refactor looks terrible on dense columns, let me clean it.
$uiContent = str_replace(
    ['bg-primary bg-opacity-10 text-primary', 'bg-primary-subtle text-primary', 'bg-primary bg-opacity-25'], 
    ['fw-semibold text-dark rounded-end', 'fw-semibold text-dark', 'fw-semibold text-dark'], 
    $uiContent
);
$uiContent = str_replace(
    ['text-end fw-bold fw-semibold text-dark rounded-end', 'fw-bold fw-semibold text-dark rounded-end'],
    ['text-end fw-bold text-dark'],
    $uiContent
);
// In headers, "SISA SALDO" should just use standard table light.
$uiContent = str_replace('<th colspan="3" class="text-center fw-semibold text-dark rounded-end">SISA SALDO</th>', '<th colspan="3" class="text-center border-end">SISA SALDO</th>', $uiContent);
$uiContent = str_replace('class="text-end fw-bold fw-semibold text-dark rounded-end"', 'class="text-end fw-bold border-start border-light-subtle bg-light"', $uiContent);
$uiContent = str_replace('text-end fw-semibold text-dark rounded-end', 'text-end text-dark fw-bold bg-light', $uiContent);

file_put_contents($uiPath, $uiContent);

echo "SUCCESS";
