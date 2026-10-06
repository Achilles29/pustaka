# Hasil task5 — 6 Oktober 2026

Sudah diterapkan ke aplikasi dan database yang digunakan sekarang, dengan backup terlebih dahulu. Acuan: [task5.md](task5.md), termasuk ralat tahun dari Anda.

## 1. Ralat tahun IPLM

**Pengukuran IPLM 2026 menggunakan data 1 Januari–31 Desember 2026, bukan 2025.**

- Periode ID 2 sekarang berjudul **IPLM 2026 — data tahun 2026**; tanggal awal/akhir sudah diubah di database, bukan sekadar label.
- Dua isian lama (ID 2 dan 4) ditandai **perlu revisi**, disertai penjelasan perubahan tahun. Angka, identitas isian, struktur pertanyaan, dan tautan bukti yang tersimpan tidak ditimpa. Riwayat sebelum perubahan disimpan.
- Status periode tetap **Persiapan/draft**; tidak dibuka otomatis.
- Snapshot populasi 403 tidak diubah oleh ralat tahun. Jumlah master yang sekarang masuk cakupan adalah 738. Admin dapat menyimpan ulang pengaturan mode otomatis untuk memperbarui snapshot, atau menetapkan populasi manual beserta alasannya.
- Tahun 2026 masih berjalan. Angka yang tersedia saat ini jangan dianggap otomatis merupakan rekap lengkap sampai 31 Desember. Data operasional sekolah dalam workbook juga tidak otomatis dipindahkan menjadi jawaban komponen IPLM.

Pernyataan tahun 2025 dalam hasil task2/task3/task4 merupakan **riwayat keputusan terdahulu**, bukan acuan aktif. Tidak perlu menjawab ulang soal tahun.

## 2. Perubahan `/libraries`

- Kolom **Nama institusi** terpisah dari nama perpustakaan; pencarian juga mencakup nama institusi.
- Tombol **Detail profil** membuka modal berisi institusi, kode/NPSN, NPP, jenis/subjenis, status, alamat/wilayah, PIC, kontak, jam layanan, koordinat, radius, fasilitas, dan deskripsi. Data kosong ditampilkan apa adanya, tidak ditebak.
- Judul dan isi kolom Aksi rata tengah. Tombol Detail, Operasional, Edit, dan Aktif/Nonaktif memakai ikon saja; keterangan muncul sebagai tooltip saat diarahkan atau difokuskan dengan keyboard. Hak akses masing-masing tetap berlaku.
- Pilihan **jenis dan subjenis langsung memfilter tabel dan titik peta**. Subjenis mengikuti jenis induknya.
- **Legenda dapat diklik** untuk memilih jenis; pilihan subjenis lama dibersihkan agar tidak bertabrakan. Klik **Semua jenis** untuk menghapus filter jenis/subjenis.
- Peta tetap memuat **semua titik dalam jenis/subjenis terpilih**, bukan hanya 10/25/50/100 baris halaman tabel. Pencarian teks, kecamatan, dan status tetap hanya memfilter tabel, sesuai penjelasan di layar.
- Peta landing/member/aktivasi tetap menggunakan cakupan sebelumnya. Data tanpa GPS valid tidak ditampilkan sebagai titik palsu.
- Tindakan aktif/nonaktif diperkuat: wajib POST dan token formulir, tidak dapat dijalankan hanya lewat URL GET.

## 3. Pemetaan sekolah dan pembaruan database

Sumber: [sekolah_kabupaten_rembang.xlsx](sekolah_kabupaten_rembang.xlsx), sheet `Sekolah`, **1.339 baris**, seluruh NPSN unik.

| Hasil pencocokan awal | Jumlah |
|---|---:|
| Sudah sesuai | 423 |
| Berhasil diperbarui | 42 |
| Ditahan untuk verifikasi | 3 |
| Belum memiliki padanan pasti | 871 |
| Total sumber sekolah | 1.339 |

Sesudah pembaruan, **465 sekolah sudah sesuai**. Jumlah master tetap **832**: task5 memperbarui unit yang sudah terdaftar, bukan mengimpor massal sekolah yang belum memiliki padanan.

Perubahan pada 42 master dapat saling tumpang tindih:

- 26 kode/NPSN diselaraskan dengan workbook sekolah baru.
- 26 nama institusi diperbarui/diseragamkan.
- 22 nama perpustakaan yang memang memakai nama sekolah diselaraskan; nama khusus seperti Maktabah/Widya Pustaka tidak diganti menjadi nama sekolah.
- 1 status institusi negeri/swasta diperbarui.

Pencocokan memakai NPSN dan kesesuaian identitas, atau nama sekolah + kecamatan yang cocok unik. Normalisasi terbatas pada ejaan/singkatan yang jelas, seperti IT/Islam Terpadu, Katholik/Katolik, nama jenjang lengkap, serta nama kecamatan di akhir nama. **Nomor sekolah tidak dibuang dan nama mirip tidak digabung secara fuzzy.**

Contoh koreksi NPSN: master 1640, SMP Islam An-Nawawiyyah, berubah dari `69757262` menjadi `69787408` berdasarkan workbook sekolah baru. Ini berbeda dari instruksi task4 untuk tidak mempercayai NPSN `pendataan.xls`: task5 menggunakan sumber sekolah baru yang Anda minta untuk menyelaraskan master.

