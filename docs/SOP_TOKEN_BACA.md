# SOP Token Baca Digital — rancangan operasional v1

Status: angka awal v1 sudah diterapkan pada paket token baru; evaluasi pengelola tetap dilakukan setelah satu bulan.

## Prinsip

Semua pemustaka membaca **online** melalui akun member aktif. Pojok Baca bukan jenis akses lain; ia adalah zona lokasi yang membuat sesi baca gratis. PDF adalah pengaturan terpisah: `Render terkunci` atau `Download diizinkan`.

| Kondisi saat membuka buku | Hasil |
| --- | --- |
| Member aktif berada di Perpustakaan Daerah, perpustakaan terdaftar GIS, atau Pojok Baca Digital aktif | Buku terbuka, token tidak berkurang. |
| Member aktif berada di luar zona tersebut | Buku terbuka bila memiliki token aktif; 1 sesi buku mengurangi 1 kuota. |
| Member tidak aktif, atau aset Internal | Ditolak. |
| Buku yang sama masih memiliki sesi aktif (maks. 3 jam) | Tidak dipotong lagi saat refresh atau dibuka ulang. |

Satu kuota harus dibakukan sebagai **1 sesi akses buku**, bukan menit atau halaman. Sistem saat ini memotong kuota ketika sebuah sesi buku dimulai, bukan berdasarkan durasi atau halaman yang dibaca.

## Tiga jalur memperoleh token

1. **Kunjungan perpustakaan.** Petugas memvalidasi kehadiran di Perpustakaan Daerah atau titik perpustakaan terdaftar GIS. Token kunjungan diterbitkan ke member aktif.
2. **Permohonan token.** Member mengajukan alasan singkat; petugas memeriksa status aktif dan riwayat pemakaian, lalu menyetujui atau menolak. Keputusan harus tercatat dengan petugas dan waktu.
3. **Arena Belajar.** Member menukar poin pada katalog hadiah yang aktif. Penerbitan dan penukaran poin tercatat otomatis.

## Usulan angka awal untuk disahkan

| Jalur | Kuota | Masa berlaku | Pengendalian |
| --- | ---: | --- | --- |
| Kunjungan perpustakaan / check-in | 5 sesi buku | sampai 23:59 hari yang sama | Maksimum satu token kunjungan per member per hari. |
| Permohonan token | 3 sesi buku | 7 hari | Maksimum satu permohonan menunggu dan satu persetujuan per 7 hari. |
| Arena Belajar | 5 atau 10 sesi buku | 14 atau 30 hari | Harga poin dan jumlah sesi dikelola dari master hadiah Arena. |

Pengelola dapat mengganti angka ini setelah evaluasi satu bulan; angka tidak boleh diubah langsung pada token yang telah terbit, hanya pada paket penerbitan berikutnya.

## Aturan pemotongan dan audit

- Potong **1 sesi** hanya ketika member mulai membaca judul/aset yang belum memiliki sesi aktif dalam tiga jam terakhir.
- Jangan potong saat GPS menunjukkan zona gratis (Perpustakaan/GIS/Pojok Baca).
- Jangan potong karena ganti halaman, refresh, atau membuka ulang sesi yang sama.
- Simpan pada audit: member, judul, waktu, koordinat/zona, token yang dipakai, kuota sebelum/sesudah, dan asal akses.
- Token habis, kedaluwarsa, atau dicabut tidak dapat dipakai untuk akses luar zona.

## Perubahan data sebelum diberlakukan penuh

Master lama masih dapat menyimpan satuan `menit` dan `halaman` untuk jejak historis, tetapi paket hadiah dan titik aktif telah dinormalisasi ke satuan `buku`/sesi. Sistem menandai unit lama sebagai legacy agar tidak disalahartikan sebagai pemotongan berbasis durasi.

## Tanggung jawab

- **Admin Pojok Baca:** menjaga titik, radius GIS, status aktif, dan kuota token kunjungan.
- **Petugas layanan:** memeriksa dan memutus permohonan token sesuai batas SOP.
- **Admin Arena:** mengelola harga poin, jumlah sesi, dan masa berlaku hadiah.
- **Admin sistem:** memantau audit, pemakaian tak wajar, dan pencabutan token bila perlu.
