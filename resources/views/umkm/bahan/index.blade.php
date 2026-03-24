@extends('layouts.umkm')

@section('title', 'Bahan Baku')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><strong>Bahan</strong> Baku</h1>
    <a href="{{ route('umkm.bahan.create') }}" class="btn btn-primary">
        <i data-feather="plus" style="width:16px;height:16px;"></i> Tambah Bahan
    </a>
</div>

{{-- Toast Notifications --}}
@if(session('success'))
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
    <div class="toast align-items-center text-bg-success border-0 show" role="alert" id="toastSuccess">
        <div class="d-flex">
            <div class="toast-body fw-medium">
                <i data-feather="check-circle" style="width:16px;height:16px;"></i>
                {{ session('success') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif

@if(session('warning'))
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
    <div class="toast align-items-center text-bg-warning border-0 show" role="alert" id="toastWarning">
        <div class="d-flex">
            <div class="toast-body fw-medium text-dark">
                <i data-feather="alert-triangle" style="width:16px;height:16px;"></i>
                {{ session('warning') }}
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;">
    <div class="toast align-items-center text-bg-danger border-0 show" role="alert" id="toastError">
        <div class="d-flex">
            <div class="toast-body fw-medium">
                <i data-feather="x-circle" style="width:16px;height:16px;"></i>
                {{ session('error') }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body pb-2">
        <form method="GET" action="{{ route('umkm.bahan.index') }}" id="search-form" class="d-flex gap-2">
            <div class="input-group" style="max-width:400px;">
                <span class="input-group-text bg-white border-end-0">
                    <i data-feather="search" style="width:16px;height:16px;color:#999;"></i>
                </span>
                <input type="text"
                       name="q"
                       id="search-bahan"
                       class="form-control border-start-0 ps-0"
                       placeholder="Cari nama bahan atau kode..."
                       value="{{ $q ?? '' }}"
                       autocomplete="off">
                @if($q ?? false)
                    <a href="{{ route('umkm.bahan.index') }}" class="btn btn-outline-secondary">
                        <i data-feather="x" style="width:14px;height:14px;"></i>
                    </a>
                @endif
            </div>
            <button type="submit" class="btn btn-outline-primary">Cari</button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table mb-0 table-hover table-borderless align-middle">
            <thead class="table-light">
            <tr>
                <th class="ps-3">Kode</th>
                <th>Nama Bahan</th>
                <th>Satuan</th>
                <th class="text-end">Stok Awal</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th width="160" class="text-center">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($bahan as $item)
                <tr class="{{ $item->is_archived ? 'opacity-50' : '' }}">
                    <td class="ps-3"><code class="small">{{ $item->kode_bahan }}</code></td>
                    <td class="fw-medium">{{ $item->nama_bahan }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td class="text-end fw-medium">{{ format_angka($item->stok_awal) }}</td>
                    <td class="text-muted small">{{ $item->keterangan ?? '-' }}</td>
                    <td>
                        @if($item->is_archived)
                            <span class="badge bg-secondary">Tidak Aktif</span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success border-opacity-25">Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex justify-content-center gap-1 flex-nowrap">
                            <a href="{{ route('umkm.bahan.edit', $item->id) }}"
                               class="btn btn-sm btn-action btn-action-edit" title="Edit">
                                <i data-feather="edit-2" style="width:14px;height:14px;"></i> Edit
                            </a>
                            @if(!$item->is_archived)
                            <button type="button"
                                    class="btn btn-sm btn-action btn-action-delete"
                                    title="Hapus"
                                    onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->nama_bahan) }}')">
                                <i data-feather="trash-2" style="width:14px;height:14px;"></i> Hapus
                            </button>
                            @else
                            <form action="{{ route('umkm.bahan.destroy', $item->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-secondary" title="Hapus Permanen"
                                        onclick="return confirm('Hapus permanen bahan \'{{ addslashes($item->nama_bahan) }}\' dari sistem?')">
                                    <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        @if($q ?? false)
                            Tidak ada bahan baku yang cocok dengan "<strong>{{ $q }}</strong>".
                            <a href="{{ route('umkm.bahan.index') }}" class="d-block mt-2">Tampilkan semua</a>
                        @else
                            Belum ada bahan baku. <a href="{{ route('umkm.bahan.create') }}">Tambah sekarang</a>.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
{{-- Form hidden untuk eksekusi delete via SweetAlert --}}
<form id="formHapusBahan" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Auto-hide toasts after 4 seconds
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toast').forEach(function(el) {
        setTimeout(() => { el.classList.remove('show'); }, 4000);
    });

    // Re-init feather icons for badges
    if (typeof feather !== 'undefined') feather.replace();
});

function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus bahan baku <strong>${nama}</strong>?<br><br>
               <span style="font-size:0.85rem; color:#6c757d;">
               Jika bahan ini sudah pernah digunakan dalam transaksi, statusnya otomatis menjadi "Tidak Aktif" (arsip) untuk menjaga integritas histori jura.
               </span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('formHapusBahan');
            form.action = '/umkm/bahan-baku/' + id;
            form.submit();
        }
    });
}
</script>
@endpush
