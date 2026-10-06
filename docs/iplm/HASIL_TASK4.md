# Hasil task4 — 5 Oktober 2026

Permintaan: [task4.md](task4.md). **Sudah diterapkan ke aplikasi dan database produksi**, sesudah backup dan pengujian terisolasi.

## Ringkasan

- Aktivasi mandiri dengan **kode unik sekali pakai**, dilanjutkan login terbatas dan pembuatan password pribadi.
- Sidebar admin kabupaten: **Pendataan IPLM → Aktivasi Perpustakaan** (`/library-activation`), berisi tautan ke halaman publik `/aktivasi-perpustakaan` dan pengelolaan kode.
- **421 perpustakaan tambahan diimport**; master bertambah dari **411 menjadi 832**.
- **378 baris pendataan dipadankan** dengan perpustakaan lama, tanpa menimpa identitas/NPSN/GPS master.
- **35 baris sekolah ditahan**, bukan dibuat duplikat, karena nomor/nama/NPSN belum konsisten.
- Seluruh **834 baris sumber diarsipkan** beserta keputusan dan relasi master. File sumber asli tidak diubah, statistik terupdate tidak dimasukkan sebagai angka IPLM 2025.

## 1. Cara aktivasi

### Admin kabupaten

1. Buka `/library-activation` melalui sidebar. Jika hak menu baru belum terlihat pada sesi admin lama, login ulang atau buka halaman IPLM agar hak akses diperbarui.
2. Cari perpustakaan menurut nama/sekolah/NPSN. Klik **Buat kode** setelah memastikan identitas pengelola resminya.
3. **Salin kode yang ditampilkan sekali** dan bagikan secara pribadi kepada pengelola perpustakaan tersebut. Jangan kirim ke grup umum atau memublikasikan Excel berisi kode.
4. Kode berlaku **7 hari**. **Terbitkan ulang** membatalkan kode sebelumnya; **Batalkan kode** menghentikan pemakaiannya.

Kode tidak dibuat massal atau ditulis di dokumen ini. Tidak ada password akun lama yang diganti selama deployment. Perpustakaan baru yang belum memiliki akun akan mendapat **satu akun Admin Perpustakaan dengan scope unit tersebut saat admin menerbitkan kode**. Username ditampilkan kepada admin; password sementara acak tidak dibagikan. Akun lama mempertahankan username-nya.

### Pengelola perpustakaan

1. Buka `/aktivasi-perpustakaan`, cari melalui peta/GPS atau kolom pencarian nama/sekolah/NPSN.
2. Pilih unit yang benar, baca identitas pada modal, masukkan kode dari kabupaten, dan centang pernyataan sebagai pengelola resmi.
3. Klik **Aktivasi & buat password**. Kode yang benar membuat sesi login khusus perpustakaan tersebut.
4. Langsung buat password pribadi **10–72 karakter** dan ulangi konfirmasinya. Pada aktivasi pertama ini **tidak perlu password lama**, karena kode sudah diverifikasi.
5. Setelah password tersimpan, dashboard dan operasional perpustakaan dapat digunakan. Catat username yang terlihat di halaman ganti password untuk login berikutnya.

Sesi pembuatan password berlaku **15 menit**. Bila terputus/kedaluwarsa sebelum selesai, hubungi kabupaten untuk pemulihan akun; jangan mengklaim ulang.

### Perlindungan akun

- Perpustakaan pusat tidak dapat diaktivasi dari jalur publik.
- Jika **salah satu akun pada unit tersebut pernah login**, atau password awal sudah diganti, atau kode sudah diklaim, **klaim/terbit ulang ditolak**. Aturan ini berlaku juga untuk akun lama sebelum task4.
- Kode terikat perpustakaan dan akun, tersimpan sebagai hash, kedaluwarsa, sekali pakai, tidak ada di API/peta/audit publik.
- Penerbitan kode menutup jalur password awal untuk unit yang sedang menunggu aktivasi. Setelah kode diklaim, password lama diganti nilai acak internal; pengguna kemudian membuat password pribadinya.
- Dua klaim bersamaan diserialisasi di database: hanya satu yang berhasil. Login biasa juga diperiksa dengan penguncian baris agar tidak berlomba dengan proses klaim.
- CSRF diwajibkan, percobaan publik dibatasi per IP dan perpustakaan; mengganti cookie tidak menghilangkan pembatasan.
- Sebelum mengganti password, akun hanya dapat membuka halaman pengaturan password. Izin penyiapan password tanpa password lama habis setelah dipakai, tidak berlaku untuk penggantian berikutnya, dan batal bila password/penugasan berubah.
- Admin sekolah/member tidak dapat menerbitkan kode. Hak dan status admin kabupaten dibaca ulang, termasuk pencabutan peran saat sesi masih berjalan.

Jika salah pengelola sudah mengaktifkan akun, **kabupaten melakukan pemulihan melalui pengelolaan akun yang sudah ada**, setelah memeriksa pemiliknya. Fitur ini sengaja tidak memiliki tombol klaim ulang bagi publik maupun penerbitan ulang kode untuk akun yang sudah dipakai.

