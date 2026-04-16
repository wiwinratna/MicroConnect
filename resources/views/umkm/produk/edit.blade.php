@extends('layouts.umkm')

@section('title', 'Edit Produk')

@push('styles')
<style>
/* ── Section label ── */
.section-label {
    font-size: 0.63rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 1rem;
    display: flex; align-items: center; gap: 0.5rem;
}
.section-label::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
.section-label .kode-ghost {
    text-transform: none; letter-spacing: 0; font-weight: 400;
    font-size: .68rem; color: #cbd5e1; font-family: monospace;
}

/* ── Rp inline prefix ── */
.rp-wrap { position: relative; }
.rp-wrap .rp-pfx {
    position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
    font-size: .75rem; font-weight: 600; color: #94a3b8;
    pointer-events: none; z-index: 2;
}
.rp-wrap input { padding-left: 2rem !important; }

/* ── HPP hint ── */
.hpp-hint {
    margin-top: 7px;
    display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
}
.hpp-hint-text { font-size: .73rem; color: #64748b; }
.hpp-hint-text strong { color: #334155; }
.hpp-est-badge {
    font-size: .6rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;
    background: #fef3c7; color: #92400e; border: 1px solid #fde68a;
}
.btn-sim {
    margin-left: auto;
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .72rem; font-weight: 600; color: #2563eb;
    background: #eff6ff; border: 1px solid #bfdbfe;
    border-radius: 6px; padding: .2rem .65rem; cursor: pointer;
    transition: background .15s; white-space: nowrap; text-decoration: none;
}
.btn-sim:hover { background: #dbeafe; }

/* ── Custom searchable autocomplete (used for ALL satuan fields) ── */
.ac-wrap { position: relative; }
.ac-dropdown {
    display: none; position: absolute;
    top: calc(100% + 3px); left: 0; right: 0; z-index: 999;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 9px; box-shadow: 0 6px 20px rgba(15,23,42,.1);
    max-height: 220px; overflow-y: auto;
}
.ac-dropdown.open { display: block; }
.ac-option {
    display: flex; align-items: center; justify-content: space-between;
    padding: .38rem .875rem; font-size: .8rem; color: #334155;
    cursor: pointer; transition: background .1s;
}
.ac-option:hover, .ac-option.focused { background: #f0f7ff; color: #1d4ed8; }
.ac-option .ac-val {
    font-size: .63rem; font-weight: 700; color: #94a3b8;
    background: #f1f5f9; border-radius: 4px; padding: 1px 5px;
    font-family: monospace; letter-spacing: .03em;
}
.ac-empty {
    padding: .5rem .875rem; font-size: .78rem;
    color: #94a3b8; font-style: italic;
}

/* ── Foto upload zone ── */
.foto-zone {
    border: 1.5px dashed #cbd5e1; border-radius: 10px;
    padding: .875rem; cursor: pointer; transition: border-color .15s, background .15s;
    text-align: center; min-height: 96px;
    display: flex; align-items: center; justify-content: center;
}
.foto-zone:hover { border-color: #93c5fd; background: #f0f7ff; }
.foto-zone input[type="file"] { display: none; }
.foto-placeholder { pointer-events: none; }
.foto-placeholder svg { color: #94a3b8; margin-bottom: 5px; }
.foto-placeholder-text { font-size: .73rem; color: #64748b; line-height: 1.4; }
.foto-placeholder-text strong { display: block; color: #334155; }
.foto-preview-row {
    display: flex; align-items: center; gap: .75rem; width: 100%; text-align: left;
}
.foto-prev-thumb {
    width: 56px; height: 56px; border-radius: 8px;
    object-fit: cover; border: 1px solid #e2e8f0; flex-shrink: 0;
}
.foto-prev-meta { flex: 1; }
.foto-prev-name { font-size: .75rem; font-weight: 600; color: #334155; display: block; }
.foto-prev-size { font-size: .7rem; color: #94a3b8; }
.btn-foto-clear {
    font-size: .7rem; color: #ef4444; background: none; border: none;
    padding: 0; cursor: pointer; text-decoration: underline; display: block; margin-top: 2px;
}
.foto-existing {
    display: flex; align-items: center; gap: .625rem; margin-top: .5rem;
}
.foto-existing img {
    width: 52px; height: 52px; border-radius: 7px;
    object-fit: cover; border: 1px solid #e2e8f0;
}
.foto-existing-label { font-size: .72rem; color: #64748b; line-height: 1.4; }

/* ── Komposisi ── */
.komposisi-header {
    display: grid; grid-template-columns: 1fr 110px 1fr 36px;
    gap: .75rem; padding: 0 1rem .4rem;
    font-size: .62rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: #b0bec5;
}
.komposisi-row {
    display: grid; grid-template-columns: 1fr 110px 1fr 36px;
    gap: .75rem; align-items: center;
    background: #fafafa; border: 1px solid #f0f4f8;
    border-radius: 9px; padding: .75rem 1rem;
    margin-bottom: .5rem; transition: border-color .15s;
}
.komposisi-row:hover { border-color: #dbeafe; }
.btn-del {
    width: 32px; height: 32px; border-radius: 7px;
    background: none; border: 1px solid #e2e8f0; color: #94a3b8;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem; font-weight: 500;
    transition: background .12s, color .12s, border-color .12s;
    flex-shrink: 0;
}
.btn-del:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }

/* Bahan autocomplete panel */
.bahan-panel {
    position: absolute; top: calc(100% + 2px); left: 0; right: 0; z-index: 30;
    border-radius: 8px; border: 1px solid #e2e8f0;
    box-shadow: 0 6px 18px rgba(15,23,42,.1); background: #fff;
    max-height: 200px; overflow-y: auto; display: none;
}
.bahan-panel .bahan-opt {
    display: flex; justify-content: space-between; align-items: center;
    padding: .38rem .75rem; font-size: .8rem; cursor: pointer; transition: background .1s;
}
.bahan-panel .bahan-opt:hover { background: #f0f7ff; color: #1d4ed8; }
.bahan-panel .bahan-opt .bo-satuan { font-size: .7rem; color: #94a3b8; }

/* ── Form footer ── */
.form-footer {
    display: flex; align-items: center; gap: .75rem;
    padding-top: 1rem; border-top: 1px solid #f1f5f9;
}

/* ══ Simulasi Modal ══ */
.sim-overlay {
    display: none; position: fixed; inset: 0; z-index: 1055;
    background: rgba(15,23,42,.45); backdrop-filter: blur(3px);
    align-items: center; justify-content: center; padding: 1rem;
}
.sim-overlay.open { display: flex; }
.sim-modal {
    background: #fff; border-radius: 16px;
    width: 100%; max-width: 500px; max-height: 88vh;
    display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 24px 64px rgba(15,23,42,.22);
    animation: simIn .18s ease;
}
@keyframes simIn { from{opacity:0;transform:scale(.96) translateY(8px)} to{opacity:1;transform:none} }
.sim-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9;
}
.sim-title { font-size: .9rem; font-weight: 700; color: #1e293b; }
.sim-x {
    width: 28px; height: 28px; border-radius: 7px; border: none;
    background: #f1f5f9; color: #64748b; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 1rem;
    transition: background .15s;
}
.sim-x:hover { background: #e2e8f0; }
.sim-body { padding: 1.25rem; overflow-y: auto; }
.sim-note {
    font-size: .72rem; color: #64748b; line-height: 1.55;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 8px; padding: .6rem .875rem; margin-bottom: 1rem;
}
.sim-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; margin-top: 1rem; }
.sc { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 9px; padding: .65rem .875rem; }
.sc .sl { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; display: block; margin-bottom: 3px; }
.sc .sv { font-size: .9rem; font-weight: 700; color: #1e293b; }
.sc.full { grid-column: 1/-1; background: linear-gradient(135deg,#1d4ed8,#2563eb); border: none; text-align: center; padding: .875rem; }
.sc.full .sl { color: rgba(255,255,255,.65); }
.sc.full .sv { color: #fff; font-size: 1.3rem; }
.sc.green { background: #f0fdf4; border-color: #bbf7d0; }
.sc.green .sl { color: #16a34a; }
.sc.green .sv { color: #15803d; font-size: .95rem; }
.sc.indigo { background: #eff6ff; border-color: #bfdbfe; }
.sc.indigo .sv { color: #1d4ed8; }
</style>
@endpush

@section('content')

{{-- Page Header --}}
<div class="mb-4">
    <a href="{{ route('umkm.produk.index') }}"
       style="display:inline-flex;align-items:center;gap:.35rem;font-size:.775rem;font-weight:500;color:#64748b;text-decoration:none;margin-bottom:.7rem;transition:color .15s;"
       onmouseover="this.style.color='#334155'" onmouseout="this.style.color='#64748b'">
        <i data-feather="arrow-left" style="width:13px;height:13px;"></i>
        Kembali ke Daftar Produk
    </a>
    <h1 class="h3 mb-0">Edit <strong>Produk</strong></h1>
    <p class="text-muted mb-0" style="font-size:.78rem;margin-top:2px;">Perbarui informasi produk dan komposisi resep.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger rounded-3 mb-3" style="font-size:.8rem;">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('umkm.produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <input type="hidden" name="kode_produk" value="{{ $produk->kode_produk }}">

    {{-- ══ CARD 1: Informasi Produk ══ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <p class="section-label">
                Informasi Produk
                <span class="kode-ghost">{{ $produk->kode_produk }}</span>
            </p>

            {{-- Nama + Satuan --}}
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="nama_produk" class="form-control"
                           value="{{ old('nama_produk', $produk->nama_produk) }}" required autofocus>
                </div>
                <div class="col-md-3" style="min-width:175px;">
                    <label class="form-label">Satuan Produk <span class="text-danger">*</span></label>
                    {{-- Searchable autocomplete: direct text input with custom value allowed --}}
                    <div class="ac-wrap" id="satuanProdukWrap">
                        <input type="text"
                               name="satuan"
                               id="satuanProdukInput"
                               class="form-control"
                               value="{{ old('satuan', $produk->satuan) }}"
                               placeholder="Cari atau ketik satuan…"
                               autocomplete="off"
                               required>
                        <div class="ac-dropdown" id="satuanProdukDropdown"></div>
                    </div>
                </div>
            </div>

            {{-- Harga + Foto + Keterangan --}}
            <div class="row g-3">
                {{-- Harga Jual --}}
                <div class="col-md-4">
                    <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                    <div class="rp-wrap">
                        <span class="rp-pfx">Rp</span>
                        {{-- Display: formatted (text), Hidden: raw number --}}
                        <input type="text" id="harga_display" class="form-control"
                               inputmode="numeric" autocomplete="off"
                               placeholder="0">
                        <input type="hidden" name="harga_jual" id="harga_jual_raw"
                               value="{{ old('harga_jual', $produk->harga_jual) }}">
                    </div>
                    <div class="hpp-hint">
                        <span class="hpp-hint-text">Est. HPP: <strong id="text_hpp_now">{{ rupiah($produk->harga_pokok ?? 0) }}</strong></span>
                        <span class="hpp-est-badge">Estimasi</span>
                        <button type="button" class="btn-sim" onclick="openSim()">
                            <i data-feather="bar-chart-2" style="width:11px;height:11px;"></i>
                            Simulasi
                        </button>
                    </div>
                </div>

                {{-- Foto Upload --}}
                <div class="col-md-4">
                    <label class="form-label">Foto Produk</label>
                    <div class="foto-zone" id="fotoZone" onclick="document.getElementById('fotoFile').click()">
                        <input type="file" name="foto" id="fotoFile" accept="image/*">
                        <div id="fotoPH" class="foto-placeholder">
                            <i data-feather="image" style="width:24px;height:24px;color:#94a3b8;display:block;margin:0 auto 5px;"></i>
                            <div class="foto-placeholder-text">
                                <strong>Klik untuk unggah</strong>
                                JPG, PNG, WebP
                            </div>
                        </div>
                        <div id="fotoPrev" class="foto-preview-row" style="display:none;">
                            <img id="fotoPrevImg" src="" alt="" class="foto-prev-thumb">
                            <div class="foto-prev-meta">
                                <span class="foto-prev-name" id="fotoName">—</span>
                                <span class="foto-prev-size" id="fotoSize"></span>
                                <button type="button" class="btn-foto-clear" onclick="clearFoto(event)">Hapus pilihan</button>
                            </div>
                        </div>
                    </div>
                    @if($produk->foto_path)
                        <div class="foto-existing">
                            <img src="{{ asset('storage/'.$produk->foto_path) }}" alt="Foto produk">
                            <span class="foto-existing-label">Foto saat ini.<br>Unggah baru untuk mengganti.</span>
                        </div>
                    @endif
                </div>

                {{-- Keterangan --}}
                <div class="col-md-4">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="4"
                              placeholder="Deskripsi singkat produk…">{{ old('keterangan', $produk->keterangan) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ CARD 2: Komposisi / Resep ══ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <p class="section-label">
                Komposisi / Resep
                <span style="text-transform:none;letter-spacing:0;font-weight:400;font-size:.7rem;color:#94a3b8;">per 1 unit produk</span>
            </p>

            @if($komposisi->isEmpty())
                <div class="d-flex align-items-center gap-2 mb-3 px-3 py-2 rounded-2"
                     style="background:#fffbeb;border:1px solid #fde68a;font-size:.78rem;color:#92400e;">
                    <i data-feather="alert-triangle" style="width:14px;height:14px;flex-shrink:0;"></i>
                    Belum ada resep. Tambahkan bahan baku di bawah.
                </div>
            @endif

            {{-- Column headers --}}
            <div class="komposisi-header">
                <span>Bahan Baku</span><span>Qty / unit</span><span>Satuan</span><span></span>
            </div>

            <div id="komposisi-wrapper">
                @forelse($komposisi as $k)
                <div class="komposisi-row">
                    <div style="position:relative;">
                        <input type="hidden" name="bahan_id[]" value="{{ $k->bahan_id }}">
                        <input type="text" name="bahan_nama[]" class="form-control form-control-sm bahan-input"
                               value="{{ $k->bahan->nama_bahan ?? '' }}"
                               placeholder="Ketik nama bahan…" autocomplete="off">
                        <div class="bahan-panel"></div>
                    </div>
                    <div>
                        <input type="number" step="0.001" name="qty[]"
                               class="form-control form-control-sm"
                               value="{{ $k->qty }}" placeholder="0">
                    </div>
                    {{-- Satuan resep: ac-wrap with hidden input --}}
                    <div class="ac-wrap resep-satuan-wrap">
                        <input type="text" class="form-control form-control-sm satuan-disp"
                               placeholder="Cari satuan…" autocomplete="off"
                               value="{{ collect($satuanOptions)->firstWhere('value', $k->satuan)['label'] ?? $k->satuan }}">
                        <input type="hidden" name="satuan[]" class="satuan-val" value="{{ $k->satuan }}">
                        <div class="ac-dropdown resep-satuan-dd"></div>
                    </div>
                    <button type="button" class="btn-del remove-row" title="Hapus baris">×</button>
                </div>
                @empty
                <div class="komposisi-row">
                    <div style="position:relative;">
                        <input type="hidden" name="bahan_id[]">
                        <input type="text" name="bahan_nama[]" class="form-control form-control-sm bahan-input"
                               placeholder="Ketik nama bahan…" autocomplete="off">
                        <div class="bahan-panel"></div>
                    </div>
                    <div>
                        <input type="number" step="0.001" name="qty[]"
                               class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="ac-wrap resep-satuan-wrap">
                        <input type="text" class="form-control form-control-sm satuan-disp"
                               placeholder="Cari satuan…" autocomplete="off">
                        <input type="hidden" name="satuan[]" class="satuan-val">
                        <div class="ac-dropdown resep-satuan-dd"></div>
                    </div>
                    <button type="button" class="btn-del remove-row" title="Hapus baris">×</button>
                </div>
                @endforelse
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-row">
                <i data-feather="plus" style="width:12px;height:12px;margin-right:3px;"></i>
                Tambah Bahan
            </button>
        </div>
    </div>

    {{-- Actions --}}
    <div class="form-footer">
        <button type="submit" class="btn btn-primary">
            <i data-feather="save" style="width:13px;height:13px;margin-right:4px;"></i>
            Simpan Perubahan
        </button>
        <a href="{{ route('umkm.produk.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

{{-- ══ SIMULASI MODAL ══ --}}
<div class="sim-overlay" id="simOverlay" onclick="closeSim(event)">
    <div class="sim-modal">
        <div class="sim-head">
            <span class="sim-title">
                <i data-feather="bar-chart-2" style="width:14px;height:14px;color:#2563eb;vertical-align:-.15em;margin-right:5px;"></i>
                Simulasi Harga Jual
            </span>
            <button class="sim-x" onclick="closeSim()">✕</button>
        </div>
        <div class="sim-body">
            <p class="sim-note">
                <strong>Estimasi saja.</strong> Dihitung dari harga beli terakhir bahan resep — bukan HPP aktual.
                Tidak dicatat di jurnal. Simpan perubahan resep sebelum simulasi.
            </p>
            <div class="d-flex align-items-end gap-3">
                <div style="flex:1;">
                    <label class="form-label" style="font-size:.78rem;">Target Margin (%)</label>
                    <input type="number" id="input_margin" class="form-control" value="30" min="0" step="1">
                </div>
                <button type="button" id="btnHitungHpp" class="btn btn-primary" style="height:38px;white-space:nowrap;">
                    Hitung Estimasi
                </button>
            </div>
            <div id="hasilHpp" style="display:none;">
                <div class="sim-grid">
                    <div class="sc"><span class="sl">Biaya Bahan</span><span class="sv" id="res_biaya_bahan">—</span></div>
                    <div class="sc"><span class="sl">Overhead</span><span class="sv" id="res_overhead">—</span></div>
                    <div class="sc full"><span class="sl">HPP Estimasi</span><span class="sv" id="res_hpp_total">—</span></div>
                    <div class="sc green">
                        <span class="sl">Saran Harga (Margin <span id="res_margin_target">30</span>%)</span>
                        <span class="sv" id="res_saran_harga">—</span>
                        <button type="button" class="btn btn-sm btn-success mt-2 w-100" id="btnGunakanHarga">Gunakan Harga Ini</button>
                    </div>
                    <div class="sc indigo">
                        <span class="sl">Margin Aktual</span>
                        <span class="sv" id="res_margin_aktual">—</span>
                        <div style="font-size:.7rem;color:#64748b;margin-top:3px;">dari <span id="res_harga_now">—</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@php
$bahanMaster = isset($bahanBaku)
    ? $bahanBaku->map(fn($b) => ['id' => $b->id, 'nama' => $b->nama_bahan, 'satuan' => $b->satuan])
    : collect();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();

    /* ────────────────────────────────────────
       SHARED DATA
    ──────────────────────────────────────── */
    const bahanMaster   = @json($bahanMaster);
    const satuanOptions = @json($satuanOptions ?? []);  // [{value, label}]

    /* ────────────────────────────────────────
       1. HARGA JUAL – formatted display
    ──────────────────────────────────────── */
    const hargaDisp = document.getElementById('harga_display');
    const hargaRaw  = document.getElementById('harga_jual_raw');

    function fmtID(n) {
        if (!n && n !== 0) return '';
        // Adaptive: no forced decimals
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3,
        }).format(n);
    }
    function parseRaw(str) {
        return parseFloat(String(str).replace(/\./g,'').replace(',','.')) || 0;
    }
    function applyHargaFormat(str) {
        const raw = parseRaw(str);
        hargaDisp.value = raw > 0 ? fmtID(raw) : '';
        hargaRaw.value  = raw;
        return raw;
    }

    // Init from existing value
    const initHarga = parseFloat(hargaRaw.value) || 0;
    if (initHarga > 0) hargaDisp.value = fmtID(initHarga);

    hargaDisp.addEventListener('blur', () => applyHargaFormat(hargaDisp.value));
    hargaDisp.addEventListener('input', function() {
        // keep raw in sync while typing (strip formatting)
        hargaRaw.value = parseRaw(this.value);
    });
    document.querySelector('form').addEventListener('submit', () => {
        hargaRaw.value = parseRaw(hargaDisp.value);
    });

    /* ────────────────────────────────────────
       2. SATUAN PRODUK – custom autocomplete
          (name="satuan" directly on text input,
           custom entry allowed)
    ──────────────────────────────────────── */
    const SATUAN_LIST = [
        {value:'pcs',    label:'Pcs / Buah'},
        {value:'buah',   label:'Buah'},
        {value:'porsi',  label:'Porsi'},
        {value:'unit',   label:'Unit'},
        {value:'set',    label:'Set'},
        {value:'lusin',  label:'Lusin'},
        {value:'kodi',   label:'Kodi'},
        {value:'kg',     label:'Kilogram'},
        {value:'gram',   label:'Gram'},
        {value:'ons',    label:'Ons'},
        {value:'mg',     label:'Miligram'},
        {value:'liter',  label:'Liter'},
        {value:'ml',     label:'Mililiter'},
        {value:'galon',  label:'Galon'},
        {value:'lembar', label:'Lembar'},
        {value:'batang', label:'Batang'},
        {value:'roll',   label:'Roll'},
        {value:'meter',  label:'Meter'},
        {value:'cm',     label:'Centimeter'},
        {value:'pack',   label:'Pack / Pak'},
        {value:'box',    label:'Box / Kotak'},
        {value:'karton', label:'Karton'},
        {value:'botol',  label:'Botol'},
        {value:'bungkus',label:'Bungkus'},
        {value:'sachet', label:'Sachet'},
        {value:'kaleng', label:'Kaleng'},
        {value:'loyang', label:'Loyang'},
    ];

    initSatuanProdukAC();

    function initSatuanProdukAC() {
        const inp = document.getElementById('satuanProdukInput');
        const dd  = document.getElementById('satuanProdukDropdown');
        if (!inp || !dd) return;
        let focused = -1;

        function render(q) {
            const f = q.trim().toLowerCase()
                ? SATUAN_LIST.filter(s => s.value.includes(q.trim().toLowerCase()) || s.label.toLowerCase().includes(q.trim().toLowerCase()))
                : SATUAN_LIST;
            dd.innerHTML = f.length
                ? f.map(s => `<div class="ac-option" data-val="${s.value}">${s.label}<span class="ac-val">${s.value}</span></div>`).join('')
                : '<div class="ac-empty">Ketik nilai kustom lalu lanjut.</div>';
            dd.querySelectorAll('.ac-option').forEach(el => {
                el.addEventListener('mousedown', e => { e.preventDefault(); inp.value = el.dataset.val; close(); });
            });
            focused = -1;
            dd.classList.add('open');
        }
        function close() { dd.classList.remove('open'); focused = -1; }
        function nav(d) {
            const opts = dd.querySelectorAll('.ac-option');
            if (!opts.length) return;
            opts.forEach(o => o.classList.remove('focused'));
            focused = Math.max(0, Math.min(opts.length-1, focused+d));
            opts[focused].classList.add('focused');
            opts[focused].scrollIntoView({block:'nearest'});
        }

        inp.addEventListener('focus', () => render(inp.value));
        inp.addEventListener('input', () => render(inp.value));
        inp.addEventListener('keydown', e => {
            if (!dd.classList.contains('open')) return;
            if (e.key==='ArrowDown') { e.preventDefault(); nav(1); }
            if (e.key==='ArrowUp')   { e.preventDefault(); nav(-1); }
            if (e.key==='Enter') {
                const f = dd.querySelector('.ac-option.focused');
                if (f) { e.preventDefault(); inp.value = f.dataset.val; close(); }
            }
            if (e.key==='Escape') close();
        });
        document.addEventListener('click', e => {
            if (!document.getElementById('satuanProdukWrap').contains(e.target)) close();
        });
    }

    /* ────────────────────────────────────────
       3. SATUAN RESEP – autocomplete with
          hidden value input (for each row)
    ──────────────────────────────────────── */
    function initResepSatuanAC(row) {
        const wrap    = row.querySelector('.resep-satuan-wrap');
        const dispInp = row.querySelector('.satuan-disp');
        const valInp  = row.querySelector('.satuan-val');
        const dd      = row.querySelector('.resep-satuan-dd');
        if (!wrap || !dispInp || !dd) return;

        let focused = -1;

        function render(q) {
            const f = q.trim().toLowerCase()
                ? satuanOptions.filter(s => s.value.includes(q.trim().toLowerCase()) || s.label.toLowerCase().includes(q.trim().toLowerCase()))
                : satuanOptions;
            dd.innerHTML = f.length
                ? f.map(s => `<div class="ac-option" data-val="${s.value}" data-label="${s.label}">${s.label}<span class="ac-val">${s.value}</span></div>`).join('')
                : '<div class="ac-empty">Tidak ditemukan.</div>';
            dd.querySelectorAll('.ac-option').forEach(el => {
                el.addEventListener('mousedown', e => {
                    e.preventDefault();
                    dispInp.value = el.dataset.label;
                    if (valInp) valInp.value = el.dataset.val;
                    close();
                });
            });
            focused = -1;
            dd.classList.add('open');
        }
        function close() { dd.classList.remove('open'); focused = -1; }
        function nav(d) {
            const opts = dd.querySelectorAll('.ac-option');
            if (!opts.length) return;
            opts.forEach(o => o.classList.remove('focused'));
            focused = Math.max(0, Math.min(opts.length-1, focused+d));
            opts[focused].classList.add('focused');
            opts[focused].scrollIntoView({block:'nearest'});
        }

        dispInp.addEventListener('focus', () => render(dispInp.value));
        dispInp.addEventListener('input', () => {
            if (valInp) valInp.value = ''; // clear until user selects
            render(dispInp.value);
        });
        dispInp.addEventListener('keydown', e => {
            if (!dd.classList.contains('open')) return;
            if (e.key==='ArrowDown') { e.preventDefault(); nav(1); }
            if (e.key==='ArrowUp')   { e.preventDefault(); nav(-1); }
            if (e.key==='Enter') {
                const f = dd.querySelector('.ac-option.focused');
                if (f) { e.preventDefault(); dispInp.value = f.dataset.label; if(valInp) valInp.value = f.dataset.val; close(); }
            }
            if (e.key==='Escape') close();
        });
        dispInp.addEventListener('blur', () => setTimeout(close, 150));
    }

    // Init all existing rows
    document.querySelectorAll('.komposisi-row').forEach(row => initResepSatuanAC(row));

    /* ────────────────────────────────────────
       4. BAHAN AUTOCOMPLETE
    ──────────────────────────────────────── */
    function initBahanAC(row) {
        const inp    = row.querySelector('.bahan-input');
        const panel  = row.querySelector('.bahan-panel');
        const idInp  = row.querySelector('input[name="bahan_id[]"]');
        if (!inp || !panel) return;

        inp.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            panel.innerHTML = '';
            if (idInp) idInp.value = '';
            if (!q) { panel.style.display='none'; return; }
            const filtered = bahanMaster.filter(b => (b.nama||'').toLowerCase().includes(q));
            if (!filtered.length) { panel.style.display='none'; return; }
            filtered.slice(0,10).forEach(b => {
                const el = document.createElement('div');
                el.className = 'bahan-opt';
                el.innerHTML = `<span>${b.nama}</span><span class="bo-satuan">${b.satuan??''}</span>`;
                el.addEventListener('mousedown', e => {
                    e.preventDefault();
                    inp.value = b.nama;
                    if (idInp) idInp.value = b.id;
                    panel.style.display = 'none';
                });
                panel.appendChild(el);
            });
            panel.style.display = 'block';
        });
        inp.addEventListener('blur', () => setTimeout(() => { panel.style.display='none'; }, 200));
    }

    document.querySelectorAll('.komposisi-row').forEach(row => initBahanAC(row));

    /* ────────────────────────────────────────
       5. ADD ROW
    ──────────────────────────────────────── */
    const wrapper = document.getElementById('komposisi-wrapper');
    document.getElementById('add-row')?.addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'komposisi-row';
        div.innerHTML = `
            <div style="position:relative;">
                <input type="hidden" name="bahan_id[]">
                <input type="text" name="bahan_nama[]" class="form-control form-control-sm bahan-input" placeholder="Ketik nama bahan…" autocomplete="off">
                <div class="bahan-panel"></div>
            </div>
            <div>
                <input type="number" step="0.001" name="qty[]" class="form-control form-control-sm" placeholder="0">
            </div>
            <div class="ac-wrap resep-satuan-wrap">
                <input type="text" class="form-control form-control-sm satuan-disp" placeholder="Cari satuan…" autocomplete="off">
                <input type="hidden" name="satuan[]" class="satuan-val">
                <div class="ac-dropdown resep-satuan-dd"></div>
            </div>
            <button type="button" class="btn-del remove-row" title="Hapus baris">×</button>
        `;
        wrapper.appendChild(div);
        feather.replace();
        initBahanAC(div);
        initResepSatuanAC(div);
    });

    /* ────────────────────────────────────────
       6. REMOVE ROW
    ──────────────────────────────────────── */
    wrapper.addEventListener('click', e => {
        if (!e.target.classList.contains('remove-row')) return;
        const rows = wrapper.querySelectorAll('.komposisi-row');
        const row  = e.target.closest('.komposisi-row');
        if (!row) return;
        if (rows.length === 1) {
            row.querySelectorAll('input').forEach(el => el.value = '');
            return;
        }
        row.remove();
    });

    /* ────────────────────────────────────────
       7. FOTO PREVIEW
    ──────────────────────────────────────── */
    const fotoFile = document.getElementById('fotoFile');
    fotoFile?.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        document.getElementById('fotoPH').style.display = 'none';
        document.getElementById('fotoPrev').style.display = 'flex';
        document.getElementById('fotoPrevImg').src = URL.createObjectURL(file);
        document.getElementById('fotoName').textContent = file.name;
        document.getElementById('fotoSize').textContent = (file.size/1024).toFixed(0)+' KB';
    });

    /* ────────────────────────────────────────
       8. HPP SIMULASI
    ──────────────────────────────────────── */
    document.getElementById('btnHitungHpp')?.addEventListener('click', async function() {
        const margin = document.getElementById('input_margin').value || 0;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        try {
            const res  = await fetch(`{{ route('umkm.produk.hitungHpp', $produk->id) }}`, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json','Accept':'application/json'},
                body: JSON.stringify({ margin })
            });
            const data = await res.json();
            const fmt  = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
            ['biaya_bahan','overhead','hpp_total','saran_harga'].forEach(k => {
                const el = document.getElementById('res_'+k);
                if (el) el.innerText = fmt(data[k==='hpp_total'?'hpp':k]);
            });
            document.getElementById('res_margin_target').innerText  = data.margin_target;
            document.getElementById('res_margin_aktual').innerText  = data.margin_aktual !== null ? data.margin_aktual+'%' : 'N/A';
            document.getElementById('res_harga_now').innerText      = fmt(data.harga_jual_now);
            document.getElementById('text_hpp_now').innerText       = fmt(data.hpp);
            document.getElementById('btnGunakanHarga').onclick = () => {
                hargaDisp.value = fmtID(data.saran_harga);
                hargaRaw.value  = data.saran_harga;
                closeSim();
            };
            document.getElementById('hasilHpp').style.display = 'block';
        } catch(err) {
            alert('Gagal menghitung. Coba lagi.');
        } finally {
            this.disabled = false;
            this.innerText = 'Hitung Estimasi';
        }
    });
});

/* modal helpers */
function openSim()  { document.getElementById('simOverlay').classList.add('open'); document.body.style.overflow='hidden'; }
function closeSim(e){ if(e && e.target!==document.getElementById('simOverlay')) return; document.getElementById('simOverlay').classList.remove('open'); document.body.style.overflow=''; }
function clearFoto(e){ e.stopPropagation(); document.getElementById('fotoFile').value=''; document.getElementById('fotoPH').style.display='block'; document.getElementById('fotoPrev').style.display='none'; }
document.addEventListener('keydown', e => { if(e.key==='Escape') closeSim(); });
</script>
@endpush
