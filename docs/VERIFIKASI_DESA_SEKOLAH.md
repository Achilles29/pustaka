# Verifikasi desa sekolah dan hasil impor perpustakaan

Tanggal: 4 Oktober 2026. Sumber utama: `docs/data/sekolah_edit.xlsx`, sheet **Data Sekolah**.

**Status: selesai diimpor ke `/libraries`. Seluruh 410 sekolah memiliki kecamatan dan desa yang cocok dengan master aplikasi.**

## Hasil

- 363 SD, 39 SMP, 7 TK, dan 1 SKB; tidak ada baris sekolah yang dilewati.
- 327 sekolah memiliki perpustakaan; 83 belum memiliki perpustakaan.
- Semua entri baru berjenis Perpustakaan Sekolah, aktif, terverifikasi, dan radius 50 meter.
- Telepon, website, email, dan foto dikosongkan.
- Jam layanan default: Senin–Jumat 08.00–15.00.
- Nama, PIC, alamat, serta latitude/longitude mengikuti Excel. Pencarian internet hanya melengkapi desa, tidak mengganti koordinat Excel.
- Kode SKB `P9963098` dipertahankan sebagai teks.
- Deskripsi sekolah yang memiliki perpustakaan: `Perpustakaan Sekolah <nama sekolah>`; jika belum memiliki perpustakaan, deskripsi hanya nama sekolah. Fasilitas masing-masing `perpustakaan` atau `belum ada perpustakaan`.

## Dasar verifikasi

Pada pemeriksaan awal, 31 desa belum pasti. Pengguna kemudian mengonfirmasi tiga SD SUMBERJO sebagai **Sumberejo** di Kecamatan Rembang dan SD LABUHAN sebagai **Labuhan Kidul** di Kecamatan Sluke. Pengguna juga meminta seluruh TK/SKB diikutkan serta lokasi SMP dicari melalui sumber publik.

Semua 39 SMP dan 8 TK/SKB diperiksa berdasarkan NPSN pada **Referensi Data Pendidikan Kemendikdasmen**. Tautan sumber per sekolah tercantum di bawah. Nama desa mengikuti ejaan master aplikasi; `SUMBERJO` pada sumber dipadankan dengan `Sumberejo` sesuai konfirmasi pengguna.

Pemetaan mesin: [sekolah_village_verified.json](data/sekolah_village_verified.json). Tidak ada nama desa yang masih menunggu keputusan.

## SD yang dikonfirmasi pengguna

| NPSN | Nama sekolah | Kecamatan (ID) | Desa/kelurahan (ID) | Dasar |
| --- | --- | --- | --- | --- |
| 20315759 | SD NEGERI 1 SUMBERJO | Rembang (10) | Sumberejo (196) | Konfirmasi pengguna |
| 20315760 | SD NEGERI 2 SUMBERJO | Rembang (10) | Sumberejo (196) | Konfirmasi pengguna |
| 20315761 | SD NEGERI 3 SUMBERJO | Rembang (10) | Sumberejo (196) | Konfirmasi pengguna |
| 20316007 | SD NEGERI 1 LABUHAN | Sluke (13) | Labuhan Kidul (265) | Konfirmasi pengguna |

## SMP, TK, dan SKB — referensi resmi

