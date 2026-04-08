@extends('layouts.umkm')
@section('title', 'Catat Beban Operasional')

@push('styles')
<style>
    .kategori-card:hover {
        border-color: var(--mn-primary) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1) !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><strong>Catat Beban</strong> Operasional</h1>
        <p class="text-muted mb-0">Beban akan otomatis membentuk jurnal akuntansi (Dr. Beban / Cr. Kas)</p>
    </div>
    <a href="{{ route('umkm.beban.index') }}" class="btn btn-outline-secondary">&larr; Kembali</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
    </div>
@endif

<div class="card shadow-sm border-0" style="border-radius:16px;">
    <div class="card-body p-4">
        <form action="{{ route('umkm.beban.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                {{-- Tanggal --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal" class="form-control"
                           value="{{ old('tanggal', date('Y-m-d')) }}" required>
                </div>

                {{-- Nominal --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nominal (Rp) <span class="text-danger">*</span></label>
                    <input type="text" id="nominal_display" class="form-control"
                           placeholder="150.000"
                           value="{{ old('nominal') }}" required inputmode="numeric">
                    <input type="hidden" name="nominal" id="nominal_hidden" value="{{ old('nominal') }}">
                </div>

                {{-- Keterangan --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Keterangan <span class="text-danger">*</span></label>
                    <input type="text" name="keterangan" class="form-control"
                           placeholder="cth: Tagihan listrik Maret 2026"
                           value="{{ old('keterangan') }}" required maxlength="200">
                </div>
            </div>

            {{-- Pilih Kategori Beban --}}
            <div class="mt-4">
                <label class="form-label fw-semibold d-block mb-2">Kategori Beban <span class="text-danger">*</span></label>
                <div class="row g-2">
                    @foreach($kategori as $k)
                    <div class="col-6 col-md-3">
                        <label class="d-block h-100">
                            <input type="radio" name="kode_beban"
                                   value="{{ $k['kode'] }}"
                                   class="d-none kategori-radio"
                                   {{ old('kode_beban') === $k['kode'] ? 'checked' : '' }}>
                            <div class="card kategori-card h-100 text-center py-3 px-2"
                                 style="cursor:pointer; border: 2px solid var(--mn-border-color); transition: var(--mn-transition);">
                                <div class="mb-2 text-primary" style="opacity: 0.85;">
                                    <i data-feather="{{ $k['icon'] }}" style="width: 28px; height: 28px;"></i>
                                </div>
                                <div class="fw-semibold small mt-1">{{ $k['nama'] }}</div>
                                <div class="text-subtext mt-1">Akun {{ $k['kode'] }}</div>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
                @error('kode_beban')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Info Jurnal yang akan terbentuk --}}
            <div class="alert alert-info mt-4 py-2 small" id="info-jurnal" style="display:none;">
                <strong>📒 Jurnal yang akan terbentuk:</strong><br>
                Dr. <span id="info-nama-beban">—</span> &nbsp;|&nbsp; Cr. Kas
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('umkm.beban.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary btn-lg px-4">
                    💾 Simpan & Buat Jurnal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios    = document.querySelectorAll('.kategori-radio');
    const cards     = document.querySelectorAll('.kategori-card');
    const infoEl    = document.getElementById('info-jurnal');
    const namaEl    = document.getElementById('info-nama-beban');

    const kategoriMap = @json(array_column($kategori, 'nama', 'kode'));

    function update(radio) {
        // Style kartu
        cards.forEach(c => {
            c.style.borderColor = 'var(--mn-border-color)';
            c.style.background  = 'var(--mn-surface)';
        });
        const selectedCard = radio.closest('label').querySelector('.kategori-card');
        selectedCard.style.borderColor = 'var(--mn-primary)';
        selectedCard.style.background  = 'var(--mn-primary-soft)';

        // Info jurnal
        namaEl.textContent = kategoriMap[radio.value] ?? radio.value;
        infoEl.style.display = 'block';
    }

    radios.forEach(r => {
        if (r.checked) update(r);
        r.addEventListener('change', () => update(r));
    });

    const nominalDisplay = document.getElementById('nominal_display');
    const nominalHidden = document.getElementById('nominal_hidden');

    if (nominalDisplay) {
        nominalDisplay.addEventListener('input', function() {
            const raw = this.value.replace(/\D/g, '');
            nominalHidden.value = raw;
            const num = parseInt(raw) || 0;
            this.value = num > 0 ? num.toLocaleString('id-ID') : '';
        });
        
        // Trigger formatting pada nilai old(nominal) kalau ada
        if (nominalDisplay.value) {
            nominalDisplay.dispatchEvent(new Event('input'));
        }
    }
});
</script>
@endpush
