# Keputusan lanjutan task3

## 1. Verifikasi aktivasi akun publik — perlu dipilih sebelum login mandiri diaktifkan

Halaman pencarian, peta dan modal sudah tersedia pada `/aktivasi-perpustakaan`. Namun nama sekolah, NPSN dan lokasinya adalah data publik, bukan bukti bahwa pengunjung merupakan pengelola. Modal konfirmasi saja tidak boleh membuka akun atau mengganti password.

Pilih salah satu:

1. **Kode aktivasi unik per perpustakaan**, dibagikan admin kabupaten kepada pengelola resmi. Rekomendasi untuk aktivasi massal. Kode harus acak, sekali pakai, kedaluwarsa, dibatasi percobaannya, dan tidak ditampilkan pada peta/API publik. Kode yang benar baru mengizinkan penggantian password akun yang belum diaktivasi.
2. **Permintaan aktivasi disetujui admin kabupaten**, setelah memeriksa penanggung jawab melalui saluran resmi. Perlu form permintaan, antrean persetujuan dan pemberian akses satu kali setelah pemeriksaan.

Jawaban cukup: **“kode aktivasi unik”** atau **“persetujuan admin kabupaten”**. Setelah dipilih, mekanisme tersebut dapat diimplementasikan tanpa membuka celah pengambilalihan akun yang sudah aktif. Sampai saat itu, login dengan kredensial resmi tetap berfungsi; tidak ada password sekolah yang diumumkan.

## 2. Konfirmasi persandingan unit sebelum import

Periksa [PERSANDINGAN_DATABASE_PENDATAAN_TASK3.xlsx](PERSANDINGAN_DATABASE_PENDATAAN_TASK3.xlsx), khususnya 65 kandidat kuning, 8 baris ganda jingga (4 pasangan), 394 hanya pendataan dan 27 hanya database.

- Untuk kandidat: **ID pendataan → ID database yang benar**, atau **“lembaga berbeda/belum ada”**.
- Untuk ganda: konfirmasi apakah satu perpustakaan dan **ID sumber mana yang menjadi rujukan utama**. Kedua riwayat tidak dihapus otomatis.
- Untuk unit satu sisi: konfirmasi boleh ditambahkan ke sisi lainnya setelah pasangan diperiksa. Saat ini belum ada import atau perubahan file sumber.

NPSN menggunakan database/Dapodik setelah pasangan unit disahkan; tidak perlu menjawab ulang konflik nilainya. Pasangan Perpusda sudah dicatat sesuai jawaban. Data pendataan dianggap terupdate, tidak otomatis dinyatakan statistik 2025; belum diperlukan jawaban ulang tahun sampai ada permintaan memasukkan angka tersebut ke periode tertentu.
