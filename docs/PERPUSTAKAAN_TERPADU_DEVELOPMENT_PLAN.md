# Rencana Pengembangan Perpustakaan Terpadu Kabupaten Rembang

Tanggal: 4 Oktober 2026.

Status: **operasional inti aktif dan 410 akun sekolah telah dibuat, 4 Oktober 2026**. Keputusan terbaru di bawah menggantikan usulan awal yang bertentangan. Catatan rilis, batas implementasi, aktivasi, dan pengujian: [LIBRARY_NETWORK_OPERATIONS.md](LIBRARY_NETWORK_OPERATIONS.md). Perubahan direktori GIS: [LIBRARIES_GIS.md](LIBRARIES_GIS.md).

## 1. Tujuan dan prinsip

Pustaka menjadi satu platform bersama untuk perpustakaan kabupaten, sekolah, desa, komunitas, swasta, dan mitra di Kabupaten Rembang. Setiap perpustakaan mengelola operasionalnya sendiri; pengelola kabupaten memperoleh laporan jaringan yang terpadu.

- Gunakan aplikasi dan database operasional Pustaka yang sekarang, dengan kepemilikan dan pembatasan data per perpustakaan.
- Direktori `libraries` menjadi identitas lembaga, bukan membuat daftar perpustakaan kedua.
- Satu perpustakaan dapat memiliki beberapa **Admin Perpustakaan yang setara**, masing-masing dengan akun pribadi dan jejak aktivitas. Tidak ada superadmin perpustakaan, petugas terbatas, atau pimpinan baca-saja sebagai tingkatan lokal.
- Data operasional bersifat lokal; data referensi dan informasi katalog publik dapat dibagikan berdasarkan kebijakan eksplisit.
- Jangan mengaktifkan seluruh akun sebelum pemisahan akses diuji.
- Layanan dan histori yang sudah berjalan, termasuk integrasi INLISLite, harus tetap terjaga.

## 2. Kondisi aplikasi yang sudah diperiksa

| Bagian | Kondisi awal | Pekerjaan yang diperlukan |
| --- | --- | --- |
| Akun | `auth_user.library_id` tersedia; beberapa akun dapat menunjuk lembaga yang sama | Penugasan akun–perpustakaan, status penugasan, peran lokal, pencabutan akses |
| Hak akses | Role dan permission tersedia | Bedakan kewenangan global dan lokal; ikuti registry/menu kanonis |
| Scope | `MY_Controller` menyediakan konteks perpustakaan | Akun lokal tanpa scope harus ditolak, bukan dianggap akses global |
| Profil lembaga | Sebagian query sudah menggunakan scope | Audit seluruh jalur baca/tulis, foto, verifikasi, dan ekspor |
| Katalog | Sebagian query dan eksemplar sudah mengenal perpustakaan | Pisahkan judul bersama, koleksi lokal, dan eksemplar; tetapkan hak edit |
| Peminjaman | Pengaturan masih memakai satu record global; pencarian belum konsisten dibatasi | Aturan, pencarian, transaksi, pengembalian, dan reservasi per perpustakaan |
| Anggota | Identitas dan pencarian masih berorientasi data bersama | Keanggotaan lokal dan pembatasan data pribadi |
| Laporan | Sebagian statistik/query masih global | Scope seluruh agregat, detail, unduhan, dan proses latar belakang |

Temuan ini merupakan pemeriksaan awal kode, bukan audit keamanan menyeluruh. Acuan kode: `application/core/MY_Controller.php`, `application/models/User_model.php`, `Library_model.php`, `Catalog_model.php`, `Loan_model.php`, dan controller terkait.

## 3. Model akses yang disepakati

| Peran konseptual | Kewenangan |
| --- | --- |
| Superadmin sistem | Konfigurasi, akses, pemeliharaan, dan tindakan sistem yang tercatat |
| Admin kabupaten | Aktivasi lembaga, pembinaan, laporan lintas lembaga, tindakan lintas scope sesuai izin |
| Admin Perpustakaan (`LIBRARY_ADMIN`) | Satu-satunya peran pengelola lokal; profil, katalog, anggota lokal, sirkulasi, kunjungan, laporan, serta menambah admin setara untuk lembaganya |
| Anggota | Layanan publik serta data dan transaksi miliknya sendiri |

Nama/kode role final harus diselaraskan dengan [RBAC_AND_SIDEBAR_STANDARD.md](RBAC_AND_SIDEBAR_STANDARD.md), bukan membentuk sistem permission paralel.

### Penugasan dan konteks aktif

