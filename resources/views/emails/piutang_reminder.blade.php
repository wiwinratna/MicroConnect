<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f6f9; padding-bottom: 60px; }
        .main { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-top: 40px; }
        .header { background-color: {{ $umkm->warna_tema ?? '#0d6efd' }}; color: #ffffff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .content { padding: 30px; color: #333333; line-height: 1.6; }
        .content p { margin-top: 0; margin-bottom: 20px; font-size: 16px; }
        .invoice-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-bottom: 25px; }
        .invoice-box table { width: 100%; border-collapse: collapse; }
        .invoice-box td { padding: 8px 0; font-size: 15px; border-bottom: 1px dashed #e9ecef; }
        .invoice-box td:last-child { text-align: right; font-weight: 600; }
        .invoice-box tr:last-child td { border-bottom: none; }
        .total-row td { font-size: 18px !important; color: {{ $umkm->warna_tema ?? '#0d6efd' }}; border-top: 2px solid #dee2e6 !important; border-bottom: none !important; padding-top: 15px !important; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 12px; font-weight: bold; border-radius: 20px; text-transform: uppercase; }
        .badge-danger { background-color: #ffe1e1; color: #dc3545; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        .footer { padding: 25px 30px; text-align: center; font-size: 14px; color: #777777; background-color: #ffffff; border-top: 1px solid #eeeeee; }
        .footer p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main table table-hover table-borderless align-middle" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="header">
                    <h1>Pengingat Tagihan</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <p>Halo <strong>{{ $pelanggan->nama_pelanggan }}</strong>,</p>
                    
                    @php
                       $tglJatuhTempo = $piutang->jatuh_tempo;
                       $sisaHari = now()->diffInDays($tglJatuhTempo, false);
                    @endphp

                    @if($tipe === 'lewat' || $sisaHari < 0)
                        <p>Kami dari <strong>{{ $umkm->nama_usaha }}</strong> ingin mengingatkan bahwa tagihan Anda telah <span style="color:#dc3545; font-weight:bold;">melewati masa jatuh tempo</span>.</p>
                    @elseif($tipe === 'h0' || $sisaHari == 0)
                        <p>Kami dari <strong>{{ $umkm->nama_usaha }}</strong> ingin mengingatkan bahwa tagihan Anda <span style="color:#ffc107; font-weight:bold;">jatuh tempo pada hari ini</span>.</p>
                    @else
                        <p>Kami dari <strong>{{ $umkm->nama_usaha }}</strong> ingin memberikan pengingat ramah bahwa Anda memiliki tagihan yang akan jatuh tempo dalam waktu dekat.</p>
                    @endif

                    <div class="invoice-box">
                        <table>
                            <tr>
                                <td>Kode Piutang / Invoice</td>
                                <td>{{ $piutang->kode_piutang }}</td>
                            </tr>
                            <tr>
                                <td>Tanggal Cetak</td>
                                <td>{{ $piutang->tanggal->isoFormat('D MMMM Y') }}</td>
                            </tr>
                            <tr>
                                <td>Jatuh Tempo</td>
                                <td>
                                    {{ $tglJatuhTempo->isoFormat('D MMMM Y') }}
                                    @if($sisaHari < 0)
                                        <div style="margin-top: 4px;"><span class="badge badge-danger">Terlambat {{ abs(intval($sisaHari)) }} hari</span></div>
                                    @elseif($sisaHari == 0)
                                        <div style="margin-top: 4px;"><span class="badge badge-warning">Hari Ini</span></div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Total Nominal Awal</td>
                                <td class="text-end fw-medium">{{ rupiah($piutang->nominal_awal) }}</td>
                            </tr>
                            <tr>
                                <td>Sudah Dibayar</td>
                                <td style="color:#198754;" class="text-end fw-medium">{{ rupiah($piutang->sudah_dibayar) }}</td>
                            </tr>
                            <tr class="total-row">
                                <td>Sisa Tagihan</td>
                                <td class="text-end fw-medium">{{ rupiah($piutang->sisa) }}</td>
                            </tr>
                        </table>
                    </div>

                    <p>Mohon segera melakukan pembayaran. Terkait instruksi pembayaran (No. Rekening, E-Wallet, atau QRIS), silakan menghubungi WhatsApp kami atau membalas email ini.</p>
                    
                    <p>Apabila Anda sudah melakukan pembayaran, mohon abaikan email ini atau balas email ini dengan melampirkan bukti transfer agar kami dapat segera memperbarui sistem kami.</p>

                    <p>Terima kasih atas kerja sama dan kepercayaan Anda.<br><br>
                    Salam hangat,<br>
                    <strong>{{ $umkm->nama_usaha }}</strong>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>Pesan ini dikirim secara otomatis oleh sistem <strong>MicroConnect KADIN</strong>.</p>
                    <p>Mohon hubungi UMKM terkait untuk pertanyaan lebih lanjut.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
