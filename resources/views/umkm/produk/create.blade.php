@extends('layouts.umkm')

@section('title', 'Tambah Produk Baru')

@include('umkm.produk.partials.create-style')

@section('content')

{{-- Page Header --}}
<div class="mb-4">
    <a href="{{ route('umkm.produk.index') }}"
       style="display:inline-flex;align-items:center;gap:.35rem;font-size:.775rem;font-weight:500;color:#64748b;text-decoration:none;margin-bottom:.7rem;transition:color .15s;"
       onmouseover="this.style.color='#334155'" onmouseout="this.style.color='#64748b'">
        <i data-feather="arrow-left" style="width:13px;height:13px;"></i>
        Kembali ke Daftar Produk
    </a>
    <h1 class="h3 mb-0">Tambah Produk <strong>Baru</strong></h1>
    <p class="text-muted mb-0" style="font-size:.78rem;margin-top:2px;">Isi informasi produk dan komposisi resep bila tersedia.</p>
</div>

@if($errors->any())
    <div class="alert alert-danger rounded-3 mb-3" style="font-size:.8rem;">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

@php $overheadPerUnit = $overheadPerUnit ?? 0; @endphp

<form action="{{ route('umkm.produk.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- ══ CARD 1: Informasi Produk ══ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <p class="section-label">
                Informasi Produk
                @if(isset($kode))
                    <span style="text-transform:none;letter-spacing:0;font-weight:400;font-size:.68rem;color:#cbd5e1;font-family:monospace;">{{ $kode }}</span>
                @endif
            </p>

            {{-- Nama + Satuan --}}
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" name="nama_produk" class="form-control"
                           value="{{ old('nama_produk') }}"
                           placeholder="Contoh: Dimsum Ayam Original"
                           required autofocus>
                </div>
                <div class="col-md-3" style="min-width:175px;">
                    <label class="form-label">Satuan Produk <span class="text-danger">*</span></label>
                    <div class="ac-wrap" id="satuanProdukWrap">
                        <input type="text" name="satuan_produk" id="satuanProdukInp"
                               class="form-control"
                               value="{{ old('satuan_produk', 'pcs') }}"
                               placeholder="Cari atau ketik…" autocomplete="off" required>
                        <div class="ac-dd" id="satuanProdukDd"></div>
                    </div>
                </div>
            </div>

            {{-- Harga + Foto + Keterangan --}}
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                    <div class="rp-wrap">
                        <span class="rp-pfx">Rp</span>
                        <input type="text" id="harga_jual_display" class="form-control"
                               inputmode="numeric" autocomplete="off"
                               value="{{ old('harga_jual') ? number_format(old('harga_jual'),0,',','.') : '' }}"
                               placeholder="0">
                        <input type="hidden" name="harga_jual" id="harga_jual" value="{{ old('harga_jual') }}">
                        <input type="hidden" name="harga_pokok" id="harga_pokok" value="{{ old('harga_pokok') }}">
                    </div>
                    <div class="hpp-chip" id="hppChip" style="{{ old('harga_pokok') ? '' : 'display:none;' }} flex-wrap:wrap;">
                        <span>Est. HPP: <strong id="harga_pokok_display">{{ old('harga_pokok') ? 'Rp '.number_format(old('harga_pokok'),0,',','.') : '—' }}</strong></span>
                        <button type="button" class="btn-sim-link" id="btnSimulasi">
                            <i data-feather="bar-chart-2" style="width:11px;height:11px;"></i>
                            Hitung Estimasi
                        </button>
                    </div>
                    {{-- Selalu tampilkan tombol jika belum ada HPP --}}
                    <div id="btnSimWrapper" style="{{ old('harga_pokok') ? 'display:none;' : '' }} margin-top:6px;">
                        <button type="button" class="btn-sim-link" id="btnSimulasiAlt">
                            <i data-feather="bar-chart-2" style="width:11px;height:11px;"></i>
                            Hitung Estimasi HPP
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Foto Produk</label>
                    <div class="foto-zone" id="fotoZone" onclick="document.getElementById('fotoFile').click()">
                        <input type="file" name="foto" id="fotoFile" accept="image/*">
                        <div id="fotoPH">
                            <i data-feather="image" style="width:22px;height:22px;color:#94a3b8;display:block;margin:0 auto 5px;"></i>
                            <div class="foto-ph-text">
                                <strong>Klik untuk unggah</strong>
                                JPG, PNG, WebP · maks 2MB
                            </div>
                        </div>
                        <div id="fotoPrev" class="foto-prev-row" style="display:none;">
                            <img id="fotoPrevImg" src="" alt="" class="foto-prev-thumb">
                            <div>
                                <span class="foto-prev-name" id="fotoName">—</span>
                                <span class="foto-prev-size" id="fotoSize"></span>
                                <button type="button" class="btn-foto-clear" onclick="clearFoto(event)">Hapus pilihan</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="4"
                              placeholder="Deskripsi singkat produk…">{{ old('keterangan') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ CARD 2: Komposisi / Resep ══ --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body" style="padding:1.25rem 1.5rem;">
            <p class="section-label">Komposisi / Resep
                <span style="text-transform:none;letter-spacing:0;font-weight:400;font-size:.7rem;color:#94a3b8;">per 1 unit produk</span>
            </p>


            {{-- Column headers --}}
            <div class="komposisi-header">
                <span>Bahan Baku</span><span>Qty / unit</span><span>Satuan</span><span></span>
            </div>

            <div id="komposisi-wrapper">
                <div class="komposisi-row">
                    <div style="position:relative;">
                        <input type="hidden" name="bahan_id[]">
                        <input type="text" name="bahan_nama[]" class="form-control form-control-sm bahan-input"
                               placeholder="Ketik nama bahan…" autocomplete="off">
                        <div class="bahan-panel"></div>
                    </div>
                    <div>
                        <input type="number" step="0.001" name="qty[]"
                               class="form-control form-control-sm qty-input" placeholder="0">
                    </div>
                    <div class="ac-wrap resep-satuan-wrap">
                        <input type="text" class="form-control form-control-sm satuan-disp"
                               placeholder="Cari satuan…" autocomplete="off">
                        <input type="hidden" name="satuan[]" class="satuan-val satuan-input">
                        <div class="ac-dd resep-satuan-dd"></div>
                    </div>
                    <button type="button" class="btn-del remove-row" title="Hapus baris">×</button>
                </div>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btn-add-row">
                <i data-feather="plus" style="width:12px;height:12px;margin-right:3px;"></i>
                Tambah Bahan
            </button>

            {{-- Simulation result (inline, collapsible) --}}
            <div class="sim-result" id="sim-result">
                <div class="sim-result-grid">
                    <div class="sr-card">
                        <span class="sr-label">Biaya Bahan</span>
                        <span class="sr-val" id="biaya_bahan_display">Rp 0</span>
                    </div>
                    <div class="sr-card">
                        <span class="sr-label">Overhead / unit</span>
                        <span class="sr-val" id="overhead_display">Rp 0</span>
                    </div>
                    <div class="sr-card accent">
                        <span class="sr-label">HPP Estimasi</span>
                        <span class="sr-val" id="hpp_display">Rp 0</span>
                    </div>
                </div>
                <div class="markup-bar" id="markup-bar">
                    <span id="markup-text">—</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="form-footer">
        <button type="submit" class="btn btn-primary">
            <i data-feather="save" style="width:13px;height:13px;margin-right:4px;"></i>
            Simpan Produk
        </button>
        <a href="{{ route('umkm.produk.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>

@endsection

@include('umkm.produk.partials.create-script')
