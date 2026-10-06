# Hasil task7 tanggal 6 Oktober 2026

Task7 sudah diterapkan pada aplikasi dan database utama. Acuan pekerjaan: [task7.md](task7.md). Master sekarang berisi **1.701 unit**, dengan **1.701 kode/NPSN unik**. Jumlah unit ini **bukan populasi IPLM**.

## 1 Peta dan profil pada menu Libraries

- Koordinat di tabel dapat diklik. Peta bergeser ke lokasi, membuka kelompok marker bila perlu, menyorot ikon dengan lingkaran kuning, dan membuka detail lokasi. Koordinat yang tidak tersedia tidak dijadikan tombol/titik perkiraan.
- Modal profil diperbarui dengan judul institusi, kartu informasi, tampilan ponsel, tombol tutup yang tetap terlihat, serta dukungan Escape.
- Modal memuat profil utama, pilihan populasi, seluruh isian pendataan tambahan pengelola, serta rincian formulir IPLM per periode bagi admin yang berhak melihat IPLM. Data lokal terbaru dibaca kembali ketika halaman direktori dibuka/dimuat ulang.
- Filter jenis/subjenis dan seluruh titik peta tetap bekerja; pagination tabel tidak membatasi titik peta. Kontak dan pendataan tambahan tidak ditambahkan ke peta publik.

## 2 Acuan sekolah dan hasil penyandingan

Acuan SD/SMP negeri adalah **dapodik_awal.xlsx**. Pembaruan sekolah lainnya memakai **sekolah_kabupaten_rembang.xlsx**. **Rekap_new.xlsx** dipakai untuk melengkapi alamat/desa/GPS ketika tersedia, tanpa menjadikan pendataan lama sebagai acuan identitas sekolah.

Sebanyak **900 profil** berhasil dibaca dari situs referensi resmi Kemendikdasmen, mencakup seluruh 869 sekolah baru dan 31 sekolah/kasus lain yang memerlukan pemeriksaan. Portal progres Dapodik yang diberikan menampilkan perlindungan akses; pemeriksaan dilanjutkan melalui halaman resmi `/pendidikan/npsn/{NPSN}`. Salinan hasil, waktu pengambilan dan URL tersimpan di `verifikasi-portal-task7.json`.

| Hasil | Jumlah |
| --- | ---: |
| Sekolah dalam gabungan acuan | 1.339 |
| Dipadankan ke ID perpustakaan yang sudah ada | 470 |
| Master lama yang diperbarui identitas/lokasinya | 24 |
| Sekolah baru ditambahkan | 869 |
| Sekolah baru dengan GPS valid | 805 |
| Sekolah baru yang GPS-nya masih perlu verifikasi | 64 |
| Total master setelah penerapan | 1.701 |

Semua sekolah baru berstatus **perlu verifikasi**, tetap dapat dicari untuk aktivasi mandiri, dan pilihan populasinya **belum dipilih**. Akun tidak dibuat otomatis oleh migrasi ini. Nama perpustakaan khusus, ID unit lama, akun/password, dan relasi operasional tetap dipertahankan.

Alamat tersedia untuk seluruh 869 sekolah baru. Angka koordinat nol atau di luar batas kewajaran wilayah Rembang ditolak, termasuk bila berasal dari portal resmi. **64 unit tetap terdaftar tanpa titik peta**; daftar koreksinya ada di [GPS_PERLU_VERIFIKASI_TASK7.xlsx](GPS_PERLU_VERIFIKASI_TASK7.xlsx).

## 3 Hasil tujuh keputusan sekolah

### Kasus 1 SMK Yos Sudarso

- ID **1624** dipertahankan.
- Institusi sekarang **SMK KATOLIK YOS SUDARSO REMBANG**, NPSN **20315657**.
- Nama perpustakaan dan seluruh relasi tetap. Alamat Diponegoro 95 sesuai dengan [profil resmi sekolah](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315657).

### Kasus 2 SMP Muhammadiyah Rembang

- ID **1598** dipertahankan, tanpa membuat unit baru.
- Institusi sekarang **SMP MUHAMMADIYAH REMBANG**, NPSN **20315667**.
- Konflik dengan Yos Sudarso diselesaikan dahulu. GPS diisi **-6.7077000, 111.3462000**, dari [profil resmi](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315667).
- Nama perpustakaan lama yang memuat angka 1 tidak otomatis diganti; nama institusi sudah sesuai Dapodik.

### Kasus 3 SMK YPI Rembang

- ID **1644**, NPSN **20315658**, dan nama Graha Pustaka dipertahankan.
- Kecamatan **Sulang**, desa **Kemadu**, dan teks alamat database **JL. PEMUDA KM 03 REMBANG** tetap, sesuai instruksi Anda.
- Koordinat diperbarui menjadi **-6.8268035, 111.3877221**. [Profil resmi terbaru](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315658) juga menyebut Sulang–Kemadu. Portal memiliki teks alamat yang berbeda; teks alamat database tidak ditimpa.

