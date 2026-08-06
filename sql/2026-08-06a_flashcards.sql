-- ============================================================
-- Modul Pembelajaran — Flashcard (Belajar Mandiri Kartu)
-- Pustaka Digital Rembang — 2026-08-06
-- ------------------------------------------------------------
-- Fase 5b. Set kartu (deck) berisi kartu istilah↔definisi.
-- Member belajar mandiri: balik kartu, tandai "sudah dikuasai".
-- Semua statement idempotent (aman dijalankan berulang).
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Deck (set kartu) — DB-driven, dikelola admin (CRUD)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_flashcard_decks` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`           VARCHAR(60)  NOT NULL,
  `name`           VARCHAR(150) NOT NULL,
  `description`    VARCHAR(300) NULL,
  `subject_id`     INT NULL,
  `grade_level_id` INT NULL,
  `icon`           VARCHAR(60)  NOT NULL DEFAULT 'ti-cards',
  `color`          VARCHAR(20)  NOT NULL DEFAULT '#8b5cf6',
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`     INT UNSIGNED NOT NULL DEFAULT 100,
  `created_by`     INT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_deck_code` (`code`),
  KEY `idx_deck_active` (`is_active`),
  KEY `idx_deck_subject` (`subject_id`),
  KEY `idx_deck_grade` (`grade_level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 2. Kartu dalam deck
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_flashcard_cards` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `deck_id`    INT UNSIGNED NOT NULL,
  `front`      VARCHAR(300)  NOT NULL COMMENT 'Sisi depan (istilah/pertanyaan)',
  `back`       VARCHAR(1000) NOT NULL COMMENT 'Sisi belakang (definisi/jawaban)',
  `hint`       VARCHAR(300)  NULL,
  `sort_order` INT UNSIGNED  NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)    NOT NULL DEFAULT 1,
  `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_deck_front` (`deck_id`, `front`(180)),
  KEY `idx_card_deck` (`deck_id`),
  CONSTRAINT `fk_card_deck` FOREIGN KEY (`deck_id`) REFERENCES `learn_flashcard_decks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 3. Progress belajar per user per kartu
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_flashcard_progress` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`          INT UNSIGNED NOT NULL,
  `card_id`          INT UNSIGNED NOT NULL,
  `deck_id`          INT UNSIGNED NOT NULL,
  `status`           ENUM('learning','known') NOT NULL DEFAULT 'learning',
  `review_count`     INT UNSIGNED NOT NULL DEFAULT 0,
  `last_reviewed_at` DATETIME     NULL,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_card` (`user_id`, `card_id`),
  KEY `idx_prog_user_deck` (`user_id`, `deck_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 4. Aturan poin untuk sesi belajar flashcard (cooldown 12 jam)
-- ────────────────────────────────────────────────────────────
INSERT INTO `learn_point_rules` (`action_code`, `label`, `description`, `points`, `cooldown_hours`, `is_active`)
VALUES ('flashcard.study', 'Belajar Flashcard', 'Menyelesaikan sesi belajar satu deck flashcard', 5, 12, 1)
ON DUPLICATE KEY UPDATE
  `label`       = VALUES(`label`),
  `description` = VALUES(`description`),
  `is_active`   = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 5. Contoh deck + kartu (starter — bisa diedit/hapus via CRUD)
-- ────────────────────────────────────────────────────────────
INSERT INTO `learn_flashcard_decks` (`code`, `name`, `description`, `icon`, `color`, `sort_order`, `is_active`)
VALUES ('ipa_dasar', 'IPA Dasar — Istilah Penting', 'Kumpulan istilah dasar Ilmu Pengetahuan Alam untuk SD.', 'ti-atom', '#8b5cf6', 1, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `description` = VALUES(`description`);

SET @deck := (SELECT `id` FROM `learn_flashcard_decks` WHERE `code` = 'ipa_dasar' LIMIT 1);

INSERT INTO `learn_flashcard_cards` (`deck_id`, `front`, `back`, `hint`, `sort_order`) VALUES
  (@deck, 'Fotosintesis', 'Proses tumbuhan membuat makanan sendiri menggunakan cahaya matahari, air, dan karbon dioksida.', 'Terjadi di daun', 1),
  (@deck, 'Metamorfosis', 'Perubahan bentuk tubuh hewan dari lahir hingga dewasa, misalnya kupu-kupu.', 'Contoh: ulat → kupu-kupu', 2),
  (@deck, 'Ekosistem',    'Hubungan timbal balik antara makhluk hidup dengan lingkungannya.', NULL, 3),
  (@deck, 'Gaya Gravitasi','Gaya tarik bumi yang membuat benda jatuh ke bawah.', 'Membuat benda jatuh', 4),
  (@deck, 'Kondensasi',   'Perubahan wujud benda dari gas menjadi cair (mengembun).', 'Uap air → titik air', 5)
ON DUPLICATE KEY UPDATE `back` = VALUES(`back`), `hint` = VALUES(`hint`), `sort_order` = VALUES(`sort_order`);

-- ────────────────────────────────────────────────────────────
-- 6. Registrasi halaman + menu sidebar (grup Pembelajaran)
-- ────────────────────────────────────────────────────────────
INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`, `is_active`)
VALUES ('learn_flashcards.index', 'Pembelajaran', 'Flashcard', 'learn-flashcards', 'Kelola deck & kartu flashcard belajar mandiri', 1)
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`), `title` = VALUES(`title`),
  `route`  = VALUES(`route`),  `description` = VALUES(`description`), `is_active` = VALUES(`is_active`);

SET @grp := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'learn.group' LIMIT 1);

INSERT INTO `sys_menu`
  (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'learn_flashcards.index'), 'MAIN', 'learn.flashcards', 'Flashcard', 'ti ti-cards', 'learn-flashcards', 8, 1, 1, 0)
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
WHERE p.`code` = 'learn_flashcards.index'
ON DUPLICATE KEY UPDATE
  `can_view` = 1, `can_create` = 1, `can_edit` = 1, `can_delete` = 1, `can_export` = 1;
