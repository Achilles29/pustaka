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

Implementasi akun login hasil migrasi:

- Semua anggota INLISLite yang valid akan dibuatkan akun di `auth_user`.
- Role akun hasil migrasi adalah `USER`.
- Username utama memakai `members.MemberNo`.
- Jika `MemberNo` kosong/duplikat, username fallback memakai pola `member-{members.ID}`.
- Password awal standar: `PustakaRembang#2026`.
- `auth_user.force_password_change = 1` agar pemustaka wajib mengganti password pada login pertama.
- `auth_user.member_source_id` menyimpan `members.ID`.
- `members.auth_user_id` menyimpan relasi balik ke akun aplikasi.
- Nomor identitas tidak ditampilkan mentah di UI; target akhirnya harus dienkripsi/di-mask.

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

## Implementasi Awal Fase 2

Tanggal: 2026-07-29

Schema katalog aplikasi baru sudah disiapkan di `sql/2026-07-29e_regions_crud_catalog_phase2.sql`.

Tabel awal:

- `books`: bibliografi utama dari INLISLite `catalogs`.
- `book_authors`: penulis/kontributor.
- `book_subjects`: subjek untuk pencarian.
- `book_items`: eksemplar/koleksi fisik dari INLISLite `collections`.
- `digital_assets`: aset PDF/file digital dan kebijakan akses.
- `catalog_sync_runs`: riwayat proses sinkronisasi.
- `catalog_sync_maps`: pemetaan ID sumber INLISLite ke ID schema baru.

Status:

- Belum ada import data katalog.
- `/catalog` menampilkan dashboard schema baru dan statistik sumber INLISLite.
- `/catalog/sync` menampilkan status sumber dan riwayat sinkronisasi.
- Langkah berikutnya adalah membuat importer dry-run dari `catalogs` ke `books`.

## Cakupan Migrasi yang Harus Didukung

Scan ulang database `inlislite_v3` pada 2026-07-29 memastikan data utama yang dibutuhkan bisa ditarik ke aplikasi `pustaka`.

Data sumber penting:

- `catalogs`: 12.749 bibliografi.
- `collections`: 22.256 eksemplar.
- `catalog_ruas`: 159.429 ruas metadata.
- `catalog_subruas`: 144.963 subruas metadata.
- `members`: 5.389 anggota.
- `memberguesses`: 40.324 kunjungan/tamu.
- `collectionloans`: 31.229 histori peminjaman.
- `collectionloanitems`: 2.200 item histori peminjaman.
- `opaclogs`: 5.098 log OPAC.
- `opaclogs_keyword`: 5.043 keyword OPAC.
- `catalogfiles`: 0 row pada database saat ini, sehingga file digital perlu dicocokkan dari folder aset fisik.

Prioritas migrasi:

- Bibliografi: `catalogs`, `catalog_ruas`, `catalog_subruas`, `worksheets`, `publishers` ke `books`, `book_authors`, `book_subjects`.
- Eksemplar: `collections` dan master koleksi ke `book_items`.
- Aset visual/digital: `catalogs.CoverURL`, `members.PhotoUrl`, folder `sampul_koleksi`, `foto_anggota`, dan `dokumen_isi` ke storage aplikasi baru.
- Member: `members`, `jenis_anggota`, `status_anggota`, dan referensi pendidikan/pekerjaan ke `members` dan `auth_user`.
- Sirkulasi: `collectionloans`, `collectionloanitems`, dan aturan hak pinjam ke tabel histori layanan yang akan dibuat pada fase berikutnya.
- Analitik: `memberguesses`, `opaclogs`, dan `opaclogs_keyword` ke tabel analitik setelah katalog dan member stabil.
- Tabel sistem INLISLite seperti RBAC Yii, cache, setting internal, dan form builder tidak menjadi schema operasional baru; hanya dijadikan referensi jika ada data yang masih dibutuhkan.

## Logic INLISLite yang Diadopsi

Pembacaan kode dilakukan dari folder `C:\xampp\htdocs\inlislite3`, terutama:

- `backend/modules/pengkatalogan/controllers/KatalogController.php`
- `backend/modules/pengkatalogan/views/katalog/_formCover.php`
- `backend/modules/pengkatalogan/views/katalog/_formDigitalContent.php`
- `digitalcollection/controllers/KeranjangController.php`
- `keanggotaan/views/user/index.php`
- `common/models/Catalogs.php`
- `common/models/Collections.php`
- `common/models/Catalogfiles.php`
- `common/models/Members.php`

