# Roadmap Pustaka Digital Rembang

Tanggal mulai: 2026-07-28
Lokasi proyek: `C:\xampp\htdocs\pustaka`
Basis awal: CodeIgniter 3.1.13, database operasional `pustaka`, sumber migrasi `inlislite_v3`

## Visi Produk

Membangun platform perpustakaan digital terpadu untuk seluruh perpustakaan di Kabupaten Rembang. Aplikasi ini melayani katalog terpadu, keanggotaan digital, baca buku online, manajemen perpustakaan jejaring, pojok baca digital berbasis GPS dan kuota, agenda kegiatan literasi, serta dashboard administrasi berjenjang.

## Sasaran Utama

- Satu portal katalog dan koleksi digital untuk Perpusda, sekolah, desa, dan swasta.
- Data katalog dan koleksi dapat diambil dari INLISLite tanpa mewarisi seluruh kompleksitas database lama.
- Member memiliki kartu digital, histori baca, token/kuota akses, dan akses berbasis lokasi.
- Admin memiliki dashboard sesuai level kewenangan.
- Buku digital dapat dibaca secara online dengan proteksi berlapis dan opsi download hanya untuk koleksi yang diberi izin.
- Pojok baca digital dapat dibuka di titik tertentu, misalnya area kerja sama seperti Namua, sekolah, desa, atau ruang publik lain.

## Checklist Roadmap

### Fase 0 - Fondasi dan Discovery

- [x] Membuat dokumentasi visi produk.
- [x] Membuat roadmap awal.
- [x] Scan ulang folder INLISLite dan aset terkait.
- [x] Import database acuan `inlislite_v3`.
- [x] Menetapkan database operasional baru `pustaka`.
- [x] Menyiapkan CodeIgniter 3 sebagai basis aplikasi.
- [x] Membersihkan risiko Git dari folder source `tabler-dev`.
- [x] Membuat standar coding dan handover developer.
- [ ] Membuat ERD final aplikasi baru.
- [ ] Menentukan strategi sinkronisasi INLISLite: live DB, dump periodik, atau job import.

### Fase 1 - MVP Admin dan Data Master

- [x] Login dan logout berbasis database.
- [x] Role awal `SUPERADMIN`, `ADMIN`, dan `USER`.
- [x] Redirect login berbeda antara admin dan pemustaka.
- [x] Admin panel dasar.
- [x] Sidebar admin berbasis database.
- [x] Modul RBAC terpadu: user, role, registry halaman, sidebar.
- [x] Landing page publik di root `/`.
- [x] Dashboard pemustaka terpisah dari admin panel.
- [x] Schema dan CRUD awal Perpustakaan GIS.
- [x] Peta Leaflet untuk titik perpustakaan.
- [x] Upload foto perpustakaan: hapus foto dan set cover.
- [x] Master kecamatan dan Desa / Kelurahan Rembang.
- [x] UI CRUD Master Wilayah.
- [x] Pembatasan `library_id` untuk admin lokal.
- [x] Dashboard admin dengan widget operasional awal.
- [x] Audit log untuk perubahan user, role, sidebar, dan perpustakaan.

### Fase 2 - Katalog dan Sinkronisasi INLISLite

- [x] Scan ulang cakupan data katalog, eksemplar, member, sirkulasi, OPAC, dan aset INLISLite.
- [x] Mapping final bibliografi, eksemplar, cover, dan file digital dari INLISLite.
- [x] Schema katalog aplikasi baru.
- [x] Import awal katalog dari `inlislite_v3`.
- [x] Import eksemplar/koleksi.
- [x] Import cover dan aset buku.
- [x] Modul migrasi aset batch dan mirror penuh `uploaded_files` INLISLite.
- [x] Pencarian katalog publik.
- [x] Detail buku dengan lokasi eksemplar.
- [x] CRUD eksemplar/item buku dari detail katalog.
- [x] Kurasi mapping master INLISLite untuk label kategori, lokasi, aturan pinjam, media, sumber, dan status koleksi.
- [x] CRUD manual katalog untuk kurasi metadata awal.
- [x] Fondasi tabel sinkronisasi dan mapping ID.
- [ ] Job sinkronisasi ulang dan log perubahan.

