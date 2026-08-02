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
- Password awal standar member hasil migrasi saat ini: `perpus2026`.
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
  - password awal `perpus2026`,
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
- Login member hasil migrasi berhasil memakai `M.63` / `perpus2026` dan redirect ke `/user/dashboard`.

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

## 2026-07-30 06:45 WIB

Status: migrasi aset INLISLite dibuat dan dijalankan sampai clear untuk aset referensi.

Yang dilakukan:

- Membuat migration SQL `sql/2026-07-30a_inlislite_asset_migration.sql`.
- Menambahkan kolom migrasi aset:
  - `books.cover_source_path`,
  - `books.cover_local_path`,
  - `books.cover_migration_status`,
  - `books.cover_migrated_at`,
  - `members.photo_source_path`,
  - `members.photo_local_path`,
  - `members.photo_migration_status`,
  - `members.photo_migrated_at`,
  - kolom sumber/status migrasi awal pada `digital_assets`.
- Menambahkan tabel:
  - `asset_migration_runs`,
  - `asset_migration_items`.
- Membuat modul admin `/assets-migration` untuk migrasi batch:
  - cover buku,
  - foto member,
  - file digital,
  - semua aset.
- Mendaftarkan page RBAC `assets.migration` dan menu sidebar `Migrasi Aset`.
- Membuat folder storage lokal:
  - `assets/uploads/inlislite/covers`,
  - `assets/uploads/inlislite/member_photos`,
  - `assets/uploads/inlislite/digital_files`,
  - `assets/uploads/inlislite/system`,
  - `assets/uploads/inlislite/source_mirror/uploaded_files`.
- Menjalankan migrasi aset referensi sampai tidak ada status `pending`.
- Menyalin mirror penuh folder `C:\xampp\htdocs\inlislite3\uploaded_files` ke `assets/uploads/inlislite/source_mirror/uploaded_files`.
- Mengubah view katalog/member agar membaca file lokal terlebih dahulu dan fallback ke mirror lokal, bukan ke `/inlislite3`.
- Mengubah importer katalog/member agar data baru berikutnya otomatis masuk antrean migrasi aset.

Hasil migrasi aset:

- Mirror penuh `uploaded_files`: 13.631 file, 1.486.730.062 byte.
- Cover referensi katalog:
  - total referensi: 7.795,
  - copied: 7.358,
  - missing: 437,
  - pending: 0.
- Foto referensi member lokal:
  - total referensi: 3.093,
  - copied: 767,
  - missing: 2.326,
  - pending: 0.
- File digital folder `dokumen_isi`:
  - copied: 4 file.

Validasi:

- Lint PHP bersih untuk:
  - `application/models/Catalog_model.php`,
  - `application/models/Member_model.php`,
  - `application/models/Asset_migration_model.php`,
  - `application/controllers/Asset_migration.php`,
  - view katalog, member, dan asset migration.
- HTTP smoke test login `superadmin` berhasil membuka `/assets-migration`.
- Tidak ada lagi view katalog/member yang membangun URL langsung ke `/inlislite3`.

Catatan:

- Banyak foto member lama berstatus `missing` karena referensi database seperti `8.jpg`, `9.jpg`, dan seterusnya tidak punya file fisik yang cocok di folder INLISLite.
- Folder `assets/uploads/*` tetap di-ignore Git. Saat pindah server, upload/copy folder `assets/uploads/inlislite` secara terpisah dari Git.
- Push Git tidak dilakukan otomatis.

## 2026-07-30 20:40 WIB

Status: CRUD katalog/member dan sinkronisasi transaksi harian INLISLite dibuat dan divalidasi.

Yang dilakukan:

- Menambahkan migration SQL `sql/2026-07-30b_catalog_member_crud_transaction_sync.sql`.
- Menambahkan soft-delete untuk:
  - `books.deleted_at`,
  - `members.deleted_at`.
- Membuat CRUD manual katalog:
  - `/catalog/create`,
  - `POST /catalog/store`,
  - `/catalog/edit/{id}`,
  - `POST /catalog/update/{id}`,
  - `/catalog/delete/{id}`.
- Membuat form katalog terpisah di `application/views/catalog/form.php`.
- Menambahkan tombol `Tambah`, `Edit`, dan `Hapus` pada list/detail katalog.
- Membuat CRUD manual member:
  - `/members/create`,
  - `POST /members/store`,
  - `/members/edit/{id}`,
  - `POST /members/update/{id}`,
  - `/members/delete/{id}`.
- Membuat form member terpisah di `application/views/members/form.php`.
- Form member mendukung pembuatan/update akun login pemustaka role `USER`.
- Membuat schema transaksi harian:
  - `member_visits`,
  - `member_access_rules`,
  - `loan_transactions`,
  - `loan_transaction_items`,
  - `transaction_sync_runs`.
- Membuat model sinkronisasi batch `Transaction_sync_model`.
- Membuat controller dan halaman `/transactions/sync`.
- Mendaftarkan page RBAC `transactions.sync`; nama tampilan terbaru modul ini adalah `Layanan Harian`.
- Detail member sekarang menampilkan tab aktivitas:
  - kunjungan,
  - histori pinjam,
  - hak akses/hak pinjam.

Hasil sinkronisasi transaksi harian:

- `memberguesses`: 41.136 sumber, 41.136 target.
- `memberloanauthorizecategory` + `memberloanauthorizelocation`: 26.864 sumber, 26.864 target.
- `collectionloans`: 31.561 sumber, 31.561 target.
- `collectionloanitems`: 2.200 sumber, 2.200 target.
- Dry-run ulang transaksi menghasilkan kandidat data baru: 0.
- Mode `Update data lama` transaksi berhasil diuji batch 1.000 data.

Status data setelah member lengkap:

- `books`: 14.097 aktif.
- `members`: 5.389 aktif.
- akun login member: 5.389.
- Foto member:
  - copied: 3.008,
  - missing: 2.355,
  - pending: 0.
- Cover katalog:
  - copied: 7.358,
  - missing: 437,
  - pending: 0.

Validasi:

- Lint PHP bersih untuk controller/model/view yang ditambahkan/diubah.
- HTTP smoke test berhasil untuk:
  - `/catalog/create`,
  - `/catalog/edit/1`,
  - `/members/create`,
  - `/members/edit/1`,
  - `/members/detail/1`,
  - `/transactions/sync`.
- Tes create katalog manual berhasil lalu data uji dibersihkan.
- Tes create member manual berhasil lalu data uji dibersihkan.
- Push Git tidak dilakukan otomatis.

## 2026-07-30 21:30 WIB

Status: langkah 1 dan 2 selesai: kurasi mapping master INLISLite dan CRUD eksemplar buku.

Yang dilakukan:

- Menambahkan migration SQL `sql/2026-07-30c_master_mapping_book_items_crud.sql`.
- Menambahkan tabel `inlislite_master_references` untuk master referensi dari INLISLite:
  - jenis anggota,
  - status anggota,
  - jenis identitas,
  - jenis kelamin,
  - pendidikan/jenjang pendidikan,
  - pekerjaan,
  - kategori koleksi,
  - aturan pinjam,
  - status koleksi,
  - lokasi koleksi,
  - lokasi perpustakaan,
  - media koleksi,
  - sumber pengadaan,
  - tujuan kunjungan.
- Menambahkan kolom label master pada `members` agar UI tidak hanya menampilkan ID mentah INLISLite.
- Menambahkan kolom label master dan raw source ID pada `book_items`.
- Menambahkan migration SQL `sql/2026-07-30d_transaction_master_labels.sql` untuk label dasar transaksi harian:
  - `member_visits.*_label`,
  - `member_access_rules.rule_label`.
- Membuat CRUD eksemplar dari detail katalog:
  - `POST /catalog/items/store/{book_id}`,
  - `POST /catalog/items/update/{book_id}/{item_id}`,
  - `/catalog/items/delete/{book_id}/{item_id}`.
- Membuat partial modal `application/views/catalog/_item_modal.php`.
- Tabel eksemplar di detail katalog sekarang menampilkan barcode, no induk, lokasi, kategori, aturan pinjam, media, status aplikasi, status INLISLite, OPAC/internal, dan aksi edit/nonaktifkan.
- `Catalog_model::sync_items_for_catalog()` diperbarui agar sync berikutnya membawa raw ID dan label master.
- Mapping status eksemplar INLISLite dikoreksi:
  - `1` Tersedia -> `available`,
  - `3` Rusak dan `4` Dalam Perbaikan -> `damaged`,
  - `5` Dipinjam -> `loaned`,
  - `8` Hilang -> `missing`,
  - selain itu -> `unknown`.
- `Member_model` dan `Transaction_sync_model` diberi refresh label otomatis setelah batch sinkronisasi.
- Detail member sekarang menampilkan label jenis identitas, gender, jenis member, pendidikan, pekerjaan, status INLISLite, label lokasi kunjungan, dan label hak pinjam jika master tersedia.

Validasi database:

- `inlislite_master_references`: 104 referensi.
- `book_items` aktif: 22.927.
- Label kategori eksemplar: 22.927 / 22.927.
- Label ruang/lokasi eksemplar: 22.917 / 22.927.
- Label status eksemplar: 22.927 / 22.927.
- `members` aktif: 5.389.
- Label jenis member: 5.389 / 5.389.
- Label jenis identitas: 427 / 5.389, karena banyak sumber INLISLite kosong.
- `member_visits`: 41.136.
- Label lokasi kunjungan: 21.270 / 41.136, sisanya kosong di sumber.
- Label tujuan kunjungan: 0 / 41.136, karena data `purpose_id` batch lokal kosong.
- `member_access_rules`: 26.864.
- Label hak pinjam: 16.117 / 26.864, sisanya rule lokasi tidak punya master label cocok di source lokal.

Validasi teknis:

