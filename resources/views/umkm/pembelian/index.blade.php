@extends('layouts.umkm')

@section('title', 'Pembelian Bahan Baku')

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
                            <th>No. Nota Vendor</th>
                            <th>Tanggal</th>
                            <th>Supplier</th>
                            <th class="text-end">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $row)
                            <tr>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $row->kode_pembelian }}
                                    </span>
                                </td>
                                <td>{{ $row->nomor_nota ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $row->supplier ?? '-' }}</td>
                                <td class="text-end">
                                    Rp {{ number_format($row->total, 0, ',', '.') }}
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
