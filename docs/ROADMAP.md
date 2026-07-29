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
- [ ] Mapping final bibliografi, eksemplar, cover, dan file digital dari INLISLite.
- [x] Schema katalog aplikasi baru.
- [ ] Import awal katalog dari `inlislite_v3`.
- [ ] Import eksemplar/koleksi.
- [ ] Import cover dan aset buku.
- [ ] Pencarian katalog publik.
- [ ] Detail buku dengan lokasi eksemplar.
- [ ] Panel admin kurasi metadata.
- [x] Fondasi tabel sinkronisasi dan mapping ID.
- [ ] Job sinkronisasi ulang dan log perubahan.

### Fase 3 - Membership Digital

- [x] Schema anggota aplikasi baru.
- [x] Fondasi akun login member hasil migrasi INLISLite.
- [ ] Import anggota dari INLISLite ke `members` dan `auth_user`.
- [ ] Profil pemustaka.
- [ ] Kartu anggota digital dengan QR.
- [ ] Status membership.
- [ ] Absensi kunjungan perpustakaan.
- [ ] Riwayat pinjam dan baca dasar.
- [ ] Reset password dan wajib ganti password awal.

### Fase 4 - Koleksi Digital dan Reader Aman

- [ ] Storage file digital non-public.
- [ ] Kebijakan akses per buku: baca online, download bebas, lokasi, member, internal.
- [ ] Upload PDF dari admin.
- [ ] Reader online berbasis halaman/token.
- [ ] Watermark dinamis.
- [ ] Rate limit dan audit akses konten.
- [ ] Opsi download hanya untuk koleksi yang diizinkan.
- [ ] Deteksi anomali baca/scrape.

### Fase 5 - Pojok Baca Digital

- [ ] Schema titik pojok baca.
- [ ] CRUD titik, mitra, koordinat, radius, jam aktif, dan foto.
- [ ] QR lokasi dan GPS lock.
- [ ] Check-in member.
- [ ] Kuota/token baca.
- [ ] Koleksi khusus per titik.
- [ ] Dashboard penggunaan titik.
- [ ] Pilot mitra, misalnya Namua.

### Fase 6 - Event dan Engagement

- [ ] Schema event.
- [ ] CRUD event untuk Perpusda dan perpustakaan terintegrasi.
- [ ] Pendaftaran peserta.
- [ ] QR attendance/check-in.
- [ ] Kuota peserta.
- [ ] Dokumentasi foto event.
- [ ] Sertifikat digital.
- [ ] Laporan event.

### Fase 7 - Hardening, Audit, dan Rilis

- [ ] CSRF aktif dan form disesuaikan.
- [ ] Backup dan restore database.
- [ ] SOP import/sinkronisasi INLISLite.
- [ ] Monitoring error.
- [ ] Optimasi performa search.
- [ ] Security review PDF reader dan akses file.
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
