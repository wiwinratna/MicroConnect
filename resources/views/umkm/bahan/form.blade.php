@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label class="form-label">Nama Bahan</label>
    <input type="text" name="nama_bahan" class="form-control"
           value="{{ old('nama_bahan', $bahan->nama_bahan ?? '') }}" required>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Satuan</label>
        <input type="text" name="satuan" class="form-control"
               value="{{ old('satuan', $bahan->satuan ?? '') }}" placeholder="kg, pcs, liter">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Keterangan</label>
    <textarea name="keterangan" rows="3" class="form-control">{{ old('keterangan', $bahan->keterangan ?? '') }}</textarea>
</div>

<button type="submit" class="btn btn-primary">
    Simpan
</button>
<a href="{{ route('umkm.bahan.index') }}" class="btn btn-outline-secondary">
    Batal
</a>
