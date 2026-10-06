# Standar RBAC dan Sidebar

Tanggal update: 2026-07-29

## Prinsip

RBAC dan sidebar adalah fondasi semua modul admin. Modul baru tidak boleh menambahkan menu hardcoded di view. Semua akses admin harus mengikuti urutan ini:

1. Daftarkan halaman di `sys_page`.
2. Berikan permission di `auth_role_permission`.
3. Daftarkan menu di `sys_menu`.
4. Controller admin extend `MY_Controller`.
5. Controller memanggil `require_permission($page_code, $action)`.

Keputusan dasar:

- Database operasional aplikasi baru adalah `pustaka`.
- Database `inlislite_v3` hanya menjadi acuan, staging, dan sumber migrasi.
- Hak akses aplikasi disimpan di database, bukan hardcoded di controller/view.
- Sidebar/menu aplikasi disimpan di database agar bisa diatur lewat UI.

## Route Utama

- `/` : landing page publik.
- `/login` : login semua role.
- `/admin` : admin panel untuk `SUPERADMIN` dan `ADMIN`.
- `/user/dashboard` : dashboard pemustaka, bukan admin panel.
- `/rbac/roles` : Role & Permission.
- `/rbac/users` : User.
- `/rbac/pages` : Registry Halaman.
- `/rbac/sidebar` : Pengaturan Sidebar.

Route lama berikut hanya compatibility alias:

- `/roles`
- `/users`
- `/sidebar/manage`

## Tabel Inti

- `auth_user`
- `auth_role`
- `auth_user_role`
- `sys_page`
- `auth_role_permission`
- `auth_user_permission_override`
- `sys_menu`
- `sys_sidebar_favorite`
- `auth_session_log`
- `audit_log`

## Tipe User / Role

| Kode | Nama | Scope | Fungsi |
| --- | --- | --- | --- |
| `SUPERADMIN` | Superadmin | Global | Mengelola seluruh sistem, role, user, sidebar, data master, dan audit. |
| `ADMIN` | Admin | Perpustakaan/unit | Mengelola operasional perpustakaan/unit yang ditugaskan. |
| `USER` | User/Pemustaka | Diri sendiri | Mengakses dashboard pemustaka, katalog, membership, event, dan layanan digital. |
| `LIBRARY_ADMIN` | Admin Perpustakaan | Satu `libraries.id` | Operasional jejaring melalui `/library-workspace`; satu-satunya role lokal baru, beberapa akun setara per lembaga. |

Keputusan 4 Oktober 2026: akun sekolah baru memakai `LIBRARY_ADMIN`, bukan `ADMIN` lama. Permission `library.workspace` dan menu didaftarkan melalui migrasi. Modul lama tidak diberikan ke akun lokal baru karena izin halaman saja belum menjamin isolasi query. `Library_access` memeriksa penugasan terkini, menolak scope kosong/multi-role, memaksa penggantian password awal, dan memblokir endpoint di luar ruang operasional serta whitelist halaman publik. Role `ADMIN` lama dipertahankan untuk kompatibilitas, bukan jaminan isolasi tenant baru. Tidak membuat `SUPERADMIN_SEKOLAH`, petugas, atau tingkatan pengelola lokal lain.

Halaman `/rbac/roles` menampilkan daftar tipe user terlebih dahulu. Hak akses dibuka dari aksi `Hak Akses` per tipe user, sehingga matrix permission tidak memenuhi halaman utama.

Contoh role tambahan dalam rancangan lama (tidak digunakan untuk onboarding jejaring sekolah sekarang):

- `ADMIN_DESA`
- `ADMIN_SEKOLAH`
- `ADMIN_SWASTA`
- `MITRA_POJOK_BACA`

Role turunan yang mengelola unit/perpustakaan memakai `scope_type = library`. Role publik/pemustaka memakai `scope_type = self`. Role lintas sistem memakai `scope_type = global`.

Seed user lokal awal sudah tidak dipakai sebagai acuan produksi. Akun operasional wajib memakai password baru dan role sesuai tugas.

## Menu Sidebar

Sidebar dirender dari `sys_menu` lewat `Menu_model::get_sidebar_tree()`.

Pembaruan jejaring 4 Oktober 2026: akun `LIBRARY_ADMIN` membaca area `LIBRARY` (11 menu operasional), sedangkan pusat tetap area `MAIN`. Permission baru `reports.network` diberikan kepada `ADMIN`/`SUPERADMIN` untuk laporan gabungan; role lokal tidak boleh membuka endpoint tersebut walaupun menu/permission salah ditambahkan. Editor visual sidebar lama masih mengelola area MAIN; menu LIBRARY diinisialisasi lewat migrasi registry.

