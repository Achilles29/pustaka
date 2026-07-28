# Referensi Theme Gratis

Tanggal cek: 2026-07-28

## Rekomendasi Utama

## Status Lokal

Tabler dipakai sebagai basis admin panel. Folder source `tabler-dev` tidak disimpan di repository karena berisi banyak aset development dan pernah memicu GitHub push protection dari data demo upstream.

Status saat ini:

- aplikasi memakai CDN `@tabler/core@1.4.0` dari jsDelivr,
- CodeIgniter memuat Tabler dari `https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/...`,
- jika nanti butuh mode offline penuh, simpan hanya file dist yang dibutuhkan, bukan seluruh source repo Tabler.

### 1. Tabler

Link: https://tabler.io/admin-template

Kelebihan:

- Open source dan MIT license.
- Bootstrap-based, bersih, modern, dan terasa rapi untuk dashboard data.
- Cocok untuk dashboard admin, profil perpustakaan, tabel katalog, event, statistik, dan membership.
- Visualnya lebih segar daripada admin panel klasik.

Catatan:

- Cocok menjadi pilihan utama untuk aplikasi baru yang ingin terlihat modern dan premium.
- Perlu adaptasi server-rendered ke CodeIgniter views.

### 2. AdminLTE

Link: https://adminlte.io/

Kelebihan:

- Open source, populer, dan sudah lama dipakai banyak aplikasi admin.
- Versi baru berbasis Bootstrap 5.
- INLISLite lama sudah memakai tema AdminLTE melalui `inliscore\adminlte\Theme`, sehingga transisi mental admin akan lebih mudah.

Catatan:

- Pilihan aman jika ingin cepat membangun dashboard.
- Perlu styling custom agar tidak terasa seperti admin panel generik.

### 3. CoreUI Free Bootstrap Admin Template

Link: https://coreui.io/product/free-bootstrap-admin-template/

Kelebihan:

- Open source dengan MIT license.
- Bootstrap 5.
- Komponen admin lengkap dan profesional.
- Cocok jika aplikasi butuh pola UI enterprise yang stabil.

Catatan:

- Visualnya lebih korporat; bagus untuk admin, tetapi perlu sentuhan brand agar publik portal terasa hangat.

### 4. AdminKit

Link: https://adminkit.io/

Kelebihan:

- Bootstrap 5.
- Banyak komponen siap pakai.
- Tampilan bersih untuk dashboard operasional.

Catatan:

- Ada free dan premium; cek lisensi/detail paket sebelum dipakai permanen.

### 5. PlainAdmin

Link: https://plainadmin.com/

Kelebihan:

- Bootstrap 5 dan vanilla JavaScript.
- Relatif ringan.
- Cocok kalau ingin UI admin sederhana tanpa stack frontend berat.

Catatan:

- Untuk produk besar, komponennya mungkin perlu diperluas.

## Rekomendasi Desain untuk Pustaka Digital

Pilihan terbaik saat ini:

- Admin dashboard: Tabler atau AdminLTE.
- Portal publik katalog: Bootstrap 5 custom dengan komponen Tabler.
- Reader PDF: custom UI minimal, fokus membaca, bukan dashboard template.
- Kiosk/Pojok Baca: custom full-screen responsive UI dengan tombol besar, QR, status kuota, dan akses cepat koleksi.

Preferensi saya:

1. Tabler untuk tampilan utama karena lebih modern, bersih, dan cocok untuk aplikasi data besar.
2. AdminLTE sebagai fallback cepat karena familiar dengan ekosistem INLISLite lama.
3. Bootstrap Icons atau Lucide Icons untuk ikon.
4. Leaflet.js untuk peta perpustakaan dan titik pojok baca.
5. Chart.js atau ApexCharts untuk grafik dashboard.

## Arah Visual

Produk ini sebaiknya tidak terasa seperti aplikasi internal yang dingin saja. Ada dua mode visual:

- Admin: rapat, efisien, mudah discan, tabel dan filter kuat.
- Publik/member: hangat, literasi, katalog nyaman, kartu digital menarik, peta perpustakaan hidup, dan reader bersih.

Warna sebaiknya mengambil inspirasi Rembang dan perpustakaan:

- biru laut/pesisir sebagai aksen kepercayaan,
- hijau literasi sebagai aksen sekunder,
- putih/abu hangat untuk latar,
- sedikit kuning emas untuk highlight event dan capaian literasi.

Hindari tampilan yang terlalu ramai, terlalu gelap, atau terlalu mirip template mentah.
