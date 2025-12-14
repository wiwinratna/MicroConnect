@extends('layouts.umkm')

@section('title', 'Edit Bahan Baku')

@section('content')
    <h1 class="h3 mb-3"><strong>Edit</strong> Bahan Baku</h1>

    <form method="POST" action="{{ route('umkm.bahan.update', $bahan->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Kode Bahan</label>
            <input type="text" class="form-control" value="{{ $bahan->kode_bahan }}" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Bahan</label>
            <input type="text" name="nama_bahan" class="form-control"
                   value="{{ old('nama_bahan', $bahan->nama_bahan) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Satuan</label>
            <input type="text" name="satuan" class="form-control"
                   value="{{ old('satuan', $bahan->satuan) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Stok Awal</label>
            <input type="number" step="0.01" name="stok_awal" class="form-control"
                   value="{{ old('stok_awal', $bahan->stok_awal) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $bahan->keterangan) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            Update
        </button>
    </form>
@endsection
