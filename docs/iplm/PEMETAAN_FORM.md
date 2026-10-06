# Pemetaan form IPLM kabupaten/kota

> Pembaruan: identitas 43 kolom tetap berlaku. Petunjuk bukti terkini mengikuti `PEMETAAN_BUKTI_DUKUNG.xlsx` / `pemetaan-bukti-dukung-task2.json`; aturan tahun, populasi, kualifikasi D2 dan kunjungan mengikuti [HASIL_TASK2.md](HASIL_TASK2.md).

Sumber: `Template_IPLM_2026.xlsx`, sheet **IPLM Kab-Kota**. Terdapat **43 kolom A–AQ**: 14 identitas/demografi/pengisi, 28 indikator, dan 1 tautan umum. Petunjuk indikator diambil dari sheet **Definisi**; petunjuk identitas adalah penjelasan operasional aplikasi, bukan kutipan definisi Excel. Bukti dukung mengikuti slide 2–3 `Format Bukti Dukung.pptx`.

## Peta isian

| Kolom | Isian | Kontrol | Sumber awal |
| --- | --- | --- | --- |
| A | Jenis Perpustakaan | Dropdown | libraries.library_type_id |
| B | Subjenis Perpustakaan | Dropdown | libraries.library_subtype_id |
| C | Jumlah Guru dan Tenaga Kependidikan | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| D | Jumlah Siswa | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| E | Jumlah Karyawan | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| F | Nama Institusi/Sekolah/OPD/TBM/Lainnya | Teks | libraries.institution_name; fallback nama |
| G | Nomor Pokok Sekolah Nasional (NPSN) | Teks | libraries.code khusus sekolah |
| H | Nomor Pokok Perpustakaan (NPP) | Teks | libraries.npp |
| I | Nama Perpustakaan | Teks | libraries.name |
| J | Alamat Institusi/Sekolah/OPD/TBM/Lainnya | Teks | libraries.address |
| K | Provinsi Asal | Dropdown | Default Jawa Tengah |
| L | Kabupaten/Kota Asal | Dropdown | Default Kab. Rembang |
| M | Nama Lengkap Pengisi Kuesioner | Teks | Nama akun aktif, dapat dikoreksi |
| N | Kontak Pengisi Kuesioner (Whatsapp Aktif) | Teks | Diisi pengelola; tidak diasumsikan nol |
| O | Jumlah Judul Koleksi Tercetak | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| P | Jumlah Eksemplar Koleksi Tercetak | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| Q | Jumlah Judul Koleksi Digital | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| R | Jumlah Eksemplar Koleksi Digital | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| S | Penambahan Jumlah Judul Koleksi Tercetak Dalam 1 Tahun Terakhir | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| T | Penambahan Jumlah Eksemplar Koleksi Tercetak Dalam 1 Tahun Terakhir | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| U | Penambahan Jumlah Judul Koleksi Digital Dalam 1 Tahun Terakhir | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| V | Penambahan Jumlah Eksemplar Koleksi Digital Dalam 1 Tahun Terakhir | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| W | Jumlah Anggaran Pengembangan Koleksi Tercetak dan Digital Dalam 1 Tahun Terakhir yang Berasal dari Dana Bos | Rupiah bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| X | Jumlah Anggaran Pengembangan Koleksi Tercetak dan Digital Dalam 1 Tahun Terakhir yang Berasal dari Dana Non Bos | Rupiah bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| Y | Jumlah Anggaran Pengembangan Koleksi Tercetak dan Digital Dalam 1 Tahun Terakhir | Rupiah bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| Z | Jumlah Tenaga Perpustakaan Memiliki Kualifikasi Pendidikan Ilmu Perpustakaan (Orang) | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AA | Jumlah Tenaga Perpustakaan Tidak Memiliki Kualifikasi Pendidikan Ilmu Perpustakaan (Orang) | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AB | Jumlah Tenaga Perpustakaan yang Mengikuti Kegiatan Pengembangan Keprofesian Berkelanjutan (PKB) di Bidang Perpustakaan Dalam 1 Tahun Terakhir (Orang) | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AC | Jumlah Anggaran Pengembangan Keprofesian (Diklat) Tenaga Perpustakaan Dalam 1 Tahun Terakhir | Rupiah bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AD | Jumlah Peserta Kegiatan Penguatan Budaya Baca dan Peningkatan Kecakapan Literasi Dalam 1 Tahun Terakhir (Orang) | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AE | Jumlah Pemustaka dari Satuan Pendidikan Atau Masyarakat yang Memanfaatkan Perpustakaan Secara Luring dan/atau Daring Dalam 1 Tahun Terakhir (Orang) | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AF | Jumlah Pemustaka yang Menggunakan Fasilitas Sarana TIK Di Perpustakaan Dalam 1 Tahun Terakhir (Orang) | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AG | Jumlah Judul Koleksi Tercetak yang Dimanfaatkan Dalam 1 Tahun Terakhir | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AH | Jumlah Eksemplar Koleksi Tercetak yang Dimanfaatkan Dalam 1 Tahun Terakhir | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AI | Jumlah Judul Koleksi Digital yang Dimanfaatkan Dalam 1 Tahun Terakhir | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AJ | Jumlah Eksemplar Koleksi Digital yang Dimanfaatkan Dalam 1 Tahun Terakhir | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AK | Jumlah Kegiatan Penguatan Budaya Baca dan Peningkatan Kecakapan Literasi Dalam 1 Tahun Terakhir | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AL | Jumlah Kolaborasi/Kerja Sama Perpustakaan dengan Pihak Eksternal Dalam Rangka Peningkatan Pelayanan dan Pengembangan Perpustakaan Dalam 1 Tahun Terakhir (Kegiatan Kerja Sama) | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AM | Jumlah Variasi Layanan yang Tersedia (Fisik Dan Digital) | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AN | Jumlah Kebijakan dan Prosedur Pelayanan Perpustakaan (Dokumen) | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AO | Jumlah Peraturan Daerah (Kebijakan) Tentang Perpustakaan | Bilangan bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AP | Jumlah Anggaran untuk Peningkatan Pelayanan dan Pengelolaan Perpustakaan Selama 1 Tahun Terakhir | Rupiah bulat ≥ 0 | Diisi pengelola; tidak diasumsikan nol |
| AQ | Tautan Bukti Dukung | Tautan HTTPS | Diisi pengelola; tidak diasumsikan nol |

