# SOP Sinkronisasi INLISLite ke Pustaka

Update: 2026-08-07

Dokumen ini menjadi standar awal untuk sinkronisasi data INLISLite produksi ke database aplikasi `pustaka`.

## Prinsip Utama

- INLISLite adalah sumber/staging data lama.
- `pustaka` adalah database operasional aplikasi baru.
- Aplikasi Pustaka tidak menulis balik ke database INLISLite.
- Sinkronisasi harus aman dijalankan lebih dari satu kali.
- Data baru dari INLISLite diimpor.
- Data lama yang sudah pernah diimpor dapat diperbarui hanya pada field yang memang dimiliki sumber INLISLite.
- Field kurasi lokal tidak boleh tertimpa oleh sync.
- Data yang hilang dari dump terbaru tidak langsung dihapus dari Pustaka.

## Pola Produksi yang Direkomendasikan

### Pilihan Utama: Dump Periodik ke Staging

1. Server INLISLite produksi membuat dump terjadwal.
2. Dump diimpor ke database staging lokal/server Pustaka, misalnya `inlislite_v3`.
3. Job ETL Pustaka membaca dari staging tersebut.
4. Operator melihat laporan hasil sync.

Kelebihan:

- Paling aman karena tidak membebani database produksi secara langsung.
- Mudah rollback.
- Cocok untuk tahap pilot.

Kekurangan:

- Data tidak real-time.
- Butuh jadwal dump dan transfer file yang disiplin.

### Alternatif: Read-only Replica

1. INLISLite produksi menyediakan replica/read-only user.
2. Pustaka membaca dari koneksi read-only.
3. Job incremental dijalankan berkala.

Kelebihan:

- Data lebih segar.
- Tidak perlu import dump manual.

Kekurangan:

- Butuh konfigurasi server lebih matang.
- Perlu kontrol beban query.

### Tidak Direkomendasikan: Akses Tulis Langsung ke INLISLite

Pustaka tidak boleh mengubah data INLISLite secara langsung karena akan memperbesar risiko rusaknya sistem lama.

## Mode Sinkronisasi

### 1. Import Data Baru

Tujuan:

- Mengambil data dari INLISLite yang belum pernah ada di Pustaka.

Perilaku:

- Jika `source_id` belum ada di tabel target atau mapping, data dibuat.
- Jika sudah ada, data dilewati.
- Mode ini aman untuk operator harian.

### 2. Update Data Lama

Tujuan:

- Memperbarui field yang berasal dari INLISLite untuk data yang sudah pernah diimpor.

Perilaku:

- Data dicari melalui `source_id`, `source_system`, atau `catalog_sync_maps`.
- Field source-owned diperbarui.
- Field local-owned dipertahankan.

### 3. Dry Run

Tujuan:

- Simulasi sync tanpa menulis ke database.

Perilaku:

- Menghitung kandidat data baru, data update, data gagal mapping, dan estimasi aset.
- Wajib dijalankan sebelum sync besar dari data produksi.

### 4. Full Reconcile

Tujuan:

- Membandingkan keseluruhan source dan target.

Perilaku:

- Menghitung jumlah source, target, mapped, unmapped, missing file, dan orphan.
- Tidak menghapus data otomatis.
- Data yang tidak lagi ada di source ditandai untuk review, bukan dihapus.

## Kepemilikan Field

### Field Source-owned

Field ini boleh diperbarui saat mode update:

- Judul, penulis, penerbit, tahun, ISBN, subjek, nomor panggil dari `catalogs`.
- Barcode, nomor induk, RFID, lokasi, status INLISLite, kategori koleksi, media, dan aturan pinjam dari `collections`.
- Nama, alamat, kontak, tanggal lahir, status anggota, pendidikan, pekerjaan, dan jenis anggota dari `members`.
- Transaksi peminjaman, detail item pinjam, kunjungan lama, dan hak layanan dari tabel layanan INLISLite.
- Label master dari tabel referensi INLISLite.

### Field Local-owned

Field ini tidak boleh ditimpa oleh sinkronisasi INLISLite:

- `books.content_category_id`
- `books.content_classification_id`
- Kurasi cover manual.
- Semua data `digital_assets` hasil upload/kurasi admin.
- Kebijakan akses ebook dan hak publikasi.
- Password akun lokal.
- Status akun lokal jika diblokir admin.
- Status kartu digital dan alasan blokir.
- Pendaftaran online dan verifikasi admin.
- Request buku dan perpanjangan membership.
- Data Pojok Baca, token, sesi baca, dan audit reader.
- Data event literasi.
- Sidebar, RBAC, dan pengaturan sistem.

## Urutan Sinkronisasi

Jalankan berurutan agar relasi data lengkap.

1. Backup database `pustaka`.
2. Import dump terbaru INLISLite ke database staging `inlislite_v3`.
3. Sync master referensi INLISLite ke `inlislite_master_references`.
4. Sync lokasi/unit layanan INLISLite sebagai referensi mapping perpustakaan.
5. Sync bibliografi `catalogs` ke `books`.
6. Sync penulis/subjek/ruas penting ke `book_authors` dan `book_subjects`.
7. Sync eksemplar `collections` ke `book_items`.
8. Migrasi cover katalog, foto member, dan file digital dari folder INLISLite.
9. Sync anggota `members` ke `members` dan `auth_user`.
10. Sync transaksi harian: kunjungan, hak layanan, peminjaman, dan detail item.
11. Refresh label master pada data target.
12. Jalankan validasi jumlah dan sampling.
13. Simpan ringkasan batch di tabel `*_sync_runs`.

