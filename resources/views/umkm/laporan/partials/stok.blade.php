            @if(feature_can('kartu_stok'))
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
            @endif
