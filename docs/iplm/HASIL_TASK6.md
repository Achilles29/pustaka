# Hasil task6 — 6 Oktober 2026

Permintaan: [task6.md](task6.md). **Sudah diterapkan pada aplikasi dan database yang digunakan sekarang**, setelah backup dan pengujian terpisah.

## 1. Aktivasi mandiri tanpa menunggu kabupaten

Halaman: `/aktivasi-perpustakaan`.

Alur pengelola:

1. Cari nama perpustakaan, nama sekolah, atau NPSN; bisa juga memilih titik di peta.
2. Pastikan unit yang dipilih benar.
3. Isi nama lengkap pengelola, telepon, password pribadi dan konfirmasinya, lalu centang pernyataan sebagai pengelola resmi.
4. Klik **Aktifkan akun & masuk**. Akun langsung aktif dan masuk ke dashboard perpustakaan sendiri, tanpa kode, persetujuan awal, atau tindakan kabupaten.
5. Simpan username yang ditampilkan pada pesan keberhasilan. Untuk login berikutnya gunakan username tersebut dan password yang baru dibuat.

Jika sudah ada akun awal yang belum pernah digunakan, akun dan username itu dipakai kembali; password awal diganti dengan password pribadi. Jika belum ada akun, dibuat satu akun **Admin Perpustakaan** dengan scope unit terpilih. Username baru berasal dari nama sekolah/institusi dan ID unit. Tidak ada tingkatan superadmin sekolah tambahan.

Password minimal 10 karakter dan maksimal 72 byte. Password tidak ditampilkan atau disimpan sebagai teks biasa. Tidak perlu mengganti password sekali lagi sesudah pendaftaran, karena pengelola sudah membuat password pribadinya di formulir aktivasi.

### Batas dan perlindungan

- Unit yang salah satu akunnya sudah pernah dipakai, sudah mengganti password awal, atau sudah diklaim tidak bisa diaktivasi ulang oleh publik.
- Perpustakaan pusat/kabupaten, perpustakaan nonaktif, akun awal yang ditangguhkan, dan akun dengan penugasan/peran tidak konsisten ditolak.
- Klaim disimpan satu kali per perpustakaan. Dua pendaftaran bersamaan hanya menghasilkan satu keberhasilan dan satu akun, bukan duplikasi.
- Pengguna tidak bisa memilih role pusat atau mengganti scope lewat parameter permintaan.
- Password awal akun yang dipakai diganti; bila ada beberapa akun bootstrap yang semuanya belum pernah dipakai, password awal akun lainnya juga dibatalkan agar password bersama tidak menjadi jalur masuk tambahan. Akun tambahan itu tetap tersimpan dan dapat dipulihkan oleh kabupaten bila diperlukan.
- Kode lama yang belum diklaim tidak lagi menjadi syarat; setelah aktivasi mandiri kode lama tidak dapat dipakai untuk mengambil alih akun.
- Wajib POST dan token CSRF. Pembatasan percobaan tetap berlaku: 20 pengiriman per IP per jam dan 40 per perpustakaan per jam. Alamat IP disimpan sebagai hash bucket, bukan alamat mentah.
- Nama/telepon pendaftar hanya tampil pada halaman moderasi kabupaten, tidak ditambahkan pada hasil pencarian atau peta publik.

**Batas kepercayaan yang penting:** sesuai pola yang Anda minta, identitas pengelola merupakan pernyataan pendaftar, bukan kepemilikan yang sudah diverifikasi. Nama sekolah/NPSN adalah informasi publik. Orang yang bukan pengelola masih bisa mencoba menjadi pendaftar pertama untuk unit yang belum pernah aktif. CSRF, batas percobaan, dan kunci klaim satu kali tidak membuktikan kepemilikan. Sengketa ditangani kabupaten melalui moderasi setelah pendaftaran. Tidak ada klaim bahwa alur ini setara verifikasi kode/identitas oleh petugas.

