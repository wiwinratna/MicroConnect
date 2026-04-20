@extends('layouts.umkm')

@section('title', 'Dashboard UMKM')

@push('styles')
{{-- Flatpickr CSS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Dashboard Compact Enhancements */
    h1.h3 { font-size: 1.35rem !important; font-weight: 700; letter-spacing: -0.02em; }
    .card { box-shadow: 0 4px 12px rgba(0,0,0,0.02) !important; border-radius: 12px !important; margin-bottom: 1rem !important; }
    .card-body { padding: 1.25rem 1.5rem !important; }
    .table > :not(caption) > * > * { padding: 0.5rem 0.5rem !important; font-size: 0.85rem; }
    .table th { font-weight: 600; font-size: 0.75rem !important; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; }
    
    /* Flatpickr override to match bootstrap inputs */
    .flatpickr-input { background-color: #fff !important; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 text-dark"><strong>Dashboard</strong> UMKM</h1>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">Ringkasan penjualan, stok, dan performa usaha.</p>
    </div>
    <form method="GET" action="{{ route('umkm.dashboard') }}" class="d-flex gap-2 align-items-center">
        <input type="text" id="daterangePicker" name="daterange" class="form-control form-control-sm text-secondary" placeholder="Pilih Tanggal..." value="{{ $daterange }}" style="width: 210px; font-size: 0.85rem; font-family: inherit;">
        <button type="submit" class="btn btn-primary btn-sm px-3" style="font-size: 0.85rem;">Terapkan</button>
    </form>
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
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted fw-semibold mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Omzet Terpilih</div>
                <div class="fs-4 fw-bold text-dark mb-0 lh-1">{{ rupiah($penjualanRentang) }}</div>
                <div class="text-muted mt-2 text-truncate" style="font-size: 0.75rem;">{{ $labelRentang }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted fw-semibold mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Total Transaksi</div>
                <div class="fs-4 fw-bold text-dark mb-0 lh-1">{{ format_angka($trxRentang) }}</div>
                <div class="text-muted mt-2 text-truncate" style="font-size: 0.75rem;">Penjualan sukses ({{ $labelRentang }})</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="text-muted fw-semibold mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Item Bahan Aktif</div>
                <div class="fs-4 fw-bold text-dark mb-0 lh-1">{{ format_angka($totalBahanAktif) }}</div>
                <div class="text-muted mt-2" style="font-size: 0.75rem;">Berdasarkan master bahan baku</div>
            </div>
        </div>
    </div>
</div>

{{-- STOK MENIPIS + TRANSAKSI TERAKHIR (PINDAH KE ATAS) --}}
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="fw-semibold text-dark mb-3" style="font-size: 0.9rem;">Stok Bahan Menipis</div>

                <div class="mb-3">
                    @if($bahanMenipis->count())
                        <div class="d-flex flex-column gap-2 mt-2">
                            @foreach($bahanMenipis as $b)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2 text-truncate" style="font-size: 0.85rem; max-width: 70%;">
                                        <div style="width: 6px; height: 6px; border-radius: 50%; background-color: #ef4444; flex-shrink: 0;"></div>
                                        <span class="text-dark fw-medium text-truncate">{{ $b->nama_bahan }}</span>
                                    </div>
                                    <div class="text-end" style="font-size: 0.82rem; white-space: nowrap;">
                                        <span class="text-secondary">Sisa:</span> <span class="fw-bold text-dark">{{ format_angka($b->current_stok) }}</span> <span class="text-secondary">{{ $b->satuan }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted d-flex align-items-center gap-2 mt-2" style="font-size: 0.85rem; background: #f8fafc; padding: 0.6rem; border-radius: 6px; border: 1px dashed #cbd5e1;">
                            <span style="font-size: 1rem;">✅</span> Stok bahan baku terpantau aman.
                        </div>
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

{{-- GRAFIK (PINDAH KE BAWAH) --}}
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fw-semibold text-dark text-truncate" style="font-size: 0.9rem;">Tren Penjualan (<span class="text-primary">{{ $labelRentang }}</span>)</div>
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
                <div class="fw-semibold text-dark mb-3 text-truncate" style="font-size: 0.9rem;">Top 5 Produk (<span class="text-primary">{{ $labelRentang }}</span>)</div>
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="chartTopProduk"></canvas>
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

    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.scale.grid.color = 'rgba(0,0,0,0.04)';

    // Penjualan Rentang Waktu
    new Chart(document.getElementById('chartSales7'), {
        type: 'line',
        data: {
            labels: labelsGrafik,
            datasets: [{
                label: 'Total Penjualan',
                data: dataGrafik,
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

</script>
@endpush
