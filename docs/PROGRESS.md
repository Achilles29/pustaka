# Progress Proyek

## 2026-07-28 21:23 WIB

Status: inisiasi proyek.

Yang sudah dilakukan:

- Membaca struktur awal folder `C:\xampp\htdocs\pustaka`.
- Menemukan CodeIgniter 3.1.13 sebagai basis framework awal.
- Menemukan dump database `inlislite_v3.sql` dengan ukuran sekitar 108 MB.
- Mengecek folder `docs`; folder sudah ada dan masih kosong.
- Mengambil gambaran schema INLISLite, terutama tabel `catalogs`, `catalogfiles`, `collections`, `members`, `library`, `locations`, `location_library`, `roles`, `user`, `users`, `collectionloans`, dan `readinlocation`.
- Mengidentifikasi bahwa dump berisi data pribadi anggota sehingga seluruh dokumentasi dan pekerjaan berikutnya harus menjaga PII.

Dokumen yang dibuat:

- `docs/README.md`
- `docs/ROADMAP.md`
- `docs/INLISLITE_MAPPING.md`
- `docs/SECURITY_AND_ACCESS.md`
- `docs/PROGRESS.md`

Keputusan awal:

- Roadmap dibuat terlebih dahulu sebelum membangun ulang aplikasi.
- INLISLite akan dipakai sebagai sumber/staging data, bukan schema operasional utama aplikasi baru.
- Proteksi PDF akan dirancang berlapis; klaim anti-download absolut tidak dijanjikan karena secara teknis tidak realistis di browser.

Langkah berikutnya:

- Tentukan stack final: lanjut CodeIgniter 3 untuk MVP atau mulai framework baru.
- Buat rancangan ERD aplikasi baru.
- Buat backlog MVP teknis.
- Jika memakai CodeIgniter 3, mulai setup struktur `application` untuk auth, dashboard, dan modul perpustakaan.

## 2026-07-28 21:48 WIB

Status: scan ulang, import database, dan setup CodeIgniter selesai.

Yang sudah dilakukan:

- Scan ulang folder aplikasi INLISLite di `C:\xampp\htdocs\inlislite3`.
- Mengidentifikasi INLISLite sebagai aplikasi Yii advanced dengan modul `backend`, `frontend`, `opac`, `digitalcollection`, `keanggotaan`, `bacaditempat`, `guestbook`, `api`, `console`, `common`, dan `inliscore`.
- Import `C:\xampp\htdocs\pustaka\inlislite_v3.sql` ke MariaDB lokal database `inlislite_v3`.
- Validasi database: 185 tabel, 47 routine, 14.097 katalog, 22.927 eksemplar, 5.389 anggota.
- Scan folder `uploaded_files`: 13.631 file dengan ukuran 1.417,86 MB.
- Mencatat aset utama: `sampul_koleksi`, `foto_anggota`, `dokumen_isi`, `settings`, `aplikasi`, dan `templates`.
- Membuat laporan CSV row count semua tabel dan inventory file.
- Mencocokkan referensi `members.PhotoUrl` dan `catalogs.CoverURL` terhadap file fisik.
- Memindahkan CodeIgniter 3.1.13 dari subfolder `CodeIgniter-3.1.13` ke root `C:\xampp\htdocs\pustaka`.
- Mengatur `base_url`, `.htaccess`, koneksi database `inlislite_v3`, autoload `database/session`, helper dasar, dan folder session.
- Smoke test `php index.php` berhasil merender halaman welcome CodeIgniter.
- Smoke test query database berhasil membaca jumlah katalog.
- Mencari referensi theme gratis dan mencatat kandidat utama.

Dokumen/laporan yang ditambahkan:

- `docs/SCAN_SUMMARY.md`
- `docs/THEME_REFERENCES.md`
- `docs/PRODUCT_VISION_PLUS.md`
- `docs/SCAN_DATABASE_TABLE_COUNTS.csv`
- `docs/SCAN_INLISLITE_FILE_EXTENSIONS.csv`
- `docs/SCAN_INLISLITE_TOP_FOLDERS.csv`
- `docs/SCAN_INLISLITE_UPLOADED_FILES.csv`
- `docs/SCAN_ASSET_REFERENCE_MATCH.csv`

Catatan penting:

- `catalogfiles` kosong, tetapi folder `dokumen_isi` berisi file arsip. Koleksi digital perlu audit lanjutan.
- Banyak foto anggota tidak cocok langsung dengan nilai `PhotoUrl`; migrasi perlu strategi pencocokan tambahan.
- Semua katalog pada dump saat ini memakai `Worksheet_id = 1` atau Monograf.
- File `_note.md` milik user dibaca sebagai konteks dan tidak diubah.

Langkah berikutnya:

- Tentukan pilihan theme final, rekomendasi sementara: Tabler untuk aplikasi baru, AdminLTE sebagai fallback cepat.
- Buat ERD/schema aplikasi baru berdasarkan hasil scan.
- Buat modul dashboard awal di CodeIgniter.
- Buat migrator/importer untuk katalog, anggota, koleksi, foto anggota, dan cover buku.

## 2026-07-28 21:58 WIB

Status: Tabler lokal disesuaikan dan dashboard awal dibuat.

Yang sudah dilakukan:

