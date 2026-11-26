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

/* Ponsel: bahan satu baris penuh; qty, satuan, dan tombol hapus di bawahnya. */
@media (max-width: 575.98px) {
    .komposisi-header { display: none; }
    .komposisi-row { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 36px; }
    .komposisi-row > :first-child { grid-column: 1 / -1; }
}
</style>
@endpush