- Akun lokal memakai `auth_user.library_id` langsung ke `libraries.id`, yaitu data yang dikelola melalui `/libraries`. Tidak membuat identitas lembaga duplikat.
- Satu akun hanya ditugaskan ke satu perpustakaan, satu perpustakaan boleh mempunyai banyak admin setara. Multi-penugasan per akun tidak termasuk implementasi awal.
- Server mengambil ulang status akun, role, status lembaga, dan penugasan dari database pada setiap permintaan. Akun lokal tidak memiliki pemilih scope lintas lembaga.
- Hak dari perpustakaan berbeda tidak boleh digabung sehingga menaikkan kewenangan lintas lembaga.
- Admin lokal tidak boleh memberikan role global, memindahkan dirinya ke lembaga lain, atau menaikkan izin melebihi kewenangannya.
- Penonaktifan akun/penugasan harus memutus akses sesi yang masih aktif, bukan menunggu login ulang.

## 4. Aktivasi perpustakaan

Terdaftar di direktori tidak otomatis berarti sudah mempunyai koleksi atau transaksi. Status akun dan status lembaga diperiksa terpisah; lembaga nonaktif tidak dapat dioperasikan oleh admin lokal.

Untuk aktivasi awal yang diminta pemilik aplikasi: backup → uji isolasi dan alur → buat satu akun pada masing-masing 410 sekolah SD/SMP/TK/SKB yang telah diimpor → distribusikan kredensial secara privat → wajib ganti password pertama → isi data operasional masing-masing.

Username berasal dari nama sekolah, huruf kecil dengan strip; benturan ditambah kecamatan atau NPSN. Password awal seragam sesuai permintaan, hanya untuk onboarding, disimpan hashed dan wajib diganti sebelum membuka operasional. Daftar kredensial tidak boleh dimasukkan ke `docs`, Git, atau direktori publik. Sekolah yang belum mempunyai perpustakaan tetap mendapat akun awal sesuai permintaan, tetapi tidak dibuatkan koleksi/anggota/transaksi fiktif.

## 5. Modul operasional

### 5.1 Profil dan petugas

Alamat, wilayah, GPS, radius, jam layanan, fasilitas, kontak, foto, dan petugas lokal. Verifikasi lembaga serta pemberian akses kabupaten tetap menjadi kewenangan pusat. Perubahan sensitif dicatat dalam audit.

### 5.2 Katalog, koleksi, dan inventaris

- **Judul/bibliografi:** judul, pengarang, penerbit, ISBN, klasifikasi. Dapat dipakai bersama, tetapi kurasi perubahan harus jelas.
- **Koleksi lokal:** relasi perpustakaan–judul, status tayang, catatan lokal, dan kebijakan layanan. Tetap dapat ada walaupun belum memiliki eksemplar.
- **Eksemplar fisik:** barcode, nomor inventaris, lokasi/rak, kondisi, sumber perolehan, dan status. Memiliki pemilik perpustakaan yang eksplisit.
- **Aset digital:** file, pemilik/pemberi akses, kebijakan akses, dan hak distribusi. Metadata publik tidak otomatis berarti file boleh dibagikan.

Admin lokal tidak boleh mengubah atau menghapus metadata bersama sehingga merusak katalog lembaga lain. Untuk metadata bersama, sediakan usulan koreksi atau kurator; data lokal dikelola pemiliknya.

Fitur awal: input/edit katalog lokal, impor Excel dengan pratinjau dan validasi, pengelolaan eksemplar, label barcode/QR, dan stok opname. Tentukan aturan keunikan barcode per lembaga; pencarian barcode selalu memakai scope perpustakaan aktif. Jangan menggabungkan buku hanya berdasarkan judul yang sama atau ISBN kosong.

### 5.3 Anggota

Keputusan awal: tiap perpustakaan mempunyai data anggota lokal sendiri (`network_members`, dipisahkan dengan `library_id` dalam database yang sama). Nomor anggota/NIS boleh sama antarperpustakaan, tetapi unik dalam satu perpustakaan. Anggota lokal tidak otomatis menjadi akun login `USER` kabupaten. Penautan identitas kabupaten lintas lembaga adalah pengembangan terpisah yang memerlukan persetujuan dan verifikasi.

