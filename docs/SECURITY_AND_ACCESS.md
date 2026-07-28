# Keamanan, Hak Akses, dan Proteksi Konten

Tanggal dibuat: 2026-07-28

## Prinsip Keamanan

- Jangan pernah menyimpan file PDF asli di folder public web.
- Jangan pernah memberi link langsung ke file PDF untuk koleksi yang tidak boleh diunduh.
- Semua akses konten harus melewati controller/server authorization.
- Semua aksi admin penting harus masuk audit log.
- Data pribadi anggota harus dienkripsi atau dibatasi aksesnya.
- Proteksi client seperti disable klik kanan hanya pelengkap.

## Anti-Download yang Realistis

Permintaan "tidak dapat didownload dengan cara apapun" perlu diterjemahkan menjadi proteksi berlapis. Di web, jika konten sudah tampil di layar, pengguna masih bisa melakukan screenshot, screen recording, atau manipulasi perangkat. Yang bisa kita cegah secara kuat adalah akses ke file asli dan download massal.

Lapisan yang disarankan:

- Storage private di luar document root XAMPP.
- PDF diproses menjadi halaman per halaman atau tile image.
- Endpoint halaman memakai signed token pendek umur.
- Token terikat user, session, buku, halaman, dan device fingerprint.
- Watermark dinamis pada setiap halaman.
- Rate limiting dan deteksi pola scraping.
- Disable klik kanan, shortcut umum, drag image, dan print pada viewer.
- CSP, X-Frame-Options, Referrer-Policy, dan header keamanan lain.
- Audit log setiap pembukaan halaman.
- Opsi revoke session jika terdeteksi anomali.

## Hak Akses Berjenjang

### Superadmin

- Semua data dan konfigurasi.
- Membuat role.
- Mengelola semua perpustakaan.
- Mengatur kebijakan akses konten.
- Melihat audit keamanan.

### Admin

- Operasional level kabupaten atau Perpusda.
- Mengelola perpustakaan jejaring.
- Validasi konten dan event.
- Melihat laporan lintas perpustakaan sesuai izin.

### Admin Sekolah

- Mengelola profil perpustakaan sekolah.
- Mengelola koleksi dan event sekolah.
- Melihat statistik sekolah.
- Tidak dapat mengakses data perpustakaan lain kecuali data publik.

### Admin Desa

- Mengelola profil perpustakaan desa.
- Mengelola pojok baca desa.
- Mengelola event desa.
- Melihat statistik desa.

### Admin Swasta/Mitra

- Mengelola profil perpustakaan/mitra sendiri.
- Mengelola titik kerja sama seperti pojok baca.
- Melihat statistik milik sendiri.

### Pemustaka

- Mencari katalog.
- Mengakses membership digital.
- Membaca koleksi sesuai izin.
- Check-in pojok baca.
- Mendaftar event.

## Pojok Baca Digital

Validasi akses:

- User harus login sebagai pemustaka aktif.
- Browser meminta geolocation.
- Lokasi harus masuk radius titik baca.
- Token hanya diberikan bila user berada di titik yang valid.
- Token punya kuota dan masa berlaku.
- Token tidak boleh dipakai lintas akun.

Mitigasi GPS palsu:

- Radius tidak terlalu kecil agar tidak mengganggu user sah.
- QR check-in fisik di lokasi sebagai lapisan tambahan.
- Device/session fingerprint.
- Audit IP, user agent, dan pola akses.
- Mode kiosk/perangkat resmi untuk lokasi tertentu.

## Data Sensitif

Kategori sensitif:

- Nomor identitas.
- Tanggal lahir.
- Alamat.
- Nomor HP.
- Email.
- Foto anggota.
- Histori baca dan pinjam.
- Lokasi GPS.

Perlakuan:

- Batasi akses berdasarkan role.
- Masking pada UI admin non-superadmin.
- Enkripsi nilai sensitif tertentu.
- Audit log saat data sensitif dilihat/diubah.
- Retensi log ditentukan sejak awal.

## Audit Log Wajib

Catat:

- Login/logout admin.
- Gagal login berulang.
- Perubahan role/permission.
- Import data INLISLite.
- Upload, ubah, hapus file digital.
- Perubahan kebijakan akses buku.
- Buka halaman PDF.
- Download koleksi yang diizinkan.
- Check-in pojok baca.
- Penerbitan token kuota.
- Perubahan data anggota sensitif.

## Header dan Konfigurasi Web

Rekomendasi awal:

- `Content-Security-Policy`
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: same-origin`
- HTTPS wajib untuk produksi, terutama geolocation dan session cookie.
- Cookie `HttpOnly`, `Secure`, dan `SameSite=Lax` atau `Strict`.
