            @if(feature_can('piutang'))
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
                                <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
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
            @endif
