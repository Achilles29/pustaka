# Operasional Perpustakaan Terpadu — rilis inti

Tanggal: 4 Oktober 2026. Rencana induk: [PERPUSTAKAAN_TERPADU_DEVELOPMENT_PLAN.md](PERPUSTAKAAN_TERPADU_DEVELOPMENT_PLAN.md).

## Cakupan dan keputusan

- Tetap satu aplikasi dan database Pustaka. `libraries` adalah identitas tunggal lembaga. Akun menunjuk langsung `auth_user.library_id`.
- Satu role lokal `LIBRARY_ADMIN` (Admin Perpustakaan), tanpa tingkatan pengelola lokal. Satu perpustakaan boleh memiliki banyak admin setara.
- Ruang operasional: `/library-workspace`. Superadmin sistem dapat memilih lembaga; akun lokal tidak bisa berpindah scope. Dari `/libraries`, superadmin dapat memakai tombol **Operasional**.
- Data baru dipisahkan pada `network_books`, `network_items`, `network_members`, `network_loans`, `network_visits`, `network_settings`, dan `network_audit`. Seluruh kepemilikan eksplisit berdasarkan `library_id`.
- Data/akun/role lama tidak dipindah atau diubah otomatis. Katalog lama kabupaten tetap di `/katalog`, katalog terbitan jejaring di `/jejaring/katalog`. Katalog jejaring ditautkan dari landing dan katalog publik.

## Fitur tersedia

1. Dashboard koleksi fisik/digital, eksemplar, anggota lokal, pinjaman aktif dan terlambat.
2. CRUD dan arsip katalog, eksemplar, anggota lokal; pencarian, pilihan jumlah baris, pagination, ekspor CSV dan Excel asli `.xlsx`.
3. Impor Excel memakai template dengan pratinjau/konfirmasi; maksimal 500 baris dan 3 MB. Formula/macro ditolak. Satu baris gagal membatalkan seluruh batch.
4. Katalog fisik/digital, metadata bibliografi, publikasi draf/tayang. Digital awal berupa tautan HTTPS; bukan unggah aset atau pemberian hak distribusi otomatis.
5. Barcode unik per perpustakaan; anggota lokal dapat memakai NIS atau nomor otomatis. Data pribadi anggota tidak muncul di katalog publik.
6. Pinjam, kembali, perpanjang, batas pinjaman, dan aturan hari/limit masing-masing lembaga. Eksemplar dipinjam tidak dapat diedit/diarsipkan. Histori tidak dihapus.
7. Kunjungan anggota/tamu, enam tujuan kunjungan, kanal offline/online terpisah. Layanan digital boleh offline.
8. Laporan bulanan per tahun, rekap tujuan kunjungan, unduhan CSV/Excel; statistik fisik/digital dipisah.
9. Profil lokal: PIC, alamat, jam, kontak, fasilitas, deskripsi. NPSN/nama/wilayah/GPS/status/verifikasi tetap dikelola pusat melalui `/libraries`.
10. Tambah admin setara dan nonaktifkan admin lokal lain; tidak dapat menonaktifkan diri atau admin aktif terakhir. Penggantian password pribadi.

## Onboarding dan penggunaan

Username nama sekolah dinormalisasi (contoh `sd-negeri-2-karangasem`); benturan ditambah kecamatan, lalu NPSN. Satu akun awal untuk masing-masing 410 SD/SMP/TK/SKB yang diimpor dan terverifikasi. Akun tambahan sebaiknya memakai nama operator untuk jejak tanggung jawab individual.

Password awal seragam sesuai permintaan pemilik aplikasi; wajib diganti pada login pertama. File daftar akun `.xlsx` beserta password awal disimpan hanya di `/www/backup/pustaka-network/` dengan direktori `0700`, file `0600`, di luar webroot. Jangan menyalin file ini ke `docs`, Git, grup umum, atau direktori publik. Distribusikan hanya kepada PIC yang benar. Password baru membatalkan sesi lama lainnya.

