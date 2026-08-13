# Progress Proyek

## 2026-08-10 — Master wilayah nasional dan alamat member

- Master wilayah diperluas menjadi provinsi, kabupaten/kota, kecamatan, serta desa/kelurahan dengan kode administrasi nasional.
- Importer `tools/import_wilayah_kemendagri_2025.php` membaca dataset Kepmendagri 2025 secara idempoten; dataset XLSX tidak disimpan di Git.
- Form pendaftaran membedakan alur warga Rembang (satu alamat berbasis kecamatan/desa Rembang) dan luar Rembang (alamat KTP serta domisili, masing-masing berjenjang nasional).
- Request pendaftaran dan data member menyimpan referensi wilayah serta label alamat identitas dan domisili.

## 2026-08-10

Status: impor koleksi buku ajar digital dari direktori server selesai.

Yang sudah dilakukan:

- Menginventaris `/www/wwwroot/Buku`: 613 PDF dengan total 10.339.560.203 byte.
- Memvalidasi signature semua sumber; tidak ada file non-PDF atau rusak pada pemeriksaan impor.
- Menyalin seluruh PDF ke storage nonpublik aplikasi: `storage/ebooks/kemendikdasmen-2026`.
- Menambahkan 613 bibliografi berstatus `published` dan 613 `digital_assets` berstatus `active` dengan sumber `buku_kemendikdasmen_2026`.
- Menetapkan akses `online_only` dan `is_downloadable = 0` pada seluruh aset, sehingga pembacaan hanya melalui Reader aman, bukan URL PDF langsung.
- Mengisi kategori kurasi: 520 `Non Fiksi` dan 93 `Anak dan Remaja`.
- Mengisi klasifikasi DDC berdasarkan pokok judul: 000 (26), 200 (168), 300 (112), 400 (54), 500 (62), 600 (102), 700 (79), dan 900 (10).
- Membuat importer idempoten `tools/import_buku_digital.php`; script dapat dijalankan ulang tanpa membuat duplikat karena memakai SHA-1 path relatif sebagai identitas sumber.

Validasi:

- Database dan storage sama-sama berisi 613 berkas/aset; seluruh ukuran file cocok dan semua signature tetap `%PDF`.
- Katalog produksi `https://pustaka.rembangkab.go.id/katalog` menampilkan hasil impor dan tombol `Baca Online`.
- Akses langsung ke path `storage/ebooks/...` ditolak server dengan HTTP 403.

Catatan hak publikasi:

- Metadata hak semua aset ditandai `unknown` dan diberi catatan verifikasi admin, karena folder sumber tidak menyertakan bukti lisensi per judul. Status ini perlu diperbarui setelah dokumen atau kebijakan publikasinya tersedia.

Penyempurnaan tampilan sesudah impor:

- Rak `Buku Digital` pada dashboard pemustaka sekarang mengurutkan aset terbaru lebih dahulu, sehingga koleksi impor muncul sebelum sampel lama.
- Filter admin `/catalog` dilengkapi sumber data, kategori sumber, media, aturan, lokasi perpustakaan, dan akses buku digital.
- Filter katalog publik mendukung `Buku digital` dan tautan dashboard mengarah ke filter tersebut.
- Cover fallback bersama tersedia di `assets/img/book-cover-default.webp`; dipakai otomatis oleh katalog publik, detail, dashboard, landing, dan katalog admin untuk data tanpa cover individual.

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
- Login akun superadmin seed berhasil.
- Setelah login, URL berikut status 200: `welcome/index`, `libraries`, `catalog`, `members`, `reading-points`, `events`, `users`, `roles`, dan `sidebar/manage`.
- Semua URL tersebut memuat layout Pustaka dan tombol logout.
- Setelah logout, akses root kembali ke halaman login dan dashboard tidak tampil.

Catatan:

- Password default seed lama wajib diganti sebelum aplikasi dipakai serius.
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

Status: root publik, redirect login per role, seed user awal, dan diagnosa Git selesai.

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
- Menambahkan seed user awal untuk `SUPERADMIN`, `ADMIN`, dan `USER`.
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
- HTTP login akun superadmin seed ke `/admin` status 200 dan sidebar terdeteksi.
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
- Login admin seed, lalu buka `/`:
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
- Login akun superadmin seed.
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
- Login akun superadmin seed.
- `/catalog/detail/14100` status 200, memuat panel `Ebook / Aset Digital`, dan link `reader/assets/create?book_id=14100`.
- `/reader/assets/create?book_id=14100` status 200, memuat `Pride and Prejudice`, dan opsi buku `14100` terpilih.
- `/catalog/create` status 200 dan memuat catatan `Katalog adalah data induk buku`.
- Push Git tidak dilakukan otomatis.

## 2026-08-07 00:00 WIB

Status: ERD aplikasi dan SOP sinkronisasi INLISLite produksi selesai dicatat.

Yang dilakukan:

- Membuat `docs/ERD.md` sebagai pegangan relasi data utama aplikasi `pustaka`.
- Membuat `docs/INLISLITE_SYNC_SOP.md` sebagai standar sync INLISLite produksi ke `pustaka`.
- Memperbarui `docs/ROADMAP.md`:
  - checklist ERD aplikasi selesai,
  - strategi sync INLISLite selesai,
  - SOP import/sinkronisasi INLISLite selesai,
  - sisa pekerjaan dipisahkan sebagai implementasi job CLI/scheduler, reconcile report, backup/restore, monitoring error, dan optimasi search.
- Memperbarui `docs/HANDOVER.md` agar developer berikutnya tahu dokumen utama, keputusan sync, urutan sync, dan field lokal yang tidak boleh ditimpa.

Keputusan teknis:

- Pola utama sync produksi untuk pilot: dump periodik INLISLite ke staging `inlislite_v3`.
- Alternatif setelah infrastruktur matang: read-only replica.
- Pustaka tidak menulis balik ke database INLISLite.
- Sync harus idempotent:
  - import baru membuat data yang belum ada,
  - update lama hanya menyentuh field source-owned,
  - data yang sudah mapped tidak boleh dobel.
- Field local-owned tidak boleh ditimpa sync, terutama:
  - kategori isi dan klasifikasi isi,
  - aset ebook/PDF dan hak publikasi,
  - password dan status akun lokal,
  - status kartu digital,
  - pendaftaran online,
  - request buku,
  - perpanjangan membership,
  - Pojok Baca, token, sesi baca, dan audit reader.

Catatan:

- Tidak ada perubahan kode aplikasi pada langkah ini.
- Ada perubahan Git yang sudah ada sebelumnya di modul learn/quiz dan tidak disentuh.
- Push Git tidak dilakukan otomatis.

## 2026-08-07 01:20 WIB

Status: Event Literasi tahap operasional selesai untuk CRUD, form dinamis, pendaftaran publik, tiket digital, dan QR attendance.

Yang dilakukan:

