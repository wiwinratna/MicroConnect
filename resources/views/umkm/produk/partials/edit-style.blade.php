@push('styles')
<style>
/* ── Section label ── */
.section-label {
    font-size: 0.63rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.1em; color: var(--mn-text-faint); margin-bottom: 1rem;
    display: flex; align-items: center; gap: 0.5rem;
}
.section-label::after { content: ''; flex: 1; height: 1px; background: var(--mn-border-color); }
.section-label .kode-ghost {
    text-transform: none; letter-spacing: 0; font-weight: 400;
    font-size: .68rem; color: var(--mn-border-strong); font-family: monospace;
}

/* ── Rp inline prefix ── */
.rp-wrap { position: relative; }
.rp-wrap .rp-pfx {
    position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
    font-size: .75rem; font-weight: 600; color: var(--mn-text-faint);
    pointer-events: none; z-index: 2;
}
.rp-wrap input { padding-left: 2rem !important; }

/* ── HPP hint ── */
.hpp-hint {
    margin-top: 7px;
    display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;
}
.hpp-hint-text { font-size: .73rem; color: var(--mn-text-muted); }
.hpp-hint-text strong { color: var(--mn-text-main); }
.hpp-est-badge {
    font-size: .6rem; font-weight: 700; padding: 2px 6px; border-radius: 4px;
    background: var(--mn-warning-bg); color: var(--mn-warning-text); border: 1px solid var(--mn-warning-border);
}
.btn-sim {
    margin-left: auto;
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .72rem; font-weight: 600; color: var(--mn-primary);
    background: var(--mn-bg-soft); border: 1px solid var(--mn-soft-teal);
    border-radius: 6px; padding: .2rem .65rem; cursor: pointer;
    transition: background .15s; white-space: nowrap; text-decoration: none;
}
.btn-sim:hover { background: var(--mn-primary-soft); }

/* ── Custom searchable autocomplete (used for ALL satuan fields) ── */
.ac-wrap { position: relative; }
.ac-dropdown {
    display: none; position: absolute;
    top: calc(100% + 3px); left: 0; right: 0; z-index: 999;
    background: #fff; border: 1px solid var(--mn-border-color);
    border-radius: 9px; box-shadow: 0 6px 20px rgba(38, 59, 56,.1);
    max-height: 220px; overflow-y: auto;
}
.ac-dropdown.open { display: block; }
.ac-option {
    display: flex; align-items: center; justify-content: space-between;
    padding: .38rem .875rem; font-size: .8rem; color: var(--mn-text-main);
    cursor: pointer; transition: background .1s;
}
.ac-option:hover, .ac-option.focused { background: var(--mn-bg-soft); color: var(--mn-primary-hover); }
.ac-option .ac-val {
    font-size: .63rem; font-weight: 700; color: var(--mn-text-faint);
    background: var(--mn-bg-soft); border-radius: 4px; padding: 1px 5px;
    font-family: monospace; letter-spacing: .03em;
}
.ac-empty {
    padding: .5rem .875rem; font-size: .78rem;
    color: var(--mn-text-faint); font-style: italic;
}

/* ── Foto upload zone ── */
.foto-zone {
    border: 1.5px dashed var(--mn-border-strong); border-radius: 10px;
    padding: .875rem; cursor: pointer; transition: border-color .15s, background .15s;
    text-align: center; min-height: 96px;
    display: flex; align-items: center; justify-content: center;
}
.foto-zone:hover { border-color: var(--mn-soft-teal); background: var(--mn-bg-soft); }
.foto-zone input[type="file"] { display: none; }
.foto-placeholder { pointer-events: none; }
.foto-placeholder svg { color: var(--mn-text-faint); margin-bottom: 5px; }
.foto-placeholder-text { font-size: .73rem; color: var(--mn-text-muted); line-height: 1.4; }
.foto-placeholder-text strong { display: block; color: var(--mn-text-main); }
.foto-preview-row {
    display: flex; align-items: center; gap: .75rem; width: 100%; text-align: left;
}
.foto-prev-thumb {
    width: 56px; height: 56px; border-radius: 8px;
    object-fit: cover; border: 1px solid var(--mn-border-color); flex-shrink: 0;
}
.foto-prev-meta { flex: 1; }
.foto-prev-name { font-size: .75rem; font-weight: 600; color: var(--mn-text-main); display: block; }
.foto-prev-size { font-size: .7rem; color: var(--mn-text-faint); }
.btn-foto-clear {
    font-size: .7rem; color: var(--mn-danger-text); background: none; border: none;
    padding: 0; cursor: pointer; text-decoration: underline; display: block; margin-top: 2px;
}
.foto-existing {
    display: flex; align-items: center; gap: .625rem; margin-top: .5rem;
}
.foto-existing img {
    width: 52px; height: 52px; border-radius: 7px;
    object-fit: cover; border: 1px solid var(--mn-border-color);
}
.foto-existing-label { font-size: .72rem; color: var(--mn-text-muted); line-height: 1.4; }

