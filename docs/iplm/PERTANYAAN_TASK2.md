# Pertanyaan lanjutan sesudah task2

Keputusan Anda tentang tahun data 2025, populasi `/libraries`, pengecualian TK/SKB, minimal D2, bukti dukung, dan pemustaka sebagai kunjungan **sudah diterapkan**. Tidak perlu menjawab ulang pertanyaan lama.

## Yang perlu jawaban untuk import pendataan berikutnya

1. **Tahun angka pendataan**: angka koleksi, siswa, tenaga, anggaran, dan kunjungan di `pendataan.xls` menggambarkan tahun/periode apa? Apakah sama untuk semua baris? Tanpa ini, angka tidak bisa dinyatakan sebagai data IPLM 2025. Waktu Created At/Updated At tidak cukup.
2. **68 kandidat dan empat pasangan ganda**: mohon verifikasi `PENCOCOKAN_PENDATAAN.xlsx`. Untuk kandidat, jawab dengan **ID sumber → ID library yang benar**, atau “lembaga berbeda/belum ada”. Untuk baris ganda, apakah benar satu perpustakaan dan ID sumber mana yang menjadi rujukan utama? Kedua riwayat tetap dipertahankan.
3. **Konflik NPSN**: khusus baris 52 (SD Negeri Demaan) dan 277 (SMP Negeri 1 Bulu), nilai mana yang benar? Cocokkan dengan dokumen sekolah sebelum mengubah sumber atau master.
4. **Perpusda**: apakah ID sumber 50964, NPP `3317103E1000003`, “Perpustakaan Umum Kabupaten Rembang” benar perpustakaan yang sama dengan master ID 2 “PERPUSTAKAAN UMUM DAERAH”? Jika ya, pasangan dan NPP dapat ditetapkan secara eksplisit.
5. **Tahap pendataan non-IPLM**: setelah daftar diverifikasi, apakah lanjut membuat penyimpanan batch/atribut pendataan umum, lalu memasukkan lembaga baru yang sudah disetujui? Rekomendasi: simpan sumber terlebih dahulu, jangan membuat seluruh baris belum cocok sebagai perpustakaan baru atau akun baru secara otomatis. SMA/SMK/MI/MTs/PT/khusus di luar kewenangan tetap boleh menjadi bahan pendataan, tetapi tidak otomatis menjadi populasi IPLM kabupaten.

## Pengaturan yang dapat Anda lakukan sendiri

- **Membuka pengiriman IPLM:** `/iplm/settings` → Periode & populasi → pilih periode 2026 → status **Dibuka** → simpan. Saat ini tetap Persiapan; rentang 2025 sudah dikonfirmasi.
- **Mengganti batas pendidikan:** tab Komponen & bukti dukung → edit `staff_qualified`. Formulir yang sudah dibuat menyimpan versi definisinya; jika aturan baru harus berlaku pada formulir lama, perlu pembaruan snapshot terkontrol dan permintaan pemeriksaan ulang.
- **Mewajibkan tautan bukti:** atur per komponen. Daftar jenis dokumen sudah lengkap; apakah kelak semua indikator wajib memiliki tautan (termasuk ketika nilainya nol) adalah kebijakan pengumpulan, bukan sesuatu yang saya tetapkan sepihak.

Tidak ada pertanyaan yang menghalangi penggunaan draft formulir sekarang. Pertanyaan di atas membatasi **penggabungan data sumber dan import statistik**, bukan menunda perbaikan tampilan/form yang sudah selesai.
