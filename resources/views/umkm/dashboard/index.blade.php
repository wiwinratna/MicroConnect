@extends('layouts.umkm')

@section('title', 'Dashboard UMKM')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><strong>Dashboard</strong> UMKM</h1>
        <p class="text-muted mb-0">Ringkasan penjualan, stok, dan performa usaha.</p>
    </div>
</div>

@if(isset($iuranBelumLunas))
<div class="alert alert-warning border-0 d-flex align-items-center gap-3 mb-4 rounded-4 shadow-sm" style="background: rgba(245, 158, 11, 0.1); color: var(--mn-warning-text); border-left: 5px solid var(--mn-warning-bg) !important;">
    <div class="flex-grow-1">
        <strong class="d-block mb-1 fs-5">⚠️ Perhatian: Iuran Aplikasi Menunggu Pembayaran.</strong> Segera bayar iuran bulan <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $iuranBelumLunas->periode)->isoFormat('MMMM Y') }}</strong> sebesar <strong>{{ rupiah($iuranBelumLunas->nominal) }}</strong>.
        @if($iuranBelumLunas->jatuh_tempo)
        <br>
        <span class="small mt-1 d-block opacity-75">Tenggat pembayaran: <strong>{{ $iuranBelumLunas->jatuh_tempo->isoFormat('D MMMM Y') }}</strong>. Mohon diselesaikan untuk menghindari ganguan akses.</span>
        @endif
    </div>
    <div>
        <a href="{{ route('umkm.iuran.index') }}" class="btn btn-warning fw-bold px-4 rounded-pill shadow-sm text-dark">Bayar Sekarang &rarr;</a>
    </div>
</div>
@endif

{{-- KPI CARDS --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Penjualan Hari Ini</div>
                <div class="h4 mb-0">{{ rupiah($penjualanHariIni) }}</div>
                <div class="text-muted small mt-1">{{ $trxHariIni }} transaksi</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Penjualan Bulan Ini</div>
                <div class="h4 mb-0">{{ rupiah($penjualanBulanIni) }}</div>
                <div class="text-muted small mt-1">{{ now()->format('F Y') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Jumlah Produk</div>
                <div class="h4 mb-0">{{ $totalProduk }}</div>
                <div class="text-muted small mt-1">item produk</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="text-muted small">Total Stok Produk</div>
                <div class="h4 mb-0">{{ format_angka($totalStokProduk) }}</div>
                <div class="text-muted small mt-1">akumulasi stok</div>
            </div>
        </div>
    </div>
</div>

{{-- GRAFIK --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="fw-semibold">Penjualan 7 Hari Terakhir</div>
                </div>
                <canvas id="chartSales7" height="110"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="fw-semibold mb-2">Top 5 Produk Terlaris (Bulan ini)</div>
                <canvas id="chartTopProduk" height="140"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- OPSIONAL: PRODUKSI --}}
<div class="row g-3 mb-3">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="fw-semibold mb-2">Produksi 7 Hari Terakhir (opsional)</div>
                <canvas id="chartProduksi7" height="90"></canvas>
                <div class="text-muted small mt-2">
                    Kalau grafik ini 0 semua, berarti nama tabel produksi kamu beda (aman, dashboard tetap jalan).
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ALERT STOK MENIPIS + TRANSAKSI TERAKHIR --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="fw-semibold mb-2">Stok Menipis</div>

                <div class="mb-3">
                    <div class="text-muted small mb-1">Bahan Baku</div>
                    @if($bahanMenipis->count())
                        <ul class="mb-0">
                            @foreach($bahanMenipis as $b)
                                <li>{{ $b->nama_bahan }} — <b>{{ format_angka($b->stok_awal) }}</b> {{ $b->satuan }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted small">Aman, belum ada bahan yang menipis.</div>
                    @endif
                </div>

                <div>
                    <div class="text-muted small mb-1">Produk</div>
                    @if($produkMenipis->count())
                        <ul class="mb-0">
                            @foreach($produkMenipis as $p)
                                <li>{{ $p->nama_produk }} — <b>{{ format_angka($p->stok) }}</b></li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted small">Aman, belum ada produk yang menipis.</div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="fw-semibold mb-2">Penjualan Terakhir</div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 table-hover table-borderless">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Kode</th>
                                <th>Pembeli</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penjualanTerakhir as $pj)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($pj->tanggal)->format('d/m/Y') }}</td>
                                    <td>{{ $pj->kode_penjualan }}</td>
                                    <td>{{ $pj->pembeli ?? '-' }}</td>
                                    <td  class="text-end fw-medium">{{ rupiah($pj->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted">Belum ada penjualan.</td>
                                </tr>
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
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const labels7 = @json($labels7);
    const data7   = @json($data7);

    const topLabels = @json($topLabels);
    const topData   = @json($topData);

    const produksiLabels7 = @json($produksiLabels7);
    const produksiData7   = @json($produksiData7);

    // Penjualan 7 hari
    new Chart(document.getElementById('chartSales7'), {
        type: 'line',
        data: {
            labels: labels7,
            datasets: [{
                label: 'Total Penjualan',
                data: data7,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Top produk
    new Chart(document.getElementById('chartTopProduk'), {
        type: 'bar',
        data: {
            labels: topLabels,
            datasets: [{
                label: 'Qty Terjual',
                data: topData
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Produksi 7 hari (opsional)
    new Chart(document.getElementById('chartProduksi7'), {
        type: 'bar',
        data: {
            labels: produksiLabels7,
            datasets: [{
                label: 'Qty Produksi',
                data: produksiData7
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
@endpush