- Menambahkan migration `sql/2026-08-07d_literacy_events_full_module.sql` dan menjalankannya ke database `pustaka`.
- Menambah tabel `event_categories`, `event_form_fields`, `event_registration_answers`, dan `event_photos`.
- Memperluas `literacy_events` dengan kategori, slug, ringkasan, poster, penyelenggara, narasumber, target peserta, mode lokasi, link online, mode pendaftaran, approval manual/otomatis, periode pendaftaran, QR attendance flag, dan sertifikat flag.
- Memperluas `event_registrations` dengan kode pendaftaran, tipe peserta, instansi, jumlah orang, ticket token, attendance token, status pending/registered/approved/rejected/attended/cancelled, admin note, approved metadata, dan check-in metadata.
- Mengisi 10 kategori event awal: Bedah Buku, Storytelling Anak, Literasi Digital, Pelatihan Menulis, Sejarah Lokal, Lomba Literasi, Kunjungan Sekolah, Bimbingan Perpustakaan, Webinar, dan Komunitas Baca.
- Mengganti `Event_model` menjadi model operasional penuh untuk list/filter, CRUD, builder form, validasi jawaban dinamis, pendaftaran publik, validasi kuota, tiket, QR attendance, dan update status peserta.
- Mengganti controller admin `Events` untuk route `/events`, `/events/create`, `/events/detail/{id}`, builder field form, status event, update peserta, dan QR check-in.
- Menambahkan controller publik `Agenda` untuk `/agenda`, `/agenda/detail/{event_id}`, `/agenda/register/{event_id}`, dan `/agenda/ticket/{ticket_token}`.
- Membuat view admin `events/index.php`, `events/form.php`, dan `events/detail.php`.
- Membuat view publik `agenda/index.php`, `agenda/detail.php`, dan `agenda/ticket.php`.
- Menambahkan link `Agenda` ke navigasi publik utama.
- Menambahkan antrean event pending ke Kotak Masuk admin.
- Menambahkan CSS event/tiket di `assets/css/pustaka-polish.css`.
- Memperbarui cache-buster CSS modul utama menjadi `v=20260807d`.
- Memperbarui `docs/ROADMAP.md`, `docs/HANDOVER.md`, dan `docs/ERD.md`.

Validasi:

- Migration berhasil dijalankan.
- Kategori event: 10.
- Form field event setelah cleanup test: 0.
- Registrasi event setelah cleanup test: 0.
- Lint PHP bersih untuk `Event_model`, `Events`, `Agenda`, dan semua view event/agenda baru.
- Smoke test HTTP:
  - `/agenda` status 200,
  - `/events` sebagai `superadmin` status 200,
  - `/events/create` sebagai `superadmin` status 200.
- Smoke test alur dinamis:
  - create event via UI,
  - tambah field wajib `Asal Instansi`,
  - `/agenda/detail/{id}` memuat field tersebut,
  - submit pendaftaran publik berhasil,
  - 1 registration tersimpan,
  - 1 jawaban dinamis tersimpan,
  - data smoke test dibersihkan dan `smoke_left = 0`.

Sisa Event Literasi:

- UI upload/dokumentasi foto event dari tabel `event_photos`.
- Sertifikat digital.
- Laporan event dan export.
- Mode scan QR yang lebih nyaman untuk kamera petugas.

Catatan:

- Modul arena belajar/quiz dianggap pekerjaan paralel dan tidak menjadi fokus pekerjaan ini.
- Ada perubahan Git lama pada modul arena belajar/quiz yang tidak disentuh secara fungsional oleh pekerjaan Event Literasi.
- Push Git tidak dilakukan otomatis.

## 2026-08-08 00:30 WIB

Status: revisi form Event Literasi dan Jam Layanan Perpustakaan selesai.

Yang dilakukan:

- Menambahkan kemampuan tambah kategori event langsung dari form `/events/create` dan `/events/edit/{id}` melalui modal `Tambah Kategori Event`.
- Menambahkan route `POST /events/categories/store`.
- Menambahkan method `Event_model::create_category()` dengan slug unik otomatis.
- Mengganti input jadwal event dari `datetime-local` menjadi input tanggal dan jam terpisah:
  - `starts_date` + `starts_time`,
  - `ends_date` + `ends_time`,
  - `registration_opens_date` + `registration_opens_time`,
  - `registration_closes_date` + `registration_closes_time`.
- Controller `Events` sekarang menyatukan tanggal dan jam sebelum dikirim ke model, sehingga error browser `Please enter valid time` tidak lagi muncul karena format lokal/locale.
- Menambahkan peta Leaflet di bagian `Jadwal dan Lokasi` event.
- Jika `Perpustakaan Penyelenggara` dipilih dan punya titik GIS, latitude/longitude event otomatis mengikuti titik perpustakaan tersebut.
- Jika perpustakaan belum punya titik GIS atau event berada di lokasi berbeda, admin bisa klik/drag pin peta untuk menentukan koordinat.
- Form `/libraries/create` dan `/libraries/edit/{id}` mengganti `Jam Layanan` dari input manual menjadi checklist hari plus jam buka/tutup.
- Nilai checklist jam layanan otomatis diringkas ke field `opening_hours`, misalnya `Senin 08:00-15:00; Selasa 08:00-15:00`.
- Menambahkan styling untuk peta event dan checklist jam layanan di `assets/css/pustaka-polish.css`.
- Cache-buster admin layout dinaikkan ke `pustaka-polish.css?v=20260808a`.

Validasi:

- Lint PHP bersih untuk:
  - `application/views/events/form.php`,
  - `application/controllers/Events.php`,
  - `application/models/Event_model.php`,
  - `application/config/routes.php`,
  - `application/views/libraries/form.php`.
- HTTP smoke test login akun superadmin seed berhasil.
- `/events/create` memuat:
  - modal tambah kategori,
  - peta lokasi event,
  - input tanggal/jam terpisah,
  - route `events/categories/store`.
- `/libraries/create` memuat checklist `library-hours-grid`.
- Submit kategori event uji berhasil membuat row `Smoke Kategori 20260808`.
- Submit event uji berhasil menyimpan:
  - `starts_at = 2026-08-10 00:00:00`,
  - `ends_at = 2026-08-10 02:00:00`,
  - `registration_opens_at = 2026-08-08 00:00:00`,
  - `registration_closes_at = 2026-08-09 23:59:00`,
  - latitude/longitude dari titik perpustakaan.
- Data smoke test kategori dan event sudah dibersihkan.
- Browser plugin untuk verifikasi visual tidak bisa dipakai pada sesi ini karena tool mengembalikan error metadata sandbox; validasi fungsional dilakukan lewat lint dan HTTP smoke test.
- Push Git tidak dilakukan otomatis.

## 2026-08-08 01:20 WIB

Status: integrasi Event Literasi ke admin detail, dashboard member, dan form pendaftaran member selesai.

Yang dilakukan:

- `/events/detail/{id}` sekarang punya panel `QR Attendance`.
- Tabel peserta event di admin detail sekarang punya tombol:
  - `QR` untuk membuka modal QR check-in peserta,
  - `Tiket` untuk membuka tiket digital peserta.