- Menerima keputusan penggunaan Tabler dari folder `C:\xampp\htdocs\pustaka\tabler-dev`.
- Mengecek struktur `tabler-dev`; folder tersebut adalah source/dev repo, bukan dist siap pakai.
- Mengaktifkan pnpm lewat Corepack dan menjalankan `corepack pnpm install`.
- Build `@tabler/core` dengan `corepack pnpm --filter @tabler/core build`.
- Memastikan aset Tabler tersedia di `tabler-dev/core/dist`.
- Membuat layout CodeIgniter `application/views/layouts/tabler.php`.
- Mengubah `Welcome` menjadi dashboard awal berbasis Tabler.
- Membuat view `application/views/dashboard/index.php`.
- Menambahkan stylesheet custom `assets/css/pustaka.css`.
- Menambahkan fitur sinkronisasi perpustakaan se-Kabupaten Rembang berbasis GIS ke `PRODUCT_VISION_PLUS.md`.
- Verifikasi HTTP lokal `http://localhost/pustaka/` berhasil dengan status 200.
- Verifikasi aset `tabler.min.css` dan `assets/css/pustaka.css` berhasil diakses lewat localhost.

Catatan penting:

- Aset Tabler saat ini dirujuk langsung dari `tabler-dev/core/dist`.
- Dashboard awal sudah membaca statistik dari database `inlislite_v3`.
- Modul GIS baru berupa konsep dan placeholder UI; implementasi peta asli nanti sebaiknya memakai Leaflet.js.

Langkah berikutnya:

- Tambahkan Leaflet.js dan schema `libraries`/`library_photos`/`reading_points`.
- Buat migrasi data perpustakaan dan lokasi dari INLISLite ke schema baru.
- Mulai modul CRUD perpustakaan berbasis GIS.

## 2026-07-28 22:08 WIB

Status: fondasi database aplikasi baru, role, permission, dan menu database sudah dibuat.

Keputusan terbaru:

- Database operasional aplikasi baru adalah `pustaka`.
- Database `inlislite_v3` hanya dipakai sebagai acuan, staging, dan sumber migrasi.
- Implementasi role awal memakai tiga role inti: `SUPERADMIN`, `ADMIN`, dan `USER`.
- Role turunan seperti admin sekolah, admin desa, dan admin mitra akan dibuat sebagai pengembangan dari `ADMIN` setelah schema perpustakaan/unit tersedia.
- Registry halaman, permission role, user role, dan menu/sidebar disimpan di database.

Yang sudah dilakukan:

- Membuat folder `sql`.
- Membuat migrasi awal `sql/2026-07-28a_auth_rbac_sidebar_foundation.sql`.
- Membuat database MariaDB `pustaka` dengan charset `utf8mb4`.
- Menjalankan migrasi awal ke database `pustaka`.
- Menambahkan tabel `auth_user`, `auth_role`, `auth_user_role`, `auth_session_log`, `sys_page`, `auth_role_permission`, `auth_user_permission_override`, `sys_menu`, `sys_sidebar_favorite`, `audit_log`, dan `ci_sessions`.
- Menambahkan seed role `SUPERADMIN`, `ADMIN`, dan `USER`.
- Menambahkan user awal `superadmin`.
- Menambahkan seed registry halaman dan menu/sidebar awal.
- Mengubah koneksi default CodeIgniter ke database `pustaka`.
- Menambahkan koneksi database kedua bernama `inlislite` untuk membaca `inlislite_v3`.
- Membuat model `Menu_model` untuk mengambil tree menu/sidebar dari database berdasarkan role.
- Mengubah layout Tabler agar menu utama dibaca dari database.
- Mengubah dashboard agar menampilkan metrik database baru `pustaka` dan sumber migrasi `inlislite_v3` secara terpisah.

Validasi:

- Database `pustaka`: 11 tabel.
- Role: 3.
- User awal: 1.
- Registry halaman: 10.
- Permission role: 21.
- Menu/sidebar: 10.
- `SUPERADMIN` memiliki akses view ke 10 halaman.
- `ADMIN` memiliki akses view ke 7 halaman operasional.
- `USER` memiliki akses view ke 4 halaman dasar.
- Render CLI `php index.php` berhasil tanpa error.

Dokumen yang ditambahkan:

- `docs/AUTH_RBAC_SIDEBAR.md`

Langkah berikutnya:

- Smoke test HTTP lokal setelah perubahan koneksi database.
- Membuat modul login berbasis `auth_user`.
- Membuat `MY_Controller` untuk memuat user aktif, role, permission, dan menu.
- Membuat UI Manajemen Sidebar seperti pola di aplikasi finance.
- Membuat UI Role & Hak Akses.

## 2026-07-28 22:30 WIB

Status: langkah berikutnya selesai, auth dan UI pengaturan dasar sudah aktif.

Yang sudah dilakukan:

- Membuat `application/models/Auth_model.php` untuk login bcrypt, role aktif, permission gabungan role, override permission, dan log auth.
- Membuat `application/core/MY_Controller.php` sebagai base controller halaman terproteksi.
- Membuat `application/controllers/Auth.php` untuk login dan logout.
- Mengubah `Welcome` agar dashboard wajib login dan wajib permission `dashboard.index`.
- Mengubah layout Tabler agar menampilkan user aktif, role aktif, menu dari database, dan logout.
- Membuat `application/controllers/Sidebar.php` dan `application/views/sidebar/manage.php`.
- Membuat UI Manajemen Sidebar: tambah/edit menu, parent, halaman permission, URL, ikon, urutan, visible, aktif/nonaktif.
- Membuat `application/controllers/Roles.php`, `application/models/Role_model.php`, dan `application/views/roles/index.php`.
- Membuat UI Role & Hak Akses berbentuk matrix halaman x aksi.
- Membuat `application/controllers/Users.php`, `application/models/User_model.php`, dan `application/views/users/index.php`.
- Membuat UI Manajemen User: tambah user, role assignment, dan aktif/nonaktif.
- Membuat placeholder terproteksi RBAC untuk modul `libraries`, `catalog`, `members`, `reading-points`, dan `events`.
- Menambahkan routes untuk login, logout, dashboard, modul operasional, users, roles, dan sidebar.

Validasi:

