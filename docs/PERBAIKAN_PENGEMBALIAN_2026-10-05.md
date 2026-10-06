# Perbaikan pengembalian buku — 5 Oktober 2026

## Kesimpulan

Error berhasil direproduksi pada database uji dengan trigger yang sama:

```text
1442: Can't update table 'inter_library_loans' in stored function/trigger
because it is already used by statement which invoked this stored function/trigger
```

Ini regresi integrasi **peminjaman antarlembaga** dengan sirkulasi kabupaten/legacy, bukan akibat perubahan IPLM, klasifikasi perpustakaan, atau data sekolah. Perbaikan kode telah diterapkan. Delapan trigger pengaman inventaris tetap aktif dan tidak diubah.

## Penyebab

`Loan_model::reconcile_item_availability()` sebelumnya menjalankan `UPDATE book_items` dengan subquery `NOT EXISTS` yang membaca `inter_library_loans`. Pada saat yang sama, trigger `pustaka_ill_legacy_item_update` menjalankan locking read (`SELECT ... FOR UPDATE`) terhadap `inter_library_loans`. MariaDB menolak kombinasi tersebut dalam satu statement pemicu.

Reproduksi sebelum perbaikan menghasilkan kode 1442 dan hasil `available = -1`. Fungsi lama tidak memeriksa kegagalan query ini. Selain itu, `return_loan()` sudah melakukan COMMIT pengembalian sebelum menjalankan penyelarasan seluruh inventaris, sehingga pengguna melihat error meskipun pengembalian sebenarnya sudah tersimpan.

