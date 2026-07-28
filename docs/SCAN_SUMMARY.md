# Ringkasan Scan INLISLite dan Pustaka

Tanggal scan ulang: 2026-07-28

## Lokasi Sumber

- Aplikasi INLISLite: `C:\xampp\htdocs\inlislite3`
- Dump database: `C:\xampp\htdocs\pustaka\inlislite_v3.sql`
- Database lokal hasil import: `inlislite_v3`
- Aplikasi baru: `C:\xampp\htdocs\pustaka`

## Hasil Import Database

Status: berhasil diimport ke MariaDB lokal.

- Database: `inlislite_v3`
- Jumlah tabel: 185
- Jumlah routine/procedure/function: 47
- Katalog `catalogs`: 14.097 row
- Eksemplar `collections`: 22.927 row
- Anggota `members`: 5.389 row
- File digital `catalogfiles`: 0 row
- Lokasi perpustakaan `location_library`: 2 row
- Lokasi/ruang `locations`: 6 row
- Role `roles`: 45 row
- User internal `users`: 17 row
- Transaksi pinjam `collectionloans`: 31.561 row
- Detail pinjam `collectionloanitems`: 2.200 row

Top tabel terbesar berdasarkan hitung row aktual:

- `modelhistory`: 344.961
- `catalog_ruas`: 162.989
- `catalog_subruas`: 147.205
- `memberguesses`: 41.136
- `collectionloans`: 31.561
- `collections`: 22.927
- `memberloanauthorizecategory`: 16.117
- `catalogs`: 14.097
- `memberloanauthorizelocation`: 10.747
- `members`: 5.389

Detail lengkap ada di [SCAN_DATABASE_TABLE_COUNTS.csv](SCAN_DATABASE_TABLE_COUNTS.csv).

## Hasil Scan Folder INLISLite

Total aplikasi `inlislite3`:

- File: 79.178
- Ukuran: 2.834,83 MB

Folder terbesar:

- `uploaded_files`: 13.631 file, 1.417,86 MB
- `vendor`: 41.042 file, 651,14 MB
- `backend`: 11.220 file, 471,42 MB
- `opac`: 600 file, 87,96 MB
- `frontend`: 266 file, 56,87 MB
- `guestbook`: 4.084 file, 42,47 MB
- `assets`: 3.762 file, 34,17 MB
- `bacaditempat`: 2.435 file, 26,80 MB
- `digitalcollection`: 279 file, 18,59 MB
- `inliscore`: 824 file, 17,05 MB

Detail lengkap ada di [SCAN_INLISLITE_TOP_FOLDERS.csv](SCAN_INLISLITE_TOP_FOLDERS.csv).

## Folder `uploaded_files`

Folder utama:

- `sampul_koleksi`: 7.645 file, 1.284,52 MB
- `foto_anggota`: 5.864 file, 110,98 MB
- `dokumen_isi`: 4 file, 12,46 MB
- `settings`: 17 file, 6,78 MB
- `aplikasi`: 9 file, 1,31 MB
- `templates`: 87 file, 1,22 MB
- `temporary`: 4 file, 0,57 MB
- `logo_ruangan`: 0 file
- `foto_rujukan`: 0 file

Ekstensi terbesar di `uploaded_files`:

- `.jpg`: 11.658 file, 1.317,23 MB
- tanpa ekstensi: 1.461 file, 14,14 MB
- `.png`: 202 file, 59,03 MB
- `.jpeg`: 197 file, 13,28 MB
- `.ods`: 76 file, 0,96 MB
- `.rar`: 4 file, 12,46 MB
- `.pdf`: 1 file, 0,49 MB

Detail lengkap ada di [SCAN_INLISLITE_UPLOADED_FILES.csv](SCAN_INLISLITE_UPLOADED_FILES.csv) dan [SCAN_INLISLITE_FILE_EXTENSIONS.csv](SCAN_INLISLITE_FILE_EXTENSIONS.csv).

## Kecocokan Referensi Database dan File

- `members.PhotoUrl`: 5.363 referensi, 3.008 file ditemukan, 2.355 belum cocok persis.
- fallback foto anggota by `members.ID`: 26 referensi, 0 file ditemukan.
- `catalogs.CoverURL` untuk Monograf: 7.795 referensi, 7.356 file ditemukan, 439 belum cocok persis.

Catatan:

- Semua `catalogs.Worksheet_id` pada dump ini bernilai `1`, yaitu `Monograf`.
- Cover buku utama berada di `uploaded_files\sampul_koleksi\original\Monograf`.
- Foto anggota berada di `uploaded_files\foto_anggota`.
- Tabel `catalogfiles` kosong, tetapi folder `dokumen_isi` berisi 4 file `.rar`. Jadi koleksi digital perlu diaudit manual dari file dan modul lama, bukan hanya dari tabel `catalogfiles`.

Detail lengkap ada di [SCAN_ASSET_REFERENCE_MATCH.csv](SCAN_ASSET_REFERENCE_MATCH.csv).

## Struktur INLISLite Lama

Aplikasi lama berbasis Yii advanced dengan entrypoint/modul utama:

- `backend`
- `frontend`
- `opac`
- `digitalcollection`
- `keanggotaan`
- `bacaditempat`
- `guestbook`
- `api`
- `console`
- `common`
- `inliscore`

Konfigurasi lokal utama mengarah ke database `inlislite_v3` di MySQL lokal. Alias penting `uploaded_files` diarahkan ke `C:\xampp\htdocs\inlislite3\uploaded_files`.

## Status Aplikasi Baru `pustaka`

CodeIgniter 3.1.13 sudah dipindahkan dari subfolder `CodeIgniter-3.1.13` ke root `C:\xampp\htdocs\pustaka`.

Yang sudah disiapkan:

- `application`
- `system`
- `index.php`
- `.htaccess`
- `base_url`: `http://localhost/pustaka/`
- koneksi database default: `inlislite_v3`
- autoload library: `database`, `session`
- autoload helper: `url`, `form`, `security`
- folder session: `application/cache/sessions`

Smoke test:

- `php index.php` berhasil merender halaman welcome CodeIgniter.
- Query `SELECT COUNT(*) FROM catalogs` berhasil dan menghasilkan 14.097.

## Risiko Migrasi Data

- Dump memuat PII anggota; jangan tampilkan data personal mentah di demo atau dokumentasi.
- Banyak foto anggota tidak cocok dengan `PhotoUrl` secara langsung; perlu algoritma pencocokan tambahan.
- Beberapa file tanpa ekstensi ada di `foto_anggota`; perlu deteksi MIME saat migrasi.
- `catalogfiles` kosong, sehingga strategi koleksi digital perlu audit folder `dokumen_isi` dan modul upload lama.
- Database lama memakai campuran charset dan struktur historis; aplikasi baru perlu schema bersih.
- Password lama memakai pola hash lama pada beberapa model; jangan dipakai langsung sebagai sistem autentikasi baru tanpa migrasi password yang aman.
