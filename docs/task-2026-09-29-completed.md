# Pembaruan katalog & layanan publik — 29 September 2026

Implementasi instruksi `docs/_task.md`.

## Pembaruan 30 September 2026 — Excel dan pemisahan format

- Tombol **Unduh Excel (.xlsx)** tersedia di katalog dan laporan tahunan; CSV tetap tersedia. Endpoint: `/catalog/export?format=xlsx` dan `/catalog/annual-report?format=xlsx`. Keduanya mengikuti filter, cakupan perpustakaan, dan izin ekspor yang sama dengan CSV.
- XLSX asli menggunakan OOXML/ZIP, bukan CSV atau HTML yang diganti ekstensi. Header dibekukan, filter kolom tersedia, angka hitungan tetap numerik, ISBN/identitas tetap teks, dan teks menyerupai formula tidak dieksekusi. Penulisan dilakukan bertahap untuk katalog besar. Batas teks per sel Excel adalah 32.767 unit UTF-16; teks lebih panjang dipotong pada ekspor Excel, sedangkan CSV mempertahankan teks lengkap.
- Laporan membedakan judul fisik, digital, keduanya, belum teridentifikasi, eksemplar fisik, serta kumulatif fisik dan digital. File Excel dan CSV memuat pemisahan yang sama.
- Fisik berarti memiliki item non-digital yang cocok dengan filter/cakupan; media kosong/tidak diketahui pada inventaris lama tetap dianggap fisik. Penanda item digital dikenali dari jenis Ebook/e-book/e book/buku digital/digital/audiobook atau media Digital/PDF/EPUB/Ebook/e-book/audiobook, tanpa membedakan kapitalisasi dan spasi tepi. Item tersebut bukan eksemplar fisik. Judul dengan aset digital aktif juga dihitung digital, termasuk akses internal. Aset draft/arsip saja tidak cukup untuk mengklasifikasikan digital.
- Satu judul boleh memiliki kedua format, tetapi total judul tetap unik: **fisik + digital − keduanya + belum teridentifikasi**. Judul tanpa item maupun penanda digital tidak otomatis dianggap fisik. Tanggal tetap mengikuti tahun masuk katalog; laporan bukan histori waktu unggah aset digital atau histori perubahan format.
- Tidak ada perubahan skema database untuk pembaruan ini.

Verifikasi tambahan: `php tools/test_catalog_excel.php` menjalankan pemeriksaan dasar berikut pengujian OOXML, teks/angka, rekonsiliasi format, fixture fisik/digital/keduanya, tanggal tidak diketahui, filter, dan cakupan perpustakaan. Fixture hanya menggunakan tabel sementara pada koneksi pengujian, tanpa mengubah buku produksi. Unduhan katalog penuh dan laporan dibuka/disimpan ulang dengan LibreOffice; halaman dan tombol diuji pada lebar 1440 dan 390 piksel.

## Fitur

### Footer landing page — 30 September 2026

Footer baru terinspirasi susunan identitas, jadwal, sosial, dan kontak pada https://rembangkab.go.id/, dengan tampilan navy–emas, ajakan membaca, ilustrasi buku CSS, pintasan layanan, tautan Pemkab/Data Rembang/OneSearch, dan tombol kembali ke atas. Diterapkan melalui `application/views/partials/landing_footer.php` dan `assets/css/pustaka-footer.css`.

Kontak mengikuti pengaturan `/patron-feedback`; kanal kosong disembunyikan. Jadwal memakai `application/config/library_public.php`, sama dengan jadwal di atas peta. Tidak menyalin nomor telepon, alamat, atau jam kerja kantor Pemkab sebagai informasi perpustakaan. Tata letak diuji pada lebar 1440, 1024, 390, dan 320 piksel; ruang bawah disediakan agar informasi footer tidak tertutup navigasi ponsel.

### Fitur sebelumnya

