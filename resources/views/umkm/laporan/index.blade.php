@extends('layouts.umkm')

@section('title', 'Laporan Keuangan')

@section('content')
@php
    use Carbon\Carbon;

    // anti error kalau controller belum ngirim variabel
    $bulan = $bulan ?? now()->format('Y-m'); // YYYY-MM
    $jurnal = $jurnal ?? collect();          // collection kosong kalau belum ada data
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><strong>Laporan</strong> Keuangan</h1>
        <p class="text-muted mb-0">Ringkasan pencatatan & laporan keuangan UMKM.</p>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- ===================== TAB NAV ===================== --}}
        <ul class="nav nav-tabs" id="laporanTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active"
                        id="tab-jurnal-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-jurnal"
                        type="button"
                        role="tab"
                        aria-controls="tab-jurnal"
                        aria-selected="true">
                    Jurnal Umum
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link"
                        id="tab-bukubesar-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-bukubesar"
                        type="button"
                        role="tab"
                        aria-controls="tab-bukubesar"
                        aria-selected="false">
                    Buku Besar
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link"
                        id="tab-labarugi-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-labarugi"
                        type="button"
                        role="tab"
                        aria-controls="tab-labarugi"
                        aria-selected="false">
                    Laba Rugi
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link"
                        id="tab-modal-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-modal"
                        type="button"
                        role="tab"
                        aria-controls="tab-modal"
                        aria-selected="false">
                    Perubahan Modal
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link"
                        id="tab-neraca-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-neraca"
                        type="button"
                        role="tab"
                        aria-controls="tab-neraca"
                        aria-selected="false">
                    Neraca
                </button>
            </li>
        </ul>

        {{-- ===================== TAB CONTENT ===================== --}}
        <div class="tab-content pt-3" id="laporanTabContent">

            {{-- ===================== JURNAL UMUM ===================== --}}
            <div class="tab-pane fade show active"
                 id="tab-jurnal"
                 role="tabpanel"
                 aria-labelledby="tab-jurnal-tab"
                 tabindex="0">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="mb-0">
                        Jurnal Umum — {{ Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('M Y') }}
                    </h5>

                    <form method="GET" action="{{ route('umkm.laporan.index') }}" class="d-flex align-items-center gap-2">
                        <input type="month"
                               name="bulan"
                               class="form-control form-control-sm"
                               value="{{ $bulan }}"
                               style="max-width: 180px;">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            Terapkan
                        </button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="text-uppercase text-muted small">
                            <tr>
                                <th style="width:140px;">Tanggal</th>
                                <th style="width:120px;">Kode Akun</th>
                                <th style="min-width:180px;">Nama Akun</th>
                                <th>Keterangan</th>
                                <th class="text-end" style="width:160px;">Debit</th>
                                <th class="text-end" style="width:160px;">Kredit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jurnal as $row)
                                @php
                                    // support beberapa nama field biar fleksibel:
                                    $tgl   = $row->tanggal ?? null;
                                    $kode  = $row->kode_akun ?? ($row->kode ?? '');
                                    $akun  = $row->nama_akun ?? ($row->akun ?? '');
                                    $ket   = $row->keterangan ?? '-';
                                    $debit = (float) ($row->debit ?? 0);
                                    $kredit= (float) ($row->kredit ?? 0);
                                @endphp

                                <tr>
                                    <td>
                                        {{ $tgl ? Carbon::parse($tgl)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td>{{ $kode }}</td>
                                    <td>{{ $akun }}</td>
                                    <td>{{ $ket }}</td>
                                    <td class="text-end">
                                        {{ $debit > 0 ? 'Rp ' . number_format($debit, 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end">
                                        {{ $kredit > 0 ? 'Rp ' . number_format($kredit, 0, ',', '.') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        Belum ada jurnal pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ===================== DUMMY: BUKU BESAR ===================== --}}
            <div class="tab-pane fade"
                 id="tab-bukubesar"
                 role="tabpanel"
                 aria-labelledby="tab-bukubesar-tab"
                 tabindex="0">
                <div class="alert alert-info mb-0">
                    Proses selanjutnya
                </div>
            </div>

            {{-- ===================== DUMMY: LABA RUGI ===================== --}}
            <div class="tab-pane fade"
                 id="tab-labarugi"
                 role="tabpanel"
                 aria-labelledby="tab-labarugi-tab"
                 tabindex="0">
                <div class="alert alert-info mb-0">
                    Proses selanjutnya
                </div>
            </div>

            {{-- ===================== DUMMY: PERUBAHAN MODAL ===================== --}}
            <div class="tab-pane fade"
                 id="tab-modal"
                 role="tabpanel"
                 aria-labelledby="tab-modal-tab"
                 tabindex="0">
                <div class="alert alert-info mb-0">
                    Proses selanjutnya
                </div>
            </div>

            {{-- ===================== DUMMY: NERACA ===================== --}}
            <div class="tab-pane fade"
                 id="tab-neraca"
                 role="tabpanel"
                 aria-labelledby="tab-neraca-tab"
                 tabindex="0">
                <div class="alert alert-info mb-0">
                    Proses selanjutnya
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
