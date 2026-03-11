@extends('layouts.umkm')

@section('title', 'Produksi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><strong>Produksi</strong></h1>
        <p class="text-muted mb-0">Catat proses produksi untuk menambah stok produk jadi & mengurangi stok bahan baku otomatis.</p>
    </div>
    <a href="{{ route('umkm.produksi.create') }}" class="btn btn-primary">
        + Tambah Produksi
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <table class="table mb-0">
            <thead>
            <tr>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Ringkasan</th>
            </tr>
            </thead>
            <tbody>
            @forelse($data as $p)
                <tr>
                    <td>{{ $p->kode_produksi }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-muted">
                        @if($p->details && $p->details->count())
                            {{ $p->details->count() }} item produksi
                            <div class="small">
                                @foreach($p->details as $d)
                                    • {{ $d->produk->nama_produk ?? '-' }} ({{ number_format($d->qty_hasil, 3, ',', '.') }})
                                    <br>
                                @endforeach
                            </div>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-muted">Belum ada data produksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
