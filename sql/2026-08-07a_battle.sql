-- ============================================================
-- Modul Pembelajaran — Mode Battle (Adu Cepat 2 Pemain)
-- Pustaka Digital Rembang — 2026-08-07
-- ------------------------------------------------------------
-- Fase 5e. Dua pemain menjawab soal yang sama, sinkron via
-- AJAX polling (tanpa WebSocket daemon). Skor & pemenang
-- ditentukan server. Soal dari pool DB (CRUD admin).
-- Semua statement idempotent (aman dijalankan berulang).
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Pool soal battle (pilihan ganda) — DB-driven, CRUD admin
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_battle_questions` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `question`       VARCHAR(500) NOT NULL,
  `option_a`       VARCHAR(300) NOT NULL,
  `option_b`       VARCHAR(300) NOT NULL,
  `option_c`       VARCHAR(300) NULL,
  `option_d`       VARCHAR(300) NULL,
  `correct_option` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=A,1=B,2=C,3=D',
  `category`       VARCHAR(80)  NULL,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bq_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 2. Ruang battle (room)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_battle_rooms` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`           VARCHAR(12)  NOT NULL,
  `status`         ENUM('waiting','playing','finished','abandoned') NOT NULL DEFAULT 'waiting',
  `question_count` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `question_ids`   TEXT         NULL COMMENT 'JSON array id soal (dibekukan saat mulai)',
  `host_user_id`   INT UNSIGNED NOT NULL,
  `guest_user_id`  INT UNSIGNED NULL,
  `host_name`      VARCHAR(120) NULL,
  `guest_name`     VARCHAR(120) NULL,
  `host_score`     INT UNSIGNED NOT NULL DEFAULT 0,
  `guest_score`    INT UNSIGNED NOT NULL DEFAULT 0,
  `host_progress`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `guest_progress` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `host_finished`  TINYINT(1)   NOT NULL DEFAULT 0,
  `guest_finished` TINYINT(1)   NOT NULL DEFAULT 0,
  `winner_user_id` INT UNSIGNED NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at`     DATETIME     NULL,
  `finished_at`    DATETIME     NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_room_code` (`code`),
  KEY `idx_room_status` (`status`),
  KEY `idx_room_host` (`host_user_id`),
  KEY `idx_room_guest` (`guest_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 3. Aturan poin (menang + ikut main; dedup per room)
-- ────────────────────────────────────────────────────────────
INSERT INTO `learn_point_rules` (`action_code`, `label`, `description`, `points`, `cooldown_hours`, `is_active`) VALUES
  ('battle.play', 'Ikut Battle', 'Menyelesaikan satu ronde Mode Battle', 5, 0, 1),
  ('battle.win',  'Menang Battle','Memenangkan satu ronde Mode Battle',   20, 0, 1)
ON DUPLICATE KEY UPDATE
  `label` = VALUES(`label`), `description` = VALUES(`description`), `is_active` = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 4. Contoh soal battle (starter — bisa diedit/hapus via CRUD)
--    Idempoten: hanya seed bila pool masih kosong.
-- ────────────────────────────────────────────────────────────
INSERT INTO `learn_battle_questions` (`question`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `category`)
SELECT * FROM (
            SELECT 'Apa ibu kota Indonesia?' AS q, 'Bandung' a, 'Jakarta' b, 'Surabaya' c, 'Medan' d, 1 co, 'Umum' cat
  UNION ALL SELECT 'Berapa hasil 7 x 8?', '54', '56', '58', '64', 1, 'Matematika'
  UNION ALL SELECT 'Planet terdekat dengan Matahari?', 'Venus', 'Bumi', 'Merkurius', 'Mars', 2, 'IPA'
  UNION ALL SELECT 'Hewan yang mengalami metamorfosis sempurna?', 'Ayam', 'Kupu-kupu', 'Kucing', 'Ular', 1, 'IPA'
  UNION ALL SELECT 'Berapa jumlah sisi pada segitiga?', '2', '3', '4', '5', 1, 'Matematika'
  UNION ALL SELECT 'Warna bendera Indonesia?', 'Merah-Putih', 'Putih-Merah', 'Merah-Biru', 'Putih-Biru', 0, 'Umum'
  UNION ALL SELECT 'Air membeku menjadi es pada suhu?', '0 derajat C', '10 derajat C', '100 derajat C', '-10 derajat C', 0, 'IPA'
  UNION ALL SELECT 'Berapa hasil 12 + 15?', '25', '26', '27', '28', 2, 'Matematika'
  UNION ALL SELECT 'Alat pernapasan ikan?', 'Paru-paru', 'Insang', 'Kulit', 'Trakea', 1, 'IPA'
  UNION ALL SELECT 'Siapa penulis proklamasi kemerdekaan Indonesia?', 'Soekarno-Hatta', 'Soeharto', 'Ki Hajar Dewantara', 'RA Kartini', 0, 'Umum'
) seed
WHERE NOT EXISTS (SELECT 1 FROM `learn_battle_questions` LIMIT 1);

-- ────────────────────────────────────────────────────────────
-- 5. Registrasi halaman + menu sidebar (grup Pembelajaran)
-- ────────────────────────────────────────────────────────────
INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`, `is_active`)
VALUES ('learn_battle.index', 'Pembelajaran', 'Mode Battle', 'learn-battle', 'Kelola pool soal Mode Battle & pantau ronde', 1)
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`), `title` = VALUES(`title`),
  `route`  = VALUES(`route`),  `description` = VALUES(`description`), `is_active` = VALUES(`is_active`);

SET @grp := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'learn.group' LIMIT 1);

INSERT INTO `sys_menu`
  (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'learn_battle.index'), 'MAIN', 'learn.battle', 'Mode Battle', 'ti ti-swords', 'learn-battle', 11, 1, 1, 0)
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`), `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`), `is_visible` = VALUES(`is_visible`), `is_active` = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 6. Hak akses default role ADMIN (role_id = 2)
-- ────────────────────────────────────────────────────────────
INSERT INTO `auth_role_permission`
  (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT 2, p.`id`, 1, 1, 1, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'learn_battle.index'
ON DUPLICATE KEY UPDATE
  `can_view` = 1, `can_create` = 1, `can_edit` = 1, `can_delete` = 1, `can_export` = 1;
