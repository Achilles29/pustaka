# Donasi Digital Berlisensi Terbuka

Form publik: `/donasi-digital`. Form dapat digunakan tanpa login; akun member hanya dipakai untuk mengaitkan identitas pengirim bila tersedia.

## Alur

1. Kontributor mengisi identitas kontak, metadata karya, jenis kontribusi, dan lisensi terbuka.
2. Kontributor mengunggah satu berkas (maksimum 200 MB) **atau** memberi tautan HTTP/HTTPS ke repositori/penyimpanan eksternal. Minimal satu sumber wajib tersedia.
3. Kontributor menyetujui pernyataan bahwa ia berhak menyerahkan karya dengan lisensi `CC0`, `CC BY`, `CC BY-SA`, atau domain publik.
4. Kiriman disimpan sebagai `pending`, tidak masuk katalog dan tidak tersedia untuk publik.
5. ADMIN/SUPERADMIN melihat antrean pada `/digital-donations` dan Kotak Masuk, lalu menandai `Ditinjau`, `Diterima`, `Perlu perbaikan`, atau `Ditolak` beserta catatan.
6. Donasi berstatus `Diterima` menampilkan aksi **Masukkan ke Katalog**. Metadata usulan menjadi isian awal, sedangkan petugas wajib melengkapi jenis koleksi, kategori isi, klasifikasi/DDC, nomor panggil, dan metadata bibliografi lain.
7. Entri selalu dibuat sebagai `draft` dan ditautkan ke donasi untuk mencegah input ganda. PDF lokal langsung dicatat sebagai aset digital `draft`; EPUB, DOC/DOCX, ODT, dan TXT diproses oleh worker CLI menjadi PDF Reader sementara berkas sumber tetap dipertahankan. Akses awal bersifat internal sampai pemeriksaan akhir.

Worker konversi berjalan setiap menit melalui crontab akun aplikasi:

```bash
php index.php digital_donation_jobs run 10
```

## Keamanan dan hak

- Berkas tersimpan di `storage/digital-donations`, tidak memiliki URL publik, dan hanya dapat dibuka petugas yang berizin.
- File yang diterima: PDF, EPUB, DOC/DOCX, ODT, dan TXT.
- Status `Diterima` berarti lolos tahap usulan; petugas tetap perlu memasukkan metadata/koleksi ke katalog dan menentukan kebijakan akses sebelum menerbitkannya.
- Jangan menerima karya berhak cipta tertutup, salinan buku komersial, atau lisensi yang tidak dapat diverifikasi.

Migrasi: `sql/2026-08-13c_digital_donations.sql`.
