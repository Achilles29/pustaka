# SOP Token Baca Digital — rancangan operasional v1

Status: angka awal v1 tersedia sebagai nilai awal dan dikelola pada menu **Pengaturan Token**; evaluasi pengelola tetap dilakukan setelah satu bulan.

## Prinsip

Semua pemustaka membaca **online** melalui akun member aktif. Pojok Baca bukan jenis akses lain; ia adalah zona lokasi dengan akses bebas tanpa token dan tanpa kuota sesi. Sesi `reading_sessions` tetap dibuat secara teknis hanya untuk keamanan PDF dan audit, bukan sebagai kuota. PDF adalah pengaturan terpisah: `Render terkunci` atau `Download diizinkan`.

| Kondisi saat membuka buku | Hasil |
| --- | --- |
| Member aktif berada di Perpustakaan Daerah, perpustakaan terdaftar GIS, atau Pojok Baca Digital aktif | Buku terbuka bebas tanpa token dan tanpa kuota sesi. |
| Member aktif berada di luar zona tersebut | Buku terbuka bila memiliki token aktif; 1 sesi buku mengurangi 1 kuota. |
| Member tidak aktif, atau aset Internal | Ditolak. |
| Buku yang sama masih memiliki sesi aktif (maks. 3 jam) | Tidak dipotong lagi saat refresh atau dibuka ulang. |

Satu kuota harus dibakukan sebagai **1 sesi akses buku**, bukan menit atau halaman. Sistem saat ini memotong kuota ketika sebuah sesi buku dimulai, bukan berdasarkan durasi atau halaman yang dibaca.

## Tiga jalur memperoleh token

1. **Kunjungan perpustakaan.** Petugas memvalidasi kehadiran di Perpustakaan Daerah melalui Monitor Buku Tamu (pencarian member atau QR). Token kunjungan diterbitkan otomatis ke member aktif.
2. **Permohonan token.** Member mengajukan alasan singkat; petugas memeriksa status aktif dan riwayat pemakaian, lalu menyetujui atau menolak. Keputusan harus tercatat dengan petugas dan waktu.
3. **Arena Belajar.** Member menukar poin pada katalog hadiah yang aktif. Penerbitan dan penukaran poin tercatat otomatis.

## Usulan angka awal untuk disahkan

| Jalur | Kuota | Masa berlaku | Pengendalian |
| --- | ---: | --- | --- |
| Kunjungan perpustakaan / check-in | Sesuai Pengaturan Token (awal: 5) | Sesuai Pengaturan Token (awal: hingga 23:59 hari yang sama) | Batas penerbitan harian diatur pada Pengaturan Token (awal: 1). |
| Permohonan token | Sesuai Pengaturan Token (awal: 3) | Sesuai Pengaturan Token (awal: 7 hari) | Maksimum satu permohonan menunggu. |
| Arena Belajar | 5 atau 10 sesi buku | 14 atau 30 hari | Harga poin dan jumlah sesi dikelola dari master hadiah Arena. |

Pengelola dapat mengganti angka ini pada menu **Layanan Digital → Pengaturan Token**. Nilai tidak diubah langsung pada token yang telah terbit, hanya pada token baru berikutnya.

### Implementasi check-in Buku Tamu

- Monitor Buku Tamu menggunakan Perpustakaan Daerah sebagai lokasi bawaan; pengaturan ini dapat diubah pada menu **Pengaturan Buku Tamu**.
- Check-in member yang valid di lokasi tersebut menerbitkan token `VIS-` sesuai jumlah kuota dan masa berlaku pada **Pengaturan Token**.
- Batas penerbitan token `VIS-` per member per hari juga mengikuti **Pengaturan Token**. Check-in berikutnya tetap tercatat sebagai kunjungan, tetapi tidak menerbitkan token jika batas tercapai.
- Check-in pada monitor yang dikonfigurasi ke lokasi selain Perpustakaan Daerah tidak menerbitkan token kunjungan.

## Aturan pemotongan dan audit

- Potong **1 sesi** hanya ketika member mulai membaca judul/aset yang belum memiliki sesi aktif dalam tiga jam terakhir.
- Jangan meminta, menautkan, atau memotong token saat GPS menunjukkan zona gratis (Perpustakaan/GIS/Pojok Baca).
- Jangan potong karena ganti halaman, refresh, atau membuka ulang sesi yang sama.
- Simpan pada audit: member, judul, waktu, koordinat/zona, token yang dipakai, kuota sebelum/sesudah, dan asal akses.
- Token habis, kedaluwarsa, atau dicabut tidak dapat dipakai untuk akses luar zona.

## Perubahan data sebelum diberlakukan penuh

Master lama masih dapat menyimpan satuan `menit` dan `halaman` untuk jejak historis, tetapi paket hadiah dan titik aktif telah dinormalisasi ke satuan `buku`/sesi. Sistem menandai unit lama sebagai legacy agar tidak disalahartikan sebagai pemotongan berbasis durasi.

## Tanggung jawab

- **Admin Pojok Baca:** menjaga titik, radius GIS, status aktif, serta Pengaturan Token.
- **Petugas layanan:** memeriksa dan memutus permohonan token sesuai batas SOP.
- **Admin Arena:** mengelola harga poin, jumlah sesi, dan masa berlaku hadiah.
- **Admin sistem:** memantau audit, pemakaian tak wajar, dan pencabutan token bila perlu.
