# SRS — MINECT v1.1 (Software Requirements Specification)

Sumber: `MASTER_PROMPT_MINECT_v1.1.md` (Bagian 3–9, 13) + audit kode v1.0 yang sudah diverifikasi pada K0. Rujukan produk: `docs/PRD.md`.

## 1. Pendahuluan
Dokumen ini menetapkan kebutuhan fungsional dan nonfungsional MINECT v1.1 sebagai penambahan atas v1.0. Jika dokumen ini berbeda dari master prompt, master prompt (bagian bernomor, lalu Bagian 13 Mode Tenggat) yang berlaku.

## 2. Stack (hasil audit v1.0, terverifikasi K0)
| Komponen | Nilai |
|---|---|
| Backend | PHP 8.2, Laravel 12 |
| Basis data | MySQL/MariaDB (XAMPP). `.env.example` masih bawaan SQLite; pakai `.env` lokal MySQL |
| Basis data uji | `minnect_test` (MySQL terpisah); `tests/TestCase.php` menghentikan tes bila koneksi bukan DB itu |
| Frontend | Blade + aset di `public/css/minect-theme`, Vite |
| Migrasi | Laravel migrations (`database/migrations`), dapat dibatalkan |
| Pengujian | PHPUnit 11 |
| Pustaka | barryvdh/laravel-dompdf, phpoffice/phpspreadsheet, midtrans/midtrans-php |
| Zona waktu | Asia/Jakarta, dibaca dari `TIMEZONE` di `.env`; uang `decimal(18,2)` |
| Konfigurasi v1.1 | `config/minect.php` (`INITIAL_PASSWORD`, `NOTIF_DRIVER`) |

## 3. Aturan bisnis
| ID | Aturan |
|---|---|
| BR-01 | Hanya Admin menetapkan level; sistem hanya merekomendasikan; tidak ada perubahan level otomatis |
| BR-02 | `level_akhir` ≠ `level_rekomendasi` → alasan wajib, terlihat oleh UMKM |
| BR-03 | Calon UMKM (pengajuan belum disetujui) tidak punya akun sehingga tidak melihat skor maupun level |
| BR-04 | Perubahan level tidak menghapus data historis |
| BR-05 | Hanya dua peran: `admin` dan `pelakuusaha` (nilai v1.0 dipertahankan). Admin dibuat lewat seeder; `/admin/register` sudah ditutup (K0) |
| BR-06 | Pengajuan `menunggu` hanya dibuat setelah data usaha dan kuesioner lengkap dikirim; tidak ada draf dan tidak ada akun sebelum disetujui |
| BR-07 | Login hanya untuk akun yang ada dan berstatus `aktif`; pengenal login tetap email |
| BR-08 | Kata sandi awal seragam dari `INITIAL_PASSWORD`; selalu ter-hash. **Keputusan pemilik (setelah K3):** mengganti kata sandi awal tidak wajib (pilihan pemilik akun, lewat menu Profil). Aturan wajib ganti saat login pertama tetap tersedia dan dihidupkan dengan `FORCE_CHANGE_INITIAL_PASSWORD=true` di `.env` |
| BR-09 | Batas skor level dan skor pilihan disimpan di basis data, bukan hardcode |
| BR-10 | Indikator dan jenis usaha yang dipakai hanya boleh dinonaktifkan |
| BR-11 | Pilihan `di_luar_batas_mikro` (pilihan d Pertanyaan 1, 2, 3): skor 3 dan `perlu_keputusan_admin = true`; tidak ditolak otomatis |
| BR-12 | Skor total = jumlah skor empat pertanyaan; semua wajib dijawab; skor dari tabel `pilihan_jawaban` |
| BR-13 | `users` dan `umkm` dibuat hanya saat Admin menyetujui pengajuan, dalam satu transaksi bersama pengisian level dan notifikasi |
| BR-14 | UMKM lama tetap `aktif` dengan level sekarang; migrasi tidak boleh menyembunyikan fitur yang mereka pakai |
| BR-15 | Iuran bulanan mengikuti level seperti v1.0; logika Midtrans tidak diubah |

## 4. Penilaian kesiapan dan jenis usaha

