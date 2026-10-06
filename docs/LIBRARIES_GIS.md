# Perpustakaan GIS

Tanggal: 2026-07-29

## Tujuan

Modul Perpustakaan GIS menjadi direktori seluruh perpustakaan terdaftar di Kabupaten Rembang. Data ini akan menjadi dasar untuk peta layanan, pembatasan akses berbasis lokasi, statistik jejaring perpustakaan, dan integrasi pojok baca digital.

## Database

Migrasi:

- `sql/2026-07-29a_libraries_gis_schema.sql`
- `sql/2026-07-29d_phase1_admin_gis_clean.sql`

Tabel:

- `library_types`: jenis perpustakaan dan warna marker peta.
- `libraries`: profil perpustakaan, alamat, kontak, koordinat, radius layanan, dan status.
- `library_photos`: foto perpustakaan, caption, cover, dan uploader.
- `ref_districts`: master kecamatan Rembang dengan `province_code = 33`, `regency_code = 17`, `code = 01..14`, dan `full_code = 33.17.xx`.
- `ref_villages`: master Desa / Kelurahan Rembang dengan kode wilayah lengkap, `province_code = 33`, `regency_code = 17`, `district_code = xx`, dan `area_type`.

Jenis awal:

- `perpusda`
- `sekolah`
- `desa`
- `swasta`
- `komunitas`
- `mitra`

## Fitur Implementasi Awal

- Daftar perpustakaan dengan filter pencarian, jenis, dan status.
- Peta Leaflet/OpenStreetMap untuk semua titik perpustakaan.
- Radius layanan ditampilkan sebagai lingkaran di peta.
- CRUD dasar: tambah, edit, aktif/nonaktif.
- Form koordinat dengan peta picker: klik peta atau drag marker untuk mengisi latitude/longitude.
- Upload foto perpustakaan ke `assets/uploads/libraries`.
- Set foto utama/cover.
- Hapus foto galeri secara soft-delete.
- Dropdown kecamatan dan Desa / Kelurahan dari master wilayah.
- UI CRUD Master Wilayah di `/regions`.
- Verifikasi data perpustakaan.
- Scope admin lokal memakai `auth_user.library_id`.
- Akses modul memakai permission `libraries.index`.

## Catatan