/* ── Komposisi ── */
.komposisi-header {
    display: grid; grid-template-columns: 1fr 110px 1fr 36px;
    gap: .75rem; padding: 0 1rem .4rem;
    font-size: .62rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .07em; color: var(--mn-text-faint);
}
.komposisi-row {
    display: grid; grid-template-columns: 1fr 110px 1fr 36px;
    gap: .75rem; align-items: center;
    background: var(--mn-bg-body); border: 1px solid var(--mn-bg-soft);
    border-radius: 9px; padding: .75rem 1rem;
    margin-bottom: .5rem; transition: border-color .15s;
}
.komposisi-row:hover { border-color: var(--mn-primary-soft); }
.btn-del {
    width: 32px; height: 32px; border-radius: 7px;
    background: none; border: 1px solid var(--mn-border-color); color: var(--mn-text-faint);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem; font-weight: 500;
    transition: background .12s, color .12s, border-color .12s;
    flex-shrink: 0;
}
.btn-del:hover { background: var(--mn-danger-bg); border-color: var(--mn-danger-border); color: var(--mn-danger-text); }

/* Bahan autocomplete panel */
.bahan-panel {
    position: absolute; top: calc(100% + 2px); left: 0; right: 0; z-index: 30;
    border-radius: 8px; border: 1px solid var(--mn-border-color);
    box-shadow: 0 6px 18px rgba(38, 59, 56,.1); background: #fff;
    max-height: 200px; overflow-y: auto; display: none;
}
.bahan-panel .bahan-opt {
    display: flex; justify-content: space-between; align-items: center;
    padding: .38rem .75rem; font-size: .8rem; cursor: pointer; transition: background .1s;
}
.bahan-panel .bahan-opt:hover { background: var(--mn-bg-soft); color: var(--mn-primary-hover); }
.bahan-panel .bahan-opt .bo-satuan { font-size: .7rem; color: var(--mn-text-faint); }

/* ── Form footer ── */
.form-footer {
    display: flex; align-items: center; gap: .75rem;
    padding-top: 1rem; border-top: 1px solid var(--mn-bg-soft);
}

/* ══ Simulasi Modal ══ */
.sim-overlay {
    display: none; position: fixed; inset: 0; z-index: 1055;
    background: rgba(38, 59, 56,.45); backdrop-filter: blur(3px);
    align-items: center; justify-content: center; padding: 1rem;
}
.sim-overlay.open { display: flex; }
.sim-modal {
    background: #fff; border-radius: 16px;
    width: 100%; max-width: 500px; max-height: 88vh;
    display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 24px 64px rgba(38, 59, 56,.22);
    animation: simIn .18s ease;
}
@keyframes simIn { from{opacity:0;transform:scale(.96) translateY(8px)} to{opacity:1;transform:none} }
.sim-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem; border-bottom: 1px solid var(--mn-bg-soft);
}
.sim-title { font-size: .9rem; font-weight: 700; color: var(--mn-text-main); }
.sim-x {
    width: 28px; height: 28px; border-radius: 7px; border: none;
    background: var(--mn-bg-soft); color: var(--mn-text-muted); cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 1rem;
    transition: background .15s;
}
.sim-x:hover { background: var(--mn-border-color); }
.sim-body { padding: 1.25rem; overflow-y: auto; }
.sim-note {
    font-size: .72rem; color: var(--mn-text-muted); line-height: 1.55;
    background: var(--mn-bg-body); border: 1px solid var(--mn-border-color);
    border-radius: 8px; padding: .6rem .875rem; margin-bottom: 1rem;
}
.sim-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; margin-top: 1rem; }
.sc { background: var(--mn-bg-body); border: 1px solid var(--mn-border-color); border-radius: 9px; padding: .65rem .875rem; }
.sc .sl { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--mn-text-faint); display: block; margin-bottom: 3px; }
.sc .sv { font-size: .9rem; font-weight: 700; color: var(--mn-text-main); }
.sc.full { grid-column: 1/-1; background: linear-gradient(135deg,var(--mn-primary-hover),var(--mn-primary)); border: none; text-align: center; padding: .875rem; }
.sc.full .sl { color: rgba(255,255,255,.65); }
.sc.full .sv { color: #fff; font-size: 1.3rem; }
.sc.green { background: var(--mn-success-bg); border-color: var(--mn-success-border); }
.sc.green .sl { color: var(--mn-success-text); }
.sc.green .sv { color: var(--mn-success-text); font-size: .95rem; }
.sc.indigo { background: var(--mn-bg-soft); border-color: var(--mn-soft-teal); }
.sc.indigo .sv { color: var(--mn-primary-hover); }
</style>
@endpush