- Lint PHP bersih untuk controller, model, dan view baru/terubah.
- Login `superadmin` / `admin123` berhasil.
- Setelah login, URL berikut status 200: `welcome/index`, `libraries`, `catalog`, `members`, `reading-points`, `events`, `users`, `roles`, dan `sidebar/manage`.
- Semua URL tersebut memuat layout Pustaka dan tombol logout.
- Setelah logout, akses root kembali ke halaman login dan dashboard tidak tampil.

Catatan:

- Password default `superadmin` masih `admin123`; modul ganti password wajib dibuat sebelum aplikasi dipakai serius.
- UI Manajemen Sidebar saat ini sudah CRUD dasar, belum drag/drop.
- Role jangka panjang seperti admin sekolah/desa/mitra akan paling bersih dibuat setelah tabel perpustakaan/unit (`libraries`) tersedia.

Langkah berikutnya:

- Buat schema inti perpustakaan berbasis GIS: `libraries`, `library_photos`, `library_staff`, `reading_points`.
- Implementasi CRUD Perpustakaan GIS dengan peta Leaflet.
- Tambahkan migrator awal dari `inlislite_v3` ke schema `pustaka`.

## 2026-07-29 05:35 WIB

Status: admin sidebar kiri dan CRUD awal Perpustakaan GIS selesai.

Yang sudah dilakukan:

- Mengubah layout admin Tabler dari navbar menu atas menjadi sidebar kiri (`navbar-vertical`).
- Sidebar kiri tetap mengambil menu dari `sys_menu` dan tetap difilter memakai permission user aktif.
- Menambahkan style admin sidebar di `assets/css/pustaka.css`.
- Membuat migrasi `sql/2026-07-29a_libraries_gis_schema.sql`.
- Menambahkan tabel `library_types`, `libraries`, dan `library_photos`.
- Menambahkan seed jenis perpustakaan: Perpusda, Sekolah, Desa, Swasta, Komunitas, dan Mitra.
- Membuat `application/models/Library_model.php`.
- Mengganti controller `Libraries` dari placeholder menjadi CRUD awal.
- Menambahkan routes `libraries/create`, `libraries/store`, `libraries/edit/{id}`, `libraries/update/{id}`, dan `libraries/toggle/{id}`.
- Membuat halaman daftar Perpustakaan GIS dengan Leaflet/OpenStreetMap.
- Membuat form tambah/edit dengan peta picker koordinat dan upload foto.
- Membuat folder upload `assets/uploads/libraries`.
- Membuat dokumentasi `docs/LIBRARIES_GIS.md`.

Validasi:

- Migrasi GIS berhasil dijalankan ke database `pustaka`.
- `library_types` berisi 6 jenis awal.
- Lint PHP bersih untuk layout, controller, model, dan view GIS.
- Login `superadmin` berhasil.
- Dashboard memuat sidebar kiri.
- Halaman `libraries` status 200 dan memuat Leaflet serta elemen peta.
- Halaman `libraries/create` status 200 dan memuat peta picker serta upload foto.
- Smoke test insert perpustakaan sementara berhasil, lalu data sementara dihapus kembali.

Catatan:

- Leaflet saat ini memakai CDN `https://unpkg.com/leaflet@1.9.4`.
- CRUD foto baru sebatas upload dan tampilkan foto; hapus foto/set cover menyusul.
- Belum ada master kecamatan/desa, sehingga input wilayah masih teks bebas.

Langkah berikutnya:

- Tambah fitur hapus foto dan set cover.
- Buat master kecamatan/desa Rembang atau import dari sumber resmi.
- Tambahkan pembatasan data per admin lokal menggunakan `auth_user.library_id`.
- Mulai modul Pojok Baca Digital yang memakai koordinat, radius, GPS lock, dan kuota/token.

## 2026-07-29 06:10 WIB

Status: root publik, redirect login per role, dummy user, dan diagnosa Git selesai.

Yang sudah dilakukan:

- Membuat halaman root `/` sebagai landing page publik yang bisa diakses tanpa login.
- Membuat `Home` controller dan view `home/landing.php`.
- Landing page memakai peta Leaflet/OpenStreetMap sebagai visual utama.
- Memindahkan admin dashboard ke route `/admin`.
- Route `/dashboard` diarahkan ke `/admin`.
- Membuat dashboard pemustaka di `/user/dashboard`, tanpa sidebar admin.
- Mengubah redirect login:
  - `SUPERADMIN` ke admin panel.
  - `ADMIN` ke admin panel.
  - `USER` ke dashboard pemustaka.
- Menambahkan dummy user:
  - `superadmin` / `admin123` role `SUPERADMIN`.
  - `admin` / `admin123` role `ADMIN`.
  - `pemustaka` / `admin123` role `USER`.
- Membuat migrasi `sql/2026-07-29b_public_root_demo_users_routes.sql`.
- Mengubah menu dashboard di database agar mengarah ke `/admin`.
- Mempercantik admin panel: sidebar kiri lebih tegas, topbar berisi label Admin Panel dan judul halaman.
- Menambahkan `.gitignore` untuk dump lokal, upload runtime, dan dependency/cache Tabler.

Validasi:

- `/` status 200, menampilkan landing page publik, dan tidak memuat sidebar admin.
- `/admin` tanpa session menampilkan login.
- Login `superadmin` masuk admin panel.
- Login `admin` masuk admin panel.
- Login `pemustaka` masuk dashboard pemustaka tanpa sidebar admin.
- Lint PHP bersih untuk controller dan view baru.

Catatan Git:

