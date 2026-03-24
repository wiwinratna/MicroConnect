@extends('layouts.admin')

@section('title', 'Daftar Tiket Pengaduan UMKM')

@section('content')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Ticketing UMKM</strong> (Pengaduan & Konsultasi)</h1>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="alert-message">{{ session('success') }}</div>
        </div>
        @endif

        <div class="card">
            <div class="card-header bg-light">
                <form action="{{ route('admin.tickets.index') }}" method="GET" class="row gx-2">
                    <div class="col-md-3">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="Open" {{ request('status') === 'Open' ? 'selected' : '' }}>Open</option>
                            <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Resolved" {{ request('status') === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="kategori" class="form-select form-select-sm">
                            <option value="">-- Semua Kategori --</option>
                            <option value="Kendala Penggunaan Sistem" {{ request('kategori') == 'Kendala Penggunaan Sistem' ? 'selected' : '' }}>Kendala Penggunaan Sistem</option>
                            <option value="Kendala Pencatatan Transaksi" {{ request('kategori') == 'Kendala Pencatatan Transaksi' ? 'selected' : '' }}>Kendala Pencatatan Transaksi</option>
                            <option value="Kendala Laporan Keuangan" {{ request('kategori') == 'Kendala Laporan Keuangan' ? 'selected' : '' }}>Kendala Laporan Keuangan</option>
                            <option value="Kendala Akun / Login" {{ request('kategori') == 'Kendala Akun / Login' ? 'selected' : '' }}>Kendala Akun / Login</option>
                            <option value="Kendala Iuran" {{ request('kategori') == 'Kendala Iuran' ? 'selected' : '' }}>Kendala Iuran</option>
                            <option value="Konsultasi Usaha" {{ request('kategori') == 'Konsultasi Usaha' ? 'selected' : '' }}>Konsultasi Usaha</option>
                            <option value="Lainnya" {{ request('kategori') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.tickets.index') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-borderless align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No. Tiket</th>
                                <th>UMKM</th>
                                <th>Tanggal Update</th>
                                <th>Kategori</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $t)
                            <tr>
                                <td class="fw-bold">{{ $t->kode_ticket }}</td>
                                <td>{{ $t->umkm->nama_perusahaan ?? '-' }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($t->updated_at)->format('d/m/Y H:i') }}
                                </td>
                                <td>{{ $t->kategori }}</td>
                                <td>{{ Str::limit($t->judul, 30) }}</td>
                                <td>
                                    @if($t->status === 'Open')
                                        <span class="badge bg-warning text-dark">Open</span>
                                    @elseif($t->status === 'In Progress')
                                        <span class="badge bg-info text-dark">In Progress</span>
                                    @else
                                        <span class="badge bg-success">Resolved</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.tickets.show', $t->id) }}" class="btn btn-sm btn-primary">
                                        Pantau / Balas &rarr;
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted">Belum ada tiket yang sesuai kriteria pencarian.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
