            @if(feature_can('laporan_penjualan'))
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
            @endif
