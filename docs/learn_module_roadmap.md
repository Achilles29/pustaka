# Modul Pembelajaran Inklusif — Pustaka Digital Rembang
## Roadmap & Progress Tracker

---

## Fase 1: Quiz Engine ✅ SELESAI (2026-08-03)

### Fitur
- [x] Bank soal: pilihan ganda + essay
- [x] Kategorisasi mata pelajaran & jenjang kelas (DB-driven, CRUD)
- [x] Tingkat kesulitan (easy / medium / hard)
- [x] Tag soal multi-label
- [x] Import soal via CSV (template downloadable, header-flexible)
- [x] Sesi latihan: random dari bank, konfigurasi per sesi (mapel, kelas, jumlah)
- [x] Kompetisi: soal pre-assigned, peserta non-member, login kode+PIN
- [x] Penilaian essay manual oleh admin + recalculate total skor
- [x] Pembahasan soal (kunci jawaban + penjelasan) setelah submit
- [x] Anti-fraud: tab switch, clipboard block, right-click block, DevTools detect, timer, heartbeat (30s), fraud score, auto-flag/disqualify
- [x] Audit log semua aksi di `quiz_activity_log`
- [x] Fraud log per kejadian di `quiz_fraud_logs`

### File Kunci
- SQL: `pustaka/docs/quiz_engine.sql` (11 tabel)
- Models: `Quiz_config_model`, `Quiz_bank_model`, `Quiz_session_model`, `Quiz_play_model`
- Controllers: `Quiz_config`, `Quiz_bank`, `Quiz_sessions`, `Quiz_competitions`, `Quiz_play`
- Views: `quiz/config/`, `quiz/bank/`, `quiz/sessions/`, `quiz/competitions/`, `quiz/play/`

---

## Fase 2: Mini Games JS 🚧 DALAM PENGERJAAN (2026-08-04)

### Ide Game
| Kode | Nama | Deskripsi | Status |
|------|------|-----------|--------|
| `memory_match` | Memory Match | Balik kartu, cocokkan pasangan (kata ↔ definisi) | 🔄 Implementasi |
| `speed_math` | Hitung Cepat | Operasi +−×÷ dalam waktu terbatas | 🔄 Implementasi |
| `word_scramble` | Susun Kata | Acak huruf, susun kembali menjadi kata benar | 📋 Planned |
| `true_false` | Benar atau Salah | Swipe kartu: benar/salah dalam 30 detik | 📋 Planned |

### Arsitektur
- Tipe game disimpan di DB (`learn_game_types`) — admin bisa aktifkan/nonaktifkan
- Konten game (pasangan kata, daftar kata) dari DB (`learn_game_content_sets`, `learn_game_content_items`)
- Admin CRUD konten di `/learn-games`
- Sesi game terlog di `learn_game_sessions` (user_id, skor, durasi)
- Game berjalan pure JS di browser, fetch konten via API endpoint

### File Kunci
- SQL: `pustaka/sql/2026-08-04a_learn_points_badges.sql`
- SQL: `pustaka/sql/2026-08-04b_mini_games.sql`
- Model: `Learn_games_model.php`
- Controller Admin: `Learn_games.php` → `/learn-games`
- Controller Public: `Play_game.php` → `/belajar/game/...`
- Views: `learn/games/`, `game/memory_match.php`, `game/speed_math.php`

### Fitur
- [x] DB schema tipe game + konten
- [x] Admin: kelola tipe game (aktif/nonaktif)
- [x] Admin: CRUD kategori konten game
- [x] Admin: CRUD set konten (pasangan kata dll)
- [x] Admin: CRUD item konten (pair term–definition)
- [x] API endpoint: ambil konten game
- [x] Game Memory Match (JS murni, responsif, animasi flip)
- [x] Game Hitung Cepat (generated JS, konfigurasi dari DB)
- [x] Skor terlog per sesi game
- [x] Halaman pilih game publik `/belajar`

---

## Fase 3: Sistem Poin & Lencana 🚧 DALAM PENGERJAAN (2026-08-04)

### Ide Poin
| Aksi | Kode | Default Poin |
|------|------|-------------|
| Selesaikan quiz | `quiz.complete` | 10 |
| Lulus quiz | `quiz.pass` | 25 |
| Nilai sempurna quiz | `quiz.perfect` | 50 |
| Selesaikan game | `game.complete` | 5 |
| Skor tinggi game | `game.highscore` | 15 |
| Kunjungan pojok baca | `visit.checkin` | 3 |
| Baca buku digital | `book.read` | 20 |

Semua nilai poin bisa diubah admin melalui CRUD (`/learn-config`).

### Ide Lencana (Badge)
| Kode | Nama | Kriteria |
|------|------|----------|
| `first_quiz` | Pejuang Pertama | Selesaikan 1 quiz |
| `quiz_pass_5` | Lulus 5 Quiz | Lulus 5 quiz apapun |
| `perfect_scorer` | Nilai Sempurna | Raih nilai 100% di quiz |
| `game_explorer` | Penjelajah Game | Mainkan semua tipe game |
| `points_100` | Kolektor 100 Poin | Kumpulkan 100 poin |
| `points_500` | Kolektor 500 Poin | Kumpulkan 500 poin |
| `daily_streak_7` | Seminggu Aktif | Kunjungan / aktivitas 7 hari berturut |

