INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('guestbook.settings', 'guestbook', 'Pengaturan Buku Tamu', 'guestbook/settings', 'Pengaturan QR dinamis monitor pelayanan dan default perpustakaan buku tamu.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 0, 1, 0, 0, 0
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'guestbook.settings'
WHERE r.`code` IN ('SUPERADMIN', 'ADMIN')
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_edit` = 1;

SET @transactions_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'transactions' LIMIT 1);

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @transactions_id, p.`id`, 'MAIN', 'guestbook.settings', 'Pengaturan Buku Tamu', 'ti ti-adjustments-horizontal', 'guestbook/settings', 16, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'guestbook.settings'
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`),
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;
