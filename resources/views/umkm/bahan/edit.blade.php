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

        {{-- ===== SALDO AWAL ===== --}}
        <div class="mb-3">
            <label class="form-label">Saldo Awal Persediaan</label>
            @if($hasTransaksi)
                {{-- Sudah ada transaksi berjalan: seluruh saldo awal dikunci --}}
                <div class="card border-0 bg-light p-3">
                    <div class="d-flex gap-3 flex-wrap">
                        <div>
                            <small class="text-muted d-block">Qty Awal</small>
                            <strong>{{ format_angka($bahan->stok_awal) }} {{ $bahan->satuan }}</strong>
                        </div>
                        <div>
                            <small class="text-muted d-block">Harga / Unit</small>
                            <strong>{{ rupiah($mutasiSaldoAwal?->harga_unit ?? 0) }}</strong>
                        </div>
                        <div>
                            <small class="text-muted d-block">Total Nilai Awal</small>
                            <strong class="text-primary">{{ rupiah(($bahan->stok_awal) * ($mutasiSaldoAwal?->harga_unit ?? 0)) }}</strong>
                        </div>
                    </div>
                    <div class="form-text text-warning mt-2">
                        <i class="fas fa-lock me-1"></i>
                        Saldo awal terkunci karena bahan ini sudah memiliki transaksi berjalan.
                        Koreksi stok dilakukan via penyesuaian stok terpisah.
                    </div>
                </div>
            @else
                {{-- Belum ada transaksi: semua field bisa diedit --}}
                <div class="card border-0 bg-light p-3">
                    <p class="text-muted small mb-3">
                        Isi <em>Qty</em> dan salah satu dari <em>Harga / Unit</em> atau <em>Total Nilai</em>.
                        Sistem menghitung yang lain secara otomatis.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small">Qty Awal</label>
                            <input type="number" step="0.001" min="0" name="stok_awal"
                                   id="stok_awal" class="form-control"
                                   value="{{ old('stok_awal', $bahan->stok_awal) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Harga / Unit (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="1" min="0" name="harga_unit_awal"
                                       id="harga_unit_awal" class="form-control"
                                       value="{{ old('harga_unit_awal', $mutasiSaldoAwal?->harga_unit ?? 0) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Total Nilai Awal (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" step="1" min="0"
                                       id="total_nilai_awal" class="form-control"
                                       value="{{ intval($bahan->stok_awal * ($mutasiSaldoAwal?->harga_unit ?? 0)) }}">
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 p-2 bg-white rounded border" id="preview_nilai" style="display:none;">
                        <small class="text-muted">Ringkasan saldo awal:</small><br>
                        <span class="fw-semibold" id="preview_text">—</span>
                    </div>
                    <div class="form-text mt-2">Setelah ada transaksi, field ini akan terkunci otomatis.</div>
                </div>
            @endif
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

@unless($hasTransaksi)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInput   = document.getElementById('stok_awal');
    const hargaInput = document.getElementById('harga_unit_awal');
    const nilaiInput = document.getElementById('total_nilai_awal');
    const previewDiv = document.getElementById('preview_nilai');
    const previewText= document.getElementById('preview_text');
    let updating = false;

    function formatRp(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(num));
    }

    function updatePreview() {
        const qty   = parseFloat(qtyInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        if (qty > 0 && harga > 0) {
            previewDiv.style.display = 'block';
            previewText.innerHTML = qty.toLocaleString('id-ID') + ' satuan × ' +
                formatRp(harga) + '/unit = <strong class="text-primary">' + formatRp(qty * harga) + '</strong>';
        } else {
            previewDiv.style.display = 'none';
        }
    }

    qtyInput.addEventListener('input', function () {
        if (updating) return; updating = true;
        const qty = parseFloat(this.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        if (harga > 0) nilaiInput.value = Math.round(qty * harga);
        updating = false; updatePreview();
    });

    hargaInput.addEventListener('input', function () {
        if (updating) return; updating = true;
        nilaiInput.value = Math.round((parseFloat(qtyInput.value) || 0) * (parseFloat(this.value) || 0));
        updating = false; updatePreview();
    });

    nilaiInput.addEventListener('input', function () {
        if (updating) return; updating = true;
        const qty = parseFloat(qtyInput.value) || 0;
        if (qty > 0) hargaInput.value = Math.round((parseFloat(this.value) || 0) / qty);
        updating = false; updatePreview();
    });

    updatePreview();
});
</script>
@endpush
@endunless
