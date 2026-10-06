# Tindak lanjut data task4

Pilihan **kode aktivasi unik** sudah diterapkan. Tidak perlu menjawab ulang pertanyaan task3. Admin kabupaten dapat menerbitkan kode melalui **Pendataan IPLM → Aktivasi Perpustakaan**.

## 1. Identitas 35 baris sekolah yang belum pasti

Lihat [PERLU_VERIFIKASI_TASK4.md](PERLU_VERIFIKASI_TASK4.md) atau filter keputusan **TAHAN_NEGERI / TAHAN_NPSN** pada [PERSANDINGAN_TASK4.xlsx](PERSANDINGAN_TASK4.xlsx).

Jawab: **ID pendataan → ID master yang benar**, atau **lembaga berbeda, boleh ditambahkan**. Jika sekolah berganti nama, digabung, atau nomor di pendataan salah, tuliskan keterangannya. Baris tersebut belum dibuat menjadi unit baru; NPSN/nama Dapodik tidak diubah.

## 2. Lokasi unit tambahan

- 152 unit baru belum memiliki GPS yang layak untuk ditampilkan sebagai titik: koordinat kosong/tidak wajar/berulang massal. Mohon pengelola/kabupaten melengkapi titik yang benar setelah pemeriksaan.
- 19 unit belum cocok ke ID desa, termasuk satu yang belum memiliki ID kecamatan. Daftarnya ada pada dokumen verifikasi di atas. Teks wilayah sumber tetap tersimpan.

Ini bukan penghalang memakai modul atau mengaktivasi unit yang benar. Pencarian nama/NPSN tetap dapat menemukan unit tanpa titik GPS. Jangan menandai unit sudah diverifikasi hanya karena kode aktivasi berhasil dipakai; pemeriksaan data lokasi tetap diperlukan.

## 3. Populasi periode

Master dalam cakupan IPLM sekarang 738. Snapshot periode 2026 masih 403 sesuai data sebelum import. Bila ingin menggunakan total baru, admin cukup menyimpan kembali **mode otomatis** pada `/iplm/settings`; tidak diperlukan migrasi tambahan. Jangan mengubah angka laporan yang sudah ditetapkan tanpa mempertimbangkan periode dan dasar populasinya.
