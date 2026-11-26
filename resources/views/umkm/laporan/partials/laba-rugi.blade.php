            @if(feature_can('laba_rugi'))
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
                                @if($saldoAwalBahan != 0)
                                    <tr><td class="ps-4">Saldo Awal Bahan (dicatat periode ini)</td><td class="text-end fw-medium">{{ rupiah($saldoAwalBahan) }}</td></tr>
                                @endif
                                @if($bahanLangsung != 0)
                                    <tr><td class="ps-4">Beban Bahan Baku</td><td class="text-end fw-medium">{{ rupiah($bahanLangsung) }}</td></tr>
                                @endif
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
            @endif
