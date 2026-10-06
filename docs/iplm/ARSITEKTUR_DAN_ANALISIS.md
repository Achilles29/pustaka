# Penyimpanan dan analisis IPLM

> Pembaruan task2: TK/SKB sekarang dikecualikan dari pendataan maupun analisis IPLM, bukan hanya analisis. Populasi berupa snapshot perpustakaan aktif dalam cakupan `/libraries`; saat diterapkan berjumlah 403. Penilaian 2026 memakai tahun data 2025 dan kualifikasi minimal D2 sesuai keputusan pengelola. Rincian terbaru: [HASIL_TASK2.md](HASIL_TASK2.md).

## Rekomendasi: satu data induk, isian berkala terpisah

`libraries` tetap identitas terkini perpustakaan. Jangan memindahkan seluruh isinya
ke satu tabel “pendataan” bersama angka tahunan. Identitas dan metrik mempunyai
masa berlaku serta proses verifikasi berbeda.

Hubungan data:

```text
library_types → library_subtypes
                      ↓
                  libraries
                      ↓ satu perpustakaan, banyak periode
iplm_periods → iplm_submissions → iplm_history
                      ↑
                 iplm_fields
               (snapshot versi)
```

- `library_types`: master jenis lama + Umum/Khusus, dengan urutan dan warna.
- `library_subtypes`: subjenis, jenis induk, status aktif, dan penanda cakupan IPLM.
- `libraries`: tambahan `library_subtype_id`, `institution_name`, `npp`.
  Kode sekolah tetap menjadi sumber NPSN; ID, kode, nama, GPS, status dan akun tidak
  diganti. Foreign key pasangan subjenis–jenis mencegah pasangan yang salah.
- `iplm_periods`: tahun, rentang data, konfirmasi tanggal, status dan populasi/sumbernya.
- `iplm_fields`: label, definisi, tipe, opsi dropdown, kelompok, urutan, kewajiban,
  petunjuk/kewajiban bukti. Perubahan berlaku untuk isian baru.
- `iplm_submissions`: satu isian aktif per perpustakaan/periode; snapshot identitas
  awal, snapshot definisi, nilai, tautan bukti, keputusan perbedaan, versi, status.
  Snapshot ini adalah riwayat yang disengaja, bukan master ganda yang harus sinkron.
- `iplm_history`: jejak perubahan isian, keputusan verifikasi, pengaturan dan master.

Snapshot nilai/definisi disimpan JSON agar komponen dapat dikelola tanpa mengubah
struktur tabel setiap kali indikator ditambah. Relasi identitas, periode, status,
versi dan kepemilikan tetap kolom terstruktur, berindeks, dan memakai foreign key.

## Alur kerja

1. Pengelola membuat isian untuk perpustakaan sendiri (pemkab dapat memilih lembaga).
2. Identitas diisi otomatis. Angka yang belum diketahui tetap kosong.
3. Simpan draft atau kirim ketika periode sudah dibuka dan seluruh kewajiban terpenuhi.
4. Pengiriman mengunci perubahan lokal sampai pemkab mengembalikan untuk revisi.
5. Pemkab melihat perbandingan nilai induk sekarang, nilai usulan, dan peringatan.
6. Petugas memilih data induk benar (koreksi nilai form), atau data form benar untuk
   laporan (tetap tidak menimpa induk), disertai alasan/bukti.
7. Isian lengkap tanpa perbedaan tertunda dapat diverifikasi. Perubahan induk setelah
   keputusan membuat perbedaan diperiksa ulang apabila nilai pembanding berubah.
8. Hapus isian adalah arsip logis; riwayat dipertahankan. Isian baru untuk periode
   sama boleh dibuat setelah pengarsipan.

Jika perlu memperbarui master berdasarkan hasil verifikasi, pemkab melakukannya
secara terpisah melalui `/libraries/edit/{id}`. Tidak ada sinkronisasi balik otomatis.
Mengedit ulang form membatalkan keputusan perbedaan sebelumnya sehingga perubahan
baru tidak mewarisi persetujuan lama secara diam-diam.

Versi isian mencegah tab/petugas yang memegang data lama menimpa perubahan terbaru.
Perubahan tanggal periode hanya boleh saat tidak ada isian aktif terkirim/terverifikasi;
draft/revisi yang sudah ada ditandai perlu pemeriksaan ulang dan versinya dinaikkan.
Tahun periode yang sudah berisi data tidak dapat diganti.

