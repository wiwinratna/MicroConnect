{{-- Jenis usaha, kuesioner, penetapan level, dan persetujuan pada formulir Admin. --}}
<div class="col-12">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3"><h5 class="card-title mb-0">Jenis usaha</h5></div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark" for="rumpun">Jenis usaha <span class="text-danger">*</span></label>
                    <select name="rumpun" id="rumpun" class="form-select" required>
                        <option value="">-- Pilih jenis usaha --</option>
                        <option value="barang" @selected(old('rumpun') === 'barang')>Barang</option>
                        <option value="jasa" @selected(old('rumpun') === 'jasa')>Jasa</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-dark" for="jenis_usaha_id">Kategori usaha <span class="text-danger">*</span></label>
                    <select name="jenis_usaha_id" id="jenis_usaha_id" class="form-select" required>
                        <option value="">-- Pilih kategori usaha --</option>
                        @foreach($jenisUsaha as $j)
                            <option value="{{ $j->id }}" data-untuk="{{ $j->rumpun_bawaan }}" @selected(old('jenis_usaha_id') == $j->id)>{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @foreach(['barang' => ['mengolah_bahan', 'Apakah usaha ini mengolah bahan menjadi produk?'], 'jasa' => ['punya_persediaan', 'Apakah usaha ini memiliki persediaan barang?']] as $jenis => [$nama, $tanya])
                    <div class="col-md-6" data-lanjutan="{{ $jenis }}" hidden>
                        <label class="form-label small fw-bold text-dark" for="{{ $nama }}">{{ $tanya }} <span class="text-danger">*</span></label>
                        <select name="{{ $nama }}" id="{{ $nama }}" class="form-select" required disabled>
                            <option value="">-- Pilih --</option>
                            <option value="1" @selected(old($nama) === '1')>Ya</option>
                            <option value="0" @selected(old($nama) === '0')>Tidak</option>
                        </select>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="col-12">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3"><h5 class="card-title mb-0">Kuesioner kesiapan usaha</h5></div>
        <div class="card-body p-4">
            @foreach($indikator as $ind)
                <fieldset class="mb-3">
                    <legend class="h6">{{ $ind->urutan }}. {{ $ind->pertanyaan }}</legend>
                    @foreach($ind->pilihan as $p)
                        <div class="form-check mb-1">
                            <input class="form-check-input jawaban" type="radio" name="jawaban[{{ $ind->id }}]" id="p{{ $p->id }}"
                                   value="{{ $p->id }}" data-skor="{{ $p->skor }}" required
                                   @checked((string) old('jawaban.' . $ind->id) === (string) $p->id)>
                            <label class="form-check-label" for="p{{ $p->id }}">
                                {{ $p->teks }}
                                @if($p->contoh)<span class="d-block text-muted small fst-italic">{{ $p->contoh }}</span>@endif
                            </label>
                        </div>
                    @endforeach
                </fieldset>
            @endforeach

            <hr>

            <div class="alert alert-light border small mb-3" id="rekomendasi" data-jumlah="{{ $indikator->count() }}">
                Jawab semua pertanyaan untuk melihat level rekomendasi.
            </div>

            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-dark" for="level_id">Level yang ditetapkan</label>
                    <select name="level_id" id="level_id" class="form-select">
                        <option value="">Ikuti rekomendasi sistem</option>
                        @foreach($levels as $lv)
                            <option value="{{ $lv->id }}" data-min="{{ $lv->skor_min }}" data-max="{{ $lv->skor_max }}"
                                    data-nama="{{ $lv->nama_level }}" @selected(old('level_id') == $lv->id)>{{ $lv->nama_level }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label small fw-bold text-dark" for="alasan">Alasan (wajib jika berbeda dari rekomendasi)</label>
                    <textarea name="alasan" id="alasan" class="form-control" rows="2" maxlength="1000">{{ old('alasan') }}</textarea>
                </div>
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="persetujuan_data_pribadi" id="persetujuan_data_pribadi"
                       value="1" required @checked(old('persetujuan_data_pribadi'))>
                <label class="form-check-label" for="persetujuan_data_pribadi">
                    Pemilik usaha menyetujui datanya diproses oleh Admin KADIN untuk pendataan dan pembinaan UMKM.
                </label>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/filter-kategori.js') }}"></script>
<script>
(function () {
    const jenis = document.getElementById('rumpun');
    const saringKategori = buatSaringKategori(document.getElementById('jenis_usaha_id'));

    // Pertanyaan lanjutan mengikuti jenis usaha; yang tersembunyi tidak ikut terkirim.
    function segarkanJenis() {
        saringKategori(jenis.value);
        document.querySelectorAll('[data-lanjutan]').forEach(function (kotak) {
            const tampil = kotak.dataset.lanjutan === jenis.value;
            kotak.hidden = !tampil;
            kotak.querySelector('select').disabled = !tampil;
        });
    }

    // Pratinjau rekomendasi; nilai resmi tetap dihitung server.
    const info = document.getElementById('rekomendasi');
    function segarkanSkor() {
        const dipilih = document.querySelectorAll('.jawaban:checked');
        if (dipilih.length < Number(info.dataset.jumlah)) { return; }
        const total = Array.from(dipilih).reduce(function (n, el) { return n + Number(el.dataset.skor); }, 0);
        const level = Array.from(document.querySelectorAll('#level_id option[data-min]')).find(function (o) {
            return total >= Number(o.dataset.min) && total <= Number(o.dataset.max);
        });
        info.textContent = 'Total skor ' + total + '. Rekomendasi sistem: ' + (level ? level.dataset.nama : '-') + '.';
    }

    jenis.addEventListener('change', segarkanJenis);
    document.querySelectorAll('.jawaban').forEach(function (el) { el.addEventListener('change', segarkanSkor); });
    segarkanJenis();
    segarkanSkor();
})();
</script>
@endpush
