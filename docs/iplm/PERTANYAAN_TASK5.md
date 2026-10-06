# Keputusan data sekolah — satu sekolah per kasus

Dirapikan pada task6, 6 Oktober 2026. **Belum ada perubahan untuk kasus-kasus berikut.** Jawab dengan nomor kasus dan keputusan Anda; setelah itu baru dikerjakan.

Tahun sudah selesai diputuskan: **data 2026 untuk pengukuran IPLM 2026**, bukan 2025.

Rujukan: [Excel penyandingan sekolah](PERSANDINGAN_SEKOLAH_TASK5.xlsx). “Baris Excel” di bawah adalah nomor baris pada **file sumber sekolah_kabupaten_rembang.xlsx**, bukan nomor baris hasil penyandingan.

## Kasus 1 — SMK Yos Sudarso

- ID perpustakaan: **1624**.
- Database: **SMK YOS SUDARSO**, NPSN **20315667**.
- Excel baris **857**: **SMK KATOLIK YOS SUDARSO REMBANG**, NPSN **20315657**.
- Masalah: NPSN database ternyata digunakan sekolah lain dalam Excel.
- Usulan: pertahankan ID perpustakaan dan seluruh relasinya; ubah nama institusi dan NPSN sesuai Excel. Nama perpustakaan tidak perlu otomatis diubah.

**Pertanyaan: Apakah ID 1624 benar sekolah yang ada pada baris 857, sehingga NPSN-nya boleh diganti menjadi 20315657?**

Jawaban Anda: …

## Kasus 2 — SMP Muhammadiyah Rembang

- ID perpustakaan: **1598**.
- Database: nama perpustakaan **SMP MUHAMMADIYAH 1 REMBANG**, nama institusi **SMP MUHAMMADIYAH REMBANG 1**, kode **PDT-54792**.
- Excel baris **867**: **SMP MUHAMMADIYAH REMBANG**, NPSN **20315667**.
- Masalah: nama database memiliki nomor **1**, sedangkan Excel tidak. NPSN Excel juga masih dipakai oleh ID 1624 pada kasus 1.
- Usulan jika benar satu sekolah: pakai nama institusi dan NPSN Excel, tanpa membuat perpustakaan baru. Koreksi dilakukan setelah konflik NPSN pada kasus 1 selesai.

**Pertanyaan: Apakah sekolah ID 1598 sama dengan SMP Muhammadiyah Rembang pada baris 867, tanpa nomor 1?**

Jawaban Anda: …

## Kasus 3 — SMK YPI Rembang

- ID perpustakaan: **1644**, nama perpustakaan **Graha Pustaka**, NPSN **20315658**.
- Database: Kecamatan **Sulang**, Desa **Kemadu**.
- Excel baris **858**: Kecamatan **Rembang**; desa dan alamat tidak dicantumkan.
- Masalah: kecamatan berbeda. Mengganti kecamatan saja akan membuat desa lama tidak konsisten.
- Data nama sekolah dan NPSN sudah cocok.

**Pertanyaan: Untuk SMK YPI Rembang, kecamatan dan desa yang benar apa? Jika tersedia, sertakan alamat atau tautan lokasi.**

Jawaban Anda: Kecamatan …; Desa …; Alamat/lokasi …

## Kasus 4 — SLB Negeri Rembang

- ID perpustakaan: **1632**.
- Database: kode **PDT-49252**, subjenis **SDLB**.
- Excel baris **852**: **SLB NEGERI REMBANG**, NPSN **20315824**, jenjang **SLB**.
- Usulan: gunakan NPSN **20315824**, buat subjenis **SLB**, lalu pindahkan ID 1632 dari SDLB ke SLB. ID dan relasi perpustakaan tetap sama. Tidak otomatis masuk cakupan perhitungan IPLM kabupaten.

**Pertanyaan: Apakah Anda setuju dengan koreksi NPSN dan subjenis untuk ID 1632 tersebut?**

Jawaban Anda: …

## Kasus 5 — SD Islam An-Nawawiyyah

