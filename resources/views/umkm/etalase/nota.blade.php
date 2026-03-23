<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk Pembayaran #{{ $penjualan->kode_penjualan }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --kasir-primary: {{ $umkm->warna_tema ?? '#0d6efd' }};
        }
        body {
            background-color: #f4f6f9;
            font-family: 'Courier Prime', monospace; /* Struk thermal look */
            color: #1a1a1a;
            margin: 0;
            padding: 2rem 1rem;
            display: flex;
            justify-content: center;
        }
        .struk-container {
            background: #ffffff;
            width: 100%;
            max-width: 320px;
            padding: 2rem 1.5rem;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            /* Edge styling to look like torn receipt paper */
            position: relative;
        }
        .struk-header {
            text-align: center;
            border-bottom: 2px dashed #ddd;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .logo-img {
            max-width: 60px;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            filter: grayscale(100%); /* Struk look */
        }
        h3, h5, p { margin: 0; padding: 0; }
        .usaha-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }
        .struk-info, .struk-totals {
            font-size: 0.85rem;
            line-height: 1.5;
        }
        .item-list {
            margin: 1.5rem 0;
            font-size: 0.85rem;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .item-name {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        .divider {
            border-bottom: 1px dashed #ddd;
            margin: 1rem 0;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }
        .grand-total {
            font-size: 1.1rem;
            font-weight: 700;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px dashed #ddd;
        }
        .struk-footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.8rem;
        }
        .btn-print {
            display: block;
            width: 100%;
            background: var(--kasir-primary);
            color: white;
            padding: 1rem;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-family: Arial, sans-serif;
            margin-top: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-back {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 1rem;
            color: #666;
            text-decoration: none;
            font-family: Arial, sans-serif;
            font-size: 0.9rem;
        }
        @media print {
            @page {
                size: portrait; /* Kertas vertikal (potrait) seperti struk laci kasir */
                margin: 0; /* Tanpa margin kertas agar pas */
            }
            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                background: white;
            }
            body { 
                display: block;
                text-align: left !important;
            }
            .struk-wrapper { 
                display: block !important;
                margin: 10mm 0 !important; 
                margin-left: calc(50% - 37.5mm) !important; /* Paksa posisi tengah menggunakan kalkulasi matematika mutlak */
                padding: 0; 
                width: 75mm !important; 
                max-width: 75mm !important; 
                text-align: left !important; 
            }
            .struk-container {
                box-shadow: none;
                max-width: 100%;
                padding: 5mm; /* Kompres padding untuk muat 75mm */
                width: 100%;
                margin: 0 auto;
                border: 1px dashed #ccc; /* outline tipis penanda batas struk di kertas putih */
            }
            .btn-print, .btn-back { display: none; }
        }
    </style>
</head>
<body>

    <div class="struk-wrapper">
        <div class="struk-container">
            
            <div class="struk-header">
                @if($umkm->logo_path)
                    <img src="{{ asset('storage/' . $umkm->logo_path) }}" class="logo-img" alt="Logo">
                @endif
                <div class="usaha-name">{{ $umkm->nama_usaha ?? 'Kasir MINECT' }}</div>
                @if($umkm->alamat)
                    <p style="font-size: 0.8rem;">{{ $umkm->alamat }}</p>
                @endif
                @if($umkm->no_telp)
                    <p style="font-size: 0.8rem;">Telp: {{ $umkm->no_telp }}</p>
                @endif
            </div>

            <div class="struk-info">
                <div>No   : {{ $penjualan->kode_penjualan }}</div>
                <div>Tgl  : {{ \Carbon\Carbon::parse($penjualan->tanggal)->format('d-m-Y H:i') }}</div>
                <div>Ops  : {{ auth()->user()->name ?? 'Kasir' }}</div>
                @if($penjualan->pelanggan_id)
                    <div>Plg  : {{ $penjualan->pelanggan->nama_pelanggan }}</div>
                @elseif($penjualan->pembeli)
                    <div>Plg  : {{ $penjualan->pembeli }}</div>
                @else
                    <div>Plg  : Tunai / Umum</div>
                @endif
            </div>

            <div class="divider"></div>

            <div class="item-list">
                @foreach($penjualan->details as $d)
                <div style="margin-bottom: 0.75rem;">
                    <div class="item-name">{{ $d->produk->nama_produk ?? 'Item Dihapus' }}</div>
                    <div class="item-row">
                        <div>{{ $d->qty }} x {{ number_format($d->harga, 0, ',', '.') }}</div>
                        <div style="font-weight: 700;">{{ number_format($d->subtotal, 0, ',', '.') }}</div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="divider"></div>

            <div class="struk-totals">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>{{ number_format($penjualan->total, 0, ',', '.') }}</span>
                </div>
                <div class="totals-row grand-total">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</span>
                </div>
                <div class="totals-row mt-2" style="margin-top: 0.5rem;">
                    <span>Tunai</span>
                    <span>Rp {{ number_format($uangDibayar, 0, ',', '.') }}</span>
                </div>
                <div class="totals-row">
                    <span>Kembali</span>
                    <span>Rp {{ number_format($kembalian, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="divider"></div>

            <div class="struk-footer">
                <p>TERIMA KASIH</p>
                <p style="font-size: 0.75rem; margin-top: 4px;">Barang yang sudah dibeli<br>tidak dapat ditukar/dikembalikan.</p>
            </div>
        </div>

        <a href="#" onclick="window.print(); return false;" class="btn-print">CETAK STRUK</a>
        <a href="{{ route('umkm.etalase.index') }}" class="btn-back">&larr; Kembali ke Kasir</a>
    </div>

</body>
</html>