## 2. Pemetaan dan import

Berkas utama: **[PERSANDINGAN_TASK4.xlsx](PERSANDINGAN_TASK4.xlsx)**. Database di **A–I (kiri)**, sumber pendataan **J–R (kanan)**, keputusan/keterangan **S–X**. Tampilan awal diringkas; tampilkan kolom tersembunyi (*Unhide*) untuk melihat lembaga induk, NPP, desa dan jenis/subjenis kedua sisi.

- Hijau: pasangan lama; ungu: unit yang baru diimport; kuning: ditahan/verifikasi; biru: hanya database, kemungkinan belum didata; merah: NPSN sumber berbeda dengan database.
- Sisi database untuk baris ditahan dapat menampilkan kandidat, **bukan penetapan pasangan**. Relasi arsipnya tetap kosong sampai disahkan.
- Sesudah import, unit baru sudah memiliki ID master pada sisi kiri. Data sumber tetap terlihat pada sisi kanan.
- Ada **32 baris HANYA_DATABASE** pada workbook. Ini berbeda dari **39 master lama tanpa pasangan pasti** karena tujuh di antaranya sudah ditampilkan sebagai kandidat pada baris yang ditahan. Sebanyak 378 baris sumber dipadankan ke 372 master lama (beberapa sumber menunjuk unit sama). Seluruh 832 ID master tetap terwakili.

Aturan yang diterapkan:

1. **Perpusda hanya satu**: ID master **2**, sumber **50964**, NPP sumber `3317103E1000003`. “Nomor 1” pada daftar adalah nomor urut, bukan alasan mengganti ID database. Tidak dibuat Perpusda kedua dan NPP master lama tidak ditimpa.
2. Nama sekolah yang dapat diidentifikasi sama memakai NPSN database/Dapodik. Variasi kapital, spasi, penulisan SDN, angka Romawi, posisi nomor, dan tambahan nama kecamatan dinormalisasi. Koreksi satu karakter hanya dipadankan bila NPSN sama, nomor sekolah sama dan wilayah tidak bertentangan. Nomor berbeda tidak digabung otomatis.
3. Nama lembaga induk dipakai untuk mengenali sekolah: **Maktabah → Sekolah / SMP / swasta**. Status negeri/swasta adalah kolom `institution_status`, bukan jenis/subjenis duplikat. Kolomnya dapat diedit di `/libraries` oleh admin kabupaten.
4. **Taman Bacaan Masyarakat Aji Gineng → Umum / TBM**. **Al-Anwar 2 Putri → Swasta / Pondok Pesantren** sesuai instruksi, meskipun subjenis sumber semula Rumah Ibadah. Klasifikasi sumber asli tetap tersimpan.
5. Subjenis desa/kelurahan tetap **Umum / Desa-Kelurahan**, tidak dipaksa menjadi sekolah. Jenis di luar kewenangan kabupaten tetap masuk registrasi, tetapi tidak otomatis menjadi peserta IPLM kabupaten.
6. SD/SMP negeri yang tidak ada padanan jelas ditahan dengan catatan kemungkinan salah nomor/nama/merger/belum terdata. Master yang tidak ada di pendataan tetap dipertahankan dan ditandai mungkin belum didata.
7. Referensi kecamatan dibatasi ke **kode Kabupaten Rembang 3317**. Nama kecamatan yang sama di kabupaten lain tidak dipakai. Desa dicocokkan hanya dalam kecamatan tersebut, dengan normalisasi spasi.

### Rincian 421 unit tambahan

| Subjenis | Jumlah | Masuk cakupan IPLM kabupaten |
| --- | ---: | --- |
| Desa/Kelurahan | 278 | Ya |
| TBM | 22 | Ya |
| SD swasta | 15 | Ya |
| SMP swasta | 20 | Ya |
| SMA | 11 | Tidak |
| SMK | 15 | Tidak |
| MTs | 38 | Tidak |
| MA | 5 | Tidak |
| SDLB | 1 | Tidak |
| Sekolah Tinggi | 4 | Tidak |
| Pondok Pesantren | 3 | Tidak |
| Rumah Ibadah | 4 | Tidak |
| Lapas | 1 | Tidak |
| Kementerian/Lembaga | 4 | Tidak |

Ditambahkan satu jenis **Perpustakaan Perguruan Tinggi** dan sepuluh subjenis yang belum ada. Total master sekarang **9 jenis / 19 subjenis**; jenis lama tidak dihapus. Ikon perguruan tinggi pada peta memakai simbol pendidikan dengan warna tersendiri.

Unit tambahan aktif tetapi **belum ditandai sudah diverifikasi**. Radius awal 50 meter. Nama, institusi, alamat, NPP, jadwal yang tersedia, jenis/subjenis dan koordinat yang layak diambil dari sumber. NPSN yang tidak tersedia tidak dikarang: kode internal `PDT-ID` dipakai, dan form IPLM tidak menganggap kode internal itu sebagai NPSN. Kontak/PIC sumber disimpan pada arsip privat, tidak otomatis dibuka ke peta publik.

