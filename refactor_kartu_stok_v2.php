<?php
$path = 'c:\xampp\htdocs\proyek_kadin\resources\views\umkm\laporan\kartu_stok.blade.php';
$content = file_get_contents($path);

// Use a regex to extract everything from `<table ` to `</table>`
$pattern = '/<table\b[^>]*>.*?<\/table>/s';

$replacement = <<<'EOD'
<table class="table table-hover table-bordered border-light align-middle mb-0" style="font-size: 0.85rem;">
            <thead class="table-light text-secondary text-uppercase" style="letter-spacing: 0.5px;">
                <tr>
                    <th rowspan="2" class="align-middle text-start border-end" style="min-width:100px;">TANGGAL</th>
                    <th rowspan="2" class="align-middle text-start border-end" style="min-width:200px;">KETERANGAN</th>
                    <th colspan="3" class="text-center border-end">MASUK (IN)</th>
                    <th colspan="3" class="text-center border-end">KELUAR (OUT)</th>
                    <th colspan="3" class="text-center bg-primary bg-opacity-10 text-primary">SISA SALDO</th>
                </tr>
                <tr>
                    {{-- MASUK --}}
                    <th class="text-end fw-semibold" style="width:70px;">QTY</th>
                    <th class="text-end fw-semibold">HARGA</th>
                    <th class="text-end fw-semibold border-end">NILAI</th>
                    {{-- KELUAR --}}
                    <th class="text-end fw-semibold" style="width:70px;">QTY</th>
                    <th class="text-end fw-semibold">HARGA</th>
                    <th class="text-end fw-semibold border-end">NILAI</th>
                    {{-- SALDO --}}
                    <th class="text-end fw-bold bg-primary bg-opacity-10 text-primary" style="width:70px;">QTY</th>
                    <th class="text-end fw-bold bg-primary bg-opacity-10 text-primary">HARGA</th>
                    <th class="text-end fw-bold bg-primary bg-opacity-10 text-primary">NILAI</th>
                </tr>
            </thead>
            <tbody>
                {{-- BARIS SALDO AWAL --}}
                <tr class="fw-bold bg-white">
                    <td class="text-start text-dark border-end" colspan="2">SALDO AWAL — Per 1 {{ $namaBulan }}</td>
                    <td colspan="3" class="text-muted text-center border-end">—</td>
                    <td colspan="3" class="text-muted text-center border-end">—</td>
                    <td class="text-end fw-bold bg-primary bg-opacity-10 text-primary">{{ format_angka($saldoAwalQty) }}</td>
                    <td class="text-end fw-bold bg-primary bg-opacity-10 text-primary">
                        @if($saldoAwalQty > 0)
                            {{ rupiah($saldoAwalNilai / $saldoAwalQty) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-end fw-bold bg-primary bg-opacity-10 text-primary">{{ rupiah($saldoAwalNilai) }}</td>
                </tr>

                {{-- LOOPING LEDGER BULAN INI --}}
                @forelse($ledger as $row)
                    @php
                        $isMasuk = $row['jenis'] === 'MASUK';
                        $tgl = Carbon::parse($row['tanggal'])->format('d/m/Y');
                        $refTipe = strtoupper($row['ref_tipe']);
                        $refId   = $row['ref_id'];

                        // Label keterangan yang informatif
                        $ketLabel = match(strtolower($row['ref_tipe'])) {
                            'saldo_awal'  => 'Saldo Awal',
                            'pembelian'   => 'Pembelian',
                            'penjualan'   => 'Penjualan',
                            'produksi'    => 'Produksi',
                            default       => $refTipe,
                        };

                        // Untuk MASUK: nilai = qty × harga
                        $masukNilai = $isMasuk ? ($row['masuk_qty'] * $row['masuk_harga']) : 0;

                        // Untuk KELUAR: hitung dari detail breakdown (FIFO/LIFO) atau langsung (Average)
                        $keluarDetails = $row['keluar_detail'] ?? [];
                        $keluarQtyTotal = $isMasuk ? 0 : $row['keluar_qty'];

                        // Harga avg saldo
                        $saldoHarga = $row['saldo_qty'] > 0 ? ($row['saldo_nilai'] / $row['saldo_qty']) : 0;

                        // Jumlah baris yang dibutuhkan untuk transaksi KELUAR multi-layer
                        $numRows = (!$isMasuk && count($keluarDetails) > 1) ? count($keluarDetails) : 1;
                    @endphp

                    @if($isMasuk)
                    {{-- BARIS MASUK: satu baris saja --}}
                    <tr>
                        <td class="text-start text-muted border-end">{{ $tgl }}</td>
                        <td class="border-end text-start">
                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill me-1" style="font-size:10px; padding: 3px 8px;">IN</span>
                            <span class="fw-medium text-dark">{{ $ketLabel }}</span>
                            <br><small class="text-muted" style="font-size:0.75rem;">{{ $refTipe }} #{{ $refId }}</small>
                        </td>
                        {{-- MASUK --}}
                        <td class="text-end text-success fw-medium">{{ format_angka($row['masuk_qty']) }}</td>
                        <td class="text-end text-success">{{ format_angka($row['masuk_harga']) }}</td>
                        <td class="text-end text-success fw-semibold border-end">{{ format_angka($masukNilai) }}</td>
                        {{-- KELUAR (kosong) --}}
                        <td class="text-center text-muted" colspan="3" class="border-end">—</td>
                        {{-- SALDO --}}
                        <td class="text-end bg-primary bg-opacity-10 text-primary fw-bold">{{ format_angka($row['saldo_qty']) }}</td>
                        <td class="text-end bg-primary bg-opacity-10 text-primary">
                            {{ format_angka($saldoHarga) }}
                            @if($methodStr === 'AVERAGE')
                                <br><span class="opacity-50" style="font-size:0.70rem;">(Avg)</span>
                            @endif
                        </td>
                        <td class="text-end bg-primary bg-opacity-10 text-primary fw-bold">{{ format_angka($row['saldo_nilai']) }}</td>
                    </tr>

                    @elseif(count($keluarDetails) <= 1)
                    {{-- BARIS KELUAR: satu layer / Average --}}
                    @php
                        $det = $keluarDetails[0] ?? null;
                        $keluarHarga = $det ? $det['harga'] : 0;
                        $keluarNilai = $keluarQtyTotal * $keluarHarga;
                    @endphp
                    <tr>
                        <td class="text-start text-muted border-end">{{ $tgl }}</td>
                        <td class="border-end text-start">
                            <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill me-1" style="font-size:10px; padding: 3px 8px;">OUT</span>
                            <span class="fw-medium text-dark">{{ $ketLabel }}</span>
                            <br><small class="text-muted" style="font-size:0.75rem;">{{ $refTipe }} #{{ $refId }}</small>
                            @if($det && isset($det['is_avg']) && $det['is_avg'])
                                <br><small class="text-muted" style="font-size:0.70rem;">(Weighted Average)</small>
                            @endif
                        </td>
                        {{-- MASUK (kosong) --}}
                        <td class="text-center text-muted border-end" colspan="3">—</td>
                        {{-- KELUAR --}}
                        <td class="text-end text-danger fw-medium">{{ format_angka($keluarQtyTotal) }}</td>
                        <td class="text-end text-danger">{{ $det ? format_angka($keluarHarga) : '—' }}</td>
                        <td class="text-end text-danger fw-semibold border-end">{{ $det ? format_angka($keluarNilai) : '—' }}</td>
                        {{-- SALDO --}}
                        <td class="text-end bg-primary bg-opacity-10 text-primary fw-bold">{{ format_angka($row['saldo_qty']) }}</td>
                        <td class="text-end bg-primary bg-opacity-10 text-primary">
                            {{ format_angka($saldoHarga) }}
                            @if($methodStr === 'AVERAGE')
                                <br><span class="opacity-50" style="font-size:0.70rem;">(Avg)</span>
                            @endif
                        </td>
                        <td class="text-end bg-primary bg-opacity-10 text-primary fw-bold">{{ format_angka($row['saldo_nilai']) }}</td>
                    </tr>

                    @else
                    {{-- BARIS KELUAR MULTI-LAYER (FIFO/LIFO): setiap layer = satu baris --}}
                    @foreach($keluarDetails as $li => $det)
                        @php
                            $detQty   = $det['qty'];
                            $detHarga = $det['harga'];
                            $detNilai = $detQty * $detHarga;
                            $isFirst  = ($li === 0);
                            $isLast   = ($li === count($keluarDetails) - 1);
                        @endphp
                        <tr>
                            @if($isFirst)
                            <td class="text-start text-muted border-end" rowspan="{{ count($keluarDetails) }}">{{ $tgl }}</td>
                            <td class="border-end text-start" rowspan="{{ count($keluarDetails) }}">
                                <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 rounded-pill me-1" style="font-size:10px; padding: 3px 8px;">OUT</span>
                                <span class="fw-medium text-dark">{{ $ketLabel }}</span>
                                <br><small class="text-muted" style="font-size:0.75rem;">{{ $refTipe }} #{{ $refId }}</small>
                                <br><span class="badge bg-light text-dark fw-normal mt-1 border border-secondary" style="font-size:0.70rem;">{{ count($keluarDetails) }} layer {{ $methodStr }}</span>
                            </td>
                            @endif
                            
                            {{-- MASUK (kosong) --}}
                            <td class="text-center text-muted border-end" colspan="3">—</td>
                            
                            {{-- KELUAR: detail tiap layer --}}
                            <td class="text-end text-danger fw-medium">{{ format_angka($detQty) }}</td>
                            <td class="text-end text-danger">
                                {{ format_angka($detHarga) }}
                                @if(isset($det['batch']))
                                    <br><small class="text-muted" style="font-size:0.70rem; opacity: 0.7;">
                                        Batch: {{ $det['batch'] }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-end text-danger fw-semibold border-end">{{ format_angka($detNilai) }}</td>
                            
                            {{-- SALDO: hanya tampil di baris terakhir layer --}}
                            @if($isFirst)
                                <td rowspan="{{ count($keluarDetails) }}" class="text-end bg-primary bg-opacity-10 text-primary fw-bold">{{ format_angka($row['saldo_qty']) }}</td>
                                <td rowspan="{{ count($keluarDetails) }}" class="text-end bg-primary bg-opacity-10 text-primary">{{ format_angka($saldoHarga) }}</td>
                                <td rowspan="{{ count($keluarDetails) }}" class="text-end bg-primary bg-opacity-10 text-primary fw-bold">{{ format_angka($row['saldo_nilai']) }}</td>
                            @endif
                        </tr>
                    @endforeach
                    @endif

                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-5 border-0">
                            <i data-feather="folder-minus" style="width: 40px; height: 40px; opacity: 0.3;" class="mb-2"></i>
                            <br>
                            Tidak ada pergerakan / transaksi untuk bahan baku ini di bulan <strong>{{ $namaBulan }}</strong>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
EOD;

$content = preg_replace($pattern, $replacement, $content);

file_put_contents($path, $content);
echo "Berhasil replace tabel kartu stok!\n";