Referensi definisi error: [MariaDB Error 1442](https://mariadb.com/docs/server/reference/error-codes/mariadb-error-codes-1400-to-1499/e1442). Akar masalah di atas dibuktikan melalui kode, trigger database, dan reproduksi lokal, bukan hanya dugaan dari pesan error.

## Dampak pada transaksi yang dilaporkan

Access log menunjukkan HTTP 500 pada endpoint pengembalian tanggal 5 Oktober 2026. Pemeriksaan database menemukan:

| ID detail pinjam | Waktu pengembalian asli (WIB) | Hasil pemeriksaan/perbaikan |
| --- | --- | --- |
| 2591 | 09:59:03 | Sudah kembali; label eksemplar diperbaiki menjadi Tersedia |
| 2589 | 09:59:15 | Sudah kembali; label eksemplar diperbaiki menjadi Tersedia |
| 2600 | 10:06:04 | Sudah kembali; status dan label sudah benar |

Tidak ada pinjaman anggota aktif lain pada ketiga eksemplar saat pemeriksaan. **Tidak dilakukan pengembalian ulang, pengubahan waktu kembali, atau penghapusan riwayat.** Dua perbaikan label dilakukan secara terarah, menggunakan lock dan verifikasi ulang. Ketiganya kini berstatus `available` / `Tersedia`.

## Perubahan implementasi

File aplikasi: `application/models/Loan_model.php`.

- Membaca ID eksemplar yang sedang ditahan antarlembaga melalui SELECT terpisah; UPDATE inventaris tidak lagi membaca tabel yang dikunci trigger dalam statement pemicu yang sama.
- Tetap mempertahankan trigger dan locking read sebagai pengaman terakhir terhadap perubahan hold secara bersamaan.
- Menyatukan detail pengembalian, header transaksi, dan status/label eksemplar dalam satu transaksi database. Jika salah satu query gagal, semuanya dibatalkan.
- Mengunci eksemplar lalu membaca ulang detail pinjam dengan lock, sehingga dua petugas tidak dapat mengembalikan transaksi yang sama dua kali.
- Saat mengembalikan buku, hanya menyelaraskan eksemplar terkait, bukan seluruh inventaris.
- Mempertahankan hold antarlembaga, pinjaman anggota lain yang masih aktif, status rusak, dan histori mentah INLISLite.
- Pengembalian tetap dapat ditutup ketika izin meminjam eksemplar dinonaktifkan setelah peminjaman; izin tersebut tidak diaktifkan kembali.
- Memeriksa hasil query dan meneruskan kegagalan sebagai pesan aplikasi, bukan keluar melalui halaman error SQL mentah. Pengaturan `db_debug` dipulihkan sesudah operasi.
- Penyelarasan manual tetap tersedia; kedua fase UPDATE-nya sekarang atomik.

## Audit kasus serupa

| Jalur | Temuan dan cakupan pemeriksaan |
| --- | --- |
| Pengembalian kabupaten `/catalog/loans/return/{id}` | Penyebab utama; diperbaiki dan diuji melalui model serta HTTP |
| Penyelarasan manual `/catalog/loans/reconcile` | Memakai fungsi bermasalah yang sama; ikut diperbaiki dan diuji HTTP dengan baris yang benar-benar memerlukan perubahan |
| Penyelarasan sesudah sinkronisasi INLISLite | `Transaction_sync_model::run_manual_sync()` memanggil fungsi yang sama; otomatis memakai perbaikan. Query bersama diuji; impor penuh dari sumber INLISLite riil sengaja tidak dijalankan |
| Sirkulasi perpustakaan lokal | Tidak memakai query bermasalah ini; alur pinjam/kembali dan isolasi akun diuji ulang |
| Peminjaman/pengembalian antarlembaga | UPDATE ledger dan inventaris merupakan statement terpisah; siklus lengkap dan pengaman inventaris diuji ulang |
| Stok opname | JOIN ke ledger memperbarui `library_stock_entries`, bukan tabel inventaris yang memiliki trigger tersebut; pengujian model lulus |
| Denda manual | Referensi ledger dalam pembacaan, bukan pola UPDATE pemicu ini; model, HTTP, dan konkurensi diuji ulang |

Penelusuran referensi `inter_library_loans` pada kode aplikasi tidak menemukan statement mutasi lain dengan pola konflik yang sama. Ini audit terarah terhadap error dan alur terkait, bukan klaim bahwa seluruh aplikasi bebas dari semua kemungkinan bug.

## Hasil pengujian

Pengujian memakai database terisolasi `pustaka_network_test_20261005_111047_c03289`, struktur produksi, delapan trigger pengaman yang sama, dan akun/transaksi uji. Tidak ada transaksi uji dimasukkan ke database produksi.

| Pengujian | Hasil |
| --- | --- |
| `tools/test_loan_return_regression.php` | 30 pemeriksaan lulus |
| `tools/test_loan_return_http.php` | 16 pemeriksaan pengembalian/penyelarasan kabupaten + 70 pemeriksaan HTTP dasar lulus |
| `tools/test_library_network.php` | 42 pemeriksaan model/import lulus |
| `tools/test_library_exchange.php` | 46 pemeriksaan model antarlembaga/denda lulus |
| `tools/test_library_exchange_concurrency.php` | 3 pemeriksaan konkurensi lulus |
| `tools/test_library_exchange_http.php` | 33 pemeriksaan antarlembaga/denda + 70 pemeriksaan HTTP dasar lulus |
| `tools/test_library_services.php` | 24 pemeriksaan model layanan lulus |
| PHP lint dan pemeriksaan whitespace perubahan model | Lulus |

Pengujian baru mencakup reproduksi SQL lama yang harus gagal dengan 1442, pengembalian lokal/legacy, pinjaman aktif ganda pada satu eksemplar, hold antarlembaga, pengembalian berulang, dua koneksi mengembalikan bersamaan, item tidak boleh dipinjam/rusak, serta kegagalan database yang sengaja disuntikkan untuk membuktikan rollback detail/header/inventaris dan rollback kedua fase penyelarasan. HTTP juga memverifikasi pesan sukses, pesan kegagalan aman, retry, dan larangan akun sekolah mengubah transaksi kabupaten.

Tes antarlembaga lama hanya memeriksa bahwa item yang ditahan tetap berstatus dipinjam; kondisi itu juga benar ketika query gagal. Tes kini diperketat untuk memeriksa keberhasilan query dan jumlah perubahan nonnegatif, sehingga error tersembunyi seperti ini tidak lagi dianggap lulus.

## Backup dan jejak perbaikan

Lokasi privat, di luar webroot:

```text
/www/backup/pustaka-network/before-network-20261005-111359-966c8e97.sql.gz
/www/backup/pustaka-network/before-network-20261005-111359-966c8e97.json
/www/backup/pustaka-network/before-network-20261005-111359-966c8e97.Loan_model.php
/www/backup/pustaka-network/before-network-20261005-111359-966c8e97.return-label-repair.json
```

Backup database dibuat sebelum perubahan, diperiksa integritas gzip, dan disertai checksum. Berkas terakhir mencatat kondisi sebelum/sesudah label serta verifikasi bahwa riwayat pinjaman tidak berubah. Tidak ada perubahan skema atau penghapusan trigger pada produksi.

Server HTTP uji sudah dihentikan; database dan direktori runtime uji sudah dihapus. Fixture dapat dipulihkan dari backup privat `/www/backup/pustaka-network/before-network-20261005-112242-00c93f28.sql.gz`; ringkasan pengujian dan hash model tersimpan pada berkas pendamping `.test-evidence.json`.

Tidak ada keputusan pengguna yang diperlukan untuk perbaikan ini. Ketiga transaksi terdampak sudah kembali, jadi tidak perlu dikembalikan ulang.