## Akses dan keamanan

- Peran `LIBRARY_ADMIN` tetap satu tingkatan. Hak `iplm.local` dibatasi ke library_id
  akun, termasuk form, riwayat, dashboard dan ekspor. Scope kiriman browser tidak dipercaya.
- Pemkab: `iplm.manage` (rekap/CRUD/verifikasi/ekspor), `iplm.settings` (form/periode),
  dan `library.types` (master jenis/subjenis), diberikan pada ADMIN/SUPERADMIN.
- Akun dengan scope perpustakaan tidak dapat menggunakan pengelolaan tingkat kabupaten.
- POST + token CSRF untuk mutasi baru; GET tidak mengubah data. Form identitas
  `/libraries` juga mendapat pemeriksaan CSRF pada simpan/tambah.
- Angka nonnegatif, batas panjang, URL HTTPS, pilihan dropdown, pasangan jenis/subjenis,
  serta versi diverifikasi server-side. Konten ditampilkan dengan escaping.
- Server tidak mengambil konten Drive/tautan lain, sehingga tautan tidak menjadi
  perintah unduh/permintaan jaringan server. Akses dan isi bukti harus diverifikasi manusia.
- Nilai yang diawali karakter formula diamankan pada CSV; XLSX memakai penulis yang
  menyimpan string sebagai teks. Bukti/data pengisi tidak dipublikasikan pada peta.

## Analisis: batas yang sengaja dinyatakan

Paparan slide 13–15 menetapkan urutan Yeo–Johnson → min–max → empat subindeks →
gabungan kepatuhan/kinerja berbobot 30/70, lalu penyesuaian responden. Namun sumber
yang diberikan tidak menetapkan seluruh parameter teknis. Karena itu halaman analisis
adalah **simulasi internal**, bukan penetapan hasil IPLM resmi.

Pilihan implementasi yang transparan:

- Hanya isian lengkap terverifikasi, identitas tanpa perbedaan tertunda, wilayah
  Jawa Tengah/Kab. Rembang, dan subjenis yang ditandai termasuk cakupan.
- TK/SKB/perluasan lokal tidak otomatis masuk analisis. Master dapat diubah petugas
  bila ada dasar kewenangan yang telah dikonfirmasi.
- 22 metrik inti: 8 koleksi, 3 tenaga, 7 pelayanan, 4 pengelolaan. Anggaran dan kebijakan
  daerah tetap direkap tetapi tidak diberi bobot tambahan yang belum ditetapkan.
- Lambda diestimasi per indikator dari sampel lokal dengan maksimum likelihood terbatas
  −5..5. Min/max juga dari sampel lokal; keduanya ditampilkan di halaman analisis.
- Setiap subindeks merupakan rata-rata indikatornya. Variabel konstan dinormalisasi 0
  hanya sebagai konvensi simulasi dan ditandai; jika semua konstan atau responden <2,
  tidak ada indeks. Nol tersebut bukan kesimpulan kualitas perpustakaan.
- Kepatuhan = `(koleksi + tenaga) / 2`; kinerja = `(pelayanan + pengelolaan) / 2`.
- Skor responden = `100 × (0,30 × kepatuhan + 0,70 × kinerja)`.
- Skor lokal awal = rata-rata skor responden, bukan parameter resmi agregasi nasional.
- Minimum responden = `ceil(0,68 × populasi)`; skor disesuaikan =
  `skor awal × min(1, responden/minimum)`. Tanpa populasi atau bila populasi kurang dari
  responden, skor penyesuaian tidak diterbitkan.
- Tidak ada kategori resmi tinggi/rendah yang dibuat sendiri. Sampel berbeda akan
  menghasilkan skala berbeda; jangan membandingkan simulasi ini dengan skor nasional
  maupun antarperiode.

Referensi rumus transformasi: [SciPy Yeo–Johnson](https://docs.scipy.org/doc/scipy/reference/generated/scipy.stats.yeojohnson.html).
Dasar kewenangan dan komponen: [Perpusnas 7/2025](https://peraturan.go.id/files/perpusnas-no-7-tahun-2025.pdf).
Rincian ketidakpastian dan pertanyaan ada di `PERTANYAAN_DAN_KEPUTUSAN.md`.
