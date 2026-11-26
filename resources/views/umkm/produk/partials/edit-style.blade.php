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
