# Pemetaan fitur kabupaten dan perpustakaan

Audit kode, registry/menu, dan skema database: 4 Oktober 2026. Ini pemetaan keberadaan fitur, bukan pernyataan bahwa seluruh modul lama sudah aman diberikan kepada sekolah.

**Pembaruan lanjutan 4 Oktober 2026:** stok opname kedua dataset, label QR/kartu sekolah, reservasi petugas, pendaftaran/perpanjangan lokal, dan buku tamu mandiri telah dibangun. Matriks di bawah merekam baseline audit sebelum tahap tersebut; status terbaru dan batas implementasinya ada di [LIBRARY_SERVICES.md](LIBRARY_SERVICES.md). Denda/layanan antarlembaga belum diaktifkan sambil menunggu keputusan alur; WhatsApp dan pembaca digital dilewati sesuai permintaan.

## Dua kewenangan di kabupaten

Pembaruan 5 Oktober 2026: peminjaman antarlembaga dan ledger denda manual kedua level telah dilanjutkan sesuai konfirmasi pengguna. Detail implementasi/batas fitur: [LIBRARY_EXCHANGE_AND_FINES.md](LIBRARY_EXCHANGE_AND_FINES.md). Status “belum ada” pada matriks baseline di bawah dibaca bersama pembaruan rilis ini.

**Pengelola kabupaten** membina dan memantau seluruh jejaring. **Operator Perpusda** menjalankan perpustakaannya sendiri: katalog, anggota, peminjaman, stok opname, dan seterusnya. Sekolah menjalankan operasional yang sejenis, tetapi hanya untuk data sekolahnya. Tidak ditambahkan tingkatan role pengelola di dalam sekolah; tetap satu Admin Perpustakaan, bisa beberapa akun setara.

Jadi stok opname perlu ada untuk **koleksi Perpusda dan koleksi sekolah**. Kabupaten juga perlu rekap hasil stok opname jejaring. Itu bukan alasan memberi sekolah akses laporan semua lembaga atau menyalin dua aplikasi terpisah.

## Matriks fitur berdasarkan hasil audit

| Fitur | Kabupaten / modul lama | Admin sekolah / jejaring | Arah pengembangan |
| --- | --- | --- | --- |
| Akun pengelola, beberapa admin per lembaga | RBAC pusat sudah ada | Satu role Admin Perpustakaan dan multi-admin sudah ada | Pertahankan; pusat mengatur penugasan, sekolah tidak dapat memberi role global |
| Katalog, eksemplar, pinjam/kembali/perpanjang | Sudah ada `/catalog`, `/catalog/loans` | Sudah ada di workspace | Lengkapi kesetaraan fitur tanpa mengubah kepemilikan data lama |
| Stok opname bersesi, scan, selisih, berita acara | **Belum ditemukan** controller/model/tabel sesi stok opname | **Belum ada** | Bangun untuk keduanya: satu mesin stok opname dengan adaptor dataset lama/jejaring dan batas perpustakaan |
| Cetak label eksemplar dan QR | **Sudah ada** `Catalog::print_item_labels`, `/catalog/detail/{id}/labels`; QR mengarah ke detail eksemplar dan label memuat nomor barcode | **Belum ada** | Adaptasi template cetak dan QR ke eksemplar lokal. Barcode batang massal bukan fitur yang sudah dibuktikan tersedia |
| Reservasi / request buku | **Sudah ada** `/catalog/requests`, `book_requests`, alur reservasi eksemplar dan peminjaman dari request | **Belum ada** | Adaptasi model reservasi setelah tersedia identitas/login anggota lokal; selalu per perpustakaan |
| Login dan layanan mandiri anggota | **Sudah ada** `/user/dashboard`, kartu, akun, katalog/request | Anggota lokal baru dataset, **belum otomatis akun login** | Desain penautan identitas dan keanggotaan lokal; jangan mencocokkan hanya berdasarkan nama/NIS |
| Kartu anggota digital/cetak dan desain | **Sudah ada** `/members/cards`, `Members::card_print`, `/user/member-card` | **Belum ada** | Gunakan mesin desain/cetak dengan identitas perpustakaan dan token anggota lokal |
| Pendaftaran online, verifikasi dan perpanjangan anggota | **Sudah ada** `/membership/register`, `/members/registrations`, `/members/renewals` | Input/impor anggota tersedia; layanan pendaftaran/renewal lokal **belum ada** | Adaptasi alur dengan pemilihan perpustakaan, persetujuan dan status lokal |
| Buku tamu, monitor, QR check-in | **Sudah ada** `/guestbook/monitor`, `/guestbook/checkin/{token}` | Input kunjungan oleh admin sudah ada; monitor/QR mandiri **belum ada** | Adaptasi pencarian anggota dan token kiosk yang terikat ke perpustakaan, tanpa membuka anggota global |
| Layanan digital berupa file dan pembaca | **Sudah ada** `/reader/assets`, pembaca/stream, aturan akses dan audit | Metadata dan tautan HTTPS sudah ada; unggah/pembaca lokal **belum ada** | Adaptasi penyimpanan, pembaca, izin aset dan pencatatan akses. Jangan memberi akses pengelolaan semua aset pusat |
| Pojok baca GPS, token, kuota | **Sudah ada** `/user/reading-checkin`, reading points dan sesi baca | Pengelolaan layanan titik baca lokal **belum tersedia** | Adaptasi pembatasan lokasi/kuota dan atribusi kunjungan; konfigurasi sensitif tetap pusat |
| Keterlambatan dan denda | Status/hari terlambat dan pengingat ada; **transaksi denda belum ditemukan** | Status terlambat ada; **denda belum ada** | Mesin denda untuk keduanya hanya jika kebijakan diberlakukan; default tidak memungut denda |
| Notifikasi WhatsApp | **Sudah ada** `/wa`, antrean, template, pengingat peminjaman | Pengingat lokal **belum ada** | Adaptasi pengiriman terikat anggota/loan lokal. Koneksi gateway, rahasia dan administrasi pengiriman tetap pusat |
| Laporan operasional lokal dan CSV/Excel | Sudah ada laporan kunjungan, klasifikasi pinjam, anggota, pertumbuhan koleksi | Ringkasan operasional, bulanan dan CSV/XLSX sudah ada | Adaptasi laporan klasifikasi/demografi/pertumbuhan yang relevan ke sekolah |
| Rekap gabungan kabupaten + jejaring | **Dikerjakan pada perubahan ini**: `/reports/network` | Tidak diberikan; sekolah tetap laporan lokal | Filter lembaga, jenis, wilayah, sumber, tahun/bulan; sumber lama/jejaring dan data belum terpetakan dijelaskan |
| Identitas anggota unik lintas perpustakaan | **Belum ada penautan lintas dataset** | **Belum ada** | Memerlukan verifikasi dan kebijakan data. Jumlah catatan anggota tidak boleh disebut jumlah orang unik |
| Pencarian katalog tunggal kabupaten + jejaring | `/katalog` dan `/jejaring/katalog` masih dua pencarian | Publikasi katalog lokal sudah ada | Buat satu pencarian dengan penanda sumber dan pemilik, bukan menggabungkan ID atau judul secara sembrono |
| Pinjam/kembali/perpindahan aset antarperpustakaan | **Belum ada alur terpadu** | **Belum ada** | Fitur bersama dengan persetujuan kedua lembaga, jejak perpindahan dan tanggung jawab aset |
| Sinkron/migrasi INLIS | Integrasi pusat sudah ada, `Catalog::run_sync`, `Members::run_sync` | Impor Excel ada; **migrasi histori INLIS masing-masing sekolah belum ada** | Adaptasi importer dengan namespace sumber per lembaga; jangan menimpa histori atau ID pusat |
| Profil, foto, wilayah, GPS, verifikasi | Pengelolaan direktori `/libraries` sudah ada | Profil dasar dapat diedit; identitas/wilayah/GPS/verifikasi masih pusat; foto lokal belum ada | Pertahankan verifikasi pusat; bila perlu tambah usulan perubahan dan galeri lokal yang tervalidasi |
| Backup, RBAC, konfigurasi gateway, audit lintas lembaga | Sudah ada administrasi pusat | Tidak diberikan dan bukan kekurangan fitur sekolah | Tetap pusat; pemulihan satu lembaga membutuhkan prosedur yang tidak merusak lembaga lain |
| Navigasi operasional sekolah | Sidebar pusat sudah ada | **Diperbaiki pada perubahan ini**: menu vertikal desktop, menu lipat pada ponsel | Registry `sys_menu` area `LIBRARY`; tidak menggunakan tab horizontal di atas konten sekolah |

