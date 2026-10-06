# Laporan gabungan kabupaten dan sidebar sekolah

Tanggal: 4 Oktober 2026. Matriks pengembangan: [PETA_FITUR_KABUPATEN_DAN_PERPUSTAKAAN.md](PETA_FITUR_KABUPATEN_DAN_PERPUSTAKAAN.md).

## Akses dan penggunaan

- Route `/reports/network`, menu **Laporan & Analitik → Gabungan Perpustakaan**.
- Untuk `SUPERADMIN` dan `ADMIN` kabupaten, memakai permission baru `reports.network`. View dan export diperiksa terpisah. Tidak tersedia untuk `LIBRARY_ADMIN` atau `USER`, termasuk melalui URL controller langsung.
- Session `ADMIN` lama mungkin perlu logout/login untuk memperbarui cache permission. Akun sekolah mengambil izin terkini pada tiap request.
- Filter tahun/bulan transaksi, sumber (kabupaten/data lama, jejaring, gabungan), perpustakaan, jenis, status lembaga, kecamatan, desa, pencarian lembaga/wilayah. Dropdown desa mengikuti kecamatan.
- Rekap dapat dikelompokkan per perpustakaan, jenis, kecamatan, atau desa. Tampilan tabel memiliki pagination 10/25/50/100; ekspor selalu seluruh hasil filter, bukan hanya halaman aktif.
- CSV dan XLSX asli, ikon unduh konsisten, CSV biru dan Excel hijau. Unduhan berisi tabel rekap yang sedang difilter/dikelompokkan; bukan ekspor identitas anggota atau transaksi pribadi.
- Tren bulanan dan rincian kontribusi kedua sumber tersedia di halaman laporan.

## Definisi indikator

| Indikator | Sumber/aturan |
| --- | --- |
| Judul lokal | Satu judul per pemilik perpustakaan. Buku legacy yang eksemplarnya tersebar di dua perpustakaan dihitung pada masing-masing pemilik |
| Rekaman katalog berbeda | Kunci namespace sumber + ID buku. Buku legacy yang sama tidak digandakan karena muncul di dua lembaga |
| ISBN valid berbeda | ISBN dibersihkan dari spasi/strip, checksum diperiksa; ISBN-10 dikonversi ke ISBN-13. Ini kelompok identifier bibliografi, bukan klaim jumlah karya unik atau pemindahan metadata |
| Tanpa ISBN valid | Rekaman tetap terpisah berdasarkan sumber/ID; tidak digabung hanya karena judul sama |
| Fisik/digital/hibrida | Data lama mengikuti penanda jenis/media eksemplar dan aset digital aktif seperti laporan katalog lama. Jejaring mengikuti `network_books.format`. Hibrida masuk fisik sekaligus digital, dikoreksi saat rekonsiliasi total |
| Eksemplar fisik | Tidak memasukkan record Ebook/digital sebagai salinan fisik; katalog dan eksemplar arsip tidak dihitung |
| Catatan keanggotaan | Record nonarsip `members` + `network_members`, **bukan jumlah orang unik lintas perpustakaan**. Nomor/NIS/nama yang sama tidak dianggap identitas yang sama |
| Pinjam | `loan_transaction_items.loan_date` dan `network_loans.loaned_at` dalam periode. Unitnya eksemplar dipinjam, bukan jumlah header `loan_transactions` |
| Kembali | `COALESCE(actual_return_at,local_return_at)` legacy dan `network_loans.returned_at` dalam periode; tidak dibatasi tanggal pinjam harus pada periode yang sama |
| Pinjaman aktif/terlambat | Posisi **saat laporan dibuka**, bukan snapshot historis pada akhir tahun. Status aktif legacy mengikuti predicate `Loan_model`: belum kembali dan `loan_status=LOAN` |
| Kunjungan offline/online | Legacy memakai `visitor_count`; hanya channel `member_dashboard`/`digital_access` online, sejalan laporan lama. Jejaring satu orang per entri memakai `channel`. Tujuan Layanan digital tidak menentukan kanal |
| Entri kunjungan | Jumlah baris, terpisah dari jumlah orang dalam rombongan |

Persamaan format: fisik + digital − hibrida + belum diketahui = judul lokal. Rekap kelompok, kontribusi sumber dan total seluruh filter harus cocok.

Koleksi/anggota/pinjaman aktif adalah posisi kini. Hanya transaksi pinjam, kembali dan kunjungan yang mengikuti filter tahun/bulan. Laporan ini belum menyimpan snapshot inventaris historis.

## Belum terpetakan bukan otomatis Perpusda