Istilah (keputusan pemilik 2026-11-26): **Jenis usaha** = `barang` atau `jasa`; **Kategori usaha** = bidang usaha (Pertanian, Jasa boga, dst.). Nama kolom dan tabel tidak diubah: jenis usaha disimpan di kolom `rumpun`, kategori usaha di tabel `jenis_usaha` (`rumpun_bawaan` = jenis tempat kategori muncul; `ditentukan_umkm` = keduanya). Kata "rumpun" di dokumen ini berarti jenis usaha.
### 4.1 Kuesioner (Tabel 3.6; teks persis seperti seeder `IndikatorSeeder`)
| P | Indikator | Pilihan (skor) |
|---|---|---|
| 1 | Omzet tahunan | a ≤ Rp500 jt (1); b > 500 jt s.d. 1,2 M (2); c > 1,2 M s.d. 2 M (3); d > 2 M (3, `di_luar_batas_mikro`) |
| 2 | Modal usaha (tanpa tanah/bangunan) | a ≤ Rp100 jt (1); b > 100 s.d. 500 jt (2); c > 500 jt s.d. 1 M (3); d > 1 M (3, `di_luar_batas_mikro`) |
| 3 | Tenaga kerja | a 1 orang (1); b 2–3 (2); c 4 (3); d > 4 (3, `di_luar_batas_mikro`) |
| 4 | Kebiasaan pencatatan | a tidak mencatat / mingguan-bulanan (1); b harian (2); c setiap transaksi (3); contoh tiap pilihan ditampilkan di formulir |

Pilihan d untuk omzet dan modal belum ada di Tabel 3.6 buku; ubah buku atau hapus lewat seeder. Level (tabel `umkm_level`): Dasar 4–6, Berkembang 7–9, Lanjutan 10–12. Di basis data nama level L3 masih "Level 3 - Lanjut".

### 4.2 Jenis usaha dan kategori usaha (Tabel 3.7)
Hanya dua jenis usaha: **barang** dan **jasa**, dipilih langsung oleh UMKM, lalu kategori usaha dipilih dari daftar yang sesuai. Data awal: Pertanian, Peternakan, Perikanan dan kelautan, Jasa boga, Perdagangan barang → barang; Jasa perorangan → jasa; Usaha kreatif → keduanya. Admin dapat menambah dan mengubah kategori (FR-16). Kategori hanya keterangan; fitur disaring dari jenis usaha. `umkm.rumpun` terisi untuk UMKM baru; UMKM lama boleh kosong sampai Admin melengkapinya (FR-27).

Pemetaan teks `jenis_usaha` lama (migrasi `2026_11_25_000007`): Kuliner, Makanan & Minuman → Jasa boga (barang); Perdagangan → Perdagangan barang (barang); Fashion & Tekstil → Usaha kreatif (barang); Manufaktur → barang tanpa `jenis_usaha_id`; Jasa → Jasa perorangan (jasa). `memiliki_persediaan` dan `melakukan_produksi` UMKM lama dibiarkan kosong.

### 4.3 Aturan modul (Tabel 3.8)
| Modul | Barang | Jasa |
|---|---|---|
| `bahan_baku` | jika_melakukan_produksi | jika_memiliki_persediaan |
| `kartu_stok`, `metode_persediaan` | selalu | jika_memiliki_persediaan |
| `produksi` | jika_melakukan_produksi | tidak_ditampilkan |
| `produk` | selalu, label "Produk" | selalu, label "Daftar Layanan Jasa" |
| `pembelian` | selalu | selalu |
| `penjualan`, `beban`, `laporan` | sesuai_level | sesuai_level |

Berbasis data (`aturan_modul`), bukan if-else. Menyembunyikan fitur tidak menghapus data; akses diblokir juga di server (rute dan aksi). **Aturan untuk UMKM lama berlaku hanya setelah `rumpun` dan flag yang relevan terisi; selama ada yang kosong, tidak ada fitur yang disembunyikan (BR-14).** Metode persediaan v1.0 (FIFO, LIFO, Average) tidak diubah. Produksi: penyimpanan di `ProduksiController::store` masih dinonaktifkan untuk MVP (tidak diubah); `produksi` juga tetap hanya untuk level Lanjutan (level dan aturan rumpun berlaku bersama).

