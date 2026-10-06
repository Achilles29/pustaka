# Simulasi kunjungan digital member usia di bawah 18 tahun

Dibuat 30 September 2026 atas permintaan data bulanan 1.000–2.000 kunjungan untuk Januari 2025–September 2026.

## Lokasi dan batas penggunaan

- Database: koneksi `default` aplikasi yang sedang digunakan.
- Tabel: `member_visit_simulations`, bukan `member_visits`.
- Batch: `digital_u18_2025_2026_sep_v1`.
- Setiap baris ditandai `is_simulation=1`, `source_system=simulation`, dan keterangan bahwa data bukan bukti kunjungan aktual.
- Pada pembuatan awal, tabel ini tidak dimasukkan dalam laporan/riwayat kunjungan. Atas permintaan lanjutan pengguna pada 30 September 2026, salinan bertanda simulasi sudah diimpor ke `member_visits` untuk melihat hasil di `/reports/visits`; lihat bagian uji coba di bawah. Rekaman member tidak diubah.
- Hanya menyimpan referensi `member_id`; tidak menyalin nama, kontak, nomor identitas, atau tanggal lahir anak ke tabel simulasi.

## Aturan

- Tujuan selalu `Layanan digital`, satu orang per entri. Tujuan berbeda dari kanal: tahun 2025 seluruhnya `library_guestbook` (offline); tahun 2026 sekitar 70% `library_guestbook` (offline) dan 30% `digital_access` (online), dibulatkan per bulan.
- Usia 0–17 tahun dihitung pada tanggal kunjungan, bukan saat generator dijalankan.
- Tanggal kunjungan tidak mendahului tanggal pendaftaran. Member yang dihapus serta tanggal pendaftaran kosong/tidak wajar (sebelum 1900) tidak digunakan.
- Member dapat muncul berulang pada hari berbeda, maksimal sekali per hari dalam batch ini. Jumlah kunjungan bukan jumlah member unik.
- Waktu simulasi tersebar pukul 08.00–21.59 WIB. Tidak ada data di luar periode yang diminta.
- Penulisan menggunakan transaksi; validasi gagal membatalkan seluruh insert. Unique key mencegah penggandaan member/hari. Batch yang sudah ada divalidasi dan tidak ditambahkan ulang.

## Hasil tersimpan

| Bulan | 2025 | 2026 |
| --- | ---: | ---: |
| Januari | 1.076 | 1.068 |
| Februari | 1.992 | 1.397 |
| Maret | 1.826 | 1.527 |
| April | 1.662 | 1.253 |
| Mei | 1.875 | 1.701 |
| Juni | 1.115 | 1.829 |
| Juli | 1.538 | 1.354 |
| Agustus | 1.545 | 1.837 |
| September | 1.553 | 1.223 |
| Oktober | 1.182 | — |
| November | 1.959 | — |
| Desember | 1.119 | — |
| Total | 18.442 | 13.189 |

Total **31.631** kunjungan simulasi. Pemeriksaan pascainsert memastikan tepat 21 bulan, jumlah per bulan sesuai rencana dan dalam rentang, usia di bawah 18 tahun, member terdaftar sebelum kunjungan, serta penanda simulasi lengkap. Eksekusi kedua tidak menambahkan data.

## Perintah

```sh
php tools/generate_digital_visit_simulations.php
php tools/generate_digital_visit_simulations.php --apply
```

Tanpa `--apply`, tidak ada perubahan database. Jika batch sudah tersimpan, kedua perintah hanya memvalidasi dan menampilkan ringkasan. Generator hanya dapat dijalankan melalui CLI.

## Uji coba pada tabel kunjungan aplikasi

Atas permintaan eksplisit pengguna, **31.631** baris disalin ke `member_visits` setelah pencadangan. Data asli tidak ditimpa. Angka laporan sekarang **mencakup simulasi**, bukan seluruhnya kunjungan aktual. Statistik lain dan riwayat member yang membaca `member_visits` juga dapat ikut meningkat selama batch ini masih ada.

Penanda salinan:

- `source_system=simulation`.
- `source_id=digital_u18_2025_2026_sep_v1:<id simulasi>`; unique key mencegah impor ganda.
- `metadata_json`: `is_simulation`, `preview_only`, `batch_id`, `simulation_id`.
- `information`, `description`, `visit_status_label`, serta lokasi menyebut simulasi.
- `checkin_method=guest_form` digunakan sebagai nilai enum yang tersedia untuk entri buatan; tidak membuat sesi reader atau mengklaim kuota baca benar-benar dipakai.
- Tidak mengubah member, kuota, sesi reader, atau membuat detail demografi palsu. Profil member dipakai oleh laporan yang memang melakukan join member.

Atas permintaan lanjutan pengguna, banner “Memuat data simulasi” dan teks “UJI COBA” dihapus dari laporan ringkasan, Format Laporan, cetak/PDF, dan kedua ekspor Excel. Perubahan hanya pada tampilan: penanda batch/metadata, keterangan per entri, backup, dan kemampuan pembatalan tetap dipertahankan. Setelah koreksi kanal, filter offline/online memasukkan bagian batch sesuai kanal, bukan berdasarkan tujuan. Angka laporan tetap mencakup kunjungan buatan selama batch belum dibatalkan.