## 2. Peran kabupaten: moderasi dan pemulihan

Sidebar sekarang **Aktivasi & Moderasi**, alamat tetap `/library-activation`.

- Menampilkan status aktivasi, nama dan telepon pendaftar mandiri, waktu pendaftaran, username dan status akun.
- Tidak lagi meminta kabupaten menerbitkan kode. Form penerbitan kode dihapus dan endpoint penerbitan/pembatalan lama mengembalikan status 410 tanpa menerbitkan kode baru.
- Petugas dengan izin edit aktivasi **dan** edit pengguna dapat melakukan **Tangguhkan**, **Aktifkan kembali**, atau **Reset password**. Semua tindakan memerlukan alasan dan token CSRF.
- Tangguhkan membatalkan password dan sesi lama. Pemilik tidak dapat melewati penangguhan dengan mendaftar ulang.
- Aktifkan kembali tidak menghidupkan password lama yang mungkin sudah diketahui pihak salah. Setelah itu lakukan reset untuk pengelola yang benar.
- Reset membuat password sementara acak, ditampilkan sekali pada respons admin, dan wajib diganti pengelola setelah login. Password lama dan sesi lama tidak berlaku lagi. Password sementara tidak dimasukkan dalam log audit permanen.
- Periksa identitas pengelola di luar aplikasi sebelum memberikan password sementara. Data pendaftar pertama tetap disimpan sebagai jejak, bukan diubah menjadi identitas yang sudah diverifikasi.
- Akun dengan role pusat/campuran atau akun dari perpustakaan lain tidak dapat direset lewat manipulasi ID formulir ini.

Catatan: halaman moderasi menampilkan perpustakaan aktif. Jika unitnya sendiri dinonaktifkan, kabupaten memeriksa status master `/libraries` terlebih dahulu. Status master perpustakaan dan status akun merupakan dua hal berbeda.

## 3. Tab tepat di atas tabel `/libraries`

Di bawah peta/legenda dan persis di atas tabel sekarang ada dua baris:

1. **Jenis** pada baris atas.
2. **Subjenis** pada baris bawah, mengikuti jenis yang dipilih.

Tab, dropdown, legenda, tabel, dan peta memakai filter yang sama. Memilih jenis menghapus subjenis yang lama; memilih subjenis juga memilih jenis induknya. Tersedia Semua jenis dan Semua subjenis. Filter pencarian, kecamatan, status, dan jumlah baris yang sudah dipilih dipertahankan; halaman tabel kembali ke awal. Tab bisa digeser horizontal di ponsel dan tab aktif digeser agar terlihat.

Jumlah baris tabel tidak membatasi jumlah titik peta. Enam jenis dan 19 subjenis hasil perapian sebelumnya tetap dipakai; jenis duplikat tidak dibuat kembali.

## 4. Pertanyaan task5 dirapikan, belum dijawab otomatis

Dokumen **[PERTANYAAN_TASK5.md](PERTANYAAN_TASK5.md)** sekarang memisahkan:

1. SMK Yos Sudarso.
2. SMP Muhammadiyah Rembang.
3. SMK YPI Rembang.
4. SLB Negeri Rembang.
5. SD Islam An-Nawawiyyah.
6. SD Hamong Siswa.
7. SMP Katholik Adisucipto Sale.

Setiap sekolah memiliki ID, kondisi database, data pembanding, penjelasan masalah, satu pertanyaan, dan tempat jawaban sendiri. Hubungan konflik NPSN pada kasus 1 dan 2 dijelaskan tanpa mencampur kedua sekolah dalam satu baris pertanyaan.

Keputusan umum import sekolah dan populasi diletakkan terpisah. **Kasus sekolah, 871 baris tanpa padanan pasti, 35 baris pendataan lama, dan populasi belum ditindaklanjuti tanpa jawaban Anda.** Tidak ada perubahan identitas sekolah atau import massal dalam task6.