### 4.4 Laporan per level (Tabel 3.9, dari konfigurasi v1.0 `FeatureAccess`; disetujui)
| Laporan | Dasar | Berkembang | Lanjutan |
|---|:-:|:-:|:-:|
| Jurnal Umum, Laporan Pembelian, Laporan Penjualan | ✓ | ✓ | ✓ |
| Buku Besar, Laba Rugi, Kartu Stok, Piutang | | ✓ | ✓ |
| Perubahan Modal, Arus Kas | | | ✓ |

Konfigurasi v1.0 dipakai ulang; tabel `laporan_keuangan` dan `level_laporan` tidak dibuat.

## 5. Model data dan pemetaan ke v1.0
Konvensi v1.0 dipakai; perubahan lewat migrasi yang dapat dibatalkan.

| Master prompt | v1.0 | Status v1.1 (terpasang K0) |
|---|---|---|
| `pengguna` | `users` (`name`, `email`, `user_group`, `must_change_password`) | Ditambah `username`, `no_wa` (unik, boleh null) |
| `level` | `umkm_level` | Ditambah `urutan`, `skor_min`, `skor_max`; nama L2 menjadi "Level 2 - Berkembang" |
| `umkm` | `umkm` | Ditambah `jenis_usaha_id`, `rumpun`, `memiliki_persediaan`, `melakukan_produksi`, `persetujuan_data_pribadi`, `tanggal_persetujuan`, `tanggal_aktivasi` (semua boleh null) |
| `produk` | `produk` | Ditambah `satuan` (boleh null; migrasi `2026_11_26_000002`), karena formulir v1.0 memintanya tetapi tidak tersimpan |
| `notifikasi` | `notifikasi_log` (`umkm_id` wajib) | Tabel baru `notifikasi`; `notifikasi_log` tetap untuk piutang/iuran |
| Baru | — | `jenis_usaha`, `modul`, `aturan_modul`, `indikator`, `pilihan_jawaban`, `pengajuan_pendaftaran`, `periode_penilaian`, `penilaian_level`, `detail_penilaian`, `log_audit` |

Constraint unik: `users.username`, `users.no_wa`; `detail_penilaian(penilaian_id, indikator_id)`; `penilaian_level(umkm_id, periode_id)`; satu pengajuan `menunggu` per `no_wa`, `username`, dan `email` (kolom virtual `*_menunggu`). Snapshot skor dan teks pilihan di `detail_penilaian`. Penyimpangan: `tanggal_persetujuan` dan `tanggal_pengajuan` pada pengajuan boleh null; `pengajuan_pendaftaran` punya kolom `email`. K4b menambah `penilaian_level.data_sistem` (JSON, boleh null): angka pembukuan yang direkam saat penilaian ulang dikirim, agar Admin bisa melihat apakah UMKM mengubah pilihan dari saran sistem.

## 6. Alur dan status
Status pengajuan: `menunggu` → (Admin setujui) `disetujui` atau (Admin tolak) `ditolak`. Status UMKM (v1.0): `aktif` / `nonaktif`. Calon UMKM tidak punya akun sampai disetujui; yang ditolak boleh mengajukan lagi.
- **Alur A (onboarding):** formulir tanpa login → pengiriman akhir membuat pengajuan, penilaian awal, dan detail dalam satu transaksi → notifikasi diterima → Admin setujui / setujui level berbeda (alasan wajib) / tolak (alasan wajib) → akun dibuat dalam satu transaksi dengan `must_change_password = true`.
- **Alur B (penilaian ulang):** Admin buka periode → UMKM isi sekali per periode → sistem beri arah naik/tetap/turun → Admin tetapkan level akhir → `umkm.level_id` diperbarui, riwayat disimpan, notifikasi hasil. Terpasang (K4b): periode berlaku bila status `dibuka` dan hari ini di antara tanggal mulai dan selesai, dan hanya satu periode boleh dibuka sekaligus. Formulir terisi otomatis dan semuanya dapat diubah UMKM: omzet (total pendapatan akun 4xx 12 bulan terakhir) dan modal (saldo akun modal 3xx ditambah laba bersih kumulatif) dari pembukuan, sedangkan tenaga kerja dan kebiasaan pencatatan dari jawaban penilaian terakhir. Angka sistem dicocokkan ke pilihan lewat rentang `nilai_min`/`nilai_max`; data kurang dari 12 bulan dipakai apa adanya (tidak diproyeksikan) dengan keterangan jumlah hari. Skor tetap dari pilihan yang dipilih UMKM (BR-12). Penilaian yang sudah dikirim tetap bisa ditinjau setelah periode ditutup.

