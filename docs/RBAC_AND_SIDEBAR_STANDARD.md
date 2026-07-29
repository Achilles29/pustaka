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

Halaman `/rbac/roles` menampilkan daftar tipe user terlebih dahulu. Hak akses dibuka dari aksi `Hak Akses` per tipe user, sehingga matrix permission tidak memenuhi halaman utama.

Tipe user tambahan dapat dibuat dari UI, misalnya:

- `ADMIN_DESA`
- `ADMIN_SEKOLAH`
- `ADMIN_SWASTA`
- `MITRA_POJOK_BACA`

Role turunan yang mengelola unit/perpustakaan memakai `scope_type = library`. Role publik/pemustaka memakai `scope_type = self`. Role lintas sistem memakai `scope_type = global`.

Seed user lokal:

- `superadmin` / `admin123` role `SUPERADMIN`
- `admin` / `admin123` role `ADMIN`
- `pemustaka` / `admin123` role `USER`

## Menu Sidebar

Sidebar dirender dari `sys_menu` lewat `Menu_model::get_sidebar_tree()`.

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

## Lanjutan

- Reset password dan wajib ganti password setelah login pertama.
- Audit log untuk perubahan user, role, permission, dan sidebar.
- Pembatasan `library_id` untuk admin unit/sekolah/desa/mitra.
- UI override permission spesifik per user.
