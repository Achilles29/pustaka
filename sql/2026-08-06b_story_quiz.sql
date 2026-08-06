-- ============================================================
-- Modul Pembelajaran — Story Quiz (Bacaan + Pemahaman)
-- Pustaka Digital Rembang — 2026-08-06
-- ------------------------------------------------------------
-- Fase 5c. Bacaan pendek + pertanyaan pemahaman (pilihan ganda).
-- Melatih kemampuan membaca & pemahaman member.
-- Semua statement idempotent (aman dijalankan berulang).
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Bacaan (passage) — DB-driven, dikelola admin (CRUD)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_story_passages` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`              VARCHAR(60)  NOT NULL,
  `title`             VARCHAR(180) NOT NULL,
  `body`              TEXT         NOT NULL COMMENT 'Isi bacaan (paragraf dipisah baris baru)',
  `summary`           VARCHAR(300) NULL COMMENT 'Ringkasan singkat untuk kartu',
  `subject_id`        INT NULL,
  `grade_level_id`    INT NULL,
  `icon`              VARCHAR(60)  NOT NULL DEFAULT 'ti-book',
  `color`             VARCHAR(20)  NOT NULL DEFAULT '#0891b2',
  `estimated_minutes` INT UNSIGNED NOT NULL DEFAULT 3,
  `is_active`         TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`        INT UNSIGNED NOT NULL DEFAULT 100,
  `created_by`        INT NULL,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_passage_code` (`code`),
  KEY `idx_passage_active` (`is_active`),
  KEY `idx_passage_subject` (`subject_id`),
  KEY `idx_passage_grade` (`grade_level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 2. Pertanyaan pemahaman untuk sebuah bacaan
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_story_questions` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `passage_id`     INT UNSIGNED NOT NULL,
  `question`       VARCHAR(500) NOT NULL,
  `option_a`       VARCHAR(300) NOT NULL,
  `option_b`       VARCHAR(300) NOT NULL,
  `option_c`       VARCHAR(300) NULL,
  `option_d`       VARCHAR(300) NULL,
  `correct_option` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=A,1=B,2=C,3=D',
  `explanation`    VARCHAR(500) NULL,
  `sort_order`     INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sq_passage` (`passage_id`),
  CONSTRAINT `fk_sq_passage` FOREIGN KEY (`passage_id`) REFERENCES `learn_story_passages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 3. Percobaan (attempt) member menyelesaikan story quiz
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_story_attempts` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`          INT UNSIGNED NOT NULL,
  `passage_id`       INT UNSIGNED NOT NULL,
  `correct_count`    INT UNSIGNED NOT NULL DEFAULT 0,
  `total_questions`  INT UNSIGNED NOT NULL DEFAULT 0,
  `score_percent`    DECIMAL(5,2) NOT NULL DEFAULT 0,
  `duration_seconds` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sa_user` (`user_id`),
  KEY `idx_sa_passage` (`passage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 4. Aturan poin (dedup per bacaan via reference; anti-farm)
-- ────────────────────────────────────────────────────────────
INSERT INTO `learn_point_rules` (`action_code`, `label`, `description`, `points`, `cooldown_hours`, `is_active`) VALUES
  ('story.read',    'Selesai Baca',      'Menyelesaikan satu story quiz (bacaan + soal)', 10, 0, 1),
  ('story.perfect', 'Pemahaman Sempurna','Menjawab semua soal story quiz dengan benar',    15, 0, 1)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`), `description` = VALUES(`description`), `is_active` = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 5. Contoh bacaan + soal (starter — bisa diedit/hapus via CRUD)
-- ────────────────────────────────────────────────────────────
INSERT INTO `learn_story_passages` (`code`, `title`, `body`, `summary`, `icon`, `color`, `estimated_minutes`, `sort_order`, `is_active`)
VALUES (
  'semut_belalang',
  'Semut dan Belalang',
  'Pada suatu musim panas, seekor belalang asyik bernyanyi dan bermain sepanjang hari. Ia melompat ke sana kemari tanpa memikirkan apa pun.\n\nDi dekatnya, sekelompok semut bekerja keras mengumpulkan makanan. Mereka membawa butir-butir gandum ke sarang untuk persediaan musim dingin.\n\nBelalang menertawakan semut, "Mengapa kalian bekerja begitu keras? Ayo bermain bersamaku!" Namun semut tetap bekerja tanpa menghiraukannya.\n\nKetika musim dingin tiba, salju menutupi tanah. Belalang kelaparan karena tidak punya makanan. Sementara itu, para semut hidup nyaman dengan persediaan yang mereka kumpulkan. Belalang pun menyesal karena tidak mempersiapkan diri sejak awal.',
  'Fabel klasik tentang kerja keras dan persiapan.',
  'ti-bug', '#0891b2', 3, 1, 1
)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `body` = VALUES(`body`), `summary` = VALUES(`summary`);

SET @pid := (SELECT `id` FROM `learn_story_passages` WHERE `code` = 'semut_belalang' LIMIT 1);

-- Hapus soal contoh lama untuk passage ini agar seed idempoten (hindari duplikat),
-- lalu masukkan ulang. Aman karena hanya menyentuh soal bawaan.
DELETE FROM `learn_story_questions` WHERE `passage_id` = @pid;
INSERT INTO `learn_story_questions` (`passage_id`, `question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `explanation`, `sort_order`) VALUES
  (@pid, 'Apa yang dilakukan belalang sepanjang musim panas?', 'Bekerja keras', 'Bernyanyi dan bermain', 'Mengumpulkan gandum', 'Tidur di sarang', 1, 'Belalang asyik bernyanyi dan bermain sepanjang hari.', 1),
  (@pid, 'Mengapa semut bekerja keras mengumpulkan makanan?', 'Untuk dijual', 'Untuk persediaan musim dingin', 'Karena disuruh belalang', 'Untuk pesta', 1, 'Semut menyiapkan persediaan untuk musim dingin.', 2),
  (@pid, 'Apa pelajaran dari cerita ini?', 'Bermain lebih penting', 'Jangan bekerja keras', 'Pentingnya kerja keras dan persiapan', 'Semut lebih kuat dari belalang', 2, 'Cerita mengajarkan pentingnya kerja keras dan mempersiapkan diri.', 3);

-- ────────────────────────────────────────────────────────────
-- 6. Registrasi halaman + menu sidebar (grup Pembelajaran)
-- ────────────────────────────────────────────────────────────
INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`, `is_active`)
VALUES ('learn_story.index', 'Pembelajaran', 'Story Quiz', 'learn-story', 'Kelola bacaan & soal pemahaman story quiz', 1)
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`), `title` = VALUES(`title`),
  `route`  = VALUES(`route`),  `description` = VALUES(`description`), `is_active` = VALUES(`is_active`);

SET @grp := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'learn.group' LIMIT 1);

INSERT INTO `sys_menu`
  (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'learn_story.index'), 'MAIN', 'learn.story', 'Story Quiz', 'ti ti-book', 'learn-story', 9, 1, 1, 0)
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`), `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`), `is_visible` = VALUES(`is_visible`), `is_active` = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 7. Hak akses default role ADMIN (role_id = 2)
-- ────────────────────────────────────────────────────────────
INSERT INTO `auth_role_permission`
  (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT 2, p.`id`, 1, 1, 1, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'learn_story.index'
ON DUPLICATE KEY UPDATE
  `can_view` = 1, `can_create` = 1, `can_edit` = 1, `can_delete` = 1, `can_export` = 1;