- Admin lokal hanya melihat anggota yang berhubungan dengan lembaganya dan atribut yang diperlukan.
- Tidak otomatis dapat menelusuri semua NIK, kontak, atau riwayat peminjaman masyarakat Rembang.
- Penautan anggota lama ke lembaga baru membutuhkan alur verifikasi, bukan pencarian data pribadi tanpa pembatasan.
- Keanggotaan lokal diblokir tidak otomatis memblokir identitas di seluruh kabupaten; tindakan global memerlukan kewenangan berbeda.
- Kebijakan data anak/siswa, persetujuan, masa simpan, dan koreksi identitas perlu ditetapkan sebelum peluncuran sekolah.

### 5.4 Sirkulasi

Peminjaman, pengembalian, perpanjangan, reservasi, kehilangan/kerusakan, serta denda bila memang diberlakukan. Setiap perpustakaan menetapkan lama pinjam, batas aktif, aturan perpanjangan, dan jadwal layanan.

Transaksi dan detailnya harus memiliki scope yang konsisten dengan anggota lokal dan pemilik eksemplar. Validasi dan penguncian transaksi mencegah dua petugas meminjamkan eksemplar yang sama. Perubahan aturan tidak mengubah tanggal jatuh tempo transaksi lama secara diam-diam.

Tahap awal: pinjam/kembali di perpustakaan pemilik. Pinjam antarperpustakaan, perpindahan koleksi, dan pengembalian lintas lokasi ditunda sampai alur tanggung jawab aset disepakati.

### 5.5 Kunjungan

Buku tamu, QR/check-in, dan monitor lokal memiliki konteks perpustakaan. Tujuan kunjungan tetap terpisah dari kanal offline/online: layanan digital dapat digunakan saat berkunjung fisik.

Pencatatan aktivitas online harus mempunyai aturan atribusi yang jelas; jangan menghitung satu aktivitas sebagai kunjungan semua perpustakaan. Data uji/simulasi harus dapat dipisahkan dari laporan operasional/resmi.

### 5.6 Dashboard dan laporan

Dashboard lokal mencakup koleksi fisik/digital, eksemplar, anggota, sirkulasi, keterlambatan, dan kunjungan. Ekspor CSV/Excel menggunakan scope dan filter yang sama dengan tampilan.

Admin kabupaten memperoleh agregat berdasarkan perpustakaan, jenis, kecamatan, desa, dan periode. Bedakan jumlah judul unik kabupaten dari penjumlahan judul tiap perpustakaan; bedakan anggota unik dari jumlah keanggotaan lokal. Definisi indikator perlu dicatat agar rekap tidak menghitung ganda.

## 6. Arsitektur dan pengamanan

Rekomendasi awal: satu aplikasi/database dengan pemisahan baris berdasarkan `library_id`, ditambah relasi yang tervalidasi. Ini belum keputusan untuk memindahkan framework atau membuat microservices.

- Buat layanan konteks perpustakaan terpusat; hindari scope opsional yang tidak sengaja berarti seluruh data.
- Bedakan akses global eksplisit dengan scope lokal yang tidak tersedia. Akun lokal tanpa scope ditolak.
- Semua query baca/tulis, agregat, autocomplete, ekspor, file, cache, notifikasi, dan job harus memakai scope yang tervalidasi server.
- Parameter URL/form hanya pilihan objek; bukan bukti kepemilikan atau kewenangan.
- Transaksi tidak boleh menunjuk eksemplar atau relasi lokal lembaga lain; gunakan constraint/index yang sesuai serta validasi aplikasi.
- Audit mencatat aktor, perpustakaan, tindakan, objek, dan waktu; hindari menyalin kredensial atau data pribadi berlebihan ke log.
- Lindungi file privat melalui endpoint berizin; jangan mengandalkan penyembunyian URL.
- Terapkan pembatasan unggahan/impor dan proses berat supaya satu perpustakaan tidak mengganggu layanan lain.
- Backup dan prosedur pemulihan perlu diuji, termasuk risiko memulihkan satu lembaga dalam database bersama.