- Modal QR admin memakai URL `events/checkin/{attendance_token}` dan tetap membutuhkan akun admin dengan hak verifikasi event.
- `/user/dashboard` sekarang menampilkan kartu `Event Literasi` berisi event yang akan datang.
- Dashboard member menampilkan badge jumlah agenda aktif pada tombol `Agenda`.
- Kartu event dashboard member menampilkan:
  - tanggal/jam,
  - lokasi,
  - kuota/pendaftar,
  - tombol daftar/detail,
  - status dan tombol tiket jika member sudah terdaftar.
- Navigasi dashboard member ditambah link `Agenda` di desktop dan mobile.
- Form `/agenda/detail/{event_id}` sekarang lebih sadar member login:
  - participant type dikunci sebagai `member` via hidden input,
  - nama, nomor HP, email, dan instansi/pekerjaan diisi dari data member jika tersedia,
  - muncul pesan bahwa data member otomatis dipakai,
  - field tambahan event otomatis diprefill jika `field_key` atau label cocok dengan data member seperti NIK, nomor anggota, alamat, desa, kecamatan, pendidikan, pekerjaan, dan lainnya.
- Menambahkan `Event_model::get_dashboard_events()` untuk data dashboard member beserta status pendaftaran member.
- Cache-buster CSS halaman terkait dinaikkan ke `pustaka-polish.css?v=20260808b`.

Validasi:

- Lint PHP bersih untuk:
  - `Event_model`,
  - `User_dashboard`,
  - `Agenda`,
  - `user/dashboard`,
  - `agenda/detail`,
  - `events/detail`.
- HTTP smoke test admin:
  - login akun superadmin seed,
  - `/events/detail/3` memuat `QR Attendance`,
  - tombol/modal QR peserta muncul saat ada registrasi,
  - link tiket peserta muncul.
- HTTP smoke test member:
  - login `3317101401620001` / `perpus2026`,
  - `/user/dashboard` memuat panel event,
  - event publik `AAAAA` tampil di dashboard,
  - label agenda aktif tampil.
- Smoke test auto-fill member:
  - menambahkan field dinamis sementara `NIK` pada event,
  - `/agenda/detail/3` memuat pesan `Data member otomatis dipakai`,
  - nama member terisi,
  - field dinamis `NIK` terisi `3317101401620001`,
  - field uji dibersihkan.
- Data registrasi smoke test QR dan field smoke test sudah dibersihkan.
- Browser plugin masih gagal dipakai karena error metadata sandbox `sandboxCwd must use the file URI scheme`; validasi visual dilakukan lewat HTTP/HTML smoke test.
- Push Git tidak dilakukan otomatis.

## 2026-08-09 00:30 WIB

Status: modul QR Event untuk pengumuman dan pendaftaran publik selesai.

Yang dilakukan:

- Menambahkan route admin `events/qr`.
- Menambahkan halaman admin `application/views/events/qr.php`.
- Menambahkan registry RBAC dan sidebar melalui `sql/2026-08-09a_event_qr_announcement_module.sql`.
- Nama modul dipastikan menjadi `QR Event`.
- Menu sidebar baru:
  - parent: `Jejaring & Agenda`,
  - menu: `QR Event`,
  - route: `events/qr`,
  - permission: `events.qr`.
- Permission `events.qr` hanya untuk role `SUPERADMIN` dan `ADMIN`; role `USER` tidak punya akses admin ke modul ini.
- QR Event mengarah ke halaman pendaftaran publik:
  - `agenda/detail/{event_id}?from=qr-event#daftar-event`.
- Halaman QR Event menyediakan:
  - filter event,
  - daftar event,
  - status kesiapan pendaftaran,
  - link QR yang bisa dicopy,
  - tombol unduh QR PNG,
  - tombol cetak,
  - kartu pengumuman siap print dengan logo, judul event, jadwal, lokasi, kuota, mode pendaftaran, dan QR.
- Tombol akses ditambahkan di:
  - `/events`,
  - `/events/detail/{id}`.
- CSS QR Event dan mode print ditambahkan di `assets/css/pustaka-polish.css`.
- Cache-buster admin dinaikkan menjadi `pustaka-polish.css?v=20260809b`.

Validasi:

- Migrasi SQL berhasil dijalankan ke database lokal `pustaka`.
- Database:
  - `sys_page.events.qr` berjudul `QR Event & Pendaftaran`,
  - `sys_menu.events-qr` berjudul `QR Event`,
  - permission hanya ada untuk `SUPERADMIN` dan `ADMIN`.
- Tidak ada sisa istilah lama pada kode aplikasi, SQL, docs, dan CSS terkait.
- Lint PHP bersih untuk:
  - `Events.php`,
  - `events/qr.php`,
  - `events/index.php`,
  - `events/detail.php`,
  - `routes.php`.
- HTTP smoke test:
  - admin seed dapat membuka `/events/qr?event_id=3`,
  - halaman memuat `QR Event`,
  - halaman tidak memuat istilah lama,
  - URL QR memakai `from=qr-event`,
  - target QR `event-registration-qr` dan kartu print tampil,
  - member `3317101401620001` / `perpus2026` mendapat HTTP 403 saat membuka `/events/qr`.
- Push Git tidak dilakukan otomatis.

## 2026-08-09 20:08 WIB

Status: import database acuan INLISLite lokal selesai.

Yang dilakukan:

- Mengimpor dump `C:\xampp\htdocs\inlislite3\inlis.sql` ke database MySQL lokal `inlislite_v3`.
- Nama database yang aktif di MySQL adalah `inlislite_v3` tanpa huruf `t` tambahan.
- Backup database sebelum import dibuat di `C:\xampp\htdocs\pustaka\db_backup\inlislite_v3_before_import_20260809_200621.sql`.
- Log import dibuat di `C:\xampp\htdocs\pustaka\db_backup\inlislite_v3_import_20260809_200621.log`.

Validasi:

- Import via CLI MySQL selesai dengan exit code `0`.
- Log import kosong, tidak ada error.
- Jumlah tabel setelah import: `201`.
- Data utama terisi:
  - `catalogs`: `14098`,
  - `members`: `5504`,
  - `collections`: `22927`,
  - `memberguesses`: `45543`,
  - `collectionloans`: `31678`,
  - `collectionloanitems`: `2392`.

## 2026-08-09 21:42 WIB

Status: hotfix import database server untuk modul quiz selesai.

Masalah:

- Import database `pustaka` di server gagal pada tabel `quiz_import_batches`.
- Penyebabnya adalah kolom `format` bertipe `ENUM('csv','xlsx','json')`, tetapi ada data batch import dari file `.txt` yang tersimpan sebagai nilai kosong.
- MySQL/MariaDB lokal menerima nilai enum kosong saat mode tidak strict, sedangkan server menolak dengan error `1265 - Data truncated for column 'format'`.
- Error lanjutan `Table 'pustaka.quiz_questions' doesn't exist` muncul karena proses import sudah berhenti/berantakan setelah error pertama.

Yang dilakukan:

