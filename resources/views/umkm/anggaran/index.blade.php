@extends('layouts.umkm')

@section('title', 'Anggaran Estimasi Overhead')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-1"><strong>Anggaran Estimasi</strong> Overhead</h1>
        <p class="text-muted mb-0">Estimasi bulanan biaya operasional untuk membantu simulasi harga jual produk. <br><span class="text-danger fw-semibold">Penting:</span> Input di sini <strong>TIDAK</strong> menjurnal pengeluaran/beban aktual di Laporan Keuangan.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('umkm.anggaran.index') }}" class="row g-2 mb-3">
            <div class="col-md-3">
                <label class="form-label small">Periode</label>
                    <input type="month" class="form-control" name="periode"
                        value="{{ \Carbon\Carbon::parse($periode)->format('Y-m') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100">Tampilkan</button>
            </div>
        </form>

        <form method="POST" action="{{ route('umkm.anggaran.store') }}">
            @csrf
            <input type="hidden" name="periode" value="{{ \Carbon\Carbon::parse($periode)->format('Y-m') }}">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Target Produksi Bulan Ini (unit)</label>
                    <input type="number" step="0.01" min="0" name="target_unit" class="form-control"
                           value="{{ old('target_unit', $anggaran->target_unit ?? 0) }}">
                    <small class="text-muted">Dipakai untuk alokasi overhead per unit.</small>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Catatan (opsional)</label>
                    <input type="text" name="catatan" class="form-control"
                           value="{{ old('catatan', $anggaran->catatan ?? '') }}"
                           placeholder="Contoh: estimasi kasar dulu, nanti update akhir bulan.">
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Detail Biaya</h5>
                <button type="button" class="btn btn-outline-primary btn-sm" id="add-item">+ Tambah Baris</button>
            </div>

            <div id="items-wrapper">
                @php
                    $items = old('nama_biaya')
                        ? collect(old('nama_biaya'))->map(fn($n, $i) => ['nama'=>$n,'nominal'=>old('nominal')[$i] ?? 0])
                        : collect($anggaran->items ?? []);
                @endphp

                @forelse($items as $it)
                    <div class="row g-2 item-row mb-2">
                        <div class="col-md-6">
                            <input type="text" name="nama_biaya[]" class="form-control"
                                   value="{{ is_array($it) ? $it['nama'] : $it->nama_biaya }}"
                                   placeholder="Contoh: Gas / Listrik / Gaji / Promosi">
                        </div>
                        <div class="col-md-5">
                            <input type="number" min="0" step="1" name="nominal[]" class="form-control nominal"
                                   value="{{ is_array($it) ? $it['nominal'] : $it->nominal }}"
                                   placeholder="Nominal (Rp)">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger w-100 remove-item">×</button>
                        </div>
                    </div>
                @empty
                    <div class="row g-2 item-row mb-2">
                        <div class="col-md-6">
                            <input type="text" name="nama_biaya[]" class="form-control" placeholder="Contoh: Gas">
                        </div>
                        <div class="col-md-5">
                            <input type="number" min="0" step="1" name="nominal[]" class="form-control nominal" placeholder="Nominal (Rp)">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger w-100 remove-item">×</button>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div class="fw-semibold">
                    Total Estimasi: <span id="totalText">Rp {{ number_format($anggaran->total ?? 0, 0, ',', '.') }}</span>
                </div>
                <button class="btn btn-primary btn-lg">Simpan Anggaran</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('items-wrapper');
    const addBtn  = document.getElementById('add-item');
    const totalText = document.getElementById('totalText');

    function recalcTotal() {
        let total = 0;
        wrapper.querySelectorAll('.nominal').forEach(inp => {
            const v = parseFloat(inp.value || '0');
            if (!isNaN(v)) total += v;
        });
        totalText.textContent = new Intl.NumberFormat('id-ID').format(total).replaceAll('.', '.');
        totalText.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }

    function rowTemplate() {
        const div = document.createElement('div');
        div.className = 'row g-2 item-row mb-2';
        div.innerHTML = `
            <div class="col-md-6">
                <input type="text" name="nama_biaya[]" class="form-control" placeholder="Contoh: Listrik">
            </div>
            <div class="col-md-5">
                <input type="number" min="0" step="1" name="nominal[]" class="form-control nominal" placeholder="Nominal (Rp)">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100 remove-item">×</button>
            </div>
        `;
        return div;
    }

    addBtn?.addEventListener('click', () => {
        wrapper.appendChild(rowTemplate());
    });

    wrapper.addEventListener('click', (e) => {
        if (!e.target.classList.contains('remove-item')) return;
        const rows = wrapper.querySelectorAll('.item-row');
        if (rows.length === 1) {
            rows[0].querySelectorAll('input').forEach(i => i.value = '');
        } else {
            e.target.closest('.item-row').remove();
        }
        recalcTotal();
    });

    wrapper.addEventListener('input', (e) => {
        if (e.target.classList.contains('nominal')) recalcTotal();
    });

    recalcTotal();
});
</script>
@endpush