`members` legacy tidak memiliki `library_id`, sehingga anggota lama ditempatkan pada **Belum terpetakan**. Alamat kecamatan/desa anggota adalah tempat tinggal, bukan bukti lembaga pemilik. Demikian pula eksemplar/kunjungan tanpa relasi library valid serta buku tanpa eksemplar tidak otomatis ditetapkan ke Perpusda.

Filter lembaga/wilayah tertentu hanya menghitung data yang benar-benar terpetakan. Pilihan **Hanya belum terpetakan** tersedia untuk memeriksa sisa data; tidak ada mutasi/migrasi pemilik oleh laporan ini. Penetapan pemilik lama memerlukan proses rekonsiliasi tersendiri.

Kunjungan dan pinjaman lama yang masuk laporan adalah record yang ada pada database saat ini, bukan histori yang dibuat ulang. Header transaksi tanpa detail dan pengembalian tanpa tanggal yang dapat dipakai tidak ditebak menjadi transaksi periode tertentu.

## Sidebar sekolah

- `sys_menu` area **LIBRARY**, tetap memakai registry halaman `library.workspace`, bukan sidebar hardcoded atau role baru.
- 11 menu: Dashboard, Katalog Buku, Eksemplar, Anggota Lokal, Peminjaman, Buku Tamu, Laporan, Profil Perpustakaan, Aturan Peminjaman, Admin Perpustakaan, Ganti Password.
- Saat login sebagai Admin Perpustakaan, desktop memakai sidebar vertikal. Tab navigasi horizontal sekolah dihilangkan. Ponsel memakai tombol pembuka menu lipat yang sama dengan layout utama.
- Tambah/edit/impor buku menyorot menu Katalog Buku; pola yang sama untuk anggota/eksemplar. Dashboard tidak ikut tersorot pada semua subhalaman.
- Navigasi admin kabupaten tetap terpisah. Superadmin yang membuka perpustakaan melalui ruang pemantauan pusat tetap mempunyai navigasi pusat; menu lokal di atas adalah untuk sesi akun Admin Perpustakaan.
- Area LIBRARY disimpan dan dibaca dari registry yang sama; editor sidebar pusat yang lama masih berfokus pada area MAIN. Pengaturan area LIBRARY melalui editor visual belum ditambahkan.

## Implementasi dan verifikasi

- Model read-only: `Network_report_model`. Query mengagregasi tiap sumber secara terpisah untuk menghindari perkalian baris saat JOIN. Pembacaan laporan memakai transaksi agar pembacaan agregat konsisten pada isolation default database.
- Controller: `Network_reports`; view `reports/network.php`; stylesheet `assets/css/network-report.css`.
- Migrasi additive DML: `sql/2026-10-04b_network_reports_sidebar.sql`. Hanya menambah registry, permission laporan pusat dan menu; tidak memindahkan data, mengganti password, atau membuat akun lagi.
- Jalankan `php tools/setup_network_reports_sidebar.php` untuk dry-run; `--apply` melakukan backup seluruh database sebelum pemasangan. Mode uji `--test=/tmp/pustaka_network_test_...` hanya menerima database fixture terpisah.
- Uji model laporan: `tools/test_network_reports.php` (**93 pemeriksaan**).
- Uji HTTP `tools/test_network_reports_http.php`: **36 pemeriksaan laporan/sidebar**, ditambah **70 regresi autentikasi/operasional**.
- Regresi model/impor perpustakaan: **42 pemeriksaan**. Jumlah keseluruhan pengujian otomatis tersebut **241 lulus**, ditambah browser desktop 1440 px/mobile 390 px: 11 menu, satu menu aktif yang benar, tanpa tab sekolah, menu lipat berfungsi, tanpa error JavaScript atau overflow halaman.
- Pemeriksaan read-only data riil awal: 14.745 judul lokal/rekaman katalog, 22.928 eksemplar fisik; angka format cocok dengan laporan katalog lama. Ini bukan data fixture yang dimasukkan ke produksi.
- Migrasi sudah dipasang pada database riil tanggal 4 Oktober 2026 setelah backup penuh `/www/backup/pustaka-network/before-network-20261004-202032-0c88302c.sql.gz` (lokasi privat, bukan unduhan publik).
- Verifikasi pascapemasangan: 11 menu LIBRARY, 410 akun sekolah tetap tersedia, permission laporan hanya ADMIN/SUPERADMIN; tabel operasional jejaring tetap kosong. Route HTTPS laporan tanpa login mengarah ke login. Pengujian HTTP terautentikasi kedua level dilakukan pada database fixture terisolasi, bukan menggunakan akun admin kabupaten riil.

Stok opname dan fitur adopsi lainnya **belum dikerjakan dalam perubahan laporan/sidebar ini**. Kebutuhan dan urutannya ada pada matriks pengembangan.
