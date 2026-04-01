@extends('layouts.umkm')
@section('title', 'Catat Beban Operasional')

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
                            <div class="card border-2 kategori-card h-100 text-center py-3 px-2"
                                 style="cursor:pointer; border-radius:12px; border-color:#dee2e6; transition: all .2s;">
                                <div style="font-size:2rem;">{{ $k['icon'] }}</div>
                                <div class="fw-semibold small mt-1">{{ $k['nama'] }}</div>
                                <div class="text-muted" style="font-size:0.7rem;">Akun {{ $k['kode'] }}</div>
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
            c.style.borderColor = '#dee2e6';
            c.style.background  = '#fff';
        });
        const selectedCard = radio.closest('label').querySelector('.kategori-card');
        selectedCard.style.borderColor = '#0d6efd';
        selectedCard.style.background  = '#e9f2ff';

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
