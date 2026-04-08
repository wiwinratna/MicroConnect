@extends('layouts.umkm')
@section('title', 'Piutang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Piutang</strong> Pelanggan</h1>
    <p class="text-muted mb-0">Catatan tagihan pelanggan yang belum lunas.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('umkm.etalase.pelanggan.index') }}" class="btn btn-outline-secondary">Data Pelanggan</a>
    <a href="{{ route('umkm.piutang.create') }}" class="btn btn-primary">+ Tambah Piutang</a>
  </div>
</div>

{{-- Toast --}}


{{-- Summary card --}}
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <p class="text-muted small mb-1">Total Sisa Piutang Aktif</p>
        <h4 class="fw-bold text-danger">{{ rupiah($totalSisa) }}</h4>
      </div>
    </div>
  </div>
</div>

{{-- Filter tab --}}
<ul class="nav nav-tabs mb-3" id="filterTab">
  <li class="nav-item"><a class="nav-link {{ request('status', 'aktif') === 'aktif' ? 'active' : '' }}" href="?status=aktif">Belum Lunas</a></li>
  <li class="nav-item"><a class="nav-link {{ request('status') === 'lunas' ? 'active' : '' }}" href="?status=lunas">Lunas</a></li>
  <li class="nav-item"><a class="nav-link {{ request('status') === 'semua' ? 'active' : '' }}" href="?status=semua">Semua</a></li>
</ul>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0 table-borderless align-middle">
      <thead class="table-light">
        <tr>
          <th class="ps-3">Kode</th>
          <th>Pelanggan</th>
          <th class="text-end">Nominal Awal</th>
          <th class="text-end">Sisa</th>
          <th>Jatuh Tempo</th>
          <th>Status</th>
          <th class="text-center" width="200">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($piutang as $p)
          @php
            $isLewat = $p->status !== 'lunas' && $p->jatuh_tempo->isPast();

            $phone = $p->pelanggan->no_whatsapp ?? '';
            $phoneClean = preg_replace('/\D/', '', $phone);
            if (str_starts_with($phoneClean, '0')) {
                $phoneClean = '62' . substr($phoneClean, 1);
            } elseif (str_starts_with($phoneClean, '8')) {
                $phoneClean = '62' . $phoneClean;
            }
            $nominal = format_angka($p->sisa);
            $tgl = $p->jatuh_tempo->isoFormat('D MMMM Y');
            $usaha = $p->umkm->nama_usaha ?? 'Kami';
            $pelanggan = $p->pelanggan->nama_pelanggan ?? 'Bapak/Ibu';

            $pesan = "Halo {$pelanggan}, kami dari *{$usaha}* ingin mengingatkan bahwa masih ada tagihan sebesar *Rp {$nominal}* dengan jatuh tempo pada *{$tgl}*.\n\nMohon konfirmasi setelah pembayaran dilakukan. Terima kasih 🙏";
            $waLink = $phoneClean ? "https://wa.me/{$phoneClean}?text=" . rawurlencode($pesan) : '';
          @endphp
          <tr class="{{ $isLewat ? 'table-danger' : '' }}">
            <td class="ps-3"><code class="small">{{ $p->kode_piutang }}</code></td>
            <td><strong>{{ $p->pelanggan->nama_pelanggan }}</strong></td>
            <td class="text-end fw-medium">{{ rupiah($p->nominal_awal) }}</td>
            <td class="text-end fw-bold {{ $p->status === 'lunas' ? 'text-success' : 'text-danger' }}">
                {{ rupiah($p->sisa) }}
            </td>
            <td>
              {{ $p->jatuh_tempo->isoFormat('D MMM Y') }}
              @if($isLewat)
                <span class="badge bg-danger ms-1">Lewat</span>
              @endif
            </td>
            <td>
              @if($p->status === 'lunas')
                <span class="badge bg-success">Lunas</span>
              @elseif($p->status === 'sebagian')
                <span class="badge bg-warning text-dark">Sebagian</span>
              @else
                <span class="badge bg-danger">Belum Lunas</span>
              @endif
            </td>
            <td class="text-center">
              <div class="d-flex justify-content-center gap-1 flex-nowrap">
                @if($p->status !== 'lunas')
                <button type="button"
                        class="btn btn-sm btn-primary"
                        title="Catat Pembayaran"
                        onclick="bukaBayar({{ $p->id }}, '{{ addslashes($p->pelanggan->nama_pelanggan) }}', {{ $p->sisa }}, '{{ $p->kode_piutang }}')">
                    <i data-feather="dollar-sign" style="width:13px;height:13px;"></i> Bayar
                </button>
                @endif
                @if($phoneClean && $p->status !== 'lunas')
                  <a href="{{ $waLink }}" target="_blank" class="btn btn-sm btn-success" title="Hubungi WhatsApp">
                    <i data-feather="message-circle" style="width:13px;height:13px;"></i>
                  </a>
                @endif
                <a href="{{ route('umkm.piutang.show', $p->id) }}" class="btn btn-sm btn-action btn-action-view" title="Detail">
                    <i data-feather="eye" style="width:13px;height:13px;"></i>
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data piutang.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if($piutang->hasPages())
  <div class="mt-3">{{ $piutang->links() }}</div>
