-- ============================================================
-- Modul Pembelajaran — Notifikasi In-App
-- Pustaka Digital Rembang — 2026-08-06
-- ------------------------------------------------------------
-- Fase 5d. Notifikasi untuk member: lencana baru (otomatis),
-- pengumuman kompetisi & broadcast dari admin.
-- Semua statement idempotent (aman dijalankan berulang).
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Notifikasi per-user
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_notifications` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED NOT NULL,
  `type`           VARCHAR(40)  NOT NULL DEFAULT 'system' COMMENT 'badge|competition|announcement|reward|system',
  `title`          VARCHAR(180) NOT NULL,
  `message`        VARCHAR(500) NULL,
  `icon`           VARCHAR(60)  NOT NULL DEFAULT 'ti-bell', 
  `color`          VARCHAR(20)  NOT NULL DEFAULT '#3b82f6',
  `url`            VARCHAR(200) NULL,
  `reference_type` VARCHAR(40)  NULL,
  `reference_id`   INT UNSIGNED NULL,
  `is_read`        TINYINT(1)   NOT NULL DEFAULT 0,
  `read_at`        DATETIME     NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_read` (`user_id`, `is_read`),
  KEY `idx_notif_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 1b. Log broadcast admin (ringkasan tiap pengiriman massal)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_broadcasts` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`           VARCHAR(180) NOT NULL,
  `message`         VARCHAR(500) NULL,
  `type`            VARCHAR(40)  NOT NULL DEFAULT 'announcement',
  `icon`            VARCHAR(60)  NOT NULL DEFAULT 'ti-speakerphone',
  `color`           VARCHAR(20)  NOT NULL DEFAULT '#3b82f6',
  `url`             VARCHAR(200) NULL,
  `recipient_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `sent_by`         INT NULL,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bc_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 2. Registrasi halaman + menu sidebar (grup Pembelajaran)
-- ────────────────────────────────────────────────────────────
INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`, `is_active`)
VALUES ('learn_notifications.index', 'Pembelajaran', 'Notifikasi', 'learn-notifications', 'Kirim pengumuman/broadcast ke member & lihat riwayat', 1)
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`), `title` = VALUES(`title`),
  `route`  = VALUES(`route`),  `description` = VALUES(`description`), `is_active` = VALUES(`is_active`);

SET @grp := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'learn.group' LIMIT 1);

INSERT INTO `sys_menu`
  (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'learn_notifications.index'), 'MAIN', 'learn.notifications', 'Notifikasi', 'ti ti-bell', 'learn-notifications', 10, 1, 1, 0)
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`), `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`), `is_visible` = VALUES(`is_visible`), `is_active` = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 3. Hak akses default role ADMIN (role_id = 2)
-- ────────────────────────────────────────────────────────────
INSERT INTO `auth_role_permission`
  (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT 2, p.`id`, 1, 1, 1, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'learn_notifications.index'
ON DUPLICATE KEY UPDATE
  `can_view` = 1, `can_create` = 1, `can_edit` = 1, `can_delete` = 1, `can_export` = 1;
