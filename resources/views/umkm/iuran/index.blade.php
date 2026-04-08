@extends('layouts.umkm')
@section('title', 'Iuran Bulanan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Iuran</strong> Bulanan</h1>
    <p class="text-muted mb-0">Status pembayaran iuran aplikasi MicroConnect per bulan.</p>
  </div>
</div>



{{-- Riwayat Iuran --}}
<div class="card border-0 shadow-sm">
  <div class="card-header bg-transparent fw-semibold">Riwayat Iuran</div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0 table-borderless align-middle">
      <thead class="table-light">
        <tr>
          <th>Periode</th>
          <th>Nominal</th>
          <th>Jatuh Tempo</th>
          <th>Status</th>
          <th>Dibayar Pada</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($iuranList as $iuran)
          <tr>
            <td>
              <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $iuran->periode)->isoFormat('MMMM Y') }}</strong>
              @if($iuran->periode === now()->format('Y-m'))
                <span class="badge bg-primary ms-1">Bulan Ini</span>
              @endif
            </td>
            <td class="text-end fw-medium">{{ rupiah($iuran->nominal) }}</td>
            <td>{{ $iuran->jatuh_tempo?->isoFormat('D MMM Y') ?? '-' }}</td>
            <td>
              <span class="badge {{ $iuran->statusBadgeClass() }}">
                {{ $iuran->statusLabel() }}
              </span>
            </td>
            <td class="text-muted small">{{ $iuran->dibayar_pada?->isoFormat('D MMM Y') ?? '-' }}</td>
            <td>
              @if($iuran->isBayarable())
                <button class="btn btn-sm btn-outline-success" onclick="bayarIuran({{ $iuran->id }})">
                  Bayar
                </button>
              @else
                <span class="text-muted small">-</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">Belum ada data iuran.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3">
  <small class="text-muted">
    💡 Pembayaran iuran dilakukan melalui Midtrans. Klik tombol "Bayar" untuk memulai proses pembayaran.
  </small>
</div>
@endsection

@push('scripts')
{{-- Midtrans SNAP JS --}}
<script src="{{ $midtransSnapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
<script>
    function bayarIuran(iuranId) {
        // Tampilkan loading
        const btn = event.target;
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Memproses...';

        // Request SNAP token dari server
        fetch(`/umkm/iuran/${iuranId}/bayar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = originalText;

            if (!data.success) {
                alert(data.message || 'Gagal membuat transaksi.');
                return;
            }

            // Buka SNAP popup Midtrans
            window.snap.pay(data.snap_token, {
                onSuccess: function(result) {
                    alert('Pembayaran berhasil! Status akan diperbarui otomatis.');
                    window.location.reload();
                },
                onPending: function(result) {
                    alert('Pembayaran sedang diproses. Status akan diperbarui saat pembayaran selesai.');
                    window.location.reload();
                },
                onError: function(result) {
                    alert('Pembayaran gagal. Silakan coba lagi.');
                },
                onClose: function() {
                    // User menutup popup tanpa menyelesaikan pembayaran
                    console.log('Popup pembayaran ditutup.');
                }
            });
        })
        .catch(error => {
            btn.disabled = false;
            btn.textContent = originalText;
            alert('Terjadi kesalahan. Silakan coba lagi.');
            console.error('Error:', error);
        });
    }
</script>
@endpush