Urutan penggunaan: `/login` → ganti password → atur profil/aturan pinjam → input/impor katalog → tambah eksemplar fisik → input/impor anggota → catat peminjaman/kembali dan kunjungan → laporan. Untuk judul katalog di luar 1.000 opsi terbaru, gunakan **Tambah eksemplar** pada baris judul yang ditemukan melalui pencarian katalog.

Anggota lokal tidak otomatis mempunyai akun pemustaka kabupaten. Ini pemisahan dataset dalam database yang sama, bukan pembuatan satu database fisik per sekolah.

## Pengamanan

- Scope berasal dari akun DB terkini, bukan POST/URL. Status akun/lembaga dan role diperiksa setiap request; scope kosong tidak menjadi global.
- Akun sekolah tidak diberi akses modul legacy (anggota global, katalog global, laporan global, RBAC, backup, dan sebagainya). Pengaman berlaku sebelum aksi controller dan sebelum logika turunan `MY_Controller`.
- Constraint gabungan `(library_id, id)` mencegah peminjaman memakai anggota/eksemplar perpustakaan lain.
- Transaksi dengan row lock dan pembacaan terkini menjaga ketersediaan dan limit anggota saat dua operator bekerja bersamaan.
- Semua perubahan menggunakan POST + token CSRF sekali pakai. Data ditampilkan dengan HTML escaping; formula CSV dinetralkan, XLSX memakai sel teks untuk identifier.
- Akun ber-role campuran atau tanpa penugasan valid ditolak. Pencabutan role/status dan reset password memutus akses sesi aktif.
- Audit `network_audit` mencatat lembaga, aktor, tindakan, objek, waktu; bukan password atau salinan data pribadi lengkap.

## Migrasi dan pengujian

- Migrasi tambahan: `sql/2026-10-04a_library_network.sql`; tidak menghapus/memindahkan tabel legacy.
- `php tools/setup_library_network.php` adalah dry-run. `--apply` membuat dan memverifikasi backup **seluruh database**, lalu skema/role/menu serta akun dalam transaksi. Tidak mereset akun yang telah dibuat; eksekusi ulang ditolak jika aktivasi sudah pernah/parsial berjalan.
- Backup `.sql.gz`, checksum/manifest `.json`, manifest akun tanpa password, dan Excel kredensial berada di direktori privat tersebut. SQL tanpa kompresi dipertahankan untuk pemulihan.
- Pengujian menggunakan database `pustaka_network_test_*` terpisah, schema asli dengan fixture sintetis; tidak memasukkan data uji transaksi ke produksi.
- Skrip: `prepare_library_network_test.php`, `test_library_network.php` (model/impor), `test_library_network_http.php` (login/HTTP/izin), `test_library_network_concurrency.php`, `test_library_network_browser.cjs`.
- Hasil final aktivasi dan pengujian dicatat di bagian verifikasi rilis. Pengujian bukan jaminan tidak akan pernah ada bug; pantau penggunaan awal sebelum distribusi luas.

## Pemulihan

Jika ditemukan masalah: hentikan distribusi akun; nonaktifkan akun `source_system=library_network_v1` melalui pusat untuk menghentikan akses baru. Jangan menghapus tabel atau merestorasi seluruh DB riil secara membabi-buta. Data baru yang sudah dimasukkan harus dibackup dahulu.

Backup penuh dapat direstorasi ke **database pemulihan terpisah** untuk rekonsiliasi. Pengembalian produksi memerlukan maintenance window dan pemeriksaan transaksi lain yang terjadi sesudah backup. Jangan memakai manifest akun untuk menghapus pengguna tanpa memeriksa histori/audit dan foreign key. Revert kode harus mempertahankan pengaman akun lokal sampai akun-akun tersebut dinonaktifkan.

## Belum termasuk rilis inti

Stok opname bersesi, cetak label barcode/QR **untuk sekolah**, reservasi mandiri lokal, denda, unggah/DRM aset digital lokal, peminjaman lintas lembaga, penautan identitas anggota kabupaten, dan migrasi histori INLIS tiap sekolah belum diimplementasikan. Label QR, reservasi dan pembaca digital pada modul kabupaten sudah ada; lihat [pemetaan fitur](PETA_FITUR_KABUPATEN_DAN_PERPUSTAKAAN.md). Perubahan identitas dan GPS tetap melalui pusat.

