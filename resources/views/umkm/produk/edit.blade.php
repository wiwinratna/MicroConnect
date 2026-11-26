@extends('layouts.umkm')

@section('title', 'Edit Produk')

@include('umkm.produk.partials.edit-style')

@section('content')

{{-- Page Header --}}
<div class="mb-4">
    <a href="{{ route('umkm.produk.index') }}"
       style="display:inline-flex;align-items:center;gap:.35rem;font-size:.775rem;font-weight:500;color:var(--mn-text-muted);text-decoration:none;margin-bottom:.7rem;transition:color .15s;"
       onmouseover="this.style.color='var(--mn-text-main)'" onmouseout="this.style.color='var(--mn-text-muted)'">
        <i data-feather="arrow-left" style="width:13px;height:13px;"></i>
        Kembali ke Daftar Produk
    </a>
    <h1 class="h3 mb-0">Edit <strong>Produk</strong></h1>
    <p class="text-muted mb-0" style="font-size:.78rem;margin-top:2px;">Perbarui informasi produk dan komposisi resep.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger rounded-3 mb-3" style="font-size:.8rem;">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('umkm.produk.update', $produk->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <input type="hidden" name="kode_produk" value="{{ $produk->kode_produk }}">

    {{-- ══ CARD 1: Informasi Produk ══ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <p class="section-label">
                Informasi Produk
                <span class="kode-ghost">{{ $produk->kode_produk }}</span>
            </p>

            {{-- Nama + Satuan --}}
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="nama_produk" class="form-control"
                           value="{{ old('nama_produk', $produk->nama_produk) }}" required autofocus>
                </div>
                <div class="col-md-3" style="min-width:175px;">
                    <label class="form-label">Satuan Produk <span class="text-danger">*</span></label>
                    {{-- Searchable autocomplete: direct text input with custom value allowed --}}
                    <div class="ac-wrap" id="satuanProdukWrap">
                        <input type="text"
                               name="satuan_produk"
                               id="satuanProdukInput"
                               class="form-control"
                               value="{{ old('satuan_produk', $produk->satuan) }}"
                               placeholder="Cari atau ketik satuan…"
                               autocomplete="off"
                               required>
                        <div class="ac-dropdown" id="satuanProdukDropdown"></div>
                    </div>
                </div>
            </div>

            {{-- Harga + Foto + Keterangan --}}
            <div class="row g-3">
                {{-- Harga Jual --}}
                <div class="col-md-4">
                    <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                    <div class="rp-wrap">
                        <span class="rp-pfx">Rp</span>
                        {{-- Display: formatted (text), Hidden: raw number --}}
                        <input type="text" id="harga_display" class="form-control"
                               inputmode="numeric" autocomplete="off"
                               placeholder="0">
                        <input type="hidden" name="harga_jual" id="harga_jual_raw"
                               value="{{ old('harga_jual', $produk->harga_jual) }}">
                    </div>
                    <div class="hpp-hint">
                        <span class="hpp-hint-text">Est. HPP: <strong id="text_hpp_now">{{ rupiah($produk->harga_pokok ?? 0) }}</strong></span>
                        <span class="hpp-est-badge">Estimasi</span>
                        <button type="button" class="btn-sim" onclick="openSim()">
                            <i data-feather="bar-chart-2" style="width:11px;height:11px;"></i>
                            Simulasi
                        </button>
                    </div>
                </div>

                {{-- Foto Upload --}}
                <div class="col-md-4">
                    <label class="form-label">Foto Produk</label>
                    <div class="foto-zone" id="fotoZone" onclick="document.getElementById('fotoFile').click()">
                        <input type="file" name="foto" id="fotoFile" accept="image/*">
                        <div id="fotoPH" class="foto-placeholder">
                            <i data-feather="image" style="width:24px;height:24px;color:var(--mn-text-faint);display:block;margin:0 auto 5px;"></i>
                            <div class="foto-placeholder-text">
                                <strong>Klik untuk unggah</strong>
                                JPG, PNG, WebP
                            </div>
                        </div>
                        <div id="fotoPrev" class="foto-preview-row" style="display:none;">
                            <img id="fotoPrevImg" src="" alt="" class="foto-prev-thumb">
                            <div class="foto-prev-meta">
                                <span class="foto-prev-name" id="fotoName">—</span>
                                <span class="foto-prev-size" id="fotoSize"></span>
                                <button type="button" class="btn-foto-clear" onclick="clearFoto(event)">Hapus pilihan</button>
                            </div>
                        </div>
                    </div>
                    @if($produk->foto_path)
                        <div class="foto-existing">
                            <img src="{{ asset('storage/'.$produk->foto_path) }}" alt="Foto produk">
                            <span class="foto-existing-label">Foto saat ini.<br>Unggah baru untuk mengganti.</span>
                        </div>
                    @endif
                </div>

                {{-- Keterangan --}}
                <div class="col-md-4">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="4"
                              placeholder="Deskripsi singkat produk…">{{ old('keterangan', $produk->keterangan) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ CARD 2: Komposisi / Resep ══ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <p class="section-label">
                Komposisi / Resep
                <span style="text-transform:none;letter-spacing:0;font-weight:400;font-size:.7rem;color:var(--mn-text-faint);">per 1 unit produk</span>
            </p>

            @if($komposisi->isEmpty())
                <div class="d-flex align-items-center gap-2 mb-3 px-3 py-2 rounded-2"
                     style="background:var(--mn-warning-bg);border:1px solid var(--mn-warning-border);font-size:.78rem;color:var(--mn-warning-text);">
                    <i data-feather="alert-triangle" style="width:14px;height:14px;flex-shrink:0;"></i>
                    Belum ada resep. Tambahkan bahan baku di bawah.
                </div>
            @endif

            {{-- Column headers --}}
            <div class="komposisi-header">
                <span>Bahan Baku</span><span>Qty / unit</span><span>Satuan</span><span></span>
            </div>

            <div id="komposisi-wrapper">
                @forelse($komposisi as $k)
                <div class="komposisi-row">
                    <div style="position:relative;">
                        <input type="hidden" name="bahan_id[]" value="{{ $k->bahan_id }}">
                        <input type="text" name="bahan_nama[]" class="form-control form-control-sm bahan-input"
                               value="{{ $k->bahan->nama_bahan ?? '' }}"
                               placeholder="Ketik nama bahan…" autocomplete="off">
                        <div class="bahan-panel"></div>
                    </div>
                    <div>
                        <input type="number" step="0.001" name="qty[]"
                               class="form-control form-control-sm"
                               value="{{ $k->qty }}" placeholder="0">
                    </div>
                    {{-- Satuan resep: ac-wrap with hidden input --}}
                    <div class="ac-wrap resep-satuan-wrap">
                        <input type="text" class="form-control form-control-sm satuan-disp"
                               placeholder="Cari satuan…" autocomplete="off"
                               value="{{ collect($satuanOptions)->firstWhere('value', $k->satuan)['label'] ?? $k->satuan }}">
                        <input type="hidden" name="satuan[]" class="satuan-val" value="{{ $k->satuan }}">
                        <div class="ac-dropdown resep-satuan-dd"></div>
                    </div>
                    <button type="button" class="btn-del remove-row" title="Hapus baris">×</button>
                </div>
                @empty
                <div class="komposisi-row">
                    <div style="position:relative;">
                        <input type="hidden" name="bahan_id[]">
                        <input type="text" name="bahan_nama[]" class="form-control form-control-sm bahan-input"
                               placeholder="Ketik nama bahan…" autocomplete="off">
                        <div class="bahan-panel"></div>
                    </div>
                    <div>
                        <input type="number" step="0.001" name="qty[]"
                               class="form-control form-control-sm" placeholder="0">
                    </div>
                    <div class="ac-wrap resep-satuan-wrap">
                        <input type="text" class="form-control form-control-sm satuan-disp"
                               placeholder="Cari satuan…" autocomplete="off">
                        <input type="hidden" name="satuan[]" class="satuan-val">
                        <div class="ac-dropdown resep-satuan-dd"></div>
                    </div>
                    <button type="button" class="btn-del remove-row" title="Hapus baris">×</button>
                </div>
                @endforelse
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-row">
                <i data-feather="plus" style="width:12px;height:12px;margin-right:3px;"></i>
                Tambah Bahan
            </button>
        </div>
    </div>

    {{-- Actions --}}
    <div class="form-footer">
        <button type="submit" class="btn btn-primary">
            <i data-feather="save" style="width:13px;height:13px;margin-right:4px;"></i>
            Simpan Perubahan
        </button>
        <a href="{{ route('umkm.produk.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

{{-- ══ SIMULASI MODAL ══ --}}
<div class="sim-overlay" id="simOverlay" onclick="closeSim(event)">
    <div class="sim-modal">
        <div class="sim-head">
            <span class="sim-title">
                <i data-feather="bar-chart-2" style="width:14px;height:14px;color:var(--mn-primary);vertical-align:-.15em;margin-right:5px;"></i>
                Simulasi Harga Jual
            </span>
            <button class="sim-x" onclick="closeSim()">✕</button>
        </div>
        <div class="sim-body">
            <p class="sim-note">
                <strong>Estimasi saja.</strong> Dihitung dari harga beli terakhir bahan resep — bukan HPP aktual.
                Tidak dicatat di jurnal. Simpan perubahan resep sebelum simulasi.
            </p>
            <div class="d-flex align-items-end gap-3">
                <div style="flex:1;">
                    <label class="form-label" style="font-size:.78rem;">Target Margin (%)</label>
                    <input type="number" id="input_margin" class="form-control" value="30" min="0" step="1">
                </div>
                <button type="button" id="btnHitungHpp" class="btn btn-primary" style="height:38px;white-space:nowrap;">
                    Hitung Estimasi
                </button>
            </div>
            <div id="hasilHpp" style="display:none;">
                <div class="sim-grid">
                    <div class="sc"><span class="sl">Biaya Bahan</span><span class="sv" id="res_biaya_bahan">—</span></div>
                    <div class="sc"><span class="sl">Overhead</span><span class="sv" id="res_overhead">—</span></div>
                    <div class="sc full"><span class="sl">HPP Estimasi</span><span class="sv" id="res_hpp_total">—</span></div>
                    <div class="sc green">
                        <span class="sl">Saran Harga (Margin <span id="res_margin_target">30</span>%)</span>
                        <span class="sv" id="res_saran_harga">—</span>
                        <button type="button" class="btn btn-sm btn-success mt-2 w-100" id="btnGunakanHarga">Gunakan Harga Ini</button>
                    </div>
                    <div class="sc indigo">
                        <span class="sl">Margin Aktual</span>
                        <span class="sv" id="res_margin_aktual">—</span>
                        <div style="font-size:.7rem;color:var(--mn-text-muted);margin-top:3px;">dari <span id="res_harga_now">—</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@include('umkm.produk.partials.edit-script')
