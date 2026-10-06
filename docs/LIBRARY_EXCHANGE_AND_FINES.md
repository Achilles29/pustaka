# Peminjaman antarlembaga dan denda manual

Rilis 5 Oktober 2026, sesuai konfirmasi pengguna: peminjaman antarlembaga dahulu; denda hanya pencatatan manual tanpa tarif otomatis. Perpindahan kepemilikan permanen, WhatsApp, dan pembaca digital tidak termasuk.

## Akses

- **Pinjam Antarlembaga:** `/library-services/exchange`.
- **Denda Manual:** `/library-services/fines`.
- Menu tersedia di admin kabupaten dan sidebar sekolah (total kini 19 menu). Tetap satu role Admin Perpustakaan; tidak ada akun/tingkatan pengelola baru.
- Sekolah menggunakan perpustakaan dari sesi login. Kabupaten yang terikat satu perpustakaan tetap dibatasi penugasannya. Pengelola pusat tanpa penugasan dapat memilih konteks lembaga; daftar antarlembaga tanpa konteks berfungsi sebagai pemantauan seluruh jejaring.
- Sekolah dapat meminta koleksi publik sekolah lain atau Perpusda, **bukan membaca database anggota, koleksi draf atau pengaturan pihak lain**. Detail transaksi hanya terlihat oleh dua lembaga yang terkait dan pengelola pusat berwenang.
- Pusat menggunakan permission `library.services`; sekolah `library.workspace`. Persetujuan/penolakan pemilik pada pusat memerlukan `approve`, ekspor memerlukan `export`, mutasi lain `create`/`edit`. Pada sekolah seluruhnya dilakukan role admin yang sama. Setiap mutasi POST + CSRF satu kali.

## Alur antarlembaga

`Permintaan peminjam → Persetujuan pemilik → Pengiriman → Penerimaan peminjam → Pengiriman kembali → Konfirmasi kembali oleh pemilik`

1. Pilih konteks lembaga **peminjam**, pemilik dan dataset koleksi (`network` untuk sekolah, `legacy` untuk data lama). Cari judul/barcode; pilihan hanya koleksi fisik publik yang tersedia (maksimal 50 hasil pencarian).
2. Pilih satu eksemplar, isi tujuan/penanggung jawab dan usulan jatuh tempo (hari ini hingga 90 hari mendatang). Permintaan merupakan persetujuan awal pihak peminjam; pada tahap ini buku **belum ditahan**. Permintaan aktif yang sama dari lembaga yang sama ditolak.
3. Petugas pemilik meninjau dan menyetujui/menolak. Akun pemberi persetujuan harus berbeda dari pembuat permintaan, termasuk saat memakai konteks pusat. Persetujuan memeriksa ulang stok, pinjaman dan reservasi anggota lokal. Permintaan yang tanggal jatuh temponya sudah lewat tidak bisa disetujui.
4. Setelah disetujui, eksemplar ditahan dengan status inventaris `loaned`, walaupun belum dikirim. Ini menggunakan status lama untuk mencegah peminjaman biasa; status proses sebenarnya tetap tersedia di ledger antarlembaga. Hanya satu penahanan aktif per source+item.
5. Pemilik mencatat pengiriman dan nomor/catatan bukti. Peminjam mengonfirmasi diterima. Tiap tahap memiliki waktu, ID akun petugas, lembaga dan catatan; tidak dapat dilompati oleh pihak yang salah.
6. Peminjam mencatat pengiriman kembali. Pemilik memeriksa barang dan mengonfirmasi kembali dengan kondisi **baik** atau **rusak**. Baik membuka status tersedia; rusak tetap tidak tersedia untuk dipinjamkan. Pemilik/barcode/ID koleksi tidak berubah.
7. Sebelum dikirim, peminjam dapat membatalkan; pemilik juga dapat membatalkan persetujuan sebelum pengiriman. Setelah dikirim tidak ada pembatalan yang diam-diam membuka stok. Barang yang belum kembali/hilang tetap menjadi tanggungan aktif sampai penyelesaian fisik/administratif; penutupan kehilangan aset belum dibuat.

