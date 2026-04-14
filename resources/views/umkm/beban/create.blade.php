@extends('layouts.umkm')
@section('title', 'Catat Beban')

@push('styles')
<style>
    /* ── Sleek Kategori Cards ── */
    .kategori-radio-hidden { display: none; }
    
    .kategori-card-modern {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 12px;
        padding: 1.25rem 0.75rem;
        text-align: center;
        transition: all 0.2s cubic-bezier(0.165, 0.84, 0.44, 1);
        cursor: pointer;
        height: 100%;
        position: relative;
    }

    .kategori-card-modern:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
    }

    .kategori-radio-hidden:checked + .kategori-card-modern {
        border-color: #2563eb;
        background-color: #f8faff;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
    }

    .kategori-radio-hidden:checked + .kategori-card-modern .icon-box {
        background-color: #2563eb;
        color: #fff;
    }

    .kategori-radio-hidden:checked + .kategori-card-modern .check-mark {
        display: flex;
    }

    .icon-box {
        width: 48px;
        height: 48px;
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        color: #64748b;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        transition: all 0.2s;
    }

    .card-title-cat { font-size: 0.8125rem; font-weight: 700; color: #334155; margin-bottom: 2px; }
    .card-subtitle-cat { font-size: 0.65rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }

    .check-mark {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 18px;
        height: 18px;
        background: #2563eb;
        color: #fff;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }

    /* ── Form Styling ── */
    .form-group-modern { margin-bottom: 1.5rem; }
    .form-label-premium { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: #64748b; margin-bottom: 0.5rem; display: block;}
    .form-control-premium { border-radius: 10px; padding: 0.625rem 1rem; border: 1px solid #e2e8f0; font-size: 0.9rem; transition: border-color 0.2s; }
    .form-control-premium:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); outline: none; }
</style>
@endpush

@section('content')
<div class="row align-items-center mb-4 g-3">
    <div class="col-6">
        <a href="{{ route('umkm.beban.index') }}" class="text-decoration-none text-muted small fw-semibold d-flex align-items-center gap-1">
            <i data-feather="chevron-left" style="width:14px; height:14px;"></i> Kembali ke Riwayat
        </a>
        <h1 class="h3 mb-1 mt-1"><strong>Catat Beban</strong> Usaha</h1>
    </div>
    <div class="col-6 text-end">
        <div class="text-muted small">Beban akan otomatis memotong Kas UMKM.</div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm" style="border-radius:12px;">
        <ul class="mb-0 small">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
@endif

<form action="{{ route('umkm.beban.store') }}" method="POST">
    @csrf

    <div class="row g-4 mb-4">
        {{-- Form Fields --}}
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm" style="border-radius:16px;">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label-premium">Tanggal Transaksi <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control form-control-premium"
                                   value="{{ old('tanggal', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-premium">Nominal Biaya (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 x-small fw-bold" style="border-radius:10px 0 0 10px; font-size:0.75rem;">RP</span>
                                <input type="text" id="nominal_display" class="form-control form-control-premium border-start-0 ps-1 fw-bold text-dark"
                                       placeholder="0" value="{{ old('nominal') }}" required inputmode="numeric" style="border-radius:0 10px 10px 0;">
                            </div>
                            <input type="hidden" name="nominal" id="nominal_hidden" value="{{ old('nominal') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-premium">Keterangan / Memo <span class="text-danger">*</span></label>
                            <input type="text" name="keterangan" class="form-control form-control-premium"
                                   placeholder="Misal: Pembayaran tagihan listrik bulan Maret..."
                                   value="{{ old('keterangan') }}" required maxlength="200">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kategori Selection --}}
        <div class="col-lg-12">
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <label class="form-label-premium mb-0">Pilih Kategori Beban <span class="text-danger">*</span></label>
                <div class="badge bg-light text-muted border px-2 py-1 x-small" id="selected-hint" style="font-size: 0.65rem;">BELUM DIPILIH</div>
            </div>
            
            <div class="row g-3">
                @foreach($kategori as $k)
                <div class="col-6 col-md-3 col-lg-2">
                    <label class="w-100 mb-0 h-100">
                        <input type="radio" name="kode_beban" value="{{ $k['kode'] }}"
                               class="kategori-radio-hidden"
                               {{ old('kode_beban') === $k['kode'] ? 'checked' : '' }} required>
                        <div class="kategori-card-modern">
                            <div class="check-mark"><i data-feather="check"></i></div>
                            <div class="icon-box">
                                <i data-feather="{{ $k['icon'] }}" style="width: 20px; height: 20px;"></i>
                            </div>
                            <div class="card-title-cat">{{ $k['nama'] }}</div>
                            <div class="card-subtitle-cat">AKUN {{ $k['kode'] }}</div>
                        </div>
                    </label>
                </div>
                @endforeach
            </div>
            @error('kode_beban') <div class="text-danger x-small mt-2">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Info Journal Integration --}}
    <div class="card border-0 shadow-sm mb-4" id="info-jurnal-box" style="border-radius:12px; display:none; background: #fdfbfc; border: 1px dashed #e2e8f0 !important;">
        <div class="card-body p-3 d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 p-2 rounded-3 text-primary">
                <i data-feather="book-open" style="width:20px; height:20px;"></i>
            </div>
            <div class="small">
                <span class="text-muted fw-semibold">Integrasi Akuntansi:</span>
                <span class="ms-1 fw-bold text-dark">Dr. <span id="info-beban-label">Beban</span> / Cr. Kas (111)</span>
                <div class="text-muted x-small">Jurnal umum akan dibuat secara otomatis saat Anda menyimpan.</div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Pastikan bukti bayar sudah sesuai dengan nominal yang diinput.
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('umkm.beban.index') }}" class="btn btn-white border px-4" style="border-radius:10px;">Batal</a>
            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm" style="border-radius:10px;">
                <i data-feather="save" style="width:16px; height:16px; margin-right:6px;"></i> Simpan Catatan Beban
            </button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if(typeof feather !== 'undefined') feather.replace();

    const radios    = document.querySelectorAll('.kategori-radio-hidden');
    const hintEl    = document.getElementById('selected-hint');
    const infoBox   = document.getElementById('info-jurnal-box');
    const bebanLbl  = document.getElementById('info-beban-label');

    const kMap = @json(array_column($kategori, 'nama', 'kode'));

    function updateRef(val) {
        const name = kMap[val] || val;
        hintEl.textContent = 'TERPILIH: ' + name.toUpperCase();
        hintEl.classList.remove('bg-light','text-muted');
        hintEl.classList.add('bg-primary','text-white','border-0');
        
        infoBox.style.display = 'block';
        bebanLbl.textContent = name;
    }

    radios.forEach(r => {
        if (r.checked) updateRef(r.value);
        r.addEventListener('change', () => updateRef(r.value));
    });

    // Formatting nominal
    const nDisp = document.getElementById('nominal_display');
    const nHid  = document.getElementById('nominal_hidden');

    nDisp.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        nHid.value = raw;
        const num = parseInt(raw) || 0;
        this.value = num > 0 ? num.toLocaleString('id-ID') : '';
    });
    
    if (nDisp.value) nDisp.dispatchEvent(new Event('input'));
});
</script>
@endpush
