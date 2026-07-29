# Standar Coding Pustaka Digital

Tanggal update: 2026-07-29

## Stack

- PHP 7/8 via XAMPP.
- CodeIgniter 3.1.13.
- MariaDB/MySQL.
- Tabler CSS dari CDN.
- Tabler Icons dari CDN.
- Leaflet untuk GIS.

## Database

- Database aplikasi: `pustaka`.
- Database acuan/migrasi: `inlislite_v3`.
- Dump acuan terbaru berada di `C:\xampp\htdocs\inlislite_v3.sql`.

Jangan membangun fitur baru langsung di tabel INLISLite. INLISLite hanya sumber data/migrasi.

## Pola Controller

- Halaman publik boleh extend `CI_Controller`.
- Halaman admin wajib extend `MY_Controller`.
- Halaman admin wajib memanggil `require_permission()`.
- Redirect role login:
  - `SUPERADMIN` dan `ADMIN` ke `/admin`.
  - `USER` ke `/user/dashboard`.

Contoh:

```php
class Catalog extends MY_Controller
{
    public function index()
    {
        $this->require_permission('catalog.index', 'view');
        $this->render('modules/placeholder', [
            'title' => 'Katalog',
        ]);
    }
}
```

## Pola Model

- Query database diletakkan di model.
- Controller tidak boleh berisi query panjang kecuali validasi sederhana.
- Payload insert/update dibersihkan di model dengan method `clean_payload()` atau pola sejenis.
- Gunakan Query Builder CodeIgniter, bukan SQL string manual, kecuali untuk file migrasi SQL.

## Pola View

- Admin memakai layout `application/views/layouts/tabler.php`.
- Jangan membuat sidebar/menu hardcoded di view modul.
- View modul hanya berisi konten halaman.
- Gunakan class Tabler dan custom CSS dari `assets/css/pustaka.css`.
- Jangan menaruh logika permission kompleks di view.

## CSS

- File utama: `assets/css/pustaka.css`.
- Admin panel harus rapat, mudah dipindai, dan tidak bergaya landing page.
- Cards radius maksimal 8px.
- Jangan memakai dekorasi visual berlebihan.
- Pastikan teks tidak overlap dan tetap terbaca di mobile.

## Migrasi SQL

Semua perubahan struktur/seed database harus dicatat di folder `sql`.

Format nama:

```text
YYYY-MM-DDx_deskripsi_singkat.sql
```

Contoh:

- `2026-07-29c_rbac_sidebar_foundation_refine.sql`

Migrasi harus idempotent jika memungkinkan:

- `CREATE TABLE IF NOT EXISTS`
- `INSERT ... ON DUPLICATE KEY UPDATE`
- `INSERT IGNORE`

## Dokumentasi

Setiap pekerjaan besar wajib dicatat di:

- `docs/PROGRESS.md`

Jika membuat modul/fondasi baru, buat dokumen khusus di `docs`.

Dokumen penting:

- `docs/RBAC_AND_SIDEBAR_STANDARD.md`
- `docs/LIBRARIES_GIS.md`
- `docs/AUTH_RBAC_SIDEBAR.md`
- `docs/ROADMAP.md`
- `docs/SECURITY_AND_ACCESS.md`

## Git

Jangan commit:

- `tabler-dev/`
- `inlislite_v3.sql`
- `assets/uploads/*`
- `docs/_note.md`
- file cache/log runtime

Sebelum push:

```bash
git status
git diff --cached --name-only
git push --dry-run --porcelain origin main
```

Push protection pernah memblokir commit karena folder source Tabler membawa token demo upstream. Jangan memasukkan source Tabler lagi.
