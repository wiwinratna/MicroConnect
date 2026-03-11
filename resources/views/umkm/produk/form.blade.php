@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- kalau edit, tampilkan kode produk --}}
@if(!empty($produk))
    <div class="mb-3">
        <label class="form-label">Kode Produk</label>
        <input type="text" class="form-control" value="{{ $produk->kode_produk }}" disabled>
    </div>
@endif

<div class="mb-3">
    <label class="form-label">Nama Produk</label>
    <input type="text" name="nama_produk" class="form-control"
           value="{{ old('nama_produk', $produk->nama_produk ?? '') }}" required>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Kategori</label>
        <input type="text" name="kategori" class="form-control"
               value="{{ old('kategori', $produk->kategori ?? '') }}"
               placeholder="Makanan, Minuman, dll.">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Harga Jual (Rp)</label>
        <input type="number" step="0.01" name="harga_jual" class="form-control"
               value="{{ old('harga_jual', $produk->harga_jual ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="aktif" id="aktif"
                   {{ old('aktif', $produk->aktif ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="aktif">
                Produk Aktif
            </label>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Keterangan</label>
    <textarea name="keterangan" rows="3" class="form-control">{{ old('keterangan', $produk->keterangan ?? '') }}</textarea>
</div>

<button type="submit" class="btn btn-primary">
    Simpan
</button>
<a href="{{ route('umkm.produk.index') }}" class="btn btn-outline-secondary">
    Batal
</a>
