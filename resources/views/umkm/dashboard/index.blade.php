@extends('layouts.umkm')

@section('title', 'Dashboard UMKM')

@push('styles')
{{-- Flatpickr CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="{{ asset('css/minect-theme/dashboard.css') }}">
@endpush

@section('content')
<div class="dash-head">
    <div>
        <h1>Dashboard UMKM</h1>
        <p>Ringkasan penjualan, stok, dan performa usaha.</p>
    </div>
    <form method="GET" action="{{ route('umkm.dashboard') }}" class="dash-filter">
        <input type="text" id="daterangePicker" name="daterange" class="form-control form-control-sm" placeholder="Pilih Tanggal..." aria-label="Rentang tanggal" value="{{ $daterange }}" style="width: 210px;">
        <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
    </form>
</div>

@if(isset($iuranBelumLunas))
<div class="alert alert-warning dash-dues mb-3">
    <div>
        <strong class="d-block mb-1">⚠️ Perhatian: Iuran Aplikasi Menunggu Pembayaran.</strong>
        <span>Segera bayar iuran bulan <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $iuranBelumLunas->periode)->isoFormat('MMMM Y') }}</strong> sebesar <strong>{{ rupiah($iuranBelumLunas->nominal) }}</strong>.</span>
        @if($iuranBelumLunas->jatuh_tempo)
        <span class="mt-1 d-block small">Tenggat pembayaran: <strong>{{ $iuranBelumLunas->jatuh_tempo->isoFormat('D MMMM Y') }}</strong>. Mohon diselesaikan untuk menghindari ganguan akses.</span>
        @endif
    </div>
    <div>
        <a href="{{ route('umkm.iuran.index') }}" class="btn btn-warning">Bayar Sekarang &rarr;</a>
    </div>
</div>
@endif

{{-- KPI CARDS --}}
<div class="stat-grid">
    <div class="card">
        <div class="card-body stat-tile">
            <div>
                <div class="stat-label">Total Omzet Terpilih</div>
                <div class="stat-value">{{ rupiah($penjualanRentang) }}</div>
                <div class="stat-note">{{ $labelRentang }}</div>
            </div>
            <span class="stat-icon is-success"><i data-lucide="banknote"></i></span>
        </div>
    </div>
    <div class="card">
        <div class="card-body stat-tile">
            <div>
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value">{{ format_angka($trxRentang) }}</div>
                <div class="stat-note">Penjualan sukses ({{ $labelRentang }})</div>
            </div>
            <span class="stat-icon"><i data-lucide="receipt"></i></span>
        </div>
    </div>
    @if(modul_boleh('bahan_baku'))
    <div class="card">
        <div class="card-body stat-tile">
            <div>
                <div class="stat-label">Item Bahan Aktif</div>
                <div class="stat-value">{{ format_angka($totalBahanAktif) }}</div>
                <div class="stat-note">Berdasarkan master bahan baku</div>
            </div>
            <span class="stat-icon"><i data-lucide="archive"></i></span>
        </div>
    </div>
    @endif
</div>

{{-- STOK MENIPIS + TRANSAKSI TERAKHIR --}}
@if(modul_boleh('bahan_baku') || modul_boleh('penjualan'))
<div class="row g-3 mb-3">
    @if(modul_boleh('bahan_baku'))
    <div class="col-xl-5">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <h2 class="panel-title">
                    <span class="stat-icon is-warning"><i data-lucide="package"></i></span>
                    Stok Bahan Menipis
                </h2>
            </div>
            @if($bahanMenipis->count())
                <ul class="dash-list">
                    @foreach($bahanMenipis as $b)
                        <li>
                            <span class="fw-semibold">{{ $b->nama_bahan }}</span>
                            <span class="badge bg-danger-subtle">Sisa: {{ format_angka($b->current_stok) }} {{ $b->satuan }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="card-body">
                    <div class="dash-calm">
                        <span aria-hidden="true">✅</span> Stok bahan baku terpantau aman.
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif

    @if(modul_boleh('penjualan'))
    <div class="{{ modul_boleh('bahan_baku') ? 'col-xl-7' : 'col-12' }}">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <h2 class="panel-title">
                    <span class="stat-icon"><i data-lucide="receipt-text"></i></span>
                    Penjualan Terakhir
                </h2>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
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
                                <td class="money">{{ \Carbon\Carbon::parse($pj->tanggal)->format('d/m/Y') }}</td>
                                <td class="money fw-semibold text-primary">{{ $pj->kode_penjualan }}</td>
                                <td>{{ $pj->pembeli ?? '-' }}</td>
                                <td class="text-end fw-bold money">{{ rupiah($pj->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted text-center py-3">Belum ada penjualan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

{{-- GRAFIK --}}
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="panel-title mb-3">Tren Penjualan ({{ $labelRentang }})</h2>
                <div class="dash-chart">
                    <canvas id="chartSales7" role="img" aria-label="Grafik tren penjualan"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="panel-title mb-3">Top 5 Produk ({{ $labelRentang }})</h2>
                <div class="dash-chart">
                    <canvas id="chartTopProduk" role="img" aria-label="Grafik lima produk terlaris"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Chart.js & Flatpickr CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr("#daterangePicker", {
            mode: "range",
            maxDate: "today",
            dateFormat: "Y-m-d",
            defaultDate: "{{ $daterange ?? '' }}"
        });
    });

    const labelsGrafik = @json($labelsGrafik);
    const dataGrafik   = @json($dataGrafik);

    const topLabels = @json($topLabels);
    const topData   = @json($topData);

    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#5C6A80';
    Chart.defaults.scale.grid.color = '#E1E8F1';

    const tooltip = { backgroundColor: '#26364D', padding: 10, cornerRadius: 8, titleFont: { size: 13 }, bodyFont: { size: 13 } };
    const angka = new Intl.NumberFormat('id-ID', { notation: 'compact' });

    // Penjualan Rentang Waktu
    new Chart(document.getElementById('chartSales7'), {
        type: 'line',
        data: {
            labels: labelsGrafik,
            datasets: [{
                label: 'Total Penjualan',
                data: dataGrafik,
                tension: 0.35,
                borderColor: '#233B63',
                backgroundColor: 'rgba(82, 121, 185, 0.14)',
                borderWidth: 2,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#233B63',
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
                tooltip: Object.assign({
                    callbacks: { label: (c) => c.dataset.label + ': Rp ' + c.parsed.y.toLocaleString('id-ID') }
                }, tooltip)
            },
            scales: {
                y: { beginAtZero: true, border: { display: false }, ticks: { callback: (v) => angka.format(v) } },
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
                backgroundColor: '#5279B9',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: tooltip },
            scales: {
                y: { beginAtZero: true, border: { display: false } },
                x: { border: { display: false }, grid: { display: false } }
            }
        }
    });
</script>
@endpush
