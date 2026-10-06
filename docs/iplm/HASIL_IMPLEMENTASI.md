# Hasil implementasi IPLM

> Ini catatan implementasi awal. Keputusan periode, populasi, D2, cakupan TK/SKB, petunjuk bukti dan tampilan telah diperbarui melalui [HASIL_TASK2.md](HASIL_TASK2.md). Gunakan dokumen tersebut sebagai acuan terbaru.

Tanggal: **5 Oktober 2026**. Modul, master dan migrasi telah diterapkan pada aplikasi
dan database yang digunakan saat ini. **Tidak ada isian IPLM fiktif di database utama.**

## Halaman dan hak akses

| Halaman | Pemkab | Admin lokal |
| --- | --- | --- |
| `/iplm` | Rekap semua perpustakaan, status, perbedaan, total indikator | Isian perpustakaan sendiri |
| `/iplm/form/{id}` | Baca/edit, putuskan perbedaan, verifikasi, kembalikan, arsipkan | Buat/edit draft/revisi dan kirim isian sendiri |
| `/iplm/settings` | CRUD komponen/petunjuk/pilihan/bukti, kelola periode/populasi | Tidak diizinkan |
| `/iplm/analysis/{periode_id}` | Analisis internal beserta rumus dan parameter | Tidak diizinkan |
| `/iplm/export/{periode_id}/xlsx` atau `/csv` | Ekspor seluruh isian periode | Hanya isian perpustakaan sendiri |
| `/library-types` | CRUD jenis/subjenis, urutan, warna, status/cakupan | Tidak diizinkan |
| `/libraries/create`, `/libraries/edit/{id}` | Jenis–subjenis bertingkat, institusi, NPP | Tetap melalui pengelola kabupaten |

Sidebar pemkab: **Pendataan IPLM** berisi rekap/verifikasi dan pengaturan form/periode;
master jenis/subjenis di **Data Master**. Sidebar lokal: **Laporan & Pendataan** berisi
laporan lama dan **Pendataan IPLM**. Tidak menambah tingkatan peran admin sekolah.
Kotak masuk admin kabupaten juga menampilkan perbedaan identitas dan antrean verifikasi
IPLM untuk periode yang belum ditutup. Bila menu belum muncul pada sesi lama, login ulang.

## Yang sudah dikerjakan

- Seluruh 43 kolom dari sheet IPLM Kab-Kota dipetakan, berikut kontrol input,
  definisi indikator, dropdown dari Validasi Data, dan petunjuk bukti slide 2–3.
- Identitas terisi dari `/libraries`; nama pengisi terisi dari akun dan dapat dikoreksi.
  Data lain yang belum diketahui tidak diubah menjadi nol secara diam-diam.
- Referensi koleksi jejaring dapat digunakan setelah konfirmasi pengelola. Tidak ada
  penyalinan otomatis kunjungan simulasi, asumsi orang unik, atau estimasi lisensi digital.
- Tautan HTTPS umum dan per indikator; file tetap di Drive masing-masing. Tidak ada
  upload berkas pendukung ke aplikasi, pengunduhan otomatis, atau pengaturan akses Drive.
- Perbandingan identitas induk vs form, peringatan, keputusan petugas dengan alasan,
  dan penguncian verifikasi selama perbedaan belum diselesaikan.
- Koreksi form/verifikasi tidak menimpa induk. Perubahan induk tetap tindakan pemkab
  terpisah melalui `/libraries`.
- Snapshot per periode dan definisi form, versi untuk mencegah penimpaan perubahan,
  serta riwayat tindakan. Hapus memakai pengarsipan, bukan menghapus sejarah.
- Rekap indikator terverifikasi dan ekspor CSV/Excel asli; data lokal dibatasi scope.
- Analisis Yeo–Johnson, min–max, empat subindeks, bobot 30/70, dan penyesuaian cakupan,
  dengan asumsi/parameter yang ditampilkan. Hasil diberi label **simulasi internal**.
- Jenis/subjenis diterapkan pada input perpustakaan, tabel/filter, profil lokal dan
  popup peta. Peta tetap independen dari filter tabel dan tidak membocorkan bukti/pengisi.

## Hasil klasifikasi data lama

- 363 SD → Perpustakaan Sekolah / SD.
- 39 SMP → Perpustakaan Sekolah / SMP.
- 7 TK → Perpustakaan Sekolah / TK (perluasan lokal).
- 1 SKB → Perpustakaan Sekolah / SKB (perluasan lokal).
- 1 Perpustakaan Umum Daerah → Perpustakaan Umum / Kabupaten-Kota.

Total 411 perpustakaan, 410 di antaranya sekolah/lembaga pendidikan yang diimpor.
Jenis lama (Daerah, Desa, Swasta, Komunitas Literasi, Mitra Pojok Baca) tetap ada
setelah jenis prioritas Umum, Sekolah, Khusus. Tidak ada penghapusan kategori lama.
ID, kode/NPSN, nama, alamat, GPS, status/verifikasi, akun dan password dipertahankan.