## Definisi dan bukti per komponen

### A — Jenis Perpustakaan

Kelompok perpustakaan dari master aplikasi. Terisi dari /libraries; perubahan hanya diajukan pada form IPLM.

### B — Subjenis Perpustakaan

Subjenis sesuai kelompok yang dipilih. Pilihan mengikuti jenis induk. TK/SKB merupakan perluasan lokal, bukan otomatis responden IPLM kabupaten.

### C — Jumlah Guru dan Tenaga Kependidikan

Jumlah guru dan tenaga kependidikan pada satuan pendidikan. Isi untuk perpustakaan sekolah; kosong bila tidak berlaku.

### D — Jumlah Siswa

Jumlah siswa pada satuan pendidikan yang dilayani. Isi untuk perpustakaan sekolah; kosong bila tidak berlaku.

### E — Jumlah Karyawan

Jumlah karyawan pada institusi yang dilayani, bila berlaku. Jangan mengganti dengan jumlah kunjungan perpustakaan.

### F — Nama Institusi/Sekolah/OPD/TBM/Lainnya

Nama resmi sekolah, instansi, OPD, TBM atau lembaga penyelenggara perpustakaan. Boleh berbeda dari nama perpustakaan.

### G — Nomor Pokok Sekolah Nasional (NPSN)

Nomor Pokok Sekolah Nasional. Untuk sekolah otomatis dari kode /libraries. Simpan sebagai teks agar nol awal/kode alfanumerik tidak hilang; bukan NPP.