- ID perpustakaan: **1586**, nama perpustakaan **Baitul Ulum**.
- Database: nama institusi **SD ISLAM AN-NAWAWIYYAH TASIKAGUNG REMBANG**, kode **PDT-61611**.
- Excel baris **829**: **SD ISLAM AN - NAWAWIYYAH**, NPSN **20315837**, Kecamatan **Rembang**.
- Masalah: database menyebut Tasikagung, Excel tidak. Masih berupa kandidat pasangan, belum dipastikan satu sekolah.
- Usulan jika cocok: isi NPSN dari Excel dan selaraskan nama institusi; nama perpustakaan Baitul Ulum tetap dipertahankan.

**Pertanyaan: Apakah ID 1586 merupakan sekolah yang sama dengan baris 829?**

Jawaban Anda: …

## Kasus 6 — SD Hamong Siswa

- ID perpustakaan: **1638**.
- Database: **SD HAMONG SISWA**, kode **PDT-45869**, Kecamatan **Pamotan**.
- Excel sekolah baru: **belum ditemukan padanan pasti**.
- Data tetap disimpan dan belum dinonaktifkan. Sekolah lain yang namanya mirip tidak dijadikan pengganti.

**Pertanyaan: Apakah sekolah ini masih aktif? Jika berubah nama atau tercantum dengan nama lain, mohon berikan nama dan NPSN yang benar.**

Jawaban Anda: …

## Kasus 7 — SMP Katholik Adisucipto Sale

- ID perpustakaan: **1602**.
- Database: **SMP KATHOLIK ADISUCIPTO SALE**, NPSN **20315662**, Kecamatan **Sale**.
- Excel sekolah baru: **belum ditemukan padanan pasti**.
- Data tetap disimpan dan belum dinonaktifkan.

**Pertanyaan: Apakah sekolah ini masih aktif? Jika berubah nama atau NPSN, mohon berikan data penggantinya.**

Jawaban Anda: …

## Keputusan umum setelah kasus sekolah selesai

### A. Sekolah yang belum memiliki padanan

Ada **871 baris** tanpa padanan pasti; sebagian mungkin terkait kandidat sekolah di atas. Belum diimpor otomatis.

**Pilih arah berikutnya:**
1. Masukkan semua sekolah yang benar-benar belum ada, sebagai unit perlu verifikasi.
2. Masukkan hanya sekolah yang sudah memiliki perpustakaan.
3. Verifikasi Excel dahulu, lalu tentukan daftar yang akan dimasukkan.

Saran: **pilihan 3** agar tidak membuat duplikat. Sumber tidak menyediakan desa, alamat, PIC, atau GPS; data tersebut tidak boleh ditebak. Angka pada kolom “Perpustakaan” belum otomatis membuktikan ketersediaan layanan perpustakaan.

Jawaban Anda: …

### B. Populasi IPLM

Snapshot periode masih **403**; master dalam cakupan pada penerapan task5 sebanyak **738**. Belum diganti diam-diam.

**Pertanyaan: Setelah penyandingan selesai, gunakan hitungan otomatis terbaru atau angka manual tertentu?**

Jawaban Anda: Otomatis / Manual …, alasan …

### C. Pendataan lama — pekerjaan terpisah

Masih ada **35 baris pendataan lama** yang ditahan. Daftarnya ada di [penyandingan pendataan](PERSANDINGAN_PENDATAAN_TASK5.xlsx). Tidak perlu digabung dengan jawaban tujuh sekolah di atas; daftar ini dapat dibahas tersendiri setelah keputusan sekolah.

## Cara menjawab singkat

Contoh format (bukan keputusan):
- Kasus 1: setuju / berbeda sekolah / koreksi …
- Kasus 2: sama / berbeda / koreksi …
- Kasus 3: Kecamatan …, Desa …
- Kasus 4: setuju / koreksi …
- Kasus 5: sama / berbeda …
- Kasus 6: masih aktif / berubah nama … / perlu dicek
- Kasus 7: masih aktif / berubah nama … / perlu dicek
- Keputusan A: pilihan …
- Keputusan B: …
