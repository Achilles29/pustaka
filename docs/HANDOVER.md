# Handover Developer

Tanggal update: 2026-07-29

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

## Route Penting

- `/` landing publik.
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
6. Buka `/catalog`, `/catalog/sync`, `/members`, `/members/sync`, dan `/audit`.
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
- Fase 2 sudah dimulai dengan schema katalog baru dan fondasi sinkronisasi INLISLite, tetapi belum melakukan import data katalog.
- Fondasi membership sudah ada: tabel `members`, `member_sync_runs`, dashboard `/members`, dan halaman `/members/sync`.
- Akun member hasil migrasi memakai role `USER`, username dari nomor anggota, password awal `PustakaRembang#2026`, dan wajib ganti password saat login pertama.
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
- Akun login member hasil migrasi aktif dengan password awal `PustakaRembang#2026`; status membership historis tetap tersimpan terpisah di tabel `members`.
- `/catalog` sudah berupa data table dengan search, filter status/tahun, pagination, cover, jumlah eksemplar, dan detail.
- `/members` sudah berupa data table dengan search, filter membership/akun, pagination, foto, status akun, dan detail.
- Mode sinkronisasi tersedia:
  - `Import data baru`,
  - `Update data lama`,
  - `Dry run / simulasi`.
- Status validasi terakhir:
  - `books`: 14.097,
  - `book_items`: 22.927,
  - `members`: 1.593,
  - akun login member: 1.593,
  - member tanpa akun: 0.
