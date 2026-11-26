@extends('layouts.admin')

@section('title', 'Daftar Tiket Pengaduan UMKM')

@section('content')
<div>
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Ticketing UMKM</strong> (Pengaduan & Konsultasi)</h1>

        

        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white py-4 border-bottom">
                <form action="{{ route('admin.tickets.index') }}" method="GET">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        
                        {{-- Status Tabs --}}
                        <div class="btn-group bg-light p-1 rounded-4" style="border: 1px solid var(--mn-border-color);">
                            <button type="submit" name="status" value="" class="btn btn-sm rounded-3 px-3 {{ request('status') == '' ? 'bg-white shadow-sm fw-bold border text-primary' : 'border-0 text-muted' }}">Semua</button>
                            <button type="submit" name="status" value="Open" class="btn btn-sm rounded-3 px-3 {{ request('status') == 'Open' ? 'bg-white shadow-sm fw-bold border text-warning' : 'border-0 text-muted' }}">Menunggu</button>
                            <button type="submit" name="status" value="In Progress" class="btn btn-sm rounded-3 px-3 {{ request('status') == 'In Progress' ? 'bg-white shadow-sm fw-bold border text-info' : 'border-0 text-muted' }}">Diproses</button>
                            <button type="submit" name="status" value="Resolved" class="btn btn-sm rounded-3 px-3 {{ request('status') == 'Resolved' ? 'bg-white shadow-sm fw-bold border text-success' : 'border-0 text-muted' }}">Selesai</button>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end">
                            {{-- Minimalist Category Dropdown --}}
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <span class="input-group-text bg-light border-0 rounded-start-3"><i data-feather="tag" style="width: 14px;"></i></span>
                                <select name="kategori" class="form-select form-select-sm border-0 bg-light rounded-end-3 shadow-none" onchange="this.form.submit()">
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="Kendala Penggunaan Sistem" {{ request('kategori') == 'Kendala Penggunaan Sistem' ? 'selected' : '' }}>Sistem</option>
                                    <option value="Kendala Pencatatan Transaksi" {{ request('kategori') == 'Kendala Pencatatan Transaksi' ? 'selected' : '' }}>Transaksi</option>
                                    <option value="Kendala Laporan Keuangan" {{ request('kategori') == 'Kendala Laporan Keuangan' ? 'selected' : '' }}>Laporan</option>
                                    <option value="Kendala Akun / Login" {{ request('kategori') == 'Kendala Akun / Login' ? 'selected' : '' }}>Akun</option>
                                    <option value="Kendala Iuran" {{ request('kategori') == 'Kendala Iuran' ? 'selected' : '' }}>Iuran</option>
                                    <option value="Konsultasi Usaha" {{ request('kategori') == 'Konsultasi Usaha' ? 'selected' : '' }}>Konsultasi</option>
                                    <option value="Lainnya" {{ request('kategori') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>

                            @if(request()->anyFilled(['status', 'kategori']))
                                <a href="{{ route('admin.tickets.index') }}" class="btn btn-light btn-sm rounded-3 border-0" title="Reset">
                                    <i data-feather="x-circle" style="width: 16px;"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-borderless align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nomor Tiket</th>
                                <th>Kendala / UMKM</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $t)
                            <tr class="border-bottom-faint">
                                <td class="ps-4" style="white-space: nowrap;">
                                    <span class="fw-bold text-primary">#{{ $t->kode_ticket }}</span>
                                    <div class="text-muted x-small mt-1">{{ \Carbon\Carbon::parse($t->updated_at)->format('d/m/y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-0">{{ $t->umkm->nama_usaha ?? '-' }}</div>
                                    <div class="text-dark small small-text mb-1">{{ Str::limit($t->judul, 45) }}</div>
                                    <span class="badge bg-light text-muted border fw-normal" style="font-size: 9px;">
                                        {{ $t->kategori }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($t->status === 'Open')
                                        <span class="badge bg-warning-subtle text-warning rounded-pill px-2" style="font-size: 10px;">Menunggu</span>
                                    @elseif($t->status === 'In Progress')
                                        <span class="badge bg-info-subtle text-info rounded-pill px-2" style="font-size: 10px;">Diproses</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2" style="font-size: 10px;">Selesai</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.tickets.show', $t->id) }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill" style="font-size: 11px;">
                                        Lihat & Balas
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-5">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .x-small { font-size: 0.7rem; }
    .bg-primary-subtle { background-color: rgba(23, 107, 101, 0.1) !important; }
    .bg-success-subtle { background-color: rgba(22, 107, 76, 0.1) !important; }
    .bg-warning-subtle { background-color: rgba(133, 87, 0, 0.1) !important; }
    .bg-danger-subtle { background-color: rgba(174, 42, 32, 0.1) !important; }
    .bg-secondary-subtle { background-color: rgba(95, 112, 108, 0.1) !important; }
    .bg-info-subtle { background-color: rgba(29, 100, 120, 0.1) !important; }
    .text-primary { color: var(--mn-primary) !important; }
    .text-success { color: var(--mn-success-text) !important; }
    .text-warning { color: var(--mn-warning-text) !important; }
    .text-info { color: var(--mn-info-text) !important; }
    .border-bottom-faint { border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important; }
</style>
@endsection
