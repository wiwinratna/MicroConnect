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
    color: var(--mn-text-faint);
    margin-bottom: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.form-section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--mn-border-color);
}

/* ── Kode badge ── */
.kode-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    background: var(--mn-bg-soft);
    border: 1px solid var(--mn-border-color);
    border-radius: 7px;
    padding: 0.32rem 0.8rem;
    font-family: 'SF Mono', 'Fira Code', ui-monospace, monospace;
    font-size: 0.78rem;
    color: var(--mn-text-muted);
    letter-spacing: 0.04em;
}
.kode-badge .dot { width: 6px; height: 6px; border-radius: 50%; background: var(--mn-text-faint); flex-shrink: 0; }

/* ── Saldo Awal section ── */
.saldo-section {
    background: linear-gradient(145deg, var(--mn-bg-soft) 0%, var(--mn-bg-body) 100%);
    border: 1px solid var(--mn-soft-teal);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
}
.saldo-section-head {
    display: flex;
    align-items: flex-start;
    gap: 0.875rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(169, 207, 197,0.6);
    margin-bottom: 1rem;
}
.saldo-icon-box {
    width: 38px; height: 38px;
    background: var(--mn-primary-soft);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: var(--mn-primary);
    flex-shrink: 0;
}
.saldo-icon-box svg { width: 17px; height: 17px; }
.saldo-section-title { font-size: 0.875rem; font-weight: 700; color: var(--mn-text-main); margin: 0 0 3px; }
.saldo-section-desc  { font-size: 0.73rem; color: var(--mn-text-muted); margin: 0; line-height: 1.55; }

/* ── Saldo fields row ── */
.saldo-fields {
    display: grid;
    grid-template-columns: 1.1fr 1.4fr 1.4fr;
    gap: 0.875rem;
    align-items: start;
}

/* ── Field group (label + input stacked) ── */
.field-group { display: flex; flex-direction: column; }
.field-group .form-label { font-size: 0.75rem; font-weight: 600; color: var(--mn-text-muted); margin-bottom: 0.3rem; }
.field-group .field-hint { font-size: 0.67rem; color: var(--mn-text-faint); margin-top: 4px; }

/* ── Input group fix ── */
.input-group-text {
    font-size: 0.78rem !important;
    color: var(--mn-text-muted) !important;
    background: var(--mn-bg-body) !important;
    border-color: var(--mn-border-strong) !important;
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
    color: var(--mn-text-faint);
    pointer-events: none;
    z-index: 2;
    user-select: none;
    letter-spacing: 0.02em;
}
.rp-input {
    padding-left: 2rem !important;
}
.rp-input::placeholder { color: var(--mn-border-strong); }

/* ── Preview bar ── */
.saldo-preview-bar {
    display: none;
    align-items: center;
    gap: 0.6rem;
    margin-top: 0.875rem;
    padding: 0.55rem 0.875rem;
    background: rgba(23, 107, 101,0.06);
    border: 1px solid rgba(23, 107, 101,0.18);
    border-radius: 8px;
    font-size: 0.775rem;
}
.saldo-preview-bar.show { display: flex; }
.saldo-preview-eq { color: var(--mn-text-muted); }
.saldo-preview-val { font-weight: 700; color: var(--mn-primary-hover); }

/* ── Action row ── */
.form-footer {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-top: 0.5rem;
    border-top: 1px solid var(--mn-bg-soft);
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
    border: 1px solid var(--mn-border-color);
    border-radius: 9px;
    box-shadow: 0 6px 20px rgba(38, 59, 56,.1);
    z-index: 999;
    overflow: hidden;
    max-height: 220px;
    overflow-y: auto;
}
.satuan-ac-dropdown.open { display: block; }
.satuan-ac-option {
    padding: 0.42rem 0.875rem;
    font-size: 0.8rem;
    color: var(--mn-text-main);
    cursor: pointer;
    transition: background .1s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.satuan-ac-option:hover,
.satuan-ac-option.focused { background: var(--mn-bg-soft); color: var(--mn-primary-hover); }
.satuan-ac-option .ac-badge {
    font-size: 0.63rem;
    font-weight: 700;
    color: var(--mn-text-faint);
    background: var(--mn-bg-soft);
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
    color: var(--mn-text-faint);
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
           style="display:inline-flex;align-items:center;gap:0.35rem;font-size:0.775rem;font-weight:500;color:var(--mn-text-muted);text-decoration:none;margin-bottom:0.75rem;transition:color .15s;"
           onmouseover="this.style.color='var(--mn-text-main)'" onmouseout="this.style.color='var(--mn-text-muted)'">
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
                                   placeholder="mis. kg"
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
                <i data-feather="check-circle" style="width:13px;height:13px;color:var(--mn-primary);flex-shrink:0;"></i>
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

@include('umkm.bahan.partials.create-script')
