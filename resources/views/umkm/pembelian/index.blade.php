@extends('layouts.umkm')

@section('title', 'Pembelian')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><strong>Riwayat</strong> Pembelian</h1>
            <p class="text-muted mb-0">
                Catatan pembelian bahan baku untuk usaha kamu.
            </p>
        </div>
        <a href="{{ route('umkm.pembelian.create') }}" class="btn btn-primary">
            + Pembelian Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($data->isEmpty())
                <p class="text-muted text-center mb-0">
                    Belum ada transaksi pembelian. Klik <strong>+ Pembelian Baru</strong> untuk menambahkan.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th>Isi Pembelian</th>
                            <th class="text-end">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $row)
                            <tr class="align-top">
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $row->kode_pembelian }}
                                    </span>
                                    @if($row->nomor_nota)
                                        <br><small class="text-muted">Nota: {{ $row->nomor_nota }}</small>
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $row->supplier ?? '-' }}</td>
                                <td>
                                    {{-- Ringkasan item yang dibeli --}}
                                    @if($row->details->isEmpty())
                                        <span class="text-muted small">-</span>
                                    @else
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($row->details as $det)
                                                <li class="py-1 border-bottom border-light-subtle d-flex justify-content-between gap-3">
                                                    <span>
                                                        <strong>{{ $det->bahan->nama_bahan ?? '?' }}</strong>
                                                        &times; {{ number_format($det->qty, 2, ',', '.') }}
                                                        {{ $det->bahan->satuan ?? '' }}
                                                    </span>
                                                    <span class="text-end text-muted text-nowrap">
                                                        {{ rupiah($det->harga_beli) }} / satuan<br>
                                                        <span class="text-dark fw-semibold">= {{ rupiah($det->subtotal) }}</span>
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <strong>{{ rupiah($row->total) }}</strong>
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