- Lint PHP bersih untuk model/controller/view yang diubah.
- HTTP smoke test login `superadmin` berhasil untuk:
  - `/catalog/detail/1`,
  - `/members/detail/1`,
  - `/catalog`.
- CRUD eksemplar diuji dengan data test:
  - create berhasil,
  - update berhasil,
  - soft-delete berhasil,
  - data dan audit test dibersihkan.
- Push Git tidak dilakukan otomatis.

## 2026-07-30 22:05 WIB

Status: UI data transaksi harian dibuat, menu migrasi aset dipindah ke Pengaturan Akses, dan nomor 3-5 dilanjutkan.

Yang dilakukan:

- Menambahkan halaman `/transactions` untuk melihat data transaksi yang sudah tersinkron.
- Halaman `/transactions` memakai tab workspace:
  - Buku Tamu dari `member_visits`,
  - Hak Layanan dari `member_access_rules`,
  - Peminjaman dari `loan_transactions`,
  - Item Koleksi dari `loan_transaction_items`.
- Setiap tab memakai filter pencarian, tanggal, baris, pagination, dan filter khusus tab bila diperlukan.
- `/transactions/sync` tetap menjadi halaman sinkronisasi dan diberi tombol balik ke Aktivitas Layanan.
- Menambahkan migration SQL `sql/2026-07-30e_transaction_data_ui_sidebar.sql`.
- Menambahkan registry halaman RBAC `transactions.index`.
- Menambahkan menu parent yang sekarang bernama `Layanan Harian` dengan anak:
  - `Aktivitas Layanan` -> `/transactions`,
  - `Sinkronisasi Layanan` -> `/transactions/sync`.
- Memindahkan menu `Migrasi Aset` ke bawah parent sidebar `Pengaturan Akses`.
- Melanjutkan nomor 3 audit aset:
  - `Asset_migration_model` sekarang menyediakan `recent_issues`,
  - `/assets-migration` menampilkan tabel `Audit Item Perlu Dicek` untuk file `missing`/`failed`.
- Detail katalog dan detail member tetap menjadi output nomor 4-5; keduanya sudah memakai label mapping master dan data migrasi aktual.

Validasi:

- Lint PHP bersih untuk:
  - `application/controllers/Transactions.php`,
  - `application/models/Transaction_sync_model.php`,
  - `application/views/transactions/index.php`,
  - `application/views/transactions/sync.php`,
  - `application/models/Asset_migration_model.php`,
  - `application/views/asset_migration/index.php`.
- HTTP smoke test login `superadmin` berhasil untuk:
  - `/transactions?tab=visits`,
  - `/transactions?tab=access`,
  - `/transactions?tab=loans`,
  - `/transactions?tab=items`,
  - `/transactions/sync`,
  - `/assets-migration`.
- Data transaksi lokal yang tampil:
  - `member_visits`: 41.136,
  - `member_access_rules`: 26.864,
  - `loan_transactions`: 31.561,
  - `loan_transaction_items`: 2.200.
- Sidebar database tervalidasi:
  - `assets.migration` parent = `Pengaturan Akses`, sort `60`,
  - `transactions.index` parent = `Layanan Harian`, sort `10`,
  - `transactions.sync` parent = `Layanan Harian`, sort `20`.
- Push Git tidak dilakukan otomatis.

## 2026-08-01 00:00 WIB

Status: nomor 2 dan 3 selesai untuk MVP: kartu anggota digital dan portal katalog publik.

Yang dilakukan:

- Menambahkan route publik:
  - `/katalog`,
  - `/katalog/detail/{id}`,
  - `/membership/verify/{member_id}/{token}`.
- Membuat controller `Public_catalog` untuk portal katalog publik.
- Membuat controller `Membership` untuk verifikasi kartu anggota digital.
- Menambahkan method katalog publik di `Catalog_model`:
  - `count_public_books`,
  - `get_public_books`,
  - `get_public_book`,
  - `get_public_book_items`,
  - `public_filter_options`.
- Portal katalog publik memakai database baru `pustaka`, bukan langsung membaca `inlislite_v3`.
- Katalog publik hanya menampilkan buku `published` dan eksemplar `is_public = 1`.
- Membuat view:
  - `application/views/public_catalog/index.php`,
  - `application/views/public_catalog/detail.php`,
  - `application/views/membership/verify.php`.
- Landing `/` sekarang mengarah ke `/katalog` dan menampilkan preview koleksi dari database lokal.
- Dashboard pemustaka `/user/dashboard` dirombak menjadi dashboard membership:
  - kartu anggota digital,
  - foto member lokal jika ada,
  - status membership,
  - jenis anggota,
  - masa berlaku,
  - QR verifikasi,
  - token verifikasi,
  - riwayat pinjam terakhir,
  - kunjungan terakhir,
  - shortcut katalog publik.
- Token kartu digital memakai HMAC dari `member.id`, `member_no`, dan `source_id`, sehingga QR tidak berisi data identitas sensitif.
- QR saat ini dibuat di frontend dengan CDN `qrcodejs@1.0.0`; fallback tetap ada melalui URL dan token verifikasi.
- Tampilan baru dibuat mobile friendly, termasuk filter katalog, grid buku, detail eksemplar, kartu digital, dan tabel riwayat.

Validasi:

- Lint PHP bersih untuk:
  - `Public_catalog.php`,
  - `Membership.php`,
  - `User_dashboard.php`,
  - `Home.php`,
  - `Catalog_model.php`,
  - `Member_model.php`,
  - view publik/member baru.
- Data publik lokal:
  - 12.703 judul publik dengan eksemplar OPAC.
- HTTP smoke test berhasil untuk:
  - `/`,
  - `/katalog`,
  - `/katalog?q=rembang&availability=with_items`,
  - `/katalog/detail/1`,
  - login `M.63` ke `/user/dashboard`,
  - URL `/membership/verify/{member_id}/{token}` dari kartu digital.
- Push Git tidak dilakukan otomatis.

## 2026-08-01 00:25 WIB

Status: aturan login member diganti menjadi NIK + password awal baru.

Yang dilakukan:

- Mengubah password default member di `Members::DEFAULT_IMPORTED_PASSWORD` menjadi `perpus2026`.
- Mengubah proses create/update/sync member agar username akun login memakai NIK/nomor identitas (`members.identity_number`) terlebih dulu.
- Menambahkan fallback username ke nomor anggota atau `member-{source_id}` untuk data lama yang NIK-nya masih kosong.
- Menambahkan dan menjalankan migration `sql/2026-08-01a_member_login_nik_password.sql`.
- Migration mengubah username akun member yang punya NIK menjadi NIK dan mereset seluruh password akun member menjadi `perpus2026`.
- UI form/detail/sync member diperbarui agar menjelaskan aturan username baru.

Validasi database lokal:

- Total member aktif: 5.389.
- Member dengan NIK/nomor identitas terisi: 427.
- Member tanpa NIK/nomor identitas: 4.962.
- Username yang sudah persis memakai NIK: 427.
- Akun login member terhubung: 5.389.
- Seluruh akun login member aktif tersetel `force_password_change = 1`: 5.389.
- Smoke test login NIK `3317101401620001` / `perpus2026` berhasil masuk ke `/user/dashboard`.
- Smoke test login fallback nomor anggota `M.63` / `perpus2026` berhasil masuk ke `/user/dashboard`.

Catatan:

- Karena mayoritas data INLISLite lokal belum punya NIK, akun tersebut sementara tetap login memakai nomor anggota sampai NIK dilengkapi.
- Push Git tidak dilakukan otomatis.

## 2026-08-01 01:05 WIB

Status: nomenklatur member baru, landing, kartu digital, dan wajah layanan harian dirapikan.

Yang dilakukan:

- Menambahkan generator nomor anggota manual baru di `Member_model::next_manual_member_no()`.
- Format nomor anggota manual baru: `PDR-3317-YYYY-000001`.
- Form tambah/edit member tidak lagi meminta input nomor manual; nomor tampil sebagai hasil sistem.
- Saat tambah member baru, `registered_at` otomatis diisi waktu saat ini jika admin mengosongkan field.
- Landing page publik dibuat lebih profesional:
  - hero GIS full-bleed,
  - statistik layanan di hero,
  - preview katalog,
  - section jejaring dengan GIS, membership, pojok baca, dan agenda literasi.
- Dashboard pemustaka dibuat lebih mobile friendly dan kartu anggota digital dibuat menyerupai kartu resmi.
- Verifikasi kartu `/membership/verify/{member_id}/{token}` dibuat seperti tampilan kartu digital terverifikasi.
- Nama tampilan modul transaksi diubah menjadi `Layanan Harian` agar lebih sesuai konteks perpustakaan.
- Menu database ikut diperbarui lewat `sql/2026-08-01b_member_number_service_labels.sql`:
  - parent `Layanan Harian`,
  - child `Aktivitas Layanan`,
  - child `Sinkronisasi Layanan`.
- Tabel layanan harian dan riwayat sinkronisasi diberi `data-label` agar mobile table lebih terbaca.

Catatan fitur yang belum masuk MVP katalog/membership:

- Katalog publik belum punya reservasi/request buku.
- Membership belum punya perpanjangan masa berlaku mandiri.
- Kartu digital belum punya fitur blokir kartu dengan alasan operasional.
- Reader PDF aman, pojok baca GPS, token/kuota, dan event masih masuk fase berikutnya.

Validasi:

- Migration label menu layanan sudah dijalankan ke database `pustaka`.
- Lint PHP bersih untuk controller/model/view yang diubah.
- HTTP smoke test berhasil untuk:
  - `/`,
  - `/user/dashboard`,
  - `/membership/verify/{member_id}/{token}`,
  - `/members/create`,
  - `/transactions`,
  - `/transactions/sync`.
- Uji tambah member manual tanpa input nomor menghasilkan `PDR-3317-2026-000001`; data uji sudah dibersihkan.
- Browser internal Codex masih gagal dibuka karena error lingkungan `sandboxCwd must use the file URI scheme`; validasi visual dilakukan dengan Chrome headless/CDP untuk landing, dashboard member, dan verifikasi kartu pada viewport mobile.
- Push Git tidak dilakukan otomatis.

