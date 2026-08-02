# Handover Developer

Tanggal update: 2026-08-01

## Lokasi Project

```text
C:\xampp\htdocs\pustaka
```

URL lokal:

```text
http://localhost/pustaka/
```

## Database

Database aplikasi:

```text
pustaka
```

Database acuan:

```text
inlislite_v3
```

Dump acuan terbaru:

```text
C:\xampp\htdocs\inlislite_v3.sql
```

Import ulang acuan jika diperlukan:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS inlislite_v3"
cmd /c ""C:\xampp\mysql\bin\mysql.exe" -u root inlislite_v3 < "C:\xampp\htdocs\inlislite_v3.sql""
```

## Akun Lokal

Semua password awal:

```text
admin123
```

User:

- `superadmin` role `SUPERADMIN`
- `admin` role `ADMIN`
- `pemustaka` role `USER`

Akun member hasil migrasi:

- username utama memakai NIK/nomor identitas dari INLISLite `members.IdentityNo`.
- jika NIK kosong, fallback sementara memakai nomor anggota atau `member-{ID sumber}` agar akun tetap bisa dipakai.
- password awal/reset massal: `perpus2026`.
- role akun member: `USER`.
- `force_password_change = 1`.

## Route Penting

- `/` landing publik.
- `/katalog` portal katalog publik.
- `/katalog/detail/{id}` detail katalog publik.
- `/membership/verify/{member_id}/{token}` verifikasi kartu anggota digital.
- `/login` login.
- `/admin` admin panel.
- `/user/dashboard` dashboard pemustaka.
- `/libraries` Perpustakaan GIS.
- `/regions` Master Wilayah.
- `/catalog` Dashboard Katalog.
- `/catalog/detail/{id}` Detail buku, penulis, subjek, dan eksemplar.
- `/catalog/sync` Status Sinkronisasi Katalog.
- `POST /catalog/sync/run` Jalankan batch sinkronisasi katalog.
- `/members` Dashboard Membership.
- `/members/detail/{id}` Detail profil member dan akun login.
- `/members/sync` Status Sinkronisasi Member.
- `POST /members/sync/run` Jalankan batch sinkronisasi member.
- `/rbac/roles` Tipe User dan Hak Akses.
- `/rbac/users` User.
- `/rbac/pages` Registry Halaman.
- `/rbac/sidebar` Sidebar.
- `/audit` Audit Log.

## File Fondasi

Controller:

- `application/core/MY_Controller.php`
- `application/controllers/Auth.php`
- `application/controllers/Admin.php`
- `application/controllers/Home.php`
- `application/controllers/Public_catalog.php`
- `application/controllers/Membership.php`
- `application/controllers/User_dashboard.php`
- `application/controllers/Rbac.php`
- `application/controllers/Audit.php`
- `application/controllers/Regions.php`
- `application/controllers/Catalog.php`
- `application/controllers/Members.php`

Model:

- `application/models/Auth_model.php`
- `application/models/Menu_model.php`
- `application/models/Role_model.php`
- `application/models/User_model.php`
- `application/models/Page_model.php`
- `application/models/Audit_model.php`
- `application/models/Region_model.php`
- `application/models/Library_model.php`
- `application/models/Catalog_model.php`
- `application/models/Member_model.php`

View:

- `application/views/layouts/tabler.php`
- `application/views/rbac/*`
- `application/views/audit/index.php`
- `application/views/regions/index.php`
- `application/views/catalog/*`
- `application/views/members/*`
- `application/views/home/landing.php`
- `application/views/user/dashboard.php`
- `application/views/libraries/*`

CSS:

- `assets/css/pustaka.css`

Aset Branding:

- `img/favicon.ico`
- `img/logo-small.jpeg` untuk logo Kabupaten Rembang/Pemkab di brand utama.
- `img/perpusnas.png` untuk logo Perpusnas di landing page.

## Migrasi SQL yang Sudah Ada

- `sql/2026-07-28a_auth_rbac_sidebar_foundation.sql`
- `sql/2026-07-29a_libraries_gis_schema.sql`
- `sql/2026-07-29b_public_root_demo_users_routes.sql`
- `sql/2026-07-29c_rbac_sidebar_foundation_refine.sql`
- `sql/2026-07-29d_phase1_admin_gis_clean.sql`
- `sql/2026-07-29e_regions_crud_catalog_phase2.sql`
- `sql/2026-07-29f_members_migration_login_foundation.sql`
- `sql/2026-07-30a_inlislite_asset_migration.sql`
- `sql/2026-07-30b_catalog_member_crud_transaction_sync.sql`
- `sql/2026-07-30c_master_mapping_book_items_crud.sql`
- `sql/2026-07-30d_transaction_master_labels.sql`
- `sql/2026-07-30e_transaction_data_ui_sidebar.sql`
- `sql/2026-08-01a_member_login_nik_password.sql`
- `sql/2026-08-01b_member_number_service_labels.sql`

Jalankan berurutan setelah database `pustaka` dibuat.

## Cara Cek Cepat

Lint PHP:

```powershell
C:\xampp\php\php.exe -l application\controllers\Rbac.php
C:\xampp\php\php.exe -l application\views\layouts\tabler.php
```

Smoke test manual:

1. Buka `/`.
2. Login sebagai `superadmin`.
3. Buka `/admin`.
4. Buka `/rbac/roles`, `/rbac/users`, `/rbac/pages`, `/rbac/sidebar`.
5. Buka `/libraries`, `/libraries/create`, dan `/regions`.
6. Buka `/catalog`, `/catalog/create`, `/catalog/edit/1`, `/catalog/sync`, `/members`, `/members/create`, `/members/edit/1`, `/members/sync`, `/transactions`, `/transactions/sync`, `/assets-migration`, dan `/audit`.
7. Logout.
8. Login sebagai `pemustaka`, pastikan masuk ke `/user/dashboard`.

## Catatan Git

Remote:

```text
origin https://github.com/Achilles29/pustaka.git
```

Jangan commit:

- `tabler-dev/`
- `C:\xampp\htdocs\inlislite_v3.sql`
- `assets/uploads/*`
- `docs/_note.md`

Sebelum push:

```powershell
git status
git push --dry-run --porcelain origin main
git push origin main
```

## Status Terakhir

- Admin sidebar sudah memakai menu tree custom dengan ikon Tabler dari `sys_menu.icon`.
- Modul RBAC terpadu sudah berada di `/rbac/*`.
- Route lama `/users`, `/roles`, dan `/sidebar/manage` tetap kompatibel tetapi diarahkan ke RBAC baru.
- Landing publik dan dashboard pemustaka terpisah dari admin panel.
- Fase 1 sudah dilengkapi master wilayah, galeri/cover foto perpustakaan, scope admin lokal, dashboard operasional awal, dan Audit Log.
- Kodifikasi wilayah: Jawa Tengah `33`, Rembang `17`, Kecamatan `01..14` dengan `full_code` seperti `33.17.01`. Desa / Kelurahan memakai kode wilayah lengkap dan kolom `area_type`.
- Fase 2 sudah menarik katalog dan eksemplar dari INLISLite ke schema baru; katalog lokal saat validasi berisi 14.097 buku dan 22.927 eksemplar.
- Fondasi membership sudah ada: tabel `members`, `member_sync_runs`, dashboard `/members`, dan halaman `/members/sync`.
- Akun member hasil migrasi memakai role `USER`, username utama dari NIK/nomor identitas, password awal `perpus2026`, dan wajib ganti password saat login pertama.
- Standar UI terbaru: semua halaman harus mobile friendly, halaman gabungan memakai tab, dan tombol edit modal memakai penanda global `data-pustaka-open-modal`.
- `/rbac/sidebar` sudah mendukung drag-and-drop untuk urutan/parent menu.
- Logic INLISLite yang wajib diadopsi untuk importer sudah dicatat di `docs/INLISLITE_MAPPING.md`, terutama cover per worksheet, konten digital `dokumen_isi`, eksemplar `collections`, dan fallback foto anggota.
- Tema admin terbaru memakai biru Demokrat dan putih dari `assets/css/pustaka.css`.
- Tab halaman/tampilan memakai pola workspace segmented agar state aktif jelas.
- Tombol aksi tabel memakai ikon plus label singkat, bukan ikon-only.
- `/catalog` dan `/members` sudah memakai metric ribbon ringkas dan tab workspace agar tabel utama tidak terlalu turun.
- `/rbac/users` sudah dipisah: tabel user ringkas, pengaturan role/scope ada di modal edit per user.
- `/rbac/roles` sudah menampilkan tipe user lebih dulu; permission dibuka dari aksi `Hak Akses`, dan tipe user bisa tambah/edit dari UI.
- Favicon dan logo resmi lokal dari folder `img` sudah dipasang di landing, login, admin layout, dan dashboard pemustaka.
- Modal edit admin punya fallback JavaScript lokal; URL edit seperti `/regions?tab=districts&edit_district_id=2` harus tetap membuka modal walaupun Bootstrap/Tabler JS CDN tidak tersedia.
- Sinkronisasi katalog/member sudah menjadi aksi POST batch. Test kecil sudah memasukkan 3 buku, 5 eksemplar, 3 member, dan 3 akun login member.
- Akun login member hasil migrasi aktif dengan password awal `perpus2026`; status membership historis tetap tersimpan terpisah di tabel `members`.
- `/catalog` sudah berupa data table dengan search, filter status/tahun, pagination, cover, jumlah eksemplar, dan detail.
- CRUD manual katalog sudah tersedia di `/catalog/create` dan `/catalog/edit/{id}`. Delete memakai soft-delete `books.deleted_at`.
- `/members` sudah berupa data table dengan search, filter membership/akun, pagination, foto, status akun, dan detail.
- CRUD manual member sudah tersedia di `/members/create` dan `/members/edit/{id}`. Form dapat membuat/update akun login pemustaka role `USER`. Delete memakai soft-delete `members.deleted_at` dan menonaktifkan akun terkait.
- Mode sinkronisasi tersedia:
  - `Import data baru`,
  - `Update data lama`,
  - `Dry run / simulasi`.
- Status validasi terakhir:
  - `books`: 14.097,
  - `book_items`: 22.927,
  - `members`: 5.389,
  - akun login member: 5.389,
  - member tanpa akun: 0.
- Migrasi aset INLISLite:
  - modul admin: `/assets-migration`,
  - mirror penuh source upload ada di `assets/uploads/inlislite/source_mirror/uploaded_files`,
  - mirror penuh berisi 13.631 file / 1.486.730.062 byte,
  - cover referensi: 7.358 copied, 437 missing, 0 pending,
  - foto referensi member lokal: 3.008 copied, 2.355 missing, 0 pending,
  - file digital `dokumen_isi`: 4 copied.
- Katalog/member tidak boleh lagi bergantung pada URL `/inlislite3`; view memakai `cover_local_path`/`photo_local_path`, lalu fallback ke mirror lokal.
- Saat deploy beda server, `assets/uploads/inlislite` wajib ikut dipindahkan di luar Git karena `assets/uploads/*` memang di-ignore.
- Sinkronisasi transaksi harian tersedia di `/transactions/sync`.
- Data transaksi harian yang sudah penuh terhadap source lokal:
  - `member_visits`: 41.136 dari `memberguesses`,
  - `member_access_rules`: 26.864 dari hak pinjam kategori/lokasi,
  - `loan_transactions`: 31.561 dari `collectionloans`,
  - `loan_transaction_items`: 2.200 dari `collectionloanitems`.
- Detail member menampilkan tab aktivitas INLISLite untuk kunjungan, histori pinjam, dan hak pinjam setelah data transaksi tersinkron.
- Mapping master INLISLite sudah tersedia melalui `inlislite_master_references` dari migration `sql/2026-07-30c_master_mapping_book_items_crud.sql`.
- Migration tambahan `sql/2026-07-30d_transaction_master_labels.sql` menambahkan label dasar pada `member_visits` dan `member_access_rules`.
- Detail katalog sudah punya CRUD eksemplar:
  - `POST /catalog/items/store/{book_id}`,
  - `POST /catalog/items/update/{book_id}/{item_id}`,
  - `/catalog/items/delete/{book_id}/{item_id}`.
- Form eksemplar memakai modal `application/views/catalog/_item_modal.php` karena jumlah field masih moderat.
- Sync katalog berikutnya sudah mengisi raw source ID dan label master eksemplar.
- Sync member/transaksi berikutnya sudah melakukan refresh label master setelah batch import/update.
- Validasi mapping terakhir:
  - master referensi: 104,
  - kategori eksemplar: 22.927 / 22.927,
  - status eksemplar: 22.927 / 22.927,
  - ruang eksemplar: 22.917 / 22.927,
  - jenis member: 5.389 / 5.389,
  - lokasi kunjungan: 21.270 / 41.136.
- UI layanan harian tersedia di `/transactions` dengan tab Buku Tamu, Hak Layanan, Peminjaman, dan Item Koleksi.
- `/transactions/sync` tetap khusus sinkronisasi batch.
- Sidebar layanan menjadi parent `Layanan Harian`; `Aktivitas Layanan` dan `Sinkronisasi Layanan` menjadi child menu.
- Menu `Migrasi Aset` berada di bawah parent `Pengaturan Akses`.
- `/assets-migration` sekarang menampilkan audit item `missing`/`failed` terbaru untuk ditindaklanjuti.
- Kartu anggota digital tersedia di `/user/dashboard` untuk akun member yang terhubung ke tabel `members`.
- Token kartu digital memakai HMAC dari ID member, nomor anggota, dan source ID.
- QR kartu digital saat ini dibuat di frontend dengan CDN `qrcodejs@1.0.0`; untuk produksi offline, ganti dengan generator QR lokal/server-side.
- Portal katalog publik tersedia di `/katalog` dengan search, filter kategori/tahun/ketersediaan, pagination, cover, dan detail eksemplar.
- Filter katalog publik sekarang lebih rinci:
  - kategori koleksi,
  - media,
  - aturan pinjam,
  - lokasi perpustakaan,
  - tahun,
  - ketersediaan,
  - tombol reset filter.
- Detail publik katalog tersedia di `/katalog/detail/{id}` dan hanya membaca buku `published` serta eksemplar `is_public = 1`.
- Landing publik `/` sudah mengarah ke portal katalog dan menampilkan preview koleksi dari database lokal `pustaka`.
- Login member lokal terakhir divalidasi dengan NIK `3317101401620001` / `perpus2026` dan fallback nomor anggota `M.63` / `perpus2026`.
- Nomor anggota manual baru otomatis memakai format `PDR-3317-YYYY-000001`; member hasil migrasi tetap memakai nomor lama INLISLite.
- Landing publik, dashboard pemustaka, dan verifikasi kartu sudah dipoles mobile friendly.
- Nama tampilan modul transaksi diubah menjadi `Layanan Harian`; route teknis tetap `/transactions`.
- Reservasi/request buku dari katalog publik sudah tersedia:
  - form publik di `/katalog/detail/{id}#request-buku`,
  - route POST `/katalog/request/{book_id}`,
  - antrean admin `/catalog/requests`,
  - tabel `book_requests`.
- Perpanjangan membership sudah tersedia:
  - form pemustaka di `/user/dashboard#membership-renewal`,
  - route POST `/membership/renewal/request`,
  - antrean admin `/members/renewals`,
  - tabel `membership_renewal_requests`.
- Detail member sekarang punya panel operasional kartu digital:
  - blokir kartu dengan alasan,
  - aktifkan kembali kartu,
  - verifikasi kartu menolak kartu dengan `card_status = blocked`.
- Parent sidebar `Layanan Digital` berisi `Request Buku`, `Perpanjangan`, `Reader PDF Aman`, `Pojok Baca`, dan `Event Literasi`.
- Admin topbar punya `Kotak Masuk` global untuk antrean pending dari pendaftaran online, request buku, dan perpanjangan membership.
- Form pendaftaran member online tersedia di `/membership/register`.
- Antrean/verifikasi pendaftaran online tersedia di `/members/registrations`.
- Pendaftaran online memakai tabel `member_registration_requests`.
- Berkas pendaftaran online tersimpan di `assets/uploads/member_registrations/YYYY/MM`.
- Pendaftar dengan NIK tidak diawali `3317` dianggap luar Kabupaten Rembang dan wajib upload surat keterangan domisili/sekolah/pondok/instansi yang berlaku.
- Approval pendaftaran online membuat member aktif, nomor otomatis `PDR-3317-YYYY-000001`, akun login username NIK, password awal `perpus2026`.
- Fondasi Reader PDF Aman tersedia di `/reader/assets` dan route awal `/reader/read/{asset_id}`.
- Pengaturan Pojok Baca Digital tersedia di `/reading-points`:
  - tambah `/reading-points/create`,
  - edit `/reading-points/edit/{id}`,
  - field perpustakaan pengampu, mitra, koordinat, radius, kuota, satuan kuota, jam aktif, status.
- Pojok Baca memakai tabel `reading_points`, `reading_tokens`, dan `reading_sessions`.
- Pojok Baca form sekarang memakai Leaflet map picker dengan marker draggable untuk mengisi koordinat.
- Fondasi Event Literasi tersedia di `/events` dengan tabel `literacy_events` dan `event_registrations`.
- Master Buku tersedia di `/catalog/masters`.
- Master buku memakai tabel:
  - `book_content_categories`,
  - `book_classification_masters`.
- Kolom kurasi katalog:
  - `books.content_category_id`,
  - `books.content_classification_id`.
- Katalog admin dan publik sudah dapat memfilter kategori isi serta klasifikasi isi.
- Form member admin dan pendaftaran publik memakai pilihan baku dari `Member_model::form_options()`.
- Data member lama dari INLISLite sudah dinormalisasi agar kolom operasional menyimpan label, bukan ID angka. Label tetap bersumber dari `inlislite_master_references`.
- Pendaftaran publik redirect ke `/membership/register/pending/{public_token}` dan menampilkan username NIK serta password awal `perpus2026`.
- Token pending publik disimpan di `member_registration_requests.public_token`; jangan ganti menjadi kode antrean berurutan.
- Kategori/klasifikasi buku aplikasi bukan master mentah INLISLite. Keduanya adalah master kurasi Pustaka:
  - `Kategori Isi` = payung pencarian pemustaka,
  - `Klasifikasi Isi/DDC` = filter subjek.
- Jangan paksa kategori dan klasifikasi menjadi parent-child murni di database; hubungan keduanya bisa saling silang. Untuk UX, katalog publik boleh menyaring klasifikasi berdasarkan kategori yang dipilih.
- Check-in Pojok Baca member tersedia di `/user/reading-checkin`.
- POST check-in `/user/reading-checkin/store` menerbitkan token harian di `reading_tokens` jika GPS berada dalam radius `reading_points`.
- SOP token Pojok Baca dicatat di `docs/POJOK_BACA_TOKEN_SOP.md`.
- Monitoring token admin tersedia di `/reading-points/tokens`.
- Reader `location_only` sudah meminta token aktif. Akses luar lokasi mengurangi kuota, sedangkan akses dalam radius Pojok Baca/perpustakaan tidak mengurangi kuota.
- Layanan harian menghitung akses digital dari `reading_sessions`, termasuk akses luar lokasi.
- Catatan keamanan reader: file PDF belum boleh disajikan sebagai URL publik. Tahap berikutnya wajib membuat storage non-public, renderer per halaman/token, watermark dinamis, rate limit, dan audit akses penuh.
- Visual refresh global sudah diterapkan di `assets/css/pustaka.css`:
  - font `Plus Jakarta Sans` untuk UI,
  - font `Fraunces` untuk headline publik tertentu,
  - tema biru Demokrat/putih,
  - sidebar lebih kontras,
  - tab/filter/tabel/tombol lebih compact dan mobile friendly.
- Semua layout mandiri yang memakai Tabler Icons sudah dikunci ke `@tabler/icons-webfont@3.34.1`; jangan kembali memakai `@latest`.
- Landing `/` sudah dipoles ulang sebagai halaman publik brand-forward dengan peta Rembang dan strip layanan utama.
- Katalog publik `/katalog` sudah dibuat compact agar hasil pencarian terlihat cepat.
- 10 PDF sampel legal/free tersedia di `storage/ebooks/free_samples`.
- Cover lokal 10 PDF sampel tersedia di `assets/uploads/sample_ebooks/covers` dan `books.cover_local_path` sudah diisi.
- `storage/.htaccess` menolak akses langsung ke file PDF; reader harus mengambil file melalui controller.
- Seed PDF sampel ada di `sql/2026-08-02b_seed_free_sample_ebooks.sql`; bisa dijalankan ulang karena data buku memakai `source_system = sample_free_pdf`.
- Data PDF sampel sudah masuk:
  - `books`: 10,
  - `book_items`: 10,
  - `digital_assets`: 10,
  - 2 aset `download_allowed`,
  - 8 aset `location_only`.
- Untuk uji cepat, buka `/katalog?q=Project%20Gutenberg`; halaman menampilkan 10 sampel dengan cover.
- Untuk uji reader admin, login `superadmin` lalu buka `/reader/read/3` atau `/reader/assets`.
- Endpoint `reader/stream/{asset_id}` sudah men-stream PDF dengan login/session check. Ini masih mode uji awal; versi final tetap perlu render per halaman, watermark dinamis, rate limit granular, dan audit detail.
- Untuk uji reader member, login `3317101401620001` / `perpus2026`.
- Dashboard member `/user/dashboard` punya rak `Buku Digital` dan tombol `Baca Online`.
- Katalog publik `/katalog` dan detail katalog sekarang menampilkan tombol `Baca Online` jika buku punya aset digital aktif.
- Member yang sudah login tidak lagi melihat tombol `Masuk` di katalog publik; nav menampilkan Dashboard/Logout.
- Halaman member, Pojok Baca, reader, dan katalog publik punya bottom navigation mobile.
- Reader saat ini masih mode PDF inline/scroll untuk uji. Rencana final: render per halaman non-public, watermark dinamis, rate limit, audit sesi, lalu UI swipe/flip.
- Landing `/` sudah sadar session login:
  - guest melihat `Daftar Member` dan `Masuk`,
  - member/admin/superadmin melihat `Dashboard` dan `Logout`,
  - admin/superadmin diarahkan ke `/admin`,
  - member diarahkan ke `/user/dashboard`.
- Landing page mendapat refresh visual premium biru Demokrat, putih, dan aksen emas; override CSS final berada di bagian paling bawah `assets/css/pustaka.css`.
- Screenshot validasi landing terbaru ada di `storage/debug_screenshots/landing-premium-20260802.png`.
- Tema global terbaru memakai `Executive contrast layer` di `assets/css/pustaka.css`:
  - background admin abu terang,
  - page header putih,
  - card/form/tabel lebih tegas,
  - header card punya aksen biru vertikal,
  - sidebar biru Demokrat ke navy lebih solid,
  - landing hero lebih pendek dengan peta sebagai panel kanan.
- Screenshot validasi tema terbaru:
  - `storage/debug_screenshots/landing-theme-v2.png`,
  - `storage/debug_screenshots/admin-theme-v2.png`,
  - `storage/debug_screenshots/reading-point-form-theme-v2.png`,
  - `storage/debug_screenshots/landing-mobile-theme-v2c.png`.
- Lapisan tema final sekarang dipisah ke `assets/css/pustaka-polish.css` dan dimuat setelah `pustaka.css` dengan cache-buster `?v=20260802a`.
- Jangan lanjut menambah override besar di `pustaka.css`; untuk polish visual berikutnya, prioritaskan `pustaka-polish.css` agar tidak kalah oleh duplikasi lama.
- Landing terbaru:
  - peta lebih pendek,
  - panel peta lebih rapat ke kanan,
  - statistik hero disembunyikan,
  - `Mulai jelajah` disembunyikan,
  - center peta digeser ke `[-6.7750, 111.3900]`.
- Landing compact terbaru memakai cache-buster `pustaka-polish.css?v=20260802c`; hero dipadatkan lagi, chip layanan hero disembunyikan, dan peta ditarik lebih ke tengah untuk mengurangi ruang kosong.
- Session login di `application/config/config.php` memakai `sess_expiration = 259200`, sehingga cookie `ci_session` bertahan 3 hari sejak aktivitas terakhir.
- Login `/login` sudah menjadi dua panel dan punya link ke `/membership/register`.
- Pendaftaran `/membership/register` sudah punya intro dan form panel baru, link login, nav `Beranda`, dan mobile guard untuk viewport kecil.
- Screenshot validasi polish terbaru:
  - `storage/debug_screenshots/landing-polish-v3d.png`,
  - `storage/debug_screenshots/login-polish-v3b.png`,
  - `storage/debug_screenshots/register-polish-v3.png`,
  - `storage/debug_screenshots/register-mobile-polish-v3g.png`.
- Landing grid terbaru memakai `pustaka-polish.css?v=20260802d`; peta tidak lagi absolut di desktop sehingga judul tidak terpotong dan ruang kanan hilang.
- Screenshot validasi landing terbaru: `storage/debug_screenshots/landing-grid-v5.png`.
- Migration kunjungan terbaru: `sql/2026-08-02c_visit_channels_guestbook.sql`.
- Pencatatan kunjungan baru dipusatkan di `application/models/Visit_model.php`.
- `member_visits` sekarang punya kanal dan origin:
  - `member_dashboard` untuk akses dashboard member,
  - `digital_access` untuk sesi reader,
  - `reading_point` untuk check-in GPS Pojok Baca,
  - `library_guestbook` untuk tamu fisik non-member,
  - `service_monitor` untuk member yang dicari manual di monitor,
  - `qr_checkin` untuk QR dinamis monitor pelayanan.
- Dashboard member mencatat kunjungan online sekali per member per hari.
- Reader member otomatis mencatat `digital_access`; `visit_origin` membedakan akses luar lokasi, Pojok Baca, atau perpustakaan.
- Monitor buku tamu tersedia di `/guestbook/monitor`.
- QR monitor memakai tabel `visit_kiosk_qr_tokens` dan default refresh dari `visit_kiosk_settings.qr_refresh_seconds` = 60 detik.
- Form monitor mendukung:
  - pengunjung non-member,
  - member via pencarian AJAX NIK/nomor anggota/nama,
  - kunjungan rombongan dengan `visitor_count`, `group_name`, dan `group_leader_name`.
- Endpoint pencarian member monitor: `/guestbook/search-members?q=keyword`.
- Submit member monitor mengirim `member_id` hasil pilihan AJAX agar nama yang mirip tidak salah tercatat.
- Scan QR memakai `/guestbook/checkin/{token}`; jika belum login diarahkan ke `/login`, lalu setelah login check-in dilanjutkan.
- Pengaturan monitor buku tamu tersedia di `/guestbook/settings`.
- Pengaturan yang bisa diubah:
  - `qr_refresh_seconds`, batas 15-600 detik,
  - `default_visit_library_id`.
- Permission `guestbook.settings` diberikan ke `SUPERADMIN` dan `ADMIN`.
- Menu `Pengaturan Buku Tamu` berada di rumpun `Layanan Harian`.
- `/transactions` tab Buku Tamu sudah menampilkan filter kanal dan kolom kanal/origin.
- Sidebar admin terbaru diatur oleh `sql/2026-08-02d_reports_sidebar_reorder.sql` dengan rumpun:
  - Dashboard,
  - Laporan & Analitik,
  - Jejaring & Agenda,
  - Koleksi & Katalog,
  - Keanggotaan,
  - Layanan Harian,
  - Layanan Digital,
  - Data Master,
  - Pengaturan Sistem.
- Modul laporan kunjungan tersedia di `/reports/visits` dan shortcut `/reports`.
- File laporan:
  - `application/controllers/Reports.php`,
  - `application/models/Report_model.php`,
  - `application/views/reports/visits.php`.
- Laporan kunjungan punya filter tahunan, bulanan, harian, dan custom range tanggal.
- Laporan menampilkan KPI total orang, entri kunjungan, member, rombongan, grafik tren, grafik kanal, breakdown origin/metode check-in, dan data terbaru.
- Permission laporan `reports.visits` sudah diberikan ke `SUPERADMIN` dan `ADMIN`.
- Export laporan tersedia:
  - `/reports/visits/print` untuk cetak atau Save as PDF,
  - `/reports/visits/excel` untuk file `.xls`.
- Export mengikuti query filter aktif di `/reports/visits`.
- Export Excel tidak memakai PhpSpreadsheet; formatnya HTML table dengan MIME Excel agar ringan di CI3/XAMPP.
- Reader aman terbaru memakai `sql/2026-08-02f_reader_secure_session_audit.sql`.
- `reading_sessions` punya `secure_token` dan `last_seen_at`.
- Audit reader tersimpan di `reader_access_logs`.
- Stream PDF member wajib memakai URL bertoken:
  - `/reader/stream/{asset_id}?session={reading_session_id}&token={secure_token}`.
- Stream tanpa token/sesi valid ditolak.
- Stream PDF utuh hanya boleh untuk aset `is_downloadable = 1` dan `access_policy = download_allowed`.
- Aset non-downloadable tidak boleh pernah diberi URL `/reader/stream/{asset_id}` di HTML member dan endpoint stream tetap menolak paksa dengan `403`.
- View member reader memakai PDF.js canvas untuk aset bebas unduh, bukan iframe PDF browser.
- Untuk aset non-downloadable, reader memakai render server-side per halaman:
  - route metadata: `/reader/page-info/{asset_id}`,
  - route gambar: `/reader/page/{asset_id}/{page_number}`,
  - response halaman harus `image/png`,
  - view memakai `secure-image-reader`,
  - jangan memakai PDF.js atau iframe untuk aset non-downloadable.
- Navigasi reader member:
  - tombol atas sebelumnya/berikutnya,
  - tap area kiri/kanan halaman,
  - swipe kiri/kanan di mobile,
  - keyboard ArrowLeft/ArrowRight di desktop.
- Animasi reader berada di `assets/css/pustaka-polish.css`:
  - `.is-turning-next`,
  - `.is-turning-prev`,
  - `@keyframes pdrPageInNext`,
  - `@keyframes pdrPageInPrev`.
- Renderer server-side:
  - script: `scripts/render_pdf_page.py`,
  - dependency: Python, Pillow, dan PyMuPDF,
  - instalasi dependency lokal: `python -m pip install --user pymupdf`,
  - jika service Apache/XAMPP tidak menemukan Python, set environment `PUSTAKA_PYTHON` ke path `python.exe`.
- Cache render halaman berada di `storage/cache/reader_pages`.
- Folder `/storage/cache/` di-ignore Git karena berisi hasil render runtime.
- `storage/.htaccess` harus tetap `Require all denied` agar PDF dan cache tidak bisa diakses langsung.
- Requirement lanjutan reader aman:
  - storage PDF tetap non-public,
  - endpoint member hanya mengirim gambar halaman ber-watermark,
  - jangan mengirim file PDF utuh ke browser untuk aset non-downloadable,
  - pertahankan audit `reader_access_logs`, token sesi, dan rate limit.
- Watermark dinamis ditampilkan di canvas reader.
- Audit halaman memakai POST `/reader/audit-page`.
- Event audit reader yang sudah dipakai:
  - `session_opened`,
  - `pdf_stream`,
  - `page_rendered`,
  - `rate_limited`,
  - `blocked`.
- Rate limit awal stream: 12 request per sesi per menit.
- Rate limit render halaman: 80 halaman per sesi per menit.
- Cache-buster tema terbaru `pustaka-polish.css?v=20260802j`.
- Manajemen ebook admin terbaru:
  - migration: `sql/2026-08-03a_digital_asset_rights_admin.sql`,
  - list: `/reader/assets`,
  - tambah: `/reader/assets/create`,
  - edit: `/reader/assets/edit/{id}`,
  - store: `POST /reader/assets/store`,
  - update: `POST /reader/assets/update/{id}`,
  - status: `POST /reader/assets/status/{id}`.
- Audit reader detail tersedia di `/reader/audit`.
- Kolom hak publikasi `digital_assets`:
  - `rights_basis`,
  - `rights_holder`,
  - `license_url`,
  - `permission_reference`,
  - `permission_starts_at`,
  - `permission_ends_at`,
  - `access_notes`.
- Upload PDF manual disimpan di `storage/ebooks/manual/YYYY/MM`; jangan pindahkan ke `assets/uploads`.
- Validasi upload PDF admin saat ini:
  - ekstensi `.pdf`,
  - header `%PDF`,
  - source system `manual_upload`.
- Rule penting:
  - `access_policy = download_allowed` boleh stream PDF utuh,
  - policy lain harus `is_downloadable = 0` dan memakai render halaman PNG.
- Preview admin untuk aset non-downloadable:
  - `/reader/admin-page-info/{asset_id}`,
  - `/reader/admin-page/{asset_id}/{page}`.
- `/reader/stream/{asset_id}` menolak aset non-downloadable untuk admin maupun member.
- Relasi katalog dan ebook:
  - `books` adalah induk bibliografi semua buku,
  - tidak semua `books` adalah ebook,
  - ebook/PDF adalah aset opsional di `digital_assets` dengan FK `book_id`,
  - setiap ebook wajib terkait satu buku katalog,
  - detail katalog `/catalog/detail/{book_id}` menampilkan panel `Ebook / Aset Digital`,
  - tombol tambah ebook dari detail katalog membuka `/reader/assets/create?book_id={book_id}`,
  - form `/catalog/create` hanya membuat data induk buku; ebook ditambahkan setelah buku tersimpan.