### Kasus 4 SLB Negeri Rembang

- ID **1632** dipertahankan; NPSN menjadi **20315824**.
- Subjenis menjadi **SLB**. GPS dilengkapi dari [profil resmi](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315824).
- Tidak otomatis masuk kewenangan/populasi IPLM kabupaten.

### Kasus 5 SD Islam An Nawawiyyah

- ID **1586** dan nama perpustakaan **Baitul Ulum** dipertahankan.
- Institusi mengikuti Dapodik, NPSN menjadi **20315837**.
- Alamat Nelayan II/10–12 Tasikagung pada sumber cadangan dan [profil resmi](https://referensi.data.kemendikdasmen.go.id/pendidikan/npsn/20315837) mendukung padanan ini.

### Kasus 6 SD Hamong Siswa

ID **1638** dipertahankan. Tidak dinonaktifkan, tidak diganti dengan sekolah lain, dan tidak diberi NPSN perkiraan.

### Kasus 7 SMP Katholik Adisucipto Sale

ID **1602** dan NPSN **20315662** dipertahankan. Tidak dinonaktifkan atau diganti dengan sekolah lain.

## 4 Migrasi pendataan lama dan kontak

Dari **834 baris** pendataan lama:

- **795 baris** dipadankan dan diisikan ke pendataan tambahan pada unit yang sesuai.
- **33 baris** belum mempunyai padanan pasti. Tidak dibuat sekolah baru hanya berdasarkan data pendataan ini.
- **6 catatan tambahan** mengarah ke unit yang sudah mempunyai catatan pendataan. Kedua versi tetap diarsipkan; catatan kedua tidak menimpa formulir.

Dua padanan tambahan dikenali dari ejaan satu huruf dengan nomor sekolah, kecamatan dan desa yang sama: **SUMBEREJO → SUMBERJO** serta **PALEMSARI → PELEMSARI**. Identitas Dapodik tetap menjadi acuan. Nomor sekolah berbeda tidak disamakan otomatis.

**Seluruh 317 baris selain sekolah sudah memiliki padanan.** Tidak ada baris nonsekolah yang tertahan pada penyandingan ini.

Kolom kontak kosong pada **538 profil** dilengkapi dari sumber yang mempunyai satu padanan: **353 nomor HP**, **522 email**, dan **4 website HTTPS**. Hanya format yang valid diterima; kontak yang sudah terisi tidak ditimpa. Keaktifan nomor/email tetap perlu diperiksa pengelola. Nama kepala perpustakaan disimpan sebagai nama kepala, bukan ditebak menjadi contact person.

Nomor telepon yang bukan format HP, website non-HTTPS, nilai tanggal/tahun tidak valid dan sumber lain yang belum dapat dipakai tetap ada pada arsip sumber. Tidak diubah menjadi angka nol atau nilai buatan. Data yang diimpor berasal dari pendataan lama; tahun acuannya tidak otomatis dianggap 2026.

## 5 Formulir lokal dan kewajiban kontak

Halaman baru: **`/library-workspace/survey`**.

**60 kolom** pendataan telah diinventarisasi; **39 kolom tambahan** mendapat isian lokal tersendiri, ditambah konfirmasi keberadaan perpustakaan, tahun acuan, dan catatan sumber. Rinciannya ada di [INVENTARIS_FORMULIR_TASK7.xlsx](INVENTARIS_FORMULIR_TASK7.xlsx).

Formulir mencakup legalitas dan tahun berdiri, kepala perpustakaan/lembaga, visi/misi, sistem/hari/jumlah jam layanan, akreditasi, teknologi, otomasi, internet, CCTV, bantuan, dan angka pendataan lama. Kosong berbeda dari nol. Pendataan tambahan disimpan terpisah dari jawaban IPLM dan **tidak masuk perhitungan IPLM**.

**Contact person dan nomor HP aktif wajib** pada penyimpanan profil, penyimpanan pendataan tambahan, dan pengiriman IPLM. Server memeriksa kewajiban ini, bukan hanya atribut wajib di browser. Data yang sudah ada tetap dapat dibaca dan draf IPLM tetap dapat dikerjakan.

Masih ada **1.290 unit tanpa PIC** dan **1.348 unit tanpa nomor HP** sesudah pelengkapan sumber. Angka ini menunjukkan pekerjaan pengisian oleh pengelola, bukan kontak yang diisi otomatis dengan nama/nomor perkiraan. Dashboard menampilkan pengingat ketika kontak belum lengkap.

Akses lokal dikunci ke perpustakaan akun masing-masing. Penyimpanan memakai POST dan CSRF; isian tambahan memiliki nomor versi agar formulir lama tidak menimpa perubahan yang lebih baru. Pada konflik, pengguna harus memuat ulang data terbaru.

## 6 Dashboard dan panduan

Dashboard lokal memiliki panel sorotan **Profil & pendataan** dengan tautan ke profil, pendataan tambahan, IPLM, serta panduan.

Panduan tersedia di **`/library-workspace/guide`**. Isinya menjelaskan identitas Dapodik, kontak wajib, pengisian tambahan, arti kosong/nol, tahun acuan angka, bukti dukung, simpan draf, pengiriman dan revisi. IPLM 2026 tetap memakai **1 Januari–31 Desember 2026**.

## 7 Populasi otomatis dan manual

Edit perpustakaan sekarang memiliki pilihan **Seleksi populasi IPLM**:

1. Belum dipilih / perlu verifikasi.
2. Dipilih karena mempunyai perpustakaan dan sesuai kewenangan.
3. Dikecualikan dari populasi.

Semua unit mulai dengan status belum dipilih, karena keputusan Anda menyatakan jumlah master belum merupakan populasi. Mode otomatis menghitung **unit yang dipilih kabupaten**, aktif, serta jenis/subjenis yang sesuai kewenangan. Memilih SLB atau subjenis di luar kewenangan tidak membuatnya masuk hitungan otomatis.

Mode manual tetap tersedia dengan jumlah dan alasan penetapan. Snapshot periode lama **403** tidak berubah. Hitungan otomatis saat ini **0 unit terpilih**, sampai seleksi dilakukan dan pengaturan periode disimpan ulang. Periode tidak dapat dibuka dengan populasi otomatis kosong; pilih unit dahulu atau gunakan penetapan manual.

Seleksi ini mengatur penyebut populasi. Formulir/responden tetap mengikuti aturan cakupan jenis/subjenis yang sudah ada; histori dan status isian lama tidak dihapus oleh perubahan pilihan populasi.

## 8 Dokumen hasil dan pertanyaan tersisa

- [PERSANDINGAN_SEKOLAH_TASK7.xlsx](PERSANDINGAN_SEKOLAH_TASK7.xlsx): master database di kiri, sumber sekolah di kanan, keputusan dan sumber GPS di bagian akhir.
- [PERSANDINGAN_PENDATAAN_TASK7.xlsx](PERSANDINGAN_PENDATAAN_TASK7.xlsx): hubungan pendataan dengan unit dan status setiap baris.
- [INVENTARIS_FORMULIR_TASK7.xlsx](INVENTARIS_FORMULIR_TASK7.xlsx): seluruh 60 kolom beserta tujuan penyimpanannya.
- [GPS_PERLU_VERIFIKASI_TASK7.xlsx](GPS_PERLU_VERIFIKASI_TASK7.xlsx): 64 sekolah baru yang membutuhkan lokasi benar.
- [PERTANYAAN_TASK7.md](PERTANYAAN_TASK7.md): pertanyaan dipisahkan per kasus, lengkap dengan ID, masalah dan tempat jawaban. Tujuh kasus sekolah yang sudah Anda putuskan tidak ditanyakan ulang.

## 9 Pengujian dan backup

Pengujian pada database terpisah lulus: **37 pemeriksaan model**, **4 pemeriksaan rollback migrasi**, **34 pemeriksaan browser desktop/ponsel**, serta **1.419 pemeriksaan pelestarian kontak**. Pengujian khusus formulir versi lama memastikan pengiriman ulang tidak melewati penolakan konflik. Tidak ada error JavaScript pada pengujian browser utama.

Regresi peta setelah penerapan: **39 pemeriksaan lulus**, dengan **1.505 titik valid**. Landing produksi merespons 200; direktori/panduan memerlukan login; sumber privat dalam `docs/iplm` merespons 403 melalui HTTP. PHP lint, sintaks JavaScript, dan whitespace pada berkas yang diubah telah diperiksa.

Backup sebelum perubahan awal:

`/www/backup/pustaka-network/before-network-20261006-114401-88e9b201.sql.gz`

Backup tepat sebelum migrasi utama:

`/www/backup/pustaka-network/before-network-20261006-115709-b706f469.sql.gz`

Backup sebelum pelengkapan kontak:

`/www/backup/pustaka-network/before-network-20261006-120414-f987f9f9.sql.gz`

Penerapan utama: `tools/setup_iplm_task7.php`; kontak: `tools/setup_iplm_task7_contacts.php`. Keduanya hanya menganalisis bila tanpa `--apply`, menyimpan riwayat, dan tidak mengulang perubahan pada batch yang sudah diterapkan. Perubahan identitas/lokasi dan kontak mempunyai catatan sumber serta nilai sebelumnya.

Jika ada transaksi baru, pemulihan harus terarah dari riwayat sebelum/sesudah. Jangan menimpa seluruh database dengan backup lama. Arsip pendataan dan catatan sumber lama dipertahankan untuk peninjauan.

Server uji sudah dihentikan dan empat database/folder fixture task7 sudah dihapus setelah backup. Bukti pengujian dan tangkapan layar tersimpan privat di `/www/backup/pustaka-network/task7-evidence-20261006/`; manifest backup/pembersihan ada di `fixture-cleanup.json`. Pengujian tambahan formulir versi lama mencakup **3 pemeriksaan browser/HTTP** yang lulus. Tidak ada data produksi yang dihapus dalam pembersihan tersebut.