## Checklist Sebelum Sync Produksi

- Backup database `pustaka` tersedia dan bisa direstore.
- Dump INLISLite terbaru sudah diterima lengkap.
- Encoding dump dicek, terutama perbedaan `latin1` dan `utf8mb4`.
- Koneksi database staging `inlislite_v3` bisa dibaca.
- Disk cukup untuk mirror `uploaded_files`.
- Folder `assets/uploads/inlislite` dan `storage` punya izin tulis.
- Operator sudah memilih mode sync: dry-run, import baru, update lama, atau reconcile.
- Tidak ada proses sync lain yang sedang berjalan.
- Untuk sync besar, admin diberi informasi bahwa data sedang diperbarui.

## Idempotensi dan Duplikasi

Aturan umum:

- Katalog dicocokkan melalui `books.source_id` dan `catalog_sync_maps`.
- Eksemplar dicocokkan melalui `book_items.source_id`.
- Member dicocokkan melalui `members.source_id`.
- Transaksi dicocokkan melalui `source_id` per tabel transaksi.
- Aset dicocokkan melalui source path/checksum/status migrasi.

Jika sync dijalankan berulang:

- Data yang sudah ada tidak dibuat ulang.
- Mode import baru akan skip data yang sudah mapped.
- Mode update akan memperbarui field source-owned.
- File aset yang sudah copied tidak disalin ulang kecuali status/hash berbeda dan operator memilih refresh.

## Strategi Aset

Sumber utama dari folder INLISLite:

- `uploaded_files/sampul_koleksi`
- `uploaded_files/foto_anggota`
- `uploaded_files/dokumen_isi`

Target Pustaka:

- `assets/uploads/inlislite/covers`
- `assets/uploads/inlislite/member_photos`
- `assets/uploads/inlislite/digital_files`
- `assets/uploads/inlislite/source_mirror/uploaded_files`
- `storage/ebooks/manual`
- `storage/cache/reader_pages`

Aturan:

- Aset dari INLISLite dimirror agar Pustaka bisa pindah server tanpa bergantung pada folder aplikasi INLISLite lama.
- File missing dicatat di `asset_migration_items`, bukan membuat proses sync gagal total.
- PDF untuk reader aman dipindahkan ke storage non-public bila akan dipakai sebagai ebook.
- Jangan menyimpan PDF ebook terkunci di folder public.

## Validasi Setelah Sync

Minimal validasi:

- Jumlah `catalogs` source dibandingkan `books` target yang punya `source_system = inlislite`.
- Jumlah `collections` source dibandingkan `book_items` target.
- Jumlah `members` source dibandingkan `members` target.
- Jumlah akun member aktif di `auth_user`.
- Jumlah transaksi source dibandingkan tabel transaksi target.
- Jumlah cover copied, missing, dan failed.
- Jumlah foto member copied, missing, dan failed.
- Jumlah file digital copied, missing, dan failed.
- Daftar source ID duplikat.
- Daftar item tanpa buku.
- Daftar loan item tanpa item koleksi.
- Daftar member tanpa akun login.
- Sampling manual minimal 20 buku, 20 member, dan 20 transaksi.
- Uji reader: storage langsung tetap `403`, aset non-downloadable tetap tidak punya URL PDF utuh.

## Rollback

Jika sync gagal berat:

1. Jangan menjalankan sync lanjutan.
2. Simpan log error dan ID batch.
3. Restore database `pustaka` dari backup sebelum sync.
4. Aset hasil mirror tidak perlu langsung dihapus; tandai batch gagal.
5. Jalankan ulang dry-run setelah penyebab error diperbaiki.

Jika hanya sebagian data missing:

- Jangan rollback total.
- Gunakan laporan `missing`/`failed`.
- Perbaiki file sumber atau mapping.
- Jalankan ulang mode import/update terbatas.

## Jadwal Operasional Awal

Pilot:

- Dry-run setiap kali menerima dump baru.
- Import/update katalog dan member 1 kali per hari di luar jam layanan sibuk.
- Sync transaksi harian 1 kali per hari.
- Reconcile penuh 1 kali per minggu.
- Audit aset 1 kali per bulan.

Produksi matang:

- Katalog/member incremental tiap malam.
- Transaksi harian setiap 1-3 jam jika dibutuhkan.
- Asset mirror berjalan terpisah dari sync data agar job utama tidak terlalu lama.
- Alert jika error batch lebih dari ambang yang ditentukan.

## Laporan Operator

Setiap batch sync harus menghasilkan ringkasan:

- Tanggal dan jam mulai/selesai.
- Mode sync.
- User/operator.
- Jumlah source dibaca.
- Jumlah created, updated, skipped, failed.
- Jumlah unmapped.
- Jumlah aset copied, missing, failed.
- Durasi proses.
- Link daftar error.

## Keputusan Teknis Saat Ini

- Untuk tahap berikutnya, pola yang disarankan adalah dump periodik ke staging `inlislite_v3`.
- Job sinkronisasi dibuat sebagai command/CLI CodeIgniter agar bisa dijalankan scheduler Windows atau cron server.
- UI admin tetap menyediakan tombol manual untuk dry-run dan batch terbatas.
- Data kurasi lokal tetap menjadi milik Pustaka dan tidak ditimpa oleh source INLISLite.
