INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('transactions.index', 'transactions', 'Data Transaksi', 'transactions', 'Daftar kunjungan tamu, hak pinjam, transaksi pinjam, dan detail item hasil sinkronisasi INLISLite.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, IF(r.`code` = 'SUPERADMIN', 1, 0), IF(r.`code` = 'SUPERADMIN', 1, 0), 0, 1, IF(r.`code` = 'SUPERADMIN', 1, 0)
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'transactions.index'
WHERE r.`code` IN ('SUPERADMIN', 'ADMIN')
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = VALUES(`can_create`),
  `can_edit` = VALUES(`can_edit`),
  `can_export` = 1,
  `can_approve` = VALUES(`can_approve`);

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES (NULL, NULL, 'MAIN', 'transactions', 'Transaksi Harian', 'ti ti-report-analytics', NULL, 45, 1, 1, 0)
ON DUPLICATE KEY UPDATE
  `id` = LAST_INSERT_ID(`id`),
  `parent_id` = NULL,
  `page_id` = NULL,
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = NULL,
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

SET @transactions_menu_id := LAST_INSERT_ID();

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @transactions_menu_id, p.`id`, 'MAIN', 'transactions.index', 'Data Transaksi', 'ti ti-table', 'transactions', 10, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'transactions.index'
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`),
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

UPDATE `sys_menu` m
JOIN `sys_menu` parent ON parent.`menu_key` = 'transactions'
SET m.`parent_id` = parent.`id`,
    m.`title` = 'Sinkronisasi',
    m.`icon` = 'ti ti-refresh',
    m.`url` = 'transactions/sync',
    m.`sort_order` = 20,
    m.`is_visible` = 1,
    m.`is_active` = 1
WHERE m.`menu_key` = 'transactions.sync';

UPDATE `sys_menu` asset_menu
JOIN `sys_menu` settings_menu ON settings_menu.`menu_key` = 'system'
SET asset_menu.`parent_id` = settings_menu.`id`,
    asset_menu.`sort_order` = 60,
    asset_menu.`is_visible` = 1,
    asset_menu.`is_active` = 1
WHERE asset_menu.`menu_key` = 'assets.migration';