Yang dipertahankan: ID perpustakaan, akun/password, relasi operasional, desa, alamat, GPS, PIC/kontak, jam layanan, radius, status aktif/verifikasi, foto, deskripsi, fasilitas, dan hubungan sumber lama. Workbook baru tidak memuat alamat/desa/GPS, sehingga tidak dipakai untuk mengosongkan kolom tersebut. Jumlah ruangan `Perpustakaan`, PD, guru, dan lainnya tetap menjadi informasi sumber, bukan otomatis angka IPLM atau bukti ketiadaan perpustakaan.

Seluruh 1.339 baris beserta keputusan, hash sumber, padanan, dan nilai sebelum/sesudah perubahan diarsipkan pada `library_source_records` dengan `source_system=sekolah_task5`. Workbook sumber tidak diubah.

## 4. Excel penyandingan terbaru

1. **[PERSANDINGAN_SEKOLAH_TASK5.xlsx](PERSANDINGAN_SEKOLAH_TASK5.xlsx)** — database terkini dibandingkan workbook sekolah baru. Berisi 1.339 baris sumber + 364 master yang tidak ditemukan di workbook = **1.703 baris data**.
2. **[PERSANDINGAN_PENDATAAN_TASK5.xlsx](PERSANDINGAN_PENDATAAN_TASK5.xlsx)** — penyandingan `pendataan.xls` dibuat ulang terhadap database sesudah koreksi task5. Berisi 834 baris sumber + 39 master tanpa padanan = **873 baris data**. Hubungan sumber hasil task4 dipertahankan; kasus yang dahulu ditahan tidak dianggap otomatis selesai.

Keduanya: **database di kiri A–I**, **sumber di kanan J–R**, **keputusan dan keterangan di S–X**. Nama institusi database ikut terlihat. Kolom rincian dapat ditampilkan dengan *Unhide*. Ada filter header dan baris judul dibekukan.

Warna: hijau = sesuai/diperbarui; kuning = perlu verifikasi; ungu = belum terdaftar pada penyandingan sekolah (atau hasil import task4 pada penyandingan pendataan); biru = hanya database. Sel NPSN sumber yang berbeda dari database ditandai merah. Pada file sekolah, kolom T/U/W memuat kolom berubah, nilai sebelumnya, dan nilai sesudahnya.

`HANYA_DATABASE` **bukan berarti tutup/tidak sah**: workbook sekolah tidak mencakup seluruh perpustakaan desa, TBM, madrasah, dan jenis lain. `BELUM_TERDAFTAR` berarti belum ada padanan pasti, bukan jaminan lembaganya benar-benar baru.

Keputusan lanjutan yang diperlukan ada di **[PERTANYAAN_TASK5.md](PERTANYAAN_TASK5.md)**.

## 5. Pengujian

- Database uji terpisah: **7.545 pemeriksaan** lulus, termasuk field yang harus tetap sama pada 832 master, filter semua jenis/subjenis, penolakan perubahan rentang ketika ada isian terverifikasi, rollback utuh, arsip sumber, serta proses ulang tanpa duplikasi/perubahan tambahan.
- Browser desktop 1440 px dan ponsel 390 px: **37 pemeriksaan** lulus; kolom institusi, ikon/tooltip, modal responsif, Escape, dropdown bertingkat, perubahan titik peta, legenda/reset, data kosong, GET tidak valid, CSRF, tahun 2026, dan pembatasan akses admin lokal. Tidak ada error JavaScript.
- Regresi peta publik/member: **39 pemeriksaan** lulus; 680 titik GPS valid, cakupan akun dan pembatasan data privat tetap terjaga. Fixture tes lama diperbaiki agar tidak menganggap baris alfabetis pertama selalu memiliki GPS.
- PHP lint, pemeriksaan sintaks JavaScript, dan pemeriksaan whitespace lulus.
- Pemeriksaan produksi tanpa login: landing 200, direktori mengarahkan ke login, aset JavaScript 200, akses publik ke dokumen privat 403.
- Pencarian publik aktivasi menggunakan NPSN yang baru diperbarui berhasil menemukan unit yang sama (contoh ID 844); akun tidak dipindahkan.

## 6. Backup dan jejak teknis

Backup lengkap tepat sebelum pembaruan database:

`/www/backup/pustaka-network/before-network-20261006-103409-13b8ac21.sql.gz`

Backup database dan kode sebelum perubahan tampilan:

`/www/backup/pustaka-network/before-network-20261006-102337-c5b062c4.sql.gz`

`/www/backup/pustaka-network/before-network-20261006-102337-c5b062c4-code/`

Ringkasan mesin: [ringkasan-task5.json](ringkasan-task5.json). Script: `tools/iplm_task5_support.php` dan `tools/setup_iplm_task5.php`; default tanpa `--apply` hanya analisis. Pengulangan `--apply` pada batch yang sudah lengkap tidak menimpa perubahan admin atau laporan awal.

Jangan memulihkan seluruh database secara sembarang jika sudah ada transaksi baru. Gunakan jejak sebelum/sesudah pada arsip batch untuk pemulihan terarah; backup lengkap disimpan sebagai pengaman.

Server uji sudah dihentikan. Database dan folder fixture khusus task5 di `/tmp` sudah dihapus setelah dibackup; tidak ada data produksi yang dihapus. Fixture dapat dipulihkan dari `/www/backup/pustaka-network/before-network-20261006-103723-d4c4e48c.sql.gz`. Log dan tangkapan layar pengujian tersimpan privat di `/www/backup/pustaka-network/before-network-20261006-103723-d4c4e48c-task5-evidence/`.