- Repository lokal sudah ada `.git` dan remote `origin` mengarah ke `https://github.com/Achilles29/pustaka.git`.
- Branch `main` lokal berada 1 commit di depan `origin/main`.
- `git push --dry-run --porcelain origin main` berhasil, artinya remote menerima rencana push dari commit lokal.
- Commit lokal saat ini membawa folder `tabler-dev` source sehingga pack Git sekitar 66,87 MiB. Tidak ada file tunggal di atas 100 MiB, tetapi push bisa lambat atau putus pada koneksi tertentu.
- Jika push masih gagal, kemungkinan terbesar adalah credential GitHub/token di terminal atau koneksi saat upload, bukan konflik branch.

## 2026-07-29 06:35 WIB

Status: perbaikan tampilan setelah folder `tabler-dev` dihapus dan pembersihan riwayat Git.

Masalah:

- Landing page dan panel admin menjadi acak karena view masih memuat CSS/JS dari `tabler-dev/core/dist`, sementara folder `tabler-dev` sudah dihapus.
- Push GitHub gagal karena commit lama `e8ff991` membawa secret dari file upstream Tabler: `tabler-dev/shared/data/site.json`.

Yang dilakukan:

- Mengganti seluruh referensi Tabler lokal ke CDN jsDelivr `@tabler/core@1.4.0`.
- Memastikan `landing`, `login`, `admin layout`, dan `dashboard user` tidak lagi memuat URL `tabler-dev`.
- Menambahkan `/tabler-dev/` ke `.gitignore`.
- Memperbarui `THEME_REFERENCES.md` agar kondisi theme terbaru sesuai: Tabler CDN, bukan source lokal.
- Menyiapkan ulang riwayat lokal agar commit yang akan dipush tidak lagi membawa commit lama berisi secret.

Catatan:

- Folder source Tabler tidak boleh ikut repository.
- Jika ingin aset offline, ambil hanya file dist CSS/JS yang dibutuhkan dan simpan di folder aset khusus tanpa file demo/config upstream.

## 2026-07-29 07:35 WIB

Status: sidebar admin dirapikan ulang dan modul RBAC/sidebar dibuat sebagai fondasi kanonis.

Yang dilakukan:

- Membuat migrasi `sql/2026-07-29c_rbac_sidebar_foundation_refine.sql`.
- Menambahkan registry halaman `system.pages.index`.
- Mengubah menu sistem menjadi `Pengaturan Akses` dengan submenu User, Role & Permission, Registry Halaman, dan Sidebar.
- Memindahkan modul pengaturan akses ke controller utama `Rbac`.
- Membuat route kanonis:
  - `/rbac/roles`
  - `/rbac/users`
  - `/rbac/pages`
  - `/rbac/sidebar`
- Menjaga route lama `/roles`, `/users`, dan `/sidebar/manage` sebagai kompatibilitas.
- Mengganti view lama dengan view baru di `application/views/rbac`.
- Memperbaiki layout sidebar admin agar rapi, rekursif, memakai ikon dari database, dan siap menerima modul baru.
- Menambahkan CSS dasar admin di `assets/css/pustaka.css`: sidebar, menu aktif, submenu, topbar, tab RBAC, form permission, dan preview tree.
- Menambahkan dokumen standar:
  - `docs/RBAC_AND_SIDEBAR_STANDARD.md`
  - `docs/CODING_STANDARDS.md`
  - `docs/HANDOVER.md`

Standar penting:

- Setiap modul baru harus didaftarkan dulu di `sys_page`.
- Permission role diatur di `auth_role_permission`.
- Menu/sidebar diatur di `sys_menu` dan sebaiknya mengarah ke `page_id`.
- Controller admin harus extend `MY_Controller` dan memanggil `$this->require_permission('kode.halaman')`.
- Sidebar tidak hardcode di view; semua item berasal dari database.

Validasi:

- Migrasi SQL berhasil dijalankan ke database `pustaka`.
- Lint PHP bersih untuk controller, model, layout, dan view RBAC baru.
- Smoke test login superadmin berhasil.
- Route `/admin`, `/rbac/roles`, `/rbac/users`, `/rbac/pages`, `/rbac/sidebar`, `/roles`, `/users`, dan `/sidebar/manage` status 200.
- Halaman RBAC memuat tab pengaturan dan ikon sidebar.

## 2026-07-29 08:05 WIB

Status: dokumentasi dirapikan agar tidak terlalu banyak file yang tumpang tindih.

Yang dilakukan:

- Mengecek seluruh file di folder `docs`.
- Menggabungkan isi `AUTH_RBAC_SIDEBAR.md` ke `RBAC_AND_SIDEBAR_STANDARD.md`.
- Menghapus `AUTH_RBAC_SIDEBAR.md` karena sudah tidak menjadi rujukan utama.
- Memperbarui `README.md` sebagai peta dokumentasi aktif.
- Menambahkan penjelasan struktur dokumentasi agar jelas fungsi tiap file.
- Menambahkan checklist roadmap di `ROADMAP.md`.

Catatan:

- `PRODUCT_VISION_PLUS.md` tetap dipisah sebagai bank ide.
- `ROADMAP.md` menjadi checklist eksekusi.
- `PROGRESS.md` tetap sebagai catatan kronologis.
- `HANDOVER.md` tetap untuk pindah device/onboarding.
- File scan dan CSV dipertahankan sebagai bukti inventaris INLISLite.
- Push Git tidak dilakukan otomatis lagi; push dilakukan manual oleh pemilik proyek kecuali ada instruksi eksplisit.

## 2026-07-29 09:10 WIB

Status: Fase 1 Admin dan Data Master dibuat clear.

Yang dilakukan:

- Membuat migrasi `sql/2026-07-29d_phase1_admin_gis_clean.sql`.
- Menambahkan master wilayah:
  - `ref_districts`
  - `ref_villages`