Catatan wajib pada semua tindakan. Cetak bukti serah-terima dari detail transaksi, atau unduh daftar CSV/Excel. Cetakan menunjukkan status saat dicetak dan kolom tanda tangan, bukan tanda tangan elektronik atau bukti penerimaan sebelum pihak penerima mengonfirmasi.

Daftar layar menampilkan 200 terbaru; ekspor memuat **seluruh transaksi dalam scope**, bukan hanya 200. Pemantauan global pusat dapat mengekspor seluruh jejaring. Tidak ada impor koleksi ke inventaris peminjam, peminjaman lanjutan ke anggota penerima, perpanjangan jatuh tempo, atau perpindahan aset permanen pada rilis ini.

## Konsistensi dengan sirkulasi dan sinkronisasi lama

- Tabel utama: `inter_library_loans` dan `inter_library_events`. Namespace source+item mencegah tabrakan ID data lama dan jejaring.
- Kunci baris eksemplar dan transaksi serta unique active hold mencegah dua persetujuan/peminjaman bersamaan.
- Delapan trigger `pustaka_ill_*` pada `books`, `book_items`, `network_books`, `network_items` melindungi inventaris yang sedang ditahan. Upaya sinkronisasi mengubah status menjadi tersedia dipertahankan sebagai loaned. Pemindahan library, parent book, barcode atau pengarsipan/penghapusan item yang ditahan ditolak. Katalog yang memiliki penahanan aktif tidak boleh diarsipkan/dihapus.
- Trigger dapat menolak baris impor/sinkron yang mencoba mengubah identitas/ownership aset aktif. Selesaikan pinjaman atau koreksi sumber impor; jangan menonaktifkan trigger untuk memaksa perubahan.
- `Loan_model::reconcile_item_availability` mengecualikan penahanan antarlembaga saat membuka kembali inventaris lama. Stok opname lama mengenali penahanan ini sebagai sedang dipinjam/dialokasikan saat snapshot. Konfirmasi fisik tetap diperlukan, khususnya pada status disetujui tetapi belum dikirim.
- Laporan sirkulasi anggota dan `/reports/network` tidak mencampur transaksi institusi ini sebagai pinjaman anggota atau sebagai anggota baru. Pemantauan institusi tersedia di menu tersendiri agar hitungannya tidak rancu.

## Denda manual kedua level

1. Pilih lembaga pencatat; pilih sumber pinjaman (`legacy`, `network`, atau `interlibrary`) dan ID transaksi pinjam, bukan ID buku. Nama peminjam dan koleksi diambil dari transaksi yang memang berada dalam scope. Anggota global tanpa transaksi milik lembaga ini tidak dapat dikenai denda dari menu tersebut.
2. Petugas mengisi nominal rupiah bilangan bulat (1–1.000.000.000) serta alasan/dasar keputusan. **Tidak ada perhitungan tarif/hari terlambat, pembuatan denda otomatis, atau pengiriman tagihan.** Petugas harus memeriksa catatan sebelumnya untuk menghindari mendenda alasan yang sama dua kali.
3. Catat pelunasan yang benar-benar sudah diverifikasi, atau pembebasan yang telah diputuskan petugas, dengan nominal, nomor bukti/keputusan dan catatan. Pembayaran/pembebasan boleh sebagian, tidak boleh melebihi sisa.
4. Kesalahan bukti dikoreksi dengan entri pembalik terhadap satu bukti asli, bukan menghapus bukti lama. Satu bukti tidak dapat dibalik dua kali. Pembalikan ini hanya koreksi ledger, **bukan pengembalian uang otomatis**.
5. Catatan denda dapat dibatalkan dengan alasan hanya setelah pelunasan/pembebasan neto nol. Nominal asli dan seluruh histori tetap disimpan; untuk salah nominal, koreksi bukti bila perlu, batalkan catatan, lalu buat catatan benar.

