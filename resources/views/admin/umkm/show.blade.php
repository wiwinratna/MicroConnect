@extends('layouts.admin')
@section('title', 'Detail UMKM')

@section('content')
<div class="container-fluid p-0">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h3 mb-1"><strong>{{ $umkm->nama_usaha ?? 'UMKM Tanpa Nama' }}</strong></h1>
      <p class="text-muted mb-0"><code>{{ $umkm->kode_umkm }}</code> &bull; {{ $umkm->user->email }}</p>
    </div>
    <div class="d-flex gap-2">
      {{-- Toggle Status --}}
      <form method="POST" action="{{ route('admin.umkm.toggleStatus', $umkm->id) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn btn-sm {{ $umkm->status === 'aktif' ? 'btn-outline-warning' : 'btn-success' }}"
                onclick="return confirm('Ubah status UMKM ini?')">
          {{ $umkm->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
        </button>
      </form>
      <a href="{{ route('admin.umkm.index') }}" class="btn btn-outline-secondary btn-sm">← Kembali</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
  @endif

  <div class="row g-4">

    {{-- Info Usaha --}}
    <div class="col-md-5">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header fw-semibold">🏪 Informasi Usaha</div>
        <div class="card-body">
          <table class="table table-sm table-borderless mb-0">
            <tr><td class="text-muted">Nama Usaha</td><td>{{ $umkm->nama_usaha ?? '—' }}</td></tr>
            <tr><td class="text-muted">Jenis Usaha</td><td>{{ $umkm->jenis_usaha ?? '—' }}</td></tr>
            <tr><td class="text-muted">NIB</td><td>{{ $umkm->nib ?? '—' }}</td></tr>
            <tr><td class="text-muted">No. Telepon</td><td>{{ $umkm->no_telepon ?? '—' }}</td></tr>
            <tr><td class="text-muted">WhatsApp</td><td>{{ $umkm->no_whatsapp ?? '—' }}</td></tr>
            <tr><td class="text-muted">Alamat</td><td>{{ $umkm->alamat ?? '—' }}</td></tr>
            <tr><td class="text-muted">Metode Pencatatan</td><td><span class="badge bg-secondary">{{ $umkm->recording_method }}</span></td></tr>
            <tr><td class="text-muted">Metode Inventori</td><td><span class="badge bg-info text-dark">{{ $umkm->inventory_method }}</span></td></tr>
            <tr><td class="text-muted">Status</td>
              <td>
                @if($umkm->status === 'aktif')
                  <span class="badge bg-success">Aktif</span>
                @else
                  <span class="badge bg-secondary">Nonaktif</span>
                @endif
              </td>
            </tr>
            <tr><td class="text-muted">Bergabung</td><td>{{ $umkm->created_at->isoFormat('D MMMM Y') }}</td></tr>
          </table>
        </div>
      </div>
    </div>

    {{-- Aktivitas + Ubah Level --}}
    <div class="col-md-7">

      {{-- KPI Aktivitas --}}
      <div class="row g-3 mb-4">
        <div class="col-6">
          <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
              <p class="text-muted small mb-1">Penjualan Bulan Ini</p>
              <h5 class="fw-bold text-success">Rp {{ number_format($penjualanBulanIni, 0, ',', '.') }}</h5>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-3">
              <p class="text-muted small mb-1">Total Piutang Aktif</p>
              <h5 class="fw-bold {{ $totalPiutangAktif > 0 ? 'text-danger' : 'text-muted' }}">
                Rp {{ number_format($totalPiutangAktif, 0, ',', '.') }}
              </h5>
            </div>
          </div>
        </div>
      </div>

      {{-- Ubah Level --}}
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header fw-semibold">⭐ Level UMKM</div>
        <div class="card-body">
          <p class="text-muted small mb-2">
            Level saat ini:
            <strong>{{ $umkm->level ? $umkm->level->kode . ' — ' . $umkm->level->nama_level : 'Belum dipilih' }}</strong>
          </p>
          <form method="POST" action="{{ route('admin.umkm.updateLevel', $umkm->id) }}" class="d-flex gap-2">
            @csrf @method('PUT')
            <select name="level_id" class="form-select flex-grow-1">
              @foreach($levels as $lv)
                <option value="{{ $lv->id }}" {{ $umkm->level_id == $lv->id ? 'selected' : '' }}>
                  {{ $lv->kode }} — {{ $lv->nama_level }}
                </option>
              @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Simpan</button>
          </form>
        </div>
      </div>

      {{-- Riwayat & Konfirmasi Iuran --}}
      <div class="card border-0 shadow-sm">
        <div class="card-header fw-semibold">💳 Iuran Bulanan (6 Bulan Terakhir)</div>
        <div class="card-body p-0">
          <table class="table table-sm mb-0">
            <thead class="table-light">
              <tr>
                <th>Periode</th>
                <th>Nominal</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($iuranList as $iuran)
                <tr>
                  <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $iuran->periode)->isoFormat('MMM Y') }}</td>
                  <td>Rp {{ number_format($iuran->nominal, 0, ',', '.') }}</td>
                  <td>
                    @if($iuran->status === 'lunas')
                      <span class="badge bg-success">Lunas</span>
                    @else
                      <span class="badge bg-danger">Belum Bayar</span>
                    @endif
                  </td>
                  <td>
                    @if($iuran->status !== 'lunas')
                      <form method="POST" action="{{ route('admin.umkm.konfirmasiIuran', $umkm->id) }}">
                        @csrf
                        <input type="hidden" name="iuran_id" value="{{ $iuran->id }}">
                        <button type="submit" class="btn btn-xs btn-outline-success btn-sm py-0">✔ Konfirmasi</button>
                      </form>
                    @else
                      <small class="text-muted">{{ $iuran->dibayar_pada?->isoFormat('D MMM Y') }}</small>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-muted text-center py-3">Belum ada data iuran.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

</div>
@endsection