- Seed wilayah Rembang dari OpenData resmi: 14 kecamatan dan 294 Desa / Kelurahan.
- Menambahkan field wilayah relasional di `libraries`: `district_id` dan `village_id`.
- Menambahkan field verifikasi perpustakaan: `is_verified`, `verified_by`, `verified_at`.
- Menambahkan soft-delete foto perpustakaan: `deleted_at`, `deleted_by`.
- Menambahkan Audit Log UI di `/audit`.
- Menambahkan menu `Audit Log` di `Pengaturan Akses`.
- Menambahkan `Audit_model` dan helper `audit_event()` di `MY_Controller`.
- Menambahkan `Region_model` untuk master kecamatan dan Desa / Kelurahan.
- Memperbarui CRUD Perpustakaan GIS:
  - filter kecamatan,
  - dropdown kecamatan dan Desa / Kelurahan,
  - set foto utama,
  - hapus foto galeri secara soft-delete,
  - verifikasi data,
  - audit create/update/status/photo/verify.
- Memperbarui RBAC User agar user bisa diberi `library_id`.
- Menerapkan scope admin lokal: jika user memiliki `library_id`, data GIS dibatasi ke perpustakaan tersebut.
- Memperbarui dashboard admin agar menampilkan ringkasan Fase 1.

Validasi:

- Lint PHP bersih untuk controller, model, dan view yang berubah.
- Migrasi SQL berhasil dijalankan ke database `pustaka`.
- Master wilayah terisi 14 kecamatan dan 294 Desa / Kelurahan.
- Route `/admin`, `/libraries`, `/libraries/create`, `/rbac/users`, dan `/audit` status 200 tanpa PHP error.
- User `admin` tidak bisa mengakses `/audit` dan mendapat status 403.

## 2026-07-29 09:35 WIB

Status: kodifikasi master wilayah disesuaikan dengan ketentuan proyek.

Yang dilakukan:

- Mengubah kode kecamatan dari format lama `10`, `20`, dan seterusnya menjadi dua digit terakhir kode wilayah:
  - `01` Sumber
  - `02` Bulu
  - `03` Gunem
  - `04` Sale
  - `05` Sarang
  - `06` Sedan
  - `07` Pamotan
  - `08` Sulang
  - `09` Kaliori
  - `10` Rembang
  - `11` Pancur
  - `12` Kragan
  - `13` Sluke
  - `14` Lasem
- Menambahkan `province_code = 33`, `regency_code = 17`, dan `full_code = 33.17.xx` ke `ref_districts`.
- Menambahkan `province_code`, `regency_code`, `district_code`, dan `area_type` ke `ref_villages`.
- Mengubah label UI menjadi `Desa / Kelurahan`.
- Menyiapkan `area_type` agar 7 kelurahan bisa ditandai admin nanti tanpa ubah schema.

Validasi:

- Database lokal berhasil dimigrasikan ulang.
- `ref_districts` berisi 14 kecamatan dengan kode `01..14`.
- `ref_villages` tetap berisi 294 Desa / Kelurahan dengan kode wilayah lengkap.

## 2026-07-29 10:05 WIB

Status: UI CRUD Master Wilayah selesai dan Fase 2 dimulai dari schema katalog.

Yang dilakukan:

- Menambahkan controller `Regions`.
- Menambahkan view `application/views/regions/index.php`.
- Menambahkan CRUD kecamatan:
  - tambah,
  - edit,
  - aktif/nonaktif.
- Menambahkan CRUD Desa / Kelurahan:
  - tambah,
  - edit,
  - aktif/nonaktif,
  - filter kecamatan,
  - filter tipe `desa`/`kelurahan`.
- Menambahkan audit log untuk perubahan kecamatan dan Desa / Kelurahan.
- Menambahkan route `/regions`.
- Menambahkan menu `Data Master > Master Wilayah`.
- Membuat migrasi `sql/2026-07-29e_regions_crud_catalog_phase2.sql`.
- Memulai Fase 2 dengan schema:
  - `books`,
  - `book_authors`,
  - `book_subjects`,
  - `book_items`,
  - `digital_assets`,
  - `catalog_sync_runs`,
  - `catalog_sync_maps`.
- Mengubah `/catalog` dari placeholder menjadi dashboard katalog awal.
- Menambahkan halaman `/catalog/sync` untuk status sinkronisasi.

Catatan:

- Data operasional perpustakaan/katalog belum diimport.
- Fase 2 dimulai dari schema dan pemetaan ID agar import INLISLite bisa dry-run dulu.
- Push Git tidak dilakukan otomatis.

## 2026-07-29 10:45 WIB

Status: standar UI CRUD diperjelas, halaman awal ditata ulang, dan fondasi migrasi member dibuat.

Yang dilakukan:

- Menambahkan standar coding untuk pola UI CRUD:
  - halaman `index` hanya berisi data, ringkasan, filter, pagination, dan aksi,
  - form tambah/edit dipisah dari index,
  - form kecil memakai modal,
  - form besar/kompleks memakai halaman `create`/`edit`,
  - data besar wajib memakai `limit` dan `offset`.
- Menata ulang UI Master Wilayah:
  - card ringkasan,
  - filter pencarian,
  - filter baris per halaman,
  - pagination,
  - modal tambah/edit Kecamatan,
  - modal tambah/edit Desa / Kelurahan.
- Menata ulang UI RBAC User, Registry Halaman, dan Sidebar agar form tambah/edit tidak lagi menjadi panel permanen di halaman index.
- Menata ulang halaman Perpustakaan GIS dengan `per_page`, pagination server-side, dan total hasil filter.
- Menata ulang Audit Log dengan `per_page`, pagination server-side, dan total log.
- Scan ulang data penting INLISLite untuk memastikan cakupan migrasi:
  - `catalogs`: 12.749,
  - `collections`: 22.256,
  - `catalog_ruas`: 159.429,
  - `catalog_subruas`: 144.963,
  - `members`: 5.389,
  - `memberguesses`: 40.324,
  - `collectionloans`: 31.229,
  - `collectionloanitems`: 2.200,
  - `opaclogs`: 5.098,
  - `opaclogs_keyword`: 5.043,
  - `catalogfiles`: 0.
