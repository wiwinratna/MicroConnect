@extends('layouts.umkm')

@section('title', 'Bahan Baku')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><strong>Bahan</strong> Baku</h1>
        <a href="{{ route('umkm.bahan.create') }}" class="btn btn-primary">
            + Tambah Bahan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Stok Awal</th>
                    <th>Keterangan</th>
                    <th width="120">Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($bahan as $item)
                    <tr>
                        <td>{{ $item->kode_bahan }}</td>
                        <td>{{ $item->nama_bahan }}</td>
                        <td>{{ $item->satuan }}</td>
                        <td>{{ number_format($item->stok_awal, 2, ',', '.') }}</td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                        <td>
                            <a href="{{ route('umkm.bahan.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                Edit
                            </a>
                            <form action="{{ route('umkm.bahan.destroy', $item->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus bahan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
