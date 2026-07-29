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

## Pola UI CRUD

- Halaman `index` hanya untuk menampilkan data, ringkasan, filter, pagination, dan aksi.
- Form tambah/edit dipisahkan dari tabel index:
  - gunakan modal jika form pendek dan data tidak terlalu kompleks,
  - gunakan halaman `create`/`edit` terpisah jika form panjang, data banyak, ada upload file, peta, tab, atau relasi kompleks.
- Jangan membuat panel form permanen di sebelah tabel pada halaman index.
- Halaman data wajib menyediakan komponen sesuai kebutuhan:
  - filter pencarian,
  - filter baris per halaman,
  - pagination,
  - filter status/kategori/jenis data yang relevan,
  - card ringkasan jika membantu membaca kondisi data.
- Tombol utama seperti `Tambah` diletakkan di header halaman atau card toolbar, bukan di dalam tabel.
- Aksi edit/nonaktifkan boleh berada di kolom aksi per baris, tetapi form besar tetap modal/halaman terpisah.
- Untuk data besar, query model harus memakai `limit` dan `offset`; jangan render semua row sekaligus.
- Untuk data kecil/referensi, modal tambah/edit boleh dirender di halaman index selama tetap dipisahkan sebagai partial view.
- Semua halaman wajib mobile friendly:
  - header dan tombol aksi harus bisa wrap di layar kecil,
  - tabel wajib berada dalam `.table-responsive`,
  - pagination/footer tabel harus bisa turun ke bawah tanpa overlap,
  - tab/filter harus tetap bisa disentuh dengan nyaman,
  - tombol aksi tabel memakai ikon plus label perintah, contoh `Edit`, `Aktifkan`, dan `Nonaktifkan`; jangan memakai label status seperti `Aktif`/`Nonaktif` pada tombol aksi.
  - status sekarang ditampilkan di badge/kolom status, contoh `Aktif`, `Nonaktif`, `Pending`, atau `Tayang`.
- Jika satu route memuat dua kelompok data atau lebih, gunakan tab dalam satu card/workspace.
  Contoh: `/regions` memakai tab `Kecamatan` dan `Desa / Kelurahan`, bukan dua card besar bertumpuk.
- Untuk halaman campuran yang punya peta/preview/tabel, gunakan tab atau layout split yang tetap nyaman di mobile.
- Tab halaman dan tab tampilan wajib memakai gaya segmented/pill yang jelas, ada state aktif kontras, dan tidak terlihat seperti tombol kecil yang mengambang.

## CSS

- File utama: `assets/css/pustaka.css`.
- Tema admin utama memakai biru Demokrat dan putih:
  - primary blue `#005baa`,
  - dark blue `#003f88`,
  - background putih/biru sangat muda,
  - aksen lain hanya dipakai secukupnya agar UI tetap bersih.
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
- `docs/ROADMAP.md`
- `docs/SECURITY_AND_ACCESS.md`

Perubahan data penting pada modul admin harus memanggil `audit_event()` dari controller agar masuk ke `audit_log`.

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

Push dilakukan manual oleh pemilik proyek. Jangan push otomatis kecuali ada instruksi eksplisit.

Push protection pernah memblokir commit karena folder source Tabler membawa token demo upstream. Jangan memasukkan source Tabler lagi.