- Menambahkan `txt` sebagai nilai valid enum `quiz_import_batches.format`.
- Membetulkan data lokal `MTK_KELAS_2.txt` dari format kosong menjadi `txt`.
- Menambahkan patch SQL `sql/2026-08-09b_quiz_import_txt_format_fix.sql`.
- Menyesuaikan referensi schema di `docs/quiz_engine.sql`.
- Membuat dump server-ready baru:
  - `C:\xampp\htdocs\pustaka\db_backup\pustaka_server_ready_20260809_214200.sql`.

Validasi:

- Patch SQL berhasil dieksekusi di database lokal `pustaka`.
- `quiz_import_batches.format` sekarang `ENUM('csv','xlsx','json','txt')`.
- Tidak ada lagi nilai enum kosong pada `quiz_import_batches`.
- `quiz_questions` ada dan berisi `25` data.

## 2026-08-09 22:20 WIB

Status: dependensi tampilan umum ke database INLISLite diputus.

Prinsip:

- Halaman operasional dan publik harus membaca dari database aplikasi `pustaka`.
- Koneksi/database `inlislite_v3` hanya boleh dipakai di halaman sinkronisasi/migrasi yang memang bertugas menarik data sumber.

Yang dilakukan:

- `Home` tidak lagi membuka koneksi `inlislite`; statistik landing membaca `books`, `book_items`, dan `members` dari `pustaka`.
- `Admin` dan `Welcome` tidak lagi membuka koneksi `inlislite`; dashboard admin memakai statistik lokal `pustaka`.
- `/catalog` tidak lagi memanggil `Catalog_model::source_stats()`; tab ringkasan menjadi `Data Lokal`.
- `/members` tidak lagi memanggil `Member_model::source_stats()`; tab ringkasan menjadi `Data Lokal`.
- `Asset_migration_model::process_covers()` tidak lagi join ke `inlislite_v3.catalogs` dan `inlislite_v3.worksheets`; migrasi cover memakai data lokal `books.cover_path` lalu mencari file fisik di folder sumber.
- Label tampilan umum dibersihkan:
  - `Katalog INLISLite` menjadi `Katalog Buku`,
  - `Live dari INLISLite` menjadi `Data layanan terpadu`,
  - `Status INLISLite` menjadi `Status Sumber`,
  - `Aktivitas INLISLite` menjadi `Aktivitas Layanan`,
  - `Buku Tamu INLISLite` menjadi `Buku Tamu Legacy`.

Validasi:

- Lint PHP bersih untuk controller/model/view yang diubah.
- HTTP smoke test:
  - `/` status `200`,
  - login akun superadmin seed berhasil redirect `303`,
  - `/admin`, `/catalog`, `/members`, `/transactions` status `200`.
- Halaman admin umum tidak lagi memuat teks visible lama:
  - `Live dari INLISLite`,
  - `Katalog INLISLite`,
  - `Status INLISLite`,
  - `Aktivitas INLISLite`,
  - `Sumber Migrasi`,
  - `Buku Tamu INLISLite`.
- Audit kode:
  - pemanggilan `source_stats()` tersisa hanya di `/catalog/sync`, `/members/sync`, dan `/transactions/sync`,
  - koneksi `load->database('inlislite')` tersisa hanya di model sinkronisasi.

Catatan:

- URL aset hasil migrasi masih mengandung path `assets/uploads/inlislite/...`; ini hanya nama folder aset lokal hasil salin, bukan pembacaan database INLISLite.
- Rename folder aset bisa dilakukan belakangan jika ingin steril sampai level URL/network path.

## 2026-08-09 23:10 WIB

Status: hardening awal server aaPanel/Nginx dan dashboard production selesai.

Yang dilakukan:

- Menambahkan contoh rewrite Nginx aaPanel:
  - `deploy/nginx/aapanel-codeigniter3-rewrite.conf`,
  - `docs/NGINX_AAPANEL.md`.
- Memperkuat `.htaccess` Apache lokal agar folder privat tidak bisa dibuka langsung:
  - `application`,
  - `system`,
  - `storage`,
  - `db_backup`,
  - `Buku`,
  - `docs`,
  - `sql`,
  - `scripts`,
  - `deploy`.
- Menghapus petunjuk kredensial default dari halaman login.
- Membersihkan dokumentasi aktif dari password seed lama agar tidak ikut terbawa ke deployment.
- Merombak dashboard admin menjadi dashboard operasional produksi:
  - kotak masuk layanan,
  - statistik katalog, member, GIS, dan aset digital,
  - aktivitas hari ini,
  - quick action admin,
  - kesehatan sinkronisasi.
- Menghapus objek penanda internal dari dashboard, termasuk panel ringkasan eksperimen dan label fase/roadmap yang tidak cocok untuk production.
- Menaikkan cache-buster admin polish CSS ke `20260809c`.
- Menambahkan patch SQL server:
  - `sql/2026-08-09c_production_admin_cleanup_and_inlislite_users.sql`.
- Patch SQL tersebut:
  - menambah kolom penanda sumber pada `auth_user`,
  - menambah unique index sumber,
  - mengimpor user aktif dari `inlislite_v3.users` sebagai role `ADMIN`,
  - mengikat admin lama ke perpustakaan utama lokal jika ditemukan,
  - memakai password awal admin import sesuai standar operasional terbaru dan `force_password_change = 1`,
  - mensuspensi akun demo generik `admin` dan `pemustaka`.

Validasi:

- SQL patch berhasil dijalankan lokal dan aman dijalankan ulang.
- User aktif INLISLite yang berhasil masuk sebagai admin: `16`.
- User INLISLite nonaktif tidak ikut dimigrasikan.
- Akun demo `admin` dan `pemustaka` lokal berstatus `suspended`.
- Lint PHP bersih untuk:
  - `Admin.php`,
  - `Welcome.php`,
  - `auth/login.php`,
  - `dashboard/index.php`.
- HTTP smoke test:
  - login `superadmin` redirect `303`,
  - `/admin`, `/catalog`, `/members`, `/transactions`, `/guestbook/monitor`, `/reports/visits` status `200`.
- Marker production yang harus hilang tidak muncul di HTML halaman admin, termasuk panel eksperimen, petunjuk kredensial lama, label fase, dan prioritas roadmap internal.
- Folder privat lokal sudah ditolak:
  - `/application/config/database.php` -> `403`,
  - `/system/core/CodeIgniter.php` -> `403`,
  - `/storage/...` -> `403`,
  - `/sql/...` -> `403`,
  - `/docs/...` -> `403`,
  - `/deploy/...` -> `403`.

Catatan:

- Untuk Nginx, `.htaccess` tidak berlaku; rewrite aaPanel harus memakai snippet di `docs/NGINX_AAPANEL.md`.
- Modul Arena Belajar tetap tidak disentuh karena merupakan pekerjaan paralel.

## 2026-08-09 23:45 WIB

Status: daftar admin khusus dan standar tabel admin selesai.

Yang dilakukan:

- Membuat route khusus `rbac/admins` untuk daftar admin operasional.
- Route lama `rbac/users` tetap aktif sebagai alias agar link lama tidak 404.
- Mengubah tab RBAC dari `User` menjadi `Daftar Admin`.
- Memisahkan akun admin dari akun member:
  - daftar admin hanya mengambil akun dengan role selain `USER`,
  - akun member hasil migrasi tidak ikut dirender di daftar admin.
