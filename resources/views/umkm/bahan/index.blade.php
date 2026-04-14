@extends('layouts.umkm')

@section('title', 'Bahan Baku')

@push('styles')
<style>
/* ── Page: full width, sidebar is the natural boundary ── */

/* ── Search bar ── */
.bb-search-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.65rem 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    margin-bottom: 0.875rem;
}
.bb-search-icon { color: #94a3b8; flex-shrink: 0; width: 15px; height: 15px; }
.bb-search-input {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    flex: 1;
    font-size: 0.8125rem;
    color: #334155;
    padding: 0 !important;
    background: transparent !important;
}
.bb-search-input::placeholder { color: #94a3b8; }
.bb-search-clear {
    color: #94a3b8;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
    display: flex; align-items: center;
    transition: color .15s;
}
.bb-search-clear:hover { color: #475569; }
.bb-search-btn {
    padding: 0.28rem 0.85rem;
    font-size: 0.775rem;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    font-weight: 500;
    cursor: pointer;
    transition: background .15s, border-color .15s;
    white-space: nowrap;
}
.bb-search-btn:hover { background: #f1f5f9; border-color: #cbd5e1; }

/* ── Table tweaks ── */
.kode-chip {
    font-family: 'SF Mono', 'Fira Code', monospace;
    font-size: 0.68rem;
    color: #64748b;
    background: #f1f5f9;
    border-radius: 5px;
    padding: 1px 7px;
    letter-spacing: 0.03em;
}
</style>
@endpush

@section('content')
<div>
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0"><strong>Bahan</strong> Baku</h1>
            <p class="text-muted mb-0" style="font-size:0.8rem; margin-top:2px;">Master data bahan baku & stok persediaan awal.</p>
        </div>
        <a href="{{ route('umkm.bahan.create') }}" class="btn btn-primary">
            <i data-feather="plus" style="width:13px;height:13px;margin-right:4px;"></i> Tambah Bahan
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('umkm.bahan.index') }}" id="search-form">
        <div class="bb-search-card">
            <i data-feather="search" class="bb-search-icon"></i>
            <input type="text" name="q" id="search-bahan" class="bb-search-input"
                   placeholder="Cari nama bahan atau kode..."
                   value="{{ $q ?? '' }}" autocomplete="off">
            @if($q ?? false)
                <a href="{{ route('umkm.bahan.index') }}" class="bb-search-clear">
                    <i data-feather="x" style="width:14px;height:14px;"></i>
                </a>
            @endif
            <button type="submit" class="bb-search-btn">Cari</button>
        </div>
    </form>

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 table-hover table-borderless align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:110px; padding-left:1.25rem;">Kode</th>
                            <th>Nama Bahan</th>
                            <th style="width:80px;">Satuan</th>
                            <th style="width:110px;" class="text-end">Stok Awal</th>
                            <th style="width:200px;">Keterangan</th>
                            <th style="width:90px;" class="text-center">Status</th>
                            <th style="width:140px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahan as $item)
                            <tr class="{{ $item->is_archived ? 'opacity-50' : '' }}">
                                <td style="padding-left:1.25rem;">
                                    <span class="kode-chip">{{ $item->kode_bahan }}</span>
                                </td>
                                <td class="fw-medium" style="color:#1e293b;">{{ $item->nama_bahan }}</td>
                                <td class="text-muted">{{ $item->satuan }}</td>
                                <td class="text-end fw-semibold" style="color:#1e293b;">
                                    {{ format_angka($item->stok_awal) }}
                                </td>
                                <td class="text-muted" style="
                                    max-width:200px;
                                    overflow:hidden;
                                    text-overflow:ellipsis;
                                    white-space:nowrap;
                                " title="{{ $item->keterangan }}">
                                    {{ $item->keterangan ?: '—' }}
                                </td>
                                <td class="text-center">
                                    @if($item->is_archived)
                                        <span class="badge bg-secondary" style="font-size:0.65rem;">Nonaktif</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success" style="font-size:0.65rem;">Aktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1 flex-nowrap">
                                        <a href="{{ route('umkm.bahan.edit', $item->id) }}"
                                           class="btn btn-sm btn-action btn-action-edit">
                                            <i data-feather="edit-2" style="width:12px;height:12px;"></i> Edit
                                        </a>
                                        @if(!$item->is_archived)
                                            <button type="button"
                                                    class="btn btn-sm btn-action btn-action-delete"
                                                    onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->nama_bahan) }}')">
                                                <i data-feather="trash-2" style="width:12px;height:12px;"></i> Hapus
                                            </button>
                                        @else
                                            <form action="{{ route('umkm.bahan.destroy', $item->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-action" style="color:#94a3b8;"
                                                        onclick="return confirm('Hapus permanen bahan \'{{ addslashes($item->nama_bahan) }}\' dari sistem?')"
                                                        title="Hapus Permanen">
                                                    <i data-feather="trash-2" style="width:12px;height:12px;"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    @if($q ?? false)
                                        <p class="text-muted mb-1">Tidak ada bahan baku yang cocok dengan "<strong>{{ $q }}</strong>".</p>
                                        <a href="{{ route('umkm.bahan.index') }}" class="btn btn-sm btn-outline-secondary mt-1">Tampilkan semua</a>
                                    @else
                                        <p class="text-muted mb-1">Belum ada bahan baku.</p>
                                        <a href="{{ route('umkm.bahan.create') }}" class="btn btn-sm btn-primary mt-1">+ Tambah Sekarang</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Hidden delete form --}}
<form id="formHapusBahan" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();

    // Auto-submit search on Enter (already done by form submit)
    const searchInput = document.getElementById('search-bahan');
    if (searchInput) searchInput.focus();
});

function confirmDelete(id, nama) {
    Swal.fire({
        title: 'Hapus Bahan Baku?',
        html: `Anda yakin ingin menghapus <strong>${nama}</strong>?<br>
               <span style="font-size:0.78rem;color:#64748b;margin-top:4px;display:block;">
               Jika bahan sudah digunakan dalam transaksi, statusnya akan menjadi Nonaktif (tersimpan di histori).
               </span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-4' }
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
