@extends('layouts.umkm')

@section('title', 'Laporan Keuangan')

@section('content')
@php
    use Carbon\Carbon;
    $namaBulan = Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><strong>Laporan</strong> Keuangan</h1>
        <p class="text-muted mb-0">Ringkasan pencatatan & laporan keuangan UMKM periode {{ $namaBulan }}.</p>
    </div>
    
    <form method="GET" action="{{ route('umkm.laporan.index') }}" class="d-flex gap-2">
        <input type="month" name="bulan" class="form-control" value="{{ $bulan }}">
        <button type="submit" class="btn btn-primary">Terapkan</button>
    </form>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        {{-- ===================== TAB NAV ===================== --}}
        <ul class="nav nav-tabs border-bottom-0 gap-1 mb-3" id="laporanTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-jurnal">Jurnal Umum</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bukubesar">Buku Besar</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-labarugi">Laba Rugi</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-modal">Perubahan Modal</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-kas">Arus Kas</button></li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Persediaan Barang</a>
                <ul class="dropdown-menu shadow-sm border-0">
                    <li><button class="dropdown-item" data-bs-toggle="tab" data-bs-target="#tab-stok">Rekap Mutasi Stok</button></li>
                    <li><a class="dropdown-item text-primary fw-semibold" href="{{ route('umkm.laporan.kartu_stok') }}">Lihat Kartu Stok (FIFO/LIFO/Avg)</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Lainnya</a>
                <ul class="dropdown-menu shadow-sm border-0">
                    <li><button class="dropdown-item" data-bs-toggle="tab" data-bs-target="#tab-beli">Laporan Pembelian</button></li>
                    <li><button class="dropdown-item" data-bs-toggle="tab" data-bs-target="#tab-jual">Laporan Penjualan</button></li>
                    <li><button class="dropdown-item" data-bs-toggle="tab" data-bs-target="#tab-piutang">Laporan Piutang</button></li>
                </ul>
            </li>
        </ul>

        {{-- ===================== TAB CONTENT ===================== --}}
        <div class="tab-content" id="laporanTabContent">

            {{-- 1. JURNAL UMUM --}}
            <div class="tab-pane fade show active" id="tab-jurnal">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Rincian Jurnal Umum</h5>
                    <div class="gap-2 d-flex">
                        <a href="{{ route('umkm.export.jurnal_umum', ['bulan' => $bulan, 'format' => 'excel']) }}" class="btn btn-sm btn-action btn-action-excel" title="Unduh Excel"><i data-feather="file-text"></i> Excel</a>
                        <a href="{{ route('umkm.export.jurnal_umum', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-borderless">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Kode Akun</th>
                                <th>Nama Akun</th>
                                <th>Keterangan</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jurnal as $j)
                            <tr>
                                <td>{{ Carbon::parse($j->tanggal)->format('d/m/Y') }}</td>
                                <td><span class="badge bg-secondary">{{ $j->kode_akun }}</span></td>
                                <td>{{ $j->nama_akun }}</td>
                                <td class="small text-muted">{{ $j->keterangan }}</td>
                                <td class="text-end text-success">{{ $j->debit > 0 ? format_angka($j->debit) : '-' }}</td>
                                <td class="text-end text-danger">{{ $j->kredit > 0 ? format_angka($j->kredit) : '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada jurnal bulan ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. BUKU BESAR --}}
            <div class="tab-pane fade" id="tab-bukubesar">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Rincian Buku Besar</h5>
                    <a href="{{ route('umkm.export.buku_besar', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                </div>
                <div class="row">
                    @forelse($bukuBesar as $kode => $data)
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">{{ $kode }} - {{ $data['nama_akun'] }}</h6>
                                <span class="badge bg-primary">Saldo ({{ $data['posisi'] }}): {{ rupiah($data['saldo_akhir']) }}</span>
                            </div>
                            <div class="card-body p-0" style="max-height:300px; overflow-y:auto;">
                                <table class="table table-sm mb-0 bg-white table-hover table-borderless align-middle" style="font-size:0.85rem">
                                    <thead class="table-light position-sticky top-0">
                                        <tr>
                                            <th>Tgl</th>
                                            <th>Ket</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Kredit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($data['items'] as $item)
                                        <tr>
                                            <td>{{ Carbon::parse($item->tanggal)->format('d/m') }}</td>
                                            <td class="text-truncate" style="max-width:150px;" title="{{ $item->keterangan }}">{{ $item->keterangan }}</td>
                                            <td class="text-end">{{ $item->debit > 0 ? format_angka($item->debit) : '-' }}</td>
                                            <td class="text-end">{{ $item->kredit > 0 ? format_angka($item->kredit) : '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light fw-bold position-sticky bottom-0">
                                        <tr>
                                            <td colspan="2" class="text-end">Total Mutasi:</td>
                                            <td  class="text-end fw-medium">{{ format_angka($data['total_debit']) }}</td>
                                            <td  class="text-end fw-medium">{{ format_angka($data['total_kredit']) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center text-muted py-4">Belum ada buku besar bulan ini.</div>
                    @endforelse
                </div>
            </div>

            {{-- 3. LABA RUGI --}}
            <div class="tab-pane fade" id="tab-labarugi">
                <div class="text-end mb-2">
                    <a href="{{ route('umkm.export.laba_rugi', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                </div>
                <div class="row justify-content-center"><div class="col-md-8">
                    <h5 class="text-center fw-bold mb-4">Laporan Laba Rugi<br><small class="text-muted fw-normal">Periode: {{ $namaBulan }}</small></h5>
                    <table class="table table-borderless table-sm table-hover align-middle">
                        <tbody>
                            {{-- Pendapatan --}}
                            <tr><td colspan="2" class="fw-bold text-primary">PENDAPATAN USAHA</td></tr>
                            @foreach($pendapatan->groupBy('nama_akun') as $nama => $items)
                                <tr><td class="ps-4">{{ $nama }}</td><td  class="text-end fw-medium">{{ rupiah($items->sum('kredit') - $items->sum('debit')) }}</td></tr>
                            @endforeach
                            <tr class="fw-bold"><td class="text-end">Total Pendapatan:</td><td  class="text-end border-top fw-medium">{{ rupiah($totalPendapatan) }}</td></tr>
                            <tr><td colspan="2">&nbsp;</td></tr>

                            {{-- HPP --}}
                            <tr><td colspan="2" class="fw-bold text-primary">HARGA POKOK PENJUALAN (HPP) / BIAYA PRODUK</td></tr>
                            @if($isPeriodik)
                                <tr><td class="ps-4">Persediaan Awal Bahan Baku</td><td  class="text-end fw-medium">{{ rupiah($persediaanAwal) }}</td></tr>
                                <tr><td class="ps-4">Pembelian Bahan</td><td  class="text-end fw-medium">{{ rupiah($totalPembelianBulanIni) }}</td></tr>
                                <tr><td class="ps-4">Persediaan Akhir Bahan Baku</td><td class="text-end text-danger">({{ rupiah($persediaanAkhir) }})</td></tr>
                            @else
                                @foreach($hpp->groupBy('nama_akun') as $nama => $items)
                                    <tr><td class="ps-4">{{ $nama }}</td><td  class="text-end fw-medium">{{ rupiah($items->sum('debit') - $items->sum('kredit')) }}</td></tr>
                                @endforeach
                            @endif
                            <tr class="fw-bold"><td class="text-end">Total HPP:</td><td  class="text-end border-top fw-medium">{{ rupiah($totalHpp) }}</td></tr>
                            <tr><td colspan="2">&nbsp;</td></tr>
                            <tr class="fw-bold {{ $labaKotor >= 0 ? 'text-success' : 'text-danger' }}">
                                <td>LABA KOTOR:</td><td  class="text-end border-top border-bottom fw-medium">{{ rupiah($labaKotor) }}</td>
                            </tr>
                            <tr><td colspan="2">&nbsp;</td></tr>

                            {{-- Beban Operasional --}}
                            <tr><td colspan="2" class="fw-bold text-primary">BEBAN OPERASIONAL</td></tr>
                            @foreach($bebanDetail as $b)
                                <tr><td class="ps-4">{{ $b['nama'] }}</td><td  class="text-end fw-medium">{{ rupiah($b['total']) }}</td></tr>
                            @endforeach
                            <tr class="fw-bold"><td class="text-end">Total Beban Operasional:</td><td  class="text-end border-top fw-medium">{{ rupiah($totalBeban) }}</td></tr>
                            <tr><td colspan="2">&nbsp;</td></tr>

                            <tr class="fw-bold fs-5 {{ $labaBersih >= 0 ? 'text-success' : 'text-danger' }}">
                                <td>LABA BERSIH:</td><td  class="text-end border-top border-bottom fw-medium">{{ rupiah($labaBersih) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div></div>
            </div>

            {{-- 4. PERUBAHAN MODAL --}}
            <div class="tab-pane fade" id="tab-modal">
                <div class="text-end mb-2">
                    <a href="{{ route('umkm.export.perubahan_modal', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                </div>
                <div class="row justify-content-center"><div class="col-md-8">
                    <h5 class="text-center fw-bold mb-4">Laporan Perubahan Modal<br><small class="text-muted fw-normal">Periode: {{ $namaBulan }}</small></h5>
                    <table class="table table-borderless table-sm table-hover align-middle">
                        <tbody>
                            <tr><td>Modal Awal Periode</td><td  class="text-end fw-bold fw-medium">{{ rupiah($modalAwal) }}</td></tr>
                            <tr>
                                <td>{{ $labaBersih >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}</td>
                                <td class="text-end {{ $labaBersih>= 0 ? 'text-success' : 'text-danger' }}">{{ $labaBersih >= 0 ? '+ ' : '- ' }} {{ rupiah(abs($labaBersih)) }}</td>
                            </tr>
                            <tr><td>Prive (Pengambilan)</td><td class="text-end text-danger">- {{ rupiah($prive) }}</td></tr>
                            <tr class="border-top border-2">
                                <td class="fw-bold fs-5 text-primary">Modal Akhir Periode</td>
                                <td  class="text-end fw-bold fs-5 text-primary fw-medium">{{ rupiah($modalAkhir) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div></div>
            </div>

            {{-- 5. ARUS KAS --}}
            <div class="tab-pane fade" id="tab-kas">
                <div class="text-end mb-2">
                    <a href="{{ route('umkm.export.arus_kas', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                </div>
                <div class="row justify-content-center"><div class="col-md-8">
                    <h5 class="text-center fw-bold mb-4">Arus Kas Sederhana<br><small class="text-muted fw-normal">Akun Kas (111) | Periode: {{ $namaBulan }}</small></h5>
                    <table class="table table-borderless table-sm table-hover align-middle">
                        <tbody>
                            <tr><td>Saldo Kas Awal Periode</td><td  class="text-end fw-bold fw-medium">{{ rupiah($kasAwalPeriode) }}</td></tr>
                            <tr><td>Total Uang Masuk (Debit)</td><td class="text-end text-success">+ {{ rupiah($kasIn) }}</td></tr>
                            <tr><td>Total Uang Keluar (Kredit)</td><td class="text-end text-danger">- {{ rupiah($kasOut) }}</td></tr>
                            <tr class="fw-bold"><td class="text-end border-top">Mutasi Kas Bersih:</td><td class="text-end border-top {{ $netKas>= 0 ? 'text-success' : 'text-danger' }}">{{ rupiah($netKas) }}</td></tr>
                            <tr class="border-top border-2">
                                <td class="fw-bold fs-5 text-primary">Saldo Kas Akhir Periode</td>
                                <td  class="text-end fw-bold fs-5 text-primary fw-medium">{{ rupiah($kasAkhirPeriode) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div></div>
            </div>

            {{-- 6. STOK PERSEDIAAN --}}
            <div class="tab-pane fade" id="tab-stok">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Mutasi Stok Bahan Baku</h5>
                    <div class="gap-2 d-flex">
                        <a href="{{ route('umkm.export.rekap_stok', ['bulan' => $bulan, 'format' => 'excel']) }}" class="btn btn-sm btn-action btn-action-excel" title="Unduh Excel"><i data-feather="file-text"></i> Excel</a>
                        <a href="{{ route('umkm.export.rekap_stok', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                        <a href="{{ route('umkm.laporan.kartu_stok', ['bulan' => $bulan]) }}" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="align-middle" data-feather="list"></i> Lihat Detail Kartu Stok
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-borderless">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Bahan Baku</th>
                                <th class="text-end">Kas Masuk (Beli)</th>
                                <th class="text-end">Nilai Masuk (Rp)</th>
                                <th class="text-end">Kas Keluar (Jual)</th>
                                <th class="text-end">Mutasi Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stokPerBahan as $stok)
                            <tr>
                                <td class="fw-semibold">{{ $stok['nama_bahan'] }}</td>
                                <td class="text-end text-success">+ {{ $stok['masuk'] }} {{ $stok['satuan'] }}</td>
                                <td  class="text-end fw-medium">{{ rupiah($stok['nilai_masuk']) }}</td>
                                <td class="text-end text-danger">- {{ $stok['keluar'] }} {{ $stok['satuan'] }}</td>
                                <td class="text-end fw-bold {{ $stok['saldo']>= 0 ? 'text-success' : 'text-warning' }}">{{ $stok['saldo'] }} {{ $stok['satuan'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada pergerakan stok bahan baku bulan ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 7. PEMBELIAN --}}
            <div class="tab-pane fade" id="tab-beli">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Riwayat Pembelian Bahan Baku</h5>
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="mb-0 fw-bold text-danger">Total: {{ rupiah($totalPembelian) }}</h5>
                        <div class="border-start ps-3 gap-2 d-flex">
                            <a href="{{ route('umkm.export.laporan_pembelian', ['bulan' => $bulan, 'format' => 'excel']) }}" class="btn btn-sm btn-action btn-action-excel" title="Unduh Excel"><i data-feather="file-text"></i> Excel</a>
                            <a href="{{ route('umkm.export.laporan_pembelian', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle table-hover table-borderless">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th><th>Bahan Dibeli</th><th>Supplier/Ket</th><th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pembelianList as $p)
                            <tr>
                                <td>{{ Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                                <td class="small text-muted">
                                    @foreach($p->details as $d) {{ $d->bahan->nama_bahan ?? '?' }} ({{ $d->qty }}), @endforeach
                                </td>
                                <td>{{ $p->supplier ?? '-' }} <br><small class="text-muted">{{ $p->keterangan }}</small></td>
                                <td  class="text-end fw-semibold fw-medium">{{ rupiah($p->total) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada pembelian.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 8. PENJUALAN --}}
            <div class="tab-pane fade" id="tab-jual">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Riwayat Penjualan Produk</h5>
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="mb-0 fw-bold text-success">Total: {{ rupiah($totalPenjualan) }}</h5>
                        <div class="border-start ps-3 gap-2 d-flex">
                            <a href="{{ route('umkm.export.laporan_penjualan', ['bulan' => $bulan, 'format' => 'excel']) }}" class="btn btn-sm btn-action btn-action-excel" title="Unduh Excel"><i data-feather="file-text"></i> Excel</a>
                            <a href="{{ route('umkm.export.laporan_penjualan', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle table-hover table-borderless">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th><th>Pembeli/Ket</th><th>Item Terjual</th><th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penjualanList as $p)
                            <tr>
                                <td>{{ Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $p->pelanggan->nama_pelanggan ?? 'Umum/Kas' }}</td>
                                <td class="small text-muted">
                                    @foreach($p->details as $d) {{ $d->produk->nama_produk ?? '?' }} ({{ $d->qty }}), @endforeach
                                </td>
                                <td  class="text-end fw-semibold fw-medium">{{ rupiah($p->total) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada penjualan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 9. PIUTANG --}}
            <div class="tab-pane fade" id="tab-piutang">
                <div class="row mb-4">
                    <div class="col-md-4"><div class="card bg-light border-0"><div class="card-body">
                        <div class="small text-muted">Total Pembayaran Piutang (Bln Ini)</div>
                        <div class="h4 mb-0 fw-bold text-success">{{ rupiah($totalPembayaranBulanIni) }}</div>
                    </div></div></div>
                    <div class="col-md-4"><div class="card bg-light border-0"><div class="card-body">
                        <div class="small text-muted">Total Seluruh Piutang Beredar</div>
                        <div class="h4 mb-0 fw-bold">{{ rupiah($totalPiutang) }}</div>
                    </div></div></div>
                    <div class="col-md-4"><div class="card bg-light border-0"><div class="card-body">
                        <div class="small text-muted">Sisa Piutang Belum Dibayar</div>
                        <div class="h4 mb-0 fw-bold text-danger">{{ rupiah($totalSisaPiutang) }}</div>
                    </div></div></div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Piutang Pelanggan</h6>
                    <a href="{{ route('umkm.export.laporan_piutang', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-borderless align-middle">
                        <thead class="table-light">
                            <tr><th>Tanggal Catat</th><th>Pelanggan</th><th class="text-end">Nominal Awal</th><th class="text-end">Telah Dibayar</th><th class="text-end">Sisa</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse($piutangList as $p)
                            <tr>
                                <td>{{ Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $p->pelanggan->nama_pelanggan ?? '-' }}</td>
                                <td  class="text-end fw-medium">{{ rupiah($p->nominal_awal) }}</td>
                                <td  class="text-end text-success fw-medium">{{ rupiah($p->sudah_dibayar) }}</td>
                                <td  class="text-end text-danger fw-semibold fw-medium">{{ rupiah($p->sisa) }}</td>
                                <td>
                                    @if($p->status === 'lunas')<span class="badge bg-success">Lunas</span>
                                    @else<span class="badge bg-warning text-dark">Belum Lunas</span>@endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">Tidak ada data piutang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
.nav-tabs .nav-link { border-radius: 8px 8px 0 0; color: #495057; font-weight: 500; }
.nav-tabs .nav-link.active { font-weight: 700; color: #0d6efd; border-bottom-color: transparent; }
.table-sm td, .table-sm th { padding: 0.5rem; }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // restore last active tab from hash
    let hash = window.location.hash;
    if (hash) {
        let triggerEl = document.querySelector(`.nav-tabs button[data-bs-target="${hash}"]`);
        if (triggerEl) new bootstrap.Tab(triggerEl).show();
    }
    
    // update hash on tab change
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', e => {
            history.replaceState(null, null, e.target.getAttribute('data-bs-target'));
        });
    });
});
</script>
@endpush
