# Laporan kunjungan berdasarkan tujuan

Ditambahkan 30 September 2026 pada `/reports/visits`.

- Rekap tujuan menampilkan jumlah orang, entri buku tamu, persentase orang, dan total. Mengikuti periode harian/bulanan/tahunan/custom dan filter semua/offline/online.
- Tujuan menggunakan `member_visits.purpose_label`, sesuai isian buku tamu. Spasi di tepi diabaikan. Jika label kosong tetapi ID tujuan tersedia, nama dicari pada referensi INLIS `tujuan_kunjungan`. ID yang tidak dikenali tetap ditampilkan sebagai `Tujuan lama (ID …)`; tanpa label/ID menjadi `Belum diisi`.
- Jumlah orang adalah akumulasi `visitor_count`, termasuk peserta rombongan, bukan pengunjung unik. Entri adalah jumlah rekaman kunjungan. Tidak memakai join rincian demografi untuk rekap utama sehingga satu kunjungan tidak dihitung berulang.
- Tujuan otomatis `Akses layanan digital` (dashboard member) dan `Baca buku digital` digabung ke `Layanan digital` pada rekap, cetak/PDF, Excel, dan dimensi Format Laporan. Normalisasi hanya saat membaca laporan; rekaman asli dan detail kunjungan tidak diubah. Tujuan berbeda dari kanal/offline-online; pengunjung fisik tetap dapat memilih tujuan “Layanan digital”. Dengan enam tujuan buku tamu dan kedua alias ini, rekap menampilkan enam kategori, bukan delapan. Tujuan lama/lainnya dan `Belum diisi` tetap dipertahankan jika ada agar total tidak berkurang.
- Rekap disertakan pada cetak/PDF dan ekspor Excel yang sudah tersedia. Persentase dibulatkan satu desimal sehingga penjumlahan persentase tampil dapat sedikit berbeda dari 100%.
- Tombol **Format per tujuan** mempertahankan periode dan cakupan serta membuka tabel tujuan tanpa pemecah gender, agar data tanpa jenis kelamin tetap disertakan. Variabel **Tujuan Kunjungan** juga tersedia sebagai baris atau kolom dalam Format Laporan, termasuk ekspornya. Jika pengguna memilih pemecah gender, aturan lama pengecualian gender kosong tetap berlaku dan ditampilkan pada catatan tabel.

Tidak ada migrasi atau perubahan data kunjungan. Izin halaman dan ekspor yang sudah ada tetap berlaku.

## Pengujian

`php tools/test_visit_purposes.php`

31 pemeriksaan: rekonsiliasi total data aktual per cakupan, penggabungan alias digital tanpa mengubah data asli, filter kanal untuk tujuan digital, kelompok rombongan, batas tanggal, spasi/tujuan kosong, referensi lama, periode kosong, dimensi silang dan demografi tanpa penggandaan, serta pengamanan label HTML/Excel. Fixture menggunakan tabel sementara pada koneksi pengujian, tanpa menulis data kunjungan produksi.

Browser diuji pada 1440 dan 390 piksel untuk semua/offline/online; cetak, format tujuan, periode kosong, serta kedua ekspor memberikan respons 200 tanpa error PHP/JavaScript.