- Membuat migrasi `sql/2026-07-29f_members_migration_login_foundation.sql`.
- Menambahkan tabel `members` dan `member_sync_runs`.
- Menambahkan registry halaman `members.sync` dan permission view untuk `SUPERADMIN`.
- Mengubah `Members` dari placeholder menjadi dashboard membership.
- Menambahkan model `Member_model`.
- Menambahkan halaman `/members/sync`.

Keputusan:

- Sinkronisasi katalog berarti proses ETL read-only dari database sumber `inlislite_v3` ke schema aplikasi `pustaka`, bukan memakai tabel INLISLite langsung sebagai tabel operasional.
- Semua data INLISLite yang memang dibutuhkan harus bisa dimigrasikan ke `pustaka`, tetapi tabel sistem/cache/form internal INLISLite tidak perlu diwarisi sebagai schema operasional.
- Semua anggota INLISLite yang valid akan dibuatkan akun login role `USER`.
- Password awal standar member hasil migrasi: `PustakaRembang#2026`.
- Member wajib mengganti password pada login pertama melalui `auth_user.force_password_change = 1`.

Validasi:

- Migrasi SQL membership berhasil dijalankan ke database `pustaka`.
- Tabel `members` dan `member_sync_runs` tersedia.
- Halaman `members.sync` tersedia di `sys_page` dan hanya `SUPERADMIN` yang mendapat akses view awal.
- Lint PHP bersih untuk controller/model/view member, Master Wilayah, dan view RBAC yang diubah.
- HTTP smoke test login `superadmin` berhasil untuk `/admin`, `/libraries`, `/regions`, `/rbac/users`, `/rbac/pages`, `/rbac/sidebar`, `/audit`, `/catalog`, `/catalog/sync`, `/members`, dan `/members/sync`.

Catatan:

- Import data member belum dijalankan; langkah berikutnya adalah importer dry-run dari `inlislite_v3.members` ke `members` dan `auth_user`.
- Verifikasi visual dengan Browser plugin belum bisa dijalankan karena koneksi browser lokal gagal dengan error internal `sandboxCwd must use the file URI scheme`; fallback validasi dilakukan lewat HTTP smoke test.
- Push Git tidak dilakukan otomatis.

## 2026-07-29 11:25 WIB

Status: standar mobile, tab workspace, modal edit, drag-drop sidebar, dan adopsi logic INLISLite diperkuat.

Yang dilakukan:

- Menambahkan standar bahwa semua halaman wajib mobile friendly.
- Menetapkan pola: jika satu route memuat beberapa kelompok data, gunakan tab dalam satu workspace/card.
- Mengubah `/regions` menjadi tab:
  - `Kecamatan`,
  - `Desa / Kelurahan`.
- Mengubah tombol aksi menjadi tombol ikon ringkas untuk edit/toggle pada halaman yang dirapikan.
- Menambahkan helper global di layout admin untuk membuka modal edit lewat `data-pustaka-open-modal`.
- Menghapus pola script modal per-view yang rentan tidak konsisten.
- Menambahkan mode mobile table card otomatis melalui `data-label` dari header tabel.
- Menambahkan drag-and-drop di `/rbac/sidebar`:
  - tab `Struktur` untuk geser urutan/menu,
  - tab `Data Menu` untuk edit detail,
  - endpoint `rbac/sidebar/reorder`,
  - penyimpanan ke `sys_menu.parent_id` dan `sys_menu.sort_order`,
  - pengaman agar menu tidak menjadi parent untuk dirinya sendiri/turunannya.
- Mempercantik ulang komponen admin:
  - workspace tabs,
  - responsive footer pagination,
  - sortable shell,
  - drag handle,
  - action button ikon,
  - card table mode untuk mobile.
- Membaca logic INLISLite dari folder `C:\xampp\htdocs\inlislite3` untuk modul yang akan diadopsi:
  - pengkatalogan katalog/cover/konten digital,
  - digital collection/OPAC availability,
  - model katalog/koleksi/member/catalogfiles,
  - tampilan foto anggota.

Temuan INLISLite yang dicatat:

- Cover memakai `catalogs.CoverURL` dan folder worksheet `uploaded_files/sampul_koleksi/original/{WorksheetDir}`.
- Konten digital memakai `catalogfiles.FileURL`, `FileFlash`, `isCompress`, dan `IsPublish`.
- File digital lama berada di `uploaded_files/dokumen_isi/{WorksheetDir}`.
- Eksemplar penting dari `collections`: barcode, NoInduk, RFID, lokasi, kategori, media, sumber, status, rule akses, OPAC, booking.
- OPAC lama hanya menampilkan `catalogs.IsOPAC = 1`.
- Ketersediaan memakai `collections.Status_id = 1` dan booking yang sudah kedaluwarsa.
- Foto anggota memakai `members.PhotoUrl`, fallback ke `members.ID`, lalu fallback `nophoto.jpg`.

Validasi:

- Lint PHP bersih untuk layout, view `/regions`, modal region/RBAC, controller RBAC, model Menu, dan routes.
- HTTP smoke test berhasil untuk `/regions` tab kecamatan/desa, URL edit modal region, URL edit modal registry halaman, `/rbac/sidebar`, dan URL edit modal sidebar.
- Endpoint `POST /rbac/sidebar/reorder` berhasil menyimpan payload urutan saat ini dan kembali ke `/rbac/sidebar` dengan flash sukses.
- Marker modal edit `data-pustaka-open-modal` hanya muncul pada URL edit, bukan pada index normal.

