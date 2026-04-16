@extends('layouts.umkm')
@section('title', 'Data Pelanggan')

@push('styles')
<style>
/* ── Table & Actions ── */
.btn-action-edit {
    width: 32px; height: 32px; border-radius: 8px;
    background: #f8fafc; border: 1px solid #e2e8f0; color: #64748b;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .2s;
}
.btn-action-edit:hover { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }

.btn-action-delete {
    width: 32px; height: 32px; border-radius: 8px;
    background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .2s;
}
.btn-action-delete:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; }

/* ── Pelanggan UI ── */
.avatar-circle {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    color: #475569; font-weight: 700; font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; border: 1px solid #cbd5e1;
}

.table-premium th {
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.05em; color: #94a3b8; border-bottom: 1px solid #e2e8f0 !important;
    padding: 1rem 1.25rem; background: #fafbfc;
}
.table-premium td {
    padding: 1rem 1.25rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle;
}
.table-premium tr:last-child td { border-bottom: none; }
.table-premium tr:hover td { background: #f8fafc; }

.contact-item {
    display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: #475569; margin-bottom: 3px;
}
.contact-item:last-child { margin-bottom: 0; }
.contact-item i { width: 12px; height: 12px; color: #94a3b8; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Data <strong>Pelanggan</strong></h1>
        <p class="text-muted mb-0" style="font-size: .85rem;">Daftar pelanggan terdaftar untuk pencatatan piutang dan invoice.</p>
    </div>
    <div class="d-flex align-items-center">
        <a href="{{ route('umkm.piutang.index') }}" 
           class="btn btn-outline-secondary btn-sm"
           style="border-radius:6px; font-weight:500; display:flex; align-items:center; gap:6px; padding: 0.4rem 0.8rem;">
            <i data-feather="arrow-left" style="width:14px;height:14px;"></i> Kembali ke Piutang
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3 mb-4" style="font-size:.85rem; border: none; background: #f0fdf4; color: #166534;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger rounded-3 mb-4" style="font-size:.85rem; border: none;">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
        <form action="{{ route('umkm.etalase.pelanggan.index') }}" method="GET" style="position:relative; width: 300px;">
            <i data-feather="search" style="width:16px;height:16px;position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;"></i>
            <input type="text" name="search" class="form-control" placeholder="Cari nama, alamat, no WA..." value="{{ request('search') }}" style="padding-left:36px !important; border-radius:8px; border:1px solid #e2e8f0; border-radius: 8px;">
        </form>
        <a href="{{ route('umkm.etalase.pelanggan.create') }}" class="btn btn-primary shadow-sm" style="border-radius:8px; padding: 0.5rem 1.25rem; font-size: 0.85rem; font-weight:500;">
            <i data-feather="plus" style="width:14px;height:14px; margin-right:6px; margin-bottom: 2px;"></i> Tambah Pelanggan
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-borderless table-premium mb-0" style="min-width: 800px;">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Nama Pelanggan</th>
                    <th style="width: 250px;">Kontak & Info</th>
                    <th style="width: 160px;">Total Piutang</th>
                    <th style="width: 140px;">Status</th>
                    <th class="text-end" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelanggan as $p)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-circle">
                                    {{ strtoupper(substr($p->nama_pelanggan, 0, 1)) }}
                                </div>
                                <div class="d-flex flex-column">
                                    <strong class="text-dark" style="font-size: 0.95rem;">
                                        {{ $p->nama_pelanggan }}
                                    </strong>
                                    
                                    @if($p->nama_instansi)
                                        <span class="text-primary" style="font-size: 0.78rem; font-weight: 500; margin-top: 1px;">
                                            {{ $p->nama_instansi }}
                                        </span>
                                    @elseif($p->alamat)
                                        <span class="text-muted" style="font-size: 0.78rem; max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;" title="{{ $p->alamat }}">
                                            {{ $p->alamat }}
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 0.75rem; font-style: italic; margin-top: 1px;">Pelanggan Reguler</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                @if($p->no_whatsapp)
                                    <div class="contact-item">
                                        <i data-feather="phone"></i> 
                                        <span>{{ $p->no_whatsapp }}</span>
                                    </div>
                                @endif
                                @if($p->email)
                                    <div class="contact-item">
                                        <i data-feather="mail"></i> 
                                        <span>{{ $p->email }}</span>
                                    </div>
                                @endif
                                @if(!$p->no_whatsapp && !$p->email)
                                    <span class="text-muted small fst-italic">Belum ada kontak</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php $total = $p->totalPiutangAktif(); @endphp
                            <div class="d-flex flex-column">
                                @if($total > 0)
                                    <strong style="font-size: 0.9rem; color: #b91c1c;">
                                        {{ rupiah($total) }}
                                    </strong>
                                @else
                                    <span style="font-size: 0.9rem; color: #64748b; font-weight: 600;">
                                        Rp 0
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($total > 0)
                                <span style="background: #fef2f2; color: #b91c1c; font-size: 0.72rem; font-weight: 600; padding: 0.35rem 0.6rem; border-radius: 6px; display: inline-block;">
                                    Ada Piutang
                                </span>
                            @else
                                <span style="background: #f0fdf4; color: #15803d; font-size: 0.72rem; font-weight: 600; padding: 0.35rem 0.6rem; border-radius: 6px; display: inline-block;">
                                    Lunas / Bersih
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('umkm.etalase.pelanggan.edit', $p->id) }}" class="btn-action-edit" title="Edit Pelanggan">
                                    <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                </a>
                                <form method="POST" action="{{ route('umkm.etalase.pelanggan.destroy', $p->id) }}" onsubmit="return confirm('Hapus pelanggan ini beserta data hutangnya?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action-delete" title="Hapus">
                                        <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="mb-3">
                                <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                                    <i data-feather="users" style="width: 28px; height: 28px; color: #94a3b8;"></i>
                                </div>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Belum Ada Pelanggan</h6>
                            <p class="text-muted small mb-4" style="max-width: 300px; margin: 0 auto;">Daftar pelanggan Anda masih kosong. Tambahkan pelanggan pertama untuk mulai mencatat histori piutang secara terstruktur.</p>
                            <a href="{{ route('umkm.etalase.pelanggan.create') }}" class="btn btn-primary px-4 shadow-sm" style="border-radius: 8px;">
                                <i data-feather="plus" style="width:14px;height:14px; margin-right:4px;"></i> Tambah Pelanggan Pertama
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($pelanggan->hasPages())
    <div class="mt-4">{{ $pelanggan->links() }}</div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();
});
</script>
@endpush