### H — Nomor Pokok Perpustakaan (NPP)

Nomor Pokok Perpustakaan sesuai nomor yang diterbitkan untuk perpustakaan tersebut. Bukan NPSN. Kosongkan jika belum memiliki/belum diketahui.

### I — Nama Perpustakaan

Nama perpustakaan yang melayani pengguna pada institusi tersebut. Terisi dari nama pada /libraries.

### J — Alamat Institusi/Sekolah/OPD/TBM/Lainnya

Alamat institusi/perpustakaan sesuai kondisi yang dilaporkan. Koreksi alamat tidak otomatis mengubah alamat induk.

### K — Provinsi Asal

Provinsi lokasi institusi. Default Jawa Tengah; perubahan ditandai untuk verifikasi.

### L — Kabupaten/Kota Asal

Kabupaten/kota lokasi institusi. Default Kab. Rembang; perubahan ditandai untuk verifikasi dan tidak otomatis mengubah kewenangan.

### M — Nama Lengkap Pengisi Kuesioner

Nama orang yang benar-benar mengisi dan bertanggung jawab atas data. Bukan otomatis nama kepala sekolah/PIC.

### N — Kontak Pengisi Kuesioner (Whatsapp Aktif)

Nomor WhatsApp aktif pengisi untuk konfirmasi oleh petugas. Aplikasi tidak mengirim pesan otomatis.

### O — Jumlah Judul Koleksi Tercetak

Jumlah keseluruhan judul bahan pustaka dalam bentuk fisik yang dimiliki oleh perpustakaan.

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### P — Jumlah Eksemplar Koleksi Tercetak

Jumlah keseluruhan eksemplar bahan pustaka dalam bentuk fisik yang dimiliki oleh perpustakaan.

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### Q — Jumlah Judul Koleksi Digital

Jumlah keseluruhan judul bahan pustaka dalam format digital yang dimiliki dan diperoleh perpustakaan secara resmi (jual beli, hibah, hadiah, atau tukar menukar) serta dapat diakses oleh pemustaka.

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### R — Jumlah Eksemplar Koleksi Digital

Jumlah keseluruhan eksemplar bahan pustaka dalam format digital yang dimiliki dan diperoleh perpustakaan secara resmi (jual beli, hibah, hadiah, atau tukar menukar) serta dapat diakses oleh pemustaka.

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### S — Penambahan Jumlah Judul Koleksi Tercetak Dalam 1 Tahun Terakhir

Jumlah judul koleksi dari bahan perpustakaan tercetak yang ditambahkan ke dalam koleksi perpustakaan dalam satu tahun terakhir

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### T — Penambahan Jumlah Eksemplar Koleksi Tercetak Dalam 1 Tahun Terakhir

Jumlah eksemplar koleksi dari bahan perpustakaan tercetak yang ditambahkan ke dalam koleksi perpustakaan dalam satu tahun terakhir

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### U — Penambahan Jumlah Judul Koleksi Digital Dalam 1 Tahun Terakhir

Jumlah judul koleksi dari bahan perpustakaan digital yang ditambahkan ke dalam koleksi perpustakaan dalam satu tahun terakhir.

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### V — Penambahan Jumlah Eksemplar Koleksi Digital Dalam 1 Tahun Terakhir

Jumlah eksemplar koleksi dari bahan perpustakaan digital yang ditambahkan ke dalam koleksi perpustakaan dalam satu tahun terakhir.

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### W — Jumlah Anggaran Pengembangan Koleksi Tercetak dan Digital Dalam 1 Tahun Terakhir yang Berasal dari Dana Bos

Jumlah anggaran yang berasal dari dana BOS untuk dialokasikan bagi koleksi perpustakaan dalam satu tahun terakhir

Bukti dukung: Belum dirinci pada slide bukti dukung; menunggu ketentuan admin kabupaten.

