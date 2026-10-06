# Perapian legenda dan jenis/subjenis — 6 Oktober 2026

Sudah diterapkan pada aplikasi dan database produksi setelah backup.

## Legenda

`/libraries` sekarang memakai dua kolom berdampingan, termasuk pada ponsel:

- **Jenis** di kiri: memilih jenis dan membersihkan pilihan subjenis lama.
- **Subjenis** di kanan: mengikuti jenis terpilih; jika semua jenis dipilih, menampilkan semua subjenis disertai nama induknya. Klik subjenis otomatis memilih jenis induk dan memfilter tabel serta titik peta.
- **Semua subjenis** menghapus filter subjenis tetapi mempertahankan jenis. **Semua jenis** menghapus keduanya.
- Subjenis yang panjang dapat digulir pada kolom kanan; seluruh enam jenis tetap terlihat di kiri.
- Pagination tabel tidak membatasi titik peta. Hak akses tetap berlaku.

## Referensi yang dihapus

Tiga baris jenis lama dihapus secara fisik dari `library_types`, bukan hanya disembunyikan:

| Jenis lama | Klasifikasi yang digunakan |
|---|---|
| Perpustakaan Daerah (`perpusda`, ID 1) | Umum → Kabupaten/Kota |
| Perpustakaan Desa (`desa`, ID 3) | Umum → Desa/Kelurahan |
| Komunitas Literasi (`komunitas`, ID 5) | Umum → TBM/Rumah Baca/penamaan lainnya |

Ketiganya sudah tidak dipakai oleh master perpustakaan, subjenis, atau identitas isian IPLM. Tidak diperlukan pemindahan akun atau unit.

Hasil akhir: **6 jenis, 19 subjenis, 832 perpustakaan, 680 titik GPS valid**. Enam jenis yang tersisa: Umum, Sekolah, Khusus, Perguruan Tinggi, Swasta, dan Mitra Pojok Baca.

**Swasta** dipertahankan sesuai keputusan sebelumnya untuk tiga perpustakaan pesantren. **Mitra Pojok Baca** belum menduplikasi subjenis yang ada. Permintaan ini tidak digunakan untuk mengubah pengelompokan pesantren atau menambah klasifikasi baru tanpa alasan.

## UI, pilihan enum, dan pencegahan duplikasi

- Form perpustakaan, form IPLM, filter, dan master jenis memakai referensi database yang sama; pilihan lama hilang dari seluruh dropdown tersebut.
- Jenis/subjenis menggunakan tabel referensi, bukan tipe kolom SQL `ENUM`; tidak ada enum SQL identitas yang harus dipotong. Enum status negeri/swasta, aktif, dan status transaksi tidak diubah.
- Daftar ikon/warna jenis di JavaScript tidak lagi memuat tiga jenis lama.
- Seed jenis pada `sql/2026-07-29a_libraries_gis_schema.sql` sudah diperbarui agar instalasi/seed ulang tidak membuat ketiganya kembali.
- Validasi master menolak kode/nama jenis yang menduplikasi subjenis, menolak pembuatan kembali tiga kategori lama, dan menolak subjenis yang menduplikasi jenis induk.
- Data sumber dan riwayat lama tetap dipertahankan untuk audit; istilah “desa” dalam referensi wilayah atau nama perpustakaan tidak dihapus karena bukan enum jenis perpustakaan.

## Pengujian dan keamanan data

- 20 pemeriksaan model/migrasi + 1 pemeriksaan penolakan duplikasi terbalik lulus: penghapusan ditolak bila referensi masih digunakan, rollback, data master/map tidak berubah, 3 jenis benar-benar terhapus, validasi enum, edit jenis normal, dan pengulangan tanpa perubahan tambahan.
- 23 pemeriksaan browser pada desktop 1440 px dan ponsel 390 px lulus: dua kolom, enum bersih, filter subjenis/peta, highlight, reset, dan form tambah/master jenis. Tidak ada error JavaScript.
- 39 pemeriksaan regresi peta publik/member lulus. Tidak ada pasangan jenis/subjenis yatim.
- Akun, perpustakaan, seluruh subjenis, nilai/bukti isian IPLM, dan periode 2026 tidak berubah oleh proses ini.

Backup database sebelum penghapusan:

`/www/backup/pustaka-network/before-network-20261006-105002-77120508.sql.gz`

Backup kode sebelum perubahan:

`/www/backup/pustaka-network/before-network-20261006-104624-a6e5b6b2-taxonomy-code/`

Baris jenis yang dihapus juga disimpan lengkap pada `iplm_history`, event `taxonomy.duplicates.removed`, serta manifest backup `.taxonomy.json`, sehingga dapat dipulihkan secara terarah bila diperlukan. Script `tools/cleanup_library_taxonomy.php` default hanya analisis; perubahan memerlukan `--apply` dan backup otomatis.

Server uji dihentikan; database/folder fixture khusus pengujian sudah dibersihkan setelah backup. Backup fixture: `/www/backup/pustaka-network/before-network-20261006-105131-fb9a911e.sql.gz`. Bukti pengujian dan tangkapan layar: `/www/backup/pustaka-network/before-network-20261006-105131-fb9a911e-taxonomy-evidence/`. Pembersihan tidak menghapus data produksi.