- `/catalog`: **Unduh katalog (CSV)** mengunduh seluruh hasil filter, bukan hanya halaman aktif. Memuat 27 kolom, termasuk judul, pengarang, tahun terbit, penerbit, ISBN, subjek, abstrak, kategori, klasifikasi, sumber, perpustakaan, dan jumlah eksemplar. Cakupan perpustakaan serta izin `catalog.index.can_export` tetap berlaku. CSV menggunakan UTF-8 BOM dan perlindungan formula spreadsheet.
- `/catalog/annual-report`: tabel, visual batang, kumulatif, perubahan tahunan, unduhan CSV, dan cetak. Tautan dari katalog mempertahankan filter.
- `/patron-feedback`: pengaturan WhatsApp, Instagram, dan TikTok. Izin `patron_feedback.index.can_edit` diperlukan untuk menyimpan; terdapat pemeriksaan metode POST, token formulir, validasi, dan audit perubahan. Kolom kosong menyembunyikan kanal.
- `/suara-pemustaka`: kartu kontak dengan tautan langsung. Nomor lokal WhatsApp dikonversi ke awalan 62 pada URL.
- Beranda: jam layanan di atas peta, penanda jadwal hari ini dalam zona waktu Asia/Jakarta, menu Profil Perpustakaan, dan Tautan ke Data Rembang / Indonesia OneSearch. Navigasi juga tersedia di ponsel.
- `/profil-perpustakaan`: template profil responsif, ilustrasi buku berbasis CSS, bagian layanan, kelembagaan, jam kunjungan, dan kontak.

## Catatan data

Laporan perkembangan menggunakan **tahun masuk katalog**, bukan tahun terbit. Untuk sumber `inlislite_v3`, tanggal asli `inlislite_v3.catalogs.CreateDate` digunakan agar tahun migrasi tidak dianggap sebagai tahun masuk semua katalog. Sumber lain memakai `books.created_at`.

Laporan mencakup judul yang belum dihapus saat laporan dibuat; bukan rekonstruksi inventaris historis. Kolom eksemplar menghitung eksemplar yang masih ada sekarang pada kelompok judul tersebut, bukan pengadaan eksemplar pada tahun tersebut. Tanggal kosong, di luar 1900–tahun berjalan, atau tidak tersedia dipisahkan dari kumulatif. Tahun kosong di antara tahun pertama dan tahun berjalan ditampilkan dengan nol. Perubahan tahunan tidak dihitung jika pembandingnya nol.

## Pengaturan & migrasi

Migrasi `sql/2026-09-29a_patron_feedback_contacts.sql` **sudah diterapkan pada workspace ini**. Pada instalasi lain jalankan migrasi tersebut sebelum menggunakan penyimpanan kontak. Dapat dijalankan ulang tanpa menimpa kontak yang telah disimpan.

Kontak awal:

- WhatsApp: `085165805518`
- Instagram: `dinarpusrembang`
- TikTok: `perpustakaan.umum.rbg`

Jadwal dan isi template profil berada di `application/config/library_public.php`. Bagian sejarah/visi-misi sengaja berupa placeholder karena naskah resmi belum diberikan. Lengkapi `profile_history` dengan materi yang disetujui pengelola; tidak ada sejarah institusi yang dikarang.

## Verifikasi

`php tools/test_public_catalog_updates.php`

30 pemeriksaan lulus: 14.745 judul diekspor tanpa duplikasi, rekonsiliasi laporan dan eksemplar, filter, cakupan perpustakaan, validasi/normalisasi kontak, penyimpanan dan penyembunyian kontak dalam transaksi yang di-rollback, izin ekspor/edit, dan pengamanan CSV.

Browser Chromium diuji pada 1440 × 1000 dan 390 × 844: beranda, profil, dan formulir publik tidak mengalami overflow halaman atau error JavaScript. Jam layanan berada sebelum peta. Halaman katalog, laporan, dan pengaturan admin diuji dengan sesi sintetis pada server loopback terisolasi; tidak menggunakan sesi pengguna. Unduhan CSV memberikan HTTP 200, token kontak salah ditolak HTTP 403, dan input kontak tidak valid menampilkan pesan validasi. Akses anonim ke ekspor diarahkan ke login.
