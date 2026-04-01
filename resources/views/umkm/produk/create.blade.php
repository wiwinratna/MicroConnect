@extends('layouts.umkm')

@section('title', 'Tambah Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><strong>Tambah</strong> Produk</h1>
            <p class="text-muted mb-0">
                Daftarkan produk jadi yang akan dijual, beserta komposisi bahan per 1 unit + simulasi harga jual dari estimasi overhead anggaran.
            </p>
        </div>
        <a href="{{ route('umkm.produk.index') }}" class="btn btn-outline-secondary">
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

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">

            {{-- ===================== ALERT ANGGARAN ===================== --}}
            @php
                $overheadPerUnit = $overheadPerUnit ?? 0;
            @endphp

            @if($overheadPerUnit <= 0)
                <div class="alert alert-warning d-flex align-items-start gap-2">
                    <div style="font-size:18px; line-height:1;">&#9888;</div>
                    <div>
                        <div class="fw-semibold">Anggaran bulanan bulan ini belum ada / target unit masih 0.</div>
                        <div class="small text-muted">
                            Overhead per unit akan dianggap <b>Rp 0</b>. Kamu tetap bisa input komposisi dan harga jual.
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('umkm.produk.store') }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf

                {{-- ===================== DATA DASAR PRODUK ===================== --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Produk (Auto)</label>
                        <input type="text" class="form-control" value="{{ $kode ?? '-' }}" readonly>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Nama Produk</label>
                        <input type="text"
                               name="nama_produk"
                               class="form-control"
                               value="{{ old('nama_produk') }}"
                               required
                               placeholder="Contoh: Dimsum Ayam Original">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Satuan Produk</label>
                        <input type="text"
                               name="satuan_produk"
                               class="form-control"
                               value="{{ old('satuan_produk', 'pcs') }}"
                               required
                               placeholder="Contoh: pcs/porsi/box">
                        <small class="text-muted">Ini satuan output produk jadi.</small>
                    </div>

                    {{-- HARGA POKOK ESTIMASI (readonly, hasil hitung) --}}
                    <div class="col-md-4">
                        <label class="form-label text-primary fw-semibold">HPP Estimasi (Untuk Simulasi)</label>
                        <input type="text"
                               id="harga_pokok_display"
                               class="form-control bg-light"
                               value="{{ old('harga_pokok') ? format_angka(old('harga_pokok')) : '' }}"
                               readonly
                               placeholder="Klik tombol Simulasi dulu">
                        <input type="hidden" name="harga_pokok" id="harga_pokok" value="{{ old('harga_pokok') }}">
                        <small class="text-muted mt-1 d-block lh-sm" style="font-size:0.8rem">
                            HPP Estimasi = biaya bahan (resep) + overhead/unit.<br>
                            <span class="text-danger fw-semibold">Hanya untuk Simulasi Harga Jual.</span> Laporan Keuangan Aktual tetap menggunakan harga pokok stok fisik yang keluar (FIFO/LIFO/Average).
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="text"
                               id="harga_jual_display"
                               class="form-control"
                               value="{{ old('harga_jual') ? number_format(old('harga_jual'), 0, ',', '.') : '' }}"
                               placeholder="Contoh: 15.000"
                               inputmode="numeric">
                        <input type="hidden" name="harga_jual" id="harga_jual" value="{{ old('harga_jual') }}">
                        <small class="text-muted">
                            Setelah HPP ada, sistem akan hitung markup otomatis.
                        </small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Foto Produk (opsional)</label>
                        <input type="file" name="foto" class="form-control">
                        <small class="text-muted">
                            Maksimal 2MB, format gambar (jpg, png, webp).
                        </small>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan (opsional)</label>
                        <textarea name="keterangan"
                                  class="form-control"
                                  rows="2"
                                  placeholder="Catatan tambahan tentang produk">{{ old('keterangan') }}</textarea>
                    </div>
                </div>

                {{-- ===================== KOMPOSISI ===================== --}}
                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1">Komposisi / Resep per 1 unit produk</h5>
                        <p class="text-muted small mb-0">
                            Tambahkan bahan baku untuk membuat <strong>1 unit</strong> produk ini.
                            <span class="text-danger fw-semibold">Wajib diisi</span> agar HPP dan stok bahan terhitung otomatis saat penjualan.
                        </p>
                    </div>

                    {{-- tombol hitung HPP --}}
                    <button type="button" class="btn btn-outline-primary" id="btn-hitung-hpp">
                        Simulasi Harga Jual
                    </button>
                </div>

                <div class="alert alert-warning d-flex gap-2 align-items-start mt-2 py-2" id="alert-no-resep" style="display:none;">
                    <span>⚠️</span>
                    <div class="small">Produk tanpa komposisi bahan akan memiliki <strong>HPP = Rp 0</strong> dan stok bahan <strong>tidak berkurang</strong> saat penjualan.</div>
                </div>

                <div class="mt-3" id="hpp-breakdown" style="display:none;">
                    <div class="card border-0 shadow-sm" style="border-radius:16px;">
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="text-muted small">Biaya Bahan / unit</div>
                                    <div class="fw-bold" id="biaya_bahan_display">Rp 0</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">Overhead / unit (Anggaran)</div>
                                    <div class="fw-bold" id="overhead_display">Rp 0</div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-muted small">HPP / unit</div>
                                    <div class="fw-bold" id="hpp_display">Rp 0</div>
                                </div>
                                <div class="col-12">
                                    <div class="text-muted small" id="hpp_note"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- markup info --}}
                <div class="mt-3" id="markup-box" style="display:none;">
                    <div class="alert alert-info mb-0">
                        <div class="fw-semibold" id="markup_text">Markup kamu: -</div>
                        <div class="small text-muted" id="markup_subtext"></div>
                    </div>
                </div>

                <div class="mt-3" id="komposisi-wrapper">
                    {{-- baris komposisi awal --}}
                    <div class="row g-2 komposisi-row mb-2">
                        <div class="col-md-4 position-relative">
                            <input type="hidden" name="bahan_id[]">
                            <label class="form-label small mb-1">Bahan Baku</label>
                            <input type="text"
                                   name="bahan_nama[]"
                                   class="form-control bahan-input"
                                   placeholder="Ketik nama bahan..."
                                   autocomplete="off">
                            <div class="autocomplete-panel list-group shadow-sm bahan-panel"
                                 style="position:absolute; z-index:30; top:100%; left:0; right:0;
                                        max-height:220px; overflow:auto; display:none;">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small mb-1">Qty per 1 unit</label>
                            <input type="number"
                                   step="0.001"
                                   name="qty[]"
                                   class="form-control qty-input"
                                   placeholder="Qty">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small mb-1">Satuan</label>
                            <select name="satuan[]" class="form-select satuan-input">
                                <option value="">-- pilih --</option>
                                @foreach($satuanOptions as $opt)
                                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Satuan pemakaian resep ini</small>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small mb-1">Harga / unit</label>
                            <input type="text" class="form-control harga-display" readonly placeholder="Rp 0">
                            <small class="text-muted small harga-satuan-note"></small>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label small mb-1">Biaya</label>
                            <input type="text" class="form-control biaya-display" readonly placeholder="Rp 0">
                        </div>

                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-action btn-action-delete remove-row" title="Hapus">
                                <i data-feather="trash-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-add-row">
                        + Tambah Bahan
                    </button>
                </div>

                <hr class="my-4">

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        Simpan Produk
                    </button>
                    <a href="{{ route('umkm.produk.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bahanMaster    = @json($bahanMaster ?? []);
    const overheadPerUnit = {{ $overheadPerUnit ?? 0 }};

    const wrapper          = document.getElementById('komposisi-wrapper');
    const addRowBtn        = document.getElementById('btn-add-row');
    const hargaPokokHidden = document.getElementById('harga_pokok');
    const hargaPokokDisplay= document.getElementById('harga_pokok_display');
    const hargaJualInput   = document.getElementById('harga_jual');
    const btnHitung        = document.getElementById('btn-hitung-hpp');
    const breakdownBox     = document.getElementById('hpp-breakdown');
    const biayaBahanDisplay= document.getElementById('biaya_bahan_display');
    const overheadDisplay  = document.getElementById('overhead_display');
    const hppDisplay       = document.getElementById('hpp_display');
    const hppNote          = document.getElementById('hpp_note');
    const markupBox        = document.getElementById('markup-box');
    const markupText       = document.getElementById('markup_text');
    const markupSub        = document.getElementById('markup_subtext');

    function rupiah(num) {
        return 'Rp ' + Math.round(num).toLocaleString('id-ID');
    }

    // ==== AUTOCOMPLETE ====
    function setupAutocomplete(row) {
        const input  = row.querySelector('.bahan-input');
        const hidden = row.querySelector('input[name="bahan_id[]"]');
        const panel  = row.querySelector('.bahan-panel');

        if (!input || !panel) return;

        input.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            panel.innerHTML = '';
            hidden.value = '';

            if (!q) { panel.style.display = 'none'; return; }

            const matches = bahanMaster.filter(b =>
                b.nama.toLowerCase().includes(q)
            ).slice(0, 8);

            if (!matches.length) { panel.style.display = 'none'; return; }

            matches.forEach(b => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action border-0 py-2 px-3';
                item.innerHTML = `<strong>${b.nama}</strong> <small class="text-muted">${b.satuan}</small>`;
                item.addEventListener('click', function () {
                    input.value  = b.nama;
                    hidden.value = b.id;
                    panel.style.display = 'none';
                    updateRowCost(row);
                });
                panel.appendChild(item);
            });
            panel.style.display = 'block';
        });

        document.addEventListener('click', function (e) {
            if (!row.contains(e.target)) panel.style.display = 'none';
        });
    }

    function syncBahanIdByName(row) {
        const input  = row.querySelector('.bahan-input');
        const hidden = row.querySelector('input[name="bahan_id[]"]');
        if (!input || !hidden || hidden.value) return;
        const found = bahanMaster.find(b => b.nama.toLowerCase() === input.value.toLowerCase().trim());
        if (found) hidden.value = found.id;
    }

    // ==== KONVERSI SATUAN ====
    const unitRatios = {
        'kg': { 'g': 0.001, 'kg': 1, 'gr': 0.001, 'gram': 0.001 },
        'liter': { 'ml': 0.001, 'liter': 1, 'cc': 0.001 },
        'pcs': { 'pcs': 1 }, 'buah': { 'buah': 1 },
    };

    function convertUnit(qty, fromUnit, toUnit) {
        if (!fromUnit || !toUnit || fromUnit === toUnit) return qty;
        const base = toUnit.toLowerCase();
        const from = fromUnit.toLowerCase();
        const groupKeys = Object.keys(unitRatios);
        for (const key of groupKeys) {
            const group = unitRatios[key];
            if (base in group && from in group) {
                return qty * (group[from] / group[base]);
            }
        }
        return qty;
    }

    // ==== UPDATE ROW COST ====
    function updateRowCost(row) {
        const hidden  = row.querySelector('input[name="bahan_id[]"]');
        const qtyEl   = row.querySelector('.qty-input');
        const satuanEl= row.querySelector('.satuan-input');
        const hargaEl = row.querySelector('.harga-display');
        const biayaEl = row.querySelector('.biaya-display');
        const noteEl  = row.querySelector('.harga-satuan-note');

        if (!hidden || !qtyEl || !hargaEl || !biayaEl) return;

        const bahanId = hidden.value;
        const bahan   = bahanMaster.find(b => String(b.id) === String(bahanId));

        if (!bahan) {
            hargaEl.value = '';
            biayaEl.value = '';
            if (noteEl) noteEl.textContent = '';
            return;
        }

        const harga   = Number(bahan.harga || 0);
        const qty     = parseFloat(qtyEl.value) || 0;
        const satuan  = satuanEl ? satuanEl.value : bahan.satuan;
        const qtyBase = convertUnit(qty, satuan, bahan.satuan);
        const biaya   = qtyBase * harga;

        hargaEl.value = rupiah(harga);
        biayaEl.value = rupiah(biaya);
        if (noteEl) noteEl.textContent = `per ${bahan.satuan}`;
    }

    // ==== SETUP ROW ====
    function setupRow(row) {
        setupAutocomplete(row);
        row.querySelector('.qty-input')?.addEventListener('input', () => updateRowCost(row));
        row.querySelector('.satuan-input')?.addEventListener('change', () => updateRowCost(row));
    }

    // ==== CREATE NEW ROW ====
    function createKomposisiRow() {
        const firstRow = wrapper.querySelector('.komposisi-row');
        const newRow   = firstRow.cloneNode(true);

        newRow.querySelectorAll('input').forEach(i => {
            i.value = '';
            if (i.readOnly && i.type !== 'hidden') { i.placeholder = i.placeholder || 'Rp 0'; }
        });
        newRow.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        const oldPanel = newRow.querySelector('.bahan-panel');
        if (oldPanel) { oldPanel.innerHTML = ''; oldPanel.style.display = 'none'; }
        const note = newRow.querySelector('.harga-satuan-note');
        if (note) note.textContent = '';

        setupRow(newRow);
        return newRow;
    }

    // setup row pertama
    setupRow(wrapper.querySelector('.komposisi-row'));

    // tambah row
    addRowBtn?.addEventListener('click', () => wrapper.appendChild(createKomposisiRow()));

    // hapus row
    wrapper.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.remove-row');
        if (!removeBtn) return;
        const rows = wrapper.querySelectorAll('.komposisi-row');
        if (rows.length === 1) {
            rows[0].querySelectorAll('input:not([type=hidden])').forEach(i => i.value = '');
            rows[0].querySelectorAll('select').forEach(s => s.selectedIndex = 0);
            rows[0].querySelector('input[name="bahan_id[]"]').value = '';
            updateRowCost(rows[0]);
            return;
        }
        removeBtn.closest('.komposisi-row').remove();
    });

    // ==== HITUNG HPP ====
    function computeBiayaBahan() {
        let total = 0;
        wrapper.querySelectorAll('.komposisi-row').forEach(row => {
            syncBahanIdByName(row);
            const hidden  = row.querySelector('input[name="bahan_id[]"]');
            const qtyEl   = row.querySelector('.qty-input');
            const satuanEl= row.querySelector('.satuan-input');
            const bahanId = hidden?.value;
            const bahan   = bahanMaster.find(b => String(b.id) === String(bahanId));
            if (!bahanId || !bahan) return;
            const harga   = Number(bahan.harga || 0);
            const qty     = parseFloat(qtyEl?.value) || 0;
            const satuan  = satuanEl?.value;
            if (!qty) return;
            const qtyBase = convertUnit(qty, satuan, bahan.satuan);
            total += (qtyBase * harga);
            updateRowCost(row);
        });
        return total;
    }

    function updateMarkupInfo() {
        const hpp  = Number(hargaPokokHidden?.value || 0);
        const jual = Number(hargaJualInput?.value || 0);
        if (!hpp || hpp <= 0 || !jual) { markupBox.style.display = 'none'; return; }
        const markup = ((jual - hpp) / hpp) * 100;
        markupBox.style.display = 'block';
        markupText.textContent = `Markup kamu: ${markup.toFixed(1)}%`;
        if (markup >= 0) {
            markupSub.textContent = `Harga jual lebih tinggi dari HPP sebesar ${rupiah(jual - hpp)} per unit.`;
        } else {
            markupSub.textContent = `Harga jual lebih rendah dari HPP sebesar ${rupiah(hpp - jual)} per unit (rugi).`;
        }
    }

    btnHitung?.addEventListener('click', function () {
        const biayaBahan = computeBiayaBahan();
        const hpp = biayaBahan + overheadPerUnit;

        hargaPokokHidden.value  = Math.round(hpp);
        hargaPokokDisplay.value = (hpp > 0) ? Math.round(hpp).toLocaleString('id-ID') : '';

        breakdownBox.style.display = 'block';
        biayaBahanDisplay.textContent = rupiah(biayaBahan);
        overheadDisplay.textContent   = rupiah(overheadPerUnit);
        hppDisplay.textContent        = rupiah(hpp);

        const period = new Date().toISOString().slice(0,7);
        hppNote.textContent = overheadPerUnit > 0
            ? `Overhead memakai anggaran periode ${period} (estimasi).`
            : `Overhead = Rp 0 (anggaran belum ada / target unit = 0).`;

        updateMarkupInfo();
    });

    const hargaJualDisplay = document.getElementById('harga_jual_display');
    if (hargaJualDisplay) {
        hargaJualDisplay.addEventListener('input', function() {
            const raw = this.value.replace(/\D/g, '');
            hargaJualInput.value = raw;
            const num = parseInt(raw) || 0;
            this.value = num > 0 ? num.toLocaleString('id-ID') : '';
            updateMarkupInfo();
        });
    }

});
</script>
@endpush
