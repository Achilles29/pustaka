# Stok opname dan adopsi layanan sekolah

Tanggal: 4 Oktober 2026. Kelanjutan [pemetaan fitur](PETA_FITUR_KABUPATEN_DAN_PERPUSTAKAAN.md). Pengingat WhatsApp dan pembaca digital **tidak termasuk**, sesuai permintaan pengguna.

## Yang sudah dibangun

| Menu | Kabupaten / Perpusda | Sekolah |
| --- | --- | --- |
| Stok Opname | `/library-services/stock`; pilih perpustakaan dan sumber `legacy` untuk koleksi lama. Admin terikat perpustakaan tidak bisa mengganti scope. Pengelola pusat tanpa penugasan bisa memantau sesi jejaring | Route sama, source dikunci `network`, perpustakaan dari akun |
| Label QR | Modul lama `/catalog/detail/{id}/labels` tidak diubah | `/library-services/labels`: cari barcode/judul, cetak 50 label per halaman |
| Kartu Anggota | Modul lama tetap tersedia | `/library-services/cards`: cari nama/nomor, cetak kartu lokal dengan nama, nomor, perpustakaan, status dan masa berlaku bila ada |
| Reservasi | Modul lama tetap tersedia | `/library-services/reservations`: pencatatan oleh petugas, antrean terhubung peminjaman biasa |
| Pendaftaran / perpanjangan | Modul lama tetap tersedia | `/library-services/registrations`: tautan publik dan verifikasi permohonan lokal |
| Buku Tamu Mandiri | Modul lama tetap tersedia | `/library-services/kiosk`: tautan/QR 24 jam untuk monitor/tablet di lokasi |

Menu sekolah bertambah dari 11 menjadi 17, tetap sidebar desktop/menu lipat ponsel, satu role `LIBRARY_ADMIN`. Tidak ada pembuatan akun atau penggantian password pada migrasi ini.

## Alur stok opname kedua dataset

1. Pilih lembaga dan sumber (akun sekolah otomatis). Buat nama sesi. Hanya satu sesi terbuka per lembaga/sumber; sesi tanpa eksemplar fisik ditolak.
2. Sistem menyimpan snapshot judul, barcode, lokasi, status awal dan penanda sedang dipinjam. Dataset lama dan jejaring tidak digabung atau dipindahkan kepemilikannya. Eksemplar digital/arsip dikecualikan; eksemplar jejaring yang ditarik juga dikecualikan.
3. Pindai barcode melalui scanner USB atau input manual. Kondisi: ditemukan baik/rusak, dengan catatan. Scan ulang memperbarui pengamatan eksemplar yang sama, tidak menambah hitungan. Barcode asing ditolak. Barcode ganda dalam snapshot ditolak agar tidak memilih eksemplar secara acak.
4. Rekap membedakan ditemukan baik, ditemukan rusak, belum dipindai tetapi dipinjam saat mulai, dan belum ditemukan. Sirkulasi **tidak dibekukan**; petugas wajib mencocokkan pergerakan pinjam/kembali yang terjadi selama sesi. Status awal tetap snapshot, bukan kondisi real-time.
5. Tutup atau batalkan dengan catatan wajib. Setelah ditutup, pengamatan tidak bisa diubah. Buat sesi baru bila perlu pemeriksaan ulang.
6. Unduh seluruh detail sesi melalui CSV/Excel atau cetak berita acara dengan kolom tanda tangan. Sesi terbuka ditandai draf saat dicetak. Cetakan bukan tanda tangan elektronik.

Tidak ada penetapan hilang/penghapusan otomatis atau perubahan status inventaris asli. Koreksi aset tetap memerlukan verifikasi petugas pada modul koleksi. Batas daftar sesi: 200 terbaru; detail 100 baris/halaman, ekspor seluruh snapshot. Pusat dapat melihat daftar lintas lembaga lalu membuka masing-masing sesi; rekap statistik stok opname lintas sesi belum dimasukkan ke `/reports/network`.

## Reservasi, keanggotaan dan kartu

- Reservasi **dicatat petugas**, belum reservasi mandiri melalui login anggota sekolah. Login/penautan identitas anggota tetap pekerjaan tersendiri.
- Reservasi judul fisik berlaku 7 hari, maksimal 3 aktif per anggota; antrean berdasarkan ID pembuatan. Peminjaman melayani pemesan pertama, lalu menandai reservasi terpenuhi dalam transaksi yang sama. Reservasi kedaluwarsa tidak menghalangi peminjaman. Pembatalan tidak menghapus histori.
- Formulir publik: `/jejaring/daftar/{library_id}`. Permohonan baru/perpanjangan selalu pending; tidak membuat anggota aktif atau akun login sendiri.
- Petugas memverifikasi identitas/duplikat, mengisi catatan dan tanggal berakhir sebelum menyetujui. Permohonan baru mendapat nomor `L{library}-R{request}`. Perpanjangan harus cocok nomor, nama dan tanggal lahir; anggota diblokir tidak bisa diaktifkan melalui alur ini. Biodata lama tidak ditimpa oleh formulir publik.
- Masa berlaku tidak otomatis diterapkan pada anggota lama yang belum mempunyai term. Setelah term ditetapkan, kedaluwarsa menolak pinjaman baru, perpanjangan pinjaman dan reservasi; pengembalian tetap diperbolehkan.
- Kartu/label menggunakan QR nomor anggota/barcode mentah, **bukan password, token akses atau tautan publik yang membuka identitas**. Barcode yang sama antarlembaga tetap ditafsirkan dalam scope operator. Cetak kartu belum mengadopsi editor desain kartu pusat/foto anggota; desain lokal memakai template khusus sederhana. Hasil cetak dapat disimpan sebagai PDF melalui browser.

