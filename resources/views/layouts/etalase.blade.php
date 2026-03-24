<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') - Mode Kasir</title>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/custom-polish.css') }}" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>

    <style>
        :root {
            --kasir-primary: {{ $umkm->warna_tema ?? '#0d6efd' }}; 
            --kasir-primary-hover: color-mix(in srgb, var(--kasir-primary) 85%, black);
            --kasir-primary-light: color-mix(in srgb, var(--kasir-primary) 10%, white);
            --kasir-bg: #f4f6f9;
        }

        body {
            background-color: var(--kasir-bg);
            font-family: 'Inter', sans-serif;
            color: #333;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar khusus Kasir */
        .kasir-navbar {
            background-color: #ffffff;
            height: 72px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .kasir-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #1a1a1a;
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: -0.02em;
        }

        .kasir-brand img {
            height: 40px;
            width: 40px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .kasir-brand .logo-fallback {
            height: 40px;
            width: 40px;
            border-radius: 10px;
            background-color: var(--kasir-primary-light);
            color: var(--kasir-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .btn-theme {
            background-color: var(--kasir-primary);
            color: #ffffff;
            border: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .btn-theme:hover {
            background-color: var(--kasir-primary-hover);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px color-mix(in srgb, var(--kasir-primary) 40%, transparent);
        }

        .text-theme { color: var(--kasir-primary) !important; }
        .bg-theme { background-color: var(--kasir-primary) !important; color: white; }
        .bg-theme-light { background-color: var(--kasir-primary-light) !important; }

        .kasir-wrapper {
            flex-grow: 1;
            padding: 1.5rem;
            max-width: 1600px;
            margin: 0 auto;
            width: 100%;
        }

        /* Subtle scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
    
    @stack('styles')
</head>

<body>

    {{-- NAVBAR --}}
    <nav class="kasir-navbar position-relative w-100 d-flex justify-content-between align-items-center px-4" style="height: 72px; background: white; box-shadow: 0 1px 4px rgba(0,0,0,0.04); z-index: 1000; position: sticky; top: 0;">
        {{-- Kiri: Brand --}}
        <div style="flex: 1; min-width: 0;" class="d-flex align-items-center">
            <a class="kasir-brand d-inline-flex align-items-center gap-3 text-decoration-none text-dark" href="{{ route('umkm.etalase.index') }}">
                @if(isset($umkm) && $umkm->logo_path)
                    <img src="{{ asset('storage/' . $umkm->logo_path) }}" alt="Logo" style="height: 40px; width: 40px; border-radius: 10px; object-fit: cover;">
                @else
                    <div class="logo-fallback shadow-sm" style="height: 40px; width: 40px; border-radius: 10px; background-color: var(--kasir-primary-light); color: var(--kasir-primary); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; font-weight: 800;">
                        {{ strtoupper(substr($umkm->nama_usaha ?? 'U', 0, 2)) }}
                    </div>
                @endif
                <div class="d-flex flex-column" style="min-width: 0;">
                    <span class="fw-bold text-truncate" style="font-size: 1.05rem; line-height: 1.2;">{{ $umkm->nama_usaha ?? 'Kasir MINECT' }}</span>
                    <span class="text-muted fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Mode Kasir</span>
                </div>
            </a>
        </div>

        {{-- Tengah: Search (Bisa di-inject dari layout child) --}}
        <div class="d-none d-md-flex justify-content-center align-items-center" style="flex: 1;">
            @yield('navbar_center')
        </div>

        {{-- Kanan: Nav Buttons --}}
        <div class="d-flex justify-content-end align-items-center gap-3" style="flex: 1;">
            <a href="{{ route('umkm.dashboard') }}" class="btn btn-light btn-sm rounded-pill fw-semibold px-3 py-2 border shadow-sm text-secondary d-flex align-items-center gap-2" style="background: white;">
                <i data-feather="arrow-left" style="width:14px;height:14px;"></i> <span>Dashboard Utama</span>
            </a>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="kasir-wrapper">
        @yield('content')
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        feather.replace();
    </script>
    @stack('scripts')
</body>
</html>