- Menambahkan filter pada daftar admin:
  - pencarian nama/username/email/perpustakaan,
  - status,
  - role,
  - scope perpustakaan,
  - sumber lokal atau admin INLISLite,
  - jumlah baris.
- Default tabel admin memakai `25` baris, pagination, dan area scrollable.
- Menambahkan CSS global tabel admin:
  - filter lebih padat,
  - header tabel sticky,
  - area tabel scrollable,
  - footer pagination responsif.
- Mengubah menu database `system.users` menjadi `Daftar Admin` dengan URL `rbac/admins`.
- Mengganti password awal admin hasil import INLISLite sesuai permintaan dan tetap mewajibkan `force_password_change = 1`.
- Menambahkan patch SQL server:
  - `sql/2026-08-09d_admin_accounts_password_and_menu.sql`.
- Menaikkan cache-buster polish CSS ke `20260809d`.

Validasi:

- SQL patch `2026-08-09d_admin_accounts_password_and_menu.sql` berhasil dijalankan lokal.
- Password baru admin import terverifikasi cocok pada sampel user INLISLite.
- Total akun admin yang tampil dari query admin: `18`.
- Akun member terhubung yang ikut query admin: `0`.
- Login salah satu admin INLISLite dengan password baru berhasil dan dapat membuka `/admin`.
- HTTP smoke test:
  - `/rbac/admins` status `200`,
  - `/rbac/users` status `200`,
  - `/rbac/admins?per_page=25&source=inlislite_admin` status `200`,
  - `/rbac/admins?role_id=2&status=active` status `200`.
- Lint PHP bersih untuk:
  - `User_model.php`,
  - `Rbac.php`,
  - `Users.php`,
  - `routes.php`,
  - view RBAC admin dan modalnya.

## 2026-08-10 00:20 WIB

Status: pengaturan akun pemustaka selesai.

Yang dilakukan:

- Memastikan akun member tetap menjadi bagian dari modul Membership:
  - data profil di `members`,
  - akun login di `auth_user`,
  - relasi melalui `members.auth_user_id`.
- Menambahkan route pemustaka:
  - `/user/account`,
  - `/user/account/username`,
  - `/user/account/password`.
- Menambahkan halaman `/user/account` untuk ganti username dan password.
- Jika akun member masih `force_password_change = 1`, login member diarahkan ke `/user/account`.
- Menambahkan link `Akun` di:
  - topbar `/user/dashboard`,
  - bottom navigation member,
  - quick action dashboard,
  - halaman `/user/reading-checkin`.
- Update username wajib memasukkan password saat ini, username unik, dan format username dibatasi huruf/angka/titik/underscore/strip.
- Update password wajib memasukkan password saat ini, password baru minimal 8 karakter, dan konfirmasi cocok.
- Setelah password berhasil diganti, `auth_user.force_password_change` diset `0`.
- Tidak ada perubahan struktur database sehingga tidak perlu patch SQL baru.

Validasi:

- Lint PHP bersih untuk:
  - `Auth_model.php`,
  - `User_dashboard.php`,
  - `routes.php`,
  - `user/account.php`,
  - `user/dashboard.php`,
  - `user/reading_checkin.php`.
- Akun member contoh `3317101401620001` ditemukan aktif dan terhubung ke member.
- Total akun member terhubung: `5.504`.
- HTTP smoke test login member berhasil redirect `303`.
- Login member dengan password awal diarahkan ke `/user/account`.
- `/user/dashboard`, `/user/account`, dan `/user/reading-checkin` status `200`.
- Halaman `/user/account` memuat form ganti username dan form ganti password.
- Test negatif update username dengan password salah tidak mengubah username.

## 2026-08-10 01:45 WIB

Status: dashboard pemustaka dipoles, highlight katalog admin dibuat, dan alur GPS reader disederhanakan.

Yang dilakukan:

- Menambahkan modul admin `/catalog/highlights`.
- Menambahkan tabel `catalog_highlights` untuk mengatur:
  - highlight buku,
  - highlight kategori katalog,
  - label/jenis highlight,
  - judul tampilan opsional,
  - ringkasan,
  - urutan,
  - periode tayang,
  - status aktif/nonaktif.
- Menambahkan registry RBAC dan sidebar:
  - page code `catalog.highlights`,
  - menu `Highlight Katalog` di bawah `Koleksi & Katalog`,
  - permission penuh untuk `SUPERADMIN` dan `ADMIN`.
- Menambahkan tombol `Highlight` dari `/catalog`.
- Merombak `/user/dashboard`:
  - section `Pilihan Pustakawan untuk kamu`,
  - card buku/katalog pilihan,
  - panel `Jelajahi Kategori`,
  - fallback kategori populer dari katalog jika admin belum mengatur highlight kategori,
  - pesan token baca luar zona yang lebih jelas.
- Menyederhanakan reader `location_only`:
  - member cukup klik `Baca Online`, lalu nyalakan GPS,
  - sistem otomatis mendeteksi apakah lokasi ada di radius Pojok Baca/perpustakaan,
  - akses dalam zona resmi tidak wajib token aktif dan tidak mengurangi kuota,
  - akses luar zona wajib token aktif dan mengurangi kuota,
  - pesan token habis ditampilkan langsung di halaman validasi reader.
- Menambahkan `reading_point_id`/`library_id` dari hasil deteksi GPS ke konteks sesi baca dan kunjungan digital.
- Memperbarui nav reader member agar konsisten dengan halaman member lain.
- Menaikkan cache-buster polish CSS ke `20260810b`.
- Menambahkan polish CSS untuk:
  - dashboard member,
  - card highlight buku,
  - panel kategori,
  - validasi GPS reader.
- Menambahkan patch SQL server:
  - `sql/2026-08-10a_catalog_highlights_reader_gps.sql`.
- Memperbarui dokumentasi:
  - `docs/ERD.md`,
  - `docs/POJOK_BACA_TOKEN_SOP.md`,
  - `docs/HANDOVER.md`.

Validasi:

- SQL patch berhasil dijalankan lokal.
- SQL patch dijalankan ulang dan tetap idempotent:
  - `catalog.highlights` page: `1`,
  - `catalog.highlights` menu: `1`,
  - permission role: `2`.
- Lint PHP bersih untuk:
  - `Catalog.php`,
  - `Reader.php`,
  - `User_dashboard.php`,
  - `Catalog_model.php`,
  - `Reading_point_model.php`,
  - `Reader_model.php`,
  - `Visit_model.php`,
  - `catalog/highlights.php`,
  - `user/dashboard.php`,
  - `reader/location_gate.php`,
  - `reader/member_read.php`,
  - `layouts/tabler.php`,
  - `routes.php`.
