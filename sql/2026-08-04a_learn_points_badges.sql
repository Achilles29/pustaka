-- ============================================================
-- Modul Pembelajaran Inklusif: Poin & Lencana (Gamifikasi)
-- Pustaka Digital Rembang
-- Dibuat: 2026-08-04
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Konfigurasi aturan poin per aksi (CRUD oleh admin)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_point_rules` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `action_code`     VARCHAR(60)  NOT NULL COMMENT 'e.g. quiz.complete, quiz.pass, game.complete',
  `label`           VARCHAR(150) NOT NULL,
  `description`     TEXT         NULL,
  `points`          SMALLINT     NOT NULL DEFAULT 0,
  `cooldown_hours`  SMALLINT     NOT NULL DEFAULT 0 COMMENT '0 = tidak ada cooldown',
  `is_active`       TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_action_code` (`action_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data aturan poin
INSERT INTO `learn_point_rules` (`action_code`, `label`, `description`, `points`, `cooldown_hours`, `is_active`) VALUES
('quiz.complete',   'Selesaikan Quiz',         'Poin diberikan saat peserta menyelesaikan sesi latihan/kompetisi', 10, 0, 1),
('quiz.pass',       'Lulus Quiz',              'Tambahan poin saat nilai peserta mencapai passing grade',          25, 0, 1),
('quiz.perfect',    'Nilai Sempurna Quiz',     'Bonus poin saat peserta meraih 100%',                             50, 0, 1),
('game.complete',   'Selesaikan Game',         'Poin saat menyelesaikan satu sesi mini game',                      5, 0, 1),
('game.highscore',  'Rekor Baru di Game',      'Bonus poin saat mencetak skor tertinggi pribadi',                 15, 0, 1),
('visit.checkin',   'Kunjungan Pojok Baca',    'Poin saat melakukan check-in di pojok baca',                       3, 6, 1),
('book.read',       'Baca Buku Digital',       'Poin saat membaca buku digital (sekali per buku)',                20, 0, 1)
ON DUPLICATE KEY UPDATE
  `label`          = VALUES(`label`),
  `description`    = VALUES(`description`),
  `points`         = VALUES(`points`),
  `cooldown_hours` = VALUES(`cooldown_hours`);

-- ────────────────────────────────────────────────────────────
-- 2. Log poin yang diperoleh setiap member
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_member_points` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`          INT UNSIGNED NOT NULL,
  `rule_id`          INT UNSIGNED NOT NULL,
  `points`           SMALLINT     NOT NULL DEFAULT 0,
  `description`      VARCHAR(300) NULL,
  `reference_type`   VARCHAR(50)  NULL COMMENT 'quiz_attempt | game_session | visit | book',
  `reference_id`     INT UNSIGNED NULL,
  `awarded_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_awarded` (`user_id`, `awarded_at`),
  KEY `idx_rule`         (`rule_id`),
  CONSTRAINT `fk_mp_rule` FOREIGN KEY (`rule_id`) REFERENCES `learn_point_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 3. Definisi lencana (CRUD oleh admin)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_badge_definitions` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`                VARCHAR(60)  NOT NULL,
  `name`                VARCHAR(150) NOT NULL,
  `description`         TEXT         NULL,
  `icon`                VARCHAR(60)  NOT NULL DEFAULT 'ti-award' COMMENT 'Tabler icon name',
  `color`               VARCHAR(20)  NOT NULL DEFAULT '#3b82f6' COMMENT 'CSS color for badge bg',
  `criteria_type`       ENUM('points_total','quiz_complete','quiz_pass','quiz_perfect','game_complete','visit_count','book_read','custom')
                        NOT NULL DEFAULT 'points_total',
  `criteria_value`      INT          NOT NULL DEFAULT 1 COMMENT 'Threshold: poin, jumlah quiz, dll',
  `criteria_subject_id` INT UNSIGNED NULL COMMENT 'Jika harus pada mapel tertentu (NULL = mapel apapun)',
  `is_active`           TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_badge_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data lencana awal
INSERT INTO `learn_badge_definitions` (`code`, `name`, `description`, `icon`, `color`, `criteria_type`, `criteria_value`) VALUES
('first_quiz',      'Pejuang Pertama',   'Selesaikan quiz untuk pertama kali',              'ti-star',         '#f59e0b', 'quiz_complete',  1),
('quiz_pass_5',     'Bintang 5 Quiz',    'Lulus minimal 5 quiz apapun',                     'ti-award',        '#6366f1', 'quiz_pass',      5),
('quiz_pass_20',    'Juara Kelas',       'Lulus minimal 20 quiz',                           'ti-trophy',       '#eab308', 'quiz_pass',      20),
('perfect_scorer',  'Nilai Sempurna',    'Raih skor 100% di salah satu quiz',               'ti-rosette',      '#10b981', 'quiz_perfect',   1),
('game_explorer',   'Penjelajah Game',   'Selesaikan minimal 3 sesi mini game',             'ti-joystick',     '#8b5cf6', 'game_complete',  3),
('game_veteran',    'Veteran Game',      'Selesaikan minimal 20 sesi mini game',            'ti-device-gamepad', '#6366f1', 'game_complete', 20),
('points_50',       'Pengumpul Poin',    'Kumpulkan total 50 poin',                         'ti-coin',         '#f97316', 'points_total',   50),
('points_200',      'Kolektor 200 Poin', 'Kumpulkan total 200 poin',                        'ti-coins',        '#ef4444', 'points_total',   200),
('points_500',      'Sultan Poin',       'Kumpulkan total 500 poin',                        'ti-diamond',      '#ec4899', 'points_total',   500),
('reader_5',        'Pembaca Aktif',     'Baca minimal 5 buku digital',                     'ti-book',         '#14b8a6', 'book_read',      5),
('visitor_10',      'Pelanggan Setia',   'Kunjungi pojok baca minimal 10 kali',             'ti-door-enter',   '#0ea5e9', 'visit_count',    10)
ON DUPLICATE KEY UPDATE
  `name`         = VALUES(`name`),
  `description`  = VALUES(`description`),
  `icon`         = VALUES(`icon`),
  `color`        = VALUES(`color`),
  `criteria_type`  = VALUES(`criteria_type`),
  `criteria_value` = VALUES(`criteria_value`);

-- ────────────────────────────────────────────────────────────
-- 4. Lencana yang sudah diperoleh member
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_member_badges` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED NOT NULL,
  `badge_id`   INT UNSIGNED NOT NULL,
  `awarded_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_badge` (`user_id`, `badge_id`),
  CONSTRAINT `fk_mb_badge` FOREIGN KEY (`badge_id`) REFERENCES `learn_badge_definitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
