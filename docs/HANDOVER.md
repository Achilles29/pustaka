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
- `/rbac/roles` Role & Permission.
- `/rbac/users` User.
- `/rbac/pages` Registry Halaman.
- `/rbac/sidebar` Sidebar.

## File Fondasi

Controller:

- `application/core/MY_Controller.php`
- `application/controllers/Auth.php`
- `application/controllers/Admin.php`
- `application/controllers/Home.php`
- `application/controllers/User_dashboard.php`
- `application/controllers/Rbac.php`

Model:

- `application/models/Auth_model.php`
- `application/models/Menu_model.php`
- `application/models/Role_model.php`
- `application/models/User_model.php`
- `application/models/Page_model.php`
- `application/models/Library_model.php`

View:

- `application/views/layouts/tabler.php`
- `application/views/rbac/*`
- `application/views/home/landing.php`
- `application/views/user/dashboard.php`
- `application/views/libraries/*`

CSS:

- `assets/css/pustaka.css`

## Migrasi SQL yang Sudah Ada

- `sql/2026-07-28a_auth_rbac_sidebar_foundation.sql`
- `sql/2026-07-29a_libraries_gis_schema.sql`
- `sql/2026-07-29b_public_root_demo_users_routes.sql`
- `sql/2026-07-29c_rbac_sidebar_foundation_refine.sql`

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
5. Buka `/libraries`.
6. Logout.
7. Login sebagai `pemustaka`, pastikan masuk ke `/user/dashboard`.

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
