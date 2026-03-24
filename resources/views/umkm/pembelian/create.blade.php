@extends('layouts.umkm')

@section('title', 'Pembelian Baru')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><strong>Pembelian</strong> Bahan Baku</h1>
            <p class="text-muted mb-0">
                Input transaksi pembelian per nota, stok akan ter-update otomatis.
            </p>
        </div>
        <a href="{{ route('umkm.pembelian.index') }}" class="btn btn-outline-secondary">
            &larr; Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('umkm.pembelian.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- HEADER PEMBELIAN --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Pembelian (Auto)</label>
                        <input type="text" class="form-control" value="{{ $kode ?? '-' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nomor Nota Vendor</label>
                        <input type="text" name="nomor_nota" class="form-control"
                               value="{{ old('nomor_nota') }}" placeholder="Contoh: STRUK-8891">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control"
                               value="{{ old('tanggal', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <input type="text" name="supplier" class="form-control"
                               value="{{ old('supplier') }}" placeholder="Nama toko / pemasok (opsional)">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" rows="1" class="form-control"
                                  placeholder="Catatan tambahan (opsional)">{{ old('catatan') }}</textarea>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Bukti Pembelian (Opsional)</label>
                        <input type="file" name="bukti_pembelian" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        <small class="text-muted">Maksimal 2MB. Format: JPG, PNG, PDF.</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAIL PEMBELIAN --}}
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Detail Bahan yang Dibeli</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-row">
                    + Tambah Baris
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-borderless" id="detail-table">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 35%">Bahan</th>
                            <th style="width: 15%">Qty</th>
                            <th style="width: 22%">Harga Beli (Rp)</th>
                            <th style="width: 20%" class="text-end">Subtotal (Rp)</th>
                            <th style="width: 8%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        {{-- Satu baris awal --}}
                        <tr class="detail-row">
                            <td>
                                <input type="hidden" name="bahan_id[]" class="bahan-id-hidden">
                                <div class="position-relative">
                                    <input type="text"
                                           class="form-control bahan-search-input"
                                           placeholder="Ketik nama bahan..."
                                           autocomplete="off">
                                    <div class="bahan-search-panel list-group shadow-sm"
                                         style="position:absolute;z-index:50;top:100%;left:0;right:0;max-height:200px;overflow:auto;display:none;">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input type="number" name="qty[]" step="0.001" min="0"
                                       class="form-control qty-input" placeholder="0">
                            </td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text text-muted small">Rp</span>
                                    <input type="text" name="harga_beli_display[]"
                                           class="form-control harga-input"
                                           placeholder="0"
                                           inputmode="numeric">
                                </div>
                                {{-- Hidden field yg dikirim saat submit --}}
                                <input type="hidden" name="harga_beli[]" class="harga-raw">
                                <small class="harga-display-text text-muted"></small>
                            </td>
                            <td class="text-end fw-semibold">
                                <span class="subtotal-text text-success">—</span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">&times;</button>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total Pembelian</th>
                            <th class="text-end fw-bold text-primary fs-6">
                                Rp <span id="grand-total">0</span>
                            </th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-lg">
                Simpan Pembelian
            </button>
        </div>
    </form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableBody   = document.querySelector('#detail-table tbody');
    const btnAddRow   = document.getElementById('btn-add-row');
    const grandTotalEl= document.getElementById('grand-total');
    const searchUrl   = '{{ route("umkm.bahan.search") }}';

    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(Math.round(num || 0));
    }

    function formatRupiah(input) {
        // Simpan posisi kursor dasar
        const raw = input.value.replace(/\D/g, '');
        const num  = parseInt(raw) || 0;
        input.value = num > 0 ? num.toLocaleString('id-ID') : '';
        // Simpan nilai asli di data-raw supaya bisa dikirim saat submit
        input.dataset.raw = raw;
    }

    function recalcRow(row) {
        const qty      = parseFloat(row.querySelector('.qty-input')?.value) || 0;
        // Baca dari dataset.raw (angka bersih) agar format ribuan tidak ganggu
        const hargaEl  = row.querySelector('.harga-input');
        const harga    = parseFloat((hargaEl?.dataset?.raw || hargaEl?.value || '').replace(/\D/g, '')) || 0;
        const subtotal = qty * harga;
        const subtEl   = row.querySelector('.subtotal-text');
        subtEl.textContent = subtotal > 0 ? formatNumber(subtotal) : '—';
        recalcTotal();
    }

    function recalcTotal() {
        let total = 0;
        tableBody.querySelectorAll('.detail-row').forEach(row => {
            const qty      = parseFloat(row.querySelector('.qty-input')?.value) || 0;
            const hargaEl  = row.querySelector('.harga-input');
            const harga    = parseFloat((hargaEl?.dataset?.raw || hargaEl?.value || '').replace(/\D/g, '')) || 0;
            total += qty * harga;
        });
        grandTotalEl.textContent = formatNumber(total);
    }

    function setupBahanAutocomplete(row) {
        const input  = row.querySelector('.bahan-search-input');
        const hidden = row.querySelector('.bahan-id-hidden');
        const panel  = row.querySelector('.bahan-search-panel');
        if (!input || !panel) return;
        let timer;
        input.addEventListener('input', function () {
            clearTimeout(timer);
            const q = this.value.trim();
            panel.innerHTML = ''; hidden.value = '';
            if (!q) { panel.style.display = 'none'; return; }
            timer = setTimeout(() => {
                fetch(searchUrl + '?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        if (!data.length) { panel.style.display = 'none'; return; }
                        data.forEach(b => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action border-0 py-2 px-3';
                            btn.innerHTML = `<strong>${b.nama}</strong> <small class="text-muted">${b.satuan}</small>`;
                            btn.addEventListener('click', () => {
                                input.value  = b.nama;
                                hidden.value = b.id;
                                panel.style.display = 'none';
                                if (b.harga > 0) {
                                    const hEl = row.querySelector('.harga-input');
                                    if (hEl && !hEl.dataset.raw) {
                                        hEl.value = b.harga.toLocaleString('id-ID');
                                        hEl.dataset.raw = Math.round(b.harga);
                                        row.querySelector('.harga-display-text').textContent = 'Saldo awal: Rp ' + formatNumber(b.harga);
                                    }
                                }
                                recalcRow(row);
                            });
                            panel.appendChild(btn);
                        });
                        panel.style.display = 'block';
                    });
            }, 280);
        });
    }

    function attachEventsToRow(row) {
        setupBahanAutocomplete(row);
        row.querySelector('.qty-input')?.addEventListener('input', () => recalcRow(row));

        // Harga: format ribuan saat user mengetik
        const hEl = row.querySelector('.harga-input');
        if (hEl) {
            hEl.addEventListener('input', function () {
                const raw = this.value.replace(/\D/g, '');
                this.dataset.raw = raw;
                const num = parseInt(raw) || 0;
                this.value = num > 0 ? num.toLocaleString('id-ID') : '';
                // Sync hidden field
                const rawHidden = row.querySelector('.harga-raw');
                if (rawHidden) rawHidden.value = raw || 0;
                recalcRow(row);
            });
        }

        row.querySelector('.btn-remove-row')?.addEventListener('click', () => {
            if (tableBody.querySelectorAll('.detail-row').length > 1) {
                row.remove();
                recalcTotal();
            }
        });
    }

    document.addEventListener('click', e => {
        document.querySelectorAll('.bahan-search-panel').forEach(p => {
            if (!p.closest('.position-relative')?.contains(e.target)) p.style.display = 'none';
        });
    });

    document.querySelector('form').addEventListener('submit', function() {
        // Sync semua hidden harga_beli[] dari display input
        tableBody.querySelectorAll('.detail-row').forEach(row => {
            const display = row.querySelector('.harga-input');
            const rawHidden = row.querySelector('.harga-raw');
            if (display && rawHidden) {
                rawHidden.value = (display.dataset.raw || display.value.replace(/\D/g, '')) || 0;
            }
        });
    });

    attachEventsToRow(tableBody.querySelector('.detail-row'));

    btnAddRow.addEventListener('click', () => {
        const firstRow = tableBody.querySelector('.detail-row');
        const newRow   = firstRow.cloneNode(true);
        newRow.querySelectorAll('input').forEach(el => el.value = '');
        newRow.querySelector('.bahan-search-panel').innerHTML = '';
        newRow.querySelector('.bahan-search-panel').style.display = 'none';
        newRow.querySelector('.subtotal-text').textContent = '—';
        newRow.querySelector('.harga-display-text').textContent = '';
        tableBody.appendChild(newRow);
        attachEventsToRow(newRow);
    });
});
</script>
@endpush