## Buku tamu mandiri

- Tautan dibuat petugas, berlaku 24 jam, dapat dicabut. Token acak 256 bit hanya disimpan hash-nya; URL lengkap ditampilkan sekali setelah dibuat. Buka perangkat di lokasi atau pajang QR di perpustakaan.
- Formulir tidak menampilkan/pencarian daftar anggota atau daftar pengunjung. Pengunjung mengisi nomor anggota lokal atau nama tamu dan memilih satu dari enam tujuan kunjungan.
- Kanal selalu offline, termasuk tujuan Layanan digital. Tautan dapat dibagikan sehingga **bukan bukti geolokasi**. Check-in GPS bukan fitur baru pada rilis ini.
- Entri ulang anggota/nama tamu yang sama dalam lima menit ditolak. Nama sama tidak berarti identitas orang unik; petugas bisa memakai buku tamu admin untuk kasus yang perlu pemeriksaan manual.
- Public form memakai CSRF satu kali, honeypot, validasi, pembatasan IP/jam (pendaftaran 15; kiosk 300 per perpustakaan). Tidak menyimpan IP mentah pada tabel limiter. Pemakaian di jaringan bersama perlu dipantau agar limit sesuai kebutuhan.
- Admin sekolah dapat membuka layanan publik miliknya, bukan layanan lembaga lain dalam sesi pengelola. Tautan anonim tetap dapat digunakan pengunjung sesuai token/lembaga tujuan.

## Keamanan, migrasi dan pengujian

- `sql/2026-10-04c_library_services.sql`: tujuh tabel tambahan, permission `library.services` untuk ADMIN/SUPERADMIN pusat, enam menu lokal area LIBRARY melalui permission `library.workspace` yang sudah ada.
- Endpoint admin mensyaratkan session, permission, scope tetap, POST dan token satu kali untuk mutasi. Permission ekspor diperiksa terpisah. Query kepemilikan dilakukan juga di model, termasuk stok/detail/scan, review pendaftaran, reservasi dan kiosk.
- Transaksi dan kunci baris mencegah sesi stok ganda, peminjaman melewati antrean, penggandaan persetujuan, dan perubahan parsial. DDL MariaDB melakukan commit implisit; migrasi idempotent per statement, bukan klaim rollback DDL atomik.
- `php tools/setup_library_services.php` dry-run; `--apply` backup penuh dahulu. Mode `--test=/tmp/pustaka_network_test_...` hanya database fixture terpisah. Manifest privat mencatat jumlah data operasional/akun sebelum-sesudah.
- Uji: 42 regresi model/impor, 24 model layanan, 70 regresi HTTP, 48 HTTP layanan, 6 konkurensi sirkulasi, 1 konkurensi pembuatan stok = **191 pemeriksaan**. Browser: 14 kombinasi halaman/ukuran layar, 17 menu dengan satu yang aktif, QR label/kartu benar-benar dirender, formulir publik responsif, tanpa error JavaScript. Regresi browser workspace/laporan gabungan juga lulus. Deprecation PHP saat mencetak sesi dengan catatan kosong ditemukan dan diperbaiki sebelum pemeriksaan akhir.
- Pengujian fitur tulis memakai database fixture, bukan data produksi. Tidak ada anggota/koleksi/kunjungan/sesi stock uji yang dimasukkan ke database riil.
- Pemasangan database riil selesai setelah backup `/www/backup/pustaka-network/before-network-20261004-211603-29a134d1.sql.gz`. Manifest `.services-verification.json` mencatat jumlah akun/data operasional sebelum-sesudah sama. Pemeriksaan pascapasang: 17 menu lokal, 410 akun sekolah, tujuh tabel layanan baru kosong; formulir publik HTTPS 200, route stok tanpa login mengarah ke login. Tidak mengubah password akun riil untuk pengujian.
- Database fixture, runtime sementara dan server HTTP uji telah dibersihkan setelah selesai. Screenshot serta ringkasan 191 pemeriksaan disimpan privat di `/www/backup/pustaka-network/services-20261004-211603/`; fixture dapat dibuat ulang dengan skrip pengujian.

## Belum diselesaikan / keputusan operasional

1. **Pembaruan 5 Oktober — denda:** pengguna memilih pencatatan manual. Ledger nominal, pelunasan/pembebasan dan koreksi sudah dibangun, tanpa tarif/penagihan/pembayaran otomatis. Lihat [LIBRARY_EXCHANGE_AND_FINES.md](LIBRARY_EXCHANGE_AND_FINES.md).
2. **Pembaruan 5 Oktober — antarlembaga:** pengguna menyetujui peminjaman antarlembaga dengan persetujuan dua pihak dan kembali ke pemilik. Alur tersebut sudah dibangun; perpindahan aset permanen tetap tidak termasuk. Sidebar sekolah kini 19 menu.
3. Login mandiri anggota sekolah, reservasi mandiri, editor desain kartu/foto lokal, rekap stok lintas sesi, serta katalog tunggal/identitas lintas lembaga tetap pekerjaan lanjutan. Fitur yang tersedia pada tabel di atas tidak berarti seluruh modul pusat telah disalin setara penuh.
4. WhatsApp dan pembaca digital sengaja dilewati, bukan kegagalan pemasangan.
