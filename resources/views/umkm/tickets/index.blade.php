@extends('layouts.umkm')

@section('title', 'Daftar Tiket Pengaduan')

@section('content')
<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-3">
            <div class="col-auto">
                <h1 class="h3 mb-1"><strong>Pengaduan & Konsultasi</strong></h1>
                <p class="text-muted mb-0">Hubungi tim KADIN untuk melaporkan kendala atau konsultasi bisnis.</p>
            </div>
            <div class="col-auto ms-auto text-end mt-n1">
                <a href="{{ route('umkm.tickets.create') }}" class="btn btn-primary">
                    + Buat Tiket Baru
                </a>
            </div>
        </div>

        

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 table-borderless align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No. Tiket</th>
                                <th>Tanggal</th>
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
                                <td>{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $t->kategori }}</td>
                                <td>{{ Str::limit($t->judul, 40) }}</td>
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
                                    <a href="{{ route('umkm.tickets.show', $t->id) }}" class="btn btn-sm btn-action btn-action-view" title="Detail"><i data-feather="eye"></i> Detail</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">Belum ada tiket pengaduan / konsultasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
