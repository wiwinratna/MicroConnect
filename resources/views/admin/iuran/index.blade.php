@extends('layouts.admin')
@section('title', 'Daftar Periode Iuran')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><strong>Periode</strong> Iuran</h1>
            <p class="text-muted mb-0">Kelola iuran bulanan UMKM per periode.</p>
        </div>
        <a href="{{ route('admin.iuran-periode.create') }}" class="btn btn-primary">
            + Buat Periode Baru
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Periode</th>
                        <th>Nominal Default</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th class="text-center">Total UMKM</th>
                        <th class="text-center">Lunas</th>
                        <th class="text-center">Belum Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodes as $periode)
                        <tr>
                            <td class="fw-semibold">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $periode->periode)->translatedFormat('F Y') }}
                            </td>
                            <td>{{ rupiah($periode->nominal_default) }}</td>
                            <td>{{ $periode->jatuh_tempo->format('d/m/Y') }}</td>
                            <td>
                                @if($periode->status === 'terbit')
                                    <span class="badge bg-success">Terbit</span>
                                @elseif($periode->status === 'selesai')
                                    <span class="badge bg-secondary">Selesai</span>
                                @else
                                    <span class="badge bg-warning text-dark">Draft</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill">{{ $periode->total_umkm }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success rounded-pill">{{ $periode->total_lunas }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning text-dark rounded-pill">{{ $periode->total_belum_bayar }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.iuran-periode.show', $periode->id) }}" class="btn btn-sm btn-outline-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada periode iuran. Klik tombol "Buat Periode Baru" untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($periodes->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $periodes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