Catatan:

- Verifikasi visual via Browser plugin masih gagal karena error internal `sandboxCwd must use the file URI scheme`; validasi dilakukan lewat lint dan HTTP smoke test.
- Push Git tidak dilakukan otomatis.

## 2026-07-29 20:45 WIB

Status: perapihan visual admin, tab, tombol aksi, dan RBAC lanjutan selesai.

Yang dilakukan:

- Mengubah tema admin ke biru Demokrat dan putih melalui `assets/css/pustaka.css`.
- Memperjelas tab halaman/tampilan dengan pola workspace segmented dan state aktif kontras.
- Mengubah tombol aksi tabel dari ikon-only menjadi ikon plus label singkat agar edit/toggle jelas di desktop dan mobile.
- Merapikan `/catalog`:
  - ringkasan dipadatkan menjadi metric ribbon,
  - tabel utama masuk tab `Data`,
  - statistik sumber dan mapping dipindah ke tab terpisah agar tabel tidak terlalu turun.
- Merapikan `/members` dengan pola yang sama seperti katalog.
- Merapikan `/rbac/users`:
  - tabel utama hanya menampilkan ringkasan user, status, role, scope, dan login terakhir,
  - pengaturan role dan cakupan perpustakaan dipindah ke modal edit per user.
- Mengubah `/rbac/roles` menjadi halaman `Tipe User`:
  - daftar tipe user tampil terlebih dahulu,
  - tambah/edit tipe user tersedia lewat modal,
  - matrix permission dibuka dari aksi `Hak Akses` per tipe user.
- Mempertahankan `/rbac/sidebar` sebagai pengaturan drag-and-drop dan data menu dalam tab yang lebih jelas.

Validasi:

- Lint PHP bersih untuk controller/model/view yang diubah: `Rbac`, `Role_model`, `User_model`, layout admin, view RBAC, view region, view library, view catalog, view members, dan routes.
- HTTP smoke test login `superadmin` berhasil untuk `/catalog`, `/members`, `/rbac/roles`, `/rbac/users`, `/rbac/pages`, `/rbac/sidebar`, `/regions`, dan `/libraries`.
- URL edit yang ditemukan otomatis pada `/regions`, `/rbac/roles`, `/rbac/users`, `/rbac/pages`, dan `/rbac/sidebar` berhasil diakses dengan status 200.
- Marker modal edit `data-pustaka-open-modal` muncul pada halaman edit yang memang memakai modal.

Catatan:

- Browser plugin untuk inspeksi visual masih gagal dari lingkungan Codex dengan error internal `sandboxCwd must use the file URI scheme`; fallback validasi memakai lint dan HTTP smoke test.
- Push Git tidak dilakukan otomatis.

## 2026-07-29 21:05 WIB

Status: branding resmi lokal dipasang.

Yang dilakukan:

- Memakai favicon dari `img/favicon.ico`.
- Mengganti brand mark teks `PR` dengan logo Kabupaten Rembang dari `img/logo-small.jpeg` pada:
  - landing page,
  - login,
  - admin sidebar,
  - dashboard pemustaka.
- Menambahkan logo Perpusnas dari `img/perpusnas.png` pada navbar landing dan hero landing.
- Menambahkan CSS `brand-logo-shell`, `brand-logo`, `hero-logo-row`, dan `public-agency-strip` agar logo stabil, rapi, dan mobile friendly.
- Menyesuaikan background login agar tetap konsisten dengan tema biru Demokrat dan putih.

Validasi:

- Lint PHP bersih untuk view `layouts/tabler.php`, `auth/login.php`, `home/landing.php`, dan `user/dashboard.php`.
- HTTP asset check berhasil:
  - `/img/favicon.ico` status 200,
  - `/img/logo-small.jpeg` status 200,
  - `/img/perpusnas.png` status 200,
  - `/` status 200,
  - `/login` status 200.

Catatan:

- Push Git tidak dilakukan otomatis.

## 2026-07-29 21:35 WIB

Status: modal edit diperkuat, sidebar dipoles, dan tombol sinkronisasi mulai menarik data.

Yang dilakukan:

- Menambahkan fallback JavaScript modal di layout admin:
  - URL seperti `/regions?tab=districts&edit_district_id=2` tetap membuka modal edit walaupun Bootstrap/Tabler JS dari CDN gagal dimuat.
  - Tombol tambah/edit berbasis `data-bs-toggle="modal"` tetap bisa membuka modal tanpa Bootstrap JS.
  - Tombol close, klik backdrop, dan tombol `Esc` ditangani oleh fallback.
- Menambahkan fallback tab agar tab workspace tetap bisa dipakai bila Bootstrap JS tidak tersedia.
- Mempercantik ulang sidebar:
  - warna biru dibuat lebih dalam,
  - teks menu dan submenu dibuat lebih kontras,
  - icon menu diberi bidang visual,
  - active/hover state dibuat lebih tegas.
- Menambahkan endpoint sinkronisasi katalog:
  - `POST /catalog/sync/run`
  - menarik batch dari `inlislite_v3.catalogs` ke `books`,
  - menarik eksemplar dari `inlislite_v3.collections` ke `book_items`,
  - mencatat run ke `catalog_sync_runs` dan mapping ke `catalog_sync_maps`.
- Menambahkan endpoint sinkronisasi member:
  - `POST /members/sync/run`
  - menarik batch dari `inlislite_v3.members` ke `members`,
  - membuat akun login di `auth_user`,
  - memberi role `USER`,
  - password awal `PustakaRembang#2026`,
  - akun login member dibuat aktif agar semua hasil migrasi bisa masuk aplikasi; status membership historis tetap disimpan di tabel `members`.