## 2026-08-01 01:55 WIB

Status: UI layanan harian dan dashboard pemustaka dipoles ulang; fondasi layanan digital lanjutan mulai aktif.

Yang dilakukan:

- Dashboard pemustaka `/user/dashboard` dibangun ulang sebagai app shell anggota:
  - kartu digital besar dengan logo, foto, status, token, QR,
  - panel layanan cepat untuk katalog, verifikasi, perpanjangan, dan request buku,
  - daftar ringkas pengajuan, histori pinjam, dan kunjungan.
- UI `Layanan Harian` `/transactions` dibuat lebih ramah operasional:
  - command center biru-putih,
  - metric ribbon,
  - kartu navigasi tab Buku Tamu, Hak Layanan, Peminjaman, dan Item Koleksi,
  - filter dan table tetap mobile friendly.
- Menambahkan migration `sql/2026-08-01c_public_requests_membership_reader_events.sql` dan menjalankannya ke database `pustaka`.
- Menambahkan tabel/fondasi:
  - `book_requests`,
  - `membership_renewal_requests`,
  - kolom status kartu digital pada `members`,
  - `reading_points`,
  - `reading_tokens`,
  - `reading_sessions`,
  - `literacy_events`,
  - `event_registrations`.
- Reservasi/request buku:
  - form publik di `/katalog/detail/{id}#request-buku`,
  - route POST `/katalog/request/{book_id}`,
  - antrean admin `/catalog/requests`,
  - aksi admin untuk setujui, selesai, tolak, atau batalkan.
- Perpanjangan membership:
  - form pemustaka di `/user/dashboard#membership-renewal`,
  - route POST `/membership/renewal/request`,
  - antrean admin `/members/renewals`,
  - approval otomatis memperpanjang `members.expired_at` dari tanggal expiry aktif jika masih berlaku.
- Blokir/aktifkan kartu digital:
  - panel operasional di `/members/detail/{id}`,
  - alasan blokir wajib saat memblokir,
  - kartu terblokir tidak lolos verifikasi publik.
- Menambahkan control room:
  - `/reader/assets` untuk kebijakan aset PDF dan route awal `/reader/read/{asset_id}`,
  - `/reading-points` untuk titik GPS, radius, token, dan kuota,
  - `/events` untuk agenda literasi dan pendaftaran peserta.
- Parent sidebar `Layanan Digital` sekarang berisi Request Buku, Perpanjangan, Reader PDF Aman, Pojok Baca, dan Event Literasi.
- Standar coding ditambah: label aksi harus eksplisit dan tab/workspace harus jelas di mobile.

Validasi:

- Lint PHP bersih untuk model/controller/view baru dan view yang diubah.
- HTTP smoke test login `superadmin` berhasil membuka:
  - `/transactions`,
  - `/catalog/requests`,
  - `/members/renewals`,
  - `/reader/assets`,
  - `/reading-points`,
  - `/events`,
  - `/members/detail/1`.
- Smoke test POST request buku publik berhasil membuat 1 baris `book_requests`; data uji sudah dibersihkan.
- Smoke test POST perpanjangan membership dari akun NIK `3317101401620001` berhasil membuat 1 baris `membership_renewal_requests`; data uji sudah dibersihkan.
- Validasi visual mobile dilakukan dengan Chrome headless/CDP untuk `/user/dashboard`, `/transactions`, dan `/reader/assets`.
- Browser internal Codex tetap gagal karena error lingkungan `sandboxCwd must use the file URI scheme`; fallback headless dipakai.
- Push Git tidak dilakukan otomatis.

Catatan berikutnya:

- Reader aman belum final sampai file PDF dipindah ke storage non-public, endpoint render per halaman/token dibuat, watermark dinamis dipasang, serta rate limit dan audit akses penuh aktif.
- Pojok Baca berikutnya perlu penerbitan token/check-in dan galeri/foto titik.
- Event Literasi berikutnya perlu CRUD event + pendaftaran publik/member + QR attendance.

## 2026-08-01 02:35 WIB

Status: UI layanan harian dipadatkan, admin inbox ditambahkan, filter katalog diperkaya, Pojok Baca bisa diatur, dan pendaftaran member online tersedia.

Yang dilakukan:

- `/transactions` disederhanakan:
  - menghapus card navigasi dobel yang terasa kuno,
  - menghapus tombol sinkronisasi dobel di header,
  - mengganti hero besar dengan strip ringkas,
  - menambahkan pesan antrean layanan jika ada item pending.
- Menambahkan `Kotak Masuk` global di admin topbar.
- Kotak masuk menghitung antrean pending dari:
  - `member_registration_requests`,
  - `book_requests`,
  - `membership_renewal_requests`.
- `/katalog` publik diperbarui:
  - tombol reset filter,
  - filter kategori koleksi lebih banyak,
  - filter media,
  - filter aturan pinjam,
  - filter lokasi perpustakaan,
  - filter tahun dan ketersediaan tetap tersedia.
- `/reading-points` menjadi pengaturan Pojok Baca:
  - tambah titik `/reading-points/create`,
  - edit titik `/reading-points/edit/{id}`,
  - field perpustakaan pengampu, mitra, alamat, latitude, longitude, radius, kuota, satuan kuota, jam aktif, status.
- Menambahkan migration `sql/2026-08-01d_member_registration_reading_point_crud.sql` dan menjalankannya.
- Menambahkan pendaftaran member online:
  - form publik `/membership/register`,
  - route POST `/membership/register/submit`,
  - antrean admin `/members/registrations`,
  - tabel `member_registration_requests`.
- Berkas wajib pendaftaran:
  - foto diri,
  - KTP,
  - KK.
- Pendaftar luar Rembang:
  - NIK yang tidak diawali `3317` wajib menyertakan surat pendukung,
  - contoh surat: domisili desa, pondok, sekolah, atau keterangan instansi lain yang berlaku.
- Approval admin:
  - membuat member aktif,
  - membuat akun login role `USER`,
  - username memakai NIK,
  - password awal `perpus2026`,
  - nomor anggota otomatis `PDR-3317-YYYY-000001`.
- Foto dari pendaftaran online sekarang dibaca langsung dari path `assets/uploads/member_registrations`, bukan fallback mirror INLISLite.

Validasi:

- Lint PHP bersih untuk controller, model, dan view yang diubah.
- HTTP smoke test publik berhasil untuk:
  - `/`,
  - `/katalog` dengan filter baru,
  - `/membership/register`.
- HTTP smoke test admin berhasil untuk:
  - `/transactions`,
  - `/members/registrations`,
  - `/reading-points`,
  - `/reading-points/create`.
- Smoke test tambah Pojok Baca berhasil; data uji sudah dibersihkan.
- Smoke test pendaftaran online dengan upload foto/KTP/KK/surat berhasil; row dan file uji sudah dibersihkan.
- Smoke test NIK luar Rembang tanpa surat pendukung ditolak dan tidak membuat row.
- Smoke test approval pendaftaran online berhasil membuat member dan akun login username NIK; data member, user, role, row pendaftaran, dan file uji sudah dibersihkan.
- Validasi visual mobile dilakukan dengan Chrome headless/CDP untuk `/transactions` dan `/members/registrations`.
- Push Git tidak dilakukan otomatis.

## 2026-08-01 22:33 WIB

Status: opsi member, pending dashboard pendaftaran, master buku, filter katalog, map drag Pojok Baca, dan compact hero selesai.

Yang dilakukan:

- Menambahkan migration `sql/2026-08-01e_catalog_master_categories.sql`:
  - tabel `book_content_categories`,
  - tabel `book_classification_masters`,
  - kolom `books.content_category_id`,
  - kolom `books.content_classification_id`,
  - seed awal fiksi, non-fiksi, pengetahuan, karya ilmiah, lokal Rembang, referensi, sejarah-budaya, agama, teknologi, dan klasifikasi ringkas DDC.
- Menambahkan migration `sql/2026-08-01f_member_registration_pending_token.sql`:
  - kolom `member_registration_requests.public_token`,
  - token publik acak untuk URL pending agar NIK tidak memakai kode antrean berurutan di URL.
- Menambahkan halaman admin `/catalog/masters`:
  - tab Kategori Konten,
  - tab Klasifikasi Isi,
  - tambah/edit memakai modal karena data master relatif pendek.
- CRUD katalog admin sekarang mengakomodir:
  - kategori isi,
  - klasifikasi isi.
- Katalog publik `/katalog` sekarang punya filter:
  - kategori isi,
  - klasifikasi isi,
  - kategori INLISLite,
  - media,
  - aturan,
  - lokasi perpustakaan,
  - tahun,
  - ketersediaan,
  - reset filter.
- Form member admin dan form pendaftaran online sekarang memakai pilihan baku untuk:
  - jenis identitas,
  - gender,
  - tipe member,
  - pendidikan,
  - pekerjaan.
- Tipe member awal:
  - Umum,
  - Pelajar,
  - Mahasiswa,
  - Guru/Tenaga Pendidik,
  - Peneliti,
  - Komunitas/Lembaga,
  - Istimewa.
- Setelah pendaftaran online berhasil, user diarahkan ke `/membership/register/pending/{public_token}`.
- Halaman pending menampilkan:
  - status verifikasi,
  - kode antrean,
  - username NIK,
  - password awal `perpus2026`,
  - pesan bahwa akun aktif setelah admin menyetujui.
- Pojok Baca `/reading-points/create` dan `/reading-points/edit/{id}` sekarang punya Leaflet map picker:
  - marker bisa di-drag,
  - klik peta memindahkan marker,
  - input latitude/longitude terisi otomatis.
