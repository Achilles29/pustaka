- revisi /libraries ketika koordinat di tabel sekolah di klik maka tampilkan highlight icon nya di map 
- percantik modal Profil Perpustakaan
- seperti yang pernah saya jelaskan, intinya data master sekolah yang dijadikan acuan adalah yang ada di dapodik (yang pernah saya share dan sudah diinput di database, dan update nya untuk sekolah swasta dan selain SD SMP ada di sekolah_kabupaten_rembang.xlsx).jadi kalau data di pendataan tidak sesuai, maka acuan utama adalah di data dapodik. lalu cara data pendataan dengan instansi unit yang sesuai (untuk sekolah), baru lakukan migrasi data ke data instansi tersebut. kalau selain sekolah sudah tidak ada masalah sudah diinput di database kan?
jadi  lakukan pengecekan di dapodik baru yang belum ada di database, baru masukkan data dari pendataan yang sesuai

- setelah itu baru inventarisir data di pendataan yang belum ada di formulir kita, lalu buatkan formulir di dashboard perpustakaan lokal untuk mengisi data tersebut, tapi data tersebut bukan menjadi perhitungan IPLM. dan pastikan setiap perpustakaan wajib mengisi contact person dan nomor hp

- lalu cek di menu libraires admin, pastikan di profil atau lainnya menampilkan data lengkap inputan perpustakaan lokal

- di dashboard perpustakaan lokal tampilkan flare dan buat halaman panduan pengisian profil perpustakaan dan pendataan IPLM




## Kasus 1 — SMK Yos Sudarso

- ID perpustakaan: **1624**.
- Database: **SMK YOS SUDARSO**, NPSN **20315667**.
- Excel baris **857**: **SMK KATOLIK YOS SUDARSO REMBANG**, NPSN **20315657**.
- Masalah: NPSN database ternyata digunakan sekolah lain dalam Excel.
- Usulan: pertahankan ID perpustakaan dan seluruh relasinya; ubah nama institusi dan NPSN sesuai Excel. Nama perpustakaan tidak perlu otomatis diubah.


**Pertanyaan: Apakah ID 1624 benar sekolah yang ada pada baris 857, sehingga NPSN-nya boleh diganti menjadi 20315657?**

Jawaban Anda: …
===> gunakan excel dapodik sesuai instruksi saya.


## Kasus 2 — SMP Muhammadiyah Rembang

- ID perpustakaan: **1598**.
- Database: nama perpustakaan **SMP MUHAMMADIYAH 1 REMBANG**, nama institusi **SMP MUHAMMADIYAH REMBANG 1**, kode **PDT-54792**.
- Excel baris **867**: **SMP MUHAMMADIYAH REMBANG**, NPSN **20315667**.
- Masalah: nama database memiliki nomor **1**, sedangkan Excel tidak. NPSN Excel juga masih dipakai oleh ID 1624 pada kasus 1.
- Usulan jika benar satu sekolah: pakai nama institusi dan NPSN Excel, tanpa membuat perpustakaan baru. Koreksi dilakukan setelah konflik NPSN pada kasus 1 selesai.

**Pertanyaan: Apakah sekolah ID 1598 sama dengan SMP Muhammadiyah Rembang pada baris 867, tanpa nomor 1?**

Jawaban Anda: => sesuai excel dapodik

## Kasus 3 — SMK YPI Rembang

- ID perpustakaan: **1644**, nama perpustakaan **Graha Pustaka**, NPSN **20315658**.
- Database: Kecamatan **Sulang**, Desa **Kemadu**.
- Excel baris **858**: Kecamatan **Rembang**; desa dan alamat tidak dicantumkan.
- Masalah: kecamatan berbeda. Mengganti kecamatan saja akan membuat desa lama tidak konsisten.
- Data nama sekolah dan NPSN sudah cocok.

**Pertanyaan: Untuk SMK YPI Rembang, kecamatan dan desa yang benar apa? Jika tersedia, sertakan alamat atau tautan lokasi.**

Jawaban Anda: Kecamatan …; Desa …; Alamat/lokasi …

=> kecamatan di excel salah, di link portal dibawah itu koordinatnya, tapi almamat sesuai database

## Kasus 4 — SLB Negeri Rembang

- ID perpustakaan: **1632**.
- Database: kode **PDT-49252**, subjenis **SDLB**.
- Excel baris **852**: **SLB NEGERI REMBANG**, NPSN **20315824**, jenjang **SLB**.
- Usulan: gunakan NPSN **20315824**, buat subjenis **SLB**, lalu pindahkan ID 1632 dari SDLB ke SLB. ID dan relasi perpustakaan tetap sama. Tidak otomatis masuk cakupan perhitungan IPLM kabupaten.


