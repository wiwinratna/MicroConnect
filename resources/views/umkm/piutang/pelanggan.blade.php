@extends('layouts.umkm')
@section('title', 'Piutang Pelanggan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h1 class="h3 mb-1"><strong>Data</strong> Pelanggan</h1>
    <p class="text-muted mb-0">Daftar pelanggan yang bisa dicatat piutangnya.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="{{ route('umkm.piutang.index') }}" class="btn btn-outline-secondary">← Piutang</a>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPelanggan">+ Tambah Pelanggan</button>
  </div>
</div>

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <table class="table table-hover mb-0 table-borderless align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Nama Pelanggan</th>
          <th>No. WhatsApp</th>
          <th>Email</th>
          <th>Alamat</th>
          <th>Total Piutang</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($pelanggan as $p)
          <tr>
            <td class="text-muted small">{{ $loop->iteration }}</td>
            <td><strong>{{ $p->nama_pelanggan }}</strong></td>
            <td>{{ $p->no_whatsapp ?? '<span class="text-muted">-</span>' }}</td>
            <td>{{ $p->email ?? '<span class="text-muted">-</span>' }}</td>
            <td>{{ $p->alamat ?? '-' }}</td>
            <td>
              @php $total = $p->totalPiutangAktif(); @endphp
              @if($total > 0)
                <span class="badge bg-danger-subtle text-danger">{{ rupiah($total) }}</span>
              @else
                <span class="badge bg-success-subtle text-success">Lunas</span>
              @endif
            </td>
            <td class="text-end">
              <form method="POST" action="{{ route('umkm.etalase.pelanggan.destroy', $p->id) }}" onsubmit="return confirm('Hapus pelanggan ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-action btn-action-delete" title="Hapus"><i data-feather="trash-2"></i> Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pelanggan. Tambahkan pelanggan pertama Anda.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if($pelanggan->hasPages())
  <div class="mt-3">{{ $pelanggan->links() }}</div>
@endif

{{-- Modal Tambah Pelanggan --}}
<div class="modal fade" id="modalTambahPelanggan" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="{{ route('umkm.etalase.pelanggan.store') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Tambah Pelanggan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
            <input type="text" name="nama_pelanggan" class="form-control" required placeholder="Nama lengkap pelanggan">
          </div>
          <div class="mb-3">
            <label class="form-label">No. WhatsApp</label>
            <input type="text" name="no_whatsapp" class="form-control" placeholder="08xxxxxxxxxx">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" placeholder="contoh@email.com">
            <div class="form-text">Digunakan untuk mengirim pengingat pembayaran otomatis via Email.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat</label>
            <input type="text" name="alamat" class="form-control" placeholder="Alamat pelanggan (opsional)">
          </div>
          <div class="mb-3">
            <label class="form-label">Catatan</label>
            <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan opsional..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