Koreksi terhadap penjelasan sebelumnya: label eksemplar/QR, reservasi, kartu anggota dan pembaca digital **bukan belum ada di seluruh aplikasi**. Fitur tersebut sudah ada pada modul kabupaten, tetapi belum diadopsi pada workspace sekolah.

## Yang seharusnya tersedia di kabupaten tetapi masih kurang

1. Stok opname operasional Perpusda, serta rekap pemeriksaan seluruh jejaring.
2. Satu pencarian katalog lintas sumber dan rekonsiliasi bibliografi. Laporan baru mengelompokkan ISBN valid, tetapi tidak menggabungkan data katalog.
3. Penautan identitas anggota lintas perpustakaan dengan verifikasi; bukan deduplikasi nama.
4. Alur perpindahan koleksi dan pinjam antarperpustakaan.
5. Denda/pembayaran jika kebijakan memerlukannya; bukan sekadar kolom hari terlambat.
6. Pengelolaan sumber impor/sinkron banyak lembaga dan pemulihan per lembaga yang teruji.

Laporan gabungan ditutup pada perubahan ini, dengan batas definisi data sebagaimana [NETWORK_REPORTS.md](NETWORK_REPORTS.md).

## Urutan implementasi yang disarankan

1. **Perubahan ini:** laporan gabungan kabupaten, sidebar sekolah, serta pemetaan kesenjangan yang terverifikasi.
2. **Operasional inventaris:** stok opname untuk Perpusda dan sekolah; adaptasi label QR sekolah; hasilnya direkap pusat.
3. **Pelayanan lokal:** kartu anggota sekolah, pendaftaran/verifikasi lokal, monitor/QR buku tamu dan laporan lokal tambahan.
4. **Layanan mandiri/digital:** identitas/login anggota, reservasi, pembaca dan aset digital lokal, pengingat terikat perpustakaan.
5. **Integrasi jejaring:** pencarian tunggal, penautan identitas, impor histori tiap lembaga dan layanan antarperpustakaan.

Status matriks bukan janji bahwa seluruh fitur lanjutan diimplementasikan sekaligus. Masing-masing tahap perlu migrasi tambahan, backup, pengujian kedua dataset dan uji penolakan akses lintas lembaga.
