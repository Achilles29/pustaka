# SOP Token Pojok Baca Digital

## Tujuan

Token Pojok Baca dipakai untuk membuka koleksi digital dari mana saja selama token masih tersedia. Token juga menjadi instrumen agar kunjungan fisik tetap tinggi: ketika kuota habis, member harus melakukan update token melalui perpustakaan daerah atau titik layanan yang ditentukan.

## Alur Member

1. Member login ke dashboard.
2. Member membuka `Pojok Baca`.
3. Member menekan tombol `Ambil GPS dan Check-in`.
4. Browser meminta izin lokasi.
5. Sistem membandingkan koordinat member dengan titik aktif di `reading_points` dan perpustakaan aktif di `libraries`.
6. Jika masuk radius, sistem menerbitkan atau memperbarui token di `reading_tokens`.
7. Setelah token tersedia, member dapat mengakses koleksi dari mana saja.
8. Saat reader dibuka dari Pojok Baca atau perpustakaan daerah, kuota tidak berkurang.
9. Saat reader dibuka dari luar lokasi, kuota berkurang sesuai satuan token.
10. Jika kuota habis, member harus login/check-in di perpustakaan daerah atau titik layanan untuk update token.

## Aturan Token

- Satu token diterbitkan untuk satu member dan satu titik Pojok Baca/perpustakaan layanan.
- Token memakai kuota dari pengaturan titik:
  - `minutes`,
  - `pages`,
  - `books`.
- Masa berlaku default token adalah hari yang sama sampai `23:59:59`.
- Token yang melewati masa berlaku otomatis dianggap `expired`.
- Jika member masih punya token aktif di titik yang sama, sistem tidak membuat token baru.
- Token dapat dicabut admin dengan status `revoked` jika ada penyalahgunaan.
- Kuota `0` dapat dipakai sebagai kebijakan unlimited pada titik tertentu.

## Aturan Radius GPS

- Radius disimpan di `reading_points.radius_meters`.
- Titik harus berstatus `active`.
- Titik wajib punya latitude dan longitude.
- GPS browser bisa dipalsukan, sehingga validasi GPS harus dianggap lapisan awal, bukan satu-satunya kontrol.

## Mitigasi Penyalahgunaan

- Gunakan radius wajar, misalnya 50-150 meter untuk lokasi kecil.
- Untuk lokasi publik yang luas, radius bisa dinaikkan sesuai kebutuhan.
- Tahap lanjutan harus menambahkan:
  - QR lokasi untuk check-in fisik,
  - audit IP dan device,
  - deteksi check-in berpindah lokasi tidak wajar,
  - pembatasan token aktif per member,
  - log pemakaian token per sesi reader.

## Hubungan Dengan Reader PDF

- Koleksi `location_only` harus meminta token aktif sebelum reader dibuka.
- Reader tidak mengurangi kuota jika GPS berada dalam radius Pojok Baca/perpustakaan.
- Reader mengurangi kuota jika akses dilakukan dari luar lokasi.
- Reader menyimpan sesi ke `reading_sessions`.
- Setiap sesi reader member juga masuk ke `member_visits` sebagai `digital_access`, dengan `visit_origin`:
  - `digital_external` untuk akses luar lokasi,
  - `reading_point` untuk akses dari Pojok Baca,
  - `library` untuk akses dari radius perpustakaan.
- File PDF asli tidak boleh dibuka langsung lewat URL publik.
- Tahap reader aman berikutnya wajib memakai storage non-public, render per halaman, watermark dinamis, rate limit, dan audit akses.

## Buku Tamu dan QR Monitor Pelayanan

- Monitor pelayanan tersedia di `/guestbook/monitor`.
- QR monitor berubah sesuai `visit_kiosk_settings.qr_refresh_seconds` (default 60 detik).
- Member yang scan QR harus login; setelah berhasil, kunjungan dicatat sebagai `qr_checkin`.
- Petugas/pengunjung dapat mencatat tamu non-member lewat form `Pengunjung`.
- Kunjungan rombongan dicatat dengan `visitor_count`, `group_name`, dan `group_leader_name`.
- Member yang tidak scan QR dapat dicatat lewat tab `Member` memakai NIK atau nomor anggota.
- Kunjungan fisik monitor masuk ke `member_visits`, bukan tabel terpisah, agar laporan layanan harian tetap satu pintu.

## Catatan Implementasi Saat Ini

- Check-in member tersedia di `/user/reading-checkin`.
- POST check-in tersedia di `/user/reading-checkin/store`.
- Monitoring admin tersedia di `/reading-points/tokens`.
- Token tersimpan di `reading_tokens`.
- Pengaturan titik tersedia di `/reading-points`.
- Reader `location_only` sudah memakai token aktif dan mencatat pengurangan kuota awal per sesi buka.
- Kunjungan dashboard member dicatat sekali per member per hari sebagai `member_dashboard`.
- Kunjungan Pojok Baca dari check-in GPS dicatat sebagai `reading_point`.
- Monitor buku tamu awal tersedia di `/guestbook/monitor` dengan QR dinamis dan form tamu/member.
- Renderer PDF aman non-public masih tahap berikutnya.
