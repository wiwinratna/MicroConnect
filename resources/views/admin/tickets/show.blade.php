@extends('layouts.admin')

@section('title', 'Kelola Tiket: ' . $ticket->kode_ticket)

@section('content')
<main class="content">
    <div class="container-fluid p-0">
        <div class="row mb-3">
            <div class="col-auto">
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary mb-3">&larr; Kembali ke Daftar Tiket</a>
                <h1 class="h3 mb-1"><strong>{{ $ticket->kode_ticket }}</strong></h1>
                <p class="text-muted mb-0">UMKM: <strong>{{ $ticket->umkm->nama_perusahaan ?? '-' }}</strong></p>
                <p class="text-muted mb-0">Kategori: {{ $ticket->kategori }} | Judul: {{ $ticket->judul }}</p>
            </div>
            <div class="col-auto ms-auto mt-3 mt-md-0">
                <div class="card shadow-sm border mt-4">
                    <div class="card-body py-2 px-3">
                        <form action="{{ route('admin.tickets.status', $ticket->id) }}" method="POST" class="d-flex align-items-center">
                            @csrf
                            @method('PATCH')
                            <label class="fw-bold me-2 mb-0">Ubah Status:</label>
                            <select name="status" class="form-select form-select-sm me-2" style="width: 140px;">
                                <option value="Open" {{ $ticket->status === 'Open' ? 'selected' : '' }}>Open</option>
                                <option value="In Progress" {{ $ticket->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Resolved" {{ $ticket->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-warning">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        
        
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0"><i class="align-middle" data-feather="message-circle"></i> Riwayat Konsultasi</h5>
                    </div>
                    <div class="card-body" style="background-color: #f8f9fa;">
                        
                        @foreach($ticket->messages as $msg)
                            @php
                                $isAdmin = $msg->user->user_group === 'admin';
                            @endphp
                            
                            <div class="d-flex mb-4 {{ $isAdmin ? 'justify-content-end' : 'justify-content-start' }}">
                                <div class="card border mb-0 shadow-sm" style="max-width: 75%; border-radius: 12px; {{ $isAdmin ? 'background-color: #dcf8c6; border-color: #c4e5ac;' : 'background-color: #ffffff; border-color: #ddd;' }}">
                                    <div class="card-header py-2 px-3 border-bottom-0 bg-transparent">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold mb-0 text-dark">
                                                @if($isAdmin)
                                                    <i class="align-middle text-success" data-feather="shield"></i> Anda (KADIN)
                                                @else
                                                    <i class="align-middle text-primary" data-feather="user"></i> UMKM: {{ $ticket->umkm->nama_perusahaan ?? 'Admin' }}
                                                @endif
                                            </span>
                                            <small class="text-muted ms-3" style="font-size: 0.75rem;">
                                                {{ \Carbon\Carbon::parse($msg->created_at)->format('d M Y, H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-body py-2 px-3">
                                        <p class="mb-0 text-dark" style="white-space: pre-wrap;">{{ $msg->message }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>

                    @if($ticket->status !== 'Resolved')
                    <div class="card-footer bg-white border-top">
                        <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="fw-bold text-muted mb-1"><i class="align-middle" data-feather="edit-2"></i> Balas Tiket UMKM</label>
                                <textarea name="message" class="form-control bg-light" rows="4" placeholder="Ketik panduan atau jawaban untuk UMKM di sini..." required></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary"><i class="align-middle" data-feather="send"></i> Balas & Pindahkan status ke In Progress</button>
                            </div>
                        </form>
                    </div>
                    @else
                    <div class="card-footer bg-light text-center py-4 border-top">
                        <span class="text-muted fw-bold"><i class="align-middle text-success" data-feather="check-circle"></i> Tiket ini telah ditutup (Resolved).</span>
                        <br>
                        <small class="text-muted">Balasan dinonaktifkan. Anda dapat mengubah status di bagian atas jika tiket perlu dibuka kembali.</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
