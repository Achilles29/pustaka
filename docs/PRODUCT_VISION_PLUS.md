# Pengembangan Ide Produk

Tanggal dibuat: 2026-07-28

Dokumen ini berisi perluasan ide sebelum roadmap teknis dipadatkan. Targetnya bukan sekadar memindahkan katalog INLISLite ke web baru, tetapi membuat ekosistem literasi digital yang terasa modern, berguna, dan membanggakan untuk Kabupaten Rembang.

## Konsep Besar

Nama kerja: Pustaka Digital Rembang.

Produk dibagi menjadi empat pengalaman utama:

- Portal publik: mencari buku, perpustakaan, event, dan koleksi lokal.
- Area pemustaka: kartu anggota digital, baca online, kuota, histori, event.
- Dashboard pengelola: operasional admin berjenjang untuk Perpusda, sekolah, desa, swasta, dan mitra.
- Pojok baca/kiosk: akses cepat di lokasi fisik yang dikunci GPS/QR/perangkat.

## Fitur Premium yang Layak Masuk

### 1. Peta Literasi Rembang

Peta interaktif semua perpustakaan, pojok baca, sekolah, desa, dan mitra.

Fitur:

- Filter jenis perpustakaan.
- Status buka/tutup.
- Radius akses pojok baca.
- Koleksi unggulan per lokasi.
- Statistik ringkas per kecamatan.

### 1A. Sinkronisasi Perpustakaan Se-Kabupaten Berbasis GIS

Seluruh perpustakaan terdaftar di Kabupaten Rembang disinkronkan ke satu layer GIS terpadu. Setiap titik lokasi menjadi pintu masuk ke profil perpustakaan, koleksi, event, foto, dan status layanan.

Data yang perlu ditampilkan:

- nama perpustakaan,
- jenis perpustakaan: Perpusda, sekolah, desa, swasta, komunitas, mitra,
- alamat lengkap,
- titik koordinat,
- radius layanan,
- kontak dan jam buka,
- pengelola/PIC,
- foto gedung, ruang, koleksi, fasilitas, dan aktivitas,
- jumlah koleksi dan koleksi digital,
- event aktif,
- status aktif, perlu verifikasi, atau nonaktif.

Kemampuan sinkronisasi:

- import awal dari INLISLite dan data pendataan perpustakaan daerah,
- update berkala oleh admin masing-masing perpustakaan,
- validasi/verifikasi oleh admin kabupaten,
- log perubahan data profil dan koordinat,
- deteksi data ganda berdasarkan nama, alamat, dan radius koordinat.

Tampilan GIS:

- peta semua titik perpustakaan terdaftar,
- marker berbeda per jenis perpustakaan,
- panel detail dengan galeri foto,
- filter kecamatan, Desa / Kelurahan, sekolah, dan swasta,
- pencarian lokasi,
- statistik sebaran perpustakaan,
- layer pojok baca digital dan mitra.

### 2. Kartu Anggota Digital yang Serius

Kartu anggota tidak hanya QR statis.

Fitur:

- QR dinamis untuk check-in.
- Status aktif/kedaluwarsa.
- Badge literasi.
- Riwayat kunjungan.
- Kuota baca aktif.
- Mode kartu keluarga/sekolah jika nanti dibutuhkan.

### 3. Rak Digital Tematik

Kurasi koleksi agar pemustaka tidak hanya mencari, tapi juga menemukan.

Contoh rak:

- Rembang Hari Ini
- Sejarah dan Budaya Rembang
- Anak dan Keluarga
- Buku Sekolah
- UMKM dan Kewirausahaan
- Pertanian, Perikanan, dan Pesisir
- Literasi Digital
- Koleksi Baru
- Paling Banyak Dibaca

### 4. Rembang Local Knowledge

Ruang khusus untuk konten lokal.

Isi:

- arsip foto lama,
- cerita rakyat,
- karya penulis lokal,
- dokumentasi event literasi,
- naskah dan publikasi daerah,
- profil tokoh literasi,
- koleksi sejarah desa/kecamatan.

### 5. Tantangan Membaca

Gamifikasi yang tetap elegan.

Fitur:

- tantangan membaca bulanan,
- leaderboard sekolah/desa,
- badge pemustaka aktif,
- sertifikat digital,
- target baca per kelas/komunitas,
- statistik capaian literasi.

### 6. Event Literasi Terpadu

Agenda bukan hanya daftar event.

Fitur:

- pendaftaran peserta,
- QR check-in,
- reminder,
- kuota,
- dokumentasi,
- sertifikat,
- galeri event,
- laporan dampak.

### 7. Usulan Buku dan Aspirasi Pemustaka

Pemustaka bisa mengusulkan buku.

Fitur:

- usulan judul,
- voting,
- status pengadaan,
- komentar admin,
- prioritas berdasarkan lokasi/sekolah/desa.

### 8. Analytics untuk Pengambil Kebijakan

Dashboard untuk melihat dampak.

Fitur:

- aktivitas baca per kecamatan,
- koleksi paling diminati,
- sekolah/desa paling aktif,
- jam ramai pojok baca,
- tren anggota baru,
- event dengan engagement tinggi,
- kebutuhan koleksi berdasarkan pencarian yang tidak ditemukan.

### 9. Mode Pojok Baca Mitra

Untuk lokasi kerja sama seperti Namua, desa, sekolah, atau ruang publik.

Fitur:

- akun perangkat/kiosk,
- QR lokasi,
- GPS lock,
- kuota per member,
- koleksi khusus lokasi,
- halaman brand mitra,
- laporan trafik ke mitra.

### 10. Reader yang Nyaman dan Aman

Reader harus terasa seperti membaca, bukan membuka file.

Fitur:

- animasi swipe/flip yang ringan,
- bookmark,
- lanjutkan membaca,
- mode terang/gelap,
- zoom,
- watermark dinamis,
- token halaman,
- opsi download hanya jika lisensi mengizinkan.

### 11. Aksesibilitas

Produk publik perlu nyaman untuk semua pengguna.

Fitur:

- ukuran teks nyaman,
- kontras cukup,
- navigasi keyboard,
- alt text untuk cover/foto penting,
- mode ringan untuk koneksi lambat.

### 12. Progressive Web App

Fase lanjut:

- install ke layar utama HP,
- notifikasi event,
- kartu anggota cepat dibuka,
- cache UI dasar,
- tetap online-only untuk konten PDF yang dilindungi.

## Prinsip Produk

- Data lama diselamatkan, tetapi pengalaman baru tidak terasa seperti sistem lama.
- Admin dibuat efisien, bukan dekoratif.
- Portal publik dibuat menarik, bukan sekadar tabel katalog.
- Keamanan konten dibangun dari server, bukan dari trik klik kanan.
- Peta, membership, dan pojok baca menjadi pembeda utama aplikasi.

## Prioritas Pengalaman Pertama

Saat aplikasi pertama kali dibuka, pengguna harus langsung melihat:

- pencarian katalog terpadu,
- kartu/akses membership,
- peta perpustakaan,
- koleksi unggulan,
- event terdekat,
- akses pojok baca jika sedang berada di lokasi valid.

Untuk admin, layar pertama harus langsung memperlihatkan:

- ringkasan koleksi,
- anggota,
- perpustakaan terdaftar,
- event,
- aktivitas baca,
- alert data/keamanan.