## 7. Kebutuhan fungsional dan kriteria uji
| ID | Kebutuhan | Kriteria uji | Prioritas |
|---|---|---|---|
| FR-01 | Registrasi mandiri tanpa kata sandi dan tanpa login (nama pemilik, email, no WA, username, data usaha, persetujuan data pribadi) sebagai bagian formulir yang dikirim bersama kuesioner | Kolom wajib kosong / no WA, username, atau email sudah dipakai akun atau pengajuan `menunggu` / persetujuan tidak dicentang → ditolak dengan pesan. Belum ada data tersimpan sebelum pengiriman akhir | WAJIB |
| FR-02 | Pilih jenis usaha (barang/jasa), lalu kategori usaha yang sesuai; pertanyaan produksi (barang) atau persediaan (jasa) | Tanpa jenis ditolak; kategori yang tidak sesuai jenis ditolak; dropdown hanya menampilkan kategori sesuai jenis; barang → `memiliki_persediaan` true; jasa sesuai jawaban | WAJIB |
| FR-03 | Kuesioner 4 pertanyaan pilihan ganda | Ada yang belum dijawab → ditolak; satu pilihan per pertanyaan; contoh P4 tampil | WAJIB |
| FR-04 | Hitung skor, total, level rekomendasi dari tabel | Semua a → 4 Dasar; a,a,a,c → 6 Dasar; a,a,b,c → 7 Berkembang; c,b,b,b → 9 Berkembang; c,c,b,b → 10 Lanjutan; semua c → 12 Lanjutan; pilihan d P1/P2/P3 = skor 3 + `perlu_keputusan_admin`; ubah tabel mengubah hasil tanpa ubah kode | WAJIB |
| FR-05 | Saat dikirim: satu transaksi membuat pengajuan `menunggu`, penilaian, detail; tanpa akun/login; layar konfirmasi tanpa skor/level | Klik ganda → satu pengajuan; gagal di tengah → tidak ada data setengah jadi; layar tanpa skor/level | WAJIB |
| FR-06 | Notifikasi registrasi diterima | Tercatat; WA gagal → notifikasi aplikasi tetap ada | WAJIB |
| FR-07 | Login hanya akun yang ada dan `aktif` | Akun `nonaktif` ditolak dengan pesan; email/username/no WA yang masih pengajuan tidak bisa login | WAJIB |
| FR-08 | Admin melihat pengajuan `menunggu` | Hanya status itu tampil | WAJIB |
| FR-09 | Admin melihat detail usaha, jawaban, skor, total, rekomendasi | Sesuai data | WAJIB |
| FR-10 | Keputusan: setujui / level berbeda (alasan wajib) / tolak (alasan wajib) | Tanpa alasan ditolak; tersimpan level akhir, alasan, admin, tanggal | WAJIB |
| FR-11 | Persetujuan membuat `users` dan `umkm` dalam satu transaksi, kata sandi awal dari `INITIAL_PASSWORD` | Bawaan: login pertama langsung masuk ke dashboard dan muncul pop up saran ganti kata sandi yang boleh dilewati (setelah dilewati, pengguna diberi tahu bahwa penggantian ada di menu Profil UMKM, bagian Ubah password); pop up hanya muncul bila akun masih memakai kata sandi awal. Jika `FORCE_CHANGE_INITIAL_PASSWORD=true`: login pertama diarahkan ke ganti kata sandi dan fitur lain terkunci. Setujui dua kali → satu akun; tanpa masa kedaluwarsa | WAJIB |
| FR-12 | Notifikasi akun aktif atau penolakan | Isi sesuai keputusan FR-10 | WAJIB |
| FR-13 | Ganti kata sandi | Lama salah / konfirmasi beda → ditolak; berhasil → ter-hash, flag false | WAJIB |
| FR-14 | UMKM melihat skor per indikator, total, rekomendasi, level akhir, alasan | Alasan tampil hanya jika level akhir ≠ rekomendasi | WAJIB |
| FR-15 | Admin kelola indikator dan pilihan jawaban | Nonaktif tidak muncul di kuesioner baru; riwayat utuh | TUNDA (seeder) |
| FR-16 | Admin kelola kategori usaha dan jenisnya (barang, jasa, atau keduanya) | Nama kosong/duplikat ditolak; tidak ada hapus, hanya nonaktifkan; jenis tidak bisa diubah bila membuat UMKM atau pengajuan `menunggu` tidak sesuai | WAJIB |
| FR-17 | Admin atur aturan modul per rumpun | Perubahan memengaruhi menu UMKM; satu aturan per modul per rumpun | WAJIB |
| FR-18 | Admin atur laporan per level (layar sederhana) | Memengaruhi daftar laporan FR-20 | WAJIB |
| FR-19 | Tampilkan dan batasi fitur v1.0 sesuai `aturan_modul` | Jasa tanpa persediaan: bahan baku, kartu stok, metode persediaan tidak tampil dan URL ditolak; jasa dengan persediaan tampil; barang tanpa produksi: produksi tidak tampil; label Produk / Daftar Layanan Jasa; data lama utuh; UMKM lama dengan rumpun/flag kosong tidak disembunyikan apa pun | WAJIB |
| FR-20 | Laporan sesuai level (Tabel 3.9) | Level Dasar hanya laporan yang dipetakan untuk Dasar | WAJIB |
| FR-21 | Admin buka periode penilaian ulang; notifikasi ke UMKM aktif | Hanya UMKM `aktif` dinotifikasi | PENTING |
| FR-22 | UMKM isi penilaian ulang | Hanya saat `dibuka`; sekali per periode | PENTING |
| FR-23 | Hitung rekomendasi dan arah naik/tetap/turun | Label sesuai perbandingan; level tidak berubah otomatis | PENTING |
| FR-24 | Admin tinjau dan tetapkan level akhir (alasan wajib jika beda), catat pembinaan | Level baru berlaku setelah Admin menyimpan | PENTING |
| FR-25 | Perbarui level, simpan riwayat, notifikasi hasil | Riwayat dan transaksi lama tetap saat level turun | PENTING |
| FR-26 | Log audit | Minimal keputusan level: siapa, level lama/baru, alasan, kapan | TUNDA (sebagian) |
| FR-27 | Admin melengkapi jenis usaha, kategori, persediaan, produksi UMKM lama lewat halaman edit UMKM | Setelah diisi, aturan modul berlaku; sebelum diisi, tidak ada fitur disembunyikan; kategori harus sesuai jenis | WAJIB |
| FR-28 | Rute pendaftaran Admin publik ditutup | Akses tamu ke `/admin/register` → 404, tidak membuat akun. **Selesai K0 (2 tes lulus)** | WAJIB |