- HTTP smoke test:
  - login `superadmin` lokal berhasil membuka `/catalog/highlights`,
  - `/catalog/highlights` status `200` dan memuat `Daftar Highlight`,
  - create/delete highlight test berhasil dan data test bersih lagi,
  - login member contoh berhasil,
  - `/user/dashboard` status `200` dan memuat `Pilihan Pustakawan`, `Jelajahi Kategori`, serta `Token luar zona`,
  - `/reader/read/4` tanpa GPS menampilkan gate `Nyalakan GPS untuk mulai baca`,
  - `/reader/read/4?lat=-6.7513701&lng=111.4334398` terbuka sebagai reader aman dari Pojok Baca test.
- Validasi sesi baca zona Pojok Baca:
  - `access_origin = reading_point`,
  - `access_location_label = LOKASI TES`,
  - `quota_charged = 0`,
  - `reading_point_id = 6`,
  - `reading_token_id = NULL`.
- Validasi kunjungan digital zona Pojok Baca:
  - `visit_origin = reading_point`,
  - `location_label = LOKASI TES`,
  - `reading_point_id = 6`.
- Validasi sesi baca radius perpustakaan:
  - `access_origin = library`,
  - `access_location_label = PERPSUTAKAAN UMUM DAERAH`,
  - `quota_charged = 0`,
  - `reading_point_id = NULL`,
  - `library_id` kunjungan = `2`.
- Validasi akses luar zona tanpa token:
  - reader tetap status `200`,
  - pesan `Token baca luar zona tidak tersedia atau kuota sudah habis` tampil di halaman validasi.
- Browser plugin untuk screenshot visual belum bisa dipakai di sesi ini karena koneksi browser lokal gagal dengan error internal `sandboxCwd must use the file URI scheme`; validasi visual dilakukan melalui HTML smoke test.

## 2026-08-10 02:20 WIB

Status: kurasi highlight, rak katalog dashboard, dan Arena Belajar dirapikan.

Yang dilakukan:

- Mengganti pemilih buku di `/catalog/highlights` dari daftar statis menjadi pencarian AJAX dengan debounce dan preview cover/metadata.
- Menambahkan filter eksplisit `Semua`, `Digital`, dan `Non-digital` pada pemilih tersebut, serta badge jenis koleksi pada daftar highlight admin.
- Menambahkan rak katalog di `/user/dashboard` untuk seluruh katalog dan koleksi digital, masing-masing berisi:
  - Jelajah Acak,
  - Sering Dibaca (gabungan transaksi pinjam fisik dan sesi baca digital),
  - Koleksi Terbaru.
- Mengubah jumlah pada dashboard menjadi `Seluruh katalog` dan `Buku digital` agar sesuai dengan dua jenis rak.
- Merapikan kartu highlight agar satu pilihan tidak meninggalkan kolom kosong lebar.
- Merombak halaman `/belajar` menjadi Arena Belajar yang responsif: hero, akses raport/notifikasi, kartu quiz, penukaran poin, aktivitas mandiri, battle, dan mini game.
- Menambahkan CSS responsif untuk pemilih highlight, rak buku, dan Arena Belajar; cache-buster CSS dinaikkan ke `20260810c`.

Validasi:

- Lint PHP bersih untuk `Catalog.php`, `User_dashboard.php`, `Catalog_model.php`, `catalog/highlights.php`, `user/dashboard.php`, `game/lobby.php`, dan `routes.php`.
- `git diff --check` bersih.

## 2026-08-10 02:35 WIB

Status: metadata pemilih highlight disederhanakan dan koleksi impor dikenali oleh filter katalog digital.

- Preview hasil pencarian buku di `/catalog/highlights` kini hanya menampilkan judul, penulis/penerbit, kategori, tahun, dan status digital; tanpa gambar cover.
- Importer `tools/import_buku_digital.php` kini membuat eksemplar katalog digital publik bagi setiap PDF impor, dengan metadata `Ebook`, `PDF`, dan aturan `Baca digital`.
- Importer dijalankan ulang secara idempoten untuk 613 PDF:
  - 613 buku dan aset diperbarui,
  - 613 eksemplar katalog digital dibuat,
  - tidak ada file yang disalin ulang atau gagal.
- Cache-buster CSS halaman admin dinaikkan ke `20260810d`.

## 2026-08-10 02:55 WIB

Status: katalog dipertegas sebagai satu pintu seluruh jenis koleksi.

- Menambahkan atribut dan filter `Jenis Koleksi` pada katalog admin dan katalog publik.
- Jenis koleksi dapat diisi saat menambah/mengubah eksemplar, dengan pilihan awal Buku, Ebook, CD, DVD, Majalah, Audio, dan Peta (tetap menerima jenis lain).
- Kartu hasil katalog kini menampilkan jenis koleksi; filter `Ebook` memuat 623 judul dan seluruhnya memiliki pintu `Baca Online` dari katalog.
- Halaman Reader diubah menjadi `Aset PDF & Reader Aman`: fungsinya mengelola berkas dan kebijakan akses yang selalu ditautkan ke judul katalog, bukan daftar katalog terpisah.
- Rak katalog pada dashboard tidak lagi memakai gambar cover; thumbnail diganti ikon jenis koleksi agar ukurannya tetap rapat dan konsisten. Cache-buster dashboard dinaikkan ke `20260810g`.

Validasi:

- HTTP `/katalog?collection_type=Ebook` berhasil (`200`) dan mengembalikan 623 judul.
- Lint PHP dan `git diff --check` bersih.

## 2026-08-10 03:20 WIB

Status: jenis koleksi dipindahkan ke alur utama katalog dan buku ajar diberi kelompok khusus.

- Form `/catalog/create` dan `/catalog/edit` sekarang memiliki pilihan `Jenis Koleksi`: Buku, Ebook, CD, DVD, Majalah, Audio, Peta, atau Lainnya.
- Saat katalog manual disimpan, sistem otomatis membuat/memperbarui eksemplar utama dengan jenis yang dipilih. Detail eksemplar tetap dapat memuat lebih dari satu jenis untuk satu judul.
- Menambahkan patch SQL `sql/2026-08-10b_textbook_catalog_grouping.sql` dan menjalankannya:
  - Kategori Isi: `Buku Pelajaran`,
  - Klasifikasi Isi: `Buku Pelajaran dan Pembelajaran`,
  - Jenis Koleksi: `Ebook`,
  - Media: `PDF`,
  - Aturan: `Baca digital`.
- Seluruh 613 buku hasil impor dipetakan ke kelompok tersebut. Nomor klasifikasi mata pelajaran asal tetap tersimpan pada kolom bibliografi, sehingga tidak hilang.
- Importer diselaraskan agar impor ulang mempertahankan kelompok Buku Pelajaran.
- Rak katalog dashboard kembali memakai cover dengan ukuran tetap kecil (`2.45rem × 3.1rem`); cache-buster CSS dinaikkan ke `20260810h`.

Validasi:

- `/katalog?content_category_id=12` status `200` dan menghasilkan 613 judul Buku Pelajaran.
- Data item impor: `Ebook | Buku Pelajaran | PDF | Baca digital | 613`.

## 2026-08-10 03:45 WIB

Status: master jenis koleksi dapat dikelola admin, dan kode taksonomi ditampilkan pada form katalog.