Pembaruan berikutnya pada 4 Oktober 2026: laporan gabungan kabupaten `/reports/network` dan sidebar vertikal admin sekolah telah ditambahkan. Laporan membedakan jumlah keanggotaan, rekaman katalog berbeda dan ISBN valid; belum mengklaim orang unik lintas lembaga. Definisi dan hasil uji: [NETWORK_REPORTS.md](NETWORK_REPORTS.md).

## Verifikasi rilis — 4 Oktober 2026

Kelanjutan setelah rilis inti dan laporan: [LIBRARY_SERVICES.md](LIBRARY_SERVICES.md). Dokumen tersebut mencatat stok opname kedua sumber, enam menu layanan sekolah tambahan dan batas fitur yang belum diaktifkan. Catatan verifikasi di bawah adalah baseline rilis inti, bukan hitungan menu terbaru.

Aktivasi produksi selesai: **410 akun pada 410 perpustakaan sekolah**, seluruhnya aktif, satu role `LIBRARY_ADMIN`, penugasan cocok dengan manifest, password awal seragam terverifikasi dan `force_password_change=1`. Dry-run setelah aktivasi menghasilkan 0 akun baru/410 sudah tersedia. Tidak ada reset atau penimpaan akun lama.

Backup penuh: `/www/backup/pustaka-network/before-network-20261004-174321-1a1d1aa4.sql.gz` (checksum pada manifest di direktori yang sama). Backup benar-benar direstorasi ke database pemulihan terpisah; 11 jumlah tabel baseline dan identitas/credential hash/penugasan akun lama serta role asli cocok. Database pemulihan sementara kemudian dihapus; backup asli tetap disimpan. Tidak ada stored routine/event SQL pada database saat backup.

| Kelompok pengujian | Hasil |
| --- | --- |
| Model, validasi, isolasi dan Excel | 42 lulus |
| HTTP, login, CSRF, CRUD, pinjam–kembali, multipart import, pencabutan sesi, lintas scope | 70 lulus |
| Transaksi konkuren dua proses | 6 lulus |
| Regresi GIS direktori/landing/member | 38 lulus |
| Regresi katalog lama dan Excel | 54 lulus |
| Verifikasi akun/database dan situs produksi | 26 lulus |
| Browser Chrome desktop 1440 px / mobile 390 px | Tidak ada error JavaScript; halaman mobile tidak meluber horizontal; sidebar akun sekolah hanya menampilkan ruang operasional |

Total **236 pemeriksaan otomatis**, ditambah pemeriksaan browser dan pemulihan backup. Permintaan skrip HTTP awal tanpa User-Agent terkena tantangan Cloudflare; pengujian ulang dengan identitas klien berhasil, tanpa mengubah proteksi situs. `/login`, landing, `/katalog`, `/jejaring/katalog`, login akun sekolah dan formulir wajib ganti password telah diperiksa pada domain produksi. Password awal tidak diganti oleh pengujian produksi.

Baseline sebelum/sesudah aktivasi sama: 411 perpustakaan, 5.585 anggota, 14.748 baris buku, 23.577 eksemplar, 31.741 transaksi pinjam legacy, 2.458 detail transaksi pinjam, dan 78.401 kunjungan legacy. Hitungan ini mencakup baris arsip, bukan selalu angka aktif di dashboard.

Tabel operasional baru masih kosong saat serah terima (selain audit pembuatan akun), sehingga tidak ada anggota, koleksi, pinjaman, atau kunjungan sintetis yang masuk ke produksi.

Tiga database fixture, database pemulihan, server HTTP uji, dan runtime privat pengujian telah dibersihkan. Bukti screenshot desktop/mobile, manifest pemeriksaan produksi/pemulihan, backup penuh, serta Excel akun tetap disimpan privat di `/www/backup/pustaka-network/`. Lingkungan uji dapat dibuat ulang dengan skrip persiapan; data produksi tidak dihapus.
