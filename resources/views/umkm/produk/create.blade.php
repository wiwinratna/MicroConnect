@extends('layouts.umkm')

@section('title', 'Tambah Produk Baru')

@push('styles')
<style>
/* ── Section label ── */
.section-label {
    font-size: .63rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .1em; color: #94a3b8; margin-bottom: 1rem;
    display: flex; align-items: center; gap: .5rem;
}
.section-label::after { content:''; flex:1; height:1px; background:#e2e8f0; }

/* ── Rp prefix ── */
.rp-wrap { position: relative; }
.rp-wrap .rp-pfx {
    position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
    font-size: .75rem; font-weight: 600; color: #94a3b8; pointer-events: none; z-index: 2;
}
.rp-wrap input { padding-left: 2rem !important; }

/* ── HPP chip (subtle, below harga jual) ── */
.hpp-chip {
    display: inline-flex; align-items: center; gap: .4rem;
    margin-top: 6px; font-size: .72rem; color: #64748b;
}
.hpp-chip strong { color: #334155; }
.btn-sim-link {
    margin-left: auto;
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .72rem; font-weight: 600; color: #2563eb;
    background: #eff6ff; border: 1px solid #bfdbfe;
    border-radius: 6px; padding: .2rem .6rem; cursor: pointer;
    transition: background .15s; white-space: nowrap; border: 1px solid #bfdbfe;
}
.btn-sim-link:hover { background: #dbeafe; }

/* ── Simulation results (inline, under resep card) ── */
.sim-result {
    display: none;
    margin-top: .75rem;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 10px; padding: .875rem 1rem;
}
.sim-result.visible { display: block; }
.sim-result-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .5rem; }
@media(max-width:640px) { .sim-result-grid { grid-template-columns: 1fr 1fr; } }
.sr-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: .6rem .875rem; }
.sr-card .sr-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; display: block; margin-bottom: 3px; }
.sr-card .sr-val { font-size: .9rem; font-weight: 700; color: #1e293b; }
.sr-card.accent { background: linear-gradient(135deg,#1d4ed8,#2563eb); border: none; }
.sr-card.accent .sr-label { color: rgba(255,255,255,.65); }
.sr-card.accent .sr-val { color: #fff; }

/* ── Markup info bar ── */
.markup-bar {
    display: none; margin-top: .5rem;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 8px; padding: .5rem .875rem;
    font-size: .78rem; color: #15803d;
}
.markup-bar.visible { display: block; }
.markup-bar.loss { background: #fef2f2; border-color: #fecaca; color: #dc2626; }

/* ── Foto upload zone ── */
.foto-zone {
    border: 1.5px dashed #cbd5e1; border-radius: 10px;
    padding: .875rem; cursor: pointer; text-align: center;
    min-height: 96px; display: flex; align-items: center; justify-content: center;
    transition: border-color .15s, background .15s;
}
.foto-zone:hover { border-color: #93c5fd; background: #f0f7ff; }
.foto-zone input[type="file"] { display: none; }
.foto-ph-text { font-size: .73rem; color: #64748b; line-height: 1.4; }
.foto-ph-text strong { display: block; color: #334155; }
.foto-prev-row { display: flex; align-items: center; gap: .75rem; width: 100%; text-align: left; }
.foto-prev-thumb { width: 56px; height: 56px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; flex-shrink: 0; }
.foto-prev-name { font-size: .75rem; font-weight: 600; color: #334155; display: block; }
.foto-prev-size { font-size: .7rem; color: #94a3b8; }
.btn-foto-clear { font-size: .7rem; color: #ef4444; background: none; border: none; padding: 0; cursor: pointer; text-decoration: underline; display: block; margin-top: 2px; }

/* ── Custom autocomplete ── */
.ac-wrap { position: relative; }
.ac-dd {
    display: none; position: absolute; top: calc(100% + 3px); left: 0; right: 0; z-index: 999;
    background: #fff; border: 1px solid #e2e8f0; border-radius: 9px;
    box-shadow: 0 6px 20px rgba(15,23,42,.1); max-height: 220px; overflow-y: auto;
}
.ac-dd.open { display: block; }
.ac-opt {
    display: flex; align-items: center; justify-content: space-between;
    padding: .38rem .875rem; font-size: .8rem; color: #334155;
    cursor: pointer; transition: background .1s;
}
.ac-opt:hover, .ac-opt.focused { background: #f0f7ff; color: #1d4ed8; }
.ac-opt .ac-badge {
    font-size: .62rem; font-weight: 700; color: #94a3b8;
    background: #f1f5f9; border-radius: 4px; padding: 1px 5px;
    font-family: monospace; letter-spacing: .03em;
}
.ac-empty { padding: .5rem .875rem; font-size: .78rem; color: #94a3b8; font-style: italic; }

/* ── Komposisi rows ── */
.komposisi-header {
    display: grid; grid-template-columns: 1fr 100px 170px 36px;
    gap: .625rem; padding: 0 1rem .4rem;
    font-size: .61rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #b0bec5;
}
.komposisi-row {
    display: grid; grid-template-columns: 1fr 100px 170px 36px;
    gap: .625rem; align-items: center;
    background: #fafafa; border: 1px solid #f0f4f8;
    border-radius: 9px; padding: .75rem 1rem;
    margin-bottom: .5rem; transition: border-color .15s;
}
.komposisi-row:hover { border-color: #dbeafe; }

/* Bahan autocomplete panel (inside row) */
.bahan-panel {
    position: absolute; top: calc(100% + 2px); left: 0; right: 0; z-index: 30;
    background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
    box-shadow: 0 6px 18px rgba(15,23,42,.1); max-height: 200px; overflow-y: auto; display: none;
}
.bahan-opt { display: flex; justify-content: space-between; align-items: center; padding: .38rem .75rem; font-size: .8rem; cursor: pointer; transition: background .1s; }
.bahan-opt:hover { background: #f0f7ff; color: #1d4ed8; }
.bahan-opt .bo-sat { font-size: .7rem; color: #94a3b8; }

.btn-del {
    width: 32px; height: 32px; border-radius: 7px;
    background: none; border: 1px solid #e2e8f0; color: #94a3b8;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem; transition: background .12s, color .12s, border-color .12s;
}
.btn-del:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }

/* ── Form footer ── */
.form-footer { display: flex; align-items: center; gap: .75rem; padding-top: 1rem; border-top: 1px solid #f1f5f9; }
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
    <h1 class="h3 mb-0">Tambah Produk <strong>Baru</strong></h1>
    <p class="text-muted mb-0" style="font-size:.78rem;margin-top:2px;">Isi informasi produk dan komposisi resep bila tersedia.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger rounded-3 mb-3" style="font-size:.8rem;">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

@php $overheadPerUnit = $overheadPerUnit ?? 0; @endphp

<form action="{{ route('umkm.produk.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- ══ CARD 1: Informasi Produk ══ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <p class="section-label">
                Informasi Produk
                @if(isset($kode))
                    <span style="text-transform:none;letter-spacing:0;font-weight:400;font-size:.68rem;color:#cbd5e1;font-family:monospace;">{{ $kode }}</span>
                @endif
            </p>

            {{-- Nama + Satuan --}}
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="nama_produk" class="form-control"
                           value="{{ old('nama_produk') }}"
                           placeholder="Contoh: Dimsum Ayam Original"
                           required autofocus>
                </div>
                <div class="col-md-3" style="min-width:175px;">
                    <label class="form-label">Satuan Produk <span class="text-danger">*</span></label>
                    <div class="ac-wrap" id="satuanProdukWrap">
                        <input type="text" name="satuan_produk" id="satuanProdukInp"
                               class="form-control"
                               value="{{ old('satuan_produk', 'pcs') }}"
                               placeholder="Cari atau ketik…" autocomplete="off" required>
                        <div class="ac-dd" id="satuanProdukDd"></div>
                    </div>
                </div>
            </div>

            {{-- Harga + Foto + Keterangan --}}
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                    <div class="rp-wrap">
                        <span class="rp-pfx">Rp</span>
                        <input type="text" id="harga_jual_display" class="form-control"
                               inputmode="numeric" autocomplete="off"
                               value="{{ old('harga_jual') ? number_format(old('harga_jual'),0,',','.') : '' }}"
                               placeholder="0">
                        <input type="hidden" name="harga_jual" id="harga_jual" value="{{ old('harga_jual') }}">
                        <input type="hidden" name="harga_pokok" id="harga_pokok" value="{{ old('harga_pokok') }}">
                    </div>
                    <div class="hpp-chip" id="hppChip" style="{{ old('harga_pokok') ? '' : 'display:none;' }} flex-wrap:wrap;">
                        <span>Est. HPP: <strong id="harga_pokok_display">{{ old('harga_pokok') ? 'Rp '.number_format(old('harga_pokok'),0,',','.') : '—' }}</strong></span>
                        <button type="button" class="btn-sim-link" id="btnSimulasi">
                            <i data-feather="bar-chart-2" style="width:11px;height:11px;"></i>
                            Hitung Estimasi
                        </button>
                    </div>
                    {{-- Selalu tampilkan tombol jika belum ada HPP --}}
                    <div id="btnSimWrapper" style="{{ old('harga_pokok') ? 'display:none;' : '' }} margin-top:6px;">
                        <button type="button" class="btn-sim-link" id="btnSimulasiAlt">
                            <i data-feather="bar-chart-2" style="width:11px;height:11px;"></i>
                            Hitung Estimasi HPP
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Foto Produk</label>
                    <div class="foto-zone" id="fotoZone" onclick="document.getElementById('fotoFile').click()">
                        <input type="file" name="foto" id="fotoFile" accept="image/*">
                        <div id="fotoPH">
                            <i data-feather="image" style="width:22px;height:22px;color:#94a3b8;display:block;margin:0 auto 5px;"></i>
                            <div class="foto-ph-text">
                                <strong>Klik untuk unggah</strong>
                                JPG, PNG, WebP · maks 2MB
                            </div>
                        </div>
                        <div id="fotoPrev" class="foto-prev-row" style="display:none;">
                            <img id="fotoPrevImg" src="" alt="" class="foto-prev-thumb">
                            <div>
                                <span class="foto-prev-name" id="fotoName">—</span>
                                <span class="foto-prev-size" id="fotoSize"></span>
                                <button type="button" class="btn-foto-clear" onclick="clearFoto(event)">Hapus pilihan</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="4"
                              placeholder="Deskripsi singkat produk…">{{ old('keterangan') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ CARD 2: Komposisi / Resep ══ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <p class="section-label">Komposisi / Resep
                <span style="text-transform:none;letter-spacing:0;font-weight:400;font-size:.7rem;color:#94a3b8;">per 1 unit produk</span>
            </p>

            @if($overheadPerUnit <= 0)
                <div class="d-flex align-items-center gap-2 mb-3 px-3 py-2 rounded-2"
                     style="background:#fffbeb;border:1px solid #fde68a;font-size:.75rem;color:#92400e;">
                    <i data-feather="alert-triangle" style="width:13px;height:13px;flex-shrink:0;"></i>
                    Anggaran bulan ini belum ada — overhead/unit = Rp 0 dalam estimasi HPP.
                </div>
            @endif

            {{-- Column headers --}}
            <div class="komposisi-header">
                <span>Bahan Baku</span><span>Qty / unit</span><span>Satuan</span><span></span>
            </div>

            <div id="komposisi-wrapper">
                <div class="komposisi-row">
                    <div style="position:relative;">
                        <input type="hidden" name="bahan_id[]">
                        <input type="text" name="bahan_nama[]" class="form-control form-control-sm bahan-input"
                               placeholder="Ketik nama bahan…" autocomplete="off">
                        <div class="bahan-panel"></div>
                    </div>
                    <div>
                        <input type="number" step="0.001" name="qty[]"
                               class="form-control form-control-sm qty-input" placeholder="0">
                    </div>
                    <div class="ac-wrap resep-satuan-wrap">
                        <input type="text" class="form-control form-control-sm satuan-disp"
                               placeholder="Cari satuan…" autocomplete="off">
                        <input type="hidden" name="satuan[]" class="satuan-val satuan-input">
                        <div class="ac-dd resep-satuan-dd"></div>
                    </div>
                    <button type="button" class="btn-del remove-row" title="Hapus baris">×</button>
                </div>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btn-add-row">
                <i data-feather="plus" style="width:12px;height:12px;margin-right:3px;"></i>
                Tambah Bahan
            </button>

            {{-- Simulation result (inline, collapsible) --}}
            <div class="sim-result" id="sim-result">
                <div class="sim-result-grid">
                    <div class="sr-card">
                        <span class="sr-label">Biaya Bahan</span>
                        <span class="sr-val" id="biaya_bahan_display">Rp 0</span>
                    </div>
                    <div class="sr-card">
                        <span class="sr-label">Overhead / unit</span>
                        <span class="sr-val" id="overhead_display">Rp 0</span>
                    </div>
                    <div class="sr-card accent">
                        <span class="sr-label">HPP Estimasi</span>
                        <span class="sr-val" id="hpp_display">Rp 0</span>
                    </div>
                </div>
                <div class="markup-bar" id="markup-bar">
                    <span id="markup-text">—</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="form-footer">
        <button type="submit" class="btn btn-primary">
            <i data-feather="save" style="width:13px;height:13px;margin-right:4px;"></i>
            Simpan Produk
        </button>
        <a href="{{ route('umkm.produk.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

@endsection

@push('scripts')
@php
$bahanMaster = isset($bahanBaku)
    ? $bahanBaku->map(fn($b) => [
        'id'     => $b->id,
        'nama'   => $b->nama_bahan,
        'satuan' => $b->satuan,
        'harga'  => $b->harga_last ?? 0,
    ])
    : collect();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();

    /* ─── DATA ─── */
    const bahanMaster    = @json($bahanMaster);
    const satuanOptions  = @json($satuanOptions ?? []);
    const overheadPerUnit = {{ $overheadPerUnit ?? 0 }};

    /* ─── SATUAN LIST (for autocomplete) ─── */
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

    /* ─── FORMATTERS ─── */
    function fmtID(n) {
        return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 3 }).format(n || 0);
    }
    function rupiah(n) { return 'Rp ' + fmtID(Math.round(n)); }

    /* ─── SATUAN PRODUK autocomplete ─── */
    (function initSatuanProdukAC() {
        const inp = document.getElementById('satuanProdukInp');
        const dd  = document.getElementById('satuanProdukDd');
        if (!inp || !dd) return;
        let focused = -1;

        function render(q) {
            const f = q.trim().toLowerCase()
                ? SATUAN_LIST.filter(s => s.value.includes(q.trim().toLowerCase()) || s.label.toLowerCase().includes(q.trim().toLowerCase()))
                : SATUAN_LIST;
            dd.innerHTML = f.length
                ? f.map(s => `<div class="ac-opt" data-val="${s.value}">${s.label}<span class="ac-badge">${s.value}</span></div>`).join('')
                : '<div class="ac-empty">Ketik nilai kustom lalu lanjut.</div>';
            dd.querySelectorAll('.ac-opt').forEach(el => {
                el.addEventListener('mousedown', e => { e.preventDefault(); inp.value = el.dataset.val; close(); });
            });
            focused = -1; dd.classList.add('open');
        }
        function close() { dd.classList.remove('open'); focused = -1; }
        function nav(d) {
            const opts = dd.querySelectorAll('.ac-opt');
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
            if (e.key==='Enter') { const f=dd.querySelector('.ac-opt.focused'); if(f){ e.preventDefault(); inp.value=f.dataset.val; close(); } }
            if (e.key==='Escape') close();
        });
        document.addEventListener('click', e => {
            if (!document.getElementById('satuanProdukWrap')?.contains(e.target)) close();
        });
    })();

    /* ─── RESEP SATUAN autocomplete factory ─── */
    function initResepSatuanAC(row) {
        const dispInp = row.querySelector('.satuan-disp');
        const valInp  = row.querySelector('.satuan-val');
        const dd      = row.querySelector('.resep-satuan-dd');
        if (!dispInp || !dd) return;
        let focused = -1;

        // Build from satuanOptions (PHP $satuanOptions)
        function render(q) {
            const f = q.trim().toLowerCase()
                ? satuanOptions.filter(s => s.value.includes(q.trim().toLowerCase()) || s.label.toLowerCase().includes(q.trim().toLowerCase()))
                : satuanOptions;
            dd.innerHTML = f.length
                ? f.map(s => `<div class="ac-opt" data-val="${s.value}" data-label="${s.label}">${s.label}<span class="ac-badge">${s.value}</span></div>`).join('')
                : '<div class="ac-empty">Tidak ditemukan.</div>';
            dd.querySelectorAll('.ac-opt').forEach(el => {
                el.addEventListener('mousedown', e => {
                    e.preventDefault();
                    dispInp.value = el.dataset.label;
                    if (valInp) valInp.value = el.dataset.val;
                    close();
                    updateRowCost(row);
                });
            });
            focused = -1; dd.classList.add('open');
        }
        function close() { dd.classList.remove('open'); focused = -1; }
        function nav(d) {
            const opts = dd.querySelectorAll('.ac-opt');
            if (!opts.length) return;
            opts.forEach(o => o.classList.remove('focused'));
            focused = Math.max(0, Math.min(opts.length-1, focused+d));
            opts[focused].classList.add('focused');
            opts[focused].scrollIntoView({block:'nearest'});
        }
        dispInp.addEventListener('focus', () => render(dispInp.value));
        dispInp.addEventListener('input', () => { if(valInp) valInp.value=''; render(dispInp.value); });
        dispInp.addEventListener('keydown', e => {
            if (!dd.classList.contains('open')) return;
            if (e.key==='ArrowDown') { e.preventDefault(); nav(1); }
            if (e.key==='ArrowUp')   { e.preventDefault(); nav(-1); }
            if (e.key==='Enter') { const f=dd.querySelector('.ac-opt.focused'); if(f){ e.preventDefault(); dispInp.value=f.dataset.label; if(valInp)valInp.value=f.dataset.val; close(); updateRowCost(row); } }
            if (e.key==='Escape') close();
        });
        dispInp.addEventListener('blur', () => setTimeout(close, 150));
    }

    /* ─── UNIT RATIOS (konversi satuan) ─── */
    const unitRatios = {
        'kg':    { 'g': 0.001, 'kg': 1, 'gr': 0.001, 'gram': 0.001 },
        'liter': { 'ml': 0.001, 'liter': 1, 'cc': 0.001 },
        'pcs':   { 'pcs': 1 },
        'buah':  { 'buah': 1 },
    };
    function convertUnit(qty, fromUnit, toUnit) {
        if (!fromUnit || !toUnit || fromUnit === toUnit) return qty;
        const base = toUnit.toLowerCase(), from = fromUnit.toLowerCase();
        for (const key of Object.keys(unitRatios)) {
            const g = unitRatios[key];
            if (base in g && from in g) return qty * (g[from] / g[base]);
        }
        return qty;
    }

    /* ─── UPDATE ROW COST ─── */
    function updateRowCost(row) {
        const idInp   = row.querySelector('input[name="bahan_id[]"]');
        const qtyEl   = row.querySelector('.qty-input');
        const valInp  = row.querySelector('.satuan-val');
        if (!idInp || !qtyEl) return;

        const bahan = bahanMaster.find(b => String(b.id) === String(idInp.value));
        if (!bahan) return;
        const harga   = Number(bahan.harga || 0);
        const qty     = parseFloat(qtyEl.value) || 0;
        const satuan  = valInp?.value || bahan.satuan;
        const qtyBase = convertUnit(qty, satuan, bahan.satuan);
        row._biaya    = qtyBase * harga; // store for HPP calculation
    }

    /* ─── BAHAN AUTOCOMPLETE ─── */
    function initBahanAC(row) {
        const inp   = row.querySelector('.bahan-input');
        const panel = row.querySelector('.bahan-panel');
        const idInp = row.querySelector('input[name="bahan_id[]"]');
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
                el.innerHTML = `<span>${b.nama}</span><span class="bo-sat">${b.satuan??''}</span>`;
                el.addEventListener('mousedown', e => {
                    e.preventDefault();
                    inp.value = b.nama;
                    if (idInp) idInp.value = b.id;
                    panel.style.display = 'none';
                    updateRowCost(row);
                });
                panel.appendChild(el);
            });
            panel.style.display = 'block';
        });
        inp.addEventListener('blur', () => setTimeout(() => { panel.style.display='none'; }, 150));
    }

    /* ─── SETUP ROW ─── */
    function setupRow(row) {
        initBahanAC(row);
        initResepSatuanAC(row);
        row.querySelector('.qty-input')?.addEventListener('input', () => updateRowCost(row));
    }

    const wrapper = document.getElementById('komposisi-wrapper');
    wrapper.querySelectorAll('.komposisi-row').forEach(row => setupRow(row));

    /* ─── ADD ROW ─── */
    document.getElementById('btn-add-row')?.addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'komposisi-row';
        div.innerHTML = `
            <div style="position:relative;">
                <input type="hidden" name="bahan_id[]">
                <input type="text" name="bahan_nama[]" class="form-control form-control-sm bahan-input" placeholder="Ketik nama bahan…" autocomplete="off">
                <div class="bahan-panel"></div>
            </div>
            <div><input type="number" step="0.001" name="qty[]" class="form-control form-control-sm qty-input" placeholder="0"></div>
            <div class="ac-wrap resep-satuan-wrap">
                <input type="text" class="form-control form-control-sm satuan-disp" placeholder="Cari satuan…" autocomplete="off">
                <input type="hidden" name="satuan[]" class="satuan-val satuan-input">
                <div class="ac-dd resep-satuan-dd"></div>
            </div>
            <button type="button" class="btn-del remove-row" title="Hapus baris">×</button>
        `;
        wrapper.appendChild(div);
        feather.replace();
        setupRow(div);
    });

    /* ─── REMOVE ROW ─── */
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

    /* ─── HPP ESTIMATION ─── */
    function syncBahanId(row) {
        const inp   = row.querySelector('.bahan-input');
        const idInp = row.querySelector('input[name="bahan_id[]"]');
        if (!inp || !idInp || idInp.value) return;
        const found = bahanMaster.find(b => b.nama.toLowerCase() === inp.value.toLowerCase().trim());
        if (found) idInp.value = found.id;
    }

    function computeHPP() {
        let biayaBahan = 0;
        wrapper.querySelectorAll('.komposisi-row').forEach(row => {
            syncBahanId(row);
            const idInp  = row.querySelector('input[name="bahan_id[]"]');
            const qtyEl  = row.querySelector('.qty-input');
            const valInp = row.querySelector('.satuan-val');
            const bahan  = bahanMaster.find(b => String(b.id) === String(idInp?.value));
            if (!bahan) return;
            const qty = parseFloat(qtyEl?.value) || 0;
            if (!qty) return;
            const satuan   = valInp?.value || bahan.satuan;
            const qtyBase  = convertUnit(qty, satuan, bahan.satuan);
            biayaBahan += qtyBase * Number(bahan.harga || 0);
        });
        return { biayaBahan, overhead: overheadPerUnit, hpp: biayaBahan + overheadPerUnit };
    }

    function runSimulasi() {
        const { biayaBahan, overhead, hpp } = computeHPP();

        // Update hidden + display
        document.getElementById('harga_pokok').value         = Math.round(hpp);
        document.getElementById('harga_pokok_display').innerText = rupiah(hpp);

        // Show result cards
        document.getElementById('biaya_bahan_display').innerText = rupiah(biayaBahan);
        document.getElementById('overhead_display').innerText    = rupiah(overhead);
        document.getElementById('hpp_display').innerText         = rupiah(hpp);
        document.getElementById('sim-result').classList.add('visible');

        // Show hpp chip
        document.getElementById('hppChip').style.display  = 'flex';
        document.getElementById('btnSimWrapper').style.display = 'none';

        updateMarkup(hpp);
    }

    function updateMarkup(hpp) {
        const jual    = parseFloat(document.getElementById('harga_jual').value) || 0;
        const markupBar = document.getElementById('markup-bar');
        if (!hpp || !jual) { markupBar.classList.remove('visible'); return; }
        const pct  = ((jual - hpp) / hpp * 100).toFixed(1);
        const diff = rupiah(Math.abs(jual - hpp));
        markupBar.classList.add('visible');
        markupBar.classList.toggle('loss', jual < hpp);
        document.getElementById('markup-text').innerHTML =
            jual >= hpp
                ? `Markup <strong>${pct}%</strong> — harga jual lebih tinggi Rp ${diff} dari HPP estimasi.`
                : `⚠ Harga jual lebih rendah Rp ${diff} dari HPP estimasi (rugi).`;
    }

    document.getElementById('btnSimulasi')?.addEventListener('click', runSimulasi);
    document.getElementById('btnSimulasiAlt')?.addEventListener('click', runSimulasi);

    /* ─── HARGA JUAL FORMAT ─── */
    const hargaDisp  = document.getElementById('harga_jual_display');
    const hargaRaw   = document.getElementById('harga_jual');
    hargaDisp?.addEventListener('input', function() {
        const raw = this.value.replace(/\./g,'').replace(/[^\d]/g,'');
        hargaRaw.value = raw;
        const num = parseInt(raw) || 0;
        this.value = num > 0 ? fmtID(num) : '';
        const hpp = parseFloat(document.getElementById('harga_pokok').value) || 0;
        if (hpp) updateMarkup(hpp);
    });
    // Format initial value on load
    const initJual = parseInt(hargaRaw.value) || 0;
    if (initJual > 0) hargaDisp.value = fmtID(initJual);

    /* ─── FOTO PREVIEW ─── */
    document.getElementById('fotoFile')?.addEventListener('change', function() {
        const file = this.files[0]; if (!file) return;
        document.getElementById('fotoPH').style.display   = 'none';
        document.getElementById('fotoPrev').style.display = 'flex';
        document.getElementById('fotoPrevImg').src = URL.createObjectURL(file);
        document.getElementById('fotoName').textContent = file.name;
        document.getElementById('fotoSize').textContent = (file.size/1024).toFixed(0)+' KB';
    });
});

function clearFoto(e) {
    e.stopPropagation();
    document.getElementById('fotoFile').value = '';
    document.getElementById('fotoPH').style.display   = 'block';
    document.getElementById('fotoPrev').style.display = 'none';
}
</script>
@endpush
