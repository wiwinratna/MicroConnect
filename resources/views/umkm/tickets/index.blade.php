@extends('layouts.umkm')
@section('title', 'Daftar Tiket Pengaduan')

@push('styles')
<style>
    /* ── Premium Table Integration ── */
    .table-premium th { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; border-bottom: 1px solid #e2e8f0 !important; padding: 1rem 1.25rem; background: #fafbfc; }
    .table-premium td { padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .table-premium tr:last-child td { border-bottom: none; }
    .table-premium tr:hover td { background: #f8fafc; }

    /* ── High-Contrast Status Badges ── */
    .status-badge { 
        font-size: 0.65rem; 
        font-weight: 800; 
        padding: 4px 10px; 
        border-radius: 50px; 
        text-transform: uppercase;
        letter-spacing: 0.02em;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .status-badge i { font-size: 8px; }
    
    .badge-open { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-progress { background-color: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }
    .badge-resolved { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
</style>
@endpush

@section('content')
{{-- Header Area - CLEAN --}}
<div class="mb-4">
    <h1 class="h3 mb-1"><strong>Pengaduan & Konsultasi</strong></h1>
    <p class="text-muted mb-0" style="font-size: 0.85rem;">Hubungi tim KADIN untuk melaporkan kendala atau konsultasi bisnis.</p>
</div>

<div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
    {{-- Card Header with Actions --}}
    <div class="card-header bg-white border-bottom px-4 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="font-size: 0.95rem;">Daftar Tiket Aktif</h5>
            <a href="{{ route('umkm.tickets.create') }}" class="btn btn-primary btn-sm shadow-sm" style="border-radius:8px; padding: 0.45rem 1rem; font-size: 0.8125rem; font-weight: 600;">
                <i data-feather="plus" style="width:14px; height:14px; margin-right:4px;"></i> Buat Tiket Baru
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0 table-borderless table-premium">
                <thead>
                    <tr>
                        <th class="ps-4">No. Tiket</th>
                        <th>Tanggal Masuk</th>
                        <th>Kategori</th>
                        <th>Judul Kendala</th>
                        <th>Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                    <tr>
                        <td class="ps-4">
                            <span class="fw-bold text-primary" style="font-family: 'Courier Prime', monospace; letter-spacing: 1px;">{{ $t->kode_ticket }}</span>
                        </td>
                        <td>
                            <div class="text-dark fw-semibold small">{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y') }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($t->created_at)->format('H:i') }} WIB</div>
                        </td>
                        <td>
                            <span class="text-muted small fw-medium">{{ $t->kategori }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark small" style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $t->judul }}
                            </div>
                        </td>
                        <td>
                            @if($t->status === 'Open')
                                <span class="status-badge badge-open"><i class="fas fa-circle"></i> Open</span>
                            @elseif($t->status === 'In Progress')
                                <span class="status-badge badge-progress"><i class="fas fa-circle"></i> In Progress</span>
                            @else
                                <span class="status-badge badge-resolved"><i class="fas fa-circle"></i> Resolved</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <a href="{{ route('umkm.tickets.show', $t->id) }}" class="btn btn-sm btn-action btn-action-view" style="font-size: 0.75rem; border-radius: 6px;">
                                <i data-feather="message-square" style="width:12px; height:12px; margin-right:4px;"></i> Detail Percakapan
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="mb-3">
                                <div style="width: 48px; height: 48px; background: #f1f5f9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto;">
                                    <i data-feather="message-circle" style="width: 24px; height: 24px; color: #94a3b8;"></i>
                                </div>
                            </div>
                            <h6 class="text-dark fw-bold mb-1">Daftar Tiket Kosong</h6>
                            <p class="text-muted mb-0 small">Anda belum melakukan pengaduan atau konsultasi.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 p-3 border border-dashed rounded-3 bg-light d-flex align-items-center gap-2">
    <i data-feather="info" class="text-muted" style="width:16px; height:16px;"></i>
    <p class="text-muted x-small mb-0">Tiket pengaduan akan segera direspon oleh tim KADIN pada jam kerja operasional.</p>
</div>

@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if(typeof feather !== 'undefined') feather.replace();
    })
</script>
@endpush
