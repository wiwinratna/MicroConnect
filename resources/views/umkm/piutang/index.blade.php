@extends('layouts.umkm')
@section('title', 'Piutang Pelanggan')

@push('styles')
<style>
    /* ── Table Premium Styling ── */
    .table-premium th { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; border-bottom: 1px solid #e2e8f0 !important; padding: 1rem 1.25rem; background: #fafbfc; }
    .table-premium td { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .table-premium tr:last-child td { border-bottom: none; }
    .table-premium tr:hover td { background: #f8fafc; }

    /* ── Action Buttons ── */
    .btn-action { width: 32px; height: 32px; border-radius: 8px; background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; }
    .btn-action:hover { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
    .btn-action-view:hover { background: #fdf5ff; border-color: #f5d0fe; color: #c026d3; }
    .btn-action-whatsapp:hover { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
    .btn-action-pay:hover { background: #fffbeb; border-color: #fde68a; color: #d97706; }

    /* ── Summary Card ── */
    .summary-card { border-radius: 16px; transition: transform 0.2s; background: #fff; }
    .summary-card:hover { transform: translateY(-3px); }

    /* ── Dialog Design ── */
    #dialogBayarPiutang { border: none; border-radius: 16px; padding: 0; margin: auto; max-width: 500px; width: 95%; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25); border: 1px solid #f1f5f9; }
    #dialogBayarPiutang[open] { display: flex; flex-direction: column; animation: dialogIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes dialogIn { from { opacity:0; transform: translateY(20px) scale(0.95); } to { opacity:1; transform: translateY(0) scale(1); } }
    #dialogBayarPiutang::backdrop { background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); }
</style>
@endpush

@section('content')
{{-- Header Area - CLEAN --}}
<div class="mb-4">
    <h1 class="h3 mb-1"><strong>Piutang</strong> Pelanggan</h1>
    <p class="text-muted mb-0" style="font-size: 0.85rem;">Manajemen tagihan dan riwayat pembayaran pelanggan Anda.</p>
</div>

{{-- Summary Section --}}
<div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm overflow-hidden" style="border-radius:20px; background: #fff;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em; text-transform: uppercase;">Total Piutang Aktif</p>
                        <h3 class="fw-bold mb-0" style="color: #1e293b; letter-spacing: -0.02em;">{{ rupiah($totalSisa) }}</h3>
                    </div>
                    <div class="p-3 bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                        <i data-feather="dollar-sign" class="text-danger" style="width: 28px; height: 28px;"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 4px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 100%; border-radius: 2px;"></div>
                    </div>
                    <p class="text-muted mt-2 mb-0" style="font-size: 0.75rem;">Total tagihan yang harus ditagih</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Content --}}
<div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
    <div class="card-header bg-white border-0 p-0">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center px-4 pt-3 border-bottom gap-3">
            {{-- Left: Tabs --}}
            <ul class="nav nav-tabs border-0" id="filterTab" style="gap: 1.5rem;">
                <li class="nav-item">
                    <a class="nav-link px-0 pb-3 border-0 rounded-0 position-relative {{ request('status', 'aktif') === 'aktif' ? 'active' : '' }}" 
                       href="?status=aktif" 
                       style="font-size: 0.82rem; font-weight: 600; color: {{ request('status', 'aktif') === 'aktif' ? '#2563eb' : '#64748b' }};">
                        Belum Lunas
                        @if(request('status', 'aktif') === 'aktif')
                            <div style="position:absolute; bottom:0; left:0; width:100%; height:3px; background:#2563eb; border-radius:3px 3px 0 0;"></div>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-0 pb-3 border-0 rounded-0 position-relative {{ request('status') === 'lunas' ? 'active' : '' }}" 
                       href="?status=lunas"
                       style="font-size: 0.82rem; font-weight: 600; color: {{ request('status') === 'lunas' ? '#2563eb' : '#64748b' }};">
                        Sudah Lunas
                        @if(request('status') === 'lunas')
                            <div style="position:absolute; bottom:0; left:0; width:100%; height:3px; background:#2563eb; border-radius:3px 3px 0 0;"></div>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-0 pb-3 border-0 rounded-0 position-relative {{ request('status') === 'semua' ? 'active' : '' }}" 
                       href="?status=semua"
                       style="font-size: 0.82rem; font-weight: 600; color: {{ request('status') === 'semua' ? '#2563eb' : '#64748b' }};">
                        Semua Data
                        @if(request('status') === 'semua')
                            <div style="position:absolute; bottom:0; left:0; width:100%; height:3px; background:#2563eb; border-radius:3px 3px 0 0;"></div>
                        @endif
                    </a>
                </li>
            </ul>

            {{-- Right: Actions - Moved here for NEATER look --}}
            <div class="d-flex gap-2 pb-3 pb-md-0">
                <a href="{{ route('umkm.etalase.pelanggan.index') }}" class="btn btn-white btn-sm border shadow-none" style="border-radius:8px; padding: 0.4rem 0.8rem; font-size: 0.78rem; font-weight: 500; background:#fff;">
                    <i data-feather="users" style="width:13px; height:13px; margin-right:4px;"></i> Pelanggan
                </a>
                <a href="{{ route('umkm.piutang.create') }}" class="btn btn-primary btn-sm shadow-sm" style="border-radius:8px; padding: 0.4rem 0.8rem; font-size: 0.78rem; font-weight: 600;">
                    <i data-feather="plus" style="width:13px; height:13px; margin-right:4px;"></i> Tambah Piutang
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-borderless table-premium">
                <thead>
                    <tr>
                        <th class="ps-4">Kode</th>
                        <th>Pelanggan</th>
                        <th class="text-end">Nominal Awal</th>
                        <th class="text-end">Sisa Tagihan</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($piutang as $p)
                        @php
                            $isLewat = $p->status !== 'lunas' && $p->jatuh_tempo->isPast();
                            $variant = 'danger';
                            if($p->status === 'lunas') $variant = 'success';
                            if($p->status === 'sebagian') $variant = 'warning';

                            $phone = $p->pelanggan->no_whatsapp ?? '';
                            $phoneClean = preg_replace('/\D/', '', $phone);
                            if (str_starts_with($phoneClean, '0')) $phoneClean = '62' . substr($phoneClean, 1);
                            elseif (str_starts_with($phoneClean, '8')) $phoneClean = '62' . $phoneClean;
                            
                            $pesan = "Halo {$p->pelanggan->nama_pelanggan}, kami dari *{$p->umkm->nama_umkm}* ingin mengingatkan tagihan sebesar *Rp ".format_angka($p->sisa)."* dengan jatuh tempo pada *".$p->jatuh_tempo->isoFormat('D MMMM Y')."*. Mohon konfirmasinya. Terima kasih 🙏";
                            $waLink = $phoneClean ? "https://wa.me/{$phoneClean}?text=" . rawurlencode($pesan) : '';
                        @endphp
                        <tr class="{{ $isLewat ? 'bg-danger bg-opacity-10' : '' }}">
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border" style="font-family: monospace; font-size: 0.75rem;">{{ $p->kode_piutang }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $p->pelanggan->nama_pelanggan }}</div>
                                <div class="text-muted small">{{ $p->pelanggan->no_whatsapp ?: 'No WA -' }}</div>
                            </td>
                            <td class="text-end fw-medium text-muted small">{{ rupiah($p->nominal_awal) }}</td>
                            <td class="text-end fw-bold {{ $p->status === 'lunas' ? 'text-success' : 'text-danger' }}">
                                {{ rupiah($p->sisa) }}
                            </td>
                            <td>
                                <div class="small fw-semibold {{ $isLewat ? 'text-danger' : 'text-dark' }}">
                                    {{ $p->jatuh_tempo->format('d M Y') }}
                                </div>
                                @if($isLewat) <span class="badge bg-danger p-1" style="font-size: 0.6rem;">OVERDUE</span> @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $variant }} {{ $variant === 'warning' ? 'text-dark' : '' }} border-0 shadow-none" style="font-size: 0.7rem; border-radius: 6px; padding: 0.4rem 0.6rem;">
                                    {{ strtoupper($p->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    @if($p->status !== 'lunas')
                                    <button type="button" class="btn-action btn-action-pay" title="Catat Bayar" onclick="bukaBayar({{ $p->id }}, '{{ addslashes($p->pelanggan->nama_pelanggan) }}', {{ $p->sisa }}, '{{ $p->kode_piutang }}')">
                                        <i data-feather="dollar-sign" style="width:14px; height:14px;"></i>
                                    </button>
                                    @if($phoneClean)
                                    <a href="{{ $waLink }}" target="_blank" class="btn-action btn-action-whatsapp" title="WhatsApp Reminder">
                                        <i data-feather="message-circle" style="width:14px; height:14px;"></i>
                                    </a>
                                    @endif
                                    @endif
                                    <a href="{{ route('umkm.piutang.show', $p->id) }}" class="btn-action btn-action-view" title="Detail">
                                        <i data-feather="eye" style="width:14px; height:14px;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada catatan piutang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($piutang->hasPages())
    <div class="mt-4">{{ $piutang->links() }}</div>
@endif

{{-- Native Dialog Catat Pembayaran --}}
<dialog id="dialogBayarPiutang">
    <div style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: start;">
        <div>
            <h5 class="fw-bold text-dark mb-1">Catat Pembayaran</h5>
            <div class="text-muted small" id="dialogMeta">Memuat info...</div>
        </div>
        <button type="button" class="btn-close" onclick="document.getElementById('dialogBayarPiutang').close()"></button>
    </div>
    <form method="POST" id="formBayarPiutang">
        @csrf
        <div style="padding: 1.5rem;">
            <div class="alert alert-warning border-0 d-flex justify-content-between align-items-center mb-4" style="border-radius:10px;">
                <span class="small fw-semibold">Sisa Tagihan:</span>
                <span class="fw-bold h5 mb-0" id="dialogSisa">Rp 0</span>
            </div>

            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label small fw-bold">Tanggal Bayar</label>
                    <input type="date" name="tanggal_bayar" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-6">
                    <label class="form-label small fw-bold">Nominal Bayar</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0 bg-light small">Rp</span>
                        <input type="text" id="jumlahBayarDisplay" class="form-control border-start-0 fw-bold" placeholder="0" required>
                        <input type="hidden" name="jumlah_bayar" id="jumlahBayarInput">
                    </div>
                </div>
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-sm btn-link text-decoration-none small p-0 fw-bold" id="btnLunasPenuh">✅ Bayar Lunas Penuh</button>
                </div>

                <div id="jatuhTempoBaruGroup" class="col-12" style="display:none;">
                    <label class="form-label small fw-bold text-danger">Jatuh Tempo Sisa Tagihan</label>
                    <input type="date" name="jatuh_tempo_baru" id="jatuhTempoBaruInput" class="form-control">
                    <div class="small text-muted mt-1">Sisa tagihan akan jatuh tempo pada tanggal ini.</div>
                </div>

                <div class="col-12">
                    <label class="form-label small fw-bold">Metode Bayar (Opsional)</label>
                    <select name="metode_bayar" class="form-select">
                        <option value="">-- Pilih --</option>
                        <option>Tunai</option><option>Transfer Bank</option><option>QRIS</option>
                    </select>
                </div>
            </div>
        </div>
        <div style="padding: 1rem 1.5rem 1.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button type="button" class="btn btn-white border px-4" onclick="document.getElementById('dialogBayarPiutang').close()">Batal</button>
            <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Pembayaran</button>
        </div>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
let currentSisa = 0;
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();

    const displayInput = document.getElementById('jumlahBayarDisplay');
    const hiddenInput  = document.getElementById('jumlahBayarInput');
    const displaySisa  = document.getElementById('dialogSisa');
    const jtGroup      = document.getElementById('jatuhTempoBaruGroup');
    const jtInput      = document.getElementById('jatuhTempoBaruInput');

    displayInput.addEventListener('input', function() {
        const raw = this.value.replace(/\D/g, '');
        const num = parseInt(raw) || 0;
        hiddenInput.value = raw;
        this.value = num > 0 ? num.toLocaleString('id-ID') : '';

        if (num > 0 && num < currentSisa) {
            jtGroup.style.display = 'block';
            jtInput.required = true;
        } else {
            jtGroup.style.display = 'none';
            jtInput.required = false;
        }
    });

    window.bukaBayar = function(id, nama, sisa, kode) {
        currentSisa = sisa;
        document.getElementById('formBayarPiutang').action = `{{ url("/umkm/piutang") }}/${id}/bayar`;
        document.getElementById('dialogMeta').innerText = `${kode} • ${nama}`;
        displaySisa.innerText = 'Rp ' + Math.round(sisa).toLocaleString('id-ID');
        displayInput.value = '';
        hiddenInput.value = '';
        jtGroup.style.display = 'none';
        
        document.getElementById('btnLunasPenuh').onclick = () => {
            hiddenInput.value = currentSisa;
            displayInput.value = currentSisa.toLocaleString('id-ID');
            displayInput.dispatchEvent(new Event('input'));
        };

        document.getElementById('dialogBayarPiutang').showModal();
        if (typeof feather !== 'undefined') feather.replace();
    };
});
</script>
@endpush