- Pola hero besar dipadatkan via CSS:
  - command center layanan menjadi strip ringkas,
  - landing/public hero dibuat lebih ringan,
  - search band katalog dan detail buku dibuat lebih sederhana,
  - form pendaftaran mobile diperkuat agar tidak overflow.

Validasi:

- Lint PHP bersih untuk model, controller, dan view yang diubah.
- HTTP smoke test publik berhasil untuk:
  - `/katalog?content_category_id=1`,
  - `/membership/register`,
  - `/membership/register/pending/{public_token}`.
- HTTP smoke test admin dengan login superadmin berhasil untuk:
  - `/catalog/masters`,
  - `/catalog?content_category_id=1`,
  - `/catalog/create`,
  - `/reading-points/create`.
- Smoke test pendaftaran online berhasil redirect ke pending token; row dan file uji sudah dibersihkan.
- Screenshot headless dibuat untuk `/katalog` desktop dan `/membership/register` mobile.
- Browser internal Codex masih gagal karena `sandboxCwd must use the file URI scheme`; fallback Chrome headless dipakai.
- Push Git tidak dilakukan otomatis.

## 2026-08-01 22:58 WIB

Status: normalisasi member lama, refresh taksonomi katalog, dan check-in GPS Pojok Baca tahap awal selesai.

Yang dilakukan:

- Menambahkan migration `sql/2026-08-01g_normalize_member_catalog_taxonomy.sql`.
- Member hasil migrasi INLISLite dinormalisasi:
  - `member_type` memakai label seperti `Umum` / `Pelajar`,
  - `education` memakai label seperti `SMP`, `SMA`, `S1`,
  - `occupation` memakai label seperti `Pelajar`, `Pegawai Swasta`, `Guru`,
  - tidak lagi menampilkan angka ID sumber seperti `2` atau `9` di form.
- `Member_model::form_options()` sekarang mengambil opsi dari `inlislite_master_references` lalu digabung fallback aplikasi.
- Sinkronisasi member berikutnya menyimpan label operasional, bukan ID angka.
- Katalog ditata ulang dengan dua dimensi:
  - `Kategori Isi` sebagai payung pencarian yang mudah dipahami pemustaka,
  - `Klasifikasi Isi/DDC` sebagai filter subjek.
- Catatan desain: kategori dan klasifikasi tidak dipaksa menjadi parent-child murni di database karena keduanya bisa saling silang. UI katalog publik menyaring pilihan klasifikasi berdasarkan kategori terpilih agar terasa bertingkat.
- Mapping katalog diperkuat:
  - semua buku punya kategori isi,
  - klasifikasi isi dipetakan ulang dari `classification` dan `call_number`,
  - kategori anak-remaja, karya ilmiah, lokal Rembang, referensi, dan fallback DDC diperbarui.
- Check-in Pojok Baca tahap awal:
  - route `/user/reading-checkin`,
  - route POST `/user/reading-checkin/store`,
  - halaman member untuk ambil GPS browser,
  - server mencari titik aktif terdekat dalam radius,
  - token harian diterbitkan di `reading_tokens`,
  - dashboard member menampilkan status token baca.
- SOP token Pojok Baca dicatat di `docs/POJOK_BACA_TOKEN_SOP.md`.
- Dropdown `Kotak Masuk` admin diperbaiki agar tidak terpotong:
  - z-index topbar/dropdown dinaikkan,
  - overflow parent dibuat visible,
  - lebar menu dan layout badge antrean distabilkan.

Validasi:

- Total member: 5.389; field numerik tersisa:
  - `member_type`: 0,
  - `education`: 0,
  - `occupation`: 0.
- Total katalog: 14.097 buku;
  - kategori isi terisi: 14.097,
  - klasifikasi isi terisi: 9.479.
- Lint PHP bersih untuk file yang diubah.
- Smoke test admin:
  - `/members/edit/{id}` tidak lagi memuat opsi numerik `2`,
  - `/catalog/masters` 200.
- Smoke test publik:
  - `/katalog?content_category_id=5` 200.
- Smoke test member:
  - login member contoh,
  - `/user/reading-checkin` 200,
  - POST check-in dengan titik uji berhasil menerbitkan token 60 menit,
  - token dan titik uji sudah dibersihkan.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 00:18 WIB

Status: monitoring token admin, aturan kuota baru, dan gate Reader `location_only` selesai tahap awal.

Yang dilakukan:

- Menambahkan migration `sql/2026-08-02a_reader_token_monitoring.sql`.
- `reading_tokens` ditambah kolom:
  - `revoked_by`,
  - `revoked_at`,
  - `revoke_reason`.
- `reading_sessions` ditambah kolom:
  - `access_origin`,
  - `access_location_label`,
  - `quota_charged`,
  - `quota_unit`.
- Admin monitoring token tersedia di `/reading-points/tokens`.
- Admin dapat melihat:
  - token,
  - member,
  - titik,
  - sisa kuota,
  - status,
  - masa berlaku.
- Admin dapat mencabut token aktif.
- Aturan token diperbarui:
  - member bisa akses dari mana saja selama token aktif dan kuota tersedia,
  - akses dari luar lokasi mengurangi kuota,
  - akses dari Pojok Baca/perpustakaan aktif tidak mengurangi kuota,
  - token habis mendorong member login/check-in fisik untuk update token.
- Reader `location_only` sekarang:
  - meminta token aktif,
  - menampilkan gate validasi GPS,
  - mengurangi kuota saat akses luar lokasi,
  - tidak mengurangi kuota saat GPS berada dalam radius titik/perpustakaan,
  - mencatat sesi ke `reading_sessions`.
- Layanan harian menambahkan metrik:
  - `Akses Digital`,
  - `Luar Lokasi`.

Validasi:

- Lint PHP bersih untuk model, controller, dan view yang diubah.
- Smoke test `/reading-points/tokens` 200 dan menampilkan token uji.
- Smoke test revoke token berhasil mengubah status menjadi `revoked`; data uji dibersihkan.
- Smoke test Reader `location_only`:
  - gate lokasi 200,
  - akses luar lokasi 200 dan `quota_used` naik 1,
  - akses dari radius Pojok Baca 200 dan kuota tidak bertambah,
  - `reading_sessions` mencatat `external = 1` dan `reading_point = 0`,
  - data uji dibersihkan.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 09:40 WIB

Status: refresh tampilan global, landing publik, katalog publik compact, dan 10 PDF sampel legal selesai.

Yang dilakukan:

- Mengganti tipografi aplikasi ke `Plus Jakarta Sans` untuk UI dan `Fraunces` untuk headline publik tertentu agar terasa lebih premium tapi tetap ramah.
- Mengunci CDN Tabler Icons dari `@latest` ke `@3.34.1` pada layout admin dan halaman publik mandiri agar ikon tidak berubah mendadak.
- Memperbarui landing `/`:
  - headline menjadi `Pustaka Digital Rembang`,
  - hero dibuat lebih ringan,
  - menambahkan strip layanan `Katalog terpadu`, `Kartu digital`, dan `Pojok baca`,
  - tetap memakai logo Pemkab Rembang dan Perpusnas dari folder `img`.
- Menambahkan visual refresh global pada `assets/css/pustaka.css`:
  - warna biru Demokrat/putih lebih bersih,
  - sidebar admin lebih kontras,
  - card, tabel, tab, filter, dan tombol dibuat lebih konsisten,
  - tombol aksi dibuat lebih jelas dengan label dan icon,
  - mobile table dan tab tetap scroll/stack dengan aman.
- Merapikan katalog publik `/katalog`:
  - header pencarian dibuat lebih pendek,
  - form filter dibuat lebih padat,
  - kartu buku dibuat list-card compact agar hasil data langsung terlihat.
- Menambahkan folder `storage/ebooks/free_samples` untuk 10 PDF sampel.
- Menambahkan `storage/.htaccess` berisi `Require all denied` agar file PDF tidak bisa dibuka langsung melalui URL publik.
- Menambahkan seed SQL `sql/2026-08-02b_seed_free_sample_ebooks.sql`.
- Seed SQL menambahkan 10 buku sumber Project Gutenberg ke:
  - `books`,
  - `book_authors`,
  - `book_subjects`,
  - `book_items`,
  - `digital_assets`.
- Policy aset digital sampel:
  - 2 judul `download_allowed`,
  - 8 judul `location_only` untuk test token dan reader.

Daftar PDF sampel:

- `Alice's Adventures in Wonderland` - Lewis Carroll.
- `Pride and Prejudice` - Jane Austen.
- `Frankenstein` - Mary Wollstonecraft Shelley.
- `The Adventures of Sherlock Holmes` - Arthur Conan Doyle.
- `Moby-Dick` - Herman Melville.
- `A Tale of Two Cities` - Charles Dickens.
- `The Adventures of Tom Sawyer` - Mark Twain.
- `Dracula` - Bram Stoker.
- `Great Expectations` - Charles Dickens.
- `The Prince` - Niccolo Machiavelli.

Validasi:

- `php -l` bersih untuk view yang diubah:
  - `home/landing.php`,
  - `layouts/tabler.php`,
  - `public_catalog/index.php`,
  - `public_catalog/detail.php`,
  - `membership/register.php`,
  - `membership/pending.php`,
  - `membership/verify.php`,
  - `user/dashboard.php`,
  - `user/reading_checkin.php`,
  - `reader/member_read.php`,
  - `reader/location_gate.php`.
- HTTP landing `/` status 200.
- HTTP katalog publik `/katalog?q=Alice` status 200 dan menampilkan sampel.
- HTTP login `superadmin` / `admin123` ke `/admin` status 200 dan sidebar terdeteksi.
- Direct URL PDF `http://localhost/pustaka/storage/ebooks/free_samples/alice-adventures-wonderland.pdf` status 403.
- Database setelah seed:
  - sample books: 10,
  - sample book items: 10,
  - sample digital assets: 10,
  - total ukuran PDF: 60.940.267 bytes.
