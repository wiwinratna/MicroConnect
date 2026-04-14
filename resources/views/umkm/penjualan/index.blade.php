@extends('layouts.umkm')
@section('title','Penjualan')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
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

/* ── Thermal Struk (Receipt) Design ── */
#pb-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.45); z-index: 9050; backdrop-filter: blur(4px); }
#pb-overlay.active { display: block; }

#pb-popup {
    display: none;
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9051;
    width: min(380px, calc(100vw - 2rem));
    max-height: calc(100vh - 4rem);
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 30px 60px rgba(0,0,0,0.22);
    overflow: hidden;
    flex-direction: column;
}
#pb-popup.active { display: flex; animation: slideUpStruk .3s cubic-bezier(0.16, 1, 0.3, 1); }
@keyframes slideUpStruk { from { opacity: 0; transform: translate(-50%, -45%); } to { opacity: 1; transform: translate(-50%, -50%); } }

.struk-wrapper {
    padding: 1.5rem;
    overflow-y: auto;
    font-family: 'Courier Prime', monospace;
    color: #1a1a1a;
    line-height: 1.4;
}

.struk-header { text-align: center; margin-bottom: 1.25rem; }
.struk-logo { max-width: 60px; filter: grayscale(100%); margin-bottom: 0.75rem; }
.struk-brand { font-size: 1.15rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.25rem; line-height: 1.2; }
.struk-address { font-size: 0.75rem; color: #4b5563; font-family: sans-serif; }

.struk-divider { border-top: 1px dashed #ced4da; margin: 1rem 0; }

.struk-meta { font-size: 0.8rem; margin-bottom: 1rem; }
.struk-meta-row { display: flex; justify-content: space-between; gap: 10px; }
.struk-meta-val { text-align: right; font-weight: 700; }

.struk-table { width: 100%; font-size: 0.8rem; border-collapse: collapse; margin-bottom: 1rem; }
.struk-item-name { display: block; font-weight: 700; margin-bottom: 1px; }
.struk-item-calc { display: block; font-size: 0.75rem; color: #6b7280; }
.struk-item-row td { padding: 0.4rem 0; vertical-align: top; }

.struk-total-area { border-top: 1.5px solid #1a1a1a; padding-top: 0.75rem; }
.struk-total-row { display: flex; justify-content: space-between; margin-bottom: 0.25rem; font-size: 0.85rem; }
.struk-grand-total { font-weight: 700; font-size: 1.05rem; border-top: 1px dashed #ced4da; margin-top: 0.5rem; padding-top: 0.5rem; }

.struk-footer { text-align: center; margin-top: 2rem; font-size: 0.75rem; color: #4b5563; }
.struk-footer p { margin: 2px 0; }

.struk-actions { position: sticky; bottom: 0; background: #fff; padding: 1rem 1.5rem; border-top: 1px solid #f3f4f6; display: flex; gap: 0.75rem; }
.btn-struk-print { flex: 1; padding: 0.625rem; background: #1e293b; color: #fff; border: none; border-radius: 8px; font-weight: 600; font-family: sans-serif; font-size: 0.8125rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem; cursor: pointer; transition: background 0.2s; }
.btn-struk-print:hover { background: #0f172a; }
.btn-struk-close { flex: 1; padding: 0.625rem; background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; border-radius: 8px; font-weight: 600; font-family: sans-serif; font-size: 0.8125rem; cursor: pointer; }
.btn-struk-close:hover { background: #e5e7eb; color: #1f2937; }

@media print {
    body * { visibility: hidden; }
    #pb-popup, #pb-popup * { visibility: visible; }
    #pb-popup { position: absolute; left: 0; top: 0; transform: none; box-shadow: none; width: 75mm; }
    .struk-actions { display: none; }
}
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Riwayat <strong>Penjualan</strong></h1>
        <p class="text-muted mb-0" style="font-size:.85rem;">Catatan transaksi penjualan produk kepada pembeli/pelanggan Anda.</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius:12px;">
        {{ $errors->first('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <form action="{{ route('umkm.penjualan.index') }}" method="GET" class="d-flex align-items-center gap-2" style="position:relative; width: 300px;">
            <i data-feather="search" style="width:16px;height:16px;position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;"></i>
            <input type="text" name="search" class="form-control" placeholder="Cari kode transaksi atau pembeli..." value="{{ request('search') }}" style="padding-left:36px !important; border-radius:8px; border:1px solid #e2e8f0;">
        </form>
        <a href="{{ route('umkm.penjualan.create') }}" class="btn btn-primary shadow-sm" style="border-radius:8px; padding: 0.5rem 1.25rem; font-size: 0.85rem; font-weight:500;">
            <i data-feather="plus" style="width:14px;height:14px; margin-right:6px; margin-bottom: 2px;"></i> Penjualan Baru
        </a>
    </div>
    <div class="table-responsive">
        @if($data->isEmpty())
            <div class="py-5 text-center">
                <div class="mb-3">
                    <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                        <i data-feather="inbox" style="width: 28px; height: 28px; color: #94a3b8;"></i>
                    </div>
                </div>
                <p class="text-muted mb-0">Belum ada transaksi penjualan. Klik <strong>+ Penjualan Baru</strong> untuk menambahkan perdana.</p>
            </div>
        @else
            <table class="table align-middle mb-0 table-borderless table-premium" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th style="width:170px">Kode</th>
                        <th style="width:110px">Tanggal</th>
                        <th>Pembeli</th>
                        <th style="width:150px" class="text-end">Total</th>
                        <th style="width:140px" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $d)
                        @php
                            $detailsArr = $d->details->map(function($det) {
                                return [
                                    'nama'     => $det->produk->nama_produk ?? '?',
                                    'qty'      => (string) format_angka($det->qty),
                                    'harga'    => (string) rupiah($det->harga),
                                    'subtotal' => (string) rupiah($det->subtotal),
                                ];
                            })->values()->all();
                            $detailsB64 = base64_encode(json_encode($detailsArr));
                        @endphp
                        <tr class="align-top">
                            <td>
                                <span class="badge bg-light text-dark border" style="font-size: 0.8rem; font-family: monospace;">
                                    {{ $d->kode_penjualan }}
                                </span>
                            </td>
                            <td class="text-nowrap" style="font-weight: 500; font-size: 0.85rem; color: #1e293b;">
                                {{ \Carbon\Carbon::parse($d->tanggal)->format('d/m/Y') }}
                            </td>
                            <td>
                                <span style="font-size: 0.9rem; font-weight: 600; color: #334155;">{{ $d->pembeli ?? '-' }}</span>
                            </td>
                            <td class="text-end text-nowrap" style="font-weight: 700; color: #16a34a;">
                                {{ rupiah($d->total) }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1 flex-nowrap">
                                    <button type="button"
                                            class="btn-action-view btn-open-detail border-0"
                                            data-tanggal="{{ \Carbon\Carbon::parse($d->tanggal)->format('d/m/Y H:i') }}"
                                            data-pembeli="{{ $d->pembeli ?: 'Tunai / Umum' }}"
                                            data-kode="{{ $d->kode_penjualan }}"
                                            data-total="{{ rupiah($d->total) }}"
                                            data-total-raw="{{ format_angka($d->total) }}"
                                            data-b64="{{ $detailsB64 }}"
                                            title="Lihat Detail (Struk)">
                                        <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                                    </button>
                                @if($d->isLocked())
                                    <button type="button" class="btn-action-view text-muted border-0 bg-transparent" style="opacity:0.4; cursor:not-allowed;" title="Tidak bisa diedit karena piutang sudah ada pembayaran">
                                        <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                    <button type="button" class="btn-action-view text-muted border-0 bg-transparent" style="opacity:0.4; cursor:not-allowed;" title="Tidak bisa dihapus karena piutang sudah ada pembayaran">
                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                @else
                                    <a href="{{ route('umkm.penjualan.edit', $d->id) }}"
                                       class="btn-action-edit text-decoration-none" title="Edit Penjualan" style="border: 1px solid #e2e8f0; background: #fff;">
                                        <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                    </a>
                                    <form action="{{ route('umkm.penjualan.destroy', $d->id) }}" method="POST" class="d-inline-block m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Riwayat Penjualan ini? Stok produk dan jurnal akan ditarik kembali.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action-edit text-danger" title="Hapus Penjualan" style="background:#fff1f2; border-color:#ffe4e6;">
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

@if(isset($data) && $data->hasPages())
    <div class="mt-4">{{ $data->links() }}</div>
@endif

{{-- ═══════════════════════════ STRUK PENJUALAN ═══════════════════════════ --}}
<div id="pb-overlay"></div>

<div id="pb-popup">
    <div class="struk-wrapper" id="strukPrintArea">
        <div class="struk-header">
            @if(auth()->user()->umkm->logo_path)
                <img src="{{ asset('storage/' . auth()->user()->umkm->logo_path) }}" class="struk-logo">
            @endif
            <div class="struk-brand">{{ auth()->user()->umkm->nama_umkm }}</div>
            <div class="struk-address">{{ auth()->user()->umkm->alamat ?? 'Digital Receipt' }}</div>
        </div>

        <div class="struk-divider"></div>

        <div class="struk-meta">
            <div class="struk-meta-row">
                <span>No Ref</span>
                <span class="struk-meta-val" id="pb-kode">—</span>
            </div>
            <div class="struk-meta-row">
                <span>Tgl/Jam</span>
                <span class="struk-meta-val" id="pb-tanggal">—</span>
            </div>
            <div class="struk-meta-row">
                <span>Pelanggan</span>
                <span class="struk-meta-val" id="pb-pembeli">—</span>
            </div>
        </div>

        <div class="struk-divider"></div>

        <table class="struk-table">
            <tbody id="pb-tbody">
                {{-- Diisi JS --}}
            </tbody>
        </table>

        <div class="struk-total-area">
            <div class="struk-total-row">
                <span>Subtotal</span>
                <span id="pb-total-raw">Rp 0</span>
            </div>
            <div class="struk-total-row struk-grand-total">
                <span>TOTAL</span>
                <span id="pb-total">Rp 0</span>
            </div>
        </div>

        <div class="struk-footer">
            <p>TERIMA KASIH</p>
            <p style="margin-top:4px;">Barang dibeli tidak dapat ditukar/dikembalikan.</p>
            <p style="font-style:italic; font-size: 0.65rem; margin-top:8px;">Powered by MINECT</p>
        </div>
    </div>

    <div class="struk-actions">
        <button class="btn-struk-print" onclick="window.print()">
            <i data-feather="printer" style="width:14px; height:14px;"></i> Cetak Struk
        </button>
        <button class="btn-struk-close" id="pb-close-bot">Tutup</button>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const overlay  = document.getElementById('pb-overlay');
    const popup    = document.getElementById('pb-popup');
    const tbody    = document.getElementById('pb-tbody');
    const elPem    = document.getElementById('pb-pembeli');
    const elKode   = document.getElementById('pb-kode');
    const elTgl    = document.getElementById('pb-tanggal');
    const elTotal  = document.getElementById('pb-total');
    const elSub    = document.getElementById('pb-total-raw');

    function openPopup(btn) {
        const tanggal  = btn.dataset.tanggal  || '-';
        const pembeli  = btn.dataset.pembeli  || '-';
        const kode     = btn.dataset.kode     || '-';
        const total    = btn.dataset.total    || '-';
        const sub      = btn.dataset.totalRaw || '0';
        let   details  = [];

        try {
            const jsonStr = atob(btn.dataset.b64 || '');
            details = JSON.parse(jsonStr);
        } catch (e) {
            console.error('Detail decode error:', e);
        }

        elTgl.textContent   = tanggal;
        elPem.textContent   = pembeli;
        elKode.textContent  = kode;
        elTotal.textContent = total;
        elSub.textContent   = sub;

        if (!details.length) {
            tbody.innerHTML = '<tr><td style="text-align:center;padding:1rem;">Tidak ada produk.</td></tr>';
        } else {
            tbody.innerHTML = details.map(function (d, i) {
                return '<tr class="struk-item-row">' +
                    '<td>' +
                        '<span class="struk-item-name">' + escHtml(d.nama) + '</span>' +
                        '<span class="struk-item-calc">' + escHtml(d.qty) + ' x ' + escHtml(d.harga) + '</span>' +
                    '</td>' +
                    '<td style="text-align:right; vertical-align:bottom; font-weight:700; font-size:0.85rem;">' + escHtml(d.subtotal.replace('Rp','')) + '</td>' +
                    '</tr>';
            }).join('');
        }

        overlay.classList.add('active');
        popup.classList.add('active');
        document.body.style.overflow = 'hidden';
        if(typeof feather !== 'undefined') feather.replace();
    }

    function closePopup() {
        overlay.classList.remove('active');
        popup.classList.remove('active');
        document.body.style.overflow = '';
    }

    function escHtml(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    document.querySelectorAll('.btn-open-detail').forEach(function (btn) {
        btn.addEventListener('click', function () { openPopup(this); });
    });
    document.getElementById('pb-close-bot').addEventListener('click', closePopup);
    overlay.addEventListener('click', closePopup);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closePopup(); });
})();
</script>
@endpush