Sisa = nominal awal − pelunasan/pembebasan + koreksi. Denda yang dibatalkan tidak menjadi saldo terutang. Tidak ada gateway pembayaran, mutasi rekening, kas tunai otomatis, atau klaim bahwa ekspor adalah dokumen fiskal resmi. CSV/Excel memuat seluruh catatan scope; detail bukti/koreksi tersedia pada halaman masing-masing catatan.

Untuk transaksi antarlembaga, hanya perpustakaan **pemilik** yang dapat mencatat denda setelah buku dikirim; peminjam adalah nama lembaga, bukan identitas anggota lembaga lain. Ledger denda tetap privat untuk pengelola pencatat, tidak otomatis menjadi notifikasi/tagihan kepada pihak peminjam.

## Migrasi dan pengujian

- `sql/2026-10-05a_library_exchange.sql`: empat tabel tambahan, tanpa memindahkan data lama.
- `tools/library_exchange_guards.php`: definisi delapan guard inventaris, dipasang sebelum menu diaktifkan.
- `sql/2026-10-05b_library_exchange_menus.sql`: dua menu pusat dan dua menu lokal menggunakan registry/permission yang sudah ada.
- `php tools/setup_library_exchange.php` dry-run; `--apply` backup penuh dahulu. Untuk fixture gunakan `--test=/tmp/pustaka_network_test_...`. DDL MariaDB tidak dijanjikan atomik; instalasi idempotent, memeriksa delapan guard sebelum menu. Perubahan definisi guard di masa depan memerlukan migrasi upgrade eksplisit, bukan mengandalkan `IF NOT EXISTS`.
- Backup MariaDB standar menyertakan trigger. Pemulihan harus memulihkan ledger dan guard bersama inventaris. Jangan menghapus tabel/trigger saat ada penahanan aktif: sirkulasi lama hanya mengetahui status item, bukan seluruh siklus logistik antarlembaga.
- Pengujian tulis memakai database terisolasi: 42 regresi model inti, 45 model pertukaran/denda, 24 model layanan, 70 regresi HTTP, 33 HTTP pertukaran/denda, 48 HTTP layanan, 6 konkurensi sirkulasi, 1 konkurensi stok, 3 konkurensi pertukaran/denda = **272 pemeriksaan**.
- Kasus konkuren mencakup dua pemilik-approval untuk satu eksemplar, approval vs peminjaman biasa, dan dua pelunasan yang jika keduanya diterima akan melampaui saldo.
- Browser desktop 1440 px/mobile 390 px: delapan kombinasi halaman baru, 19 menu scoped, satu menu aktif, tanpa error JavaScript atau overflow halaman. Regresi layanan lama diuji terpisah. Tidak ada data pinjaman/denda contoh yang dimasukkan ke produksi.
- Pemulihan dump penuh database fixture juga diuji, terpisah dari hitungan 272 di atas: jumlah ledger/inventaris cocok, kedelapan trigger ikut dipulihkan, dan pengaman penahanan aktif tetap bekerja setelah restore. Database restore sementara dibuang setelah pemeriksaan.

## Pemasangan produksi

Migrasi dipasang pada 5 Oktober 2026 setelah backup penuh `/www/backup/pustaka-network/before-network-20261005-060552-515dcc46.sql.gz`. Manifest privat `.exchange-verification.json` mencatat jumlah akun/data operasional dan distribusi status inventaris sebelum/sesudah sama. Empat tabel ledger masih kosong setelah pemasangan; delapan guard aktif; 410 akun sekolah tetap tersedia; sidebar lokal 19 menu. Tidak ada password yang diubah atau pinjaman/denda percobaan yang dibuat di produksi.

Route HTTPS kedua modul tanpa login mengarah ke login; katalog publik tetap dapat dibuka. Uji tulis dan akses terautentikasi dilakukan pada fixture terisolasi, bukan akun admin kabupaten riil.

Database fixture, database restore, server HTTP uji dan dump/runtime sementara sudah dibersihkan. Bukti screenshot dan ringkasan pemeriksaan disimpan privat di `/www/backup/pustaka-network/exchange-20261005-060552/`. Data uji dapat dibuat ulang memakai skrip pengujian; data produksi/backup asli tidak dihapus.
