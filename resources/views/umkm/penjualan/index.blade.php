@extends('layouts.umkm')
@section('title','Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><strong>Riwayat</strong> Penjualan</h1>
        <p class="text-muted mb-0">Catatan transaksi penjualan produk.</p>
    </div>
    <a href="{{ route('umkm.penjualan.create') }}" class="btn btn-primary">+ Tambah Penjualan</a>
</div>



@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        @if($data->isEmpty())
            <p class="text-muted text-center mb-0">Belum ada data penjualan.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover table-borderless">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Pembeli</th>
                            <th>Produk Dijual</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $d)
                            <tr class="align-top">
                                <td>
                                    <span class="badge bg-success-subtle text-success">
                                        {{ $d->kode_penjualan }}
                                    </span>
                                </td>
                                <td class="text-nowrap">
                                    {{ \Carbon\Carbon::parse($d->tanggal)->format('d/m/Y') }}
                                </td>
                                <td>{{ $d->pembeli ?? '-' }}</td>
                                <td>
                                    {{-- Ringkasan produk yang dijual --}}
                                    @if($d->details->isEmpty())
                                        <span class="text-muted small">-</span>
                                    @else
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($d->details as $det)
                                                <li class="py-1 border-bottom border-light-subtle d-flex justify-content-between gap-3">
                                                    <span>
                                                        <strong>{{ $det->produk->nama_produk ?? '?' }}</strong>
                                                        &times; {{ format_angka($det->qty) }}
                                                    </span>
                                                    <span class="text-end text-muted text-nowrap">
                                                        {{ rupiah($det->harga) }} / unit<br>
                                                        <span class="text-dark fw-semibold">= {{ rupiah($det->subtotal) }}</span>
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <strong>{{ rupiah($d->total) }}</strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