Kelanjutan layanan: area LIBRARY menjadi **17 menu**, enam tambahan tetap memakai permission `library.workspace` (tidak ada role baru). Controller `Library_services` memaksa source network dan library akun untuk sekolah; pusat memakai permission `library.services`, dengan scope penugasan bila ada. Controller formulir publik hanya mengizinkan sesi operator sekolah pada lembaganya sendiri. Semua write admin memakai POST/CSRF, ekspor tetap permission terpisah. Lihat [LIBRARY_SERVICES.md](LIBRARY_SERVICES.md).

Field penting:

- `menu_key`: kode unik menu, contoh `catalog`.
- `page_id`: relasi ke `sys_page`; dipakai untuk filter permission.
- `parent_id`: parent menu untuk submenu.
- `icon`: class Tabler Icons, contoh `ti ti-books`.
- `url`: route relatif, contoh `catalog`.
- `sort_order`: urutan tampilan.
- `is_visible`: tampil/sembunyi dari sidebar.
- `is_active`: aktif/nonaktif.
- `is_locked`: menu sistem yang tidak boleh dimatikan dari UI.

Urutan dan parent sidebar diatur lewat drag-and-drop di `/rbac/sidebar`. Simpan urutan akan memperbarui `sys_menu.parent_id` dan `sys_menu.sort_order`.

Menu sistem saat ini:

- Dashboard
- Data Master
  - Master Wilayah
- Perpustakaan GIS
- Katalog
- Membership
- Pojok Baca
- Event
- Pengaturan Akses
  - User
  - Tipe User
  - Registry Halaman
  - Sidebar
  - Audit Log

## Standar Menambah Modul Admin

Rilis 5 Oktober 2026 menambah menu Pinjam Antarlembaga dan Denda Manual pada MAIN/LIBRARY (lokal kini 19 menu). Tetap `library.services` untuk pusat dan `library.workspace` untuk sekolah. Persetujuan pemilik pusat memakai aksi `approve`, ekspor `export`. Sekolah tidak diberi scope global: hanya dua pihak transaksi antarlembaga, atau ledger denda milik lembaganya. Persetujuan pemilik harus oleh akun berbeda dari pembuat permintaan. Tidak ada role baru.

Contoh modul baru `reading_reports`:

1. Buat controller `application/controllers/Reading_reports.php`.
2. Controller harus extend `MY_Controller`.
3. Tambahkan page registry:
   - `code`: `reading_reports.index`
   - `module`: `reading_reports`
   - `title`: `Laporan Baca`
   - `route`: `reading-reports`
4. Tambahkan permission role yang sesuai.
5. Tambahkan menu:
   - `menu_key`: `reading-reports`
   - `page_id`: id dari `reading_reports.index`
   - `icon`: `ti ti-chart-bar`
   - `url`: `reading-reports`
6. Tambahkan route di `application/config/routes.php`.
7. Di controller:

```php
$this->require_permission('reading_reports.index', 'view');
```

## Aksi Permission

Aksi standar:

- `view`
- `create`
- `edit`
- `delete`
- `export`
- `approve`

Jangan membuat nama aksi baru tanpa alasan kuat. Jika modul butuh aksi khusus, catat dulu di docs dan pertimbangkan apakah bisa dipetakan ke aksi standar.

## UI

Admin layout utama:

- `application/views/layouts/tabler.php`

RBAC views:

- `application/views/rbac/roles.php`
- `application/views/rbac/users.php`
- `application/views/rbac/pages.php`
- `application/views/rbac/sidebar.php`
- `application/views/rbac/_tabs.php`
- `application/views/rbac/_role_modal.php`
- `application/views/rbac/_user_scope_modal.php`

CSS utama:

- `assets/css/pustaka.css`

Tabler dan Tabler Icons dimuat dari CDN. Jangan commit folder source `tabler-dev`.

## Implementasi Aktif

- Modul RBAC dipusatkan di controller `Rbac`.
- View RBAC berada di `application/views/rbac`.
- Controller lama `Roles`, `Users`, dan `Sidebar` hanya wrapper kompatibilitas.
- Registry halaman dikelola dari `/rbac/pages`.
- Pengaturan sidebar dikelola dari `/rbac/sidebar`.
- Sidebar admin memakai ikon Tabler dari database dan struktur menu rekursif.
- Pengaturan role/scope user dilakukan dari modal per user di `/rbac/users`; tabel utama tetap ringkas dan hanya menampilkan ringkasan role serta cakupan akses.
- Pengaturan tipe user dilakukan dari `/rbac/roles`; tambah/edit tipe user memakai modal, sedangkan permission memakai aksi `Hak Akses`.

