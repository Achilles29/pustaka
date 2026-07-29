# Perpustakaan GIS

Tanggal: 2026-07-29

## Tujuan

Modul Perpustakaan GIS menjadi direktori seluruh perpustakaan terdaftar di Kabupaten Rembang. Data ini akan menjadi dasar untuk peta layanan, pembatasan akses berbasis lokasi, statistik jejaring perpustakaan, dan integrasi pojok baca digital.

## Database

Migrasi:

- `sql/2026-07-29a_libraries_gis_schema.sql`
- `sql/2026-07-29d_phase1_admin_gis_clean.sql`

Tabel:

- `library_types`: jenis perpustakaan dan warna marker peta.
- `libraries`: profil perpustakaan, alamat, kontak, koordinat, radius layanan, dan status.
- `library_photos`: foto perpustakaan, caption, cover, dan uploader.
- `ref_districts`: master kecamatan Rembang dengan `province_code = 33`, `regency_code = 17`, `code = 01..14`, dan `full_code = 33.17.xx`.
- `ref_villages`: master Desa / Kelurahan Rembang dengan kode wilayah lengkap, `province_code = 33`, `regency_code = 17`, `district_code = xx`, dan `area_type`.

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
- Set foto utama/cover.
- Hapus foto galeri secara soft-delete.
- Dropdown kecamatan dan Desa / Kelurahan dari master wilayah.
- UI CRUD Master Wilayah di `/regions`.
- Verifikasi data perpustakaan.
- Scope admin lokal memakai `auth_user.library_id`.
- Akses modul memakai permission `libraries.index`.

## Catatan

- Leaflet saat ini dipasang dari CDN versi `1.9.4`.
- Data sample tidak ditinggalkan setelah smoke test.
- Foto masih public upload untuk fase admin profile; nanti aset sensitif/digital book tetap harus masuk storage non-public.
- Master wilayah memakai sumber OpenData resmi Rembang/Jateng, dataset [Nama Desa di Kabupaten Rembang](https://data.rembangkab.go.id/id/dataset/nama-desa-dan-kode-kec-di-kabupaten-rembang-2023).
- Kodifikasi wilayah mengikuti ketentuan: Provinsi Jawa Tengah `33`, Kabupaten Rembang `17`, Kecamatan memakai dua digit terakhir, misalnya `33.17.01` Sumber.
- Label wilayah bawah memakai `Desa / Kelurahan` karena Kabupaten Rembang memiliki desa dan kelurahan.
- Kolom `area_type` disiapkan untuk membedakan `desa` dan `kelurahan`; penandaan 7 kelurahan dilakukan admin saat data master wilayah dikelola.
- Field teks `district` dan `village` tetap ada sebagai denormalisasi/kompatibilitas, tetapi input utama memakai `district_id` dan `village_id`.

## Lanjutan

- Tambah detail publik/profil perpustakaan.
- Tambah import data lokasi dari INLISLite bila mapping memungkinkan.
- Tambah drag/drop atau sort order foto galeri.
- Lengkapi validasi wilayah jika nanti ada perubahan resmi kode wilayah.
