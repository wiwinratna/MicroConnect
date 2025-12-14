@extends('layouts.umkm')

@section('title', 'Edit Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><strong>Edit</strong> Produk</h1>
            <p class="text-muted mb-0">
                Perbarui informasi produk kamu.
            </p>
        </div>
        <a href="{{ route('umkm.produk.index') }}" class="btn btn-outline-secondary">
            &larr; Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('umkm.produk.update', $produk->id) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Produk</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $produk->kode_produk }}"
                               readonly>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Nama Produk</label>
                        <input type="text"
                               name="nama_produk"
                               class="form-control"
                               value="{{ old('nama_produk', $produk->nama_produk) }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Satuan</label>
                        <input type="text"
                               name="satuan"
                               class="form-control"
                               value="{{ old('satuan', $produk->satuan) }}"
                               required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="number"
                               name="harga_jual"
                               class="form-control"
                               min="0"
                               step="1"
                               value="{{ old('harga_jual', $produk->harga_jual) }}">
                        <small class="text-muted">
                            HPP saat ini: Rp {{ number_format($produk->harga_pokok, 0, ',', '.') }}
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Foto Produk</label>
                        <input type="file"
                               name="foto"
                               class="form-control">

                        @if($produk->foto_path)
                            <small class="text-muted d-block mt-1">
                                Foto saat ini:
                            </small>
                            <img src="{{ asset('storage/'.$produk->foto_path) }}"
                                 alt="foto produk"
                                 class="img-thumbnail mt-1"
                                 style="max-height: 120px;">
                        @endif
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
