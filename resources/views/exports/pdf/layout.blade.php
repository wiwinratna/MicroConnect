<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0 0 5px 0; font-size: 18px; }
        .header p { margin: 0; color: #555; }
        .title { text-align: center; margin-bottom: 20px; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background-color: #f5f5f5; font-weight: bold; text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 10px; }
        
        /* Utility */
        .w-100 { width: 100%; }
        .bg-light { background-color: #f9fafb; }
        .table-sm th, .table-sm td { padding: 4px 6px; }
        
        @page { margin: 30px 40px; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="header">
        <h2>{{ $umkm->nama_usaha ?? 'Sistem UMKM' }}</h2>
        <p>{{ $umkm->alamat ?? '-' }} | Telp: {{ $umkm->no_telepon ?? '-' }}</p>
    </div>

    <div class="title">
        {{ $title ?? 'Laporan Keuangan' }}
    </div>

    @yield('content')

    <div style="margin-top: 40px; width: 300px; float: right; text-align: center;">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}</p>
        <br><br><br>
        <p><strong>{{ auth()->user()->name }}</strong></p>
    </div>
</body>
</html>
