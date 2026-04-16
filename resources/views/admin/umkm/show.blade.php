@extends('layouts.admin')
@section('title', 'Detail Profil UMKM')

@section('content')
<div class="container-fluid p-0">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="{{ route('admin.umkm.index') }}" class="btn bg-white shadow-sm border rounded-circle p-2 me-3" title="Kembali">
            <i data-feather="arrow-left" class="text-dark"></i>
        </a>
        <div>
            <h1 class="h3 mb-1"><strong>{{ $umkm->nama_usaha ?? 'Proyek Tanpa Nama' }}</strong></h1>
            <p class="text-muted small mb-0"><i data-feather="hash" class="me-1" style="width: 12px;"></i>{{ $umkm->kode_umkm }} &bull; <i data-feather="mail" class="me-1 ms-2" style="width: 12px;"></i>{{ $umkm->user->email }}</p>
        </div>
    </div>
    <div>
      <form method="POST" action="{{ route('admin.umkm.toggleStatus', $umkm->id) }}">
        @csrf @method('PATCH')
        <button type="submit" class="btn {{ $umkm->status === 'aktif' ? 'btn-outline-danger' : 'btn-success' }} px-3 rounded-pill btn-sm d-flex align-items-center shadow-sm">
          <i data-feather="{{ $umkm->status === 'aktif' ? 'user-x' : 'user-check' }}" class="me-1" style="width: 14px;"></i>
          {{ $umkm->status === 'aktif' ? 'Nonaktifkan UMKM' : 'Pulihkan Akun' }}
        </button>
      </form>
    </div>
  </div>

  <div class="row g-4">

    {{-- Info Profil Utama --}}
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
        <div class="card-body p-4">
          <div class="text-center mb-4">
             <div class="position-relative d-inline-block">
                <div class="p-4 bg-primary-subtle text-primary rounded-circle mb-3" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
                   <i data-feather="briefcase" style="width: 32px; height: 32px;"></i>
                </div>
                <span class="position-absolute bottom-0 end-0 badge bg-{{ $umkm->status === 'aktif' ? 'success' : 'secondary' }} rounded-circle p-2 border border-white" style="width: 12px; height: 12px;"></span>
             </div>
             <h4 class="fw-bold text-dark mb-1">{{ $umkm->nama_usaha ?? '—' }}</h4>
             <div class="badge bg-secondary-subtle text-secondary rounded-pill px-3 fw-normal small">{{ $umkm->jenis_usaha ?? 'Sektor Belum Diisi' }}</div>
          </div>

          <div class="p-3 bg-light rounded-4 mb-4">
              <div class="d-flex align-items-center mb-3">
                  <div class="bg-white p-2 rounded-3 shadow-sm me-3"><i data-feather="credit-card" class="text-muted" style="width: 16px;"></i></div>
                  <div>
                      <div class="text-muted x-small text-uppercase fw-bold" style="letter-spacing: 0.5px;">NIB / Izin Usaha</div>
                      <div class="fw-bold text-dark">{{ $umkm->nib ?? '—' }}</div>
                  </div>
              </div>
              <div class="d-flex align-items-center mb-3">
                  <div class="bg-white p-2 rounded-3 shadow-sm me-3"><i data-feather="phone" class="text-muted" style="width: 16px;"></i></div>
                  <div>
                      <div class="text-muted x-small text-uppercase fw-bold" style="letter-spacing: 0.5px;">WhastApp</div>
                      <div class="fw-bold text-success">{{ $umkm->no_whatsapp ?? '—' }}</div>
                  </div>
              </div>
              <div class="d-flex align-items-start border-top pt-3">
                  <div class="bg-white p-2 rounded-3 shadow-sm me-3 mt-1"><i data-feather="map-pin" class="text-muted" style="width: 16px;"></i></div>
                  <div>
                      <div class="text-muted x-small text-uppercase fw-bold" style="letter-spacing: 0.5px;">Lokasi Operasional</div>
                      <div class="text-dark small lh-sm">{{ $umkm->alamat ?? 'Alamat belum tercatat.' }}</div>
                  </div>
              </div>
          </div>

          <div class="row g-2">
              <div class="col-6">
                <div class="p-3 border rounded-3 text-center">
                    <div class="text-muted x-small text-uppercase mb-1">Metode Stok</div>
                    <div class="fw-bold text-dark x-small">{{ $umkm->inventory_method }}</div>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 border rounded-3 text-center">
                    <div class="text-muted x-small text-uppercase mb-1">Pencatatan</div>
                    <div class="fw-bold text-dark x-small">{{ ucfirst($umkm->recording_method) }}</div>
                </div>
              </div>
          </div>
          
          <div class="mt-4 pt-3 border-top text-center">
             <span class="text-muted x-small"><i data-feather="calendar" class="me-1" style="width: 12px;"></i>Terdaftar Sejak: {{ $umkm->created_at->isoFormat('D MMM Y') }}</span>
          </div>
        </div>
      </div>
    </div>

    {{-- Detail & Monitoring --}}
    <div class="col-lg-8">
      
      {{-- Stats Banner --}}
      <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-success">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-2">Omzet (30 Hari)</p>
                        <h2 class="fw-bold text-dark mb-0">{{ rupiah($penjualanBulanIni) }}</h2>
                    </div>
                    <div class="p-3 bg-success-subtle text-success rounded-4">
                        <i data-feather="trending-up" style="width: 28px; height: 28px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-start border-4 border-danger">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-2">Tunggakan Iuran / Piutang</p>
                        <h2 class="fw-bold text-danger mb-0">{{ rupiah($totalPiutangAktif) }}</h2>
                    </div>
                    <div class="p-3 bg-danger-subtle text-danger rounded-4">
                        <i data-feather="alert-octagon" style="width: 28px; height: 28px;"></i>
                    </div>
                </div>
            </div>
        </div>
      </div>

      {{-- Keanggotaan --}}
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
            <h5 class="card-title mb-0 fw-bold d-flex align-items-center text-dark">
                <i data-feather="award" class="text-warning me-2" style="width: 18px;"></i> Pengaturan Membership & Level
            </h5>
        </div>
        <div class="card-body p-4">
           <form method="POST" action="{{ route('admin.umkm.updateLevel', $umkm->id) }}">
            @csrf @method('PUT')
            <div class="row align-items-center">
                <div class="col-md-7 mb-3 mb-md-0">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-warning text-white rounded-pill px-2 me-2" style="font-size: 10px;">LEVEL ACTIVE</span>
                        <h5 class="fw-bold mb-0 text-dark">{{ $umkm->level ? $umkm->level->nama_level : 'Status belum ditentukan' }}</h5>
                    </div>
                    <p class="text-muted small mb-0">Kode Akses: <code>{{ $umkm->level->kode ?? '-' }}</code> &bull; Penyesuaian ini berdampak pada fitur & iuran bulanan.</p>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <select name="level_id" class="form-select border-0 bg-light rounded-start-3 shadow-none py-2 h-100">
                          @foreach($levels as $lv)
                            <option value="{{ $lv->id }}" {{ $umkm->level_id == $lv->id ? 'selected' : '' }}>
                              {{ $lv->nama_level }}
                            </option>
                          @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary px-3 fw-bold rounded-end-3" style="font-size: 12px;">SIMPAN PERUBAHAN</button>
                    </div>
                </div>
            </div>
           </form>
        </div>
      </div>

      {{-- Iuran Table --}}
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden border-top border-4 border-primary">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center">
                <i data-feather="file-text" class="text-primary me-2" style="width: 18px;"></i> Riwayat Penagihan Terakhir
            </h5>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0 table-borderless align-middle">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Periode</th>
                <th>Nominal</th>
                <th>Status Pembayaran</th>
                <th class="pe-4 text-end">Aksi Admin</th>
              </tr>
            </thead>
            <tbody>
              @forelse($iuranList as $iuran)
                <tr class="border-bottom-faint">
                  <td class="ps-4 fw-bold text-dark">{{ \Carbon\Carbon::createFromFormat('Y-m', $iuran->periode)->isoFormat('MMMM Y') }}</td>
                  <td class="fw-bold">{{ rupiah($iuran->nominal) }}</td>
                  <td>
                    @if($iuran->status === 'lunas')
                      <span class="badge bg-success-subtle text-success rounded-pill px-3">Lunas</span>
                    @else
                      <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Tertunda</span>
                    @endif
                  </td>
                  <td class="pe-4 text-end">
                    @if($iuran->status !== 'lunas')
                      <form method="POST" action="{{ route('admin.umkm.konfirmasiIuran', $umkm->id) }}">
                        @csrf
                        <input type="hidden" name="iuran_id" value="{{ $iuran->id }}">
                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-1 shadow-sm border-0 fw-bold" style="font-size: 10px;">SELESAIKAN TAGIHAN</button>
                      </form>
                    @else
                      <div class="text-muted italic" style="font-size: 10px;">
                        <i data-feather="check-circle" class="me-1 text-success" style="width: 12px;"></i> Terverifikasi {{ $iuran->dibayar_pada?->format('d/m/y') }}
                      </div>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-muted text-center py-5">Record penagihan belum tersedia.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

</div>

<style>
    .x-small { font-size: 0.72rem; }
    .bg-primary-subtle { background-color: rgba(59, 125, 221, 0.1) !important; }
    .bg-success-subtle { background-color: rgba(28, 187, 140, 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }
    .bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1) !important; }
    .bg-info-subtle { background-color: rgba(23, 162, 184, 0.1) !important; }
    .text-primary { color: #3b7ddd !important; }
    .text-success { color: #1cbb8c !important; }
    .text-danger { color: #dc3545 !important; }
    .text-secondary { color: #6c757d !important; }
    .border-bottom-faint { border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important; }
    .italic { font-style: italic; }
</style>
@endsection
