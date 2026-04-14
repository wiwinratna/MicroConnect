@extends('layouts.umkm')

@section('title', 'Tambah Bahan Baku')

@push('styles')
<style>
/* ── Section label divider ── */
.form-section-label {
    font-size: 0.63rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #94a3b8;
    margin-bottom: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.form-section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e2e8f0;
}

/* ── Kode badge ── */
.kode-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    padding: 0.32rem 0.8rem;
    font-family: 'SF Mono', 'Fira Code', ui-monospace, monospace;
    font-size: 0.78rem;
    color: #475569;
    letter-spacing: 0.04em;
}
.kode-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: #94a3b8; flex-shrink: 0; }

/* ── Saldo Awal section ── */
.saldo-section {
    background: linear-gradient(145deg, #eff6ff 0%, #f8fafc 100%);
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.saldo-section-head {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(191,219,254,0.6);
    margin-bottom: 1rem;
}
.saldo-icon-box {
    width: 38px; height: 38px;
    background: #dbeafe;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #2563eb;
    flex-shrink: 0;
}
.saldo-icon-box svg { width: 17px; height: 17px; }
.saldo-section-title { font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0 0 3px; }
.saldo-section-desc  { font-size: 0.73rem; color: #64748b; margin: 0; line-height: 1.55; }

/* ── Saldo fields row ── */
.saldo-fields {
    display: grid;
    grid-template-columns: 1.1fr 1.4fr 1.4fr;
    gap: 0.875rem;
    align-items: start;
}

/* ── Field group (label + input stacked) ── */
.field-group { display: flex; flex-direction: column; }
.field-group .form-label { font-size: 0.75rem; font-weight: 600; color: #475569; margin-bottom: 0.3rem; }
.field-group .field-hint { font-size: 0.67rem; color: #94a3b8; margin-top: 4px; }

/* ── Input group fix ── */
.input-group-text {
    font-size: 0.78rem !important;
    color: #64748b !important;
    background: #f8fafc !important;
    border-color: #cbd5e1 !important;
    min-width: 36px;
    justify-content: center;
}


/* ── Rp inline prefix (elegant, no separate box) ── */
.rp-input-wrap {
    position: relative;
    display: block;
}
.rp-prefix {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.75rem;
    font-weight: 600;
    color: #94a3b8;
    pointer-events: none;
    z-index: 2;
    user-select: none;
    letter-spacing: 0.02em;
}
.rp-input {
    padding-left: 2rem !important;
}
.rp-input::placeholder { color: #cbd5e1; }

/* ── Preview bar ── */
.saldo-preview-bar {
    display: none;
    align-items: center;
    gap: 0.6rem;
    margin-top: 0.875rem;
    padding: 0.55rem 0.875rem;
    background: rgba(37,99,235,0.06);
    border: 1px solid rgba(37,99,235,0.18);
    border-radius: 8px;
    font-size: 0.775rem;
}
.saldo-preview-bar.show { display: flex; }
.saldo-preview-eq { color: #64748b; }
.saldo-preview-val { font-weight: 700; color: #1d4ed8; }

/* ── Action row ── */
.form-footer {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-top: 0.5rem;
    border-top: 1px solid #f1f5f9;
    margin-top: 0;
}
/* ── Satuan autocomplete ── */
.satuan-ac-wrap { position: relative; }
.satuan-ac-dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    box-shadow: 0 6px 20px rgba(15,23,42,.1);
    z-index: 999;
    overflow: hidden;
    max-height: 220px;
    overflow-y: auto;
}
.satuan-ac-dropdown.open { display: block; }
.satuan-ac-option {
    padding: 0.42rem 0.875rem;
    font-size: 0.8rem;
    color: #334155;
    cursor: pointer;
    transition: background .1s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.satuan-ac-option:hover,
.satuan-ac-option.focused { background: #f0f7ff; color: #1d4ed8; }
.satuan-ac-option .ac-badge {
    font-size: 0.63rem;
    font-weight: 700;
    color: #94a3b8;
    background: #f1f5f9;
    border-radius: 4px;
    padding: 1px 5px;
    font-family: 'SF Mono', 'Fira Code', monospace;
    letter-spacing: 0.03em;
    margin-left: auto;
    flex-shrink: 0;
}
.satuan-ac-empty {
    padding: 0.6rem 0.875rem;
    font-size: 0.78rem;
    color: #94a3b8;
    font-style: italic;
}
</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-12 col-xl-10">

    {{-- Page Header --}}
    <div class="mb-4">
        <a href="{{ route('umkm.bahan.index') }}"
           style="display:inline-flex;align-items:center;gap:0.35rem;font-size:0.775rem;font-weight:500;color:#64748b;text-decoration:none;margin-bottom:0.75rem;transition:color .15s;"
           onmouseover="this.style.color='#334155'" onmouseout="this.style.color='#64748b'">
            <i data-feather="arrow-left" style="width:13px;height:13px;"></i>
            Kembali ke Daftar Bahan Baku
        </a>
        <h1 class="h3 mb-0">Tambah Bahan Baku <strong>Baru</strong></h1>
        <p class="text-muted mb-0" style="font-size:0.78rem;margin-top:2px;">Isi informasi bahan baku dan stok awal bila tersedia.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-3 mb-3" style="font-size:0.8rem;">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('umkm.bahan.store') }}">
        @csrf
        <input type="hidden" name="kode_bahan" value="{{ $kodeBaru }}">

        {{-- ═══ CARD: Info Dasar + Keterangan ═══ --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body" style="padding:1.25rem 1.5rem;">

                <p class="form-section-label">Informasi Dasar</p>
                {{-- Nama + Satuan dalam satu row --}}
                <div class="row g-3 mb-3">
                    <div class="col">
                        <label class="form-label">Nama Bahan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_bahan"
                               class="form-control @error('nama_bahan') is-invalid @enderror"
                               value="{{ old('nama_bahan') }}"
                               placeholder="Contoh: Tepung Terigu, Gula Pasir…"
                               required autofocus>
                        @error('nama_bahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2" style="min-width:130px;">
                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                        <div class="satuan-ac-wrap" id="satuanWrap">
                            <input type="text"
                                   name="satuan"
                                   id="satuan_input"
                                   class="form-control @error('satuan') is-invalid @enderror"
                                   value="{{ old('satuan') }}"
                                   placeholder="Cari atau ketik…"
                                   autocomplete="off"
                                   required>
                            <div class="satuan-ac-dropdown" id="satuanDropdown"></div>
                        </div>
                        @error('satuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <p class="form-section-label">Keterangan</p>
                <div class="mb-0">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"
                              placeholder="Contoh: Supplier utama PT. Maju, kualitas ekspor, dsb.">{{ old('keterangan') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ═══ SALDO AWAL ═══ --}}
        <div class="saldo-section">
            <div class="saldo-section-head">
                <div class="saldo-icon-box">
                    <i data-feather="layers"></i>
                </div>
                <div>
                    <p class="saldo-section-title">Saldo Awal Persediaan</p>
                    <p class="saldo-section-desc">Isi Qty dan salah satu: Harga/Unit atau Total Nilai.</p>
                </div>
            </div>

            {{-- 3 field dalam grid seimbang --}}
            <div class="saldo-fields">

                {{-- Qty --}}
                <div class="field-group">
                    <label class="form-label">Qty Awal</label>
                    <input type="number" step="0.001" min="0"
                           name="stok_awal" id="stok_awal"
                           class="form-control"
                           value="{{ old('stok_awal', 0) }}"
                           placeholder="0">

                </div>

                {{-- Harga / Unit --}}
                <div class="field-group">
                    <label class="form-label">Harga / Unit</label>
                    <div class="rp-input-wrap">
                        <span class="rp-prefix">Rp</span>
                        <input type="text" id="harga_display"
                               class="form-control rp-input"
                               placeholder="0"
                               inputmode="numeric"
                               autocomplete="off">
                        <input type="hidden" name="harga_unit_awal" id="harga_unit_awal"
                               value="{{ old('harga_unit_awal', 0) }}">
                    </div>
                </div>

                {{-- Total Nilai --}}
                <div class="field-group">
                    <label class="form-label">Total Nilai</label>
                    <div class="rp-input-wrap">
                        <span class="rp-prefix">Rp</span>
                        <input type="text" id="nilai_display"
                               class="form-control rp-input"
                               placeholder="0"
                               inputmode="numeric"
                               autocomplete="off">
                        <input type="hidden" id="total_nilai_awal">
                    </div>
                </div>
            </div>

            {{-- Preview bar --}}
            <div class="saldo-preview-bar" id="preview_nilai">
                <i data-feather="check-circle" style="width:13px;height:13px;color:#2563eb;flex-shrink:0;"></i>
                <span class="saldo-preview-eq">Akan dicatat:</span>
                <span class="saldo-preview-val" id="preview_text">—</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="form-footer">
            <button type="submit" class="btn btn-primary">
                <i data-feather="save" style="width:13px;height:13px;margin-right:4px;"></i>
                Simpan Bahan Baku
            </button>
            <a href="{{ route('umkm.bahan.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>

</div> {{-- /col --}}
</div> {{-- /row --}}
@endsection

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

    /* ── Format helpers ── */
    function fmtNum(n) {
        return new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
    }
    function fmtRp(n) {
        return 'Rp\u00a0' + fmtNum(n);
    }
    function parseRaw(str) {
        // Strip dots (Indonesian thousand sep) then parse
        return parseInt(String(str).replace(/\./g, '').replace(/[^\d]/g, '')) || 0;
    }
    function applyFormat(displayEl, rawEl) {
        const raw = parseRaw(displayEl.value);
        displayEl.value = raw > 0 ? fmtNum(raw) : '';
        if (rawEl) rawEl.value = raw;
        return raw;
    }

    /* ── Preset from old() values ── */
    (function initOld() {
        const h = parseInt('{{ old("harga_unit_awal", 0) }}') || 0;
        if (h > 0) {
            hargaDisp.value = fmtNum(h);
            hargaRaw.value  = h;
        }
        const q = parseFloat('{{ old("stok_awal", 0) }}') || 0;
        if (h > 0 && q > 0) {
            nilaiDisp.value = fmtNum(q * h);
            nilaiRaw.value  = Math.round(q * h);
        }
    })();

    /* ── Preview update ── */
    function updatePreview() {
        const qty   = parseFloat(qtyEl.value) || 0;
        const harga = parseRaw(hargaDisp.value);
        if (qty > 0 && harga > 0) {
            previewBar.classList.add('show');
            previewTxt.innerHTML =
                qty.toLocaleString('id-ID') + ' satuan \u00d7 ' + fmtRp(harga) +
                ' = <strong>' + fmtRp(qty * harga) + '</strong>';
        } else {
            previewBar.classList.remove('show');
        }
    }

    /* ── Harga display → sync nilai display & raw ── */
    hargaDisp.addEventListener('input', function () {
        if (locked) return;
        locked = true;
        const harga = applyFormat(this, hargaRaw);
        const qty   = parseFloat(qtyEl.value) || 0;
        if (qty > 0) {
            const total = Math.round(qty * harga);
            nilaiDisp.value = total > 0 ? fmtNum(total) : '';
            nilaiRaw.value  = total;
        }
        locked = false;
        updatePreview();
    });
    hargaDisp.addEventListener('blur', function () {
        applyFormat(this, hargaRaw);
        updatePreview();
    });

    /* ── Nilai display → back-calculate harga ── */
    nilaiDisp.addEventListener('input', function () {
        if (locked) return;
        locked = true;
        const total = applyFormat(this, nilaiRaw);
        const qty   = parseFloat(qtyEl.value) || 0;
        if (qty > 0 && total > 0) {
            const harga = Math.round(total / qty);
            hargaDisp.value = fmtNum(harga);
            hargaRaw.value  = harga;
        }
        locked = false;
        updatePreview();
    });
    nilaiDisp.addEventListener('blur', function () {
        applyFormat(this, nilaiRaw);
        updatePreview();
    });

    /* ── Qty → sync nilai ── */
    qtyEl.addEventListener('input', function () {
        if (locked) return;
        const qty   = parseFloat(this.value) || 0;
        const harga = parseRaw(hargaDisp.value);
        if (harga > 0) {
            const total = Math.round(qty * harga);
            nilaiDisp.value = total > 0 ? fmtNum(total) : '';
            nilaiRaw.value  = total;
        }
        updatePreview();
    });

    /* ── On submit: make sure hidden raw is up-to-date ── */
    document.querySelector('form').addEventListener('submit', function () {
        hargaRaw.value = parseRaw(hargaDisp.value);
    });

    updatePreview();
});

/* ── Satuan autocomplete ── */
(function() {
    const UNITS = [
        { label: 'Kilogram', value: 'kg' },
        { label: 'Gram',     value: 'gram' },
        { label: 'Ons',      value: 'ons' },
        { label: 'Miligram', value: 'mg' },
        { label: 'Liter',    value: 'liter' },
        { label: 'Mililiter',value: 'ml' },
        { label: 'Galon',    value: 'galon' },
        { label: 'Pcs / Buah', value: 'pcs' },
        { label: 'Buah',     value: 'buah' },
        { label: 'Lusin',    value: 'lusin' },
        { label: 'Kodi',     value: 'kodi' },
        { label: 'Lembar',   value: 'lembar' },
        { label: 'Batang',   value: 'batang' },
        { label: 'Roll / Gulungan', value: 'roll' },
        { label: 'Meter',    value: 'meter' },
        { label: 'Centimeter', value: 'cm' },
        { label: 'Pack / Pak', value: 'pack' },
        { label: 'Box / Kotak', value: 'box' },
        { label: 'Karton',   value: 'karton' },
        { label: 'Botol',    value: 'botol' },
        { label: 'Bungkus',  value: 'bungkus' },
        { label: 'Sachet',   value: 'sachet' },
        { label: 'Kaleng',   value: 'kaleng' },
        { label: 'Loyang',   value: 'loyang' },
        { label: 'Porsi',    value: 'porsi' },
        { label: 'Set',      value: 'set' },
        { label: 'Unit',     value: 'unit' },
    ];

    const input    = document.getElementById('satuan_input');
    const dropdown = document.getElementById('satuanDropdown');
    if (!input || !dropdown) return;

    let focusedIdx = -1;

    function renderOptions(query) {
        const q = query.trim().toLowerCase();
        const filtered = q
            ? UNITS.filter(u => u.value.includes(q) || u.label.toLowerCase().includes(q))
            : UNITS;

        if (!filtered.length) {
            dropdown.innerHTML = '<div class="satuan-ac-empty">Ketik satuan kustom lalu tekan Enter.</div>';
        } else {
            dropdown.innerHTML = filtered.map((u, i) =>
                `<div class="satuan-ac-option" data-value="${u.value}" role="option">
                    ${u.label}
                    <span class="ac-badge">${u.value}</span>
                 </div>`
            ).join('');
            dropdown.querySelectorAll('.satuan-ac-option').forEach(el => {
                el.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    input.value = this.dataset.value;
                    close();
                });
            });
        }
        focusedIdx = -1;
        dropdown.classList.add('open');
    }

    function close() {
        dropdown.classList.remove('open');
        focusedIdx = -1;
    }

    function moveFocus(dir) {
        const opts = dropdown.querySelectorAll('.satuan-ac-option');
        if (!opts.length) return;
        opts.forEach(o => o.classList.remove('focused'));
        focusedIdx = Math.max(0, Math.min(opts.length - 1, focusedIdx + dir));
        opts[focusedIdx].classList.add('focused');
        opts[focusedIdx].scrollIntoView({ block: 'nearest' });
    }

    input.addEventListener('focus', () => renderOptions(input.value));
    input.addEventListener('input', () => renderOptions(input.value));
    input.addEventListener('keydown', function(e) {
        if (!dropdown.classList.contains('open')) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); moveFocus(1); }
        if (e.key === 'ArrowUp')   { e.preventDefault(); moveFocus(-1); }
        if (e.key === 'Enter') {
            const focused = dropdown.querySelector('.satuan-ac-option.focused');
            if (focused) { e.preventDefault(); input.value = focused.dataset.value; close(); }
        }
        if (e.key === 'Escape') close();
    });

    document.addEventListener('click', function(e) {
        if (!document.getElementById('satuanWrap').contains(e.target)) close();
    });
})();
</script>
@endpush