### Fase 3 - Membership Digital

- [x] Schema anggota aplikasi baru.
- [x] Fondasi akun login member hasil migrasi INLISLite.
- [x] Import anggota dari INLISLite ke `members` dan `auth_user` sampai 5.389 sumber selesai.
- [x] Migrasi foto member lokal untuk member yang sudah masuk aplikasi.
- [x] CRUD manual member dan profil admin.
- [x] Kurasi mapping master INLISLite untuk label jenis identitas, gender, jenis anggota, pendidikan, pekerjaan, dan status anggota.
- [x] Kartu anggota digital dengan QR.
- [x] Nomor anggota manual baru otomatis dengan format `PDR-3317-YYYY-000001`.
- [x] Username login member memakai NIK jika tersedia dan password awal `perpus2026`.
- [x] Status membership.
- [x] Absensi kunjungan perpustakaan.
- [x] Riwayat kunjungan dan pinjam dasar dari INLISLite untuk detail member.
- [x] Label master dasar untuk kunjungan dan hak pinjam transaksi INLISLite.
- [x] UI data transaksi harian untuk kunjungan tamu, hak pinjam, transaksi pinjam, dan detail item.
- [x] Reset password dan wajib ganti password awal.
- [x] Form pendaftaran member online dengan upload foto, KTP, KK, dan surat pendukung luar Rembang.
- [x] Verifikasi admin untuk pendaftaran online sebelum akun member aktif.
- [x] Perpanjangan membership dari dashboard/admin.
- [x] Blokir/aktifkan kartu digital dengan alasan operasional.
- [x] Reservasi atau request buku dari katalog publik.

### Fase 4 - Koleksi Digital dan Reader Aman

- [x] Storage file digital non-public.
- [x] Fondasi kebijakan akses per buku: baca online, download bebas, lokasi, member, internal.
- [x] Enforcement penuh kebijakan akses per buku di reader publik.
- [x] Upload PDF dari admin.
- [x] Control room aset PDF dan route reader terproteksi awal.
- [x] Reader online berbasis halaman/token.
- [x] Watermark dinamis.
- [x] Fondasi tabel audit sesi baca.
- [x] Rate limit dan audit akses konten penuh.
- [x] Opsi download hanya untuk koleksi yang diizinkan.
- [ ] Deteksi anomali baca/scrape.

### Fase 5 - Pojok Baca Digital

- [x] Schema titik pojok baca.
- [x] CRUD titik, mitra, koordinat, radius, jam aktif, dan kuota.
- [ ] Galeri/foto titik Pojok Baca.
- [x] QR lokasi dan GPS lock.
- [x] Check-in member.
- [x] Fondasi tabel kuota/token baca.
- [x] Penerbitan token/check-in dari UI.
- [ ] Koleksi khusus per titik.
- [x] Control room Pojok Baca dengan ringkasan titik, token, dan sesi.
- [ ] Dashboard penggunaan titik lanjutan.
- [ ] Pilot mitra, misalnya Namua.

### Fase 6 - Event dan Engagement

- [x] Schema event.
- [ ] CRUD event untuk Perpusda dan perpustakaan terintegrasi.
- [x] Fondasi tabel pendaftaran peserta.
- [ ] UI pendaftaran peserta.
- [ ] QR attendance/check-in.
- [x] Fondasi kuota peserta.
- [ ] Dokumentasi foto event.
- [ ] Sertifikat digital.
- [ ] Laporan event.

### Fase 7 - Hardening, Audit, dan Rilis

- [ ] CSRF aktif dan form disesuaikan.
- [ ] Backup dan restore database.
- [ ] SOP import/sinkronisasi INLISLite.
- [x] Sinkronisasi transaksi harian INLISLite: kunjungan, hak pinjam, transaksi pinjam, item pinjam.
- [ ] Monitoring error.
- [ ] Optimasi performa search.
- [x] Security review PDF reader dan akses file.
- [ ] Uji role admin lokal/sekolah/desa/swasta.
- [ ] Pelatihan admin.
- [ ] Pilot terbatas.
- [ ] Rilis bertahap.