Semua lencana bisa dikelola admin: nama, ikon, kriteria, warna.

### Arsitektur
- `learn_point_rules` — konfigurasi poin per aksi (CRUD oleh admin)
- `learn_member_points` — log poin per member per aksi
- `learn_badge_definitions` — definisi lencana (CRUD oleh admin)
- `learn_member_badges` — lencana yang diperoleh member
- `award_points()` di `Learn_points_model` — dipanggil dari model lain (quiz, game, visit)
- `check_and_award_badges()` — otomatis cek + berikan lencana setelah ada aktivitas

### File Kunci
- Model: `Learn_points_model.php`
- Controller Admin: `Learn_config.php` → `/learn-config`
- Views: `learn/config/index.php` (dua tab: Point Rules + Badges)

---

## Fase 4: Dashboard Progress Belajar 🚧 DALAM PENGERJAAN (2026-08-04)

### Widget di `/user/dashboard`
- Total poin + ranking global
- Lencana yang sudah diraih (icon grid)
- Riwayat 5 quiz terakhir (nama, skor, tanggal)
- Riwayat 5 game terakhir (nama game, skor)
- Shortcut ke halaman belajar `/belajar`

### Halaman Public Belajar `/belajar`
- Pilihan tipe belajar: Quiz Latihan, Kompetisi, Game
- Kartu game yang tersedia
- Leaderboard poin mingguan

---

## Fase 5: Fitur Lanjutan 💡

| Fitur | Deskripsi | Prioritas | Status |
|-------|-----------|-----------|--------|
| Poin → Token Baca | Tukar poin dengan token baca tambahan | High | ✅ SELESAI (2026-08-04) |
| Flashcard | Buat set flashcard, belajar mandiri | Medium | 📋 Planned |
| Story Quiz | Bacaan pendek + pertanyaan pemahaman | Medium | 📋 Planned |
| Notifikasi | Notif saat ada kompetisi baru / lencana baru | Medium | 📋 Planned |
| Mode Battle | Kuis real-time 2 pemain via WebSocket | Low | 📋 Planned |
| Export Raport | PDF raport per member berisi progress belajar | Low | 📋 Planned |

### Fase 5a: Tukar Poin → Token Baca ✅ SELESAI (2026-08-04)

Menghubungkan modul pembelajaran dengan sistem token baca perpustakaan.
Member menukar poin belajar → token baca digital (`reading_tokens`) terbit otomatis.

**Alur**: member buka `/belajar/tukar` → pilih hadiah → poin dikurangi (baris negatif
di `learn_member_points`, anchor rule `redeem.reading_token`) + `reading_tokens` terbit
untuk member (via `members.auth_user_id`), semua dalam satu transaksi DB.

**File Kunci**
- SQL: `pustaka/sql/2026-08-04d_reward_exchange.sql` (2 tabel: `learn_reward_catalog`, `learn_reward_redemptions` + seed rule & katalog + sys_page/menu + perm ADMIN)
- Model: `Learn_rewards_model.php` (CRUD katalog + `redeem()` transaksi)
- Controller Admin: `Learn_rewards.php` → `/learn-rewards` (CRUD + riwayat)
- Controller Publik: `Play_game::hadiah()` `/belajar/tukar`, `Play_game::redeem_reward()` `/belajar/tukar/redeem` (AJAX)
- Views: `learn/rewards/index.php`, `learn/rewards/_form_fields.php`, `learn/rewards/redemptions.php`, `game/rewards.php`

**Fitur**
- [x] Katalog hadiah DB-driven, CRUD admin (biaya poin, kuota token, satuan, masa berlaku, stok, batas per user)
- [x] Penukaran transaksional: kurangi poin + terbitkan token baca + log
- [x] Validasi: poin cukup, stok, batas per user, akun tertaut member
- [x] Halaman member responsif + modal sukses menampilkan kode token
- [x] Riwayat penukaran (admin & member)
- [x] Perbaikan bug tabel `auth_users`→`auth_user` di leaderboard poin

---

## Progress Log

| Tanggal | Aktivitas |
|---------|-----------|
| 2026-08-03 | Fase 1 Quiz Engine selesai — semua model, controller, view |
| 2026-08-04 | Fase 2 (Games) + Fase 3 (Poin/Lencana) + Fase 4 (Dashboard) selesai |
| 2026-08-04 | Sidebar "Pembelajaran" + wizard kompetisi 3-langkah + bank soal bulk-select |
| 2026-08-04 | Import penuh skema quiz+learn ke DB lokal; perbaikan idempotensi SQL sidebar |
| 2026-08-04 | Fase 5a: Tukar Poin → Token Baca selesai (katalog, transaksi, halaman member) |

---

*File ini diperbarui seiring pengembangan. Terakhir diupdate: 2026-08-04*
