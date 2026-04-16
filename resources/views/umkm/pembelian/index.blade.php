@extends('layouts.umkm')

@section('title', 'Riwayat Pembelian')

@push('styles')
<style>
/* ── Premium Table & Actions ── */
.btn-action-edit { width: 32px; height: 32px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; display: inline-flex; align-items: center; justify-content: center;  transition: all .2s; }
.btn-action-edit:hover { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
.btn-action-view { width: 32px; height: 32px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; }
.btn-action-view:hover { background: #fdf5ff; border-color: #f5d0fe; color: #c026d3; }

.table-premium th { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; border-bottom: 1px solid #e2e8f0 !important; padding: 1rem 1.25rem; background: #fafbfc; }
.table-premium td { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.table-premium tr:last-child td { border-bottom: none; }
.table-premium tr:hover td { background: #f8fafc; }

/* ── Popup Overlay ── */
#pb-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(10, 18, 36, 0.52);
    z-index: 9050;
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
#pb-overlay.active { display: block; animation: fadeIn .18s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

/* ── Popup Panel ── */
#pb-popup {
    display: none;
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9051;
    width: min(700px, calc(100vw - 2rem));
    max-height: calc(100vh - 3rem);
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 24px 80px rgba(15,23,42,.22), 0 4px 20px rgba(15,23,42,.1);
    overflow: hidden;
    flex-direction: column;
}
#pb-popup.active {
    display: flex;
    animation: popIn .22s cubic-bezier(.34,1.4,.64,1);
}
@keyframes popIn {
    from { opacity:0; transform: translate(-50%,-50%) scale(.94); }
    to   { opacity:1; transform: translate(-50%,-50%) scale(1); }
}

/* ── Popup Header ── */
.pb-header {
    padding: 1.25rem 1.5rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-shrink: 0;
}
.pb-eyebrow {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #94a3b8;
    margin: 0 0 4px;
}
.pb-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    line-height: 1.3;
}
.pb-close {
    width: 32px; height: 32px;
    border-radius: 50%;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    font-size: 1.1rem;
    line-height: 1;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: background .15s, color .15s;
}
.pb-close:hover { background: #e2e8f0; color: #0f172a; }

/* ── Meta Bar ── */
.pb-meta {
    display: flex;
    align-items: center;
    gap: 0;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
    overflow: hidden;
}
.pb-meta-item {
    padding: 0.6rem 1.25rem;
    border-right: 1px solid #e2e8f0;
    font-size: 0.77rem;
}
.pb-meta-item:last-child { border-right: none; margin-left: auto; }
.pb-meta-label { color: #94a3b8; display: block; font-size: 0.68rem; margin-bottom: 1px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; }
.pb-meta-value { color: #1e293b; font-weight: 600; }
.pb-meta-total .pb-meta-value { color: #2563eb; font-size: 1rem; font-weight: 800; }

/* ── Table Body ── */
.pb-table-wrap {
    overflow-y: auto;
    flex: 1;
    min-height: 0;
}
.pb-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8rem;
}
.pb-table thead th {
    position: sticky;
    top: 0;
    background: #f8fafc;
    padding: 0.6rem 1.25rem;
    font-size: 0.63rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #94a3b8;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.pb-table thead th:first-child { text-align: left; width: 36px; }
.pb-table thead th.col-bahan { text-align: left; }
.pb-table thead th.col-qty { text-align: center; width: 120px; }
.pb-table thead th.col-harga { text-align: right; width: 140px; }
.pb-table thead th.col-sub { text-align: right; width: 140px; }

.pb-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .12s; }
.pb-table tbody tr:last-child { border-bottom: none; }
.pb-table tbody tr:hover { background: #f8fafc; }
.pb-table tbody td { padding: 0.65rem 1.25rem; vertical-align: middle; }
.pb-table tbody td.td-no { color: #cbd5e1; font-size: 0.72rem; font-weight: 600; text-align: left; }
.pb-table tbody td.td-nama { font-weight: 600; color: #1e293b; }
.pb-table tbody td.td-qty { text-align: center; color: #475569; }
.pb-table tbody td.td-satuan { font-size: 0.7rem; color: #94a3b8; }
.pb-table tbody td.td-harga { text-align: right; color: #64748b; }
.pb-table tbody td.td-sub { text-align: right; font-weight: 700; color: #0f172a; }

/* ── Footer ── */
.pb-footer {
    padding: 0.75rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem;
    flex-shrink: 0;
    background: #fff;
}
.pb-btn-close {
    padding: 0.38rem 1.1rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s, border-color .15s;
}
.pb-btn-close:hover { background: #f8fafc; border-color: #cbd5e1; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Riwayat <strong>Pembelian</strong></h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Catatan pembelanjaan / restock suplai usaha Anda.</p>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <form action="{{ route('umkm.pembelian.index') }}" method="GET" class="d-flex align-items-center gap-2" style="position:relative; width: 300px;">
            <i data-feather="search" style="width:16px;height:16px;position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;"></i>
            <input type="text" name="search" class="form-control" placeholder="Cari nomor nota atau supplier..." value="{{ request('search') }}" style="padding-left:36px !important; border-radius:8px; border:1px solid #e2e8f0; border-radius: 8px;">
        </form>
        <a href="{{ route('umkm.pembelian.create') }}" class="btn btn-primary shadow-sm" style="border-radius:8px; padding: 0.5rem 1.25rem; font-size: 0.85rem; font-weight:500;">
            <i data-feather="plus" style="width:14px;height:14px; margin-right:6px; margin-bottom: 2px;"></i> Pembelian Baru
        </a>
    </div>
    <div class="table-responsive">
        @if($data->isEmpty())
            <div class="py-5 text-center">
                <div class="mb-3">
                    <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                        <i data-feather="shopping-cart" style="width: 28px; height: 28px; color: #94a3b8;"></i>
                    </div>
                </div>
                <p class="text-muted mb-0">Belum ada transaksi pembelian. Klik <strong>+ Pembelian Baru</strong> untuk menambahkan.</p>
            </div>
        @else
            <table class="table align-middle mb-0 table-borderless table-premium" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th style="width:110px">Tanggal</th>
                        <th>Supplier</th>
                        <th style="width:150px">No. Nota</th>
                        <th style="width:110px" class="text-center">Jumlah Item</th>
                        <th style="width:150px" class="text-end">Total Pembelian</th>
                        <th style="width:100px" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $row)
                        @php
                            /* Encode via base64 to avoid ANY HTML-escaping issues */
                            $detailsArr = $row->details->map(function($d) {
                                return [
                                    'nama'     => $d->bahan->nama_bahan ?? '?',
                                    'qty'      => (string) format_angka($d->qty),
                                    'satuan'   => $d->bahan->satuan ?? '',
                                    'harga'    => (string) rupiah($d->harga_beli),
                                    'subtotal' => (string) rupiah($d->subtotal),
                                ];
                            })->values()->all();
                            $detailsB64 = base64_encode(json_encode($detailsArr));
                        @endphp
                            <tr>
                                <td class="text-nowrap" style="font-weight: 500; font-size: 0.85rem; color: #1e293b;">
                                    {{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}
                                </td>
                                <td>
                                    <span style="font-size: 0.9rem; font-weight: 600; color: #334155;">{{ $row->supplier ?: '-' }}</span>
                                    @if($row->bukti_pembelian)
                                        <a href="{{ asset('storage/' . $row->bukti_pembelian) }}" target="_blank"
                                           class="ms-2 badge bg-primary text-white text-decoration-none shadow-sm"
                                           style="font-size:0.65rem; border-radius: 6px;">
                                            Lihat Bukti
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if($row->nomor_nota)
                                        <span class="badge bg-light text-dark border" style="font-size: 0.8rem; font-family: monospace;">{{ $row->nomor_nota }}</span>
                                    @else
                                        <span class="text-muted" style="opacity:0.4;">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span style="background: #e0f2fe; color: #0284c7; font-size: 0.72rem; font-weight: 600; padding: 0.35rem 0.6rem; border-radius: 6px; display: inline-block;">
                                        {{ $row->details->count() }} item
                                    </span>
                                </td>
                                <td class="text-end text-nowrap" style="font-weight: 700; color: #0f172a;">
                                    {{ rupiah($row->total) }}
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1 flex-nowrap">
                                        <button type="button"
                                                class="btn-action-view btn-open-detail"
                                                data-tanggal="{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}"
                                                data-supplier="{{ $row->supplier ?: '-' }}"
                                                data-nota="{{ $row->nomor_nota ?: '-' }}"
                                                data-total="{{ rupiah($row->total) }}"
                                                data-b64="{{ $detailsB64 }}"
                                                title="Lihat Detail">
                                            <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                                        </button>
                                        
                                        @if($row->isUsed())
                                            <button type="button" class="btn-action-edit border-0 bg-transparent text-muted" style="opacity:0.4; cursor:not-allowed;" title="Tidak bisa diedit karena stok sudah terpakai">
                                                <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                            </button>
                                            <button type="button" class="btn-action-edit border-0 bg-transparent text-muted" style="opacity:0.4; cursor:not-allowed;" title="Tidak bisa dihapus karena stok sudah terpakai">
                                                <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        @else
                                            <a href="{{ route('umkm.pembelian.edit', $row->id) }}"
                                               class="btn-action-edit" title="Edit Pembelian">
                                                <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                            </a>
                                            <form action="{{ route('umkm.pembelian.destroy', $row->id) }}" method="POST" class="d-inline-block m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Riwayat Pembelian ini? Stok bahan yang sudah masuk akan ditarik kembali.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action-edit text-danger" title="Hapus Pembelian" style="background:#fff1f2; border-color:#ffe4e6;">
                                                    <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

@if($data->hasPages())
    <div class="mt-4">{{ $data->links() }}</div>
@endif

{{-- ═══════════════════════════ POPUP DETAIL ═══════════════════════════ --}}
<div id="pb-overlay"></div>

<div id="pb-popup">
    {{-- Header --}}
    <div class="pb-header">
        <div>
            <p class="pb-eyebrow">Riwayat Pembelian</p>
            <h5 class="pb-title" id="pb-title">Detail Pembelian</h5>
        </div>
        <button class="pb-close" id="pb-close-top">&times;</button>
    </div>

    {{-- Meta Bar --}}
    <div class="pb-meta">
        <div class="pb-meta-item">
            <span class="pb-meta-label">Supplier</span>
            <span class="pb-meta-value" id="pb-supplier">—</span>
        </div>
        <div class="pb-meta-item">
            <span class="pb-meta-label">No. Nota</span>
            <span class="pb-meta-value" id="pb-nota">—</span>
        </div>
        <div class="pb-meta-item pb-meta-total">
            <span class="pb-meta-label">Total Pembelian</span>
            <span class="pb-meta-value" id="pb-total">—</span>
        </div>
    </div>

    {{-- Scrollable Table --}}
    <div class="pb-table-wrap">
        <table class="pb-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th class="col-bahan">Nama Bahan</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-harga">Harga / Satuan</th>
                    <th class="col-sub">Subtotal</th>
                </tr>
            </thead>
            <tbody id="pb-tbody">
                <tr><td colspan="5" style="text-align:center;padding:2rem;color:#94a3b8;">Memuat...</td></tr>
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="pb-footer">
        <button class="pb-btn-close" id="pb-close-bot">Tutup</button>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const overlay  = document.getElementById('pb-overlay');
    const popup    = document.getElementById('pb-popup');
    const tbody    = document.getElementById('pb-tbody');
    const title    = document.getElementById('pb-title');
    const elSup    = document.getElementById('pb-supplier');
    const elNota   = document.getElementById('pb-nota');
    const elTotal  = document.getElementById('pb-total');

    function open(btn) {
        const tanggal  = btn.dataset.tanggal  || '-';
        const supplier = btn.dataset.supplier || '-';
        const nota     = btn.dataset.nota     || '-';
        const total    = btn.dataset.total    || '-';
        let   details  = [];

        try {
            // Decode base64 → JSON string → JS array
            const jsonStr = atob(btn.dataset.b64 || '');
            details = JSON.parse(jsonStr);
        } catch (e) {
            console.error('Detail decode error:', e);
        }

        // Fill header & meta
        title.textContent   = 'Detail Pembelian \u2014 ' + tanggal;
        elSup.textContent   = supplier;
        elNota.textContent  = nota;
        elTotal.textContent = total;

        // Render rows
        if (!details.length) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem;color:#94a3b8;font-size:0.8rem;">Tidak ada detail item tercatat.</td></tr>';
        } else {
            tbody.innerHTML = details.map(function (d, i) {
                return '<tr>' +
                    '<td class="td-no">' + (i + 1) + '</td>' +
                    '<td class="td-nama">' + escHtml(d.nama) + '</td>' +
                    '<td class="td-qty">' + escHtml(d.qty) + ' <span class="td-satuan">' + escHtml(d.satuan) + '</span></td>' +
                    '<td class="td-harga">' + escHtml(d.harga) + '</td>' +
                    '<td class="td-sub">' + escHtml(d.subtotal) + '</td>' +
                    '</tr>';
            }).join('');
        }

        overlay.classList.add('active');
        popup.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        overlay.classList.remove('active');
        popup.classList.remove('active');
        document.body.style.overflow = '';
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // Attach
    document.querySelectorAll('.btn-open-detail').forEach(function (btn) {
        btn.addEventListener('click', function () { open(this); });
    });
    document.getElementById('pb-close-top').addEventListener('click', close);
    document.getElementById('pb-close-bot').addEventListener('click', close);
    overlay.addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
@endpush