### X — Jumlah Anggaran Pengembangan Koleksi Tercetak dan Digital Dalam 1 Tahun Terakhir yang Berasal dari Dana Non Bos

Jumlah anggaran yang berasal dari dana non BOS untuk dialokasikan bagi koleksi perpustakaan dalam satu tahun terakhir

Bukti dukung: Belum dirinci pada slide bukti dukung; menunggu ketentuan admin kabupaten.

### Y — Jumlah Anggaran Pengembangan Koleksi Tercetak dan Digital Dalam 1 Tahun Terakhir

Jumlah anggaran yang dialokasikan oleh pemerintah daerah atau instansi terkait selama satu tahun anggaran untuk membeli, menambah, atau memperbarui koleksi bahan perpustakaan, baik berupa buku cetak maupun bahan digital (e-book, jurnal elektronik, dan sumber digital lainnya)

Bukti dukung: Belum dirinci pada slide bukti dukung; menunggu ketentuan admin kabupaten.

### Z — Jumlah Tenaga Perpustakaan Memiliki Kualifikasi Pendidikan Ilmu Perpustakaan (Orang)

Jumlah pustawakan dan tenaga teknis yang memiliki latar belakang pendidikan minimal Diploma (D2) di bidang Ilmu Perpustakaan.

Bukti dukung: Daftar identitas tenaga perpustakaan (Excel), scan/copy ijazah.

### AA — Jumlah Tenaga Perpustakaan Tidak Memiliki Kualifikasi Pendidikan Ilmu Perpustakaan (Orang)

Jumlah pustakawan dan tenaga teknis perpustakaan yang memiiliki surat keputusan resmi dari lembaga/yayasan yang tidak memiliki latar belakang pendidikan Ilmu Perpustakaan

Bukti dukung: Daftar identitas tenaga perpustakaan (Excel), scan/copy ijazah.

### AB — Jumlah Tenaga Perpustakaan yang Mengikuti Kegiatan Pengembangan Keprofesian Berkelanjutan (PKB) di Bidang Perpustakaan Dalam 1 Tahun Terakhir (Orang)

Jumlah pustakawan dan tenaga teknis yang mengikuti kegiatan pengembangan keprofesian berkelanjutan (PKB), seperti pelatihan, seminar, lokakarya, sertifikasi, atau kegiatan sejenis yang mendukung kinerja perpustakaan yang dibuktikan dengan sertifikat yang mencantumkan jam pelatihan dalam kurun waktu satu tahun terakhir.

Bukti dukung: Daftar peserta PKB (Excel), scan/foto sertifikat PKB.

### AC — Jumlah Anggaran Pengembangan Keprofesian (Diklat) Tenaga Perpustakaan Dalam 1 Tahun Terakhir

Jumlah anggaran yang dialokasikan oleh pemerintah daerah atau instansi terkait selama satu tahun anggaran untuk kegiatan pendidikan dan pelatihan, kursus, workshop, atau program peningkatan kompetensi lainnya bagi pustakawan dan tenaga teknis

Bukti dukung: Belum dirinci pada slide bukti dukung; menunggu ketentuan admin kabupaten.

### AD — Jumlah Peserta Kegiatan Penguatan Budaya Baca dan Peningkatan Kecakapan Literasi Dalam 1 Tahun Terakhir (Orang)

Jumlah peserta yang mengikuti kegiatan yang bertujuan meningkatkan minat baca dan keterampilan literasi yang diselenggarakan oleh Perpustakaan dalam satu tahun terakhir

Bukti dukung: Absensi, foto peserta saat kegiatan berlangsung.

### AE — Jumlah Pemustaka dari Satuan Pendidikan Atau Masyarakat yang Memanfaatkan Perpustakaan Secara Luring dan/atau Daring Dalam 1 Tahun Terakhir (Orang)

Jumlah pemustaka yang menggunakan layanan perpustakaan, baik luring maupun daring dalam satu tahun terakhir

Bukti dukung: Daftar pemustaka yang mengunjungi perpustakaan dalam format Excel.

