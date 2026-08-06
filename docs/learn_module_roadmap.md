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
| Flashcard | Buat set flashcard, belajar mandiri | Medium | ✅ SELESAI (2026-08-06) |
| Story Quiz | Bacaan pendek + pertanyaan pemahaman | Medium | ✅ SELESAI (2026-08-06) |
| Notifikasi | Notif saat ada kompetisi baru / lencana baru | Medium | ✅ SELESAI (2026-08-06) |
| Mode Battle | Kuis 2 pemain (polling, bukan WebSocket) | Low | ✅ SELESAI (2026-08-07) |
| Export Raport | Raport progress belajar member (cetak/PDF via browser) | Low | ✅ SELESAI (2026-08-07) |

> **Fase 5 TUNTAS** — seluruh fitur lanjutan (High→Low) selesai 2026-08-07.

### Fase 5b: Flashcard belajar mandiri ✅ SELESAI (2026-08-06)

Deck berisi kartu istilah↔definisi. Member belajar mandiri: balik kartu (3D flip),
tandai "sudah hafal", progress tersimpan per kartu, dapat poin per sesi (cooldown 12 jam).

**File Kunci**
- SQL: `pustaka/sql/2026-08-06a_flashcards.sql` (3 tabel: `learn_flashcard_decks`, `learn_flashcard_cards` [FK cascade], `learn_flashcard_progress` [unik user+card]; seed rule `flashcard.study` + deck contoh 5 kartu + sys_page/menu + perm ADMIN)
- Model: `Learn_flashcards_model.php` (deck/card CRUD + `get_deck_cards_with_progress` + `set_card_status` upsert + summary)
- Controller Admin: `Learn_flashcards.php` → `/learn-flashcards` (deck + kartu per deck)
- Controller Publik: `Play_game::flashcard()` `/belajar/flashcard`, `flashcard_study($code)` `/belajar/flashcard/:code`, AJAX `flashcard_progress` & `flashcard_finish`
- Views: `learn/flashcards/index.php`+`cards.php`+partials, `game/flashcard_list.php`, `game/flashcard_study.php`

**Fitur**
- [x] Deck & kartu DB-driven, CRUD admin (mapel, jenjang, ikon, warna, urutan)
- [x] Kartu flip 3D + petunjuk opsional, keyboard (Spasi/1/2)
- [x] Progress "learning/known" per user per kartu (upsert), bar progress per deck
- [x] Poin otomatis per sesi (cooldown 12 jam) + cek badge
- [x] Halaman publik responsif di arena `/belajar`

### Fase 5c: Story Quiz (bacaan + pemahaman) ✅ SELESAI (2026-08-06)

Bacaan pendek + soal pilihan ganda pemahaman. Penilaian di server (anti-cheat),
poin sekali per bacaan (dedup via reference) + bonus nilai sempurna.

**File Kunci**
- SQL: `pustaka/sql/2026-08-06b_story_quiz.sql` (3 tabel: `learn_story_passages`, `learn_story_questions` [FK cascade], `learn_story_attempts`; rule `story.read` 10pt & `story.perfect` 15pt; bacaan contoh "Semut dan Belalang" 3 soal; sys_page/menu `learn.story` + perm ADMIN)
- Model: `Learn_story_model.php` (passage/question CRUD + `grade()` server-side + `record_attempt` + best scores)
- Controller Admin: `Learn_story.php` → `/learn-story` (bacaan + soal per bacaan)
- Controller Publik: `Play_game::cerita()` `/belajar/cerita`, `cerita_read($code)` `/belajar/cerita/:code`, AJAX `cerita_submit` `/belajar/cerita/submit`
- Views: `learn/story/index.php`+`questions.php`+partials, `game/story_list.php`, `game/story_read.php`

**Fitur**
- [x] Bacaan & soal DB-driven, CRUD admin (paragraf, mapel/jenjang, estimasi baca)
- [x] Halaman baca + soal pilihan ganda, timer durasi, opsi acak-friendly
- [x] Penilaian server-side (kunci tak bocor ke DOM) + reveal jawaban + penjelasan
- [x] Poin `story.read` (10) sekali/bacaan + bonus `story.perfect` (15) bila 100%
- [x] Nilai terbaik per bacaan tampil di daftar; badge auto-cek

### Fase 5d: Notifikasi in-app ✅ SELESAI (2026-08-06)

Notifikasi per-member: lencana baru (otomatis), pengumuman kompetisi & broadcast admin.

**File Kunci**
- SQL: `pustaka/sql/2026-08-06c_notifications.sql` (`learn_notifications` + `learn_broadcasts` log; sys_page/menu `learn.notifications` + perm ADMIN)
- Model: `Learn_notifications_model.php` (`notify` per-user, `broadcast` set-based INSERT..SELECT ke semua member, `unread_count`, `mark_read`)
- Hook: `Learn_points_model::_notify_badge()` — otomatis notif saat lencana diraih (guard `table_exists`)
- Admin: `Learn_notifications.php` → `/learn-notifications` (compose broadcast + riwayat)
- Kompetisi: `Quiz_competitions::announce($id)` — tombol "Umumkan ke Member" di daftar kompetisi
- Publik: `Play_game::notifikasi()` `/belajar/notifikasi` (auto mark-read) + `notif_read()` AJAX; lonceng + badge unread di lobby
- Views: `learn/notifications/index.php`, `game/notifications.php`

