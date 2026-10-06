# Hasil task3 — 5 Oktober 2026

Permintaan: [task3.md](task3.md). Bagian persandingan, populasi manual, penyederhanaan bukti, serta pencarian perpustakaan publik sudah dikerjakan. **Aktivasi/login otomatis setelah memilih sekolah belum diaktifkan**: membutuhkan keputusan verifikasi kepemilikan, bukan sekadar modal konfirmasi.

## 1. Satu Excel persandingan

Buka **[PERSANDINGAN_DATABASE_PENDATAAN_TASK3.xlsx](PERSANDINGAN_DATABASE_PENDATAAN_TASK3.xlsx)**. Ini berkas utama pemeriksaan, menggantikan pemakaian beberapa berkas task2 untuk menyandingkan unit.

- Database di kiri (A–N), pendataan di kanan (O–AB), status/keputusan/catatan di AC–AH.
- Tampilan awal menampilkan ID, NPSN, nama dan kecamatan kedua sisi, status serta catatan. Kolom detail disembunyikan agar tidak terlalu lebar; untuk melihat alamat, desa, NPP, GPS, jenis/subjenis dan atribut lain, pilih semua kolom lalu **Unhide / Tampilkan kolom**.
- Pasangan kuat sejajar. Kandidat belum pasti juga sejajar tetapi berwarna kuning, disertai catatan dan kandidat ID alternatif. **Sejajar bukan berarti seluruh atribut sudah disetujui.**
- Unit satu sisi memiliki sisi seberangnya kosong, dengan warna berbeda. Tidak ada nama atau angka sumber yang dihapus.
- Header dibekukan, tersedia filter, NPSN disimpan sebagai teks agar nol awal tidak hilang. Sheet kedua menjelaskan warna dan cara menjawab.

| Status | Jumlah baris | Arti |
| --- | ---: | --- |
| DIKONFIRMASI_TASK3 | 3 | Dua pasangan konflik NPSN dan Perpusda sesuai jawaban |
| COCOK | 364 | Pasangan kuat berdasarkan identitas; atribut tetap perlu diperiksa |
| COCOK_GANDA | 8 | Empat pasangan ganda; perlu menentukan sumber utama |
| PERLU_VERIFIKASI | 65 | Kandidat belum disahkan |
| HANYA_DATABASE | 27 | Sisi pendataan kosong |
| HANYA_PENDATAAN | 394 | Sisi database kosong |

Total **861 baris persandingan**, mencakup seluruh **834 baris sumber tepat sekali** dan **411 ID master minimal sekali**. Satu master bisa muncul beberapa kali untuk kandidat/ganda. Sebanyak 370 master punya pasangan kuat/eksplisit; 41 belum. Angka 27 HANYA_DATABASE bukan 41 karena 14 master lainnya sudah ditampilkan sebagai kandidat.

Ada **18 sel NPSN sumber berbeda** ditandai merah, termasuk yang pasangan unitnya masih kandidat. Database/Dapodik menjadi acuan **setelah unitnya dipastikan sama**; NPSN kandidat tidak boleh diterapkan sebelum pasangan diverifikasi.

Pasangan eksplisit:

- Sumber 352595 → library 92 (SD Negeri Demaan).
- Sumber 210305 → library 732 (SMP Negeri 1 Bulu).
- Sumber 50964 → library 2 (Perpusda); NPP sumber `3317103E1000003` dicatat pada persandingan.

**Tidak ada import unit baru, penggabungan/penghapusan, perubahan NPSN/NPP/GPS master, atau penambahan ke file sumber asli.** Tahap berikutnya menunggu konfirmasi Excel sesuai instruksi Anda. Angka `pendataan.xls` diperlakukan sebagai data terupdate, **bukan otomatis data tahun 2025**, sehingga tidak disalin ke statistik IPLM 2025.

Ringkasan terstruktur dan hash berkas: [ringkasan-persandingan-task3.json](ringkasan-persandingan-task3.json). Generator read-only: `tools/build_iplm_task3_comparison.php`.

## 2. Populasi otomatis atau manual

Pada `/iplm/settings` → **Periode & populasi**:

- **Otomatis** memakai total master aktif dalam cakupan IPLM. Saat penerapan: 403 (363 SD, 39 SMP, 1 Perpusda); TK/SKB tetap dikecualikan.
- **Manual** memungkinkan admin kabupaten menetapkan bilangan bulat positif (maksimal 10 juta) dengan alasan/sumber wajib.
- Pilihan mode, angka manual, alasan dan total master pembanding tersimpan; perubahan dicatat dalam riwayat sebelum/sesudah. Angka manual digunakan sebagai populasi periode untuk analisis, bukan mengubah master.
- Kembali ke otomatis menghitung ulang total saat disimpan. Snapshot tersimpan tidak berubah diam-diam ketika master berubah.
- Akun lokal tidak dapat membuka/mengubah pengaturan kabupaten. Versi lama formulir pengaturan ditolak agar perubahan bersamaan tidak tertimpa.

