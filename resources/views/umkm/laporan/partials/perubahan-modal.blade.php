            @if(feature_can('perubahan_modal'))
            <div class="tab-pane fade" id="tab-modal">
                <div class="text-end mb-2">
                    <a href="{{ route('umkm.export.perubahan_modal', ['bulan' => $bulan, 'format' => 'pdf']) }}" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>
                </div>
                <div class="row justify-content-center"><div class="col-md-8">
                    <h5 class="text-center fw-bold mb-4">Laporan Perubahan Modal<br><small class="text-muted fw-normal">Periode: {{ $namaBulan }}</small></h5>
                    <table class="table table-borderless table-sm table-hover align-middle">
                        <tbody>
                            <tr><td>Modal Awal Periode</td><td  class="text-end fw-bold fw-medium">{{ rupiah($modalAwal) }}</td></tr>
                            <tr><td>Setoran Modal</td><td class="text-end {{ $setoranModal >= 0 ? 'text-success' : 'text-danger' }}">{{ $setoranModal >= 0 ? '+ ' : '- ' }} {{ rupiah(abs($setoranModal)) }}</td></tr>
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
            @endif
