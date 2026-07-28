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
