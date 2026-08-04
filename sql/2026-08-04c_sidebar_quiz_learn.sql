-- ============================================================
-- Quiz Engine & Learn Module: Sidebar, Page Registry, Schema
-- Pustaka Digital Rembang — 2026-08-04
-- ------------------------------------------------------------
-- URUTAN JALANKAN (jika DB masih kosong untuk modul ini):
--   1) docs/quiz_engine.sql              (tabel dasar quiz)
--   2) sql/2026-08-04a_learn_points_badges.sql
--   3) sql/2026-08-04b_mini_games.sql
--   4) sql/2026-08-04c_sidebar_quiz_learn.sql   <-- FILE INI
-- Semua statement idempotent (aman dijalankan berulang).
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Tambah kolom baru ke quiz_sessions (butuh tabel sudah ada)
-- ────────────────────────────────────────────────────────────
ALTER TABLE `quiz_sessions`
  ADD COLUMN IF NOT EXISTS `scoring_system`   ENUM('standard','tka')                 NOT NULL DEFAULT 'standard' COMMENT 'standard=biasa, tka=+4/-1/0' AFTER `passing_score`,
  ADD COLUMN IF NOT EXISTS `time_mode`        ENUM('per_participant','simultaneous') NOT NULL DEFAULT 'per_participant' COMMENT 'Waktu dimulai masing-masing atau serentak' AFTER `scoring_system`,
  ADD COLUMN IF NOT EXISTS `access_mode`      ENUM('assigned','public')              NOT NULL DEFAULT 'assigned' COMMENT 'assigned=token wajib, public=langsung join' AFTER `time_mode`,
  ADD COLUMN IF NOT EXISTS `show_leaderboard` TINYINT(1) NOT NULL DEFAULT 0 AFTER `access_mode`,
  ADD COLUMN IF NOT EXISTS `announce_results` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Umumkan hasil ke peserta setelah selesai' AFTER `show_leaderboard`,
  ADD COLUMN IF NOT EXISTS `allow_self_reset` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Peserta bisa reset attempt sendiri' AFTER `announce_results`,
  ADD COLUMN IF NOT EXISTS `is_published`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Publikasikan ke halaman publik' AFTER `allow_self_reset`,
  ADD COLUMN IF NOT EXISTS `is_paused`        TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=dijeda, peserta tidak bisa mulai' AFTER `is_published`,
  ADD COLUMN IF NOT EXISTS `has_certificate`  TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_paused`;

-- ────────────────────────────────────────────────────────────
-- 2. Register halaman (sys_page) untuk quiz & learn modules
--    `code` UNIQUE → idempotent via ON DUPLICATE KEY UPDATE
-- ────────────────────────────────────────────────────────────
INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`, `is_active`) VALUES
  ('quiz_config.index',       'Quiz Engine',  'Konfigurasi Quiz', 'quiz-config',       'Kelola jenjang kelas dan mata pelajaran',        1),
  ('quiz_bank.index',         'Quiz Engine',  'Bank Soal',        'quiz-bank',         'Kelola bank soal pilihan ganda dan essay',      1),
  ('quiz_sessions.index',     'Quiz Engine',  'Sesi Latihan',     'quiz-sessions',     'Kelola sesi latihan soal',                      1),
  ('quiz_competitions.index', 'Quiz Engine',  'Kompetisi',        'quiz-competitions', 'Kelola kompetisi dan peserta',                  1),
  ('learn_config.index',      'Pembelajaran', 'Poin & Lencana',   'learn-config',      'Konfigurasi aturan poin dan definisi lencana',  1),
  ('learn_games.index',       'Pembelajaran', 'Konten Game',      'learn-games',       'Kelola tipe game dan konten mini game',         1)
ON DUPLICATE KEY UPDATE
  `module`      = VALUES(`module`),
  `title`       = VALUES(`title`),
  `route`       = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active`   = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 3a. Menu induk: grup PEMBELAJARAN
--     Konvensi grup: parent_id NULL, page_id NULL, url NULL.
--     sort_order 75 → antara "Layanan Digital" (70) & "Data Master" (80).
--     `menu_key` UNIQUE → idempotent.
-- ────────────────────────────────────────────────────────────
INSERT INTO `sys_menu`
  (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES
  (NULL, NULL, 'MAIN', 'learn.group', 'Pembelajaran', 'ti ti-school', NULL, 75, 1, 1, 0)
ON DUPLICATE KEY UPDATE
  `title`      = VALUES(`title`),
  `icon`       = VALUES(`icon`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = VALUES(`is_visible`),
  `is_active`  = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 3b. Menu anak (di bawah grup Pembelajaran)
--     parent_id diambil dari variabel @grp, page_id via subquery.
--     Bentuk INSERT..VALUES + ON DUPLICATE KEY UPDATE (paling portabel).
-- ────────────────────────────────────────────────────────────
SET @grp := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'learn.group' LIMIT 1);

INSERT INTO `sys_menu`
  (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'quiz_bank.index'),         'MAIN', 'learn.quiz_bank',         'Bank Soal',         'ti ti-clipboard-list',  'quiz-bank',         1, 1, 1, 0),
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'quiz_sessions.index'),     'MAIN', 'learn.quiz_sessions',     'Sesi Latihan',      'ti ti-pencil-check',    'quiz-sessions',     2, 1, 1, 0),
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'quiz_competitions.index'), 'MAIN', 'learn.quiz_competitions', 'Kompetisi & Lomba', 'ti ti-trophy',          'quiz-competitions', 3, 1, 1, 0),
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'learn_games.index'),       'MAIN', 'learn.games',             'Konten Game',       'ti ti-device-gamepad',  'learn-games',       4, 1, 1, 0),
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'learn_config.index'),      'MAIN', 'learn.config',            'Poin & Lencana',    'ti ti-award',           'learn-config',      5, 1, 1, 0),
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'quiz_config.index'),       'MAIN', 'learn.quiz_config',       'Jenjang & Mapel',   'ti ti-adjustments',     'quiz-config',       6, 1, 1, 0)
ON DUPLICATE KEY UPDATE
  `parent_id`  = VALUES(`parent_id`),
  `page_id`    = VALUES(`page_id`),
  `title`      = VALUES(`title`),
  `icon`       = VALUES(`icon`),
  `url`        = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = VALUES(`is_visible`),
  `is_active`  = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 4. Hak akses default untuk role ADMIN (role_id = 2)
--    Superadmin otomatis full-access (bypass), tidak perlu baris.
--    PK (role_id, page_id) → idempotent. Bisa dicabut via RBAC UI.
-- ────────────────────────────────────────────────────────────
INSERT INTO `auth_role_permission`
  (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT 2, p.`id`, 1, 1, 1, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` IN (
  'quiz_config.index', 'quiz_bank.index', 'quiz_sessions.index',
  'quiz_competitions.index', 'learn_config.index', 'learn_games.index'
)
ON DUPLICATE KEY UPDATE
  `can_view`   = 1,
  `can_create` = 1,
  `can_edit`   = 1,
  `can_delete` = 1,
  `can_export` = 1;