**152 unit baru memerlukan verifikasi GPS**: kosong/tidak wajar/berulang massal. Disimpan sebagai 0,0 sehingga tidak menjadi titik lokasi palsu; sumber koordinat asli tetap di arsip. **19 unit belum cocok ID desanya**, termasuk satu yang juga belum memiliki ID kecamatan. Teks wilayah tidak dibuang. Rincian pada [PERLU_VERIFIKASI_TASK4.md](PERLU_VERIFIKASI_TASK4.md).

## 3. Penyimpanan dan populasi IPLM

- `libraries` tetap master tunggal per unit.
- `library_source_records` adalah **arsip/provenans pendataan**, dengan ID sumber unik, relasi master jika sudah jelas, baris sumber, keputusan, catatan, hash sumber dan JSON asli. Ini bukan master kedua yang diedit terpisah. Baris ganda boleh menunjuk master sama tanpa membuat perpustakaan ganda.
- Identitas/NPSN/GPS master lama, akun lama dan seluruh isian IPLM dipertahankan. Metadata status negeri/swasta ditambahkan tanpa mengubah data identitasnya. Source ID impor sekolah yang lama tidak ditimpa ID pendataan.
- **Populasi master dalam cakupan sekarang 738** (403 lama + 335 tambahan dalam cakupan). Periode 2026 yang sudah tersimpan tetap **403, mode otomatis, status draft**, karena snapshot periode tidak diubah diam-diam. Untuk memakai hitungan baru, admin membuka `/iplm/settings`, memilih periode dan **menyimpan mode otomatis**. Mode manual juga tetap tersedia.
- Angka koleksi, tenaga, anggaran, siswa dan kunjungan dari pendataan **tidak dimasukkan ke IPLM 2025** karena hanya diketahui sebagai data terupdate, bukan angka dengan periode yang pasti.
- Import bersifat idempoten: pengulangan terhadap sumber yang sama tidak menambah unit/arsip lagi. Jika hash sumber berubah, skrip menolak penerapan ulang otomatis.

Ringkasan mesin: [ringkasan-task4.json](ringkasan-task4.json). Migrasi: `sql/2026-10-05f_iplm_task4.sql`; importer: `tools/setup_iplm_task4.php`; aturan pemetaan: `tools/iplm_task4_support.php`.

## 4. Verifikasi dan backup

Pengujian dilakukan di database terisolasi, tanpa membuat akun/klaim uji pada produksi:

- **29** pemeriksaan task4: pemetaan, lingkup wilayah Rembang, subtype/IPLM, perlindungan akun, kode salah/kedaluwarsa/dibatalkan/replay, akun nonaktif/peran campuran dan audit tanpa kode mentah.
- **27** pemeriksaan HTTP task4, termasuk penerbitan melalui admin, claim publik, ganti password, login baru, CSRF dan batas akses.
- **1** pengujian dua klaim bersamaan: tepat satu berhasil.
- **2** pengujian keamanan tambahan: pencabutan superadmin pada sesi aktif dan rate limit lintas cookie baru.
- **15** pemeriksaan browser task4 pada desktop 1440 px dan ponsel 390 px: dari penerbitan kode hingga masuk dashboard setelah mengganti password; tanpa error JavaScript.
- Regresi: **42** model/import operasional, **70** HTTP operasional, **36** model IPLM, **37** HTTP IPLM, serta **16** halaman/ukuran browser IPLM/master.
- Import bersih dan penerapan ulang lolos. Guard transaksi memeriksa master lama, akun dan isian sebelum commit. Syntax PHP/JavaScript terkait lolos; form kode publik pada HTTPS produksi telah dicek tampil tanpa error PHP.

Backup produksi sebelum import:

`/www/backup/pustaka-network/before-network-20261005-160043-7036edd5.sql.gz`

Backup awal task4 dan salinan berkas autentikasi terkait:

`/www/backup/pustaka-network/before-network-20261005-154035-b6c4733d.sql.gz` dan direktori `-code` pada awalan yang sama.

Pemulihan database penuh tidak boleh langsung dilakukan setelah aplikasi menerima transaksi baru; gunakan backup dan arsip sumber untuk menyusun pemulihan selektif agar transaksi setelah deployment tidak tertimpa.

Ketiga database/runtime uji task4 sudah dibersihkan setelah dibackup; tidak ada database produksi yang dihapus. Manifest pemulihan uji: `/www/backup/pustaka-network/task4-cleanup-20261005.json`. Bukti pengujian terakhir, termasuk screenshot tanpa kode terlihat, disimpan di `/www/backup/pustaka-network/before-network-20261005-160633-3af066f6-evidence`.

## 5. Yang perlu diperiksa pengguna

Tidak ada keputusan aktivasi yang masih menggantung. Yang tersisa hanya verifikasi data: **35 baris sekolah yang ditahan**, GPS unit baru, dan wilayah yang belum cocok. Lihat [PERTANYAAN_TASK4.md](PERTANYAAN_TASK4.md). Semua unit yang aman dipetakan dan ditambahkan sudah masuk produksi.
