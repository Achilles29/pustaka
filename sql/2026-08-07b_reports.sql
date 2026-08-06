-- ============================================================
-- Modul Pembelajaran — Export Raport Belajar
-- Pustaka Digital Rembang — 2026-08-07
-- ------------------------------------------------------------
-- Fase 5f. Raport progress belajar per member (cetak/PDF via
-- browser). Tanpa tabel baru — hanya registrasi halaman & menu.
-- Semua statement idempotent (aman dijalankan berulang).
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Registrasi halaman + menu sidebar (grup Pembelajaran)
-- ────────────────────────────────────────────────────────────
INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`, `is_active`)
VALUES ('learn_reports.index', 'Pembelajaran', 'Raport Belajar', 'learn-reports', 'Lihat & cetak raport progress belajar member', 1)
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`), `title` = VALUES(`title`),
  `route`  = VALUES(`route`),  `description` = VALUES(`description`), `is_active` = VALUES(`is_active`);

SET @grp := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'learn.group' LIMIT 1);

INSERT INTO `sys_menu`
  (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES
  (@grp, (SELECT `id` FROM `sys_page` WHERE `code` = 'learn_reports.index'), 'MAIN', 'learn.reports', 'Raport Belajar', 'ti ti-report', 'learn-reports', 12, 1, 1, 0)
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`), `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`), `is_visible` = VALUES(`is_visible`), `is_active` = VALUES(`is_active`);

-- ────────────────────────────────────────────────────────────
-- 2. Hak akses default role ADMIN (role_id = 2) — view & export
-- ────────────────────────────────────────────────────────────
INSERT INTO `auth_role_permission`
  (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT 2, p.`id`, 1, 0, 0, 0, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'learn_reports.index'
ON DUPLICATE KEY UPDATE
  `can_view` = 1, `can_export` = 1;
