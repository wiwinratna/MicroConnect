@push('styles')
<style>
/* ── Section label ── */
.section-label {
    font-size: .63rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .1em; color: var(--mn-text-faint); margin-bottom: 1rem;
    display: flex; align-items: center; gap: .5rem;
}
.section-label::after { content:''; flex:1; height:1px; background:var(--mn-border-color); }

/* ── Rp prefix ── */
.rp-wrap { position: relative; }
.rp-wrap .rp-pfx {
    position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
    font-size: .75rem; font-weight: 600; color: var(--mn-text-faint); pointer-events: none; z-index: 2;
}
.rp-wrap input { padding-left: 2rem !important; }

/* ── HPP chip (subtle, below harga jual) ── */
.hpp-chip {
    display: inline-flex; align-items: center; gap: .4rem;
    margin-top: 6px; font-size: .72rem; color: var(--mn-text-muted);
}
.hpp-chip strong { color: var(--mn-text-main); }
.btn-sim-link {
    margin-left: auto;
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .72rem; font-weight: 600; color: var(--mn-primary);
    background: var(--mn-bg-soft); border: 1px solid var(--mn-soft-accent);
    border-radius: 6px; padding: .2rem .6rem; cursor: pointer;
    transition: background .15s; white-space: nowrap; border: 1px solid var(--mn-soft-accent);
}
.btn-sim-link:hover { background: var(--mn-primary-soft); }

/* ── Simulation results (inline, under resep card) ── */
.sim-result {
    display: none;
    margin-top: .75rem;
    background: var(--mn-bg-body); border: 1px solid var(--mn-border-color);
    border-radius: 10px; padding: .875rem 1rem;
}
.sim-result.visible { display: block; }
.sim-result-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: .5rem; }
@media(max-width:640px) { .sim-result-grid { grid-template-columns: 1fr 1fr; } }
.sr-card { background: #fff; border: 1px solid var(--mn-border-color); border-radius: 8px; padding: .6rem .875rem; }
.sr-card .sr-label { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--mn-text-faint); display: block; margin-bottom: 3px; }
.sr-card .sr-val { font-size: .9rem; font-weight: 700; color: var(--mn-text-main); }
.sr-card.accent { background: linear-gradient(135deg,var(--mn-primary-hover),var(--mn-primary)); border: none; }
.sr-card.accent .sr-label { color: rgba(255,255,255,.65); }
.sr-card.accent .sr-val { color: #fff; }

/* ── Markup info bar ── */
.markup-bar {
    display: none; margin-top: .5rem;
    background: var(--mn-success-bg); border: 1px solid var(--mn-success-border);
    border-radius: 8px; padding: .5rem .875rem;
    font-size: .78rem; color: var(--mn-success-text);
}
.markup-bar.visible { display: block; }
.markup-bar.loss { background: var(--mn-danger-bg); border-color: var(--mn-danger-border); color: var(--mn-danger-text); }

/* ── Foto upload zone ── */
.foto-zone {
    border: 1.5px dashed var(--mn-border-strong); border-radius: 10px;
    padding: .875rem; cursor: pointer; text-align: center;
    min-height: 96px; display: flex; align-items: center; justify-content: center;
    transition: border-color .15s, background .15s;
}
.foto-zone:hover { border-color: var(--mn-soft-accent); background: var(--mn-bg-soft); }
.foto-zone input[type="file"] { display: none; }
.foto-ph-text { font-size: .73rem; color: var(--mn-text-muted); line-height: 1.4; }
.foto-ph-text strong { display: block; color: var(--mn-text-main); }
.foto-prev-row { display: flex; align-items: center; gap: .75rem; width: 100%; text-align: left; }
.foto-prev-thumb { width: 56px; height: 56px; border-radius: 8px; object-fit: cover; border: 1px solid var(--mn-border-color); flex-shrink: 0; }
.foto-prev-name { font-size: .75rem; font-weight: 600; color: var(--mn-text-main); display: block; }
.foto-prev-size { font-size: .7rem; color: var(--mn-text-faint); }
.btn-foto-clear { font-size: .7rem; color: var(--mn-danger-text); background: none; border: none; padding: 0; cursor: pointer; text-decoration: underline; display: block; margin-top: 2px; }

/* ── Custom autocomplete ── */
.ac-wrap { position: relative; }
.ac-dd {
    display: none; position: absolute; top: calc(100% + 3px); left: 0; right: 0; z-index: 999;
    background: #fff; border: 1px solid var(--mn-border-color); border-radius: 9px;
    box-shadow: 0 6px 20px rgba(38, 54, 77,.1); max-height: 220px; overflow-y: auto;
}
.ac-dd.open { display: block; }
.ac-opt {
    display: flex; align-items: center; justify-content: space-between;
    padding: .38rem .875rem; font-size: .8rem; color: var(--mn-text-main);
    cursor: pointer; transition: background .1s;
}
.ac-opt:hover, .ac-opt.focused { background: var(--mn-bg-soft); color: var(--mn-primary-hover); }
.ac-opt .ac-badge {
    font-size: .62rem; font-weight: 700; color: var(--mn-text-faint);
    background: var(--mn-bg-soft); border-radius: 4px; padding: 1px 5px;
    font-family: monospace; letter-spacing: .03em;
}
.ac-empty { padding: .5rem .875rem; font-size: .78rem; color: var(--mn-text-faint); font-style: italic; }

/* ── Komposisi rows ── */
.komposisi-header {
    display: grid; grid-template-columns: 1fr 100px 170px 36px;
    gap: .625rem; padding: 0 1rem .4rem;
    font-size: .61rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--mn-text-faint);
}
.komposisi-row {
    display: grid; grid-template-columns: 1fr 100px 170px 36px;
    gap: .625rem; align-items: center;
    background: var(--mn-bg-body); border: 1px solid var(--mn-bg-soft);
    border-radius: 9px; padding: .75rem 1rem;
    margin-bottom: .5rem; transition: border-color .15s;
}
.komposisi-row:hover { border-color: var(--mn-primary-soft); }

/* Bahan autocomplete panel (inside row) */
.bahan-panel {
    position: absolute; top: calc(100% + 2px); left: 0; right: 0; z-index: 30;
    background: #fff; border: 1px solid var(--mn-border-color); border-radius: 8px;
    box-shadow: 0 6px 18px rgba(38, 54, 77,.1); max-height: 200px; overflow-y: auto; display: none;
}
.bahan-opt { display: flex; justify-content: space-between; align-items: center; padding: .38rem .75rem; font-size: .8rem; cursor: pointer; transition: background .1s; }
.bahan-opt:hover { background: var(--mn-bg-soft); color: var(--mn-primary-hover); }
.bahan-opt .bo-sat { font-size: .7rem; color: var(--mn-text-faint); }

.btn-del {
    width: 32px; height: 32px; border-radius: 7px;
    background: none; border: 1px solid var(--mn-border-color); color: var(--mn-text-faint);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 1rem; transition: background .12s, color .12s, border-color .12s;
}
.btn-del:hover { background: var(--mn-danger-bg); border-color: var(--mn-danger-border); color: var(--mn-danger-text); }

/* ── Form footer ── */
.form-footer { display: flex; align-items: center; gap: .75rem; padding-top: 1rem; border-top: 1px solid var(--mn-bg-soft); }

/* Ponsel: bahan satu baris penuh; qty, satuan, dan tombol hapus di bawahnya. */
@media (max-width: 575.98px) {
    .komposisi-header { display: none; }
    .komposisi-row { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) 36px; }
    .komposisi-row > :first-child { grid-column: 1 / -1; }
}
</style>
@endpush
