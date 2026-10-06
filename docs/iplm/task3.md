# Pertanyaan lanjutan sesudah task2

Keputusan Anda tentang tahun data 2025, populasi `/libraries`, pengecualian TK/SKB, minimal D2, bukti dukung, dan pemustaka sebagai kunjungan **sudah diterapkan**. Tidak perlu menjawab ulang pertanyaan lama.

## Yang perlu jawaban untuk import pendataan berikutnya

1. **Tahun angka pendataan**: angka koleksi, siswa, tenaga, anggaran, dan kunjungan di `pendataan.xls` menggambarkan tahun/periode apa? Apakah sama untuk semua baris? Tanpa ini, angka tidak bisa dinyatakan sebagai data IPLM 2025. Waktu Created At/Updated At tidak cukup.

=> data tersebut adalah data terupdate

2. **68 kandidat dan empat pasangan ganda**: mohon verifikasi `PENCOCOKAN_PENDATAAN.xlsx`. Untuk kandidat, jawab dengan **ID sumber → ID library yang benar**, atau “lembaga berbeda/belum ada”. Untuk baris ganda, apakah benar satu perpustakaan dan ID sumber mana yang menjadi rujukan utama? Kedua riwayat tetap dipertahankan.
=> PENCOCOKAN_PENDATAAN itu maksudnya gimana? saya belum melihat persandingan antara database dan pendataan. harusnya dibuat dalam 1 file excel SEBELAH kiri kolom database sebelah kanan kolom pendataan, yang sudah sama berarti dibuat sejajar, yang perlu konfirmasi berarti sejajar tapi beri warna dan keterangan berbeda, yang salah satu tidak ada berarti  kolom sebelahnya dikosongi 


3. **Konflik NPSN**: khusus baris 52 (SD Negeri Demaan) dan 277 (SMP Negeri 1 Bulu), nilai mana yang benar? Cocokkan dengan dokumen sekolah sebelum mengubah sumber atau master.
=> untuk NPSN yang berbeda, gunakan database karena itu dari dapodik. tapi beri tanda di kolom pendataannya.


4. **Perpusda**: apakah ID sumber 50964, NPP `3317103E1000003`, “Perpustakaan Umum Kabupaten Rembang” benar perpustakaan yang sama dengan master ID 2 “PERPUSTAKAAN UMUM DAERAH”? Jika ya, pasangan dan NPP dapat ditetapkan secara eksplisit.
=> untuk perpusda betul tersebut di pendataan adalah datanya

Jadi pertama kita sandingkan unitnya dulu, setelah dikonfirmasi nanti yang belum masuk di database kita masukkan, dan yang belum ada di pendataan masukkan juga dengan warna dan keterangan berbeda

Tambahan:
- untuk jumlah populasi memang menggunkaan total data, tapi dapat diatur manual populasinya

form input tautan bukti dukung jadi 1 saja ya.
untuk yang ditampilkan di masing maising soal itu keterangannya aja, bukti dukungnya berupa apa sesuai di excel bukti dukung



masing masing perpustakaan (kecuali perpustakaan pusat) belum tau kalau mereka punya akun. jadi polanya nanti buatkan halaman publik untuk masing masing perpustakaan mencari data mereka. halaman tersebut menampilkan peta dan ada kolom pencarian ajax data nama perpustakaan atau nama sekolah atau npsn. jadi user pertama masuk halaman bisa mencari di gps atau mengetik nama tadi di form pencarian. lalu di pilih, setelah dipilih akan ada modal warning untuk meyakinkan bahwa itu adalah milik user, setelah oke langsung login dan ganti password.