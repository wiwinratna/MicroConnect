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

        {{-- ===== SALDO AWAL ===== --}}
        <div class="card border-0 bg-light mb-3 p-3">
            <h6 class="fw-bold mb-1">Saldo Awal Persediaan</h6>
            <p class="text-muted small mb-3">
                Isi data persediaan awal sebelum ada transaksi. Setelah disimpan, saldo awal ini akan
                muncul di kartu stok sebagai <strong>mutasi masuk awal</strong>.<br>
                Isi <em>Qty</em> dan salah satu dari <em>Harga / Unit</em> atau <em>Total Nilai</em>,
                sistem akan menghitung yang lain secara otomatis.
            </p>

            <div class="row g-3">
                {{-- Qty Awal --}}
                <div class="col-md-4">
                    <label class="form-label">Qty Awal <span class="text-muted small">(opsional)</span></label>
                    <input type="number" step="0.001" min="0" name="stok_awal"
                           id="stok_awal" class="form-control"
                           value="{{ old('stok_awal', 0) }}"
                           placeholder="0">
                </div>

                {{-- Harga per Unit --}}
                <div class="col-md-4">
                    <label class="form-label">Harga / Unit (Rp) <span class="text-muted small">(opsional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" step="1" min="0" name="harga_unit_awal"
                               id="harga_unit_awal" class="form-control"
                               value="{{ old('harga_unit_awal', 0) }}"
                               placeholder="0">
                    </div>
                    <div class="form-text">Isi salah satu: harga/unit <strong>atau</strong> total nilai.</div>
                </div>

                {{-- Total Nilai (tidak dikirim ke server — hanya untuk kalkulasi UI) --}}
                <div class="col-md-4">
                    <label class="form-label">Total Nilai Awal (Rp) <span class="text-muted small">(opsional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" step="1" min="0"
                               id="total_nilai_awal" class="form-control"
                               value="{{ old('stok_awal', 0) > 0 && old('harga_unit_awal', 0) > 0
                                   ? (old('stok_awal', 0) * old('harga_unit_awal', 0))
                                   : 0 }}"
                               placeholder="0">
                    </div>
                    <div class="form-text">Dihitung otomatis dari Qty × Harga/Unit.</div>
                </div>
            </div>

            {{-- Preview --}}
            <div class="mt-3 p-2 bg-white rounded border" id="preview_nilai" style="display:none;">
                <small class="text-muted">Ringkasan saldo awal yang akan disimpan:</small><br>
                <span class="fw-semibold" id="preview_text">—</span>
            </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtyInput      = document.getElementById('stok_awal');
    const hargaInput    = document.getElementById('harga_unit_awal');
    const nilaiInput    = document.getElementById('total_nilai_awal');
    const previewDiv    = document.getElementById('preview_nilai');
    const previewText   = document.getElementById('preview_text');

    // Mencegah panggilan kalkulasi rekursif
    let updating = false;

    function formatRp(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(num));
    }

    function updatePreview() {
        const qty   = parseFloat(qtyInput.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        const nilai = harga * qty;

        if (qty > 0 && harga > 0) {
            previewDiv.style.display = 'block';
            previewText.innerHTML =
                qty.toLocaleString('id-ID') + ' satuan × ' +
                formatRp(harga) + '/unit = <strong class="text-primary">' +
                formatRp(nilai) + '</strong>';
        } else {
            previewDiv.style.display = 'none';
        }
    }

    // Qty berubah → update total nilai
    qtyInput.addEventListener('input', function () {
        if (updating) return;
        updating = true;
        const qty   = parseFloat(this.value) || 0;
        const harga = parseFloat(hargaInput.value) || 0;
        if (harga > 0) {
            nilaiInput.value = Math.round(qty * harga);
        }
        updating = false;
        updatePreview();
    });

    // Harga/unit berubah → update total nilai
    hargaInput.addEventListener('input', function () {
        if (updating) return;
        updating = true;
        const harga = parseFloat(this.value) || 0;
        const qty   = parseFloat(qtyInput.value) || 0;
        nilaiInput.value = Math.round(qty * harga);
        updating = false;
        updatePreview();
    });

    // Total nilai berubah → update harga/unit (jika qty > 0)
    nilaiInput.addEventListener('input', function () {
        if (updating) return;
        updating = true;
        const nilai = parseFloat(this.value) || 0;
        const qty   = parseFloat(qtyInput.value) || 0;
        if (qty > 0) {
            hargaInput.value = Math.round(nilai / qty);
        }
        updating = false;
        updatePreview();
    });

    // Init preview saat page load (jika ada old() values)
    updatePreview();
});
</script>
@endpush