- Menambahkan panel aksi sinkronisasi dengan pilihan batch 500, 1.000, dan 2.000 data pada `/catalog/sync` dan `/members/sync`.
- Memperbaiki query join lintas database dengan collation eksplisit karena `pustaka` dan `inlislite_v3` berbeda collation.

Validasi:

- Lint PHP bersih untuk layout, controller/model Catalog, controller/model Members, view sync, dan routes.
- HTTP smoke test berhasil untuk `/catalog`, `/catalog/sync`, `/members`, `/members/sync`, dan `/regions?tab=districts&edit_district_id=2`.
- Batch test katalog berhasil:
  - `books`: 3 row,
  - `book_items`: 5 row,
  - run terakhir status `success`.
- Batch test member berhasil:
  - `members`: 3 row,
  - akun member di `auth_user`: 3 row,
  - run terakhir status `success`.
- Login member hasil migrasi berhasil memakai `M.63` / `PustakaRembang#2026` dan redirect ke `/user/dashboard`.

Catatan:

- Browser plugin untuk screenshot visual masih gagal dari lingkungan Codex dengan error internal `sandboxCwd must use the file URI scheme`; fallback validasi memakai lint dan HTTP smoke test.
- Push Git tidak dilakukan otomatis.

## 2026-07-29 21:50 WIB

Status: label tombol aksi toggle diperjelas.

Yang dilakukan:

- Mengubah semua tombol toggle status dari label ambigu `Aktif`/`Nonaktif` menjadi perintah:
  - `Aktifkan`
  - `Nonaktifkan`
- Halaman yang dicek dan dirapikan:
  - `/libraries`
  - `/regions`
  - `/rbac/users`
  - `/rbac/roles`
  - `/rbac/pages`
  - `/rbac/sidebar`
- Badge status tetap memakai label kondisi sekarang:
  - `Aktif`
  - `Nonaktif`
  - `Pending`
  - `Tayang`
  - `Kedaluwarsa`
- Status katalog, member, dan riwayat sinkronisasi ikut diterjemahkan agar tidak menampilkan value database mentah seperti `active`, `published`, atau `success`.
- Standar coding UI diperbarui: tombol aksi harus berupa kata kerja/perintah, sedangkan status berada di badge.

Validasi:

- Lint PHP bersih untuk view yang diubah.
- HTTP smoke test berhasil untuk `/regions`, `/rbac/users`, `/rbac/roles`, `/rbac/pages`, `/rbac/sidebar`, `/libraries`, `/catalog`, dan `/members`.
- Pemeriksaan markup memastikan tidak ada tombol toggle dengan label ambigu `Aktif` atau `Nonaktif`.

Catatan:

- Push Git tidak dilakukan otomatis.

## 2026-07-29 22:20 WIB

Status: `/catalog`, `/members`, dan mode sinkronisasi diperkuat.

Yang dilakukan:

- Mengubah `/catalog` menjadi data table operasional:
  - filter pencarian,
  - filter status,
  - filter tahun,
  - filter baris,
  - pagination,
  - thumbnail cover dari folder INLISLite,
  - jumlah eksemplar,
  - tombol `Detail`.
- Menambahkan `/catalog/detail/{id}`:
  - cover,
  - bibliografi,
  - penulis,
  - subjek,
  - daftar eksemplar.
- Mengubah `/members` menjadi data table operasional:
  - filter pencarian,
  - filter status membership,
  - filter status akun,
  - filter baris,
  - pagination,
  - thumbnail foto anggota dari folder INLISLite,
  - status akun login,
  - tombol `Detail`.
- Menambahkan `/members/detail/{id}`:
  - foto,
  - profil lengkap,
  - alamat,
  - akun login,
  - password awal migrasi.
- Menambahkan mode sinkronisasi:
  - `Import data baru`,
  - `Update data lama`,
  - `Dry run / simulasi`.
- Mode `Import data baru` katalog hanya mengambil katalog yang belum masuk.
- Mode `Update data lama` katalog menyegarkan buku dan eksemplar yang sudah ada tanpa membuat duplikat.
- Mode `Dry run / simulasi` katalog/member mencatat run tanpa menulis data target.
- Mode `Import data baru` member sekarang juga memperbaiki member yang profilnya sudah masuk tetapi akun loginnya belum terhubung.
- Import member dibuat lebih aman dengan transaksi per row.

Validasi:

- Lint PHP bersih untuk controller, model, routes, dan view katalog/member baru.
- HTTP smoke test berhasil untuk:
  - `/catalog`,
  - `/catalog?q=Dasar&status=published&per_page=10`,
  - `/catalog/detail/1`,
  - `/catalog/sync`,
  - `/members`,
  - `/members?q=BUDI&status=expired&per_page=10`,
  - `/members/detail/1`,
  - `/members/sync`.
- Mode `Dry run` katalog dan member tidak mengubah jumlah data.
- Mode `Update data lama` katalog berhasil update 3 buku dan 5 eksemplar tanpa duplikasi.
- Mode `Update data lama` member berhasil update 3 member tanpa duplikasi.
- Batch kecil `Import data baru` member berhasil:
  - memperbaiki 2 member tanpa akun,
  - menambah 3 member baru,
  - membuat 5 akun login,
  - `members_without_user` menjadi 0.

Status data lokal saat validasi:

- `books`: 14.097.
- `book_items`: 22.927.
- `members`: 1.593.
- akun login member: 1.593.
- member tanpa akun: 0.

Catatan:

- Katalog lokal sudah penuh terhadap sumber saat validasi (`sisa belum masuk: 0`).
- Member masih bertahap (`sisa belum masuk: 3.796`), lanjutkan dengan batch berikutnya dari UI.
- Push Git tidak dilakukan otomatis.