Adopsi penting:

- Cover katalog:
  - `catalogs.CoverURL` hanya menyimpan nama file.
  - Lokasi fisik mengikuti worksheet: `uploaded_files/sampul_koleksi/original/{WorksheetDir}/{CoverURL}`.
  - Jika cover kosong, INLISLite memakai fallback `sampul_koleksi/nophoto.jpg` atau `original/Monograf/tdkada.gif`.
  - Importer harus menyimpan `worksheet_id`, nama worksheet, dan path cover hasil resolve.
- Konten digital:
  - `catalogfiles.FileURL` adalah nama file utama.
  - Path lama mengikuti worksheet: `uploaded_files/dokumen_isi/{WorksheetDir}/{FileURL}`.
  - `FileFlash` dipakai untuk hasil ekstraksi/flipbook dari zip/rar.
  - `isCompress` menandai bentuk flipbook/arsip.
  - `IsPublish` dipakai sebagai kebijakan akses awal: `0` tidak publik, `1` publik, `2` hanya anggota.
  - Walaupun `catalogfiles` pada dump saat ini kosong, folder `dokumen_isi` tetap harus diaudit sebagai kandidat aset digital yatim.
- Eksemplar:
  - `collections` berelasi ke `catalogs` lewat `Catalog_id`.
  - Barcode `NomorBarcode` unik dan menjadi identitas eksemplar.
  - Field penting: `NoInduk`, `RFID`, `CallNumber`, `Location_Library_id`, `Location_id`, `Rule_id`, `Category_id`, `Media_id`, `Source_id`, `Status_id`, `ISOPAC`, `BookingMemberID`, dan `BookingExpiredDate`.
  - Tampilan OPAC lama memakai join ke `collectionmedias`, `collectionrules`, `collectionstatus`, dan `collectionlocations`.
- Ketersediaan katalog:
  - INLISLite menghitung eksemplar tersedia dengan `collections.Status_id = 1` dan booking yang sudah kedaluwarsa.
  - Detail publik hanya mengambil katalog dengan `catalogs.IsOPAC = 1`.
- Foto anggota:
  - `members.PhotoUrl` dipakai sebagai nama file foto jika ada.
  - Jika `PhotoUrl` kosong, INLISLite mencoba fallback ke `members.ID`.
  - Path lama: `uploaded_files/foto_anggota/{PhotoUrl atau ID}`.
  - Jika file tidak ada, fallback `nophoto.jpg`.
- Member:
  - `MemberNo` dan `IdentityNo` diperlakukan unik di INLISLite.
  - Data aktif/status bergantung `StatusAnggota_id`; mapping status perlu mengambil tabel master `status_anggota`.
  - `JenisAnggota_id`, `EducationLevel_id`, `Job_id`, `IdentityType_id`, dan referensi lain tidak boleh dibuang karena dipakai untuk segmentasi layanan.

## Implementasi Awal Membership

Tanggal: 2026-07-29

Schema membership awal sudah disiapkan di `sql/2026-07-29f_members_migration_login_foundation.sql`.

Tabel awal:

- `members`: profil anggota aplikasi baru dan relasi ke `auth_user`.
- `member_sync_runs`: riwayat sinkronisasi anggota.

Status:

- Belum ada import data member.
- `/members` menampilkan dashboard membership dan statistik sumber INLISLite.
- `/members/sync` menampilkan cakupan migrasi member, password awal standar, dan riwayat sinkronisasi.
- Langkah berikutnya adalah membuat importer dry-run dari `inlislite_v3.members` ke `members` plus `auth_user`.

## Pertanyaan Data yang Perlu Dijawab

- Sumber INLISLite nanti berupa dump berkala, akses database langsung, API, atau export CSV?
- Apakah tiap perpustakaan sekolah/desa punya instance INLISLite sendiri atau semua sudah ada di satu database?
- Bagaimana relasi koleksi digital di `catalogfiles` terhadap file PDF aktual di server?
- Data anggota INLISLite akan menjadi akun login `USER` dengan password awal standar dan wajib ganti password.
- Apakah histori pinjam fisik perlu tampil ke pemustaka pada MVP?