- Screenshot verifikasi tersimpan di `storage/debug_screenshots`.
- Browser internal Codex gagal tersambung karena error sandbox `sandboxCwd must use the file URI scheme`; verifikasi visual dilakukan dengan Chrome headless.
- Push Git tidak dilakukan otomatis.

Tambahan setelah cek langsung:

- PDF sampel memang sudah tersimpan di server lokal `storage/ebooks/free_samples`.
- Cover 10 sampel dibuat lokal di `assets/uploads/sample_ebooks/covers`.
- `books.cover_local_path` untuk 10 sampel sudah diisi, sehingga katalog publik tidak lagi memakai ikon default.
- Seed SQL `sql/2026-08-02b_seed_free_sample_ebooks.sql` diperbarui agar path cover ikut dipulihkan saat seed dijalankan ulang.
- Reader awal sekarang bisa menampilkan PDF melalui endpoint login-protected `reader/stream/{asset_id}`.
- `reader/read/{asset_id}` menampilkan iframe PDF untuk admin/member setelah gate akses lolos.
- Direct file PDF di `storage/...` tetap status 403.

Validasi tambahan:

- Cover `alice-adventures-wonderland.png` status 200.
- `/katalog?q=Project%20Gutenberg` status 200, menampilkan 10 judul sampel, dan memuat cover dari `sample_ebooks/covers`.
- Login admin lalu akses `/reader/stream/3` status 200, `Content-Type: application/pdf`, dan byte awal `%PDF`.
- Screenshot katalog sampel dengan cover tersimpan di `storage/debug_screenshots/katalog-gutenberg-covers.png`.

## 2026-08-02 10:25 WIB

Status: navigasi pemustaka, dashboard member, dan tombol baca online dari katalog selesai tahap UX awal.

Yang dilakukan:

- Dashboard member `/user/dashboard` dirombak agar lebih mudah dipakai:
  - topbar punya Beranda, Katalog, Pojok Baca, dan Logout,
  - mobile memakai bottom navigation,
  - ditambahkan rak `Buku Digital - Siap dibaca online`,
  - setiap buku digital punya tombol `Baca Online`.
- Halaman Pojok Baca `/user/reading-checkin` ditambah navigasi lengkap di desktop dan mobile.
- Halaman reader member dan gate lokasi ditambah navigasi lengkap agar user tidak tersesat.
- Katalog publik `/katalog` sekarang sadar status login:
  - jika sudah login, tidak menampilkan tombol `Masuk`,
  - menampilkan Dashboard dan Logout,
  - mobile punya bottom navigation.
- Kartu katalog publik sekarang menampilkan tombol:
  - `Detail`,
  - `Baca Online` jika buku punya aset digital aktif.
- Detail katalog publik menampilkan panel `Buku Digital` dengan tombol `Baca Online` dan label policy aset.
- Login flow diperbaiki:
  - jika user menekan link reader saat belum login, setelah login kembali ke halaman reader tersebut.
- Route eksplisit `reader/stream/(:num)` ditambahkan.
- Catatan reader:
  - mode uji sekarang masih PDF inline/scroll,
  - animasi swipe/flip belum final,
  - desain final yang disarankan adalah render PDF per halaman terlebih dahulu, lalu UI swipe/flip di atas halaman yang sudah diberi watermark dan audit.

Validasi:

- Lint PHP bersih untuk model/controller/view yang diubah.
- Login member `3317101401620001` / `perpus2026` berhasil.
- `/user/dashboard` status 200 dan memuat rak buku digital + tombol `Baca Online`.
- `/katalog?q=Project%20Gutenberg` setelah login:
  - status 200,
  - tombol `Masuk` hilang,
  - Dashboard tampil,
  - tombol `Baca Online` tampil pada kartu.
- `/katalog/detail/14099` status 200 dan menampilkan panel baca online.
- `/reader/read/3` sebagai member status 200 dan memuat iframe reader PDF.
- `/reader/read/4` sebagai member status 200 dan menampilkan gate `Validasi lokasi baca` untuk aset `location_only`.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 10:50 WIB

Status: landing page diperbaiki lagi, termasuk nav yang sadar status login.

Yang dilakukan:

- Landing `/` sekarang membaca session login.
- Jika sudah login sebagai member, admin, atau superadmin:
  - tombol `Masuk` tidak tampil,
  - tombol `Daftar Member` tidak tampil,
  - nav menampilkan `Dashboard` dan `Logout`.
- Link dashboard otomatis:
  - `superadmin` dan `admin` ke `/admin`,
  - pemustaka/member ke `/user/dashboard`.
- Mobile bottom nav landing ikut disesuaikan:
  - guest melihat Beranda, Katalog, Daftar, Masuk,
  - user login melihat Beranda, Katalog, Dashboard, Logout.
- Landing page dipoles ulang dengan palet premium biru Demokrat, putih, dan aksen emas halus:
  - nav lebih kontras,
  - tombol utama lebih kuat,
  - chip layanan dan card ringkasan lebih bersih,
  - hero tetap memakai peta Rembang dan logo resmi.

Validasi:

- Lint PHP bersih untuk `application/views/home/landing.php`.
- Landing guest status 200 dan masih menampilkan `Masuk` + `Daftar Member`.
- Login member `3317101401620001` / `perpus2026`, lalu buka `/`:
  - status 200,
  - `Masuk` hilang,
  - `Daftar Member` hilang,
  - `Dashboard` tampil dan mengarah ke `/user/dashboard`,
  - `Logout` tampil.
- Login admin `superadmin` / `admin123`, lalu buka `/`:
  - status 200,
  - `Masuk` hilang,
  - `Daftar Member` hilang,
  - `Dashboard` tampil dan mengarah ke `/admin`,
  - `Logout` tampil.
- Screenshot guest tersimpan di `storage/debug_screenshots/landing-premium-20260802.png`.
- Browser internal Codex masih gagal tersambung karena sandbox `sandboxCwd must use the file URI scheme`; verifikasi visual dilakukan dengan Chrome headless lokal.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 11:20 WIB

Status: tema visual seluruh halaman ditegaskan ulang agar area, card, form, tabel, sidebar, dan landing lebih premium serta tidak blur.

Yang dilakukan:

- Menambahkan `Executive contrast layer` di `assets/css/pustaka.css`.
- Tema global dibuat lebih tegas:
  - background aplikasi admin menjadi abu terang solid,
  - `page-header` putih dengan border bawah jelas,
  - area kerja memakai background berbeda dari card,
  - card/form/tabel memakai border lebih tegas dan shadow lebih bersih,
  - header card diberi aksen biru vertikal,
  - tab dan filter dibuat lebih kontras,
  - sidebar dibuat solid biru Demokrat ke navy, tidak terlalu lembut.
- Landing `/` diperbaiki:
  - hero dipendekkan dari gaya hampir satu layar menjadi panel lebih ringkas,
  - peta Rembang menjadi panel kanan yang tegas dengan border, radius, dan shadow,
  - overlay blur dikurangi,
  - area kosong kanan peta dipotong dengan layout panel,
  - section bawah diberi border agar pemisahan antar area jelas.
- Mobile landing dikunci ulang:
  - overflow horizontal dicegah,
  - tombol hero jadi grid satu kolom,
  - chip layanan dan statistik mengikuti lebar layar,
  - bottom nav publik dikunci 4 kolom.

Validasi:

- Landing `/` status 200.
- Admin `/reading-points/create` status 200 setelah login superadmin.
- Screenshot visual:
  - `storage/debug_screenshots/landing-theme-v2.png`,
  - `storage/debug_screenshots/admin-theme-v2.png`,
  - `storage/debug_screenshots/reading-point-form-theme-v2.png`,
  - `storage/debug_screenshots/landing-mobile-theme-v2c.png`.
- Screenshot admin/form menunjukkan pemisahan sidebar, topbar, page header, area kerja, card, dan form sudah lebih jelas.
- Screenshot landing desktop menunjukkan peta sudah menjadi panel kanan yang lebih nyata dan hero lebih pendek.
- Catatan: screenshot mobile Chrome headless memakai viewport CSS minimum yang lebih lebar dari file gambar, sehingga item nav keempat bisa terlihat terpotong di screenshot alat; HTML tetap memuat 4 item dan CSS bottom nav sudah grid 4 kolom.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 11:55 WIB

Status: polish visual final tahap berikutnya selesai untuk landing, login, dan pendaftaran member.

Yang dilakukan:

- Menambahkan file tema final `assets/css/pustaka-polish.css`.
- File polish dimuat setelah `assets/css/pustaka.css` agar override final tidak kalah oleh duplikasi lama di `pustaka.css`.
- Link `pustaka-polish.css` diberi cache-buster `?v=20260802a` di:
  - layout admin,
  - landing,
  - login,
  - pendaftaran/pending/verifikasi membership,
  - katalog publik,
  - reader,
  - dashboard dan check-in member.
- Landing `/` dipadatkan lagi:
  - peta menjadi panel kanan yang lebih pendek,
  - jarak kanan panel peta dikurangi,
  - center peta digeser lebih ke darat,
  - logo/eyebrow hero disembunyikan agar tidak tertutup topbar,
  - statistik hero disembunyikan karena sudah tampil di section katalog bawah,
  - tombol `Mulai jelajah` dihapus dari hero karena mengganggu komposisi compact.
- `/login` dirombak:
  - layout dua panel,
  - panel kiri brand biru navy,
  - logo Pemkab dan Perpusnas tampil,
  - pesan manfaat login,
  - form kanan lebih bersih,
  - label berubah menjadi `Masuk Akun`,
  - ditambahkan link ke `/membership/register`.
- `/membership/register` dipoles:
  - nav publik ditambah `Beranda`,
  - intro memakai logo Pemkab dan Perpusnas,
  - headline lebih ramah,
  - panel form punya header `Data calon anggota`,
  - submit area dibuat sebagai bar terpisah,
  - link kembali ke login ditampilkan jelas,
  - mobile register dikunci agar panel tidak melebar di viewport kecil.

