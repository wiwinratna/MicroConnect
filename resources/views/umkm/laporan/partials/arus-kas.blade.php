            @if(feature_can('arus_kas'))
            <div class="tab-pane fade" id="tab-kas">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0 fw-bold">Laporan Arus Kas<br><small class="text-muted fw-normal">Akun Kas (111) | Periode: {{ $namaBulan }}</small></h5>
                    <a href="{{ route('umkm.export.arus_kas', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                </div>

                <div class="row">
                    <div class="col-12">
                        {{-- Ringkasan Arus Kas --}}
                        <div class="card bg-light border-0 mb-4 pb-2">
                            <div class="card-body">
                                <h6 class="fw-bold border-bottom pb-2 mb-3">Ringkasan Mutasi Kas</h6>
                                <table class="table table-borderless table-sm align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="fw-semibold text-muted">Saldo Kas Awal Periode</td>
                                            <td class="text-end fw-bold">{{ rupiah($kasAwalPeriode) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-success pt-3">Total Kas Masuk</td>
                                            <td class="text-end text-success fw-bold pt-3">+ {{ rupiah($kasIn) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-semibold text-danger pb-3 border-bottom">Total Kas Keluar</td>
                                            <td class="text-end text-danger fw-bold pb-3 border-bottom">- {{ rupiah($kasOut) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold pt-3">Mutasi Kas Bersih (Kenaikan/Penurunan)</td>
                                            <td class="text-end fw-bold pt-3 {{ $netKas >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $netKas > 0 ? '+' : '' }}{{ rupiah($netKas) }}
                                            </td>
                                        </tr>
                                        <tr class="border-top border-2 border-primary mt-2">
                                            <td class="fw-bold fs-5 text-primary pt-3">Saldo Kas Akhir Periode</td>
                                            <td class="text-end fw-bold fs-5 text-primary pt-3">{{ rupiah($kasAkhirPeriode) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Rincian Arus Kas Berdasarkan Aktivitas --}}
                        <div class="mb-3 mt-4 d-flex align-items-center">
                            <i data-feather="pie-chart" class="text-primary me-2"></i>
                            <h6 class="fw-bold mb-0">Rincian Arus Kas Berdasarkan Sumber</h6>
                        </div>
                        
                        @foreach($arusKasGrup as $namaGrup => $grup)
                            @if(count($grup['masuk']) > 0 || count($grup['keluar']) > 0)
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom fw-bold text-primary">
                                    {{ $namaGrup }}
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-hover table-borderless table-sm align-middle mb-0">
                                        <tbody>
                                            {{-- Arus Kas Masuk --}}
                                            @if(count($grup['masuk']) > 0)
                                            <tr><td colspan="2" class="fw-semibold text-success bg-light ps-3 py-2 small">Kas Masuk</td></tr>
                                            @foreach($grup['masuk'] as $sumber => $nom)
                                                <tr><td class="ps-4 text-muted">{{ $sumber }}</td><td class="text-end pe-3 fw-medium text-success">+ {{ rupiah($nom) }}</td></tr>
                                            @endforeach
                                            @endif
                                            
                                            {{-- Arus Kas Keluar --}}
                                            @if(count($grup['keluar']) > 0)
                                            <tr><td colspan="2" class="fw-semibold text-danger bg-light ps-3 py-2 small">Kas Keluar</td></tr>
                                            @foreach($grup['keluar'] as $sumber => $nom)
                                                <tr><td class="ps-4 text-muted">{{ $sumber }}</td><td class="text-end pe-3 fw-medium text-danger">- {{ rupiah($nom) }}</td></tr>
                                            @endforeach
                                            @endif

                                            {{-- Total Per Aktivitas --}}
                                            @php $netGrup = $grup['total_masuk'] - $grup['total_keluar']; @endphp
                                            <tr class="border-top">
                                                <td class="fw-bold bg-light ps-3 pb-2 pt-2">Kas Bersih dari {{ $namaGrup }}</td>
                                                <td class="text-end pe-3 fw-bold bg-light pt-2 pb-2 {{ $netGrup >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $netGrup > 0 ? '+' : '' }}{{ rupiah($netGrup) }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        @endforeach

                        {{-- Tabel Riwayat dihapus atas permintaan user --}}

                    </div>
                </div>
            </div>
            @endif