### AF — Jumlah Pemustaka yang Menggunakan Fasilitas Sarana TIK Di Perpustakaan Dalam 1 Tahun Terakhir (Orang)

Jumlah pemustaka yang memanfaatkan fasilitas teknologi informasi dan komunikasi di Perpustakaan dalam satu tahun terakhir, misalnya komputer, internet, perangkat lunak edukatif, audio book, atau akses ke basis data digital

Bukti dukung: Daftar pemustaka yang menggunakan sarana TIK perpustakaan dalam format Excel.

### AG — Jumlah Judul Koleksi Tercetak yang Dimanfaatkan Dalam 1 Tahun Terakhir

Jumlah judul bahan pustaka cetak yang dipinjam, dibaca di tempat, atau silang pinjam koleksi yang digunakan oleh pemustaka dalam satu tahun terakhir.

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### AH — Jumlah Eksemplar Koleksi Tercetak yang Dimanfaatkan Dalam 1 Tahun Terakhir

Jumlah eksemplar bahan pustaka cetak yang dipinjam, dibaca di tempat, atau silang pinjam koleksi yang digunakan oleh pemustaka dalam satu tahun terakhir.

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### AI — Jumlah Judul Koleksi Digital yang Dimanfaatkan Dalam 1 Tahun Terakhir

Jumlah akses atau penggunaan  judul bahan perpustakaan dalam bentuk digital oleh pemustaka, misalnya unduhan, kunjungan, atau pencarian dalam basis data elektronik dalam satu tahun terakhir.

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### AJ — Jumlah Eksemplar Koleksi Digital yang Dimanfaatkan Dalam 1 Tahun Terakhir

Jumlah akses atau penggunaan eksemplar bahan perpustakaan dalam bentuk digital oleh pemustaka, misalnya unduhan, kunjungan, atau pencarian dalam basis data elektronik dalam satu tahun terakhir.

Bukti dukung: Daftar koleksi dalam format Excel/foto koleksi.

### AK — Jumlah Kegiatan Penguatan Budaya Baca dan Peningkatan Kecakapan Literasi Dalam 1 Tahun Terakhir

Jumlah program atau kegiatan yang diselenggarakan oleh perpustakaan untuk meningkatkan minat baca dan keterampilan literasi pengguna dalam satu tahun terakhir, misalnya lokakarya (workshop) literasi, program membaca, seminar edukasi, kegiatan penjangkauan pemustaka diluar perpustakaan, atau kegiatan literasi digital

Bukti dukung: Daftar kegiatan (Excel), foto kegiatan.

### AL — Jumlah Kolaborasi/Kerja Sama Perpustakaan dengan Pihak Eksternal Dalam Rangka Peningkatan Pelayanan dan Pengembangan Perpustakaan Dalam 1 Tahun Terakhir (Kegiatan Kerja Sama)

Jumlah kerja sama formal atau informal yang dilakukan perpustakaan dengan pihak eksternal, seperti institusi pendidikan, komunitas literasi, penerbit, atau perusahaan, dalam upaya meningkatkan layanan dan pengembangan perpustakaan dalam satu tahun terakhir

Bukti dukung: MoU, foto saat advokasi.

### AM — Jumlah Variasi Layanan yang Tersedia (Fisik Dan Digital)

Jumlah berbagai jenis layanan yang tersedia di perpustakaan, baik dalam bentuk fisik maupun digital

Bukti dukung: Daftar layanan (Excel), foto layanan.

### AN — Jumlah Kebijakan dan Prosedur Pelayanan Perpustakaan (Dokumen)

Jumlah dokumen kebijakan, pedoman, dan prosedur tertulis yang mengatur penyelenggaraan layanan perpustakaan, misalnya layanan peminjaman, referensi, ruang baca, pocadi, perpustakaan keliling, e-library, akses database online, katalog digital dan lain-lain

Bukti dukung: Daftar dokumen (Excel), foto dokumen.