Migrasi menambah `iplm_periods.population_mode` dan `population_registry_count`; **nilai populasi, status dan tanggal periode lama tidak diubah**. Total pembanding awal disalin dari snapshot otomatis sebelumnya.

## 3. Satu tautan bukti dukung

Pada formulir IPLM, isi **Satu tautan folder bukti dukung** di tab Bukti dukung. Kelompokkan dokumen dalam folder masing-masing menurut Koleksi, Tenaga, Pelayanan, Pengelolaan. Berikan akses baca kepada verifikator; jangan membuka data pribadi secara publik.

- Di sebelah masing-masing pertanyaan hanya ada petunjuk jenis dokumen dari pemetaan `bukti_dukung.xlsx`, tanpa input URL per indikator.
- Satu tautan HTTPS memenuhi kebutuhan tautan indikator yang diwajibkan.
- Tautan per indikator yang sudah pernah disimpan **tidak dihapus**. Ditampilkan dalam panel **Arsip tautan lama** di tab Bukti dukung, bukan di setiap soal. Menyimpan formulir baru tidak mengosongkan arsip ini.
- Export tetap mempertahankan kolom riwayat tautan lama; tautan folder baru ada pada kolom bukti global.

## 4. Pencarian publik dan batas aktivasi

Halaman: **`/aktivasi-perpustakaan`**. Tautan tersedia di halaman login, “Pengelola sekolah/perpustakaan? Temukan perpustakaan Anda”.

Sudah tersedia:

- Peta semua perpustakaan aktif berkoordinat selain subjenis kabupaten/pusat; ikon berdasarkan jenis, pengelompokan titik saat zoom-out, GPS lokasi pengguna.
- Pencarian AJAX nama perpustakaan, lembaga/sekolah atau NPSN, minimal dua karakter, maksimum 30 hasil per pencarian. Persempit kata kunci jika hasil terlalu banyak.
- Unit tanpa koordinat tetap dapat dicari melalui kolom pencarian.
- Pilihan hasil pencarian atau tombol **Pilih perpustakaan ini** pada popup peta membuka modal identitas dan peringatan kepemilikan.
- Pencarian tidak memfilter/menghilangkan titik peta lain. Data akun, password, PIC dan kontak pribadi tidak dikirim pada API pencarian/peta.
- Untuk sementara modal mengarahkan ke **Masuk dengan akun resmi**, bukan memberikan sesi akun berdasarkan ID sekolah. Pengguna yang sudah mendapat akses resmi tetap mengikuti login dan kewajiban ganti password awal yang ada.

**Belum dikerjakan sampai ada pilihan pengguna:** penerbitan/distribusi kode aktivasi atau proses persetujuan admin, aktivasi satu kali, dan login terverifikasi menuju penggantian password. Memilih sekolah lalu klik “milik saya” tanpa bukti akan memungkinkan siapa saja mengambil akun sekolah lain. Pertanyaan keputusan ada di [PERTANYAAN_TASK3.md](PERTANYAAN_TASK3.md).

## 5. Pemeriksaan dan backup

Pengujian memakai database terisolasi, bukan membuat sekolah/akun/transaksi uji di produksi:

- 36 pemeriksaan model IPLM; 42 model/import perpustakaan.
- 70 pemeriksaan HTTP operasional; 37 HTTP IPLM.
- 50 regresi task2 dan 7 HTTP task2.
- 23 pemeriksaan task3: populasi manual/otomatis, input invalid, stale edit, audit, bukti global/arsip, cakupan workbook dan warna NPSN.
- 16 halaman/ukuran browser untuk form, pengaturan, master dan sidebar; 19 pemeriksaan browser task3 untuk peta/pencarian/modal, akses tanpa login, populasi, desktop 1440 px dan ponsel 390 px. Tidak ditemukan error JavaScript pada pengujian.
- Syntax PHP/JavaScript berkas terkait lolos. Smoke test halaman publik pada origin aplikasi dan HTTPS domain produksi mengembalikan HTTP 200.

Backup produksi sebelum migrasi: `/www/backup/pustaka-network/before-network-20261005-145412-01e69638.sql.gz`. Backup awal pekerjaan: `before-network-20261005-144107-6f93f260.sql.gz` di folder privat yang sama. Verifikasi snapshot sebelum/sesudah migrasi memastikan master perpustakaan, akun, definisi, seluruh isian/bukti IPLM dan nilai periode lama tidak berubah.

Perubahan hanya menambahkan kemampuan konfigurasi; periode produksi tidak dibuka otomatis. Hasil uji adalah bukti skenario yang diperiksa, bukan jaminan tidak mungkin ada bug di semua kondisi.

Database/runtime uji khusus task3 telah dihapus setelah dibackup; data produksi tidak termasuk pembersihan. Backup uji: `/www/backup/pustaka-network/before-network-20261005-145718-59199175.sql.gz`; screenshot dan ringkasan pengujian berada di folder dengan awalan sama dan akhiran `-evidence`.