| NPSN | Nama sekolah | Kecamatan (ID) | Desa/kelurahan (ID) | Dasar |
| --- | --- | --- | --- | --- |
| 20315731 | SMP NEGERI 1 SUMBER | Sumber (1) | Sumber (17) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315731) |
| 20315719 | SMP NEGERI 1 BULU | Bulu (2) | Jukung (21) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315719) |
| 20315732 | SMP NEGERI 2 BULU | Bulu (2) | Warugunung (34) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315732) |
| 20315720 | SMP NEGERI 1 GUNEM | Gunem (3) | Sendangmulyo (44) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315720) |
| 20315717 | SMP NEGERI 2 GUNEM | Gunem (3) | Tegaldowo (47) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315717) |
| 20315727 | SMP NEGERI 1 SALE | Sale (4) | Mrayun (56) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315727) |
| 20330108 | SMP NEGERI 3 SATU ATAP SALE | Sale (4) | Tengger (63) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20330108) |
| 20338566 | SMP NEGERI 4 SATU ATAP SALE | Sale (4) | Ukir (64) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20338566) |
| 20315704 | SMP NEGERI 2 SALE | Sale (4) | Wonokerto (65) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315704) |
| 20315705 | SMP NEGERI 2 SARANG | Sarang (5) | Lodanwetan (80) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315705) |
| 20315728 | SMP NEGERI 1 SARANG | Sarang (5) | Sendangmulyo (85) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315728) |
| 20338584 | SMP NEGERI 3 SATU ATAP SARANG | Sarang (5) | Tawangrejo (87) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20338584) |
| 20315706 | SMP NEGERI 2 SEDAN | Sedan (6) | Sidomulyo (108) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315706) |
| 20315729 | SMP NEGERI 1 SEDAN | Sedan (6) | Sidorejo (109) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315729) |
| 20315711 | SMP NEGERI 3 PAMOTAN | Pamotan (7) | Bangunrejo (111) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315711) |
| 20315701 | SMP NEGERI 2 PAMOTAN | Pamotan (7) | Kepohagung (116) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315701) |
| 20315724 | SMP NEGERI 1 PAMOTAN | Pamotan (7) | Pamotan (122) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315724) |
| 20315707 | SMP NEGERI 2 SULANG | Sulang (8) | Seren (150) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315707) |
| 20315730 | SMP NEGERI 1 SULANG | Sulang (8) | Sulang (152) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315730) |
| 20315716 | SMP NEGERI 2 KALIORI | Kaliori (9) | Gunungsari (160) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315716) |
| 20315721 | SMP NEGERI 1 KALIORI | Kaliori (9) | Tambakagung (173) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315721) |
| 20315703 | SMP NEGERI 2 REMBANG | Rembang (10) | Kabongan Lor (180) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315703) |
| 20315712 | SMP NEGERI 3 REMBANG | Rembang (10) | Kabongan Lor (180) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315712) |
| 20315733 | SMP NEGERI 6 REMBANG | Rembang (10) | Ngotet (187) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315733) |
| 20315714 | SMP NEGERI 5 REMBANG | Rembang (10) | Pandean (189) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315714) |
| 20315713 | SMP NEGERI 4 REMBANG | Rembang (10) | Tritunggal (200) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315713) |
| 20315726 | SMP NEGERI 1 REMBANG | Rembang (10) | Magersari (209) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315726) |
| 20315702 | SMP NEGERI 2 PANCUR | Pancur (11) | Sidowayah (228) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315702) |
| 20315725 | SMP NEGERI 1 PANCUR | Pancur (11) | Wuwur (233) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315725) |
| 20315709 | SMP NEGERI 3 KRAGAN | Kragan (12) | Kendalagung (239) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315709) |
| 20315722 | SMP NEGERI 1 KRAGAN | Kragan (12) | Kragan (240) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315722) |
| 20338565 | SMP NEGERI 4 SATU ATAP KRAGAN | Kragan (12) | Sendang (247) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20338565) |
| 20315715 | SMP NEGERI 2 KRAGAN | Kragan (12) | Sumbergayam (251) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315715) |
| 20330107 | SMP NEGERI 2 SATU ATAP SLUKE | Sluke (13) | Bendo (261) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20330107) |
| 20330109 | SMP NEGERI 3 SATU ATAP SLUKE | Sluke (13) | Labuhan Kidul (265) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20330109) |
| 20330110 | SMP NEGERI 1 SLUKE | Sluke (13) | Sluke (273) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20330110) |
| 20315710 | SMP NEGERI 3 LASEM | Lasem (14) | Babagan (275) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315710) |
| 20315723 | SMP NEGERI 1 LASEM | Lasem (14) | Gedongmulyo (280) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315723) |
| 20315700 | SMP NEGERI 2 LASEM | Lasem (14) | Sendangasri (289) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315700) |
| 69804226 | TK NEGERI PEMBINA | Pamotan (7) | Pamotan (122) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/69804226) |
| 69804463 | TK NEGERI PEMBINA | Sulang (8) | Sulang (152) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/69804463) |
| 69949998 | TK NEGERI 1 KALIORI | Kaliori (9) | Tambakagung (173) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/69949998) |
| 69804338 | TK NEGERI 1 REMBANG | Rembang (10) | Kabongan Kidul (179) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/69804338) |
| 69945869 | TK NEGERI 2 REMBANG | Rembang (10) | Sidowayah (204) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/69945869) |
| 69804235 | TK NEGERI PEMBINA | Pancur (11) | Sumberagung (229) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/69804235) |
| 69959013 | TK NEGERI 1 KRAGAN | Kragan (12) | Sudan (250) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/69959013) |
| P9963098 | SKB REMBANG | Rembang (10) | Sumberejo (196) | [Kemendikdasmen](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/P9963098) |

## Urutan input

SD → SMP → TK → SKB. Di dalam setiap jenjang: ID kecamatan terkecil → ID desa terkecil → nama sekolah → NPSN.

| Jenjang | Jumlah | ID pertama | ID terakhir |
| --- | ---: | ---: | ---: |
| SD | 363 | 4 | 728 |
| SMP | 39 | 730 | 806 |
| TK | 7 | 808 | 820 |
| SKB | 1 | 822 | 822 |

Urutan ini adalah urutan penyimpanan/ID saat impor. Tampilan daftar `/libraries` tetap memakai pengurutan nama bawaan aplikasi. ID mengikuti auto-increment database yang ada; tidak dilakukan penomoran ulang.

## Backup dan pengujian

Backup privat sebelum impor (di luar web root):

`/www/backup/pustaka-libraries/before-school-import-20261004-161739-03b255a4.sql.gz`

Manifest `.json` dan jurnal `.import.json` berada pada nama dasar yang sama. Backup memuat `libraries` dan `library_photos`; arsip lolos pemeriksaan gzip dan memiliki SHA-256. Jurnal mencatat setiap ID baru, NPSN, baris Excel, kecamatan/desa, serta metode pencocokan.

Impor dalam satu transaksi. Setiap nilai hasil penyimpanan dibandingkan dengan rencana; perpustakaan dan foto yang sudah ada diperiksa tidak berubah. Total akhir 411 perpustakaan: 1 entri lama + 410 entri baru. Tidak ada foto yang ditambahkan.

```sh
php tools/import_school_libraries.php
php tools/import_school_libraries.php --verify
```

Perintah pertama hanya pemeriksaan tanpa perubahan; `--verify` membandingkan 9.020 nilai field dari 410 entri dengan Excel dan pemetaan desa serta memeriksa urutan ID dan foto. Impor ulang dengan `--apply` ditolak jika NPSN sudah ada, sehingga tidak menambah duplikat atau menimpa data.
