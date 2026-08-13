# Modul Naskah Kuno

Modul ini dipakai untuk pelestarian dan digitalisasi naskah kuno/warisan dokumenter. Data operasional berada pada `ancient_manuscripts`; preview digital tidak disajikan langsung dari folder upload.

## Alur kerja

1. Petugas membuka `/manuscripts` lalu memilih **Input Naskah**.
2. Isi nomor inventaris, identitas fisik, asal-usul, deskripsi isi, dan catatan kondisi sebelum mengunggah hasil digitalisasi.
3. Unggah cover untuk representasi katalog. Preview tunggal PDF/citra tetap tersedia; untuk naskah yang sudah dipindai halaman demi halaman, unggah beberapa citra JPG/PNG/WEBP melalui **Halaman Digital**. Unggah secara bertahap dengan total maksimal 200 MB setiap penyimpanan.
4. Tentukan visibilitas rekam:
   - `Publik`: cover, metadata, dan deskripsi dapat dipreview siapa saja; isi digital tetap membutuhkan login member;
   - `Member`: rekam dan isi ditujukan bagi member login;
   - `Internal`: hanya tersimpan sebagai rekam internal dan tidak muncul di koleksi publik.
5. Gunakan status `Draft` selama verifikasi metadata; pilih `Tayang` hanya setelah informasi asal, hak pemanfaatan, dan preview sudah diperiksa.

## Prinsip pengelolaan

- Nomor inventaris adalah identitas unik naskah. Bila belum ada, kosongkan kolomnya: sistem menerbitkan nomor internal `NK-TAHUN-URUT` yang dapat diperbarui kelak.
- Catat ketidakpastian sebagai perkiraan, misalnya `akhir abad XIX`, bukan sebagai fakta pasti.
- Riwayat koleksi/provenans, kondisi fisik, serta hak pemanfaatan perlu diisi sebelum publikasi.
- Preview/master PDF disimpan pada `storage/manuscripts/YYYY/MM`; citra halaman pada `storage/manuscripts/{id}/pages`. Folder `storage` ditutup dari akses HTTP. PDF mentah tidak disajikan lagi ke user: petugas mengubahnya menjadi halaman viewer. Tamu hanya dapat melihat cover dan metadata; viewer `/naskah-kuno/baca/{id}` serta citra halaman selalu membutuhkan sesi member dan menulis jejak akses ke `ancient_manuscript_access_logs`.
- Penghapusan data dari UI hanya menghapus rekam database; file digital sengaja tidak dihapus otomatis agar tidak menghilangkan master scan tanpa pemeriksaan arsip.

## Titik akses

- Admin CRUD: `/manuscripts`
- Koleksi publik/member: `/naskah-kuno`
- Detail: `/naskah-kuno/detail/{id}`
- Preview terkendali: `/naskah-kuno/preview/{id}`
- Viewer halaman dan zoom: `/naskah-kuno/baca/{id}`

## PDF lama

Jika naskah hanya memiliki master PDF, buka Edit Naskah lalu pilih **Buat Halaman Viewer**. Sistem membuat citra halaman turunan memakai resolusi web; master PDF tetap aman di storage dan tidak diberikan sebagai unduhan.

Migrasi: `sql/2026-08-12l_ancient_manuscripts_module.sql` dan `sql/2026-08-13b_manuscript_page_viewer.sql`.
