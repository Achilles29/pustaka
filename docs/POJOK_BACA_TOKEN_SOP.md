# SOP Token Pojok Baca Digital

## Tujuan

Token Pojok Baca dipakai untuk membuka koleksi digital dari mana saja selama token masih tersedia. Token juga menjadi instrumen agar kunjungan fisik tetap tinggi: ketika kuota habis, member harus melakukan update token melalui perpustakaan daerah atau titik layanan yang ditentukan.

## Alur Member

1. Member login ke dashboard.
2. Member membuka katalog atau dashboard lalu menekan `Baca Online`.
3. Untuk koleksi `location_only`, halaman reader meminta member menyalakan GPS.
4. Sistem membandingkan koordinat member dengan titik aktif di `reading_points` dan perpustakaan aktif di `libraries`.
5. Jika masuk radius Pojok Baca/perpustakaan, reader langsung terbuka dan kuota token tidak berkurang.
6. Jika berada di luar radius, sistem memakai token aktif di `reading_tokens` dan mengurangi kuota sesuai satuan token.
7. Jika token luar zona tidak tersedia atau kuota habis, member harus login/check-in di perpustakaan daerah atau titik layanan untuk update token.
8. Halaman `/user/reading-checkin` tetap tersedia untuk menerbitkan token luar zona melalui check-in GPS di titik layanan.

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

- Koleksi `location_only` harus meminta GPS lebih dulu sebelum reader dibuka.
- Reader tidak mewajibkan token aktif jika GPS berada dalam radius Pojok Baca/perpustakaan.
- Reader mengurangi kuota hanya jika akses dilakukan dari luar lokasi.
- Jika akses luar lokasi dan token tidak tersedia, reader menampilkan pesan langsung di halaman validasi lokasi.
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
- Reader `location_only` sudah memakai alur satu klik GPS:
  - di zona Pojok Baca/perpustakaan: akses langsung, `quota_charged = 0`, `reading_point_id`/`library_id` tersimpan jika tersedia,
  - di luar zona: token aktif wajib tersedia dan kuota berkurang.
- Kunjungan dashboard member dicatat sekali per member per hari sebagai `member_dashboard`.
- Kunjungan Pojok Baca dari check-in GPS dicatat sebagai `reading_point`.
- Monitor buku tamu awal tersedia di `/guestbook/monitor` dengan QR dinamis dan form tamu/member.
- Renderer PDF aman non-public sudah tersedia untuk aset non-downloadable melalui render per halaman/gambar ber-watermark, audit sesi, dan rate limit.
