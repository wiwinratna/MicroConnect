@extends('layouts.umkm')

@section('title', 'Buat Tiket Baru')

@section('content')
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Buat Tiket</strong> Pengaduan & Konsultasi</h1>

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="alert-message">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-md-8 col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Isi Formulir Tiket</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('umkm.tickets.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kategori Permasalahan</label>
                                <select name="kategori" class="form-select w-100" required>
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <option value="Kendala Penggunaan Sistem">Kendala Penggunaan Sistem</option>
                                    <option value="Kendala Pencatatan Transaksi">Kendala Pencatatan Transaksi</option>
                                    <option value="Kendala Laporan Keuangan">Kendala Laporan Keuangan</option>
                                    <option value="Kendala Akun / Login">Kendala Akun / Login</option>
                                    <option value="Kendala Iuran">Kendala Iuran</option>
                                    <option value="Konsultasi Usaha">Konsultasi Usaha</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Judul / Topik Singkat</label>
                                <input type="text" name="judul" class="form-control" placeholder="Contoh: Salah catat beban listrik" value="{{ old('judul') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Pesan / Detail Kendala</label>
                                <textarea name="message" class="form-control" rows="6" placeholder="Jelaskan kendala Anda selengkap mungkin agar KADIN dapat membantu..." required>{{ old('message') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-100"><i class="align-middle" data-feather="send"></i> Kirim Tiket</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-xl-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="fw-bold"><i class="align-middle text-primary" data-feather="info"></i> Informasi</h6>
                        <p class="text-muted small">
                            Fitur ini digunakan untuk komunikasi dua arah antara UMKM dan tim pembina KADIN. <br><br>
                            Silakan deskripsikan masalah Anda dengan jelas dan rinci agar tim kami bisa segera memberikan solusi yang tepat.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
