-- ============================================================
-- Modul Pembelajaran — Tukar Poin → Token Baca (Reward Exchange)
-- Pustaka Digital Rembang — 2026-08-04
-- ------------------------------------------------------------
-- Prasyarat: 2026-08-04a (learn_point_rules/member_points),
--            sistem reading_tokens (auth/reading module) sudah ada.
-- Semua statement idempotent (aman dijalankan berulang).
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Katalog hadiah (reward) — DB-driven, dikelola admin (CRUD)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_reward_catalog` (
  `id`                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`                VARCHAR(60)  NOT NULL,
  `name`                VARCHAR(150) NOT NULL,
  `description`         VARCHAR(300) NULL,
  `icon`                VARCHAR(60)  NOT NULL DEFAULT 'ti-gift',
  `color`               VARCHAR(20)  NOT NULL DEFAULT '#0ea5e9',
  `cost_points`         INT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'Poin yang dibutuhkan untuk menukar',
  `reward_type`         ENUM('reading_token') NOT NULL DEFAULT 'reading_token',
  `quota_amount`        INT UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Jumlah kuota token yang diberikan',
  `quota_unit`         ENUM('minutes','pages','books') NOT NULL DEFAULT 'minutes',
  `token_validity_days` INT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Masa berlaku token (hari); 0=tanpa kadaluarsa',
  `stock`               INT NULL COMMENT 'NULL=tak terbatas; angka=sisa stok',
  `per_user_limit`      INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=tak terbatas; batas penukaran per user',
  `redeemed_count`      INT UNSIGNED NOT NULL DEFAULT 0,
  `sort_order`          INT UNSIGNED NOT NULL DEFAULT 100,
  `is_active`           TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reward_code` (`code`),
  KEY `idx_reward_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 2. Log penukaran hadiah (audit trail per redeem)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_reward_redemptions` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`          INT UNSIGNED NOT NULL,
  `member_id`        BIGINT UNSIGNED NULL,
  `catalog_id`       INT UNSIGNED NULL,
  `reward_name`      VARCHAR(150) NOT NULL COMMENT 'Snapshot nama hadiah saat ditukar',
  `cost_points`      INT UNSIGNED NOT NULL,
  `quota_amount`     INT UNSIGNED NOT NULL,
  `quota_unit`       ENUM('minutes','pages','books') NOT NULL,
  `reading_token_id` BIGINT UNSIGNED NULL,
  `points_entry_id`  INT UNSIGNED NULL COMMENT 'Baris pengurangan di learn_member_points',
  `token_code`       VARCHAR(80)  NULL,
  `status`           ENUM('completed','revoked') NOT NULL DEFAULT 'completed',
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_redeem_user` (`user_id`),
  KEY `idx_redeem_catalog` (`catalog_id`),
  KEY `idx_redeem_token` (`reading_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 3. Aturan poin "anchor" untuk pengurangan saat menukar
--    (points=0 → tak dipakai award_points; hanya sbagai rule_id
--     & label pada ledger. Baris pengurangan diisi manual -cost.)
-- ────────────────────────────────────────────────────────────
INSERT INTO `learn_point_rules` (`action_code`, `label`, `description`, `points`, `cooldown_hours`, `is_active`)
VALUES ('redeem.reading_token', 'Tukar Token Baca', 'Pengurangan poin saat menukar poin dengan token baca digital', 0, 0, 1)
ON DUPLICATE KEY UPDATE
  `label`       = VALUES(`label`),
  `description` = VALUES(`description`),
  `is_active`   = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 4. Contoh isi katalog (starter — bisa diedit/hapus via CRUD)
-- ────────────────────────────────────────────────────────────
INSERT INTO `learn_reward_catalog`
  (`code`, `name`, `description`, `icon`, `color`, `cost_points`, `quota_amount`, `quota_unit`, `token_validity_days`, `sort_order`, `is_active`)
VALUES
  ('read_30min', 'Baca Digital 30 Menit', 'Token akses baca koleksi digital selama 30 menit.', 'ti-clock',        '#0ea5e9',  80, 30, 'minutes', 30, 1, 1),
  ('read_60min', 'Baca Digital 60 Menit', 'Token akses baca koleksi digital selama 1 jam.',      'ti-clock-hour-1', '#6366f1', 150, 60, 'minutes', 30, 2, 1),
  ('read_1book', 'Buka 1 Buku Penuh',     'Token akses membaca 1 judul buku digital sampai selesai.', 'ti-book',    '#22c55e', 250,  1, 'books',   60, 3, 1)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`);

-- ────────────────────────────────────────────────────────────
-- 5. Registrasi halaman + menu sidebar (grup Pembelajaran)
-- ────────────────────────────────────────────────────────────
INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`, `is_active`)
VALUES ('learn_rewards.index', 'Pembelajaran', 'Tukar Poin', 'learn-rewards', 'Kelola katalog hadiah tukar poin & riwayat penukaran', 1)
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`), `title` = VALUES(`title`),
  `route`  = VALUES(`route`),  `description` = VALUES(`description`), `is_active` = VALUES(`is_active`);

SET @grp := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'learn.group' LIMIT 1);

INSERT INTO `sys_menu`
  (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'learn_rewards.index'), 'MAIN', 'learn.rewards', 'Tukar Poin', 'ti ti-gift', 'learn-rewards', 7, 1, 1, 0)
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
WHERE p.`code` = 'learn_rewards.index'
ON DUPLICATE KEY UPDATE
  `can_view` = 1, `can_create` = 1, `can_edit` = 1, `can_delete` = 1, `can_export` = 1;
