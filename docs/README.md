# Dokumentasi Proyek Pustaka Digital Rembang

Dokumentasi ini menjadi catatan kerja utama untuk proyek perpustakaan digital terintegrasi Kabupaten Rembang.

## Dokumen Utama

- [ROADMAP.md](ROADMAP.md) - arah produk, modul, fase pengembangan, dan prioritas rilis.
- [PRODUCT_VISION_PLUS.md](PRODUCT_VISION_PLUS.md) - pengembangan ide agar produk terasa modern, lengkap, dan menarik.
- [INLISLITE_MAPPING.md](INLISLITE_MAPPING.md) - pemetaan awal data INLISLite ke domain aplikasi baru.
- [SECURITY_AND_ACCESS.md](SECURITY_AND_ACCESS.md) - strategi hak akses, membership digital, GPS lock, kuota, dan proteksi konten.
- [AUTH_RBAC_SIDEBAR.md](AUTH_RBAC_SIDEBAR.md) - fondasi role, permission, user, dan menu/sidebar berbasis database.
- [LIBRARIES_GIS.md](LIBRARIES_GIS.md) - schema dan implementasi awal direktori perpustakaan berbasis GIS.
- [SCAN_SUMMARY.md](SCAN_SUMMARY.md) - hasil scan ulang folder aplikasi INLISLite, database, aset, dan kesiapan CodeIgniter.
- [THEME_REFERENCES.md](THEME_REFERENCES.md) - kandidat theme/admin template gratis untuk aplikasi.
- [PROGRESS.md](PROGRESS.md) - catatan progress harian.

## Laporan Inventaris Generated

- [SCAN_DATABASE_TABLE_COUNTS.csv](SCAN_DATABASE_TABLE_COUNTS.csv) - row count semua tabel database `inlislite_v3`.
- [SCAN_INLISLITE_TOP_FOLDERS.csv](SCAN_INLISLITE_TOP_FOLDERS.csv) - ringkasan ukuran folder utama INLISLite.
- [SCAN_INLISLITE_FILE_EXTENSIONS.csv](SCAN_INLISLITE_FILE_EXTENSIONS.csv) - ringkasan file berdasarkan ekstensi.
- [SCAN_INLISLITE_UPLOADED_FILES.csv](SCAN_INLISLITE_UPLOADED_FILES.csv) - ringkasan folder `uploaded_files`.
- [SCAN_ASSET_REFERENCE_MATCH.csv](SCAN_ASSET_REFERENCE_MATCH.csv) - kecocokan referensi database dengan file foto/cover.

## Prinsip Awal

- Database `pustaka` menjadi database operasional aplikasi baru.
- INLISLite menjadi sumber data katalog, koleksi, anggota, dan histori tertentu, tetapi aplikasi baru tidak menyalin seluruh struktur INLISLite begitu saja.
- Data personal anggota dianggap sensitif. Contoh data pribadi dari dump SQL tidak boleh ditulis ke dokumentasi, commit, log publik, atau tampilan demo.
- Proteksi PDF harus berlapis di server dan client. Mematikan klik kanan hanya fitur kecil, bukan lapisan keamanan utama.
- Sistem dirancang multi-perpustakaan: Perpusda, sekolah, desa, swasta, dan pojok baca digital.