- Menambahkan master CRUD `Jenis Koleksi` pada `/catalog/masters` bersama master Kategori dan Klasifikasi.
- Menambahkan patch SQL `sql/2026-08-10c_collection_type_master.sql`, dijalankan dengan jenis awal: Buku, Ebook, CD, DVD, Majalah, Audio, Peta, dan Lainnya.
- Jenis koleksi dapat ditambah, diubah, dinonaktifkan, atau dihapus jika belum dipakai oleh eksemplar.
- Form katalog kini memuat pilihan dari master tersebut, bukan daftar tetap di kode.
- Label kategori dan klasifikasi pada form sekarang selalu menampilkan `kode — nama`; dua kelompok lokal tampil sebagai `ilmiah — Karya Ilmiah Lokal` dan `buku-pelajaran — Buku Pelajaran dan Pembelajaran`.
- Audit policy Reader: saat ini enum policy mencampur lingkup akses, pembatasan lokasi/token, dan metode pengiriman PDF. Catatan ini belum diubah agar tidak mengubah hak akses koleksi yang sudah aktif tanpa keputusan kebijakan.
- Menambahkan tautan `Kelola jenis` langsung di samping pilihan Jenis Koleksi pada form katalog menuju `/catalog/masters?tab=collection_types`, serta memperjelas tombol menu menjadi `Master Katalog`.

## 2026-08-10 12:40 WIB

Status: tampilan Master Katalog dan zona waktu PHP diperbaiki.

- Header `/catalog/masters` kini menempatkan judul/deskripsi dan tab pada baris terpisah; tab dapat digulir horizontal pada layar kecil tanpa teks atau tab terakhir terpotong.
- Cache-buster CSS admin dinaikkan ke `20260810i`.
- Jam sistem server telah benar pada `Asia/Jakarta` (WIB), tetapi PHP sebelumnya memakai `PRC` (UTC+8), sehingga waktu aplikasi lebih cepat satu jam.
- Mengubah `date.timezone` pada PHP 8.1 CLI dan PHP-FPM menjadi `Asia/Jakarta`, kemudian merestart PHP-FPM.
- Validasi akhir: waktu PHP dan waktu sistem sama-sama WIB.
## 2026-08-10 — Reader, token, dan Arena Belajar

- Policy aset PDF dipisahkan menjadi `reader_audience` (Member aktif/Internal) dan `pdf_delivery` (Render terkunci/Download diizinkan). Migrasi: `sql/2026-08-10d_reader_access_delivery_split.sql`.
- Semua aset member mengikuti aturan lokasi yang sama: GIS perpustakaan dan Pojok Baca gratis; luar zona membutuhkan token. Refresh sebuah sesi aktif tidak memotong token kedua kali selama tiga jam.
- SOP token baca dan angka awal operasional dicatat di `docs/SOP_TOKEN_BACA.md`; unit paket aktif telah dinormalisasi ke sesi baca.
- Halaman `/belajar` diperbarui dengan tata kartu dan cache stylesheet baru (`20260810j`).
- Jalur request token tersedia di halaman Pojok Baca; petugas menyetujui atau menolak dari Monitoring Token. Persetujuan menerbitkan 3 sesi selama 7 hari.
- Paket Arena dan titik baca aktif dinormalisasi menjadi satuan sesi baca: 5 sesi (14 hari) atau 10 sesi (30 hari) dari Arena, serta 5 sesi untuk check-in harian.

## 2026-08-10 — Koleksi Project Gutenberg

- Menambahkan 20 klasik domain publik dari Project Gutenberg melalui mirror resmi, lalu merendernya menjadi PDF lokal pada `storage/ebooks/project-gutenberg`.
- Seluruh judul disimpan sebagai katalog `Ebook | PDF | Baca digital`, memiliki aset member aktif, dan dapat diunduh karena ditandai domain publik.
- Importer idempoten tersedia di `tools/import_project_gutenberg.php`; sumber katalog selalu memakai landing page Gutenberg, bukan tautan berkas langsung.
- Validasi akhir: 20 buku, 20 aset aktif, 20 eksemplar publik, dan seluruh signature PDF valid.

## 2026-08-10 — Cover dan proteksi Reader

- Seluruh 633 buku yang diimpor hari ini memakai cover fallback resmi `assets/img/book-cover-default.webp` secara eksplisit, sehingga kartu katalog selalu memiliki cover meski belum tersedia sampul individual.
- Aset buku pelajaran dan Project Gutenberg dikunci menjadi `Member aktif | Render terkunci`; tidak ada lagi PDF utuh yang dapat diunduh.
- Watermark render halaman Reader diperkecil, dibuat lebih transparan, dan hanya diletakkan satu kali di tengah halaman. Cache halaman lama dibersihkan agar perubahan langsung dipakai.
- Setelah itu, seluruh 20 judul Project Gutenberg dilengkapi cover individual resmi dari mirror Gutenberg pada `assets/uploads/project-gutenberg/covers`; importer cover dapat dijalankan ulang melalui `tools/fetch_project_gutenberg_covers.php`.

## 2026-08-12 — Monitor akses aplikasi

- Menambahkan `site_access_logs` melalui `sql/2026-08-12a_site_access_monitoring.sql` dan mengaktifkan hook CodeIgniter setelah controller selesai diproses.
- Akses halaman dinamis dicatat untuk tamu, member, dan petugas: halaman/route, metode, status respons, IP, perangkat, browser, sistem operasi, referrer, serta kode negara bila disediakan CDN/proxy.
- Halaman admin `/access-monitor` menyediakan ringkasan hari ini, pencarian, filter pengunjung/perangkat/halaman/periode, dan pagination.
- Menu berada pada Pengaturan Akses dan memerlukan permission `access_monitor.index`; diberikan ke SUPERADMIN serta ADMIN.
- Lokasi tidak diambil dari GPS browser; yang disimpan hanya negara kasar dari header CDN bila tersedia. NIK, alamat, tanggal lahir, dan data identitas sensitif lain tidak direplikasi ke log akses.

## 2026-08-12 — Override matriks pada import Bank Soal

- Validasi impor kini membedakan kesalahan struktur soal dari peringatan cakupan matriks Kurikulum Merdeka.
- Soal dengan struktur tidak lengkap tetap ditolak; peringatan matriks dapat di-override secara eksplisit untuk materi pengayaan atau level lebih lanjut.
- Override hanya tersedia bagi pengguna dengan izin ubah Bank Soal dan selalu dicatat pada audit log import.

## 2026-08-12 — Sirkulasi peminjaman fisik