### AO — Jumlah Peraturan Daerah (Kebijakan) Tentang Perpustakaan

Jumlah dokumen peraturan, kebijakan, pedoman, dan prosedur tertulis tentang perpustakaan yang ditandatangani pejabat daerah

Bukti dukung: Daftar dokumen (Excel), foto dokumen.

### AP — Jumlah Anggaran untuk Peningkatan Pelayanan dan Pengelolaan Perpustakaan Selama 1 Tahun Terakhir

Jumlah anggaran yang dialokasikan oleh pemerintah daerah atau instansi terkait dalam satu tahun anggaran untuk mendukung kegiatan pengelolaan, pemeliharaan, serta pengembangan layanan perpustakaan, termasuk sarana prasarana, teknologi informasi, dan program layanan kepada masyarakat

Bukti dukung: Belum dirinci pada slide bukti dukung; menunggu ketentuan admin kabupaten.

### AQ — Tautan Bukti Dukung

Tautan HTTPS ke folder/dokumen bukti dukung keseluruhan. Bukti per indikator dapat diisi pada kolom pendamping; satu folder yang sama boleh digunakan bila isinya jelas. Berikan izin baca kepada verifikator, bukan akses publik untuk data pribadi.

## Ketentuan pengisian dan kesenjangan data

- Jenis dan subjenis menggunakan master relasional, bukan ENUM database yang sulit diperluas. Subjenis disaring berdasarkan jenis; pasangan diperiksa lagi di server dan dilindungi foreign key pada `/libraries`.
- Pilihan provinsi/kabupaten berasal dari sheet Validasi Data. Kabupaten Rembang menjadi default, tetapi perubahan tetap boleh diajukan dan menghasilkan peringatan. Isian di luar Jawa Tengah/Kab. Rembang tidak dimasukkan analisis lokal Rembang.
- NPSN/NPP berupa teks; kode SKB alfanumerik dan nol awal dipertahankan. Nama pengisi tidak otomatis dianggap kepala sekolah/PIC.
- Draft boleh tidak lengkap. Tanda wajib berlaku saat dikirim/verifikasi. Jumlah guru, siswa, karyawan boleh kosong bila tidak relevan; NPP/NPSN yang tidak berlaku tidak dipaksa menjadi nol.
- Anggaran memakai rupiah penuh, bukan juta rupiah; jangan memasukkan pemisah ribuan. Tidak ada tarif atau perhitungan anggaran otomatis.
- Referensi judul cetak/eksemplar cetak/judul digital dari katalog **jejaring** perpustakaan sendiri dapat dipakai setelah pengelola menekan tombol konfirmasi. Ini kondisi saat ini, bukan otomatis kondisi akhir periode. Dataset kabupaten lama tidak dicampur tanpa dasar kepemilikan yang jelas.
- Eksemplar digital/lisensi, penambahan tahunan, pemanfaatan, dan pemustaka tidak diturunkan otomatis dari data yang belum memenuhi definisinya. Khususnya data kunjungan simulasi tidak disalin ke IPLM.
- Tautan per indikator mendampingi kolom AQ. Satu tautan folder boleh digunakan ulang jika bukti terorganisasi. Server tidak mengunduh tautan dan tidak mengubah akses Drive; keberadaan/isi/izin dokumen harus diperiksa petugas.
- Template Definisi menyebut D2 untuk tenaga, regulasi menyebut D3: UI menampilkan catatan konflik dan mengarahkan minimal D3 sementara menunggu klarifikasi. Lihat dokumen pertanyaan.
- Jenis bukti untuk anggaran belum dirinci pada PPT: tidak diciptakan persyaratan baru. Petugas dapat mengatur label, definisi, kewajiban bukti, dan pilihan untuk formulir berikutnya.
- Rekap Excel/CSV aplikasi memuat metadata/status dan tautan per indikator. Ini **rekap aplikasi**, bukan klaim berkas unggah resmi Perpusnas yang sudah tervalidasi formatnya.
