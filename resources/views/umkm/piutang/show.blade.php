@extends('layouts.umkm')
@section('title', 'Detail Piutang ' . $piutang->kode_piutang)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Detail</strong> Piutang</h1>
    <p class="text-muted mb-0">{{ $piutang->kode_piutang }} &mdash; {{ $piutang->pelanggan->nama_pelanggan }}</p>
  </div>
  <a href="{{ route('umkm.piutang.index') }}" class="btn btn-outline-secondary">← Kembali</a>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">

  {{-- Info Piutang --}}
  <div class="col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold">Informasi Tagihan</div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0 table-hover align-middle">
          <tr><td class="text-muted">Pelanggan</td><td><strong>{{ $piutang->pelanggan->nama_pelanggan }}</strong></td></tr>
          <tr><td class="text-muted">No WA</td><td>{{ $piutang->pelanggan->no_whatsapp ?? '-' }}</td></tr>
          <tr><td class="text-muted">Tanggal</td><td>{{ $piutang->tanggal->isoFormat('D MMMM Y') }}</td></tr>
          <tr><td class="text-muted">Jatuh Tempo</td>
            <td>
              {{ $piutang->jatuh_tempo->isoFormat('D MMMM Y') }}
              @if($piutang->status !== 'lunas' && $piutang->jatuh_tempo->isPast())
                <span class="badge bg-danger ms-1">Lewat</span>
              @endif
            </td>
          </tr>
          <tr><td class="text-muted">Nominal Awal</td><td class="text-end fw-medium">{{ rupiah($piutang->nominal_awal) }}</td></tr>
          <tr><td class="text-muted">Sudah Dibayar</td><td  class="text-success text-end fw-medium">{{ rupiah($piutang->sudah_dibayar) }}</td></tr>
          <tr><td class="text-muted">Sisa</td>
            <td>
              @if($piutang->sisa > 0)
                <strong class="text-danger">{{ rupiah($piutang->sisa) }}</strong>
              @else
                <strong class="text-success">Lunas</strong>
              @endif
            </td>
          </tr>
          <tr><td class="text-muted">Status</td>
            <td>
              @if($piutang->status === 'lunas')
                <span class="badge bg-success">Lunas</span>
              @elseif($piutang->status === 'sebagian')
                <span class="badge bg-warning text-dark">Sebagian</span>
              @else
                <span class="badge bg-danger">Belum Lunas</span>
              @endif
            </td>
          </tr>
          @if($piutang->catatan)
            <tr><td class="text-muted">Catatan</td><td>{{ $piutang->catatan }}</td></tr>
          @endif
        </table>

        @php
          $phone = $piutang->pelanggan->no_whatsapp ?? '';
          $phoneClean = preg_replace('/\D/', '', $phone);
          if (str_starts_with($phoneClean, '0')) {
              $phoneClean = '62' . substr($phoneClean, 1);
          } elseif (str_starts_with($phoneClean, '8')) {
              $phoneClean = '62' . $phoneClean;
          }

          $nominal = format_angka($piutang->sisa);
          $tgl = $piutang->jatuh_tempo->isoFormat('D MMMM Y');
          $usaha = $piutang->umkm->nama_usaha ?? 'Kami';
          $pelanggan = $piutang->pelanggan->nama_pelanggan ?? 'Bapak/Ibu';
          
          $pesan = "Halo {$pelanggan}, kami dari *{$usaha}* ingin mengingatkan bahwa masih ada tagihan sebesar *Rp {$nominal}* dengan jatuh tempo pada *{$tgl}*.\n\nMohon konfirmasi setelah pembayaran dilakukan. Terima kasih 🙏";
          
          $waLink = $phoneClean ? "https://wa.me/{$phoneClean}?text=" . rawurlencode($pesan) : '';
        @endphp

        <div class="mt-4 pt-3 border-top">
          @if($phoneClean)
            <div class="d-flex gap-2">
              <a href="{{ $waLink }}" target="_blank" class="btn btn-success fw-bold d-flex align-items-center justify-content-center flex-grow-1 gap-2">
                <i data-feather="message-circle" style="width: 18px;"></i> Hubungi WhatsApp
              </a>
              <button type="button" class="btn btn-outline-secondary d-flex align-items-center justify-content-center px-3" 
                      onclick="navigator.clipboard.writeText(`{{ $pesan }}`); alert('Pesan berhasil disalin!');" title="Salin Pesan">
                <i data-feather="copy" style="width: 18px;"></i>
              </button>
            </div>
            <div class="text-muted small mt-2 text-center" style="font-size: 0.75rem;">
              Pesan WA otomatis akan terbuka dengan format pengingat tagihan.
            </div>
          @else
            <div class="alert alert-warning py-2 px-3 small mb-0 d-flex align-items-center gap-2">
              <i data-feather="alert-triangle" style="width: 16px;"></i> Nomor WhatsApp pelanggan tidak tersedia atau tidak valid.
            </div>
          @endif
        </div>

        {{-- Email Reminder Box --}}
        <div class="mt-3 pt-3 border-top">
          <h6 class="fw-bold mb-3 d-flex align-items-center gap-2">
            <i data-feather="mail" style="width: 18px;"></i> Email Pengingat
          </h6>
          
          @if($piutang->pelanggan->email)
            <div class="mb-2 small">
              <span class="text-muted">Otomasi (H-3, H-0, Telat):</span> 
              @if($piutang->email_reminder_enabled)
                <span class="badge bg-success-subtle text-success">Aktif @ {{ \Carbon\Carbon::parse($piutang->reminder_send_time)->format('H:i') }} WIB</span>
              @else
                <span class="badge bg-secondary-subtle text-secondary">Nonaktif</span>
              @endif
            </div>

            <div class="mb-3 small">
              <span class="text-muted">Riwayat Pengiriman:</span> 
              @if($piutang->last_email_reminder_sent_at)
                <strong>{{ $piutang->last_email_reminder_sent_at->isoFormat('D MMM Y, HH:mm') }}</strong>
                <span class="text-muted">({{ $piutang->email_reminder_count }}x terkirim)</span>
              @else
                Belum pernah dikirim
              @endif
            </div>

            <div class="d-flex gap-2">
              <form method="POST" action="{{ route('umkm.piutang.email.send', $piutang->id) }}" onsubmit="return confirm('Kirim email pengingat manual sekarang?')" class="flex-grow-1">
                @csrf
                <button type="submit" class="btn btn-primary fw-bold w-100 d-flex align-items-center justify-content-center gap-2">
                  <i data-feather="send" style="width: 18px;"></i> Kirim Email
                </button>
              </form>
              <a href="{{ route('umkm.piutang.email.preview', $piutang->id) }}" target="_blank" class="btn btn-outline-secondary d-flex align-items-center justify-content-center px-3" title="Preview Email">
                <i data-feather="eye" style="width: 18px;"></i>
              </a>
            </div>
            
          @else
            <div class="alert alert-warning py-2 px-3 small mb-0 d-flex align-items-center gap-2">
              <i data-feather="alert-triangle" style="width: 16px;"></i> Pelanggan belum memiliki alamat Email, cek <a href="{{ route('umkm.etalase.pelanggan.index') }}" class="alert-link">Data Pelanggan</a>.
            </div>
          @endif
        </div>

      </div>
    </div>
  </div>

  {{-- Catat Pembayaran + Riwayat --}}
  <div class="col-md-7">

    {{-- Form Bayar (hanya tampil jika belum lunas) --}}
    @if($piutang->status !== 'lunas')
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent fw-semibold">Catat Pembayaran</div>
      <div class="card-body">
        <form method="POST" action="{{ route('umkm.piutang.bayar', $piutang->id) }}">
          @csrf
          @if($errors->any())
            <div class="alert alert-danger py-2 small">
              @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
          @endif
          <div class="row g-2">
            <div class="col-md-4">
              <label class="form-label small">Tanggal Bayar</label>
              <input type="date" name="tanggal_bayar" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Jumlah (Rp)</label>
              <input type="number" name="jumlah_bayar" class="form-control form-control-sm" required
                     min="1" max="{{ $piutang->sisa }}" step="1000"
                     placeholder="Maks: {{ format_angka($piutang->sisa) }}">
            </div>
            <div class="col-md-4">
              <label class="form-label small">Metode</label>
              <select name="metode_bayar" class="form-select form-select-sm">
                <option value="">- pilih -</option>
                <option value="tunai">Tunai</option>
                <option value="transfer">Transfer</option>
                <option value="qris">QRIS</option>
              </select>
            </div>
            <div class="col-12">
              <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan (opsional)">
            </div>
            <div class="col-12 d-flex justify-content-end">
              <button type="submit" class="btn btn-success btn-sm">Simpan Pembayaran</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    @endif

    {{-- Riwayat Pembayaran --}}
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">Riwayat Pembayaran</div>
      <div class="card-body p-0">
        @if($piutang->pembayaran->isEmpty())
          <p class="text-muted text-center py-3 mb-0">Belum ada pembayaran yang dicatat.</p>
        @else
          <table class="table table-sm mb-0 table-hover table-borderless align-middle">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Metode</th>
                <th>Catatan</th>
              </tr>
            </thead>
            <tbody>
              @foreach($piutang->pembayaran->sortByDesc('tanggal_bayar') as $bayar)
                <tr>
                  <td>{{ \Carbon\Carbon::parse($bayar->tanggal_bayar)->isoFormat('D MMM Y') }}</td>
                  <td  class="text-success fw-semibold text-end fw-medium">{{ rupiah($bayar->jumlah_bayar) }}</td>
                  <td>{{ $bayar->metode_bayar ?? '-' }}</td>
                  <td class="text-muted small">{{ $bayar->catatan ?? '-' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection
