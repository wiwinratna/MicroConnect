@extends('layouts.umkm')

@section('title', 'Tambah Bahan Baku')

@section('content')
    <h1 class="h3 mb-3"><strong>Tambah</strong> Bahan Baku</h1>

    <form method="POST" action="{{ route('umkm.bahan.store') }}">
        @csrf

        {{-- Kode otomatis, readonly, tapi tetap dikirim lewat input hidden --}}
        <div class="mb-3">
            <label class="form-label">Kode Bahan</label>
            <input type="hidden" name="kode_bahan" value="{{ $kodeBaru }}">
            <input type="text" class="form-control" value="{{ $kodeBaru }}" readonly>
            <small class="text-muted">Kode dibuat otomatis oleh sistem.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Bahan</label>
            <input type="text" name="nama_bahan" class="form-control"
                   value="{{ old('nama_bahan') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Satuan</label>
            <input type="text" name="satuan" class="form-control"
                   value="{{ old('satuan') }}" placeholder="kg, liter, pcs, dll" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Stok Awal</label>
            <input type="number" step="0.01" name="stok_awal" class="form-control"
                   value="{{ old('stok_awal', 0) }}">
            <small class="text-muted">Boleh 0 kalau stok awal belum dicatat.</small>
        </div>

        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <textarea name="keterangan" class="form-control" rows="3"
                      placeholder="Contoh: Beli di supplier A, kualitas ekspor, dsb.">{{ old('keterangan') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            Simpan
        </button>
    </form>
@endsection
