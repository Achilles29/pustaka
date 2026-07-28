# Perpustakaan GIS

Tanggal: 2026-07-29

## Tujuan

Modul Perpustakaan GIS menjadi direktori seluruh perpustakaan terdaftar di Kabupaten Rembang. Data ini akan menjadi dasar untuk peta layanan, pembatasan akses berbasis lokasi, statistik jejaring perpustakaan, dan integrasi pojok baca digital.

## Database

Migrasi:

- `sql/2026-07-29a_libraries_gis_schema.sql`

Tabel:

- `library_types`: jenis perpustakaan dan warna marker peta.
- `libraries`: profil perpustakaan, alamat, kontak, koordinat, radius layanan, dan status.
- `library_photos`: foto perpustakaan, caption, cover, dan uploader.

Jenis awal:

- `perpusda`
- `sekolah`
- `desa`
- `swasta`
- `komunitas`
- `mitra`

## Fitur Implementasi Awal

- Daftar perpustakaan dengan filter pencarian, jenis, dan status.
- Peta Leaflet/OpenStreetMap untuk semua titik perpustakaan.
- Radius layanan ditampilkan sebagai lingkaran di peta.
- CRUD dasar: tambah, edit, aktif/nonaktif.
- Form koordinat dengan peta picker: klik peta atau drag marker untuk mengisi latitude/longitude.
- Upload foto perpustakaan ke `assets/uploads/libraries`.
- Akses modul memakai permission `libraries.index`.

## Catatan

- Leaflet saat ini dipasang dari CDN versi `1.9.4`.
- Data sample tidak ditinggalkan setelah smoke test.
- Foto masih public upload untuk fase admin profile; nanti aset sensitif/digital book tetap harus masuk storage non-public.

## Lanjutan

- Tambah hapus/set cover foto.
- Tambah detail publik/profil perpustakaan.
- Tambah import data lokasi dari INLISLite bila mapping memungkinkan.
- Tambah batasan admin lokal berdasarkan `library_id`.
- Tambah kecamatan/desa master agar input lebih konsisten.