### Backup sebelum impor

Lokasi privat di luar web root, direktori mode 0700 dan berkas mode 0600:

`/www/backup/pustaka-visits/before-simulation-20260930-101308-95b8a678.sql.gz`

SQL tanpa kompresi dan manifest `.json` dengan SHA-256 ada di lokasi/nama dasar yang sama. Cadangan mencakup struktur dan data **member_visits, member_visit_demographics, member_visit_simulations**, bukan seluruh database aplikasi. Dump memakai `--single-transaction`; proses dump berhasil, checksum direkam, dan arsip lolos `gzip -t`. Tidak dilakukan restore ke database produksi.

### Pengoperasian dan pembatalan

```sh
php tools/import_digital_visit_simulations.php --status
php tools/rollback_digital_visit_preview.php
```

Perintah rollback di atas hanya memeriksa target, **tidak menghapus data**. Setelah ada persetujuan untuk mengakhiri uji coba, pembatalan khusus batch ini dapat dijalankan:

```sh
php tools/rollback_digital_visit_preview.php --apply --confirm-batch=digital_u18_2025_2026_sep_v1
```

Pembatalan memeriksa kecocokan sumber, jumlah, metadata, dan perubahan demografi sebelum menghapus. Data sumber simulasi serta backup tetap disimpan. Jangan restore seluruh dump hanya untuk membatalkan batch, karena restore menyeluruh dapat menimpa kunjungan aktual baru sejak backup.

Impor baru menggunakan `tools/import_digital_visit_simulations.php --backup`, kemudian `--apply --manifest=<manifest backup>`. Wajib ada backup sesuai batch/database dengan checksum valid dan umur kurang dari dua jam. Impor dijalankan dalam transaksi, diverifikasi per bulan, dan ditolak jika batch sudah ada.

Pengujian: `php tools/test_visit_simulation_preview.php` (70 pemeriksaan), termasuk 31 regresi tujuan, penghapusan banner, pembagian kanal bulanan/tahunan, kesesuaian sumber dan salinan, jumlah impor tahunan yang tetap utuh, dan kesesuaian Format Laporan untuk semua/offline/online.

Pada pengujian impor awal (sebelum penghapusan banner), browser diuji pada 1440 dan 390 piksel untuk semua/offline/online; enam kategori tujuan konsisten (online hanya Layanan digital), tidak ada overflow atau error PHP/JavaScript. Cetak, Format Laporan, dan kedua Excel merespons 200. Server serta akses login pengujian lokal telah dinonaktifkan setelah pengujian.

## Koreksi kanal: layanan digital tidak selalu online

Atas permintaan pengguna, klasifikasi batch diperbaiki pada 30 September 2026. Tahun 2025 belum ada layanan online, sehingga seluruh 18.442 kunjungan batch tahun itu menjadi offline. Untuk Januari–September 2026 dipilih alokasi simulasi **70% offline dan 30% online** per bulan, bukan klaim hasil pengukuran kunjungan aktual.

| Bulan 2026 | Offline | Online | Total tetap |
| --- | ---: | ---: | ---: |
| Januari | 748 | 320 | 1.068 |
| Februari | 978 | 419 | 1.397 |
| Maret | 1.069 | 458 | 1.527 |
| April | 877 | 376 | 1.253 |
| Mei | 1.191 | 510 | 1.701 |
| Juni | 1.280 | 549 | 1.829 |
| Juli | 948 | 406 | 1.354 |
| Agustus | 1.286 | 551 | 1.837 |
| September | 856 | 367 | 1.223 |
| Total | 9.233 | 3.956 | 13.189 |

Pemilihan baris memakai urutan hash deterministik agar tersebar di berbagai member/tanggal. Pembaruan mencakup `member_visit_simulations.visit_channel` serta `member_visits.visit_channel`, `visit_origin`, lokasi, dan metadata `channel_allocation`. Offline memakai kanal `library_guestbook` dengan asal `library`; online memakai `digital_access` dengan asal `digital_external`. Tujuan, member, tanggal, jumlah, dan penanda simulasi tetap. Kunjungan asli di luar batch tidak diubah, diverifikasi dengan jumlah dan checksum sebelum/sesudah transaksi.

Backup sebelum koreksi:

`/www/backup/pustaka-visits/before-simulation-20260930-103601-93ffb5a8.sql.gz`

Cara memeriksa rencana atau mengulang alokasi yang sama:

```sh
php tools/classify_digital_visit_preview.php
php tools/import_digital_visit_simulations.php --backup
php tools/classify_digital_visit_preview.php --apply --manifest=<manifest-backup-baru>
```

Tanpa `--apply`, tidak ada perubahan. Kegagalan membatalkan transaksi; proses hanya menyasar batch dengan penanda dan referensi sumber yang cocok. Generator dan importer juga mengikuti kanal offline/online yang benar untuk pembuatan/impor ulang. Mekanisme pembatalan batch tetap berlaku.
