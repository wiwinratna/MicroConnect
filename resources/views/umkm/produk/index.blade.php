@extends('layouts.umkm')

@section('title', 'Produk Jadi')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1"><strong>Produk</strong> Jadi</h1>
            <p class="text-muted mb-0">
                Daftar produk yang dijual oleh usaha kamu.
            </p>
        </div>
        <a href="{{ route('umkm.produk.create') }}" class="btn btn-primary">
            + Tambah Produk
        </a>
    </div>

    

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @if($data->isEmpty())
                <p class="text-muted text-center mb-0">
                    Belum ada produk. Klik <strong>+ Tambah Produk</strong> untuk menambahkan.
                </p>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover table-borderless">
                        <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Satuan</th>
                            <th class="text-end">Stok</th>
                            <th class="text-end">HPP (Rp)</th>
                            <th class="text-end">Harga Jual (Rp)</th>
                            <th width="130" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($data as $p)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($p->foto_path)
                                            <img src="{{ asset('storage/'.$p->foto_path) }}"
                                                 alt="foto"
                                                 class="rounded me-2"
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="rounded bg-light d-flex justify-content-center align-items-center me-2"
                                                 style="width: 40px; height: 40px;">
                                                <i data-feather="image" class="text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $p->nama_produk }}</div>
                                            <div class="small text-muted">{{ $p->kode_produk }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $p->satuan }}</td>
                                <td  class="text-end fw-medium">{{ format_angka($p->stok) }}</td>
                                <td  class="text-end fw-medium">{{ rupiah($p->harga_pokok) }}
                                </td>
                                <td  class="text-end fw-medium">{{ rupiah($p->harga_jual) }}
                                </td>
                                <td class="text-center">
                                  <div class="d-flex justify-content-center gap-2 flex-nowrap">
                                    <a href="{{ route('umkm.produk.edit', $p->id) }}" class="btn btn-sm btn-action btn-action-edit" title="Edit"><i data-feather="edit-2"></i> Edit</a>
                                    <button type="button" 
                                            class="btn btn-sm btn-action btn-action-delete" 
                                            title="Hapus"
                                            onclick="confirmDeleteProduk('{{ $p->id }}', '{{ addslashes($p->nama_produk) }}')">
                                        <i data-feather="trash-2"></i> Hapus
                                    </button>
                                    <form id="form-delete-{{ $p->id }}" 
                                          action="{{ route('umkm.produk.destroy', $p->id) }}"
                                          method="POST"
                                          class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                  </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteProduk(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus produk <strong>${nama}</strong>?<br><br>
               <span style="font-size:0.85rem; color:#6c757d;">
               Menghapus produk juga akan menghapus data resep (komposisi bahan) yang terkait.
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