## Pengelompokan sidebar — 5 Oktober 2026

Menu tetap bersumber dari `sys_menu`, bukan daftar hardcode di tampilan. Migrasi
`sql/2026-10-05c_sidebar_groups.sql` hanya menambah induk dan mengatur hubungan/urutan
menu. ID, URL, permission, serta status aktif/terlihat menu lama dipertahankan.

Sidebar admin perpustakaan (`LIBRARY`) memiliki 7 entri utama dan tetap 19 tujuan:

| Menu utama | Submenu |
| --- | --- |
| Dashboard | Langsung ke dashboard |
| Koleksi & Inventaris | Katalog Buku, Eksemplar, Stok Opname, Label QR |
| Keanggotaan | Anggota Lokal, Kartu Anggota, Pendaftaran Online |
| Sirkulasi | Peminjaman, Reservasi, Pinjam Antarlembaga, Denda Manual |
| Kunjungan | Buku Tamu, Buku Tamu Mandiri |
| Laporan | Langsung ke laporan |
| Pengaturan | Profil Perpustakaan, Aturan Peminjaman, Admin Perpustakaan, Ganti Password |

Sidebar kabupaten (`MAIN`) diringkas dari 17 menjadi 12 entri utama:

- Stok Opname dan Naskah Kuno masuk **Koleksi & Katalog**.
- Operasional Perpustakaan masuk **Jejaring & Agenda**.
- **Layanan Harian** menjadi **Sirkulasi & Kunjungan**: Request Buku,
  Transaksi Peminjaman, Pinjam Antarlembaga, Denda Manual, Aktivitas Layanan,
  Monitor Buku Tamu, Pengaturan Buku Tamu, Sinkronisasi Layanan.
- Kelompok lain tetap dipertahankan. Jumlah yang terlihat mengikuti permission pengguna.

Induk dapat dibuka/ditutup di desktop maupun ponsel. Kelompok halaman aktif otomatis
terbuka. `sidebar_helper.php` memilih satu tujuan dengan URL paling spesifik agar,
misalnya, `catalog/loans` tidak sekaligus menandai `catalog`. Halaman edit/import/arsip
koleksi lokal tetap menandai submenu daftar yang sesuai. Atribut `aria-controls`,
`aria-expanded`, dan `aria-current` menjelaskan status navigasi.

Penerapan: `php tools/setup_sidebar_groups.php --apply`. Tool membackup database dan
snapshot menu ke direktori privat `/www/backup/pustaka-network/`, lalu menjalankan
perubahan menu dalam transaksi dan memverifikasi bahwa permission/rute tidak berubah.
Migrasi dapat dijalankan ulang tanpa menggandakan induk menu. Jangan menjalankan ulang
jika ingin mempertahankan penataan manual baru yang berbeda dari pengelompokan ini.

Pengujian terisolasi menggunakan `tools/test_sidebar_groups.php` serta
`tools/test_sidebar_groups_browser.cjs`: struktur, hak akses terbatas, pemilihan menu
aktif, buka-tutup induk, dan tampilan desktop/ponsel. Uji regresi model dan HTTP jejaring
tetap dilakukan tanpa menyentuh transaksi pengguna.

## Lanjutan

### Penambahan IPLM — 5 Oktober 2026

- `iplm.local`: admin lokal, dibatasi library_id akun untuk seluruh isian/ekspor.
- `iplm.manage`: admin pemkab, rekap/CRUD/verifikasi/analisis IPLM.
- `iplm.settings`: admin pemkab, definisi komponen dan periode.
- `library.types`: admin pemkab, jenis/subjenis perpustakaan.
- Sidebar lokal mempertahankan 7 rumpun utama; Laporan menjadi **Laporan & Pendataan**
  berisi laporan lama dan IPLM (20 tujuan total).
- Sidebar pemkab kini 13 rumpun utama, dengan kelompok **Pendataan IPLM**; master
  jenis/subjenis ditempatkan di **Data Master**.
- Sumber teknis dan status: `docs/iplm/README.md`. Tidak ada tingkatan baru untuk
  admin sekolah; peran LIBRARY_ADMIN tetap satu tingkat.

- Reset password dan wajib ganti password setelah login pertama.
- Audit log untuk perubahan user, role, permission, dan sidebar.
- Pembatasan `library_id` untuk admin unit/sekolah/desa/mitra.
- UI override permission spesifik per user.