## Ruang Lingkup Modul

### 1. Identitas dan Hak Akses

Role konseptual jangka panjang:

- Superadmin: mengelola seluruh sistem, data master, konfigurasi keamanan, semua perpustakaan, dan audit.
- Admin: mengelola operasional kabupaten atau Perpusda.
- Admin sekolah: mengelola perpustakaan sekolah dan koleksi/agenda sekolah.
- Admin desa: mengelola perpustakaan desa, titik pojok baca desa, dan laporan lokal.
- Admin swasta/mitra: mengelola perpustakaan swasta atau mitra yang terdaftar.
- Pemustaka: mencari katalog, membaca koleksi digital, mengelola membership, mengikuti event, dan memakai kuota pojok baca.

Role implementasi awal:

- `SUPERADMIN`: akses penuh seluruh sistem, konfigurasi, user, role, sidebar, dan audit.
- `ADMIN`: akses operasional untuk modul perpustakaan, katalog, anggota, pojok baca, dan event sesuai unit yang ditugaskan.
- `USER`: akses pemustaka/member untuk katalog, membership, event, dan layanan digital.

Fitur:

- Login aman, reset password, verifikasi email/nomor HP jika diperlukan.
- Role Based Access Control.
- Registry halaman dan matrix permission disimpan di database.
- Sidebar/menu disimpan di database agar dapat dikelola lewat UI.
- Pembatasan data per perpustakaan untuk admin lokal.
- Audit log untuk aksi penting.
- Opsi verifikasi dua langkah untuk admin.

### 2. Direktori Perpustakaan Terintegrasi

Data perpustakaan:

- Nama resmi, jenis perpustakaan, pengelola, alamat, kontak, jam layanan.
- Kategori: Perpusda, sekolah, desa, swasta, komunitas, mitra.
- Titik koordinat, radius layanan GPS, status aktif/nonaktif.
- Foto gedung/ruang, logo, galeri, fasilitas.
- Statistik koleksi, anggota, kunjungan, dan aktivitas baca.

### 3. Katalog dan Database Buku

Fitur publik:

- Pencarian katalog terpadu.
- Filter berdasarkan perpustakaan, jenis bahan, subjek, tahun, penulis, penerbit, ketersediaan digital, dan ketersediaan fisik.
- Detail bibliografi, cover, lokasi eksemplar, status koleksi, dan link baca digital jika tersedia.

Fitur admin:

- Sinkronisasi katalog dari INLISLite.
- Kurasi metadata dan cover.
- Manajemen eksemplar.
- Status publikasi OPAC/digital.
- Import PDF atau file digital.

### 4. Koleksi Digital dan Reader PDF

Mode akses:

- Baca online terbatas.
- Baca online bebas.
- Download diizinkan.
- Download dilarang.
- Akses khusus lokasi/pojok baca.
- Akses khusus membership/role.

Proteksi utama:

- File asli tidak berada di public folder.
- PDF tidak diberikan sebagai URL langsung.
- Reader meminta halaman melalui endpoint server dengan signed token.
- Token pendek umur, terikat user, device/session, buku, halaman, dan izin akses.
- Watermark dinamis berisi identitas member, timestamp, dan ID sesi baca.
- Rate limit per halaman dan per sesi.
- Logging buka halaman, durasi baca, perangkat, IP, dan lokasi.
- Deteksi anomali seperti scrape cepat, banyak perangkat, atau token reuse.

Catatan penting: tidak ada anti-download 100 persen di web jika halaman sudah dapat dilihat di layar. Target realistis adalah mencegah download file asli, menghambat scraping, memberi jejak watermark, dan membatasi penyalahgunaan.

### 5. Membership Digital

Fitur:

- Kartu anggota digital dengan QR code.
- Status aktif, kedaluwarsa, suspend, atau perlu verifikasi.
- Integrasi data anggota dari INLISLite.
- Riwayat peminjaman fisik jika disinkronkan.
- Riwayat baca digital.
- Absensi kunjungan perpus.
- Perpanjangan membership.
- Verifikasi identitas untuk akses koleksi tertentu.