@endif

<style>
    #dialogBayarPiutang { border: none; border-radius: 12px; padding: 0; margin: auto; max-width: 500px; width: 95%; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
    #dialogBayarPiutang[open] { display: flex; flex-direction: column; }
    #dialogBayarPiutang::backdrop { background: rgba(0,0,0,0.5); backdrop-filter: blur(2px); }
    .dialog-header { padding: 20px 24px 12px; display: flex; justify-content: space-between; align-items: start; border-bottom: 1px solid #eee; }
    .dialog-body   { padding: 20px 24px; }
    .dialog-footer { padding: 12px 24px 20px; display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #eee; }
    .badge-sisa    { background: #fff3cd; color: #856404; padding: 4px 10px; border-radius: 6px; font-size: 0.82rem; }
    .badge-kode    { background: #e8f4fd; color: #0d6efd; padding: 3px 8px; border-radius: 4px; font-size: 0.78rem; font-family: monospace; }
    #jatuhTempoBaruGroup { display: none; }
    #jatuhTempoBaruGroup.show { display: block; }
</style>

{{-- Native Dialog Catat Pembayaran --}}
<dialog id="dialogBayarPiutang">
    <div class="dialog-header">
        <div>
            <h5 class="fw-bold mb-1" style="font-size: 1.1rem;">
                <i data-feather="dollar-sign" class="text-primary me-1" style="width:20px;height:20px;"></i>
                Catat Pembayaran
            </h5>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <span class="text-muted small" id="dialogNamaPelanggan">Pelanggan: -</span>
                <span class="badge-kode" id="dialogKodePiutang">-</span>
            </div>
        </div>
        <button type="button" class="btn-close" onclick="document.getElementById('dialogBayarPiutang').close()"></button>
    </div>
    <form method="POST" id="formBayarPiutang">
        @csrf
        <div class="dialog-body">
            {{-- Info sisa piutang --}}
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background: #f8f9fa;">
                <span class="text-muted small fw-semibold">Sisa Piutang:</span>
                <span class="badge-sisa fw-bold" id="dialogSisaPiutang">Rp 0</span>
            </div>

            {{-- Tanggal --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Tanggal Pembayaran <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_bayar" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            {{-- Jumlah bayar --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Jumlah Bayar (Rp) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" id="jumlahBayarDisplay" class="form-control fw-bold" placeholder="0" required inputmode="numeric">
                    <input type="hidden" name="jumlah_bayar" id="jumlahBayarInput">
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none" id="btnLunasPenuh">
                        ✅ Bayar lunas penuh
                    </button>
                    <small class="text-muted" id="labelStatusBayar"></small>
                </div>
            </div>

            {{-- Jatuh tempo baru (muncul hanya jika parsial) --}}
            <div class="mb-3" id="jatuhTempoBaruGroup">
                <label class="form-label fw-semibold">
                    Jatuh Tempo Baru untuk Sisa <span class="text-danger">*</span>
                </label>
                <input type="date" name="jatuh_tempo_baru" id="jatuhTempoBaruInput" class="form-control"
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                <small class="text-muted">Wajib diisi jika pembayaran belum lunas.</small>
            </div>

            {{-- Metode bayar --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Metode Bayar <span class="text-muted fw-normal">(opsional)</span></label>
                <select name="metode_bayar" class="form-select">
                    <option value="">-- Pilih metode --</option>
                    <option>Tunai</option>
                    <option>Transfer Bank</option>
                    <option>QRIS</option>
                    <option>OVO</option>
                    <option>GoPay</option>
                </select>
            </div>

            {{-- Catatan --}}
            <div class="mb-1">
                <label class="form-label fw-semibold">Catatan <span class="text-muted fw-normal">(opsional)</span></label>
                <textarea name="catatan" class="form-control" rows="2" placeholder="Misal: Bayar DP 50%..."></textarea>
            </div>

            {{-- Validation errors --}}
            @if($errors->any())
            <div class="alert alert-danger mt-3 mb-0 py-2 small">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        <div class="dialog-footer">
            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('dialogBayarPiutang').close()">Batal</button>
            <button type="submit" class="btn btn-primary" id="btnSimpanBayar">
                <i data-feather="save" style="width:14px;height:14px;"></i> Simpan Pembayaran
            </button>
        </div>
    </form>
</dialog>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentSisa = 0;

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toast').forEach(el => setTimeout(() => el.classList.remove('show'), 4500));
    if (typeof feather !== 'undefined') feather.replace();

    const displayInput = document.getElementById('jumlahBayarDisplay');
    const hiddenInput  = document.getElementById('jumlahBayarInput');
    const jatuhTempoGroup = document.getElementById('jatuhTempoBaruGroup');
    const jatuhTempoInput = document.getElementById('jatuhTempoBaruInput');
    const labelStatus  = document.getElementById('labelStatusBayar');

    displayInput.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        const num = parseInt(raw) || 0;
        hiddenInput.value = raw;
        this.value = num > 0 ? num.toLocaleString('id-ID') : '';

        if (num > 0 && num < currentSisa) {
            jatuhTempoGroup.classList.add('show');
            jatuhTempoInput.required = true;
            labelStatus.innerHTML = '<span class="text-warning fw-semibold">⚠ Parsial</span>';
        } else if (num > 0 && num >= currentSisa) {
            jatuhTempoGroup.classList.remove('show');
            jatuhTempoInput.required = false;
            jatuhTempoInput.value = '';
            labelStatus.innerHTML = '<span class="text-success fw-semibold">✅ Lunas</span>';
        } else {
            jatuhTempoGroup.classList.remove('show');
            jatuhTempoInput.required = false;
            labelStatus.innerHTML = '';
        }
    });

    @if($errors->any())
        document.getElementById('dialogBayarPiutang').showModal();
    @endif
});

function bukaBayar(piutangId, namaPelanggan, sisa, kodePiutang) {
    const baseUrl = '{{ url("/umkm/piutang") }}';
    document.getElementById('formBayarPiutang').action = baseUrl + '/' + piutangId + '/bayar';
    document.getElementById('dialogNamaPelanggan').textContent = 'Pelanggan: ' + namaPelanggan;
    document.getElementById('dialogKodePiutang').textContent = kodePiutang || '-';
    document.getElementById('dialogSisaPiutang').textContent = 'Rp ' + Math.round(sisa).toLocaleString('id-ID');

    const displayInput = document.getElementById('jumlahBayarDisplay');
    const hiddenInput  = document.getElementById('jumlahBayarInput');
    displayInput.value = '';
    hiddenInput.value  = '';
    document.getElementById('jatuhTempoBaruGroup').classList.remove('show');
    document.getElementById('jatuhTempoBaruInput').value = '';
    document.getElementById('jatuhTempoBaruInput').required = false;
    document.getElementById('labelStatusBayar').innerHTML = '';

    currentSisa = Math.round(sisa);

    document.getElementById('btnLunasPenuh').onclick = function() {
        hiddenInput.value = currentSisa;
        displayInput.value = currentSisa.toLocaleString('id-ID');
        displayInput.dispatchEvent(new Event('input'));
    };

    document.getElementById('dialogBayarPiutang').showModal();
    if (typeof feather !== 'undefined') feather.replace();
}
</script>
@endpush


