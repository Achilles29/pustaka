# Dokumentasi Proyek Pustaka Digital Rembang

Dokumentasi ini menjadi catatan kerja utama untuk proyek perpustakaan digital terintegrasi Kabupaten Rembang.

## Dokumen Utama

- [ROADMAP.md](ROADMAP.md) - checklist roadmap, fase pengembangan, prioritas MVP, risiko, dan keputusan yang perlu dipastikan.
- [PRODUCT_VISION_PLUS.md](PRODUCT_VISION_PLUS.md) - bank ide produk agar pengalaman publik, admin, membership, GIS, dan pojok baca tetap menarik.
- [INLISLITE_MAPPING.md](INLISLITE_MAPPING.md) - pemetaan awal data INLISLite ke domain aplikasi baru.
- [SECURITY_AND_ACCESS.md](SECURITY_AND_ACCESS.md) - strategi hak akses, membership digital, GPS lock, kuota, dan proteksi konten.
- [RBAC_AND_SIDEBAR_STANDARD.md](RBAC_AND_SIDEBAR_STANDARD.md) - standar kanonis untuk registry halaman, permission, dan sidebar database.
- [CODING_STANDARDS.md](CODING_STANDARDS.md) - aturan coding, struktur modul, migrasi SQL, CSS, dan Git.
- [HANDOVER.md](HANDOVER.md) - catatan pindah device: lokasi proyek, database, route penting, akun demo, dan checklist validasi.
- [LIBRARIES_GIS.md](LIBRARIES_GIS.md) - schema dan implementasi awal direktori perpustakaan berbasis GIS.
- [SCAN_SUMMARY.md](SCAN_SUMMARY.md) - hasil scan ulang folder aplikasi INLISLite, database, aset, dan kesiapan CodeIgniter.
- [THEME_REFERENCES.md](THEME_REFERENCES.md) - kandidat theme/admin template gratis untuk aplikasi.
- [PROGRESS.md](PROGRESS.md) - catatan progress harian.

## Struktur Dokumentasi

- `README.md` menjadi pintu masuk dokumentasi.
- `ROADMAP.md` menjadi pegangan kerja dan checklist.
- `PROGRESS.md` hanya catatan kronologis harian, bukan tempat standar final.
- `HANDOVER.md` dipakai saat pindah device atau onboarding developer.
- `RBAC_AND_SIDEBAR_STANDARD.md` adalah satu-satunya dokumen kanonis untuk role, permission, registry halaman, dan sidebar.
- File scan dan CSV dipertahankan karena menjadi bukti inventaris INLISLite.

Catatan pembersihan 2026-07-29:

- `AUTH_RBAC_SIDEBAR.md` digabung ke `RBAC_AND_SIDEBAR_STANDARD.md` karena isinya tumpang tindih.
- `PRODUCT_VISION_PLUS.md` tetap dipisah dari roadmap karena fungsinya sebagai bank ide, sedangkan `ROADMAP.md` dipakai sebagai rencana eksekusi.
- `THEME_REFERENCES.md` tetap dipisah karena berisi keputusan referensi visual dan catatan risiko folder `tabler-dev`.

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
