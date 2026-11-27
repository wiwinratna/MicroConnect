{{-- Satu baris pilihan jawaban. $i: indeks baris, $p: isian, $sistem: indikator omzet/modal. --}}
<div class="row g-2 align-items-start border-bottom pb-3 mb-3 baris-pilihan">
  <input type="hidden" name="pilihan[{{ $i }}][id]" value="{{ $p['id'] ?? '' }}">
  <div class="col-md-8">
    <label class="form-label">Teks pilihan</label>
    <input type="text" name="pilihan[{{ $i }}][teks]" class="form-control" maxlength="255" required value="{{ $p['teks'] ?? '' }}">
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label">Skor</label>
    <input type="number" name="pilihan[{{ $i }}][skor]" class="form-control" min="0" max="25" required value="{{ $p['skor'] ?? 1 }}">
  </div>
  <div class="col-6 col-md-2 d-grid">
    <span class="form-label d-none d-md-block">&nbsp;</span>
    <button type="button" class="btn btn-outline-secondary hapus-pilihan">Buang</button>
  </div>
  <div class="col-md-8">
    <input type="text" name="pilihan[{{ $i }}][contoh]" class="form-control" maxlength="1000"
           value="{{ $p['contoh'] ?? '' }}" placeholder="Contoh untuk membantu UMKM (boleh dikosongkan)" aria-label="Contoh">
  </div>
  <div class="col-md-4">
    <label class="form-check mt-2">
      <input type="checkbox" class="form-check-input" name="pilihan[{{ $i }}][di_luar_batas_mikro]" value="1" @checked(!empty($p['di_luar_batas_mikro']))>
      <span class="form-check-label">Di luar batas usaha mikro</span>
    </label>
  </div>
  @if($sistem)
    <div class="col-md-4">
      <label class="form-label">Rupiah, lebih dari</label>
      <input type="number" name="pilihan[{{ $i }}][nilai_min]" class="form-control" min="0" step="1" value="{{ $p['nilai_min'] ?? '' }}">
    </div>
    <div class="col-md-4">
      <label class="form-label">Rupiah, sampai dengan</label>
      <input type="number" name="pilihan[{{ $i }}][nilai_max]" class="form-control" min="0" step="1" value="{{ $p['nilai_max'] ?? '' }}">
    </div>
    <div class="col-12"><div class="form-text">Angka ini dipakai untuk memilih jawaban otomatis dari pembukuan saat penilaian ulang.</div></div>
  @endif
</div>
