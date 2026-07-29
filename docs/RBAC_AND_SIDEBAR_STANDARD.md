# Standar RBAC dan Sidebar

Tanggal update: 2026-07-29

## Prinsip

RBAC dan sidebar adalah fondasi semua modul admin. Modul baru tidak boleh menambahkan menu hardcoded di view. Semua akses admin harus mengikuti urutan ini:

1. Daftarkan halaman di `sys_page`.
2. Berikan permission di `auth_role_permission`.
3. Daftarkan menu di `sys_menu`.
4. Controller admin extend `MY_Controller`.
5. Controller memanggil `require_permission($page_code, $action)`.

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

## Role Awal

- `SUPERADMIN`: akses penuh semua halaman dan pengaturan.
- `ADMIN`: akses panel admin dan modul operasional.
- `USER`: akses dashboard pemustaka, bukan panel admin.

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

CSS utama:

- `assets/css/pustaka.css`

Tabler dan Tabler Icons dimuat dari CDN. Jangan commit folder source `tabler-dev`.
