INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('system.pages.index', 'system', 'Registry Halaman', 'rbac/pages', 'Kelola registry halaman yang menjadi dasar permission dan sidebar.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

UPDATE `sys_page`
SET `route` = 'rbac/users'
WHERE `code` = 'auth.users.index';

UPDATE `sys_page`
SET `route` = 'rbac/roles'
WHERE `code` = 'auth.roles.index';

UPDATE `sys_page`
SET `route` = 'rbac/sidebar'
WHERE `code` = 'system.sidebar.manage';

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 1, 1, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'system.pages.index'
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_delete` = 1,
  `can_export` = 1,
  `can_approve` = 1;

UPDATE `sys_menu`
SET `title` = 'Pengaturan Akses',
    `icon` = 'ti ti-shield-lock',
    `url` = NULL,
    `sort_order` = 900,
    `is_visible` = 1,
    `is_active` = 1,
    `is_locked` = 1
WHERE `menu_key` = 'system';

UPDATE `sys_menu`
SET `url` = 'rbac/users',
    `icon` = 'ti ti-users',
    `title` = 'User',
    `sort_order` = 910,
    `is_visible` = 1,
    `is_active` = 1
WHERE `menu_key` = 'system.users';

UPDATE `sys_menu`
SET `url` = 'rbac/roles',
    `icon` = 'ti ti-key',
    `title` = 'Role & Permission',
    `sort_order` = 920,
    `is_visible` = 1,
    `is_active` = 1
WHERE `menu_key` = 'system.roles';

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.`id`, p.`id`, 'MAIN', 'system.pages', 'Registry Halaman', 'ti ti-file-settings', 'rbac/pages', 925, 1, 1, 0
FROM `sys_menu` parent
JOIN `sys_page` p ON p.`code` = 'system.pages.index'
WHERE parent.`menu_key` = 'system'
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`),
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

UPDATE `sys_menu`
SET `url` = 'rbac/sidebar',
    `icon` = 'ti ti-layout-sidebar',
    `title` = 'Sidebar',
    `sort_order` = 930,
    `is_visible` = 1,
    `is_active` = 1
WHERE `menu_key` = 'system.sidebar';
