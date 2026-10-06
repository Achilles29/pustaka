# Hasil pelaksanaan task2

Tanggal: 5 Oktober 2026. Acuan: `task2.md`, `bukti_dukung.xlsx`, dan `pendataan.xls`.

## Perubahan aplikasi yang sudah diterapkan

1. **Periode penilaian 2026 menilai data 1 Januari–31 Desember 2025.** Periode yang sudah ada diperbarui tanpa mengganti ID. Tanggal sudah dikonfirmasi. Status periode tetap **Persiapan/draft**; admin kabupaten dapat membukanya melalui Pengaturan → Periode & populasi. Tidak ada pengiriman formulir massal otomatis.
2. **Populasi 403 perpustakaan**, dihitung dari `/libraries`: 363 SD, 39 SMP, 1 perpustakaan kabupaten. Syarat: perpustakaan aktif, jenis/subjenis aktif, termasuk cakupan IPLM. Tujuh TK dan satu SKB dikecualikan, tetapi tetap terdaftar dan tetap dapat menggunakan modul perpustakaan lainnya.
3. Pengecualian cakupan berlaku pada pembuatan/penyimpanan/verifikasi isian, pilihan subjenis, rekap, ekspor, analisis, dan notifikasi verifikasi. Bukan hanya menyembunyikan pilihan di browser.
4. Populasi dihitung ulang oleh server saat periode disimpan. Halaman pengaturan menunjukkan hitungan master sekarang dan snapshot periode tersimpan; sumber, waktu hitung, dan daftar ID disimpan dalam jejak audit. Perubahan jumlah master tidak diam-diam mengganti penyebut laporan lama.
5. **Kualifikasi tenaga minimal D2**, mengikuti keputusan Anda. Definisinya dapat diedit melalui tab Komponen & bukti dukung. Ini ketentuan pengisian lokal yang dipilih, bukan pernyataan bahwa D2 setara D3.
6. **Pemustaka dihitung sebagai jumlah pemanfaatan/kunjungan**, bukan hanya orang unik. Kunjungan berulang boleh dihitung sesuai rekap nyata. Data kunjungan simulasi tidak otomatis dimasukkan ke jawaban IPLM.
7. **28 petunjuk bukti dukung diperbarui** sesuai indikator pada workbook baru, termasuk semua indikator anggaran. Dokumen tetap disimpan pada Drive masing-masing; aplikasi hanya menyimpan tautan HTTPS. Kewajiban tautan mengikuti konfigurasi komponen yang sudah ada, tidak mendadak mewajibkan semua tautan pada draft.
8. `/iplm/settings` menggunakan dua tab: **Periode & populasi**, **Komponen & bukti dukung**.
9. `/iplm/form/{id}` menggunakan enam tab: identitas, koleksi, tenaga, pelayanan, pengelolaan, dan bukti umum. Pada desktop, setiap nilai berada di kiri dan bukti indikatornya di kanan; pada ponsel disusun vertikal. Berpindah tab tidak menghapus isian. Semua tab disimpan sekaligus.

Saat mulai pemeriksaan ada satu draft; saat penerapan sudah ada **dua draft aktif (ID 2 dan 4)**. Keduanya dipertahankan, petunjuk skemanya diperbarui, versi dinaikkan, dan diberi status **perlu revisi** agar angka diperiksa terhadap tahun 2025. Verifikasi sesudah penerapan membuktikan `values_json` dan `evidence_json` keduanya tidak berubah. Identitas, GPS, akun, dan master `/libraries` tidak ditimpa.

## Hasil pencocokan pendataan

`pendataan.xls` ternyata HTML tabel berekstensi XLS, bukan workbook biner. Berkas berhasil dibaca tanpa menjalankan konten HTML-nya. Isinya **834 baris dan 60 kolom**. Semua baris dipertahankan pada keluaran Excel asli `.xlsx`.

| Kategori baris sumber | Jumlah |
| --- | ---: |
| COCOK — pasangan kuat, tidak ganda | 364 |
| COCOK_GANDA — delapan baris menuju empat master yang sama | 8 |
| PERLU_VERIFIKASI — kandidat, konflik identitas, atau nama belum pasti | 68 |
| BELUM_DITEMUKAN — belum ditemukan pasangan yang cukup kuat | 394 |
| Total | 834 |

Sebanyak **368 perpustakaan unik di master** memiliki pasangan kuat. **43 dari 411 perpustakaan master** belum memiliki pasangan terkonfirmasi di pendataan; di antaranya tujuh TK dan satu SKB yang memang tidak muncul sebagai jenis sumber pada file ini. “Belum ditemukan” bukan putusan bahwa perpustakaan baru: bisa berupa nama lama, merger sekolah, atau data ganda yang belum teridentifikasi.