## 8. Notifikasi
`NotificationService` satu antarmuka, dua driver via `NOTIF_DRIVER`: **simulasi** (default; tulis ke tabel `notifikasi` dan log) dan **gateway WhatsApp** (diisi nanti; kunci hanya dari `.env`). Tidak memblokir proses utama. Templat pesan: `registrasi_diterima`, `akun_aktif`, `pengajuan_ditolak`, `periode_dibuka`, `hasil_penilaian` (teks pada master prompt Bagian 8). v1.0 sudah punya `WhatsAppService` (Fonnte) dan `notifikasi_log`; keduanya **tidak dipakai** v1.1 dan tidak diubah. Terpasang (K4a): `NotificationService` dengan driver simulasi (bawaan) dan kerangka driver WhatsApp (selalu mencatat gagal sampai penyedia dipilih). Tiap kejadian menulis dua baris di tabel `notifikasi` (whatsapp dan salinan aplikasi), dicatat dalam transaksi keputusan dan dikirim setelah respons; tanpa pengulangan kirim. Pesan WhatsApp `akun_aktif` memuat kata sandi awal sesuai templat, sedangkan salinan aplikasinya tidak. Kotak Notifikasi: Admin melihat semua, UMKM hanya salinan aplikasinya.

## 9. Temuan audit (terverifikasi K0) dan statusnya
- `/admin/register` terbuka → **sudah ditutup (K0)**.
- `/register` langsung membuat akun dan memilih level → **diganti pendaftaran pengajuan (K1)**. `UmkmLevelController` dan rute `/umkm/pilih-level` (UMKM memilih level sendiri) → **dihapus (K2)**; level hanya ditetapkan Admin.
- `loginUmkm` hanya memeriksa `user_group`; akun `nonaktif` masih bisa login → **diperbaiki (K1, FR-07)**.
- `updatePassword` belum meminta kata sandi lama → **diperbaiki (K2, FR-13)**; kata sandi baru juga harus berbeda dari yang lama.
- `umkm.feature` hanya dipasang pada ekspor, kartu stok, jurnal, COA, piutang; bahan baku, produk, pembelian, produksi, penjualan, beban, kasir belum → **diperbaiki (K3)** dengan middleware `umkm.modul` (aturan rumpun + level). Panel isi laporan di halaman Laporan sebelumnya tetap dirender untuk level yang tidak berhak → **diperbaiki (K3, FR-20)**. Fitur per level kini dibaca dari `umkm_level.fitur` di DB (FR-18).
- `ProduksiController::store` dinonaktifkan untuk MVP; menu produksi tidak ada di sidebar tetapi URL masih terbuka → dicatat, dibahas di K3.
- `UmkmLevelSeeder` kini memakai `updateOrCreate` (sebelumnya `truncate`, berisiko untuk `level_id` UMKM lama).
- `UserSeeder` berisi kata sandi dummy di kode; dipertahankan untuk pengembangan lokal.
- Berkas `a` dan `db_kadin` ter-track; dibiarkan.
- Zona waktu aplikasi kini WIB; data lama v1.0 tersimpan dalam UTC sehingga jam lama tampil 7 jam lebih awal.