Validasi:

- Lint PHP bersih untuk 12 view yang disentuh.
- HTTP publik status 200 dan memuat `pustaka-polish.css?v=20260802a`:
  - `/`,
  - `/login`,
  - `/membership/register`,
  - `/katalog`.
- HTTP admin setelah login superadmin status 200 dan memuat `pustaka-polish.css?v=20260802a`:
  - `/admin`,
  - `/reading-points/create`,
  - `/members`,
  - `/transactions`.
- Screenshot validasi:
  - `storage/debug_screenshots/landing-polish-v3d.png`,
  - `storage/debug_screenshots/login-polish-v3b.png`,
  - `storage/debug_screenshots/register-polish-v3.png`,
  - `storage/debug_screenshots/register-mobile-polish-v3g.png`.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 12:17 WIB

Status: landing dipadatkan lagi dan session login diperpanjang menjadi 3 hari idle.

Yang dilakukan:

- Landing `/` dibuat lebih compact:
  - tinggi hero turun ke kisaran 24-29rem,
  - judul dibuat 2 baris agar tidak memakan ruang vertikal,
  - peta ditarik lebih ke tengah dan tetap rapat ke kanan,
  - chip layanan di hero disembunyikan untuk mengurangi ruang kosong,
  - hero tetap menampilkan CTA utama `Cari Buku`, `Daftar Member`/`Dashboard`, dan `Lihat Jejaring`.
- Cache-buster `pustaka-polish.css` dinaikkan ke `?v=20260802c`.
- Konfigurasi session CodeIgniter di `application/config/config.php` diubah:
  - `sess_expiration = 259200`,
  - artinya sesi login bertahan 3 hari sejak aktivitas terakhir.

Validasi:

- Lint PHP bersih untuk `application/config/config.php` dan view landing/login/register.
- Landing `/` status 200 dan memuat `pustaka-polish.css?v=20260802c`.
- Login superadmin mengirim cookie `ci_session` dengan `Max-Age=259200`.
- Screenshot landing compact terbaru: `storage/debug_screenshots/landing-compact-v4b.png`.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 14:50 WIB

Status: landing diperbaiki dari sisi lebar dan fondasi kunjungan fisik/digital mulai hidup.

Yang dilakukan:

- Landing `/` diubah dari overlay peta absolut menjadi grid dua kolom:
  - judul tidak lagi terpotong topbar,
  - peta memakai lebar kolom kanan penuh,
  - ruang kosong kanan hilang,
  - mobile tetap satu kolom dengan peta di bawah copy.
- Cache-buster `pustaka-polish.css` dinaikkan ke `?v=20260802d`.
- Menambahkan migration `sql/2026-08-02c_visit_channels_guestbook.sql`.
- `member_visits` diperluas untuk membedakan kanal/asas kunjungan:
  - `inlislite_guestbook`,
  - `library_guestbook`,
  - `member_dashboard`,
  - `digital_access`,
  - `reading_point`,
  - `service_monitor`,
  - `qr_checkin`.
- Menambahkan `visit_origin` untuk laporan lokasi:
  - `library`,
  - `reading_point`,
  - `digital_external`,
  - `digital_internal`,
  - `legacy`.
- Menambahkan data rombongan/kelompok:
  - `visitor_count`,
  - `group_name`,
  - `group_leader_name`.
- Menambahkan tabel awal monitor pelayanan:
  - `visit_kiosk_settings`,
  - `visit_kiosk_qr_tokens`.
- Menambahkan `application/models/Visit_model.php` sebagai service pencatatan kunjungan baru.
- Dashboard member `/user/dashboard` sekarang mencatat `member_dashboard` satu kali per member per hari.
- Reader member mencatat sesi baca ke `member_visits` sebagai `digital_access`; akses dari luar lokasi bisa dipisah lewat `visit_origin = digital_external`.
- Check-in GPS Pojok Baca mencatat kunjungan sebagai `reading_point`.
- Menambahkan monitor buku tamu publik:
  - `/guestbook/monitor`,
  - QR dinamis refresh default 60 detik,
  - form pengunjung non-member,
  - form member via NIK/nomor anggota,
  - dukungan kunjungan rombongan lewat `visitor_count`.
- Scan QR member masuk ke `/guestbook/checkin/{token}` dan redirect ke dashboard setelah tercatat.
- Halaman `/transactions` tab Buku Tamu punya filter kanal kunjungan dan kolom kanal/origin.

Validasi:

- Migration `2026-08-02c_visit_channels_guestbook.sql` berhasil dijalankan ke database lokal `pustaka`.
- Lint PHP bersih untuk:
  - `application/models/Visit_model.php`,
  - `application/models/Reader_model.php`,
  - `application/models/Reading_point_model.php`,
  - `application/controllers/User_dashboard.php`,
  - `application/controllers/Transactions.php`,
  - `application/controllers/Guestbook.php`,
  - `application/views/transactions/index.php`,
  - `application/views/home/landing.php`,
  - `application/views/guestbook/monitor.php`.
- Login member `3317101401620001` / `perpus2026` berhasil membuka `/user/dashboard`.
- Dua kali buka dashboard pada hari yang sama hanya membuat 1 kunjungan `member_dashboard`.
- `/guestbook/monitor` status 200 dan menampilkan QR, tab Pengunjung, dan tab Member.
- Submit tamu rombongan dan member manual menambah 2 kunjungan fisik.
- Scan QR member berhasil mencatat `qr_checkin` dan redirect ke `/user/dashboard`.
- Screenshot landing baru: `storage/debug_screenshots/landing-grid-v5.png`.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 15:20 WIB

Status: sidebar ditata ulang berdasarkan rumpun kerja dan modul laporan kunjungan dibuat.

Yang dilakukan:

- Menambahkan migration `sql/2026-08-02d_reports_sidebar_reorder.sql`.
- Sidebar admin ditata ulang dengan urutan top-level:
  - `Dashboard`,
  - `Laporan & Analitik`,
  - `Jejaring & Agenda`,
  - `Koleksi & Katalog`,
  - `Keanggotaan`,
  - `Layanan Harian`,
  - `Layanan Digital`,
  - `Data Master`,
  - `Pengaturan Sistem`.
- Rumpun `Koleksi & Katalog` berisi:
  - Katalog Buku,
  - Master Buku,
  - Request Buku,
  - Sinkronisasi Katalog.
- Rumpun `Keanggotaan` berisi:
  - Data Member,
  - Pendaftaran Online,
  - Perpanjangan,
  - Sinkronisasi Member.
- Rumpun `Layanan Harian` berisi:
  - Aktivitas Layanan,
  - Monitor Buku Tamu,
  - Sinkronisasi Layanan.
- Rumpun `Layanan Digital` berisi:
  - Reader PDF Aman,
  - Pojok Baca,
  - Monitoring Token.
- Menambahkan halaman `reports.visits` dengan route:
  - `/reports`,
  - `/reports/visits`.
- Permission `reports.visits` diberikan ke `SUPERADMIN` dan `ADMIN` untuk `view` dan `export`.
- Menambahkan:
  - `application/controllers/Reports.php`,
  - `application/models/Report_model.php`,
  - `application/views/reports/visits.php`.
- Laporan kunjungan mendukung filter:
  - tahunan,
  - bulanan,
  - harian,
  - custom range tanggal.
- Laporan menampilkan:
  - total orang,
  - total entri kunjungan,
  - jumlah member,
  - jumlah kunjungan rombongan,
  - grafik tren,
  - grafik komposisi kanal,
  - breakdown kanal,
  - breakdown asal layanan,
  - breakdown metode check-in,
  - kunjungan terbaru.
- Cache-buster `pustaka-polish.css` dinaikkan ke `?v=20260802e`.

Validasi:

- Migration `2026-08-02d_reports_sidebar_reorder.sql` berhasil dijalankan ke database lokal.
- Lint PHP bersih untuk:
  - `application/models/Report_model.php`,
  - `application/controllers/Reports.php`,
  - `application/views/reports/visits.php`,
  - `application/config/routes.php`.
- Endpoint `/reports/visits` tanpa login redirect ke `/login` status 307, sesuai proteksi admin.
- Query agregasi kunjungan Agustus 2026 berhasil:
  - total terhitung 7 orang dari 4 entri uji,
  - kanal uji terbaca: `library_guestbook`, `service_monitor`, `member_dashboard`, `qr_checkin`.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 15:35 WIB

Status: pencarian member di monitor buku tamu dibuat AJAX dan mendukung nama.

Yang dilakukan:

- Menambahkan endpoint JSON `/guestbook/search-members`.
- Pencarian member monitor sekarang menerima keyword:
  - NIK,
  - nomor anggota,
  - nama member.
- Tab `Member` pada `/guestbook/monitor` menampilkan hasil pencarian sebagai kartu pilihan.
- Form submit mengirim `member_id` hasil pilihan AJAX agar tidak ambigu saat ada nama mirip.
- Fallback non-JS tetap menerima NIK/nomor anggota/nama persis.
- Cache-buster `pustaka-polish.css` dinaikkan ke `?v=20260802f`.

Validasi:

- Lint PHP bersih untuk `Visit_model`, `Guestbook`, view monitor, dan routes.
- GET `/guestbook/search-members?q=heri` status 200 dan mengembalikan data member.
- Submit kunjungan member memakai `member_id` hasil pencarian berhasil menambah `service_monitor`.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 15:45 WIB

Status: pengaturan waktu refresh QR monitor buku tamu dibuat di admin.

Yang dilakukan:

- Menambahkan halaman admin `/guestbook/settings`.
- Menambahkan route POST `/guestbook/settings/update`.
- Menambahkan `Guestbook_settings` controller.
- Menambahkan view `application/views/guestbook/settings.php`.
- `Visit_model` ditambah:
  - `get_kiosk_settings()`,
  - `update_kiosk_settings()`.
