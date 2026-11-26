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