## 10. Kebutuhan nonfungsional
- Kata sandi ter-hash; akses berbasis peran di server (uji dengan URL langsung).
- Validasi di server; pesan kesalahan berbahasa Indonesia.
- Transaksi basis data untuk registrasi + pengajuan, keputusan Admin, dan penilaian ulang; aksi ulang bersifat idempoten.
- Data pribadi diproses setelah persetujuan; rahasia hanya di `.env` (`INITIAL_PASSWORD`, `NOTIF_DRIVER`, `TIMEZONE`).
- Terbaca di komputer dan ponsel; formulir dan tabel sederhana; gaya v1.0.
- Kegagalan WA tidak menghambat proses.

## 11. Urutan pengerjaan
K0 (selesai) → K1 FR-01–05, 07 → K2 FR-08–14 → K3 FR-17–20, 27 → K4 FR-06, 12, 21–25 → K5 uji ujung ke ujung dan data demo (termasuk 3 UMKM contoh dan 1 pengajuan `menunggu`). Satu langkah per sesi; rencana dulu, kode setelah disetujui; tidak ada commit oleh agen.

### 11.1 Data demo (K5)
Seeder `Database\Seeders\Demo\DemoSeeder` (tidak masuk `DatabaseSeeder`, aman dijalankan ulang, ditolak di lingkungan produksi): Keripik Contoh Melati (barang, produksi, Lanjutan), Bengkel Contoh Jaya (jasa, persediaan, Berkembang), Pangkas Contoh Rapi (jasa, tanpa persediaan, Dasar), masing-masing dengan transaksi tiga bulan, serta pengajuan `menunggu` Tenun Contoh Lestari. Email `@contoh.test`, kata sandi dari `INITIAL_PASSWORD`.

```
php artisan migrate --seed
php artisan db:seed --class="Database\Seeders\Demo\DemoSeeder"
```

### 11.2 Temuan uji ujung ke ujung (K5, 2026-11-26)
Sudah diperbaiki:
- Halaman Laporan error 500 (`Class "Carbon" not found`) pada lima partial `umkm/laporan/partials/` (muncul sejak K3).
- Kode akun jurnal otomatis disamakan dengan COA bawaan: piutang `113`, HPP perpetual `501`, persediaan bahan `114` (keputusan pemilik), saldo awal bahan ke Modal Pemilik `311`. Baris jurnal lama di basis data tidak diubah.

