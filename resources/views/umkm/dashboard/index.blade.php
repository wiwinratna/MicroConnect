@extends('layouts.umkm')

@section('title', 'Dashboard UMKM')

@push('styles')
<style>
    /* Dashboard Compact Enhancements */
    h1.h3 { font-size: 1.35rem !important; font-weight: 700; letter-spacing: -0.02em; }
    .card { box-shadow: 0 4px 12px rgba(0,0,0,0.02) !important; border-radius: 12px !important; margin-bottom: 1rem !important; }
    .card-body { padding: 1.25rem 1.5rem !important; }
    .table > :not(caption) > * > * { padding: 0.5rem 0.5rem !important; font-size: 0.85rem; }
    .table th { font-weight: 600; font-size: 0.75rem !important; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1 text-dark"><strong>Dashboard</strong> UMKM</h1>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Ringkasan penjualan, stok, dan performa usaha.</p>
    </div>
</div>

@if(isset($iuranBelumLunas))
<div class="alert alert-warning border-0 d-flex align-items-center gap-3 mb-3 rounded-3 shadow-sm" style="background: rgba(245, 158, 11, 0.1); color: var(--mn-warning-text); border-left: 4px solid var(--mn-warning-bg) !important; padding: 1rem 1.25rem;">
    <div class="flex-grow-1">
        <strong class="d-block mb-1" style="font-size: 0.95rem;">⚠️ Perhatian: Iuran Aplikasi Menunggu Pembayaran.</strong>
        <span style="font-size: 0.85rem;">Segera bayar iuran bulan <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $iuranBelumLunas->periode)->isoFormat('MMMM Y') }}</strong> sebesar <strong>{{ rupiah($iuranBelumLunas->nominal) }}</strong>.</span>
        @if($iuranBelumLunas->jatuh_tempo)
        <br>
        <span class="mt-1 d-block opacity-75" style="font-size: 0.8rem;">Tenggat pembayaran: <strong>{{ $iuranBelumLunas->jatuh_tempo->isoFormat('D MMMM Y') }}</strong>. Mohon diselesaikan untuk menghindari ganguan akses.</span>
        @endif
    </div>
    <div>
        <a href="{{ route('umkm.iuran.index') }}" class="btn btn-warning fw-bold px-3 py-1 rounded-2 shadow-sm text-dark" style="font-size: 0.85rem;">Bayar Sekarang &rarr;</a>
    </div>
</div>
@endif

{{-- KPI CARDS --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted fw-semibold mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Penjualan Hari Ini</div>
                <div class="fs-4 fw-bold text-dark mb-0 lh-1">{{ rupiah($penjualanHariIni) }}</div>
                <div class="text-muted mt-2" style="font-size: 0.75rem;">{{ $trxHariIni }} transaksi</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted fw-semibold mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Penjualan Bulan Ini</div>
                <div class="fs-4 fw-bold text-dark mb-0 lh-1">{{ rupiah($penjualanBulanIni) }}</div>
                <div class="text-muted mt-2" style="font-size: 0.75rem;">{{ now()->format('F Y') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted fw-semibold mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Jumlah Produk</div>
                <div class="fs-4 fw-bold text-dark mb-0 lh-1">{{ $totalProduk }}</div>
                <div class="text-muted mt-2" style="font-size: 0.75rem;">item produk</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted fw-semibold mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Stok Produk</div>
                <div class="fs-4 fw-bold text-dark mb-0 lh-1">{{ format_angka($totalStokProduk) }}</div>
                <div class="text-muted mt-2" style="font-size: 0.75rem;">akumulasi stok</div>
            </div>
        </div>
    </div>
</div>

{{-- GRAFIK --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;">Penjualan 7 Hari Terakhir</div>
                </div>
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="chartSales7"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="fw-semibold text-dark mb-3" style="font-size: 0.9rem;">Top 5 Produk Terlaris</div>
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="chartTopProduk"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- OPSIONAL: PRODUKSI --}}
<div class="row g-3 mb-3">
    <div class="col-lg-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="fw-semibold text-dark mb-2" style="font-size: 0.9rem;">Produksi 7 Hari Terakhir (opsional)</div>
                <div style="position: relative; height: 180px; width: 100%;">
                    <canvas id="chartProduksi7"></canvas>
                </div>
                <div class="text-muted mt-2" style="font-size: 0.75rem;">
                    Kalau grafik ini 0 semua, berarti nama tabel produksi kamu beda (aman, dashboard tetap jalan).
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ALERT STOK MENIPIS + TRANSAKSI TERAKHIR --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="fw-semibold text-dark mb-3" style="font-size: 0.9rem;">Stok Menipis</div>

                <div class="mb-3">
                    <div class="text-muted fw-medium mb-1" style="font-size: 0.75rem; text-transform: uppercase;">Bahan Baku</div>
                    @if($bahanMenipis->count())
                        <ul class="mb-0 ps-3" style="font-size: 0.85rem;">
                            @foreach($bahanMenipis as $b)
                                <li class="mb-1">{{ $b->nama_bahan }} <span class="text-muted">—</span> <b class="text-danger">{{ format_angka($b->stok_awal) }}</b> {{ $b->satuan }}</li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted" style="font-size: 0.85rem;">Aman, belum ada bahan yang menipis.</div>
                    @endif
                </div>

                <div>
                    <div class="text-muted fw-medium mb-1" style="font-size: 0.75rem; text-transform: uppercase;">Produk</div>
                    @if($produkMenipis->count())
                        <ul class="mb-0 ps-3" style="font-size: 0.85rem;">
                            @foreach($produkMenipis as $p)
                                <li class="mb-1">{{ $p->nama_produk }} <span class="text-muted">—</span> <b class="text-danger">{{ format_angka($p->stok) }}</b></li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted" style="font-size: 0.85rem;">Aman, belum ada produk yang menipis.</div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="fw-semibold text-dark mb-3" style="font-size: 0.9rem;">Penjualan Terakhir</div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 table-hover table-borderless">
                        <thead class="table-light">
                            <tr>
                                <th class="text-muted">Tanggal</th>
                                <th class="text-muted">Kode</th>
                                <th class="text-muted">Pembeli</th>
                                <th class="text-end text-muted">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($penjualanTerakhir as $pj)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($pj->tanggal)->format('d/m/Y') }}</td>
                                    <td class="fw-medium text-primary">{{ $pj->kode_penjualan }}</td>
                                    <td>{{ $pj->pembeli ?? '-' }}</td>
                                    <td class="text-end fw-bold">{{ rupiah($pj->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted text-center py-3" style="font-size: 0.85rem;">Belum ada penjualan.</td>
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

    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.scale.grid.color = 'rgba(0,0,0,0.04)';

    // Penjualan 7 hari
    new Chart(document.getElementById('chartSales7'), {
        type: 'line',
        data: {
            labels: labels7,
            datasets: [{
                label: 'Total Penjualan',
                data: data7,
                tension: 0.4,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderWidth: 2,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4f46e5',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8, titleFont: { size: 13 }, bodyFont: { size: 13 } }
            },
            scales: {
                y: { beginAtZero: true, border: { display: false } },
                x: { border: { display: false }, grid: { display: false } }
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
                data: topData,
                backgroundColor: '#0ea5e9',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, border: { display: false } },
                x: { border: { display: false }, grid: { display: false } }
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
                data: produksiData7,
                backgroundColor: '#10b981',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, border: { display: false } },
                x: { border: { display: false }, grid: { display: false } }
            }
        }
    });
</script>
@endpush
