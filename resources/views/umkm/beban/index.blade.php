@extends('layouts.umkm')
@section('title', 'Riwayat Beban Operasional')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><strong>Beban</strong> Operasional</h1>
        <p class="text-muted mb-0">Riwayat beban usaha yang sudah dicatat</p>
    </div>
    <a href="{{ route('umkm.beban.create') }}" class="btn btn-primary">+ Catat Beban</a>
</div>

{{-- Filter Bulan --}}
<form method="GET" class="mb-3 d-flex gap-2 align-items-center">
    <input type="month" name="bulan" class="form-control" style="max-width:200px;"
           value="{{ $bulan }}">
    <button class="btn btn-outline-primary">Filter</button>
</form>



{{-- Ringkasan --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius:12px; background:linear-gradient(135deg,#ff6b6b,#ee5a24);">
    <div class="card-body text-white py-3">
        <div class="small opacity-75">Total Beban Operasional Bulan Ini</div>
        <div class="h3 mb-0 fw-bold">{{ rupiah($totalBeban) }}</div>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius:12px;">
    <div class="card-body p-0">
        @if($bebanList->isEmpty())
            <div class="text-center py-5 text-muted">
                <div style="font-size:3rem;">📋</div>
                <p class="mt-2">Belum ada beban yang dicatat untuk bulan ini.</p>
                <a href="{{ route('umkm.beban.create') }}" class="btn btn-primary">Catat Beban Pertama</a>
            </div>
        @else
        <table class="table table-hover mb-0 table-borderless align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th class="text-end">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bebanList as $b)
                <tr>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($b->tanggal)->format('d M Y') }}</td>
                    <td>
                        <span class="badge bg-warning text-dark">{{ $b->kode_akun }}</span>
                        {{ $b->nama_akun }}
                    </td>
                    <td class="small text-muted">{{ $b->keterangan }}</td>
                    <td  class="text-end fw-semibold fw-medium">{{ rupiah($b->debit) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="3" class="fw-bold">Total</td>
                    <td  class="text-end fw-bold text-danger fw-medium">{{ rupiah($totalBeban) }}</td>
                </tr>
            </tfoot>
        </table>
        @endif
    </div>
</div>

<p class="text-muted small mt-2">
    💡 Beban operasional tercatat di jurnal akun 6xx dan memengaruhi laporan Laba Rugi.
</p>
@endsection