- Admin bisa mengatur:
  - durasi refresh QR monitor, batas 15-600 detik,
  - perpustakaan default monitor jika URL `/guestbook/monitor` dibuka tanpa `library_id`.
- Menambahkan migration `sql/2026-08-02e_guestbook_settings.sql`.
- Menu `Pengaturan Buku Tamu` ditambahkan di rumpun `Layanan Harian`.
- Permission `guestbook.settings` diberikan ke `SUPERADMIN` dan `ADMIN` untuk view/edit.
- Cache-buster `pustaka-polish.css` dinaikkan ke `?v=20260802g`.

Validasi:

- Migration berhasil dijalankan ke database lokal.
- Lint PHP bersih untuk:
  - `Guestbook_settings`,
  - view pengaturan buku tamu,
  - `Visit_model`,
  - routes.
- `/guestbook/settings` tanpa login redirect ke `/login` status 307.
- Menu `Pengaturan Buku Tamu` masuk di bawah `Layanan Harian`.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 16:20 WIB

Status: laporan kunjungan siap export dan reader PDF mendapat lapisan keamanan sesi/audit.

Yang dilakukan untuk laporan:

- Menambahkan endpoint:
  - `/reports/visits/print`,
  - `/reports/visits/excel`.
- Menambahkan view export:
  - `application/views/reports/visits_print.php`,
  - `application/views/reports/visits_excel.php`.
- Export Excel memakai file `.xls` berbasis HTML table agar jalan tanpa library tambahan.
- Export PDF memakai halaman print-friendly yang bisa dicetak atau `Save as PDF` dari browser.
- Tombol `Cetak / PDF` dan `Excel` ditambahkan di `/reports/visits`.
- Data export mengikuti filter aktif: tahun, bulan, hari, atau custom range.
- `Report_model` ditambah `visit_rows()` untuk detail export maksimal 50.000 baris.

Yang dilakukan untuk reader:

- Menambahkan migration `sql/2026-08-02f_reader_secure_session_audit.sql`.
- `reading_sessions` ditambah:
  - `secure_token`,
  - `last_seen_at`.
- Menambahkan tabel `reader_access_logs`.
- Stream PDF member sekarang wajib membawa `session` dan `token` yang cocok.
- Stream tanpa token/sesi valid ditolak.
- Reader member diganti dari iframe browser ke PDF.js canvas.
- Toolbar bawaan PDF browser tidak dipakai.
- Watermark dinamis ditampilkan di atas halaman:
  - nama member,
  - nomor anggota,
  - waktu,
  - nomor halaman.
- Endpoint audit halaman ditambahkan:
  - `/reader/audit-page`.
- Setiap halaman yang dirender dicatat sebagai `page_rendered`.
- Stream dicatat sebagai `pdf_stream`.
- Sesi dibuka dicatat sebagai `session_opened`.
- Rate limit ringan stream: maksimal 12 stream per sesi per menit.

Validasi:

- Migration reader berhasil dijalankan ke database lokal.
- Lint PHP bersih untuk:
  - `Reports`,
  - `Reader`,
  - `Reader_model`,
  - `Report_model`,
  - view export laporan,
  - view reader member,
  - routes.
- `/reports/visits/print` dan `/reports/visits/excel` tanpa login redirect ke `/login`.
- Login member `3317101401620001` / `perpus2026`, buka `/reader/read/{asset_download_allowed}` status 200.
- Halaman reader memuat PDF.js dan watermark.
- `reading_sessions.secure_token` terisi.
- `/reader/stream/{asset}` tanpa token ditolak.
- `/reader/stream/{asset}?session=...&token=...` berhasil `application/pdf`.
- POST `/reader/audit-page` dengan token valid menghasilkan `{"ok":true}` dan mencatat `page_rendered`.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 16:35 WIB

Status: proteksi PDF non-downloadable diperketat agar file utuh tidak muncul di browser/Network.

Yang dilakukan:

- Menambahkan aturan server-side di `Reader::stream()`:
  - hanya aset `is_downloadable = 1` dan `access_policy = download_allowed` yang boleh dikirim sebagai `application/pdf`,
  - aset non-downloadable selalu ditolak untuk stream PDF utuh walaupun ada parameter `session` dan `token`.
- Reader member untuk aset non-downloadable tidak lagi membuat URL `/reader/stream/{asset_id}` di HTML.
- View reader non-downloadable menampilkan status terkunci dan menjelaskan bahwa file PDF utuh tidak dikirim ke perangkat.
- Percobaan stream aset terkunci dicatat ke `reader_access_logs` sebagai `blocked` dengan reason `non_downloadable_raw_pdf_denied`.
- Catatan implementasi penting: agar aset non-downloadable tetap bisa dibaca tanpa membocorkan PDF utuh di Network tab, tahap berikutnya harus memasang renderer server-side per halaman, misalnya Poppler/Ghostscript/Imagick, lalu endpoint mengirim gambar halaman ber-watermark, bukan file PDF.

Validasi:

- Lint PHP bersih untuk:
  - `application/controllers/Reader.php`,
  - `application/models/Reader_model.php`,
  - `application/views/reader/member_read.php`.
- Login member `3317101401620001` / `perpus2026`.
- Aset bebas unduh `/reader/read/3` status 200 dan masih memuat `/reader/stream/3` + PDF.js.
- Aset terkunci `/reader/read/4?lat=-6.7513701&lng=111.4334398` status 200, tidak memuat `/reader/stream/4`, tidak memuat PDF.js, dan menampilkan pesan `PDF dikunci dari browser`.
- Permintaan paksa ke `/reader/stream/4?session=...&token=...` ditolak `403 Forbidden`, `Content-Type: text/html`, bukan `application/pdf`.
- Audit `reader_access_logs` mencatat event `blocked` untuk aset 4/member 165.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 16:55 WIB

Status: reader aman non-downloadable sudah bisa membaca per halaman tanpa mengirim PDF utuh ke browser.

Yang dilakukan:

- Menambahkan renderer server-side:
  - `scripts/render_pdf_page.py`.
- Renderer memakai Python + PyMuPDF + Pillow untuk:
  - membaca metadata jumlah halaman,
  - render 1 halaman PDF menjadi PNG,
  - menanam watermark langsung ke gambar halaman.
- Dependency lokal yang dipasang:
  - `python -m pip install --user pymupdf`.
- Menambahkan endpoint:
  - `/reader/page-info/{asset_id}`,
  - `/reader/page/{asset_id}/{page_number}`.
- Untuk aset non-downloadable, view member reader sekarang memakai mode gambar halaman:
  - `data-page-info-url`,
  - `data-page-url-base`,
  - tombol sebelumnya/berikutnya,
  - response halaman berupa `image/png`.
- File PDF asli tetap berada di `storage` dan tidak bisa diakses langsung karena `storage/.htaccess`.
- Stream PDF utuh tetap ditolak untuk aset non-downloadable.
- Audit halaman server-rendered dicatat sebagai `page_rendered` dengan meta `delivery = server_rendered_png`.
- Cache hasil render ditulis ke `storage/cache/reader_pages`; cache ini tetap di bawah `storage` yang tertutup akses langsung.
- `.gitignore` ditambah `/storage/cache/` agar PNG cache sesi baca tidak ikut commit.
- Cache-buster tema dinaikkan ke `pustaka-polish.css?v=20260802i`.

Validasi:

- `python scripts/render_pdf_page.py info --input storage/ebooks/free_samples/pride-and-prejudice.pdf` menghasilkan `{"ok": true, "pages": 218}`.
- Lint PHP bersih untuk:
  - `Reader`,
  - `Reader_model`,
  - `member_read`,
  - routes.
- Login member `3317101401620001` / `perpus2026`.
- `/reader/read/4?lat=-6.7513701&lng=111.4334398`:
  - status 200,
  - tidak memuat `/reader/stream/4`,
  - tidak memuat PDF.js,
  - memuat `secure-image-reader` dan `/reader/page-info/4`.
- `/reader/page-info/4?session=...&token=...` status 200 dan mengembalikan `{"ok":true,"pages":218}`.
- `/reader/page/4/1?session=...&token=...` status 200, `Content-Type: image/png`, signature PNG valid `89504E470D0A1A0A`.
- Preview PNG ber-watermark tersimpan di `storage/debug_screenshots/reader-page-test.png`.
- Permintaan paksa `/reader/stream/4?session=...&token=...` tetap `403 Forbidden`.
- Akses langsung `/storage/ebooks/free_samples/pride-and-prejudice.pdf` tetap `403 Forbidden`.
- Push Git tidak dilakukan otomatis.

## 2026-08-02 17:10 WIB

Status: reader aman ditambah gesture swipe/tap agar nyaman dipakai di HP.

Yang dilakukan:

- Reader non-downloadable `secure-image-reader` sekarang mendukung:
  - tombol atas sebelumnya/berikutnya,
  - tap area kiri halaman untuk mundur,
  - tap area kanan halaman untuk maju,
  - swipe kanan untuk mundur,
  - swipe kiri untuk maju,
  - keyboard kiri/kanan di desktop.
- Reader PDF.js untuk aset bebas unduh juga diberi tap zone dan swipe agar perilaku navigasinya konsisten.
- Animasi halaman ditambahkan:
  - `is-turning-next`,
  - `is-turning-prev`,
  - keyframe `pdrPageInNext`,
  - keyframe `pdrPageInPrev`.
- Hint reader diubah agar user tahu bisa tap/swipec halaman.
- Mobile reader dipadatkan:
  - tombol teks disembunyikan di layar kecil,
  - area halaman memenuhi viewport lebih nyaman,
  - tap zone dibuat lebih lebar.
- Cache-buster tema dinaikkan ke `pustaka-polish.css?v=20260802j`.

Validasi:

- Lint PHP bersih untuk:
  - `member_read`,
  - `Reader`,
  - `Reader_model`,
  - routes.
