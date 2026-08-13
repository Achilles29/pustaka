-- Menu memakai hak akses laporan kunjungan yang sudah ada.
INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.id, page.id, 'MAIN', 'reports.patron_insights', 'Sorotan Pemustaka', 'ti ti-users-group', 'reports/pemustaka', 15, 1, 1, 0
FROM `sys_menu` parent
JOIN `sys_page` page ON page.code = 'reports.visits'
WHERE parent.menu_key = 'reports'
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`), `page_id` = VALUES(`page_id`), `title` = VALUES(`title`),
  `icon` = VALUES(`icon`), `url` = VALUES(`url`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;
