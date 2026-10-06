# English Quest RPG — Story Bible

Folder ini menjadi sumber kebenaran untuk desain cerita **English Quest RPG**. Isinya dibuat sebelum implementasi agar alur, karakter, quest, tujuan belajar, dan batasan teknis dapat direview bersama.

## Dokumen

- [Chapter 1 — The Missing Story Sigils](CHAPTER_01_LANTERNBROOK.md) — konsep lengkap chapter pertama, alur quest, karakter, scene, tantangan bahasa, reward, dan kriteria siap implementasi.

## Prinsip penulisan cerita

1. Bahasa utama di dalam game adalah bahasa Inggris. Bantuan Bahasa Indonesia hanya muncul bila pemain meminta hint.
2. Setiap chapter memiliki tujuan yang jelas, rangkaian tindakan, percakapan dua arah, tantangan, dan penutup yang tuntas.
3. Dialog tidak boleh menjadi daftar soal. Pemain harus merasa sedang membantu orang, menemukan sesuatu, dan mengambil keputusan.
4. Tantangan bahasa selalu punya konteks cerita. Kosakata baru dipakai kembali dalam percakapan atau tindakan berikutnya.
5. Satu chapter tidak berakhir hanya karena pemain berbicara dengan tiga NPC. Harus ada rangkaian objective yang selesai dan sebuah payoff cerita.
6. Kegagalan memberi kesempatan mencoba ulang dan feedback edukatif. Tidak ada jalan buntu permanen karena salah menjawab.
7. Setiap NPC memiliki fungsi dramatis dan fungsi belajar yang berbeda.
8. Struktur cerita boleh bercabang, tetapi semua cabang harus dapat kembali ke jalur utama tanpa kehilangan progres pemain.

## Status review

- Status: **konsep Chapter 1 disetujui dan vertical slice sudah dieksekusi**
- Sistem Hint/Translate menjadi bagian wajib setiap quest; biaya baseline (5/8 XP) masih dapat diseimbangkan lewat playtest.
- Implementasi tersimpan di world model Season 2, dengan migrasi NPC/quest di `sql/2026-08-14b_english_rpg_chapter1_story.sql`.
- Penyesuaian posisi NPC, jalur barat, dan titik jembatan disimpan di `sql/2026-08-15a_english_rpg_village_alignment.sql`.
- Progress lama Season 2 tetap dibaca dan dimigrasikan ke objective chain baru tanpa menghapus data pemain.