## Status periode dan hal yang belum diaktifkan

Periode 2026 dibuat **draft**, tanggal 1 Januari–31 Desember 2026 masih placeholder,
`dates_confirmed=0`, populasi belum diisi. Draft dapat disimpan, tetapi pengiriman
dan verifikasi memerlukan konfirmasi tanggal; pengiriman juga memerlukan status open.

Belum ada klaim/skor IPLM resmi, parameter pembanding nasional, integrasi unggah ke
Perpusnas, pemeriksaan otomatis izin/isi Drive, ataupun sinkronisasi balik otomatis
ke master. Hal-hal ini tidak disamarkan sebagai fitur yang sudah selesai.

Jawaban yang dibutuhkan disusun di [PERTANYAAN_DAN_KEPUTUSAN.md](PERTANYAAN_DAN_KEPUTUSAN.md).
Utamakan tanggal pengukuran, populasi resmi, cakupan TK/SKB, dan konflik D2/D3.

## Langkah memakai

1. Pemkab membuka **Pendataan IPLM → Pengaturan Form & Periode**.
2. Edit periode 2026, tetapkan rentang data, centang konfirmasi tanggal, isi populasi
   beserta sumbernya jika sudah ada, lalu ubah status menjadi open saat siap.
3. Tinjau petunjuk/kewajiban bukti. Anggaran pada PPT belum memiliki rincian bukti;
   ketentuan itu dapat disesuaikan tanpa mengarang persyaratan baru.
4. Admin lokal membuka **Laporan & Pendataan → Pendataan IPLM**, membuat/melanjutkan
   isian, memeriksa identitas dan koleksi, mengisi angka serta tautan, lalu mengirim.
5. Pemkab membuka form yang perlu diperiksa, menyelesaikan perbedaan per kolom,
   memeriksa bukti di Drive secara manual, dan memverifikasi atau mengembalikan.
6. Rekap dan analisis hanya menggunakan data sesuai kriteria yang dijelaskan pada
   halamannya. Ekspor rekap bukan klaim berkas resmi siap unggah ke Perpusnas.

## Pengujian

Pengujian menggunakan database terisolasi dan akun fiktif, bukan menambahkan angka
uji ke database utama:

- 36 pemeriksaan model IPLM: isolasi scope, validasi angka/URL/dropdown, konflik identitas,
  master tetap, versi form, verifikasi, pengarsipan, CRUD master, dan rumus analisis.
- 37 pemeriksaan HTTP IPLM/master: akses per peran, CSRF, scope, escaping, dashboard,
  ekspor XLSX, perubahan identitas perpustakaan dan penolakan pasangan jenis yang salah.
- 16 pemeriksaan tampilan desktop/ponsel: halaman lokal/pemkab/master, sidebar aktif,
  dropdown bertingkat, tidak ada overflow halaman ataupun error JavaScript.
- 42 pemeriksaan regresi model jejaring dan 70 pemeriksaan regresi HTTP jejaring.
- 27 pemeriksaan struktur/hak akses sidebar.
- 38 pemeriksaan peta read-only; seluruh 411 lokasi tetap tersedia sesuai scope.
- Migrasi diuji ulang: tidak menggandakan master, menu, komponen, atau isian.
- Lint PHP/JavaScript dan pemeriksaan syntax/diff untuk berkas terkait.

Kode pengujian: `tools/test_iplm.php`, `tools/test_iplm_http.php`,
`tools/test_iplm_browser.cjs`; fixture dibuat oleh `tools/prepare_library_network_test.php`
dan skema dipasang dengan `tools/setup_iplm.php --apply --test=<direktori-fixture>`.

## Backup dan pemeriksaan produksi

Backup sebelum migrasi:

`/www/backup/pustaka-network/before-network-20261005-093146-6e596936.sql.gz`

Manifest, hasil klasifikasi, dan verifikasi privat memakai awalan file yang sama:
`.json`, `.iplm-mapping.json`, `.iplm-verification.json`.

Bukti pengujian (screenshot akun fiktif dan ringkasan hasil) disimpan privat di:
`/www/backup/pustaka-network/iplm-20261005-093146/`.
Database dan runtime uji, serta hasil konversi sementara dokumen, sudah dibersihkan.
Kode pengujian tetap tersedia untuk membuat ulang fixture; Excel/PPT sumber tidak diubah.

Hasil pemeriksaan migrasi: jumlah akun/data operasional tidak berubah; fingerprint
identitas yang harus dipertahankan dan akun/password tidak berubah. Jumlah isian
IPLM produksi setelah pemasangan: **0**. Backup telah lolos uji integritas gzip.

Migrasi bersifat penambahan skema dan klasifikasi terarah. Jangan menjalankan SQL
rollback manual atau menghapus tabel tanpa rencana pemulihan; setelah pendataan
berjalan, memulihkan seluruh database lama dapat menimpa pekerjaan baru pengguna.