- Semua view yang memuat polish CSS sudah memakai `?v=20260802j`.
- Login member `3317101401620001` / `perpus2026`.
- `/reader/read/4?lat=-6.7513701&lng=111.4334398`:
  - status 200,
  - memuat `secure-image-reader`,
  - memuat `data-page-tap-prev`,
  - memuat `data-page-tap-next`,
  - memuat hint tap sisi kanan/kiri,
  - tidak memuat `/reader/stream/4`,
  - tidak memuat PDF.js.
- `/reader/page/4/3?session=...&token=...` status 200 dan `Content-Type: image/png`.
- Push Git tidak dilakukan otomatis.

## 2026-08-03 00:55 WIB

Status: manajemen ebook, hak publikasi, upload PDF admin, dan preview admin aman selesai tahap dasar.

Yang dilakukan:

- Menambahkan migration `sql/2026-08-03a_digital_asset_rights_admin.sql`.
- Tabel `digital_assets` ditambah kolom hak publikasi:
  - `rights_basis`,
  - `rights_holder`,
  - `license_url`,
  - `permission_reference`,
  - `permission_starts_at`,
  - `permission_ends_at`,
  - `access_notes`.
- Modul `/reader/assets` dirombak menjadi workspace admin:
  - filter pencarian,
  - filter policy akses,
  - filter dasar hak publikasi,
  - filter status,
  - pagination,
  - KPI aset PDF, aktif, download dikunci, dan izin hampir habis,
  - audit reader terbaru.
- Menambahkan halaman:
  - `/reader/assets/create`,
  - `/reader/assets/edit/{id}`.
- Menambahkan aksi:
  - `POST /reader/assets/store`,
  - `POST /reader/assets/update/{id}`,
  - `POST /reader/assets/status/{id}`.
- Menambahkan halaman audit detail:
  - `/reader/audit`.
- Upload PDF manual disimpan ke `storage/ebooks/manual/YYYY/MM`.
- Validasi upload:
  - ekstensi harus `.pdf`,
  - header file harus `%PDF`,
  - file tidak disimpan ke folder publik.
- Policy download dikunci di server:
  - PDF utuh hanya boleh keluar untuk `access_policy = download_allowed` dan `is_downloadable = 1`,
  - policy lain otomatis memaksa `is_downloadable = 0`.
- Preview admin untuk aset non-downloadable sekarang memakai renderer halaman PNG:
  - `/reader/admin-page-info/{asset_id}`,
  - `/reader/admin-page/{asset_id}/{page}`.
- Endpoint `/reader/stream/{asset_id}` sekarang menolak aset non-downloadable untuk admin juga, bukan hanya member.
- Audit admin reader dicatat ke `reader_access_logs` dengan meta `admin_user_id` dan `admin_username`.

Validasi:

- Migration berhasil dijalankan ke database lokal `pustaka`.
- Lint PHP bersih untuk:
  - `Reader`,
  - `Reader_model`,
  - `reader/assets`,
  - `reader/form`,
  - `reader/read`,
  - `reader/member_read`,
  - routes.
- Login `superadmin` / `admin123`.
- `/reader/assets` status 200 dan memuat `Tambah Ebook`, `Filter Ebook`, dan `Audit Reader Terbaru`.
- `/reader/audit` status 200 dan memuat `Log Akses Reader`.
- `/reader/assets/create` status 200 dan memuat form upload PDF serta hak publikasi.
- `/reader/assets/edit/4` status 200 dan memuat policy akses serta file saat ini.
- `/reader/read/4` sebagai admin:
  - status 200,
  - memuat `/reader/admin-page-info/4`,
  - tidak memuat `/reader/stream/4`,
  - memuat `secure-image-reader`.
- `/reader/admin-page-info/4` status 200 dan mengembalikan `{"ok":true,"pages":218}`.
- `/reader/admin-page/4/1` status 200 dan `Content-Type: image/png`.
- `/reader/stream/4` sebagai admin tetap `403 Forbidden`.
- `/reader/read/3` sebagai admin untuk aset bebas download tetap memuat `/reader/stream/3`.
- `/reader/stream/3` status 200 dan `Content-Type: application/pdf`.
- Smoke test upload PDF:
  - POST `/reader/assets/store` redirect 303 ke `/reader/assets`,
  - row draft tersimpan dengan `access_policy = member_only`, `is_downloadable = 0`, `rights_basis = public_domain`,
  - file tersimpan di `storage/ebooks/manual/2026/08`,
  - data/file smoke test sudah dibersihkan.
- Push Git tidak dilakukan otomatis.

## 2026-08-03 01:15 WIB

Status: review ulang roadmap, progres, dan sisa pekerjaan selesai dicatat.

Yang dilakukan:

- Membaca ulang:
  - `docs/PROGRESS.md`,
  - `docs/ROADMAP.md`,
  - `docs/HANDOVER.md`,
  - status Git lokal.
- Mengoreksi checklist roadmap yang sudah tertinggal dari kondisi terbaru:
  - Absensi kunjungan perpustakaan menjadi selesai.
  - Storage file digital non-public menjadi selesai.
  - Enforcement kebijakan akses reader menjadi selesai.
  - Upload PDF admin menjadi selesai.
  - Reader halaman/token menjadi selesai.
  - Watermark dinamis menjadi selesai.
  - Rate limit dan audit akses reader menjadi selesai.
  - Opsi download hanya untuk koleksi berizin menjadi selesai.
  - QR/GPS lock dan check-in Pojok Baca menjadi selesai.
  - Penerbitan token/check-in dari UI menjadi selesai.
  - Security review PDF reader dan akses file menjadi selesai untuk tahap implementasi lokal.
  - Laporan operasional dan export menjadi selesai.
- Menambahkan bagian `Review Status 2026-08-03` di `docs/ROADMAP.md`.

Ringkasan yang sudah selesai dan tervalidasi lokal:

- Fondasi CI3, login database, session 3 hari, RBAC, sidebar database, dan admin panel.
- Landing publik, katalog publik, dashboard member, kartu digital, pendaftaran online, perpanjangan membership, blokir/aktif kartu.
- GIS perpustakaan, master wilayah, Pojok Baca, map picker draggable, token/kuota, monitoring token.
- Migrasi katalog, eksemplar, member, foto, cover, file digital, dan transaksi harian dari INLISLite lokal.
- Katalog admin/publik dengan filter, kategori isi, klasifikasi isi, detail, request buku, CRUD buku, dan CRUD eksemplar.
- Buku tamu/layanan harian, monitor QR dinamis, pencarian AJAX member, kunjungan rombongan, dan laporan kunjungan.
- Reader aman non-downloadable:
  - PDF asli tidak public,
  - stream PDF utuh ditolak untuk aset terkunci,
  - render per halaman PNG,
  - watermark tertanam,
  - token sesi,
  - rate limit,
  - audit log,
  - gesture tap/swipe/keyboard.
- Manajemen ebook admin dengan upload PDF, hak publikasi, policy akses, status aset, preview aman, dan audit detail.

Yang masih belum selesai:

- ERD final aplikasi baru.
- Strategi sinkronisasi final INLISLite produksi.
- Job sinkronisasi ulang terjadwal dengan diff log perubahan.
- Event literasi lengkap: CRUD final, pendaftaran, QR attendance, dokumentasi, sertifikat/laporan.
- Galeri/foto Pojok Baca, koleksi khusus per titik, dashboard pemanfaatan lanjutan.
- Deteksi anomali reader yang lebih cerdas.
- Uji lengkap role admin lokal/sekolah/desa/swasta.
- CSRF aktif dan penyesuaian seluruh form.
- Backup/restore database.
- Monitoring error aplikasi.
- Optimasi performa search skala produksi.
- SOP operator, pelatihan admin, pilot terbatas, dan rilis bertahap.

Catatan:

- Git push tetap tidak dilakukan otomatis.

## 2026-08-03 01:35 WIB

Status: alur katalog buku dan ebook disinkronkan secara UI dan konsep data.

Keputusan konsep:

- `books` adalah data induk bibliografi untuk semua buku.
- Tidak semua data di `books` adalah ebook.
- Ebook/PDF adalah aset digital opsional yang menempel ke buku melalui `digital_assets.book_id`.
- Setiap ebook wajib punya buku induk di katalog.

Yang dilakukan:

- Detail katalog `/catalog/detail/{book_id}` sekarang menampilkan panel `Ebook / Aset Digital`.
- Panel tersebut menampilkan aset PDF yang terhubung dengan buku:
  - nama file,
  - policy akses,
  - status download terkunci/boleh,
  - dasar hak publikasi,
  - pemegang hak,
  - status aset,
  - aksi baca/edit.
- Tombol `Tambah Ebook` di detail katalog mengarah ke:
  - `/reader/assets/create?book_id={book_id}`.
- Form tambah ebook `/reader/assets/create?book_id={book_id}` otomatis memilih buku induk tersebut.
- Form `/catalog/create` diberi catatan bahwa katalog adalah data induk buku, sedangkan ebook/PDF ditambahkan setelah katalog tersimpan.
- `Catalog_model` ditambah `get_book_digital_assets($book_id)`.
- `Reader_model` ditambah `book_option($id)` agar preselect buku tetap aman walaupun buku tidak masuk daftar opsi awal.

Validasi:

- Lint PHP bersih untuk:
  - `Catalog_model`,
  - `Catalog`,
  - `catalog/detail`,
  - `Reader_model`,
  - `Reader`,
  - `reader/form`,
  - `catalog/form`.
- Login `superadmin` / `admin123`.
- `/catalog/detail/14100` status 200, memuat panel `Ebook / Aset Digital`, dan link `reader/assets/create?book_id=14100`.
- `/reader/assets/create?book_id=14100` status 200, memuat `Pride and Prejudice`, dan opsi buku `14100` terpilih.
- `/catalog/create` status 200 dan memuat catatan `Katalog adalah data induk buku`.
- Push Git tidak dilakukan otomatis.
