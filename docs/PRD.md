# PRD — MINECT v1.1 (Product Requirements Document)

Sumber: `MASTER_PROMPT_MINECT_v1.1.md` + audit kode MINECT v1.0 (cabang `ismi`). Status: draf untuk ditinjau.

## 1. Latar belakang
MINECT (Micro Connect) membantu pencatatan transaksi dan informasi keuangan UMKM binaan KADIN Kabupaten Bengkalis. v1.0 sudah berjalan: akun UMKM dibuat Admin atau registrasi sederhana, UMKM memilih level sendiri, fitur dibatasi per level (L1–L3), serta ada pembelian, penjualan, persediaan, produksi, piutang, jurnal, laporan, iuran (Midtrans), dan tiket.

Masalah yang ditangani v1.1 (saran penelitian TA):
1. Level masih dipilih sendiri oleh UMKM, bukan berdasarkan kesiapan yang dinilai.
2. Jenis usaha tidak memengaruhi fitur: usaha jasa tetap melihat bahan baku, kartu stok, dan produksi.

## 2. Tujuan
- G1: Registrasi mandiri disertai kuesioner 4 indikator; sistem merekomendasikan level (Dasar/Berkembang/Lanjutan); **Admin KADIN yang menetapkan level**.
- G2: Fitur operasional disesuaikan menurut rumpun usaha (barang/jasa), kepemilikan persediaan, dan aktivitas produksi, tanpa membangun ulang fitur v1.0.
- G3: Penilaian ulang berkala dengan rekomendasi naik/tetap/turun; Admin menetapkan.
- G4: Notifikasi satu arah (driver simulasi dahulu).
- Tujuan akademik: sistem berjalan ujung ke ujung, bisa didemokan dan dijelaskan mahasiswa di depan penguji.

## 3. Pengguna
| Peran | Deskripsi |
|---|---|
| UMKM (`pelakuusaha`) | Pelaku usaha mikro binaan KADIN; mendaftar, mengisi kuesioner, memakai fitur sesuai level dan rumpun |
| Admin KADIN (`admin`) | Memverifikasi pengajuan, menetapkan level, membuka periode penilaian ulang, mengatur aturan modul dan laporan |

Hanya dua peran; tidak ada Super Admin; akun Admin dibuat lewat seeder (BR-05).

## 4. Ruang lingkup
**Dalam cakupan (v1.1):** registrasi mandiri, pemilihan jenis usaha (barang/jasa) dan kategori usaha, pengelolaan kategori usaha oleh Admin, kuesioner dan skor, verifikasi Admin, aktivasi dan ganti kata sandi, aturan modul per rumpun, laporan per level, penilaian ulang, notifikasi simulasi, log audit keputusan level.

**Di luar cakupan:** membangun ulang fitur operasional v1.0; format laporan SAK EMKM/posisi keuangan/CALK; WhatsApp nyata dan chatbot dua arah; pengingat otomatis; CRUD indikator (cukup seeder); kedaluwarsa dan kirim ulang kata sandi awal. Catatan: Admin tetap melengkapi jenis usaha, persediaan, dan produksi UMKM lama lewat halaman edit UMKM yang sudah ada (FR-27).

## 5. Fitur utama dan prioritas (mode tenggat)
| Prioritas | Fitur | FR |
|---|---|---|
| WAJIB | Registrasi, jenis dan kategori usaha, kuesioner, skor, status, login hanya akun aktif | FR-01–05, 07 |
| WAJIB | Verifikasi Admin, aktivasi, ganti kata sandi, hasil penilaian untuk UMKM | FR-08–14 |
| WAJIB | Aturan modul per rumpun, laporan per level, pembatasan akses di server | FR-16–20 |
| WAJIB | Kotak Notifikasi (driver simulasi) | FR-06, 12 |
| PENTING | Penilaian ulang | FR-21–25 |
| TUNDA | CRUD indikator; log audit penuh | FR-15, 26 (sebagian) |

## 6. Alur pengguna ringkas
1. UMKM registrasi → memilih jenis usaha (barang/jasa) dan kategori usaha → menjawab pertanyaan persediaan/produksi → mengisi kuesioner → pengajuan berstatus `menunggu` tanpa akun (skor dan level tidak terlihat).
2. Admin meninjau jawaban, skor, dan rekomendasi → setujui / setujui level berbeda (alasan wajib) / tolak (alasan wajib).
3. Disetujui: akun aktif, kata sandi awal dari `.env`, wajib ganti saat login pertama; UMKM melihat skor, level, dan alasan.
4. UMKM memakai menu sesuai jenis usaha dan level. Tahunan: Admin membuka periode, UMKM mengisi ulang, sistem memberi arah, Admin menetapkan level akhir.

## 7. Penilaian kesiapan (ringkas)
Empat pertanyaan pilihan ganda (omzet, modal, tenaga kerja, kebiasaan pencatatan), skor pilihan 1–3, total 4–12. Dasar 4–6, Berkembang 7–9, Lanjutan 10–12 (usulan penelitian, bukan klasifikasi resmi; batas sudah dikonfirmasi pada K0). Pilihan d pada pertanyaan 1–3 (di luar batas mikro) diberi skor 3 dan ditandai agar Admin yang memutuskan. Detail di SRS.

## 8. Kriteria keberhasilan
- Seluruh kriteria uji FR WAJIB lulus lewat pengujian otomatis.
- Pengujian v1.0 tidak rusak.
- Demo ujung ke ujung dengan 3 UMKM contoh (barang; jasa dengan persediaan; jasa tanpa persediaan) dan 1 Admin.
- Akses URL langsung ke fitur yang disembunyikan ditolak.

## 9. Asumsi, risiko, dan keputusan terbuka
Keputusan yang sudah diambil (K0): batas skor 4–6 / 7–9 / 10–12 dipakai; pilihan di atas batas mikro dipakai untuk omzet, modal, dan tenaga kerja (diputuskan Admin); bahan baku rumpun barang = `jika_melakukan_produksi`; LIFO v1.0 dipertahankan tanpa perubahan; pengenal login tetap email; hanya rumpun barang dan jasa; `/admin/register` sudah ditutup; produksi tetap nonaktif (MVP).

Masih terbuka:
- Dasar indikator omzet dan modal mengukur ukuran usaha, bukan kesiapan; menunggu dosen akuntansi.
- Pilihan d untuk omzet dan modal belum ada di Tabel 3.6 buku; ubah buku atau hapus lewat seeder.
- Tabel 3.9 (laporan per level) belum ada di buku; matriks diambil dari konfigurasi v1.0 dan perlu ditambahkan ke buku TA.
- Nama level L3: "Lanjut" (basis data) atau "Lanjutan".
- Risiko: skema v1.0 berbeda dari model data master prompt (pemetaan di SRS bagian 5); sisa temuan audit di SRS bagian 9.