Diperbaiki kemudian (2026-11-26, disetujui pemilik):
- Iuran bulanan mengikuti `umkm_level.iuran_bulanan` (Rp0 / 20.000 / 30.000, BR-15). Level beriuran Rp0 tidak ditagih. Nominal periode (atau `IURAN_DEFAULT`) hanya untuk UMKM tanpa level. Tagihan yang sudah ada tidak diubah.
- Halaman daftar dan tambah Produksi dialihkan ke dashboard (fitur nonaktif, MVP).
- Menu samping di layar kecil tidak lagi memotong konten dan dapat dibuka lewat tombol menu.

### 11.3 Aturan Laba Rugi dan Perubahan Modal (disetujui pemilik, 2026-11-26)
Dihitung di satu tempat, `App\Services\LabaModalService`, dipakai halaman Laporan, ekspor PDF, dan isian modal penilaian ulang.
- Pendapatan `4xx`. Beban operasional `503`-`508` dan `6xx`. HPP: akun `5xx` selainnya (`501`, `502`, `510`).
- HPP periodik = persediaan awal + pembelian (`510`) + saldo awal bahan yang dicatat pada periode itu + Beban Bahan Baku (`502`) - persediaan akhir.
- Modal akhir = modal awal + setoran modal + laba bersih - prive (`312`). Modal awal = akun `3xx` sebelum periode + laba/rugi terkumpul.
- Dashboard Admin masih menghitung sendiri dari jurnal; untuk UMKM periodik angkanya dapat berbeda dari halaman Laporan.

### 11.4 Admin menambah UMKM dan mengubah level (keputusan pemilik, 2026-11-26)
- **Tambah UMKM oleh Admin:** wajib jenis usaha, kategori yang sesuai, pertanyaan persediaan/produksi, kuesioner, nama usaha, dan persetujuan data pribadi. Level bawaan mengikuti rekomendasi; berbeda dari rekomendasi wajib beralasan. Penilaian `awal` tersimpan, tercatat di `log_audit` (`umkm.buat`), notifikasi `akun_aktif`. Akun memakai `INITIAL_PASSWORD`; wajib ganti hanya bila `FORCE_CHANGE_INITIAL_PASSWORD=true`.
- **Ubah level di Detail UMKM:** alasan wajib, level harus berbeda, tercatat di `log_audit` (`umkm.ubah_level`), dan UMKM diberi tahu (templat `hasil_penilaian`). Tidak membuat baris penilaian baru, jadi halaman Hasil Penilaian tetap menampilkan penilaian terakhir.

### 11.5 Tampilan HPP produk dan kata sandi (keputusan pemilik, 2026-11-26)
- Daftar Produk menampilkan estimasi HPP = resep x harga beli terakhir tiap bahan (sama dengan tombol estimasi, tanpa overhead). Tanpa resep atau ada bahan yang belum pernah dibeli: "Belum ada". Kolom Stok produk dihapus karena produksi nonaktif.
- Kotak Notifikasi Admin menyamarkan kata sandi awal; isi pesan yang tersimpan tidak diubah.

## 12. Keputusan yang sudah diambil (K0)
Pengenal login tetap email; nama level L2 "Berkembang"; batas skor 4–6 / 7–9 / 10–12; pilihan d omzet dan modal dipakai; bahan baku rumpun barang = `jika_melakukan_produksi`; produksi tetap nonaktif (MVP) dan tetap level Lanjutan; Tabel 3.9 = konfigurasi v1.0; kata sandi awal dari `INITIAL_PASSWORD`; hanya rumpun barang dan jasa; UMKM lama tidak disembunyikan fitur sampai rumpun dan flag terisi. K4: omzet dan modal pada penilaian ulang terisi otomatis dari pembukuan (omzet = pendapatan akun 4xx 12 bulan; modal = modal akhir per hari penilaian), angka apa adanya bila data kurang dari 12 bulan; angka sistem direkam di `data_sistem`; salinan aplikasi `akun_aktif` tanpa kata sandi; driver WhatsApp berupa kerangka; UMKM tanpa nomor WhatsApp tetap mendapat salinan aplikasi.

Terbuka: nama level L3 ("Lanjut" di basis data vs "Lanjutan" di master prompt).
