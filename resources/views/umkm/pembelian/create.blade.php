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

    <form action="{{ route('umkm.pembelian.store') }}" method="POST">
        @csrf

        {{-- HEADER PEMBELIAN --}}
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Pembelian (Auto)</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $kode ?? '-' }}"
                               readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nomor Nota Vendor</label>
                        <input type="text"
                               name="nomor_nota"
                               class="form-control"
                               value="{{ old('nomor_nota') }}"
                               placeholder="Contoh: STRUK-8891">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal</label>
                        <input type="date"
                               name="tanggal"
                               class="form-control"
                               value="{{ old('tanggal', date('Y-m-d')) }}"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <input type="text"
                               name="supplier"
                               class="form-control"
                               value="{{ old('supplier') }}"
                               placeholder="Nama toko / pemasok (opsional)">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan"
                                  rows="1"
                                  class="form-control"
                                  placeholder="Catatan tambahan (opsional)">{{ old('catatan') }}</textarea>
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
                    <table class="table align-middle mb-0" id="detail-table">
                        <thead class="table-light">
                        <tr>
                            <th style="width: 35%">Bahan</th>
                            <th style="width: 15%">Qty</th>
                            <th style="width: 20%">Harga Beli (Rp)</th>
                            <th style="width: 20%" class="text-end">Subtotal (Rp)</th>
                            <th style="width: 10%"></th>
                        </tr>
                        </thead>
                        <tbody>
                        {{-- baris awal --}}
                        <tr>
                            <td>
                                <select name="bahan_id[]" class="form-select">
                                    <option value="">-- Pilih Bahan --</option>
                                    @foreach($bahan as $b)
                                        <option value="{{ $b->id }}">
                                            {{ $b->kode_bahan }} - {{ $b->nama_bahan }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number"
                                       name="qty[]"
                                       step="0.001"
                                       min="0"
                                       class="form-control qty-input">
                            </td>
                            <td>
                                <input type="number"
                                       name="harga_beli[]"
                                       step="1"
                                       min="0"
                                       class="form-control harga-input">
                            </td>
                            <td class="text-end">
                                <span class="subtotal-text">0</span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">
                                    &times;
                                </button>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end">
                                <span id="grand-total">0</span>
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tableBody = document.querySelector('#detail-table tbody');
                const btnAddRow = document.getElementById('btn-add-row');
                const grandTotalEl = document.getElementById('grand-total');

                function formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(num || 0);
                }

                function recalcRow(row) {
                    const qtyInput = row.querySelector('.qty-input');
                    const hargaInput = row.querySelector('.harga-input');
                    const subtotalText = row.querySelector('.subtotal-text');

                    const qty = parseFloat(qtyInput.value) || 0;
                    const harga = parseFloat(hargaInput.value) || 0;
                    const subtotal = qty * harga;

                    subtotalText.textContent = formatNumber(subtotal);
                    recalcTotal();
                }

                function recalcTotal() {
                    let total = 0;
                    document.querySelectorAll('#detail-table tbody tr').forEach(row => {
                        const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
                        const harga = parseFloat(row.querySelector('.harga-input')?.value || 0);
                        total += qty * harga;
                    });
                    grandTotalEl.textContent = formatNumber(total);
                }

                function attachEventsToRow(row) {
                    const qtyInput = row.querySelector('.qty-input');
                    const hargaInput = row.querySelector('.harga-input');
                    const btnRemove = row.querySelector('.btn-remove-row');

                    qtyInput.addEventListener('input', () => recalcRow(row));
                    hargaInput.addEventListener('input', () => recalcRow(row));
                    btnRemove.addEventListener('click', () => {
                        if (document.querySelectorAll('#detail-table tbody tr').length > 1) {
                            row.remove();
                            recalcTotal();
                        }
                    });
                }

                // event untuk baris awal
                attachEventsToRow(tableBody.querySelector('tr'));

                btnAddRow.addEventListener('click', () => {
                    const firstRow = tableBody.querySelector('tr');
                    const newRow = firstRow.cloneNode(true);

                    // reset nilai
                    newRow.querySelectorAll('select, input').forEach(el => el.value = '');
                    newRow.querySelector('.subtotal-text').textContent = '0';

                    tableBody.appendChild(newRow);
                    attachEventsToRow(newRow);
                });
            });
        </script>
    @endpush
@endsection
