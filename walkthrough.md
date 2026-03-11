# Walkthrough: Integrasi Stok, Piutang, dan Akuntansi MINECT

Tahap integrasi krusial pada sistem MINECT untuk kebutuhan TA telah selesai diimplementasikan. Integrasi ini menghubungkan modul Pembelian, Penjualan, Piutang, dan Akuntansi (Penjurnalan Otomatis & Laporan Keuangan).

## Ringkasan Fitur yang Telah Selesai:

1. **Integrasi Stok pada Transaksi**
   - **Pembelian Bahan Baku**: Saat UMKM melakukan "Input Pembelian", sistem kini otomatis mendata mutasi masuk di tabel `stok_mutasi`.
   - **Penjualan Produk**: Saat mencatat penjualan, sistem memotong stok "Produk" secara langsung di tabel `stok_mutasi`, bukan memotong stok "Bahan Baku" (menghindari *backflushing* yang rumit/rancu untuk UMKM).
   - **HPP Otomatis**: Nilai Harga Pokok Penjualan (HPP) otomatis dihitung berdasarkan metode inventarisasi yang dipilih UMKM di profil (FIFO, LIFO, atau Average).

2. **Otomatisasi Piutang**
   - Di form Penjualan ([create.blade.php](file:///c:/xampp/htdocs/proyek_kadin/resources/views/admin/umkm/create.blade.php)), pengguna kini dapat memilih **Metode Pembayaran (Tunai / Piutang)**.
   - Jika memilih Piutang, field **Pelanggan** dan **Jatuh Tempo** akan muncul.
   - Sistem akan otomatis membuat tagihan di modul Piutang saat transaksi Penjualan (Kredit) disimpan.

3. **Penjurnalan Otomatis (Accounting Service)**
   - Sistem kini dilengkapi dengan `App\Services\AccountingService` yang bertindak sebagai mesin penjurnalan di *background*.
   - Transaksi akan menghasilkan jurnal otomatis sesuai metode pencatatan (*Recording Method*: Periodik atau Perpetual) yang diatur di Profil UMKM.
   - **Jurnal Pembelian**: Otomatis mendebit Persediaan (Perpetual) atau Pembelian (Periodik) dan mengkredit Kas.
   - **Jurnal Penjualan**: Otomatis mendebit Kas (Tunai) / Piutang Usaha (Kredit) dan mengkredit Pendapatan. (Ditambah jurnal HPP dan Persediaan jika Perpetual).
   - **Jurnal Pembayaran Piutang**: Otomatis mendebit Kas dan mengkredit Piutang saat pelanggan melunasi tagihannya di menu Piutang.

4. **Jurnal Manual & Laporan Keuangan**
   - **Jurnal Manual**: UMKM kini dapat mencatat biaya operasional bulanan (misal: listrik, gaji) secara manual dengan fitur multikolom (Debit & Kredit) yang divalidasi harus seimbang (balance).
   - **Laporan Keuangan**: Tersedia dashboard Laporan Keuangan yang menarik data dari Buku Besar. Laporan ini secara instan merekap:
     - Mutasi Per Akun (Buku Besar)
     - Laporan Laba Rugi (Pendapatan - HPP - Beban)
     - Laporan Perubahan Modal

## Cara Validasi & Demonstrasi (Review User)
Untuk mendemokan hasil perubahan ini, silakan jalankan skenario berikut di aplikasi MINECT:
1. Pastikan Anda login sebagai **Akun UMKM** (`auth()->user()->user_group === 'pelakuusaha'`).
2. Masuk ke **Menu Profil UMKM** dan set *Metode Pencatatan* menjadi **Periodic** atau **Perpetual**. (Perpetual lebih direkomendasikan untuk melihat jurnal HPP secara real-time).
3. **Skenario Pembelian**: Masuk menu *Pembelian Bahan*, buat data pembelian baru. Buka menu *Jurnal Umum* -> akan ada jurnal baru yang mencatat pembelian dan pengurangan kas.
4. **Skenario Penjualan (Piutang)**: Masuk menu *Penjualan*, buat transaksi baru. Pilih Metode Pembayaran: **Piutang**, pilih pelanggan, tanggal jatuh tempo. Buka menu *Piutang*, pastikan tagihan pelanggan tersebut muncul di sana.
5. Coba lakukan pelunasan parsial/penuh di menu *Piutang*. Buka kembali menu *Jurnal Umum* -> akan ada jurnal penerimaan kas dan pengurangan piutang.
6. **Skenario Biaya Operasional**: Masuk ke menu *Jurnal Umum* -> klik *+ Jurnal Manual*. Catat pengeluaran Beban Listrik terhadap Kas.
7. Buka menu **Laporan Keuangan**. Anda akan melihat Laporan Laba Rugi dan Perubahan Modal bulan ini telah terbentuk sesuai dinamika transaksi di atas.
