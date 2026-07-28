# Pemetaan Awal INLISLite

Tanggal audit awal: 2026-07-28
Sumber: `inlislite_v3.sql`

## Catatan Privasi

Dump INLISLite berisi data anggota nyata, termasuk nama, alamat, kontak, tanggal lahir, dan nomor identitas pada tabel `members`. Dokumentasi ini hanya mencatat struktur dan pemetaan, bukan contoh data personal.

## Tabel INLISLite yang Relevan

### Bibliografi dan Digital

- `catalogs`: metadata bibliografi utama seperti judul, penulis, penerbit, tahun, ISBN, subjek, nomor panggil, bahasa, cover, status OPAC, worksheet, dan MARC.
- `catalogfiles`: file digital yang terhubung ke katalog melalui `Catalog_id`.
- `catalog_ruas` dan `catalog_subruas`: detail MARC/subruas bibliografi.
- `worksheets`, `worksheetfields`, `worksheetfielditems`: tipe bahan dan struktur metadata.
- `publishers`: master penerbit.

### Eksemplar dan Lokasi

- `collections`: data eksemplar fisik, barcode, RFID, nomor induk, lokasi, kategori, status, sumber, dan relasi ke `catalogs`.
- `locations`: lokasi/ruang koleksi.
- `location_library`: lokasi perpustakaan/unit layanan.
- `collectionlocations`, `collectioncategorys`, `collectionmedias`, `collectionstatus`, `collectionsources`: data master koleksi.

### Anggota dan Layanan

- `members`: data anggota.
- `membersonline`: data pendaftaran/anggota online.
- `jenis_anggota`, `status_anggota`, `masa_berlaku_anggota`: master keanggotaan.
- `collectionloans`, `collectionloanitems`, `collectionloanextends`: transaksi peminjaman fisik.
- `readinlocation`, `bacaditempat`: aktivitas baca di tempat.
- `memberguesses`: kunjungan anggota.

### User dan Hak Akses

- `user` dan `users`: akun internal INLISLite.
- `roles`, `rolemodule`, `modules`, `menu`: role dan modul.
- `auth_assignment`, `auth_item`, `auth_item_child`, `auth_rule`: RBAC Yii/INLISLite.
- `userloclibforcol`, `userloclibforloan`: pembatasan user berdasarkan unit/lokasi.

## Model Aplikasi Baru yang Disarankan

### `libraries`

Menggantikan/menormalkan konsep `location_library` dan perluasan data perpustakaan.

Kolom konseptual:

- `id`
- `code`
- `name`
- `type`
- `ownership`
- `address`
- `village`
- `district`
- `phone`
- `email`
- `website`
- `latitude`
- `longitude`
- `service_radius_meters`
- `opening_hours`
- `description`
- `status`

### `library_photos`

Galeri foto perpustakaan.

Kolom konseptual:

- `id`
- `library_id`
- `file_path`
- `caption`
- `sort_order`
- `is_cover`

### `books`

Metadata bibliografi bersih dari `catalogs`.

Mapping awal:

- `source_catalog_id` dari `catalogs.ID`
- `control_number` dari `catalogs.ControlNumber`
- `bib_id` dari `catalogs.BIBID`
- `title` dari `catalogs.Title`
- `author` dari `catalogs.Author`
- `edition` dari `catalogs.Edition`
- `publisher` dari `catalogs.Publisher`
- `publish_location` dari `catalogs.PublishLocation`
- `publish_year` dari `catalogs.PublishYear`
- `subject` dari `catalogs.Subject`
- `isbn` dari `catalogs.ISBN`
- `call_number` dari `catalogs.CallNumber`
- `language` dari `catalogs.Languages`
- `dewey_number` dari `catalogs.DeweyNo`
- `cover_path` dari `catalogs.CoverURL`
- `is_public` dari `catalogs.IsOPAC`
- `marc_raw` dari `catalogs.MARC_LOC`

### `book_items`

Eksemplar fisik dari `collections`.

Mapping awal:

- `source_collection_id` dari `collections.ID`
- `book_id` dari relasi `collections.Catalog_id`
- `barcode` dari `collections.NomorBarcode`
- `inventory_number` dari `collections.NoInduk`
- `rfid` dari `collections.RFID`
- `call_number` dari `collections.CallNumber`
- `library_id` dari `collections.Location_Library_id`
- `location_id` dari `collections.Location_id`
- `category_id` dari `collections.Category_id`
- `media_id` dari `collections.Media_id`
- `status_id` dari `collections.Status_id`
- `is_public` dari `collections.ISOPAC`
- `is_verified` dari `collections.IsVerified`

### `digital_assets`

File digital dari `catalogfiles` dan upload baru.

Mapping awal:

- `source_catalog_file_id` dari `catalogfiles.ID`
- `book_id` dari `catalogfiles.Catalog_id`
- `original_file_path` dari `catalogfiles.FileURL`
- `is_published` dari `catalogfiles.IsPublish`
- `access_policy`: nilai baru, misalnya `read_online`, `download_allowed`, `location_only`, `private`.
- `storage_path`: path aman non-public.
- `processing_status`: `pending`, `ready`, `failed`.

### `members`

Data membership digital. Jangan langsung memakai seluruh kolom INLISLite di tabel operasional.

Mapping awal:

- `source_member_id` dari `members.ID`
- `member_no` dari `members.MemberNo`
- `full_name` dari `members.Fullname`
- `birth_place` dari `members.PlaceOfBirth`
- `birth_date` dari `members.DateOfBirth`
- `address` dari `members.AddressNow` atau `members.Address`
- `phone` dari `members.NoHp` atau `members.Phone`
- `email` dari `members.Email`
- `identity_type_id` dari `members.IdentityType_id`
- `identity_no_encrypted` dari `members.IdentityNo`
- `registered_at` dari `members.RegisterDate`
- `expires_at` dari `members.EndDate`
- `status_id` dari `members.StatusAnggota_id`
- `photo_path` dari `members.PhotoUrl`

### `reading_sessions`

Sesi baca digital baru.

Kolom konseptual:

- `id`
- `member_id`
- `book_id`
- `digital_asset_id`
- `library_id`
- `reading_point_id`
- `started_at`
- `ended_at`
- `last_page`
- `ip_address`
- `user_agent`
- `device_fingerprint`
- `latitude`
- `longitude`
- `access_policy`
- `status`

### `reading_points`

Pojok baca digital.

Kolom konseptual:

- `id`
- `library_id`
- `partner_name`
- `name`
- `address`
- `latitude`
- `longitude`
- `radius_meters`
- `daily_quota`
- `quota_type`
- `opening_hours`
- `status`

### `reading_tokens`

Token/kuota baca untuk pojok baca digital.

Kolom konseptual:

- `id`
- `member_id`
- `reading_point_id`
- `token`
- `quota_total`
- `quota_used`
- `quota_unit`
- `issued_at`
- `expires_at`
- `issued_by`
- `status`

### `events`

Agenda kegiatan literasi.

Kolom konseptual:

- `id`
- `library_id`
- `title`
- `description`
- `event_type`
- `starts_at`
- `ends_at`
- `location_name`
- `latitude`
- `longitude`
- `quota`
- `registration_required`
- `status`

## Strategi Sinkronisasi

1. Import dump INLISLite ke database staging.
2. Buat proses ETL dari staging ke schema aplikasi baru.
3. Simpan `source_*_id` untuk menjaga jejak asal data.
4. Jangan update langsung ke tabel INLISLite dari aplikasi baru pada fase awal.
5. Gunakan checksum atau timestamp untuk sinkronisasi incremental jika sumber aktif tersedia.
6. Normalisasi encoding dari `latin1` ke `utf8mb4`.
7. Enkripsi kolom sensitif seperti nomor identitas.

## Pertanyaan Data yang Perlu Dijawab

- Sumber INLISLite nanti berupa dump berkala, akses database langsung, API, atau export CSV?
- Apakah tiap perpustakaan sekolah/desa punya instance INLISLite sendiri atau semua sudah ada di satu database?
- Bagaimana relasi koleksi digital di `catalogfiles` terhadap file PDF aktual di server?
- Apakah data anggota INLISLite akan menjadi akun login, atau hanya basis verifikasi membership?
- Apakah histori pinjam fisik perlu tampil ke pemustaka pada MVP?
