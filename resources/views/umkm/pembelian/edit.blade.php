@extends('layouts.umkm')

@section('title', 'Edit Informasi Pembelian')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><strong>Edit</strong> Pembelian #{{ $pembelian->kode_pembelian }}</h1>
            <p class="text-muted mb-0">
                Ubah informasi nota, catatan, dan unggah lampiran bukti transaksi. 
                <br><span class="text-danger fw-semibold"><i data-feather="lock" style="width:14px; height:14px;"></i> Qty dan Harga aset tidak dapat diubah setelah dicatat demi integritas logistik metode FIFO/Average.</span>
            </p>
        </div>
        <a href="{{ route('umkm.pembelian.index') }}" class="btn btn-outline-secondary">
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

    <form action="{{ route('umkm.pembelian.update', $pembelian->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- HEADER PEMBELIAN --}}
        <div class="card mb-4 shadow-sm border-0 border-top border-3 border-warning">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted">Kode Transaksi</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $pembelian->kode_pembelian }}"
                               disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Tanggal Transaksi</label>
                        <input type="date"
                               class="form-control"
                               value="{{ $pembelian->tanggal }}"
                               disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nomor Nota Vendor</label>
                        <input type="text"
                               name="nomor_nota"
                               class="form-control"
                               value="{{ old('nomor_nota', $pembelian->nomor_nota) }}"
                               placeholder="Contoh: STRUK-8891">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <input type="text"
                               name="supplier"
                               class="form-control"
                               value="{{ old('supplier', $pembelian->supplier) }}"
                               placeholder="Nama toko / pemasok (opsional)">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan"
                                  rows="1"
                                  class="form-control"
                                  placeholder="Catatan tambahan (opsional)">{{ old('catatan', $pembelian->catatan) }}</textarea>
                    </div>

                    <div class="col-12 mt-4">
                        <label class="form-label fw-bold">Unggah Bukti Transaksi/Struk (PDF/Gambar)</label>
                        
                        @if($pembelian->bukti_pembelian)
                            <div class="mb-3 p-3 bg-light rounded d-flex justify-content-between align-items-center">
                                <div>
                                    <i data-feather="check-circle" class="text-success me-2"></i>
                                    <span class="fw-medium">Berkas saat ini telah tersimpan!</span>
                                </div>
                                <a href="{{ asset('storage/' . $pembelian->bukti_pembelian) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Berkas Aktif</a>
                            </div>
                        @else
                            <div class="mb-3 p-3 bg-light rounded text-muted">
                                <i data-feather="info" class="me-2" style="width:16px;"></i> Belum ada bukti transaksi yang diunggah.
                            </div>
                        @endif

                        <input type="file" name="bukti_pembelian" class="form-control form-control-lg" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted mt-1 d-block">Maksimal 2MB. Mengunggah file baru akan menggantikan file sebelumnya.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 pb-5 d-flex justify-content-end">
            <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark px-5 shadow-sm">
                <i data-feather="save" class="me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
@endsection
