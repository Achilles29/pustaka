# Auth, RBAC, dan Sidebar Database

Tanggal: 2026-07-28

Catatan 2026-07-29: dokumen ini adalah catatan fondasi awal. Standar implementasi kanonis terbaru ada di [RBAC_AND_SIDEBAR_STANDARD.md](RBAC_AND_SIDEBAR_STANDARD.md). Modul pengelolaan aktif sekarang berada di route `/rbac/roles`, `/rbac/users`, `/rbac/pages`, dan `/rbac/sidebar`.

## Keputusan

- Database operasional aplikasi baru adalah `pustaka`.
- Database `inlislite_v3` hanya menjadi acuan, staging, dan sumber migrasi.
- Hak akses aplikasi disimpan di database, bukan hardcoded di controller/view.
- Sidebar/menu aplikasi disimpan di database agar bisa diatur lewat UI.

## Role Awal

| Kode | Nama | Scope | Fungsi |
| --- | --- | --- | --- |
| `SUPERADMIN` | Superadmin | Global | Mengelola seluruh sistem, role, user, sidebar, data master, dan audit. |
| `ADMIN` | Admin | Perpustakaan/unit | Mengelola operasional perpustakaan/unit yang ditugaskan. |
| `USER` | User/Pemustaka | Diri sendiri | Mengakses katalog, membership, event, dan fitur pemustaka. |

Role turunan seperti admin sekolah, admin desa, atau admin mitra dapat dibuat sebagai role tambahan dengan `scope_type = library` ketika struktur perpustakaan sudah dibangun.

## Tabel Fondasi

| Tabel | Fungsi |
| --- | --- |
| `auth_user` | Akun aplikasi baru. |
| `auth_role` | Daftar role dan level akses. |
| `auth_user_role` | Relasi user ke role. |
| `auth_session_log` | Log login/logout dan event auth. |
| `sys_page` | Registry halaman/modul yang bisa diberi permission. |
| `auth_role_permission` | Matrix izin per role dan halaman. |
| `auth_user_permission_override` | Override izin spesifik per user. |
| `sys_menu` | Struktur menu/sidebar dari database. |
| `sys_sidebar_favorite` | Favorit/shortcut menu per user. |
| `audit_log` | Jejak perubahan penting aplikasi. |
| `ci_sessions` | Tabel session jika nanti session CI dipindah ke database. |

## Seed Awal

User awal:

- Username: `superadmin`
- Email: `superadmin@pustaka.local`
- Password awal: `admin123`
- Wajib ganti password saat modul login sudah aktif.

Menu awal:

- Dashboard
- Perpustakaan GIS
- Katalog
- Membership
- Pojok Baca
- Event
- Sistem
- Manajemen User
- Role & Akses
- Manajemen Sidebar

## Langkah Berikutnya

- Menambahkan drag/drop urutan menu di `/rbac/sidebar`.
- Menambahkan reset password dan wajib ganti password setelah login pertama.
- Menambahkan audit log untuk perubahan user, role, permission, dan sidebar.
- Menambahkan pembatasan `library_id` untuk admin unit/sekolah/desa/mitra.
- Menambahkan UI override permission spesifik per user.

## Implementasi 2026-07-29

Penyempurnaan terbaru:

- Modul RBAC dipusatkan di controller `Rbac`.
- View lama `roles`, `users`, dan `sidebar` diganti dengan view terpadu di `application/views/rbac`.
- Controller lama `Roles`, `Users`, dan `Sidebar` menjadi wrapper kompatibilitas agar route lama tetap aman.
- Registry halaman bisa dikelola dari UI `/rbac/pages`.
- Pengaturan sidebar bisa dikelola dari UI `/rbac/sidebar`.
- Sidebar admin memakai ikon Tabler dari database dan struktur menu rekursif.
- Menu sistem berganti menjadi `Pengaturan Akses` sebagai pusat user, role, halaman, dan sidebar.

## Implementasi 2026-07-28

Sudah dibuat:

- Login dan logout berbasis `auth_user`.
- `MY_Controller` untuk memaksa login, memuat user aktif, role, permission, dan menu.
- Layout Tabler menampilkan user aktif, role, dan tombol logout.
- Menu admin difilter dari `sys_menu` + `auth_role_permission`.
- UI Manajemen Sidebar awal: tambah/edit menu, pilih parent, pilih halaman permission, URL, urutan, visible, aktif/nonaktif.
- UI Role & Hak Akses: matrix halaman x aksi (`view`, `create`, `edit`, `delete`, `export`, `approve`).
- UI Manajemen User: tambah user, pilih role, update role, aktif/nonaktif user.
- Placeholder terproteksi RBAC untuk modul Perpustakaan GIS, Katalog, Membership, Pojok Baca, dan Event.
