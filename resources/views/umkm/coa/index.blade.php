@extends('layouts.umkm')
@section('title', 'Chart of Accounts (COA)')

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
    cursor: pointer; transition: all .2s; border: none;
}
.btn-action-delete:hover { background: #fee2e2; border-color: #fca5a5; color: #dc2626; border: 1px solid #fca5a5; }

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
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Chart of Accounts <strong>(COA)</strong></h1>
        <p class="text-muted mb-0" style="font-size: .85rem;">Manajemen daftar akun untuk pencatatan jurnal umum keuangan Anda.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3 mb-4" style="font-size:.85rem; border: none; background: #f0fdf4; color: #166534;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger rounded-3 mb-4" style="font-size:.85rem; border: none;">
        {{ session('error') }}
    </div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
    <div class="card-header bg-white border-bottom p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            {{-- Filter & Search --}}
            <form action="{{ route('umkm.coa.index') }}" method="GET" class="d-flex align-items-center gap-2">
                @if(request('filter_header'))
                    <input type="hidden" name="filter_header" value="{{ request('filter_header') }}">
                @endif
                <div style="position:relative; width: 250px;">
                    <i data-feather="search" style="width:16px;height:16px;position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari kode atau nama akun..." value="{{ request('search') }}" style="padding-left:36px !important; border-radius:8px; border:1px solid #e2e8f0;">
                </div>
                
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 shadow-sm" type="button" data-bs-toggle="dropdown" style="border-radius: 8px; border: 1px solid #e2e8f0; height: 38px; padding: 0 1rem; background: #fff;" title="Filter Kategori">
                        <i data-feather="filter" style="width: 16px; height: 16px; color: {{ request('filter_header') ? '#2563eb' : '#475569' }};"></i>
                        <span style="font-size: 0.85rem; font-weight: 500; color: {{ request('filter_header') ? '#2563eb' : '#475569' }};">Filter</span>
                    </button>
                    <ul class="dropdown-menu shadow border-0" style="border-radius: 10px; font-size: 0.85rem; padding: 0.5rem; min-width: 160px; margin-top: 5px;">
                        <li><h6 class="dropdown-header text-uppercase fw-bold" style="font-size: 0.65rem; color: #94a3b8; letter-spacing: 0.5px;">Filter Berdasarkan</h6></li>
                        <li><a class="dropdown-item rounded-2 py-2 mb-1 {{ request('filter_header') == '' ? 'bg-primary text-white fw-medium' : '' }}" href="{{ route('umkm.coa.index', ['search' => request('search')]) }}">Semua Header</a></li>
                        <li><a class="dropdown-item rounded-2 py-2 mb-1 {{ request('filter_header') == '1' ? 'bg-primary text-white fw-medium' : '' }}" href="{{ route('umkm.coa.index', ['filter_header' => '1', 'search' => request('search')]) }}">1 - Aset</a></li>
                        <li><a class="dropdown-item rounded-2 py-2 mb-1 {{ request('filter_header') == '2' ? 'bg-primary text-white fw-medium' : '' }}" href="{{ route('umkm.coa.index', ['filter_header' => '2', 'search' => request('search')]) }}">2 - Kewajiban</a></li>
                        <li><a class="dropdown-item rounded-2 py-2 mb-1 {{ request('filter_header') == '3' ? 'bg-primary text-white fw-medium' : '' }}" href="{{ route('umkm.coa.index', ['filter_header' => '3', 'search' => request('search')]) }}">3 - Modal</a></li>
                        <li><a class="dropdown-item rounded-2 py-2 mb-1 {{ request('filter_header') == '4' ? 'bg-primary text-white fw-medium' : '' }}" href="{{ route('umkm.coa.index', ['filter_header' => '4', 'search' => request('search')]) }}">4 - Pendapatan</a></li>
                        <li><a class="dropdown-item rounded-2 py-2 {{ request('filter_header') == '5' ? 'bg-primary text-white fw-medium' : '' }}" href="{{ route('umkm.coa.index', ['filter_header' => '5', 'search' => request('search')]) }}">5 - Beban</a></li>
                    </ul>
                </div>
            </form>

            {{-- Action --}}
            <a href="{{ route('umkm.coa.create') }}" class="btn btn-primary shadow-sm" style="border-radius:8px; padding: 0.5rem 1.25rem; font-size: 0.85rem; font-weight:500;">
                <i data-feather="plus" style="width:14px;height:14px; margin-right:6px; margin-bottom: 2px;"></i> Tambah Akun
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-borderless table-premium mb-0" style="min-width: 800px;">
            <thead>
                <tr>
                    <th style="width: 120px;">Header</th>
                    <th style="width: 100px;">Kode</th>
                    <th>Nama Akun</th>
                    <th style="width: 140px;">Posisi Normal</th>
                    <th class="text-end" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $r)
                    <tr>
                        <td>
                            @php
                                $mapHeader = [
                                    '1' => 'Aset',
                                    '2' => 'Kewajiban',
                                    '3' => 'Modal',
                                    '4' => 'Pendapatan',
                                    '5' => 'Beban'
                                ];
                            @endphp
                            <strong class="text-dark" style="font-size: 0.85rem;">{{ $mapHeader[$r->header_akun] ?? $r->header_akun }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size: 0.8rem; font-family: monospace;">{{ $r->kode_akun }}</span>
                        </td>
                        <td>
                            <span style="font-size: 0.9rem; color: #334155;">{{ $r->nama_akun }}</span>
                        </td>
                        <td>
                            @if($r->posisi_dr_cr == 'Debit')
                                <span style="background: #eff6ff; color: #2563eb; font-size: 0.72rem; font-weight: 600; padding: 0.35rem 0.6rem; border-radius: 6px; display: inline-block;">Debit</span>
                            @else
                                <span style="background: #fdf4ff; color: #c026d3; font-size: 0.72rem; font-weight: 600; padding: 0.35rem 0.6rem; border-radius: 6px; display: inline-block;">Kredit</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="{{ route('umkm.coa.edit', $r->id) }}" class="btn-action-edit" title="Edit COA">
                                    <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
                                </a>
                                <button type="button" 
                                        class="btn-action-delete" 
                                        title="Hapus" 
                                        onclick="confirmDeleteCoa('{{ $r->id }}', '{{ addslashes($r->nama_akun) }}')">
                                    <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                                </button>
                                <form id="form-delete-{{ $r->id }}" 
                                      action="{{ route('umkm.coa.destroy', $r->id) }}" 
                                      method="POST" 
                                      class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="mb-3">
                                <div style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                                    <i data-feather="search" style="width: 28px; height: 28px; color: #94a3b8;"></i>
                                </div>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">COA Tidak Ditemukan</h6>
                            <p class="text-muted small mb-4">Tidak ada data akun yang sesuai dengan pencarian atau filter Anda.</p>
                            @if(request('search') || request('filter_header'))
                                <a href="{{ route('umkm.coa.index') }}" class="btn btn-outline-primary shadow-sm" style="border-radius: 8px;">
                                    Reset Cari & Filter
                                </a>
                            @else
                                <a href="{{ route('umkm.coa.create') }}" class="btn btn-primary shadow-sm" style="border-radius: 8px;">
                                    <i data-feather="plus" style="width:14px;height:14px; margin-right:4px;"></i> Tambah Akun
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($data->hasPages())
    <div class="mt-4">{{ $data->links() }}</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();
});

function confirmDeleteCoa(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus Chart of Account (COA): <strong>${nama}</strong>?<br><br>
               <span style="font-size:0.85rem; color:#dc3545;">
               Peringatan: Jika akun ini sudah digunakan di jurnal manapun, laporan bisa menjadi tidak balance.
               </span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-delete-' + id).submit();
        }
    });
}
</script>
@endpush
