            @if(feature_can('laporan_pembelian'))
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
                                <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
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
            @endif