- Menambahkan migrasi `sql/2026-08-12b_physical_loan_circulation.sql`: pengaturan layanan pinjam, durasi standar, batas pinjaman aktif, dan flag `is_loanable` pada setiap eksemplar.
- Petugas ADMIN/SUPERADMIN dapat mencatat peminjaman manual dari Request Buku/Transaksi Peminjaman dengan nomor anggota, NIK/nomor HP, serta barcode atau nomor induk eksemplar.
- Peminjaman manual dan pengembalian memakai `loan_transactions` serta `loan_transaction_items` yang sama dengan histori INLISLite. Riwayat tampil terpadu dan sumbernya diberi label aplikasi atau INLISLite.
- Status 165 transaksi `Loan` yang masih aktif telah diselaraskan ke eksemplar (`loaned`), sehingga katalog tidak lagi menawarkan request untuk buku yang sedang dipinjam.
- Katalog publik menampilkan ketersediaan fisik, memblokir request ketika tidak tersedia, dan dashboard member menampilkan pinjaman aktif, jatuh tempo, keterlambatan, serta riwayat terbaru.

### Pengembalian legacy dan konsistensi eksemplar

- Menambahkan migrasi `sql/2026-08-12m_legacy_loan_local_returns.sql` untuk menyimpan pengembalian transaksi sumber INLISLite sebagai `local_return_at`, petugas, dan catatan lokal. Snapshot sumber INLISLite tidak diubah, sehingga refresh sinkronisasi tidak membuka kembali buku yang sudah diterima petugas.
- `/catalog/loans` kini dapat menerima pengembalian untuk transaksi aplikasi maupun INLISLite. Transaksi legacy diberi penanda **kembali lokal** agar asal statusnya jelas.
- Tombol **Selaraskan Eksemplar** di `/catalog/loans` mencocokkan setiap eksemplar pinjam dengan transaksi efektif (`Loan` tanpa tanggal kembali sumber maupun lokal), tanpa mengubah eksemplar reservasi, hilang, atau perbaikan.
- Sinkronisasi transaksi INLISLite domain `loans` maupun `all` menjalankan penyelarasan eksemplar otomatis setelah import/refresh selesai.

## 2026-08-12 — Sumber soal sesi latihan

- `/quiz-sessions` memiliki tab status Semua, Buka, Berlangsung, Draft, Ditutup, dan Arsip dengan badge status yang lebih jelas.
- Editor sesi memiliki tab Bank Soal dengan tiga pilihan sumber: acak dari bank sesuai filter sesi, daftar soal bank tertentu, atau bank khusus sesi.
- Soal khusus sesi dapat diimpor langsung dengan alur pratinjau/validasi yang sama seperti import Bank Soal; disimpan sebagai soal aktif yang hanya dapat dipakai sesi tujuan.
- Mesin pengerjaan latihan kini membaca sumber soal yang dipilih, bukan selalu mengacak bank soal global.

## 2026-08-12 — Pelestarian dan digitalisasi naskah kuno

- Menambahkan modul admin `/manuscripts` untuk input, edit, filter, status publikasi, akses preview, dan audit perubahan naskah kuno.
- Rekam metadata mencakup nomor inventaris, judul/sebutan lokal, bahasa, aksara, media, halaman, ukuran, kondisi, periode, penyalin, asal, lokasi simpan, deskripsi isi, provenans, dan catatan konservasi.
- Digitalisasi dicatat dengan tanggal, petugas, catatan proses, hak pemanfaatan, cover, dan preview PDF/citra. Preview disimpan pada `storage/manuscripts` dan dilayani controller, bukan URL berkas langsung.
- Koleksi publik tersedia di `/naskah-kuno`, dengan filter, halaman detail, dan preview sesuai akses `Publik`, `Member`, atau `Internal`. Landing page serta dashboard pemustaka kini memiliki pintu masuk koleksi ini.
- Migrasi: `sql/2026-08-12l_ancient_manuscripts_module.sql`.
- Revisi input: hanya judul naskah yang wajib; nomor inventaris dapat dibuat otomatis, metadata lain boleh dilengkapi bertahap. Preview digital menerima PDF/citra hingga 200 MB, dengan batas PHP-FPM dan Nginx diselaraskan.

## 2026-08-13 — Perbaikan ikon sidebar

- Menambahkan migrasi `sql/2026-08-13a_fix_sidebar_menu_icons.sql` dan menerapkannya: Naskah Kuno memakai ikon `ti-feather`, Pengaturan Sistem memakai `ti-settings`, dan Reader PDF Aman memakai `ti-file-description`.
- Nama ikon sebelumnya tidak tersedia pada Tabler Icons yang digunakan aplikasi sehingga hanya membentuk kotak kosong di sidebar.

## 2026-08-13 — Viewer halaman naskah kuno

- Menambahkan migrasi `sql/2026-08-13b_manuscript_page_viewer.sql` dan tabel `ancient_manuscript_pages` untuk citra scan per halaman.
- Form Naskah Kuno kini menerima beberapa citra JPG/PNG/WEBP, memberi nomor halaman otomatis sesuai urutan berkas, dan menyimpan berkas di `storage/manuscripts/{id}/pages` yang tidak dapat diakses langsung dari web.
- Detail koleksi memilih viewer halaman bila tersedia; viewer menyediakan daftar halaman, navigasi sebelumnya/berikutnya, serta zoom 50%–300% untuk membaca detail.
- Akses viewer dan citra setiap halaman mengikuti hak `Publik`/`Member` yang sama dengan naskah; akses internal tidak disajikan ke publik. Pengambilan viewer/citra dicatat pada log pemanfaatan.

### Pembacaan khusus member dan nuansa buka naskah

- Endpoint preview tidak lagi mengirim PDF/citra mentah. Pengunjung tanpa login hanya menerima preview katalog berupa cover, metadata, serta deskripsi; isi naskah memerlukan login member.
- Viewer dan endpoint citra halaman menggunakan sesi member untuk seluruh naskah yang ditayangkan, mengirim respons `inline` tanpa tombol atau URL unduh PDF, dengan cache privat tanpa penyimpanan.
- Viewer menambahkan animasi membalik lembar, gesture swipe kiri/kanan, navigasi halaman, zoom, dan watermark antarmuka akses member.
- Ditambahkan aksi admin **Buat Halaman Viewer** untuk mengonversi master PDF menjadi citra halaman turunan. Naskah `Serat Kempalan Warni-Warni (Mantra, Primbon, Doa)` telah dikonversi menjadi 134 halaman viewer; master PDF tidak disajikan ke user.

## 2026-08-13 — Donasi koleksi digital terbuka

- Menambahkan form publik `/donasi-digital` yang dapat digunakan masyarakat dengan atau tanpa login untuk mengusulkan buku digital, naskah, riset, karya ilmiah, skripsi, tesis/disertasi, dan materi pembelajaran.
- Kontributor dapat mengunggah berkas hingga 200 MB atau memberi tautan HTTPS (misalnya Google Drive/repository); minimal satu sumber wajib tersedia.
- Lisensi bebas (`CC0`, `CC BY`, `CC BY-SA`, atau domain publik) dan pernyataan hak wajib diisi. Usulan tidak otomatis diterbitkan maupun masuk katalog.
- Admin mengelola antrean di `/digital-donations`, dapat meninjau berkas/tautan, memberi catatan, serta memperbarui status. Usulan `pending` ditambahkan ke Kotak Masuk ADMIN/SUPERADMIN.
- Migrasi: `sql/2026-08-13c_digital_donations.sql`; panduan: `docs/DIGITAL_DONATIONS.md`.