### 6. Pojok Baca Digital

Konsep:

- Titik lokasi resmi yang dikunci GPS.
- Member check-in di lokasi untuk mendapatkan kuota/token baca.
- Kuota berlaku dalam periode tertentu dan untuk koleksi tertentu.
- Jika kuota habis, member perlu absensi ulang di perpus atau titik yang ditentukan.

Data titik:

- Nama titik, mitra, alamat, koordinat, radius, jam aktif.
- Kuota default, koleksi yang diizinkan, aturan per user/per hari.
- Foto titik, PIC, status kerja sama.

Fitur:

- Check-in GPS dengan toleransi radius.
- Token baca digital.
- Dashboard penggunaan titik.
- Laporan kunjungan dan judul populer.
- Mode kerja sama mitra, misalnya Namua.

### 7. Event dan Literasi

Fitur:

- Agenda event Perpusda dan semua perpustakaan terintegrasi.
- Pendaftaran peserta.
- QR attendance/check-in.
- Kuota peserta.
- Dokumentasi foto.
- Sertifikat digital jika dibutuhkan.
- Laporan peserta dan engagement.

### 8. Dashboard dan Pelaporan

Dashboard superadmin/admin:

- Jumlah perpustakaan aktif.
- Jumlah katalog, eksemplar, koleksi digital.
- Jumlah anggota, anggota aktif, anggota baru.
- Aktivitas baca digital, top judul, top perpustakaan.
- Aktivitas pojok baca digital.
- Event berjalan dan statistik peserta.
- Alert keamanan dan anomali.

Dashboard admin lokal:

- Data perpustakaan sendiri.
- Koleksi sendiri.
- Member terkait.
- Event sendiri.
- Statistik penggunaan lokal.

### 9. Ide Tambahan

- Rekomendasi buku berdasarkan usia, jenjang, minat, dan histori baca.
- Tantangan membaca per sekolah/desa.
- Badge literasi digital untuk pemustaka.
- Rak digital tematik: Rembang, sejarah lokal, UMKM, pertanian, pendidikan, anak, remaja.
- Koleksi lokal Rembang: naskah, foto arsip, cerita rakyat, produk budaya, dokumentasi event.
- Usulan pengadaan buku dari pemustaka.
- Moderasi ulasan dan rating buku.
- Statistik dampak literasi per kecamatan.
- Mode kiosk untuk pojok baca digital dengan akun perangkat khusus.
- API integrasi untuk aplikasi mobile di fase lanjut.

## Arsitektur Awal

Rekomendasi konservatif untuk fase awal:

- Tetap gunakan CodeIgniter 3 yang sudah ada untuk prototipe cepat di XAMPP.
- Buat database aplikasi baru yang lebih bersih bernama `pustaka`.
- INLISLite diperlakukan sebagai source/staging, bukan schema operasional utama.
- Sinkronisasi dilakukan dengan ETL terjadwal atau command manual.
- File digital disimpan di storage non-public.
- Reader PDF memakai endpoint server, bukan link file langsung.

Alternatif fase menengah:

- Jika proyek berkembang besar, migrasi bertahap ke Laravel atau CodeIgniter 4 dapat dipertimbangkan setelah MVP stabil.
- API-first dapat disiapkan sejak awal agar mobile app dan kiosk lebih mudah dibuat.

## Fase Pengembangan

### Fase 0 - Fondasi dan Discovery

Target:

- Dokumentasi visi, roadmap, dan progress.
- Audit struktur INLISLite.
- Definisi schema aplikasi baru.
- Keputusan stack teknis final.
- Rancangan role dan permission.

Output:

- Dokumen roadmap.
- Mapping tabel INLISLite.
- ERD awal aplikasi baru.
- Backlog MVP.

### Fase 1 - MVP Admin dan Data Master

Target:

- Login admin.
- Role dasar.
- Dashboard admin awal.
- CRUD perpustakaan terintegrasi.
- Upload foto perpustakaan.
- Koordinat dan radius layanan.
- Data master jenis perpustakaan dan kategori.

Output:

- Admin dapat mengelola daftar perpustakaan.
- Hak akses superadmin/admin lokal mulai berjalan.

### Fase 2 - Katalog dan Sinkronisasi INLISLite

Target:

- Import katalog dari INLISLite.
- Import eksemplar/koleksi.
- Import anggota minimal.
- Pencarian katalog publik.
- Detail buku dan lokasi eksemplar.

Output:

- Portal katalog terpadu versi awal.
- Data INLISLite masuk ke schema baru lewat mapping.

### Fase 3 - Membership Digital

Target:

- Akun pemustaka.
- Kartu digital QR.
- Profil dan status membership.
- Absensi kunjungan.
- Riwayat baca dan pinjam dasar.

Output:

- Pemustaka dapat login dan memakai membership digital.

### Fase 4 - Koleksi Digital dan Reader Aman

Target:

- Upload file PDF.
- Kebijakan akses per buku.
- Reader online dengan halaman/token.
- Watermark dinamis.
- Audit akses konten.
- Opsi koleksi bebas download.

Output:

- Buku digital dapat dibaca online dengan proteksi berlapis.

### Fase 5 - Pojok Baca Digital

Target:

- CRUD titik pojok baca.
- GPS lock dan radius.
- Check-in member.
- Kuota/token baca.
- Dashboard penggunaan titik.

Output:

- Member dapat membaca koleksi di titik tertentu sesuai kuota.

### Fase 6 - Event dan Engagement

Target:

- CRUD event.
- Pendaftaran peserta.
- Check-in QR.
- Dokumentasi event.
- Laporan event.

Output:

- Semua perpustakaan jejaring dapat mengelola agenda literasi.

### Fase 7 - Hardening, Audit, dan Rilis

Target:

- Pengujian keamanan.
- Backup dan restore.
- Monitoring error.
- Optimasi performa search.
- Pelatihan admin.
- SOP operasional.

Output:

- Sistem siap pilot dan rilis bertahap.

## Prioritas MVP

1. Admin login dan role.
2. Direktori perpustakaan lengkap dengan koordinat dan foto.
3. Import katalog dan koleksi dari INLISLite.
4. Portal pencarian buku.
5. Membership digital.
6. Reader PDF aman versi awal.
7. Pojok baca digital versi pilot.
8. Event perpustakaan.

## Checklist Roadmap Teknis

- [x] RBAC, sidebar database, dan admin panel dasar.
- [x] GIS perpustakaan dan master wilayah Rembang.
- [x] Sinkronisasi katalog/member dari INLISLite ke database `pustaka`.
- [x] Migrasi aset INLISLite awal dan audit aset missing/failed.
- [x] Katalog publik dengan filter, reset, cover, detail, dan request buku.
- [x] Membership digital, kartu digital, verifikasi kartu, dan perpanjangan.
- [x] Layanan harian dari data transaksi INLISLite.
- [x] Pendaftaran member online dengan upload berkas dan dashboard pending.
- [x] Master kategori konten dan klasifikasi isi buku untuk pencarian yang lebih manusiawi.
- [x] CRUD Pojok Baca dengan radius, kuota, dan map picker draggable.
- [x] Check-in Pojok Baca tahap awal dan penerbitan token/kuota harian.
- [x] Pemakaian token Pojok Baca di Reader PDF, pengurangan kuota, dan audit sesi baca.
- [x] Reader PDF aman dengan storage non-public, render per halaman, watermark, rate limit, dan audit.
- [ ] CRUD event literasi lengkap, pendaftaran peserta, QR attendance, dan dokumentasi event.
- [x] Laporan operasional dan export.
- [ ] Hardening keamanan, backup/restore, dan SOP pilot.

## Review Status 2026-08-03

### Selesai dan tervalidasi lokal