Nama sekolah dibaca dari Nama maupun Lembaga Induk, dengan normalisasi SDN/SD N/SD Negeri, penulisan lengkap sekolah, angka Romawi, posisi nomor sekolah, variasi spasi, serta kecamatan. **Nomor sekolah tetap dipertahankan**: sekolah 2/3 tidak otomatis digabung ke sekolah tanpa nomor. NPSN/NPP diprioritaskan, tetapi konflik NPSN/kecamatan ditandai untuk pemeriksaan, bukan ditimpa.

Empat pasangan ganda: SD Negeri Timbrangan, SD Negeri 1 Kajar, SD Negeri Sumberagung, dan SD Negeri 2 Kalitengah. ID sumber dan nomor baris tertera di workbook. Tidak ada baris yang digabung/dihapus otomatis.

Contoh yang perlu keputusan:

- Baris 52, ID sumber 352595: SD Negeri Demaan, NPSN berbeda.
- Baris 277, ID sumber 210305: SMP Negeri 1 Bulu, NPSN berbeda.
- Baris 96, ID sumber 345100: Kedung Tulup/Kedungtulup; kandidat memiliki konflik kecamatan. Jangan memilih hanya berdasarkan kemiripan nama.
- Baris 641, ID sumber 50964: Perpustakaan Umum Kabupaten Rembang, kandidat master ID 2 “PERPUSTAKAAN UMUM DAERAH”. Sangat relevan untuk diperiksa NPP-nya, tetapi tidak disamakan otomatis.

### Koordinat

Pemeriksaan dilakukan dengan validasi angka/rentang koordinat dan jarak Haversine antartitik, tanpa penelusuran Google Maps satu per satu. Pada **261 baris yang memiliki pasangan kuat**, jarak titik sumber terhadap titik aplikasi melebihi 1 km. Ini jumlah baris, bukan jumlah lembaga unik.

Kualitas koordinat sumber perlu ditinjau: **148 baris memakai pasangan titik persis sama** (`-6.6905991, 111.469323`), dan **128 baris tidak memiliki kedua koordinat**. Temuan ini mendukung sikap tidak menimpa GPS master. Jarak besar bukan bukti bahwa titik aplikasi pasti benar; workbook menyediakan nilai sumber, master, serta jarak untuk verifikasi. **Tidak ada GPS yang diubah.**

## Berkas hasil untuk ditinjau

| Berkas | Isi / kegunaan |
| --- | --- |
| [PENCOCOKAN_PENDATAAN.xlsx](PENCOCOKAN_PENDATAAN.xlsx) | Semua 834 baris, status pencocokan, dasar, ID kandidat, konflik, GPS dan 60 kolom sumber |
| [PENDATAAN_NAMA_DISELARASKAN.xlsx](PENDATAAN_NAMA_DISELARASKAN.xlsx) | Salinan pendataan dengan Nama/Lembaga Induk diselaraskan untuk pasangan kuat; nama asli tetap ada di kolom pendamping; kandidat tidak diubah |
| [LIBRARIES_BELUM_COCOK.xlsx](LIBRARIES_BELUM_COCOK.xlsx) | Daftar 43 master yang belum memiliki pasangan terkonfirmasi |
| [PEMETAAN_KOLOM_PENDATAAN.xlsx](PEMETAAN_KOLOM_PENDATAAN.xlsx) | Pemetaan seluruh 60 kolom, jumlah isian terisi, tujuan simpan dan batas sinkronisasi |
| [PEMETAAN_BUKTI_DUKUNG.xlsx](PEMETAAN_BUKTI_DUKUNG.xlsx) | Pemetaan 28 indikator ke baris sumber, jenis dokumen, format, dan catatan |
| [PERTANYAAN_TASK2.md](PERTANYAAN_TASK2.md) | Pertanyaan baru yang perlu dijawab sebelum import/penggabungan lanjutan |

JSON pendamping tersedia untuk reproduksi: `ringkasan-pendataan-task2.json`, `pencocokan-pendataan-task2.json`, `pemetaan-bukti-dukung-task2.json`.

Workbook hasil yang memuat data sumber diberi akses file privat (0600), bukan unduhan publik. Berkas sumber asli tidak ditimpa. Pada salinan yang diselaraskan, koordinat dan angka pendataan tetap merupakan angka sumber, bukan hasil koreksi otomatis.

## Sinkronisasi dan data tambahan non-IPLM

Rekomendasi tetap **satu induk perpustakaan**, bukan menggabungkan semua angka historis ke tabel `libraries`:

- Identitas yang sama (NPSN, NPP, lembaga, alamat, jenis/subjenis) dipetakan ke induk; perubahan perlu verifikasi. Snapshot formulir IPLM tidak otomatis mengubah induk.
- Jumlah siswa, koleksi, tenaga, anggaran dan kunjungan memiliki potensi padanan IPLM, tetapi **file pendataan tidak memiliki kolom rentang tahun statistik**. Created At/Updated At adalah waktu pencatatan, bukan tahun angka. Koleksi juga belum terpisah cetak/digital; jabatan pustakawan belum membuktikan kualifikasi D2. Karena itu tidak ada penyalinan angka ke IPLM 2025 secara otomatis.
- Data tambahan (akreditasi, SK pendirian, tahun berdiri, visi/misi, layanan, fasilitas, santri, mahasiswa, rombongan, anggota, dan atribut lainnya) tetap disertakan dalam bahan pendataan non-IPLM, tidak dibuang. Pemetaan kolom lengkap ada di Excel.
- Tahap import berikutnya disarankan memakai `library_survey_imports` (batch/sumber/checksum), `library_survey_records` (ID sumber, library_id yang boleh kosong, snapshot atribut, tahun berlaku, status verifikasi), dan riwayat keputusan. Gunakan `source + external_id` untuk idempotensi, bukan nama. Statistik agregat tidak membuat anggota atau transaksi fiktif.
- Pada tugas ini **yang dilakukan adalah pemetaan dan penyiapan bahan import**, sesuai permintaan. Tidak membuat 394 perpustakaan baru, menautkan 68 kandidat, menimpa identitas, atau menambah tabel pendataan operasional tanpa keputusan atas baris ambigu.

## Catatan sumber bukti dan analisis

Kolom checklist F pada beberapa baris koleksi terlihat bergeser terhadap indikator B. Pemetaan memakai identitas indikator B serta penjelasan C/D/E, bukan menyalin urutan checklist F. D57 (SOP) memuat tambahan teks anggaran; petunjuk SOP di aplikasi mengikuti B57/C57/F57 dan tidak meminta rincian anggaran sebagai bukti SOP. Teks asli tetap ada dalam JSON pemetaan.

Pencarian sumber resmi tambahan dilakukan pada 5 Oktober 2026. [Survei IPLM 2026 Perpusnas](https://survey.perpusnas.go.id/index.php/735445?lang=id) dan [berita koordinasi kajian 2026](https://www.perpusnas.go.id/berita/perpusnas-perkuat-koordinasi-dalam-kajian-perpustakaan-indonesia-2026) ditemukan. Ringkasan sumber resmi mendukung bobot kepatuhan/kinerja 30/70, tetapi parameter kalibrasi lengkap yang diperlukan aplikasi (lambda per indikator, min–max nasional, dataset pembanding, bobot rinci) **belum ditemukan dalam penelusuran ini**. Bukan klaim bahwa parameter tersebut tidak ada.

Analisis aplikasi tetap **simulasi internal**, bukan skor resmi Perpusnas. Tidak memasukkan parameter nasional yang dikarang atau memakai rumus IPLM lama sebagai pengganti metode baru. Jawaban Anda bahwa belum memiliki parameter sudah dicatat; tidak menjadi penghalang pendataan.

## Verifikasi dan jejak penerapan

- 36 pemeriksaan model IPLM, 37 pemeriksaan HTTP IPLM, 50 pemeriksaan model/berkas task2 dan 7 pemeriksaan HTTP tambahan task2 lulus.
- 42 pemeriksaan model/import jejaring dan 70 pemeriksaan HTTP jejaring lulus.
- Browser: 16 kombinasi halaman/peran/ukuran desktop 1440 dan ponsel 390 lulus, tanpa error JavaScript. Diuji perpindahan tab, seluruh 43 nilai tetap masuk FormData, tata letak bukti kanan/bawah, pengaturan bertab, dropdown bertingkat, dan isolasi akses.
- Pengujian memakai database terisolasi, bukan pengisian transaksi percobaan ke database riil.
- Server uji telah dihentikan dan database/runtime uji dihapus sesudah selesai. Fixture dapat dipulihkan dari `/www/backup/pustaka-network/before-network-20261005-135243-262e4478.sql.gz`; tangkapan layar, metadata uji dan hash kode ada di direktori privat `/www/backup/pustaka-network/before-network-20261005-135243-262e4478-evidence`.
- Backup sebelum pekerjaan: `/www/backup/pustaka-network/before-network-20261005-132941-b16b4140.sql.gz`, beserta direktori `-code`.
- Backup sebelum penerapan keputusan: `/www/backup/pustaka-network/before-network-20261005-134422-783d9b3f.sql.gz`, beserta `.iplm-task2.json`.
- Migration idempotent: `tools/setup_iplm_task2.php --apply`; menjalankan kembali tidak menimpa perubahan pengaturan admin setelah penerapan pertama. Pada pemasangan baru, jalankan sesudah `tools/setup_iplm.php`.
- Pembuat laporan: `php tools/map_iplm_pendataan.php` (DB read-only; mengganti berkas hasil turunan, tidak mengganti sumber).
- Hash sumber pendataan: `bffd074f07702c1867860254ba9fb1f0f2c3d7205f2352766a2372c470ac20f1`.

File aplikasi utama: `Iplm_model.php`, controller `Iplm.php`, views `iplm/{header,index,form,settings}.php`, `assets/css/iplm.css`, dan `assets/iplm-tabs.js`. Tidak ada perubahan pada kode sirkulasi/pengembalian buku dalam tugas ini.