**Fitur**
- [x] Notif lencana baru otomatis (hook di award badge)
- [x] Broadcast admin ke semua member (5389) via 1 query set-based + log ringkasan
- [x] Tombol "Umumkan ke Member" pada kompetisi → notif tipe competition
- [x] Halaman notifikasi member + lonceng unread di arena; auto tandai terbaca

### Fase 5e: Mode Battle (adu cepat 2 pemain) ✅ SELESAI (2026-08-07)

Dua pemain menjawab soal yang sama, sinkron via **AJAX polling** (bukan WebSocket —
tidak butuh daemon, jalan di stack XAMPP/CI3). Skor & pemenang ditentukan server.

**File Kunci**
- SQL: `pustaka/sql/2026-08-07a_battle.sql` (`learn_battle_questions` [pool CRUD] + `learn_battle_rooms`; rule `battle.play` 5pt & `battle.win` 20pt; 10 soal contoh; sys_page/menu `learn.battle` + perm ADMIN)
- Model: `Learn_battle_model.php` (soal CRUD + `create_room`/`join_room`/`submit_answer` [nilai server, guard urutan]/`state`/`_maybe_finish` [tentukan pemenang + poin sekali via ref room])
- Controller Admin: `Learn_battle.php` → `/learn-battle` (pool soal + monitor ronde)
- Controller Publik: `Play_game::battle()` `/belajar/battle`, `battle_create`/`battle_join`, `battle_room($code)` `/belajar/battle/room/:code`, AJAX `battle_state($code)` & `battle_answer`
- Views: `learn/battle/index.php`, `game/battle_lobby.php`, `game/battle_room.php`

**Fitur**
- [x] Pool soal DB-driven, CRUD admin (kategori, kunci, aktif) + monitor ronde
- [x] Buat room (soal dibekukan acak) / gabung via kode 5-char
- [x] Gameplay polling ~1.5s: skor & progress lawan live; feedback benar/salah
- [x] Penilaian server-side + guard jawaban out-of-order/dobel
- [x] Pemenang otomatis (skor tertinggi/seri) + poin `battle.play`/`battle.win` sekali per room
- [x] Verifikasi CLI end-to-end (5–0, poin benar) + perbaikan bug tabel `visits`→`member_visits`

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
| 2026-08-06 | Fase 5b: Flashcard belajar mandiri selesai (deck/kartu CRUD, flip-card, progress, poin) |
| 2026-08-06 | Fase 5c: Story Quiz selesai (bacaan+soal CRUD, penilaian server-side, poin+bonus) |
| 2026-08-06 | Fase 5d: Notifikasi in-app selesai (badge auto, broadcast admin, umumkan kompetisi) |
| 2026-08-07 | Fase 5e: Mode Battle selesai (polling 2 pemain, penilaian server, poin); fix bug member_visits |
| 2026-08-07 | Fase 5f: Export Raport selesai (agregasi lintas-modul, cetak/PDF via browser). FASE 5 TUNTAS |

---

## Fase 5f: Export Raport Belajar ✅ SELESAI (2026-08-07)

Raport progress belajar per member: poin, lencana, ringkasan aktivitas (quiz, story,
flashcard, game, battle), riwayat quiz & poin. Cetak/PDF via browser (`window.print()`
+ `@media print`) — tanpa library PDF (composer belum terpasang di stack).

**File Kunci**
- SQL: `pustaka/sql/2026-08-07b_reports.sql` (hanya sys_page/menu `learn.reports` + perm ADMIN view+export; tanpa tabel baru)
- Model: `Learn_report_model.php` (`get_report()` agregasi lintas-modul dgn guard `table_exists`; `search_members`/`count_members` untuk picker)
- Controller Admin: `Learn_reports.php` → `/learn-reports` (picker member) + `/learn-reports/view/:user_id` (lembar printable)
- Controller Publik: `Play_game::raport()` → `/belajar/raport` (raport milik sendiri)
- Views: `learn/reports/index.php` (picker), `learn/reports/sheet.php` (lembar printable, dipakai admin & member)
- Arena: ikon Raport di lobby untuk member login

**Fitur**
- [x] Agregasi lintas-modul (poin, lencana, quiz/kompetisi, game, flashcard, story, battle)
- [x] Lembar raport rapi + KPI ringkas + riwayat, CSS `@media print`
- [x] Admin: cari member (5389) & buka raport siapa pun; Member: raport sendiri
- [x] Cetak/Simpan-PDF via browser (tombol + auto-print `?print=1`)
- [x] Verifikasi CLI: semua query agregasi jalan tanpa error

---

*File ini diperbarui seiring pengembangan. Terakhir diupdate: 2026-08-04*
