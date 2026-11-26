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
