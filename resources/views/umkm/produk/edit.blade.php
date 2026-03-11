@extends('layouts.umkm')

@section('title', 'Edit Produk')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><strong>Edit</strong> Produk</h1>
            <p class="text-muted mb-0">Perbarui informasi dan komposisi resep produk kamu.</p>
        </div>
        <a href="{{ route('umkm.produk.index') }}" class="btn btn-outline-secondary">&larr; Kembali</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
            </ul>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('umkm.produk.update', $produk->id) }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- ===== DATA DASAR ===== --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Kode Produk</label>
                        <input type="text" class="form-control" value="{{ $produk->kode_produk }}" readonly>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control"
                               value="{{ old('nama_produk', $produk->nama_produk) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Satuan Produk</label>
                        <input type="text" name="satuan" class="form-control"
                               value="{{ old('satuan', $produk->satuan) }}"
                               placeholder="pcs / porsi / box" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Harga Jual (Rp)</label>
                        <input type="number" name="harga_jual" class="form-control" id="input_harga_jual"
                               min="0" step="1"
                               value="{{ old('harga_jual', $produk->harga_jual) }}">
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted">HPP Estimasi saat ini: <span id="text_hpp_now" class="fw-bold">Rp {{ number_format($produk->harga_pokok ?? 0, 0, ',', '.') }}</span></small>
                            <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalHpp">
                                Simulasi Harga Jual (Estimasi)
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Foto Produk (opsional)</label>
                        <input type="file" name="foto" class="form-control">
                        @if($produk->foto_path)
                            <img src="{{ asset('storage/'.$produk->foto_path) }}"
                                 class="img-thumbnail mt-1" style="max-height:100px;">
                        @endif
                    </div>
                    <div class="col-12">
                        <label class="form-label">Keterangan (opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $produk->keterangan) }}</textarea>
                    </div>
                </div>

                {{-- ===== KOMPOSISI / RESEP ===== --}}
                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="mb-0">Komposisi / Resep per 1 unit produk</h5>
                        <p class="text-muted small mb-0">
                            <span class="text-danger fw-semibold">Wajib diisi</span> agar HPP dan stok bahan terhitung otomatis saat penjualan.
                        </p>
                    </div>
                </div>

                @if($komposisi->isEmpty())
                    <div class="alert alert-warning d-flex gap-2 align-items-start py-2">
                        <span>⚠️</span>
                        <div class="small">Produk ini belum punya resep. Tambahkan komposisi bahan baku di bawah.</div>
                    </div>
                @endif

                <div id="komposisi-wrapper">
                    @forelse($komposisi as $k)
                    <div class="row g-2 komposisi-row mb-2">
                        <div class="col-md-5 position-relative">
                            <input type="hidden" name="bahan_id[]" value="{{ $k->bahan_id }}">
                            <label class="form-label small mb-1">Bahan Baku</label>
                            <input type="text" name="bahan_nama[]"
                                   class="form-control bahan-input"
                                   value="{{ $k->bahan->nama_bahan ?? '' }}"
                                   placeholder="Ketik nama bahan..." autocomplete="off">
                            <div class="autocomplete-panel list-group shadow-sm bahan-panel"
                                 style="position:absolute;z-index:30;top:100%;left:0;right:0;max-height:220px;overflow:auto;display:none;"></div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Qty / 1 unit</label>
                            <input type="number" step="0.001" name="qty[]"
                                   class="form-control qty-input" value="{{ $k->qty }}" placeholder="Qty">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Satuan</label>
                            <select name="satuan[]" class="form-select satuan-input">
                                <option value="">-- pilih --</option>
                                @foreach($satuanOptions as $opt)
                                    <option value="{{ $opt['value'] }}" {{ ($k->satuan === $opt['value']) ? 'selected' : '' }}>
                                        {{ $opt['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger w-100 remove-row">×</button>
                        </div>
                    </div>
                    @empty
                    <div class="row g-2 komposisi-row mb-2">
                        <div class="col-md-5 position-relative">
                            <input type="hidden" name="bahan_id[]">
                            <label class="form-label small mb-1">Bahan Baku</label>
                            <input type="text" name="bahan_nama[]" class="form-control bahan-input"
                                   placeholder="Ketik nama bahan..." autocomplete="off">
                            <div class="autocomplete-panel list-group shadow-sm bahan-panel"
                                 style="position:absolute;z-index:30;top:100%;left:0;right:0;max-height:220px;overflow:auto;display:none;"></div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Qty / 1 unit</label>
                            <input type="number" step="0.001" name="qty[]" class="form-control qty-input" placeholder="Qty">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Satuan</label>
                            <select name="satuan[]" class="form-select satuan-input">
                                <option value="">-- pilih --</option>
                                @foreach($satuanOptions as $opt)
                                    <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-danger w-100 remove-row">×</button>
                        </div>
                    </div>
                    @endforelse
                </div>

                <button type="button" class="btn btn-outline-primary mt-2" id="add-row">+ Tambah Baris Bahan</button>
                <small class="text-muted d-block mt-1">Baris kosong (tanpa bahan atau qty) akan diabaikan saat simpan.</small>

                <div class="mt-4 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-lg">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

{{-- MODAL SIMULASI HARGA JUAL --}}
<div class="modal fade" id="modalHpp" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light border-0">
        <h5 class="modal-title fw-bold">Kalkulator Simulasi Harga Jual</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-warning py-2 small d-flex align-items-center gap-2 mb-3">
            <div>
                <strong>Penting:</strong> Simulasi HPP ini murni untuk membantu Anda menentukan harga jual ideal. Biaya overhead dihitung proporsional dari <em>Anggaran Estimasi</em>, dan <strong>TIDAK</strong> akan dicatat sebagai beban pengeluaran aktual di laporan keuangan.<br><br>
                Saat penjualan riil terjadi, <strong>Jurnal Jual & HPP Otomatis</strong> akan menggunakan perhitungan Stok Aktual (metode FIFO/LIFO/Average dari stok fisik bahan baku), bukan menggunakan angka estimasi ini.
            </div>
        </div>
        <div class="alert alert-info py-2 small d-flex align-items-center gap-2">
            ℹ️ Biaya Bahan Baku ditarik dari harga beli terakhir komposisi resep saat ini. (Jika baru mengubah resep, silakan "Simpan Perubahan" terlebih dulu).
        </div>

        <form id="formHpp">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">Target Margin (%)</label>
                    <input type="number" id="input_margin" class="form-control" value="30" min="0" step="1">
                    <div class="form-text">Berapa % keuntungan yang diharapkan?</div>
                </div>
                <div class="col-md-7">
                    <button type="button" id="btnHitungHpp" class="btn btn-primary w-100">
                        Kalkulasi Simulasi & Saran Harga
                    </button>
                </div>
            </div>
        </form>

        <div id="hasilHpp" style="display:none;">
            <hr>
            <h6 class="fw-bold mb-3">Hasil Perhitungan:</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body py-2">
                        <small class="text-muted d-block">Biaya Bahan Baku</small>
                        <span class="fs-5 fw-bold" id="res_biaya_bahan">Rp 0</span>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light border-0"><div class="card-body py-2">
                        <small class="text-muted d-block">Overhead Per Unit</small>
                        <span class="fs-5 fw-bold" id="res_overhead">Rp 0</span>
                    </div></div>
                </div>
            </div>
            
            <div class="card border-primary bg-primary text-white mb-3 shadow-sm">
                <div class="card-body text-center">
                    <div class="small opacity-75 text-uppercase fw-semibold tracking-wider">HPP Estimasi (Total Pokok)</div>
                    <div class="display-6 fw-bold my-1" id="res_hpp_total">Rp 0</div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-success border-2 shadow-sm h-100"><div class="card-body text-center">
                        <small class="d-block text-success fw-bold">Saran Harga Jual (Margin <span id="res_margin_target">30</span>%)</small>
                        <span class="fs-4 fw-bold text-success" id="res_saran_harga">Rp 0</span>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-success px-3" id="btnGunakanHarga">Gunakan Harga Ini</button>
                        </div>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 bg-light h-100"><div class="card-body text-center">
                        <small class="d-block text-muted">Margin Aktual (Harga Saat Ini)</small>
                        <span class="fs-4 fw-bold" id="res_margin_aktual">0%</span>
                        <div class="small text-muted mt-1">dari harga <span id="res_harga_now">Rp 0</span></div>
                    </div></div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@php
$bahanMaster = isset($bahanBaku)
    ? $bahanBaku->map(fn($b) => ['id' => $b->id, 'nama' => $b->nama_bahan, 'satuan' => $b->satuan])
    : collect();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const wrapper   = document.getElementById('komposisi-wrapper');
    const addRowBtn = document.getElementById('add-row');
    const bahanMaster   = @json($bahanMaster);
    const satuanOptions = @json($satuanOptions ?? []);

    function buildSatuanOptions(selected) {
        let html = '<option value="">-- pilih --</option>';
        satuanOptions.forEach(opt => {
            const sel = opt.value === selected ? 'selected' : '';
            html += `<option value="${opt.value}" ${sel}>${opt.label}</option>`;
        });
        return html;
    }

    function setupBahanAutocomplete(row) {
        const inputNama = row.querySelector('.bahan-input');
        const panel     = row.querySelector('.bahan-panel');
        const inputId   = row.querySelector('input[name="bahan_id[]"]');

        if (!inputNama || !panel) return;

        inputNama.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            panel.innerHTML = '';
            inputId.value = '';
            if (!q) { panel.style.display = 'none'; return; }

            const filtered = bahanMaster.filter(b => (b.nama || '').toLowerCase().includes(q));
            if (!filtered.length) { panel.style.display = 'none'; return; }

            filtered.slice(0, 10).forEach(b => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action';
                btn.innerHTML = `<div class="d-flex justify-content-between"><span>${b.nama}</span><span class="text-muted small">${b.satuan ?? ''}</span></div>`;
                btn.addEventListener('click', () => {
                    inputNama.value = b.nama;
                    inputId.value   = b.id;
                    panel.style.display = 'none';
                });
                panel.appendChild(btn);
            });
            panel.style.display = 'block';
        });

        inputNama.addEventListener('blur', () => setTimeout(() => { panel.style.display = 'none'; }, 200));
    }

    function setupRow(row) { setupBahanAutocomplete(row); }

    wrapper.querySelectorAll('.komposisi-row').forEach(row => setupRow(row));

    addRowBtn?.addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'row g-2 komposisi-row mb-2';
        div.innerHTML = `
            <div class="col-md-5 position-relative">
                <input type="hidden" name="bahan_id[]">
                <label class="form-label small mb-1">Bahan Baku</label>
                <input type="text" name="bahan_nama[]" class="form-control bahan-input" placeholder="Ketik nama bahan..." autocomplete="off">
                <div class="autocomplete-panel list-group shadow-sm bahan-panel" style="position:absolute;z-index:30;top:100%;left:0;right:0;max-height:220px;overflow:auto;display:none;"></div>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Qty / 1 unit</label>
                <input type="number" step="0.001" name="qty[]" class="form-control qty-input" placeholder="Qty">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Satuan</label>
                <select name="satuan[]" class="form-select satuan-input">${buildSatuanOptions('')}</select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger w-100 remove-row">×</button>
            </div>
        `;
        setupRow(div);
        wrapper.appendChild(div);
    });

    wrapper.addEventListener('click', function (e) {
        if (!e.target.classList.contains('remove-row')) return;
        const rows = wrapper.querySelectorAll('.komposisi-row');
        if (rows.length === 1) {
            rows[0].querySelectorAll('input,select').forEach(el => el.value = '');
            return;
        }
        e.target.closest('.komposisi-row').remove();
    });
	
    // Kalkulator HPP Estimasi
    const btnHitungHpp = document.getElementById('btnHitungHpp');
    if (btnHitungHpp) {
        btnHitungHpp.addEventListener('click', async () => {
            const margin = document.getElementById('input_margin').value || 0;
            
            btnHitungHpp.disabled = true;
            btnHitungHpp.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menghitung...';
            
            try {
                const res = await fetch(`{{ route('umkm.produk.hitungHpp', $produk->id) }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ margin: margin })
                });
                
                const data = await res.json();
                const fmt = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
                
                document.getElementById('res_biaya_bahan').innerText = fmt(data.biaya_bahan);
                document.getElementById('res_overhead').innerText = fmt(data.overhead);
                document.getElementById('res_hpp_total').innerText = fmt(data.hpp);
                document.getElementById('res_margin_target').innerText = data.margin_target;
                document.getElementById('res_saran_harga').innerText = fmt(data.saran_harga);
                document.getElementById('res_margin_aktual').innerText = data.margin_aktual !== null ? data.margin_aktual + '%' : 'N/A';
                document.getElementById('res_harga_now').innerText = fmt(data.harga_jual_now);
                
                document.getElementById('text_hpp_now').innerText = fmt(data.hpp);
                
                document.getElementById('btnGunakanHarga').onclick = () => {
                    document.getElementById('input_harga_jual').value = data.saran_harga;
                    bootstrap.Modal.getInstance(document.getElementById('modalHpp')).hide();
                };
                
                document.getElementById('hasilHpp').style.display = 'block';
            } catch(err) {
                alert('Gagal mensimulasikan harga jual. Pastikan koneksi aman.');
                console.error(err);
            } finally {
                btnHitungHpp.disabled = false;
                btnHitungHpp.innerText = 'Kalkulasi Simulasi & Saran Harga';
            }
        });
    }
});
</script>
@endpush
