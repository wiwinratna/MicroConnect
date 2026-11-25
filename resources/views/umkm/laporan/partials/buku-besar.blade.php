            @if(feature_can('buku_besar'))
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
            @endif