Acuan: [OWASP Multi-Tenant Application Security](https://cheatsheetseries.owasp.org/cheatsheets/Multi_Tenant_Security_Cheat_Sheet.html). Standar proyek tetap mengikuti `CODING_STANDARDS.md` dan registry RBAC yang sudah ada.

## 7. Tahapan implementasi dan kriteria selesai

| Tahap | Pekerjaan | Kriteria selesai |
| --- | --- | --- |
| 1. Audit/rancangan | Inventaris endpoint/tabel/job, klasifikasi global/lokal/publik, matriks izin, rancangan migrasi | Kepemilikan setiap data dan aturan akses terdokumentasi; data tanpa pemilik teridentifikasi |
| 2. Akses/isolasi | Penugasan multi-admin, konteks aktif, penolakan akses default, pencabutan sesi, audit | Akun A gagal mengakses data privat B melalui seluruh jalur uji |
| 3. Operasional inti | Katalog/koleksi/eksemplar, anggota lokal, aturan dan transaksi sirkulasi | Dua perpustakaan dapat bekerja independen; transaksi konkuren dan migrasi histori lolos |
| 4. Layanan/laporan | Kunjungan lokal, dashboard, ekspor, rekap kabupaten | Angka lokal/detail/ekspor sesuai; agregat tidak menghitung ganda |
| 5. Pilot/peluncuran | Pilih 3–5 perpustakaan siap, pelatihan, dokumentasi, evaluasi, aktivasi bertahap | Masalah kritis ditutup, pemulihan diuji, layanan lama tidak terganggu |

Tidak menetapkan estimasi waktu final sebelum tahap audit. Aktivasi luas bukan prasyarat untuk mencoba pilot.

## 8. Migrasi dan kompatibilitas

1. Buat backup, catat baseline jumlah/status/histori, dan uji pemulihan pada lingkungan terpisah.
2. Gunakan migrasi tambahan bertahap; hindari menghapus kolom atau menimpa histori pada langkah pertama.
3. Rekonsiliasi pemilik data lama. Jangan otomatis menganggap semua `library_id` kosong sebagai perpustakaan pusat.
4. Migrasikan penugasan akun dan koleksi tanpa menduplikasi pengguna atau mengubah ID transaksi lama.
5. Pastikan identitas sumber sinkron INLIS mencakup konteks sumber/lembaga bila kelak ada beberapa instalasi; sinkron tidak boleh menimpa data lokal lembaga lain.
6. Bandingkan jumlah, saldo eksemplar, pinjaman aktif, riwayat kembali, anggota, kunjungan, dan laporan sebelum/sesudah.
7. Uji pada lingkungan terpisah/pilot dan siapkan prosedur rollback sebelum aktivasi produksi.

## 9. Pengujian wajib

- Dua perpustakaan A/B dengan minimal dua admin setara di A, satu admin B, superadmin pusat, serta akun pemustaka biasa; tidak membuat tingkatan admin lokal tambahan.
- URL, POST, pencarian, ekspor, file, dan job lintas scope ditolak tanpa membocorkan data.
- Scope kosong, akun nonaktif, penugasan dicabut, serta perpustakaan ditangguhkan ditangani dengan benar.
- Admin lokal tidak dapat memberikan role global atau memindahkan kepemilikan secara ilegal.
- Barcode sama antarperpustakaan tidak menyebabkan salah eksemplar; transaksi bersamaan tidak menggandakan peminjaman.
- Katalog tanpa eksemplar, aset digital, dan perubahan metadata bersama mempunyai aturan yang teruji.
- Laporan lokal dan kabupaten sesuai definisi indikator; file unduhan tidak melewati scope.
- Data lama, integrasi INLIS, dan proses pemulihan tetap bekerja.
- Pengujian beban memakai ukuran data nyata dan skenario impor/laporan bersamaan; jangan mengklaim kapasitas hanya berdasarkan jumlah perpustakaan.

## 10. Keputusan sebelum implementasi inti

- Sudah disepakati: anggota lokal terpisah dan satu peran Admin Perpustakaan; satu lembaga dapat mempunyai beberapa admin setara.
- Admin lokal boleh menambah/menonaktifkan admin setara dalam lembaganya, tidak boleh menonaktifkan diri sendiri atau admin aktif terakhir. Hak global tidak dapat diberikan dari ruang lokal.
- Tentukan kurator metadata bersama, aturan berbagi aset digital, dan pemilik data lama yang belum jelas.
- Tetapkan perpustakaan pilot, aturan pinjam awal, kebutuhan denda, dukungan pengguna, dan penanggung jawab backup.
- Layanan pinjam lintas lembaga, integrasi sekolah otomatis, dan fitur lanjutan di luar MVP memerlukan keputusan terpisah.

## 11. Urutan kerja berikutnya

Implementasi awal memakai ruang operasional `/library-workspace` dengan tabel tambahan `network_*` dalam database yang sama. Modul lama tetap dipertahankan dan tidak diberikan kepada akun sekolah karena masih mempunyai query global. Perkembangan dan batas rilis dicatat di [LIBRARY_NETWORK_OPERATIONS.md](LIBRARY_NETWORK_OPERATIONS.md); seluruh roadmap di atas tidak otomatis dianggap selesai bersama rilis inti.
