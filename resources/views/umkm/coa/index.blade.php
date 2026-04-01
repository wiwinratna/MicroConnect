@extends('layouts.umkm')
@section('title','COA')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Chart of Accounts (COA)</h3>
  <a href="{{ route('umkm.coa.create') }}" class="btn btn-primary">+ Tambah Akun</a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
  <div class="card-body">
    <table class="table align-middle table-hover table-borderless">
      <thead class="table-light">
        <tr>
          <th>Header</th>
          <th>Kode</th>
          <th>Nama Akun</th>
          <th>Posisi</th>
          <th width="140">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($data as $r)
          <tr>
            <td>{{ $r->header_akun }}</td>
            <td>{{ $r->kode_akun }}</td>
            <td>{{ $r->nama_akun }}</td>
            <td>{{ $r->posisi_dr_cr }}</td>
            <td class="d-flex gap-1">
              <a class="btn btn-sm btn-outline-primary" href="{{ route('umkm.coa.edit',$r->id) }}">Edit</a>
              <button type="button" 
                      class="btn btn-sm btn-action btn-action-delete" 
                      title="Hapus" 
                      onclick="confirmDeleteCoa('{{ $r->id }}', '{{ addslashes($r->nama_akun) }}')">
                  <i data-feather="trash-2"></i> Hapus
              </button>
              <form id="form-delete-{{ $r->id }}" 
                    action="{{ route('umkm.coa.destroy', $r->id) }}" 
                    method="POST" 
                    class="d-none">
                @csrf @method('DELETE')
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted">Belum ada COA.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteCoa(id, nama) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus Chart of Account (COA): <strong>${nama}</strong>?<br><br>
               <span style="font-size:0.85rem; color:#dc3545;">
               Peringatan: Jika akun ini sudah digunakan di jurnal manapun, sistem bisa menyebabkan laporan tidak balance.
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
