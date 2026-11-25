            @if(feature_can('jurnal_umum'))
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
            @endif
