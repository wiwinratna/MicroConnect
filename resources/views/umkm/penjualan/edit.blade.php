@extends('layouts.umkm')
@section('title', 'Edit Penjualan')

@push('styles')
<style>
    /* ── Header & Layout ── */
    .page-header { margin-bottom: 2rem; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem; }
    .page-subtitle { font-size: 0.875rem; color: #64748b; }

    /* ── Form Cards ── */
    .premium-card { background: #ffffff; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); overflow: hidden; }
    .card-section-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; }
    .card-section-title i { width: 14px; height: 14px; color: #3b82f6; }

    /* ── Form Controls ── */
    .form-group-custom { margin-bottom: 1.25rem; }
    .label-custom { display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem; }
    .input-custom { width: 100%; padding: 0.625rem 0.875rem; font-size: 0.875rem; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; transition: all 0.2s; color: #1e293b; }
    .input-custom:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
    .select-custom { appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 0.5rem center; background-size: 1.5em 1.5em; padding-right: 2.5rem; }

    /* ── Item Rows ── */
    .row-item { background: #f8fafc; border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem; border: 1px solid #f1f5f9; position: relative; transition: transform 0.2s, box-shadow 0.2s; }
    .row-item:hover { border-color: #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
    .btn-delete-row { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #fee2e2; background: #fff1f2; color: #ef4444; display: flex; align-items: center; justify-content: center; transition: all 0.2s; cursor: pointer; }
    .btn-delete-row:hover { background: #ef4444; color: #fff; border-color: #ef4444; }

    /* ── Action Section ── */
    .form-actions { background: #ffffff; border-top: 1px solid #f1f5f9; padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
    .btn-submit-premium { padding: 0.75rem 2rem; border-radius: 10px; font-weight: 600; font-size: 0.9375rem; background: #2563eb; color: #fff; border: none; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2); transition: all 0.2s; }
    .btn-submit-premium:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.2); }
    .btn-add-row { display: inline-flex; align-items: center; gap: 0.5rem; color: #2563eb; font-weight: 600; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 8px; background: #eff6ff; border: 1px solid #dbeafe; transition: all 0.2s; cursor: pointer; text-decoration: none; }
    .btn-add-row:hover { background: #dbeafe; }

    /* ── Special Notification ── */
    .piutang-banner { padding: 1rem; border-radius: 12px; background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; }
    .piutang-banner i { color: #f59e0b; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="d-flex justify-content-between align-items-start page-header">
            <div>
                <h1 class="page-title">Edit <strong>Penjualan</strong></h1>
                <p class="page-subtitle">Menyesuaikan transaksi <strong>{{ $penjualan->kode_penjualan }}</strong>. Sistem akan merestorasi stok dan jurnal otomatis.</p>
            </div>
            <a href="{{ route('umkm.penjualan.index') }}" class="btn btn-light shadow-sm" style="border-radius: 10px; padding: 0.625rem 1.25rem; font-weight: 600; color: #64748b; font-size: 0.8125rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-feather="arrow-left" style="width: 14px; height: 14px;"></i> Kembali
            </a>
        </div>

        @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px; background: #fef2f2; color: #991b1b;">
            <ul class="mb-0 small fw-medium">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('umkm.penjualan.update', $penjualan->id) }}">
            @csrf
            @method('PUT')

            <div class="premium-card mb-4">
                <div class="p-4 p-md-5">
                    <div class="card-section-title">
                        <i data-feather="info"></i> Informasi Utama Transaksi
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label class="label-custom">Tanggal Penjualan</label>
                                <input type="date" name="tanggal" class="input-custom" required value="{{ old('tanggal', $penjualan->tanggal->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label class="label-custom">Metode Pembayaran</label>
                                <select name="metode_pembayaran" id="metode_pembayaran" class="input-custom select-custom" onchange="togglePiutangFields()">
                                    <option value="tunai" {{ old('metode_pembayaran', $penjualan->piutang ? 'piutang' : 'tunai') == 'tunai' ? 'selected' : '' }}>Tunai (Lunas)</option>
                                    <option value="piutang" {{ old('metode_pembayaran', $penjualan->piutang ? 'piutang' : 'tunai') == 'piutang' ? 'selected' : '' }}>Piutang (Kredit)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label class="label-custom">Nama Pembeli (Opsional)</label>
                                <input type="text" name="pembeli" class="input-custom" value="{{ old('pembeli', $penjualan->pembeli) }}" placeholder="Nama pembeli...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group-custom">
                                <label class="label-custom">Kode Transaksi</label>
                                <input type="text" class="input-custom bg-light" value="{{ $penjualan->kode_penjualan }}" readonly style="font-family: monospace;">
                            </div>
                        </div>
                    </div>

                    <div id="piutang_fields" style="display: none;">
                        <div class="piutang-banner">
                            <i data-feather="alert-circle"></i>
                            <div style="font-size: 0.8125rem; line-height: 1.4;">
                                <strong>Transaksi Kredit.</strong> Perubahan data di sini akan secara otomatis memperbarui catatan piutang terkait.
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-custom">
                                    <label class="label-custom">Pelanggan Terdaftar</label>
                                    <select name="pelanggan_id" id="pelanggan_id" class="input-custom select-custom">
                                        <option value="">-- Pilih Pelanggan --</option>
                                        @foreach($pelanggan as $plg)
                                            <option value="{{ $plg->id }}" {{ old('pelanggan_id', $penjualan->piutang->pelanggan_id ?? '') == $plg->id ? 'selected' : '' }}>{{ $plg->nama_pelanggan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-custom">
                                    <label class="label-custom">Batas Waktu Jatuh Tempo</label>
                                    <input type="date" name="jatuh_tempo" id="jatuh_tempo" class="input-custom" value="{{ old('jatuh_tempo', optional(optional($penjualan->piutang)->jatuh_tempo)->format('Y-m-d')) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-custom mt-2">
                        <label class="label-custom">Catatan Tambahan</label>
                        <textarea name="catatan" class="input-custom" rows="2" placeholder="Tulis catatan internal jika ada...">{{ old('catatan', $penjualan->catatan) }}</textarea>
                    </div>

                    <div class="mt-5 mb-4">
                        <div class="card-section-title">
                            <i data-feather="package"></i> Rincian Produk Dijual
                        </div>
                    </div>

                    <div id="rows-container">
                        <div class="row g-2 mb-2 d-none d-md-flex" style="padding: 0 1.25rem;">
                            <div class="col-md-8"><span class="label-custom m-0">Produk</span></div>
                            <div class="col-md-3 text-center"><span class="label-custom m-0">Kuantitas (Qty)</span></div>
                            <div class="col-md-1"></div>
                        </div>

                        <div id="item-rows">
                            @php
                                $details = old('produk_id') ? collect(old('produk_id'))->map(function($id, $i) {
                                    return (object) ['produk_id' => $id, 'qty' => old('qty')[$i]];
                                }) : $penjualan->details;
                            @endphp

                            @foreach($details as $index => $det)
                            <div class="row-item">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-8">
                                        <label class="label-custom d-md-none">Produk</label>
                                        <select name="produk_id[]" class="input-custom select-custom" required>
                                            <option value="">- pilih produk -</option>
                                            @foreach($produk as $p)
                                                <option value="{{ $p->id }}" {{ $det->produk_id == $p->id ? 'selected' : '' }}>{{ $p->nama_produk }} — {{ rupiah($p->harga_jual) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="label-custom d-md-none">Qty</label>
                                        <input type="number" step="0.001" name="qty[]" class="input-custom text-center" placeholder="0" required value="{{ $det->qty }}">
                                    </div>
                                    <div class="col-md-1 d-flex justify-content-end">
                                        <button type="button" class="btn-delete-row" onclick="removeRow(this)">
                                            <i data-feather="x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn-add-row" onclick="addRow()">
                                <i data-feather="plus-circle" style="width: 16px; height: 16px;"></i> Tambah Baris Produk
                            </button>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <div class="text-muted small">
                        * Perubahan akan menghapus log stok lama & membuat log mutasi stok baru.
                    </div>
                    <button type="submit" class="btn btn-primary btn-submit-premium">
                        Simpan Perubahan Penjualan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function togglePiutangFields() {
    const method = document.getElementById('metode_pembayaran').value;
    const fields = document.getElementById('piutang_fields');
    if (method === 'piutang') {
        fields.style.display = 'block';
    } else {
        fields.style.display = 'none';
    }
}

window.addEventListener('DOMContentLoaded', () => {
    togglePiutangFields();
    if(typeof feather !== 'undefined') feather.replace();
});

function addRow() {
    const rows = document.getElementById('item-rows');
    const div = document.createElement('div');
    div.className = 'row-item';
    div.innerHTML = `
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <select name="produk_id[]" class="input-custom select-custom" required>
                    <option value="">- pilih produk -</option>
                    @foreach($produk as $p)
                        <option value="{{ $p->id }}">{{ $p->nama_produk }} — {{ rupiah($p->harga_jual) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.001" name="qty[]" class="input-custom text-center" placeholder="0" required>
            </div>
            <div class="col-md-1 d-flex justify-content-end">
                <button type="button" class="btn-delete-row" onclick="removeRow(this)">
                    <i data-feather="x" style="width:14px; height:14px;"></i>
                </button>
            </div>
        </div>
    `;
    rows.appendChild(div);
    if(typeof feather !== 'undefined') feather.replace();
}

function removeRow(btn) {
    const rowItems = document.querySelectorAll('#item-rows .row-item');
    if (rowItems.length > 1) {
        btn.closest('.row-item').remove();
    } else {
        const inputs = btn.closest('.row-item').querySelectorAll('input, select');
        inputs.forEach(el => el.value = '');
    }
}
</script>
@endsection
