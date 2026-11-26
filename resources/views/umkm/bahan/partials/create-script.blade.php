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