Tahun data tetap **2026**, rentang 1 Januari–31 Desember 2026. Tidak kembali ke 2025.

## 5. Pengujian dan keadaan produksi

- **36 pemeriksaan model/keamanan/concurrency** lulus: akun baru dan bootstrap, password/role/scope, penolakan unit pusat/nonaktif/akun terpakai, input tidak valid, kode lama, klaim ulang, moderasi/reset, akun campuran, dua klaim bersamaan, preservasi master, dan tidak adanya password pada audit permanen.
- **20 pemeriksaan HTTP** lulus: POST/CSRF, aktivasi dan login tanpa kabupaten, scope lokal, privasi pencarian, reset sekali tampil, pencabutan sesi lama, kewajiban ganti password sementara, suspensi/restore, dan tidak adanya kolom kode pada formulir publik.
- Tiga pemeriksaan tambahan memastikan endpoint penerbitan/pembatalan kode lama berstatus 410 dan tidak mengubah data kode.
- **27 pemeriksaan browser** lulus pada desktop 1440 px dan ponsel 390 px: posisi tab, sinkronisasi filter/legenda/peta, moderasi, formulir responsif, aktivasi langsung ke dashboard, username, dan penolakan akses pusat. Tidak ada error JavaScript.
- **39 pemeriksaan regresi peta** lulus: 680 titik valid, cakupan dan proyeksi publik tetap terjaga. PHP lint, sintaks JavaScript dan pemeriksaan whitespace lulus.
- Penerapan database **tidak membuat atau mengklaim akun produksi**. Pada verifikasi sesudah pemasangan: 0 baris pendaftaran mandiri, 832 perpustakaan; seluruh akun/password, master perpustakaan, isian IPLM, dan periode identik dengan sebelum pemasangan.
- Pemeriksaan tanpa login: halaman aktivasi produksi 200; halaman moderasi mengarahkan ke login. Pengujian pendaftaran/reset dilakukan pada akun dan database uji, bukan akun sekolah riil.

## 6. Penyimpanan, backup, dan pemulihan

Tambahan tabel `library_self_registrations`: library ID unik, user ID, nama/telepon pendaftar, dan waktu pendaftaran. Data ini tidak menimpa PIC atau status terverifikasi pada master perpustakaan.

Aktivasi dicatat pada `network_audit`; alasan moderasi dicatat pada `iplm_history`. Tidak ada password asli atau password sementara pada audit permanen.

Backup database tepat sebelum pemasangan:

`/www/backup/pustaka-network/before-network-20261006-110952-8d433c99.sql.gz`

Backup database dan kode awal task6:

`/www/backup/pustaka-network/before-network-20261006-105701-13f7e3b5.sql.gz`

`/www/backup/pustaka-network/before-network-20261006-105701-13f7e3b5-task6-code/`

SQL: `sql/2026-10-06a_library_self_registration.sql`. Pemasangan: `tools/setup_iplm_task6.php` (default analisis; `--apply` membackup sebelum perubahan dan aman dijalankan ulang).

Jika kelak perlu membatalkan mekanisme ini, jangan menghapus tabel klaim atau memulihkan database lama secara menyeluruh setelah ada pendaftaran/transaksi riil. Pertahankan jejak klaim dan akun yang sudah aktif; perbaikan akun dilakukan terarah oleh kabupaten.

Kedua database/folder uji task6 sudah dihapus setelah dibackup dan server uji dihentikan; tidak ada data produksi yang dihapus. Backup fixture: `/www/backup/pustaka-network/before-network-20261006-111310-41a26301.sql.gz` dan `/www/backup/pustaka-network/before-network-20261006-111319-f9337fc8.sql.gz`. Hasil pengujian akhir dan tangkapan layar tersimpan privat di `/www/backup/pustaka-network/before-network-20261006-111319-f9337fc8-task6-evidence/`. Manifest pembersihan: `/www/backup/pustaka-network/task6-test-cleanup-20261006.json`.
