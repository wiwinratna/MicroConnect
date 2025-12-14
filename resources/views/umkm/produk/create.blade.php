@extends('layouts.umkm')

@section('title', 'Tambah Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><strong>Tambah</strong> Produk</h1>
            <p class="text-muted mb-0">
                Daftarkan produk jadi yang akan dijual, beserta komposisi bahan per 1 unit + estimasi HPP dari anggaran bulanan.
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

                    {{-- HARGA POKOK (readonly, hasil hitung) --}}
                    <div class="col-md-4">
                        <label class="form-label">Harga Pokok / Unit (Rp)</label>
                        <input type="text"
                               id="harga_pokok_display"
                               class="form-control"
                               value="{{ old('harga_pokok') ? number_format(old('harga_pokok'), 0, ',', '.') : '' }}"
                               readonly
                               placeholder="Klik Hitung HPP dulu">
                        <input type="hidden" name="harga_pokok" id="harga_pokok" value="{{ old('harga_pokok') }}">
                        <small class="text-muted">
                            HPP = biaya bahan (dari resep) + overhead/unit (dari anggaran bulanan).
                        </small>
                    </div>

                    {{-- HARGA JUAL --}}
                    <div class="col-md-4">
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="number"
                               name="harga_jual"
                               id="harga_jual"
                               class="form-control"
                               min="0"
                               step="1"
                               value="{{ old('harga_jual') }}"
                               placeholder="Contoh: 15000">
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
                        <h5 class="mb-1">Komposisi per 1 unit produk</h5>
                        <p class="text-muted small mb-0">
                            Tambahkan bahan baku untuk membuat <strong>1 unit</strong> produk ini.
                            Ketik nama bahan & satuan, nanti muncul daftar saran.
                        </p>
                    </div>

                    {{-- tombol hitung HPP --}}
                    <button type="button" class="btn btn-outline-primary" id="btn-hitung-hpp">
                        Hitung HPP
                    </button>
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

                        <div class="col-md-2 position-relative">
                            <label class="form-label small mb-1">Satuan</label>
                            <input type="text"
                                   name="satuan[]"
                                   class="form-control satuan-input"
                                   placeholder="ml/gram/pcs"
                                   autocomplete="off">

                            <div class="autocomplete-panel list-group shadow-sm unit-panel"
                                 style="position:absolute; z-index:30; top:100%; left:0; right:0;
                                        max-height:200px; overflow:auto; display:none;">
                            </div>
                        </div>

                        {{-- NEW: harga per unit --}}
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Harga / unit</label>
                            <input type="text" class="form-control harga-display" readonly placeholder="Rp 0">
                            <small class="text-muted small harga-satuan-note"></small>
                        </div>

                        {{-- NEW: biaya baris --}}
                        <div class="col-md-1">
                            <label class="form-label small mb-1">Biaya</label>
                            <input type="text" class="form-control biaya-display" readonly placeholder="Rp 0">
                        </div>

                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button"
                                    class="btn btn-danger w-100 remove-row"
                                    title="Hapus baris">
                                ×
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-primary mt-2" id="add-row">
                    + Tambah Baris Bahan
                </button>
                <small class="text-muted d-block mt-1">
                    Baris kosong (tanpa bahan atau qty) akan diabaikan saat simpan.
                </small>

                {{-- ===================== SUBMIT ===================== --}}
                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @php
    $bahanMaster = isset($bahanBaku)
    ? $bahanBaku->map(fn($b) => [
        'id'     => $b->id,
        'nama'   => $b->nama_bahan,
        'satuan' => $b->satuan,
        'harga'  => (float) ($b->harga_last ?? 0), // <-- ini kuncinya!
    ])
    : collect();
    @endphp


<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper   = document.getElementById('komposisi-wrapper');
    const addRowBtn = document.getElementById('add-row');

    const btnHitung = document.getElementById('btn-hitung-hpp');
    const hargaPokokHidden  = document.getElementById('harga_pokok');
    const hargaPokokDisplay = document.getElementById('harga_pokok_display');
    const hargaJualInput    = document.getElementById('harga_jual');

    const breakdownBox = document.getElementById('hpp-breakdown');
    const biayaBahanDisplay = document.getElementById('biaya_bahan_display');
    const overheadDisplay   = document.getElementById('overhead_display');
    const hppDisplay        = document.getElementById('hpp_display');
    const hppNote           = document.getElementById('hpp_note');

    const markupBox = document.getElementById('markup-box');
    const markupText = document.getElementById('markup_text');
    const markupSub  = document.getElementById('markup_subtext');

    const bahanMaster = @json($bahanMaster);
    const overheadPerUnit = Number(@json($overheadPerUnit));

    const unitMaster = [
        'ml', 'milliliter',
        'liter', 'l',
        'gram', 'g',
        'kg', 'kilogram',
        'pcs', 'buah', 'lembar',
        'lusin', 'rim'
    ];

    function rupiah(n) {
        n = Number(n || 0);
        return 'Rp ' + n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    // ====== KONVERSI (kg<->g, l<->ml) ======
    function convertUnit(qty, from, to) {
        from = (from || '').toLowerCase().trim();
        to   = (to || '').toLowerCase().trim();
        if (!from || !to || from === to) return qty;

        const mass = { g:1, gram:1, kg:1000, kilogram:1000 };
        if (mass[from] && mass[to]) return qty * (mass[from] / mass[to]);

        const vol = { ml:1, milliliter:1, l:1000, liter:1000 };
        if (vol[from] && vol[to]) return qty * (vol[from] / vol[to]);

        // pcs <-> gram tidak bisa otomatis tanpa rule (berat per pcs)
        return qty;
    }

    // ====== REALTIME: update harga/unit & biaya per baris ======
    function updateRowCost(row) {
        const bahanId = row.querySelector('input[name="bahan_id[]"]')?.value;
        const qtyVal  = row.querySelector('input[name="qty[]"]')?.value;
        const unitInp = row.querySelector('input[name="satuan[]"]')?.value;

        const hargaEl = row.querySelector('.harga-display');
        const biayaEl = row.querySelector('.biaya-display');
        const noteEl  = row.querySelector('.harga-satuan-note');

        if (hargaEl) hargaEl.value = 'Rp 0';
        if (biayaEl) biayaEl.value = 'Rp 0';
        if (noteEl)  noteEl.textContent = '';

        if (!bahanId) return;

        const bahan = bahanMaster.find(b => String(b.id) === String(bahanId));
        if (!bahan) return;

        const hargaPerBase = Number(bahan.harga || 0);
        if (hargaEl) hargaEl.value = rupiah(hargaPerBase);
        if (noteEl) noteEl.textContent = `per ${bahan.satuan || ''}`;

        const qty = Number(qtyVal || 0);
        if (!qty) return;

        const qtyBase = convertUnit(qty, unitInp, bahan.satuan);
        if (biayaEl) biayaEl.value = rupiah(qtyBase * hargaPerBase);
    }

    // ====== FIX: kalau user cuma ngetik nama tanpa klik dropdown ======
    function syncBahanIdByName(row) {
        const inputNama = row.querySelector('.bahan-input');
        const inputId   = row.querySelector('input[name="bahan_id[]"]');
        const inputSat  = row.querySelector('input[name="satuan[]"]');

        const name = (inputNama?.value || '').trim().toLowerCase();
        if (!name) return;

        const bahan = bahanMaster.find(b => (b.nama || '').trim().toLowerCase() === name);
        if (bahan) {
            inputId.value = bahan.id;
            if (inputSat && !inputSat.value && bahan.satuan) {
                inputSat.value = bahan.satuan;
            }
            updateRowCost(row);
        }
    }

    // ==== AUTOCOMPLETE BAHAN ====
    function setupBahanAutocomplete(row) {
        const inputNama = row.querySelector('.bahan-input');
        const panel     = row.querySelector('.bahan-panel');
        const inputId   = row.querySelector('input[name="bahan_id[]"]');
        const inputSat  = row.querySelector('input[name="satuan[]"]');

        if (!inputNama || !panel) return;

        inputNama.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            panel.innerHTML = '';
            inputId.value = '';

            // reset realtime display
            updateRowCost(row);

            if (!q) {
                panel.style.display = 'none';
                return;
            }

            const filtered = bahanMaster.filter(b => (b.nama || '').toLowerCase().includes(q));
            if (!filtered.length) {
                panel.style.display = 'none';
                return;
            }

            filtered.slice(0, 10).forEach(b => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span>${b.nama}</span>
                        <span class="text-muted small">${b.satuan ?? ''}</span>
                    </div>
                `;
                btn.addEventListener('click', () => {
                    inputNama.value = b.nama;
                    inputId.value   = b.id;

                    if (inputSat && !inputSat.value && b.satuan) {
                        inputSat.value = b.satuan;
                    }
                    panel.style.display = 'none';

                    // NEW: update biaya per baris
                    updateRowCost(row);
                });
                panel.appendChild(btn);
            });

            panel.style.display = 'block';
        });

        inputNama.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                syncBahanIdByName(row);
                panel.style.display = 'none';
            }
        });

        inputNama.addEventListener('blur', function () {
            setTimeout(() => {
                panel.style.display = 'none';
                syncBahanIdByName(row);
            }, 200);
        });
    }

    // ==== AUTOCOMPLETE SATUAN ====
    function setupUnitAutocomplete(row) {
        const inputUnit = row.querySelector('.satuan-input');
        const panel     = row.querySelector('.unit-panel');

        if (!inputUnit || !panel) return;

        inputUnit.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            panel.innerHTML = '';

            // NEW: update biaya per baris saat unit berubah
            updateRowCost(row);

            if (!q) {
                panel.style.display = 'none';
                return;
            }

            const filtered = unitMaster.filter(u => u.toLowerCase().includes(q));
            if (!filtered.length) {
                panel.style.display = 'none';
                return;
            }

            filtered.slice(0, 10).forEach(u => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.textContent = u;
                btn.addEventListener('click', () => {
                    inputUnit.value = u;
                    panel.style.display = 'none';

                    // NEW: update biaya per baris
                    updateRowCost(row);
                });
                panel.appendChild(btn);
            });

            panel.style.display = 'block';
        });

        inputUnit.addEventListener('blur', function () {
            setTimeout(() => {
                const val = (inputUnit.value || '').toLowerCase().trim();
                if (!val) {
                    panel.style.display = 'none';
                    updateRowCost(row);
                    return;
                }
                const valid = unitMaster.some(u => u.toLowerCase() === val);
                if (!valid) inputUnit.value = '';
                panel.style.display = 'none';

                updateRowCost(row);
            }, 200);
        });
    }

    function setupRow(row) {
        setupBahanAutocomplete(row);
        setupUnitAutocomplete(row);

        // NEW: realtime saat qty/satuan berubah
        row.querySelector('.qty-input')?.addEventListener('input', () => updateRowCost(row));
        row.querySelector('.satuan-input')?.addEventListener('input', () => updateRowCost(row));
    }

    function createKomposisiRow() {
        const row = document.createElement('div');
        row.classList.add('row', 'g-2', 'komposisi-row', 'mb-2');

        row.innerHTML = `
            <div class="col-md-4 position-relative">
                <input type="hidden" name="bahan_id[]">
                <label class="form-label small mb-1">Bahan Baku</label>
                <input type="text" name="bahan_nama[]" class="form-control bahan-input"
                       placeholder="Ketik nama bahan..." autocomplete="off">
                <div class="autocomplete-panel list-group shadow-sm bahan-panel"
                     style="position:absolute; z-index:30; top:100%; left:0; right:0;
                            max-height:220px; overflow:auto; display:none;"></div>
            </div>

            <div class="col-md-2">
                <label class="form-label small mb-1">Qty per 1 unit</label>
                <input type="number" step="0.001" name="qty[]" class="form-control qty-input" placeholder="Qty">
            </div>

            <div class="col-md-2 position-relative">
                <label class="form-label small mb-1">Satuan</label>
                <input type="text" name="satuan[]" class="form-control satuan-input"
                       placeholder="ml/gram/pcs" autocomplete="off">
                <div class="autocomplete-panel list-group shadow-sm unit-panel"
                     style="position:absolute; z-index:30; top:100%; left:0; right:0;
                            max-height:200px; overflow:auto; display:none;"></div>
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
                <button type="button" class="btn btn-danger w-100 remove-row" title="Hapus baris">×</button>
            </div>
        `;

        setupRow(row);
        return row;
    }

    // setup row pertama
    setupRow(wrapper.querySelector('.komposisi-row'));

    // tambah row
    addRowBtn?.addEventListener('click', () => wrapper.appendChild(createKomposisiRow()));

    // hapus row
    wrapper.addEventListener('click', function (e) {
        if (!e.target.classList.contains('remove-row')) return;
        const rows = wrapper.querySelectorAll('.komposisi-row');
        if (rows.length === 1) {
            rows[0].querySelectorAll('input').forEach(i => i.value = '');
            // reset display
            updateRowCost(rows[0]);
            return;
        }
        e.target.closest('.komposisi-row').remove();
    });

    // ==== HITUNG HPP ====
    function computeBiayaBahan() {
        let total = 0;

        wrapper.querySelectorAll('.komposisi-row').forEach(row => {
            // pastikan id kebaca walau user hanya mengetik
            syncBahanIdByName(row);

            const bahanId = row.querySelector('input[name="bahan_id[]"]')?.value;
            const qtyVal  = row.querySelector('input[name="qty[]"]')?.value;
            const unitInp = row.querySelector('input[name="satuan[]"]')?.value;

            const bahan = bahanMaster.find(b => String(b.id) === String(bahanId));
            if (!bahanId || !bahan) return;

            const harga = Number(bahan.harga || 0);
            const qty = Number(qtyVal || 0);
            if (!qty) return;

            const qtyBase = convertUnit(qty, unitInp, bahan.satuan);
            total += (qtyBase * harga);

            // update tampilan row juga
            updateRowCost(row);
        });

        return total;
    }

    function updateMarkupInfo() {
        const hpp = Number(hargaPokokHidden.value || 0);
        const jual = Number(hargaJualInput.value || 0);

        if (!hpp || hpp <= 0 || !jual) {
            markupBox.style.display = 'none';
            return;
        }

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

        hargaPokokHidden.value = Math.round(hpp);
        hargaPokokDisplay.value = (hpp > 0) ? Math.round(hpp).toLocaleString('id-ID') : '';

        breakdownBox.style.display = 'block';
        biayaBahanDisplay.textContent = rupiah(biayaBahan);
        overheadDisplay.textContent = rupiah(overheadPerUnit);
        hppDisplay.textContent = rupiah(hpp);

        const period = new Date().toISOString().slice(0,7);
        hppNote.textContent = overheadPerUnit > 0
            ? `Overhead memakai anggaran periode ${period} (estimasi).`
            : `Overhead = Rp 0 (anggaran belum ada / target unit = 0).`;

        updateMarkupInfo();
    });

    hargaJualInput?.addEventListener('input', updateMarkupInfo);
});
</script>
@endpush
