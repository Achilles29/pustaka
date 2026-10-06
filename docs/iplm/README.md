# Modul pendataan IPLM

Mulai membaca dari:

0. **[Hasil terbaru task6](HASIL_TASK6.md)** — aktivasi mandiri tanpa kode/persetujuan awal kabupaten, moderasi/reset akun, dua baris tab direktori, dan pertanyaan sekolah yang dipisah per kasus.
0. **[Perapian jenis dan legenda](HASIL_PERAPIAN_JENIS.md)** — legenda dua kolom, penghapusan tiga jenis duplikat dari database/UI/pilihan enum, serta validasi pencegahan duplikasi.
0. **[Hasil terbaru task5](HASIL_TASK5.md)** — ralat data IPLM menjadi tahun 2026, perapian direktori/filter peta, 42 master diperbarui, dan Excel penyandingan baru.
0. **[Verifikasi tersisa task5](PERTANYAAN_TASK5.md)** — tiga konflik dan keputusan cakupan import sekolah yang belum memiliki padanan pasti.
0. **[Hasil terbaru task4](HASIL_TASK4.md)** — aktivasi kode unik, menu admin, 421 unit tambahan, jenis/subjenis dan hasil pengujian.
0. **[Verifikasi tersisa task4](PERTANYAAN_TASK4.md)** — 35 baris sekolah, kelengkapan lokasi, dan snapshot populasi.
0. **[Hasil terbaru task3](HASIL_TASK3.md)** — satu Excel persandingan kiri/kanan, populasi manual, satu tautan bukti, pencarian publik, serta batas aktivasi akun.
0. **[Keputusan yang masih dibutuhkan](PERTANYAAN_TASK3.md)** — verifikasi aktivasi dan konfirmasi pasangan sebelum import.
0. **[Hasil tugas lanjutan task2](HASIL_TASK2.md)** — keputusan terbaru, form bertab, populasi 403, pemetaan pendataan dan daftar berkas Excel.
1. [Hasil implementasi dan cara memakai](HASIL_IMPLEMENTASI.md).
2. [Pemetaan seluruh 43 kolom, definisi, dan bukti dukung](PEMETAAN_FORM.md).
3. [Pertanyaan yang membutuhkan jawaban / keputusan](PERTANYAAN_DAN_KEPUTUSAN.md).
4. [Rancangan penyimpanan, verifikasi, dan analisis](ARSITEKTUR_DAN_ANALISIS.md).
5. [Pemetaan terstruktur untuk pemeriksaan teknis](pemetaan-template.json).

Dokumen permintaan asli: [task.md](task.md). Template Excel dan kedua PPT sumber
tidak diubah.

## Keputusan terbaru dan langkah berikutnya

**Task6 menggantikan alur kode task4:** pengelola dapat mendaftar dan langsung masuk melalui `/aktivasi-perpustakaan` tanpa menunggu kabupaten. Akun yang sudah pernah digunakan tidak dapat diklaim ulang. Kabupaten menangani moderasi/reset setelah ada masalah; kepemilikan pada pendaftaran adalah pernyataan pengelola, bukan verifikasi awal. Baca [hasil task6](HASIL_TASK6.md). Jawaban yang ditunggu sekarang tersusun per sekolah di [PERTANYAAN_TASK5.md](PERTANYAAN_TASK5.md).

**Task5 sudah diterapkan. Pengukuran IPLM 2026 sekarang memakai data 1 Januari–31 Desember 2026, bukan 2025.** Dua isian lama tetap disimpan dan diminta revisi. Gunakan [penyandingan sekolah task5](PERSANDINGAN_SEKOLAH_TASK5.xlsx) dan [penyandingan pendataan task5](PERSANDINGAN_PENDATAAN_TASK5.xlsx). Dokumen task sebelumnya adalah riwayat; angka/tahun lama di bawah tidak menggantikan keputusan ini.

Task4 sudah diterapkan: aktivasi dengan kode unik tersedia melalui `/library-activation`; import aman menghasilkan 832 master perpustakaan. Populasi master dalam cakupan kini 738, tetapi snapshot periode lama tetap dipertahankan sampai admin menyimpannya kembali. Gunakan [persandingan task4](PERSANDINGAN_TASK4.xlsx). Pernyataan “aktivasi belum diaktifkan/import menunggu” pada dokumen task3 adalah riwayat, bukan status terbaru.

Task3 memperbarui ketentuan task2: populasi dapat dipilih manual oleh admin, form memiliki satu input tautan bukti, dan pendataan dianggap data terupdate (tidak otomatis statistik 2025). Gunakan [Excel persandingan task3](PERSANDINGAN_DATABASE_PENDATAAN_TASK3.xlsx) untuk pemeriksaan unit. Pertanyaan task2 di bawah adalah riwayat; jawaban terbaru dan pertanyaan tersisa ada di dokumen task3.

Riwayat jawaban pada [task2.md](task2.md), **tahun datanya telah diralat oleh task5**: dahulu penilaian 2026 memakai data tahun 2025,
populasi dihitung dari `/libraries` yang masuk cakupan (403 saat penerapan), TK/SKB
dikecualikan, minimal D2, dan pemustaka dihitung sebagai kunjungan.

Pertanyaan **baru** untuk verifikasi/penggabungan pendataan ada di
[PERTANYAAN_TASK2.md](PERTANYAAN_TASK2.md), terutama tahun statistik sumber, kandidat
nama/NPSN, serta baris ganda. Pertanyaan lama disimpan sebagai riwayat, bukan untuk
dijawab ulang.

Periode masih Persiapan; admin dapat membukanya pada `/iplm/settings`.
Analisis lokal tetap bukan skor resmi Perpusnas.

## Task7 tanggal 6 Oktober 2026

[Hasil task7](HASIL_TASK7.md): penyandingan master Dapodik, 869 unit baru, GPS resmi/cadangan, formulir tambahan lokal, kontak wajib, panduan pengelola dan seleksi populasi. Kasus yang belum pasti tersedia pada [pertanyaan task7](PERTANYAAN_TASK7.md). Jumlah master bukan populasi IPLM; snapshot lama tetap sampai penetapan periode diperbarui.