- Fondasi aplikasi CI3, login database, session 3 hari, RBAC, sidebar database, dan admin panel.
- Landing publik, katalog publik, dashboard member, kartu digital, pendaftaran online, dan perpanjangan membership.
- GIS perpustakaan, master wilayah Rembang, Pojok Baca dengan map picker draggable, token/kuota, dan monitoring token.
- Migrasi katalog, eksemplar, member, foto, cover, file digital, dan transaksi harian dari INLISLite lokal.
- Katalog admin/publik dengan filter, kategori isi, klasifikasi isi, detail buku, request buku, CRUD buku, dan CRUD eksemplar.
- Membership admin dengan CRUD, akun login member NIK/password `perpus2026`, verifikasi pendaftaran, blokir/aktif kartu digital, dan histori aktivitas.
- Buku tamu/layanan harian: monitor QR dinamis, pencarian AJAX member by NIK/nomor/nama, tamu rombongan, dan laporan kunjungan.
- Laporan kunjungan dengan filter tahun/bulan/hari/custom, grafik, export print/PDF browser, dan export Excel `.xls`.
- Reader aman:
  - storage PDF non-public,
  - akses langsung storage ditolak,
  - PDF utuh hanya untuk `download_allowed`,
  - aset non-downloadable dirender server-side per halaman PNG,
  - watermark tertanam,
  - token sesi,
  - rate limit,
  - audit `reader_access_logs`,
  - gesture tap/swipe/keyboard.
- Manajemen ebook admin: upload PDF, edit policy, hak publikasi, status draft/aktif/arsip, preview admin aman, dan audit reader detail.

### Selesai sebagai fondasi, masih perlu diperdalam

- Sinkronisasi INLISLite masih manual/batch berbasis database lokal; belum menjadi job terjadwal dengan diff log perubahan yang rapi.
- Event literasi sudah punya schema dan fondasi controller/view, tetapi CRUD lengkap, pendaftaran peserta, QR attendance, dokumentasi, dan laporan event belum matang.
- Pojok Baca sudah punya check-in/token/reader, tetapi belum ada galeri/foto titik, koleksi khusus per titik, dan dashboard pemanfaatan lanjutan.
- Deteksi anomali reader masih berupa rate limit dasar dan audit; belum ada dashboard pola scrape/multi-device.
- Role admin lokal/sekolah/desa/swasta sudah didukung konsep scope, tetapi perlu uji skenario lengkap per role dan data wilayah/perpustakaan.

### Belum selesai

- ERD final aplikasi baru.
- Strategi sinkronisasi final INLISLite produksi: live DB, dump periodik, API/export, atau job ETL terjadwal.
- Backup dan restore database.
- SOP import/sinkronisasi INLISLite untuk operator.
- CSRF aktif dan penyesuaian seluruh form.
- Monitoring error/log aplikasi.
- Optimasi performa pencarian katalog skala produksi.
- Pelatihan admin, pilot terbatas, dan rilis bertahap.

## Risiko Utama

- Data INLISLite mengandung PII dan perlu perlindungan serius.
- Encoding dump menggunakan `latin1`, sedangkan aplikasi baru sebaiknya `utf8mb4`.
- Beberapa ID INLISLite bertipe `double`; aplikasi baru sebaiknya memakai integer/bigint/uuid yang konsisten.
- Anti-download absolut tidak bisa dijanjikan di browser; perlu ekspektasi keamanan yang realistis.
- GPS browser dapat dipalsukan pada perangkat tertentu; perlu mitigasi dengan QR lokasi, akun perangkat kiosk, audit anomali, dan radius reasonable.
- Hak cipta PDF harus jelas sebelum koleksi dibuka online.

## Keputusan yang Perlu Dipastikan

- Tetap CodeIgniter 3 untuk MVP atau langsung framework baru.
- Apakah INLISLite akan dibaca dari database aktif, dump periodik, atau API/export.
- Apakah aplikasi hanya web, atau sejak awal disiapkan web plus mobile/kiosk.
- Standar radius GPS pojok baca.
- Skema kuota: menit baca, jumlah halaman, jumlah buku, atau token sesi.
- Kebijakan koleksi: baca online, download bebas, akses lokasi, akses member, atau internal saja.
