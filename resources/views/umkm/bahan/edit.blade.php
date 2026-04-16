@extends('layouts.umkm')

@section('title', 'Edit Bahan Baku')

@push('styles')
<style>
/* ── Section label divider ── */
.form-section-label {
    font-size: 0.63rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 0.875rem;
    display: flex; align-items: center; gap: 0.5rem;
}
.form-section-label::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

/* ── Kode badge ── */
.kode-badge-input {
    display: inline-flex; align-items: center; gap: 0.45rem;
    background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 7px;
    padding: 0.32rem 0.8rem;
    font-family: 'SF Mono', 'Fira Code', ui-monospace, monospace;
    font-size: 0.78rem; color: #475569; letter-spacing: 0.04em;
}
.kode-badge-input .dot { width: 6px; height: 6px; border-radius: 50%; background: #94a3b8; flex-shrink: 0; }

/* ── Saldo section (editable) ── */
.saldo-section {
    background: linear-gradient(145deg, #eff6ff 0%, #f8fafc 100%);
    border: 1px solid #bfdbfe; border-radius: 12px;
    padding: 1.25rem; margin-bottom: 1rem;
}
.saldo-section-head {
    display: flex; align-items: flex-start; gap: 0.875rem;
    padding-bottom: 1rem; border-bottom: 1px solid rgba(191,219,254,0.6);
    margin-bottom: 1rem;
}
.saldo-icon-box {
    width: 38px; height: 38px; background: #dbeafe; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #2563eb; flex-shrink: 0;
}
.saldo-icon-box svg { width: 17px; height: 17px; }
.saldo-section-title { font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0 0 3px; }
.saldo-section-desc  { font-size: 0.73rem; color: #64748b; margin: 0; line-height: 1.55; }

/* ── Saldo fields CSS grid ── */
.saldo-fields {
    display: grid;
    grid-template-columns: 1.1fr 1.4fr 1.4fr;
    gap: 0.875rem;
    align-items: start;
}
.field-group { display: flex; flex-direction: column; }
.field-group .form-label { font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 0.3rem; }
.field-group .field-hint { font-size: 0.67rem; color: #94a3b8; margin-top: 4px; }

/* ── Saldo locked (read-only grid) ── */
.saldo-locked-section {
    background: #fafafa; border: 1px solid #e2e8f0;
    border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;
}
.saldo-locked-head { display: flex; align-items: flex-start; gap: 0.875rem; margin-bottom: 1rem; }
.saldo-icon-locked {
    width: 38px; height: 38px; background: #f1f5f9; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #94a3b8; flex-shrink: 0;
}
.saldo-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    border: 1px solid #e2e8f0; border-radius: 10px;
    overflow: hidden; background: #fff;
}
.saldo-grid-item { padding: 0.7rem 1rem; border-right: 1px solid #e2e8f0; }
.saldo-grid-item:last-child { border-right: none; }
.saldo-grid-label { font-size: 0.63rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #94a3b8; display: block; margin-bottom: 3px; }
.saldo-grid-value { font-size: 0.88rem; font-weight: 700; color: #1e293b; }
.saldo-grid-value.accent { color: #2563eb; }

/* ── Lock notice ── */
.saldo-lock-notice {
    display: flex; align-items: center; gap: 0.4rem; margin-top: 0.75rem;
    font-size: 0.73rem; color: #92400e; background: #fef3c7;
    border: 1px solid #fde68a; border-radius: 7px; padding: 0.45rem 0.75rem;
}

/* ── Preview bar ── */
.saldo-preview-bar {
    display: none; align-items: center; gap: 0.6rem;
    margin-top: 0.875rem; padding: 0.55rem 0.875rem;
    background: rgba(37,99,235,0.06); border: 1px solid rgba(37,99,235,0.18);
    border-radius: 8px; font-size: 0.775rem;
}
.saldo-preview-bar.show { display: flex; }
.saldo-preview-eq { color: #64748b; }
.saldo-preview-val { font-weight: 700; color: #1d4ed8; }

/* ── Input group ── */
.input-group-text {
    font-size: 0.78rem !important; color: #64748b !important;
    background: #f8fafc !important; border-color: #cbd5e1 !important;
    min-width: 36px; justify-content: center;
}

/* ── Actions ── */
.form-footer {
    display: flex; align-items: center; gap: 0.75rem;
    padding-top: 0.5rem; border-top: 1px solid #f1f5f9; margin-top: 0;
}
</style>
@endpush


@section('content')
<div class="row justify-content-center">
<div class="col-12 col-xl-10">

    {{-- Page Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('umkm.bahan.index') }}"
           style="width:32px;height:32px;border-radius:8px;background:#f1f5f9;border:none;display:flex;align-items:center;justify-content:center;color:#64748b;text-decoration:none;flex-shrink:0;transition:background .15s;"
           onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
            <i data-feather="arrow-left" style="width:15px;height:15px;"></i>
        </a>
        <div>
            <h1 class="h3 mb-0"><strong>Edit</strong> Bahan Baku</h1>
            <p class="text-muted mb-0" style="font-size:0.78rem; margin-top:2px;">Perbarui informasi bahan baku ini.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-3" style="font-size:0.8rem;">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('umkm.bahan.update', $bahan->id) }}">
        @csrf
        @method('PUT')

        {{-- Info Dasar + Keterangan dalam satu card --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body" style="padding:1.25rem 1.5rem;">
                <p class="form-section-label">Informasi Dasar</p>

                <div class="row g-3 mb-3">
                    <div class="col-auto">
                        <label class="form-label">Kode Bahan</label>
                        <div class="kode-badge-input">
                            <span class="dot"></span>
                            <span>{{ $bahan->kode_bahan }}</span>
                        </div>
                        <div class="form-text" style="white-space:nowrap;">Tidak dapat diubah.</div>
                    </div>
                    <div class="col">
                        <label class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_bahan"
                               class="form-control @error('nama_bahan') is-invalid @enderror"
                               value="{{ old('nama_bahan', $bahan->nama_bahan) }}" required autofocus>
                        @error('nama_bahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2" style="min-width:120px;">
                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                        <input type="text" name="satuan"
                               class="form-control @error('satuan') is-invalid @enderror"
                               value="{{ old('satuan', $bahan->satuan) }}" required>
                        @error('satuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <p class="form-section-label">Catatan Tambahan</p>
                <div class="mb-0">
                    <label class="form-label">Keterangan <span class="text-muted" style="font-size:0.72rem;">(opsional)</span></label>
                    <textarea name="keterangan" class="form-control" rows="2"
                              placeholder="Contoh: Beli di supplier A, kualitas ekspor, dsb.">{{ old('keterangan', $bahan->keterangan) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Saldo Awal --}}
        @if($hasTransaksi)
            {{-- LOCKED — sudah ada transaksi --}}
            <div class="saldo-card locked">
                <div class="saldo-card-header">
                    <div class="saldo-icon gray">
                        <i data-feather="lock"></i>
                    </div>
                    <div>
                        <p class="saldo-title">Saldo Awal Persediaan</p>
                        <p class="saldo-desc">Terkunci karena bahan ini sudah memiliki transaksi berjalan.</p>
                    </div>
                </div>

                <div class="saldo-grid">
                    <div class="saldo-grid-item">
                        <span class="saldo-grid-label">Qty Awal</span>
                        <span class="saldo-grid-value">{{ format_angka($bahan->stok_awal) }} {{ $bahan->satuan }}</span>
                    </div>
                    <div class="saldo-grid-item">
                        <span class="saldo-grid-label">Harga / Unit</span>
                        <span class="saldo-grid-value">{{ rupiah($mutasiSaldoAwal?->harga_unit ?? 0) }}</span>
                    </div>
                    <div class="saldo-grid-item">
                        <span class="saldo-grid-label">Total Nilai Awal</span>
                        <span class="saldo-grid-value accent">{{ rupiah($bahan->stok_awal * ($mutasiSaldoAwal?->harga_unit ?? 0)) }}</span>
                    </div>
                </div>

                <div class="saldo-lock-notice">
                    <i data-feather="info" style="width:13px;height:13px;flex-shrink:0;"></i>
                    Untuk koreksi stok, gunakan fitur penyesuaian stok terpisah.
                </div>
            </div>

        @else
            {{-- EDITABLE — belum ada transaksi --}}
            <div class="saldo-section">
                <div class="saldo-section-head">
                    <div class="saldo-icon-box">
                        <i data-feather="layers"></i>
                    </div>
                    <div>
                        <p class="saldo-section-title">Saldo Awal Persediaan</p>
                        <p class="saldo-section-desc">
                            Isi <em>Qty</em> dan salah satu dari <strong>Harga/Unit</strong> atau <strong>Total Nilai</strong> —
                            sistem menghitung yang lain otomatis. Setelah ada transaksi, field ini terkunci.
                        </p>
                    </div>
                </div>

                <div class="saldo-fields">
                    <div class="field-group">
                        <label class="form-label">Qty Awal</label>
                        <input type="number" step="0.001" min="0" name="stok_awal"
                               id="stok_awal" class="form-control"
                               value="{{ old('stok_awal', $bahan->stok_awal) }}">
                        <span class="field-hint">Jumlah stok awal</span>
                    </div>
                    <div class="field-group">
                        <label class="form-label">Harga / Unit</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="harga_display" class="form-control"
                                   placeholder="0" inputmode="numeric" autocomplete="off">
                            <input type="hidden" name="harga_unit_awal" id="harga_unit_awal"
                                   value="{{ old('harga_unit_awal', $mutasiSaldoAwal?->harga_unit ?? 0) }}">
                        </div>
                        <span class="field-hint">Isi salah satu: harga/unit <strong>atau</strong> total nilai</span>
                    </div>
                    <div class="field-group">
                        <label class="form-label">Total Nilai</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="nilai_display" class="form-control"
                                   placeholder="0" inputmode="numeric" autocomplete="off">
                            <input type="hidden" id="total_nilai_awal">
                        </div>
                        <span class="field-hint">Dihitung otomatis dari Qty × Harga</span>
                    </div>
                </div>

                <div class="saldo-preview-bar" id="preview_nilai">
                    <i data-feather="check-circle" style="width:13px;height:13px;color:#2563eb;flex-shrink:0;"></i>
                    <span class="saldo-preview-eq">Akan dicatat:</span>
                    <span class="saldo-preview-val" id="preview_text">—</span>
                </div>
            </div>
        @endif

        {{-- Keterangan --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body" style="padding:1.25rem;">
                <p class="form-section-label">Catatan Tambahan</p>
                <div class="mb-0">
                    <label class="form-label">Keterangan <span class="text-muted" style="font-size:0.72rem;">(opsional)</span></label>
                    <textarea name="keterangan" class="form-control" rows="2"
                              placeholder="Contoh: Beli di supplier A, kualitas ekspor, dsb.">{{ old('keterangan', $bahan->keterangan) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="form-footer">
            <button type="submit" class="btn btn-primary">
                <i data-feather="save" style="width:13px;height:13px;margin-right:4px;"></i>
                Simpan Perubahan
            </button>
            <a href="{{ route('umkm.bahan.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div> {{-- /col --}}
</div> {{-- /row --}}
@endsection

@unless($hasTransaksi)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();

    const qtyEl      = document.getElementById('stok_awal');
    const hargaDisp  = document.getElementById('harga_display');
    const hargaRaw   = document.getElementById('harga_unit_awal');
    const nilaiDisp  = document.getElementById('nilai_display');
    const nilaiRaw   = document.getElementById('total_nilai_awal');
    const previewBar = document.getElementById('preview_nilai');
    const previewTxt = document.getElementById('preview_text');
    let locked = false;

    function fmtNum(n) { return new Intl.NumberFormat('id-ID').format(Math.round(n || 0)); }
    function fmtRp(n)  { return 'Rp\u00a0' + fmtNum(n); }
    function parseRaw(str) { return parseInt(String(str).replace(/\./g, '').replace(/[^\d]/g, '')) || 0; }
    function applyFormat(displayEl, rawEl) {
        const raw = parseRaw(displayEl.value);
        displayEl.value = raw > 0 ? fmtNum(raw) : '';
        if (rawEl) rawEl.value = raw;
        return raw;
    }

    /* ── Preset from saved values ── */
    (function initValues() {
        const h = parseInt('{{ intval($mutasiSaldoAwal?->harga_unit ?? 0) }}') || 0;
        if (h > 0) { hargaDisp.value = fmtNum(h); hargaRaw.value = h; }
        const q = parseFloat('{{ $bahan->stok_awal ?? 0 }}') || 0;
        if (h > 0 && q > 0) {
            nilaiDisp.value = fmtNum(q * h);
            nilaiRaw.value  = Math.round(q * h);
        }
    })();

    function updatePreview() {
        const qty   = parseFloat(qtyEl.value) || 0;
        const harga = parseRaw(hargaDisp.value);
        if (qty > 0 && harga > 0) {
            previewBar.classList.add('show');
            previewTxt.innerHTML = qty.toLocaleString('id-ID') + ' satuan \u00d7 ' + fmtRp(harga) + ' = <strong>' + fmtRp(qty * harga) + '</strong>';
        } else {
            previewBar.classList.remove('show');
        }
    }

    hargaDisp.addEventListener('input', function () {
        if (locked) return; locked = true;
        const harga = applyFormat(this, hargaRaw);
        const qty   = parseFloat(qtyEl.value) || 0;
        if (qty > 0) { const t = Math.round(qty * harga); nilaiDisp.value = t > 0 ? fmtNum(t) : ''; nilaiRaw.value = t; }
        locked = false; updatePreview();
    });
    hargaDisp.addEventListener('blur', function () { applyFormat(this, hargaRaw); updatePreview(); });

    nilaiDisp.addEventListener('input', function () {
        if (locked) return; locked = true;
        const total = applyFormat(this, nilaiRaw);
        const qty   = parseFloat(qtyEl.value) || 0;
        if (qty > 0 && total > 0) { const h = Math.round(total / qty); hargaDisp.value = fmtNum(h); hargaRaw.value = h; }
        locked = false; updatePreview();
    });
    nilaiDisp.addEventListener('blur', function () { applyFormat(this, nilaiRaw); updatePreview(); });

    qtyEl.addEventListener('input', function () {
        if (locked) return;
        const qty   = parseFloat(this.value) || 0;
        const harga = parseRaw(hargaDisp.value);
        if (harga > 0) { const t = Math.round(qty * harga); nilaiDisp.value = t > 0 ? fmtNum(t) : ''; nilaiRaw.value = t; }
        updatePreview();
    });

    document.querySelector('form').addEventListener('submit', function () {
        hargaRaw.value = parseRaw(hargaDisp.value);
    });

    updatePreview();
});
</script>
@endpush
@endunless

@if($hasTransaksi)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();
});
</script>
@endpush
@endif

