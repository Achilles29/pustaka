# Keputusan yang perlu dikonfirmasi

> Pembaruan 5 Oktober 2026: pertanyaan awal di bawah adalah riwayat. Jawaban pada `task2.md` sudah diterapkan; lihat [HASIL_TASK2.md](HASIL_TASK2.md) dan [PERTANYAAN_TASK2.md](PERTANYAAN_TASK2.md). Berlaku sekarang: penilaian 2026/data 2025, populasi master 403, TK/SKB dikecualikan, minimal D2, serta pemustaka sebagai kunjungan. Jangan memakai pilihan sementara D3/tanggal 2026 di bagian historis ini sebagai aturan aktif.

Dicatat 5 Oktober 2026. Bagian yang belum dikonfirmasi tidak boleh dianggap sebagai
ketentuan IPLM resmi. Modul pendataan dapat disiapkan tanpa menunggu seluruh jawaban.

1. **Rentang data IPLM 2026**: tanggal awal dan akhir yang ditetapkan untuk “satu tahun
   terakhir” apa? Tahun kalender belum tentu sama dengan periode survei. Periode awal
   disiapkan sebagai **draft**, tanggal 1 Januari–31 Desember 2026 hanya placeholder.
   Admin perlu mengonfirmasi tanggal sebelum membuka pengiriman formulir.
2. **Populasi resmi**: berapa jumlah perpustakaan yang menjadi populasi IPLM Kabupaten
   Rembang, beserta sumber/tanggal penetapannya? Jumlah entri `/libraries` tidak otomatis
   dianggap populasi lengkap. Tanpa populasi, penyesuaian cakupan tidak dapat dihitung.
3. **TK dan SKB**: apakah hanya untuk pendataan internal atau masuk responden resmi?
   Keduanya tetap mendapat subjenis dan dapat didata; default tidak masuk analisis
   IPLM resmi karena tidak tercantum pada pilihan kabupaten/kota template ini.
4. **Kualifikasi tenaga**: sheet Definisi menyebut minimal D2, tetapi Perpusnas 7/2025
   Pasal 14 ayat (3) menyebut paling rendah D3. Apakah tersedia petunjuk teknis terbaru
   atau koreksi template? Form menyertakan kedua sumber dan mengarahkan minimal D3
   sampai ada konfirmasi tertulis. Jangan menganggap D2 otomatis memenuhi D3.
5. **Bukti anggaran**: slide 2–3 tidak merinci dokumen pendukung semua indikator
   anggaran. Dokumen apa yang wajib (RKAS/DPA/realisasi/lainnya)? Sementara petunjuk
   ditandai belum ditetapkan; admin dapat mengubah ketentuan form.
6. **Parameter analisis**: apakah tersedia pedoman teknis/dataset pembanding nasional
   yang menentukan lambda Yeo–Johnson, batas min–max, pengelompokan responden,
   pembobotan indikator, perlakuan anggaran BOS/non-BOS/total, dan indikator tambahan?
   Tanpa parameter itu, analisis berbasis sampel lokal hanya simulasi internal,
   bukan hasil IPLM resmi dan bukan pembanding langsung skor nasional.
7. **Pemustaka (orang)**: apakah angka dimaksud adalah orang unik sepanjang periode
   atau jumlah pemanfaatan/kunjungan? Data kunjungan berulang dan data simulasi yang
   pernah dimasukkan tidak akan otomatis dikonversi menjadi pemustaka IPLM.

## Pilihan teknis yang aman dan tidak perlu mengulang pengisian

- `/libraries` tetap data induk; `iplm_submissions` menyimpan snapshot/isian per periode.
  Ini bukan dua master yang bersaing. Menggabungkan seluruhnya ke satu tabel
  “pendataan” justru akan menghilangkan perbedaan identitas terkini dan riwayat tahunan.
- Identitas terisi otomatis dari induk. Koreksi lokal hanya mengubah formulir dan
  menimbulkan peringatan; verifikasi tidak otomatis menimpa data induk.
- Simpan draft boleh belum lengkap. Nol adalah nilai yang benar-benar dilaporkan,
  sedangkan kosong berarti belum diisi; tidak otomatis diganti nol.
- Google Drive tetap milik pengelola masing-masing. Aplikasi hanya menyimpan tautan,
  tidak mengunggah, mengunduh, ataupun mengubah izin berbagi secara otomatis.
- Jenis lama tetap dipertahankan di master setelah pilihan IPLM. TK/SKB ditandai
  perluasan lokal, bukan dipaksakan menjadi SD/SMP.

## Catatan paparan

Slide 15 menulis 68% sama dengan 2/3, padahal 2/3 ≈ 66,67%. Gunakan angka eksplisit
68% untuk simulasi sesuai paparan, jangan menggantinya diam-diam. Contoh gambar
`2/216 × 70 = 0,64` juga tidak tepat secara aritmetika: hasilnya ≈ 0,648148 (0,65
jika dibulatkan dua desimal). Aplikasi perlu menghitung presisi, bukan menyalin salah
pembulatan pada slide. Ambang jumlah minimum dibulatkan ke atas karena responden
berupa bilangan bulat; ketentuan pembulatan resmi tetap perlu konfirmasi.

## Sumber yang diperiksa

- Template lokal `Template_IPLM_2026.xlsx`: IPLM Kab-Kota, Validasi Data, Definisi.
- `Format Bukti Dukung.pptx`, slide 2–3.
- `bahan_iplm.pptx`, slide 13–15 (paparan bertanda Kajian Perpustakaan Indonesia 2025).
- [Peraturan Perpusnas Nomor 7 Tahun 2025](https://peraturan.go.id/files/perpusnas-no-7-tahun-2025.pdf):
  Pasal 8, 10–11, 13–15. PDF berhasil diunduh dan dibaca dari sumber pemerintah.
  Penghitungan/penetapan resmi berada pada Perpusnas; dashboard daerah adalah alat
  pengumpulan, verifikasi, rekap dan analisis internal, bukan penetapan skor resmi.
- [Koordinasi Kajian Perpustakaan Indonesia 2026](https://www.perpusnas.go.id/berita/perpusnas-perkuat-koordinasi-dalam-kajian-perpustakaan-indonesia-2026):
  ringkasan hasil pencarian situs resmi menguatkan bobot 30% kepatuhan dan 70%
  kinerja. Halaman penuh menolak akses otomatis (403); rumus implementasi bersumber
  langsung dari slide yang diberikan, bukan mengandalkan halaman yang tidak terbaca.