**Pertanyaan: Apakah Anda setuju dengan koreksi NPSN dan subjenis untuk ID 1632 tersebut?**

Jawaban Anda: setuju

## Kasus 5 — SD Islam An-Nawawiyyah

- ID perpustakaan: **1586**, nama perpustakaan **Baitul Ulum**.
- Database: nama institusi **SD ISLAM AN-NAWAWIYYAH TASIKAGUNG REMBANG**, kode **PDT-61611**.
- Excel baris **829**: **SD ISLAM AN - NAWAWIYYAH**, NPSN **20315837**, Kecamatan **Rembang**.
- Masalah: database menyebut Tasikagung, Excel tidak. Masih berupa kandidat pasangan, belum dipastikan satu sekolah.
- Usulan jika cocok: isi NPSN dari Excel dan selaraskan nama institusi; nama perpustakaan Baitul Ulum tetap dipertahankan.

**Pertanyaan: Apakah ID 1586 merupakan sekolah yang sama dengan baris 829?**

Jawaban Anda:Gunakan excel dapodik

## Kasus 6 — SD Hamong Siswa

- ID perpustakaan: **1638**.
- Database: **SD HAMONG SISWA**, kode **PDT-45869**, Kecamatan **Pamotan**.
- Excel sekolah baru: **belum ditemukan padanan pasti**.
- Data tetap disimpan dan belum dinonaktifkan. Sekolah lain yang namanya mirip tidak dijadikan pengganti.

**Pertanyaan: Apakah sekolah ini masih aktif? Jika berubah nama atau tercantum dengan nama lain, mohon berikan nama dan NPSN yang benar.**

Jawaban Anda: Pertahankan

## Kasus 7 — SMP Katholik Adisucipto Sale

- ID perpustakaan: **1602**.
- Database: **SMP KATHOLIK ADISUCIPTO SALE**, NPSN **20315662**, Kecamatan **Sale**.
- Excel sekolah baru: **belum ditemukan padanan pasti**.
- Data tetap disimpan dan belum dinonaktifkan.

**Pertanyaan: Apakah sekolah ini masih aktif? Jika berubah nama atau NPSN, mohon berikan data penggantinya.**

Jawaban Anda:Pertahanan

## Keputusan umum setelah kasus sekolah selesai

### A. Sekolah yang belum memiliki padanan

Ada **871 baris** tanpa padanan pasti; sebagian mungkin terkait kandidat sekolah di atas. Belum diimpor otomatis.

**Pilih arah berikutnya:**
1. Masukkan semua sekolah yang benar-benar belum ada, sebagai unit perlu verifikasi, pastikan tidak ada data duplikat dengan scraping portal data

### B. Populasi IPLM

Snapshot periode masih **403**; master dalam cakupan pada penerapan task5 sebanyak **738**. Belum diganti diam-diam.
=> itu semua bukan populasi, nanti akan kita filter mana yang masuk populasi, karena ada sekolah yang tidak perpustakaan, ada juga perpustakaan yang bukan kewenangan

**Pertanyaan: Setelah penyandingan selesai, gunakan hitungan otomatis terbaru atau angka manual tertentu?**

Jawaban Anda: bisa otomatis bisa manual

### C. Pendataan lama — pekerjaan terpisah

Masih ada **35 baris pendataan lama** yang ditahan. Daftarnya ada di [penyandingan pendataan](PERSANDINGAN_PENDATAAN_TASK5.xlsx). Tidak perlu digabung dengan jawaban tujuh sekolah di atas; daftar ini dapat dibahas tersendiri setelah keputusan sekolah.




jika butuh konfirmasi data lebih lengkap lakukan scraping di https://dapo.kemendikdasmen.go.id/progres/030000/031700. misal untuk kasus Yos sudarso jika karena di excel tidak ada alamat, kamu bisa cari alamatnya disana untuk mencocokan dengan alamat disana. termasuk titik koordinat untuk semua sekolah yang belum ada tolong carikan disana

- tambahan data Rekap_new.xlsx untuk kolom lebih lengkap ada alamat dan koordinat, tapi jumlah baris lebih sedikit dari sebelumnya, gunakan untuk data backup jika kesulitan mencari alamat siapa tau disana ada

kedepan pertanyaan dan hasil dibuat seperti itu lebih enak dibaca