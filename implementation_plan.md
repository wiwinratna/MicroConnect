# KADIN Dashboard Revamp (UMKM Monitoring)

Dashboard KADIN saat ini hanya menampilkan daftar UMKM dan omzet statis. Revisi ini akan mengubah fokus dashboard menjadi alat *monitoring* interaktif untuk melacak kesehatan bisnis, level UMKM, performa, dan tingkat keaktifan.

## Proposed Changes

### 1. UMKM Sidebar Level Restrictions ([resources/views/partials/umkm/sidebar.blade.php](file:///c:/xampp/htdocs/proyek_kadin/resources/views/partials/umkm/sidebar.blade.php))
Sistem level UMKM saat ini: Level 1 (Dasar), Level 2 (Menengah), Level 3 (Lanjut).
- **Level 1**: Hanya dapat melihat Dashboard, Profil, Pengaduan MINECT (Ticketing), Jurnal Umum (dibuka).
- **Level 2**: Fitur Level 1 + Penjualan, Piutang Pelanggan, Beban Aktual.
- **Level 3**: Fitur Level 1 & 2 + Pembelian Bahan, Anggaran & Produksi, Laporan Lengkap.
*(Catatan: Logika `level_id` atau relasi level akan digunakan di dalam file Sidebar blade untuk meng-hide menu dengan klausa `@if($level >= 2)` dll.)*

### 2. Controller Expansion: `Admin\DashboardController`
- **Summary Metrics**: Menghitung secara global: Total UMKM Aktif, UMKM Level 1, 2, 3, Total Omzet, Total Laba Bersih, UMKM Tanpa Transaksi, Tiket Open.
- **Perhitungan Profitabilitas per UMKM**: Mengurangi Omzet/Pendapatan dengan HPP (Beban Produk) dan Beban Operasional Aktual untuk mendapat *Laba Bersih* dan *Margin Laba*. Jika belum bisa ditarik akurat diproteksi dengan fallback `-`.
- **Status Kesehatan**:
  - `Sehat`: Laba bersih positif & ada margin.
  - `Waspada`: Omzet kecil atau rugi (laba negatif) atau margin terlalu tipis (< 10%).
  - `Tidak Aktif`: 0 transaksi bulan ini.
- `Perhatian` direpresentasikan menggunakan pengelompokan (Blok "Perlu Perhatian" dan "Performa Terbaik").

### 3. Level Management Audit: `Admin\UmkmController`
- Audit field `level_id` dipastikan bisa di-update via form yang sudah ada di [admin/umkm/show.blade.php](file:///c:/xampp/htdocs/proyek_kadin/resources/views/admin/umkm/show.blade.php).
- Menambahkan relasi level di grid *All UMKM* pada dashboard admin, jika belum.

### 4. UI Implementation: `admin.dashboard` View
Visual *Dashboard* direvisi dengan struktur:
1. **[Atas] Summary Cards**: Grid kartu (Total UMKM, Level-level, Omzet, Tiket MINECT).
2. **[Tengah] Performa Terbaik & Perlu Perhatian**: Dua blok bersebelahan yang mem-highlight UMKM top dan bottom.
3. **[Bawah] Tabel All UMKM**: Tabel daftar UMKM dengan nama, level, transaksi, omzet, laba bersih, profitabilitas (%), status, aksi detail.

## Kriteria Penentuan Status Kesehatan
1. **Tidak Aktif (Inactive)**: Tidak ada penjualan sama sekali bulan ini.
2. **Waspada (Need Attention)**: Ada penjualan, namun omzet berada di bawah ambang batas (contoh < 100.000) atau jika mencatat margin laba, persentasenya terlalu tipis (< 10%).
3. **Sehat (Healthy)**: Meraup omzet solid dibantu dengan margin laba yang memadai.

## Verification Plan
- Admin membuka halaman dashboard akan melihat metrik rangkuman kalkulasi di tingkat atas.
- Menguji bahwa 3 blok status terpampang akurat pada tabel (Sehat, Waspada, Tidak Aktif).
- Konfirmasi *Profitabilitas* menampilkan angka absolut laba atau persentase, dan menampilkan `-` secara aman bila kolom HPP / Modal tidak tersedia di konfigurasi *inventory*.
- Modifikasi pengaturan Level UMKM di UMKM *Detail Page* untuk membuktikan *Leveling system* teraplikasi di *database* dan segera terpampang informasinya di *dashboard* setelah pembaruan.