- Leaflet saat ini dipasang dari CDN versi `1.9.4`.
- Data sample tidak ditinggalkan setelah smoke test.
- Foto masih public upload untuk fase admin profile; nanti aset sensitif/digital book tetap harus masuk storage non-public.
- Master wilayah memakai sumber OpenData resmi Rembang/Jateng, dataset [Nama Desa di Kabupaten Rembang](https://data.rembangkab.go.id/id/dataset/nama-desa-dan-kode-kec-di-kabupaten-rembang-2023).
- Kodifikasi wilayah mengikuti ketentuan: Provinsi Jawa Tengah `33`, Kabupaten Rembang `17`, Kecamatan memakai dua digit terakhir, misalnya `33.17.01` Sumber.
- Label wilayah bawah memakai `Desa / Kelurahan` karena Kabupaten Rembang memiliki desa dan kelurahan.
- Kolom `area_type` disiapkan untuk membedakan `desa` dan `kelurahan`; penandaan 7 kelurahan dilakukan admin saat data master wilayah dikelola.
- Field teks `district` dan `village` tetap ada sebagai denormalisasi/kompatibilitas, tetapi input utama memakai `district_id` dan `village_id`.

## Lanjutan

### Pembaruan direktori 4 Oktober 2026

- Pilihan 10/25/50/100 baris berada di toolbar tabel dan langsung menerapkan pilihan; perubahan memulai halaman pertama serta mempertahankan filter formulir.
- Peta mengambil seluruh lokasi yang diizinkan scope akun melalui query tersendiri, tidak mengikuti pencarian, jenis, kecamatan, status, atau pagination tabel.
- Angka total hasil menunjukkan hasil filter tabel; jumlah titik GPS menunjukkan seluruh lokasi valid dalam scope. Data tanpa koordinat valid tidak diplot dan jumlahnya diberitahukan.
- Klik titik membuka popup profil: nama, kode/NPSN, jenis, status/verifikasi, alamat, desa/kecamatan, PIC, jam layanan, fasilitas, radius, dan GPS. Tersedia petunjuk arah; tombol edit hanya untuk akun yang mempunyai izin edit.
- Popup menggunakan DOM `textContent` dan payload JSON yang aman untuk mencegah data profil menjadi HTML/script. Detail administratif tambahan tidak disertakan pada pemanggilan payload peta Home yang lama.
- Tidak ada perubahan data/database untuk pembaruan tampilan ini.
- Pengujian: `php tools/test_libraries_map.php` (25 pemeriksaan read-only); uji browser pada lebar 1440/390/320, 411 marker dengan tabel 10 baris, pergantian jumlah baris otomatis, filter/pencarian kosong, popup klik/keyboard, serta akun lokal baca-saja (1 lokasi tanpa tombol edit). Tidak ditemukan error JavaScript pada skenario tersebut.
- Rencana layanan operasional lintas perpustakaan: [PERPUSTAKAAN_TERPADU_DEVELOPMENT_PLAN.md](PERPUSTAKAAN_TERPADU_DEVELOPMENT_PLAN.md).

### Ikon, pengelompokan lokasi, dan popup — 4 Oktober 2026

Pembaruan lanjutan untuk `/libraries`:

| Jenis | Warna ikon admin | Simbol |
| --- | --- | --- |
| Perpustakaan Daerah | Biru `#2563eb` | Gedung pilar |
| Perpustakaan Sekolah | Hijau `#15803d` | Topi pendidikan |
| Perpustakaan Desa | Amber `#b45309` | Rumah |
| Perpustakaan Swasta | Ungu `#7c3aed` | Gedung bertingkat |
| Komunitas Literasi | Magenta `#be185d` | Kelompok orang |
| Mitra Pojok Baca | Teal `#0e7490` | Buku terbuka |

- Simbol SVG dibuat aplikasi dan konsisten antara pin, legenda, dan header popup. Jenis yang belum dikenal memakai ikon buku abu-abu. Warna ini hanya untuk tampilan peta admin; master warna database dan peta Home tidak diubah.
- Semua lokasi dalam scope tetap dimuat, tetapi dikelompokkan secara spasial ketika zoom out. Angka kelompok menunjukkan jumlah lokasi; cincin menunjukkan proporsi warna jenis anggota kelompok, bukan status perpustakaan.
- Klik kelompok memperbesar area. Zoom in menguraikan kelompok menjadi pin ikon. Koordinat yang bertumpuk dapat diurai (spiderfy) pada detail maksimal, sehingga bukan hanya lokasi paling atas yang bisa dipilih.
- Radius layanan hanya muncul untuk lokasi dengan popup terbuka dan hilang ketika popup ditutup. Radius tidak digambar serentak untuk seluruh lokasi.
- Popup terdiri dari header kategori/nama/kode/status, panel informasi yang dapat digulir, serta footer arah/edit yang tetap terlihat. Ikon dan badge membantu membedakan informasi; data profil tetap dirender sebagai teks aman.
- Pengelompokan menggunakan [Leaflet.markercluster](https://github.com/Leaflet/Leaflet.markercluster) versi **1.5.3**, disimpan lokal di `assets/vendor/leaflet-markercluster/` bersama lisensinya. Tidak menambah ketergantungan CDN untuk plugin cluster.
- Uji read-only: `php tools/test_libraries_map.php` kini **27 pemeriksaan**. Uji browser memverifikasi 411 lokasi menjadi 8 kelompok pada tampilan desktop awal (zoom 10), klik cluster, pin saat zoom in, koordinat bertumpuk, filter tabel independen, 6 warna/ikon legenda, popup pada lebar 1440/390/320, akun lokal baca-saja, dan akses keyboard. Tidak ada error JavaScript pada skenario tersebut. Jumlah kelompok berubah sesuai zoom dan ukuran peta.
- Tidak ada perubahan record perpustakaan, koordinat, atau hak akses database.

### Peta publik dan member — 4 Oktober 2026

- Landing page kini menggunakan peta interaktif bersama admin: ikon per jenis, cluster, popup, radius lokasi terpilih, petunjuk arah, dan atribusi OpenStreetMap. Seluruh perpustakaan **aktif** dengan GPS valid dimuat, tidak lagi terpotong 25 baris. Angka titik layanan mengikuti jumlah titik peta.
- Dashboard member serta `/user/reading-checkin` menampilkan peta perpustakaan aktif dan titik Pojok Baca aktif. Pojok Baca menggunakan ikon buku berwarna jingga agar dapat dibedakan dari jenis perpustakaan. Data perpustakaan yang berdekatan dengan titik baca tetap merupakan dua entitas dan dapat diurai lewat cluster.
- Payload publik memakai daftar field yang diizinkan (`Library_model::public_map_payload`); tidak menyertakan PIC, telepon/email, metadata sumber, tautan edit, atau data akun. Payload member menambahkan lokasi baca tanpa token, anggota, atau riwayat pribadi.
- Tombol **Lokasi saya** meminta GPS hanya setelah diklik; marker posisi pengguna tidak ikut cluster, dilengkapi lingkaran akurasi dan perkiraan lokasi terdekat berdasarkan jarak garis lurus. Tombol **Semua lokasi** mengembalikan cakupan peta.
- GPS membutuhkan HTTPS dan izin browser. Penolakan izin, timeout, lokasi tidak tersedia, serta browser tanpa dukungan GPS diberi pesan; pengguna tetap dapat menelusuri peta. Acuan: [MDN getCurrentPosition](https://developer.mozilla.org/en-US/docs/Web/API/Geolocation/getCurrentPosition).
- Preview GPS tidak menyimpan koordinat ke database dan tidak mengirim check-in otomatis. Form **Ambil GPS dan Check-in** tetap meminta GPS baru sebelum mengirim koordinat ke validasi server yang sudah ada. Klik lokasi perpustakaan tidak mengisi posisi pengguna dan tidak memberikan akses baca.
- Komponen: `partials/network_map*.php`, `assets/libraries-map.js`, `assets/css/network-map.css`. Tampilan admin tetap menggunakan payload/hak akses admin; komponen publik selalu menonaktifkan edit.
- Daftar lokasi pada sisi halaman check-in dibatasi tinggi tampilannya agar ratusan lokasi tidak membuat halaman memanjang tanpa batas.
- Batas pembacaan titik Pojok Baca mengikuti layanan yang ada (`get_active_points(500)`); tidak ada perubahan kebijakan token/validasi zona pada pekerjaan peta ini. Jika jumlah titik baca melebihi 500, batas tersebut perlu direkonsiliasi bersama pemeriksaan zona server.
- Pengujian model read-only: `php tools/test_libraries_map.php` (38 pemeriksaan). Browser menguji landing page dan view member dengan identitas fiktif pada lebar 1440/390/320: cluster, popup tanpa field admin, preview GPS, izin ditolak, timeout, dan tidak ada POST/check-in dari tombol preview. View member diuji melalui fixture untuk menghindari pencatatan kunjungan otomatis dashboard atau perubahan status token pada akun riil.
- Tombol check-in eksplisit juga diuji mengambil GPS baru dan mengirim latitude/longitude yang sesuai; request POST dicegat browser uji sehingga tidak mencatat kunjungan atau token di database. Saat pengujian, peta publik memuat 411 perpustakaan aktif dan peta member 412 lokasi (termasuk 1 Pojok Baca).

### Pengembangan berikutnya

- Tambah detail publik/profil perpustakaan.
- Tambah import data lokasi dari INLISLite bila mapping memungkinkan.
- Tambah drag/drop atau sort order foto galeri.
- Lengkapi validasi wilayah jika nanti ada perubahan resmi kode wilayah.
