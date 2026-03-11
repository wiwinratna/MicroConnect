@extends('layouts.umkm')

@section('title', 'Produk Jadi')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><strong>Produk</strong> Jadi</h1>
            <p class="text-muted mb-0">
                Daftar produk yang dijual oleh usaha kamu.
            </p>
        </div>
        <a href="{{ route('umkm.produk.create') }}" class="btn btn-primary">
            + Tambah Produk
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
                    Belum ada produk. Klik <strong>+ Tambah Produk</strong> untuk menambahkan.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Satuan</th>
                            <th class="text-end">Stok</th>
                            <th class="text-end">HPP (Rp)</th>
                            <th class="text-end">Harga Jual (Rp)</th>
                            <th width="130" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $p)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($p->foto_path)
                                            <img src="{{ asset('storage/'.$p->foto_path) }}"
                                                 alt="foto"
                                                 class="rounded me-2"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded bg-light d-flex justify-content-center align-items-center me-2"
                                                 style="width: 40px; height: 40px;">
                                                <i data-feather="image" class="text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $p->nama_produk }}</div>
                                            <div class="small text-muted">{{ $p->kode_produk }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $p->satuan }}</td>
                                <td class="text-end">{{ number_format($p->stok, 2, ',', '.') }}</td>
                                <td class="text-end">
                                    Rp {{ number_format($p->harga_pokok, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($p->harga_jual, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('umkm.produk.edit', $p->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Edit
                                    </a>
                                    <form action="{{ route('umkm.produk.destroy', $p->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">
                                            Hapus
                                        </button>
                                    </form>
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
