INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`, `is_active`)
SELECT 'events.qr', 'events', 'QR Event & Pendaftaran', 'events/qr', 'Generator QR publik untuk pengumuman event dan pendaftaran masyarakat.', 1
WHERE NOT EXISTS (
  SELECT 1 FROM `sys_page` WHERE `code` = 'events.qr'
);

UPDATE `sys_page`
SET `title` = 'QR Event & Pendaftaran',
    `description` = 'Generator QR publik untuk pengumuman event dan pendaftaran masyarakat.',
    `route` = 'events/qr',
    `is_active` = 1
WHERE `code` = 'events.qr';

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT rp.`role_id`, qr.`id`, rp.`can_view`, rp.`can_create`, rp.`can_edit`, rp.`can_delete`, rp.`can_export`, rp.`can_approve`
FROM `auth_role_permission` rp
JOIN `sys_page` src ON src.`id` = rp.`page_id` AND src.`code` = 'events.index'
JOIN `sys_page` qr ON qr.`code` = 'events.qr'
WHERE NOT EXISTS (
  SELECT 1
  FROM `auth_role_permission` existing
  WHERE existing.`role_id` = rp.`role_id`
    AND existing.`page_id` = qr.`id`
);

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 0, 1, 0
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'events.qr'
WHERE r.`code` IN ('SUPERADMIN', 'ADMIN')
  AND NOT EXISTS (
    SELECT 1
    FROM `auth_role_permission` arp
    WHERE arp.`role_id` = r.`id`
      AND arp.`page_id` = p.`id`
  );

DELETE arp
FROM `auth_role_permission` arp
JOIN `auth_role` r ON r.`id` = arp.`role_id`
JOIN `sys_page` p ON p.`id` = arp.`page_id`
WHERE p.`code` = 'events.qr'
  AND r.`code` = 'USER';

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.`id`, page.`id`, 'MAIN', 'events-qr', 'QR Event', 'ti ti-qrcode', 'events/qr', 21, 1, 1, 0
FROM `sys_menu` parent
JOIN `sys_page` page ON page.`code` = 'events.qr'
WHERE parent.`menu_key` = 'network_services'
  AND NOT EXISTS (
    SELECT 1 FROM `sys_menu` WHERE `menu_key` = 'events-qr'
  );

UPDATE `sys_menu` menu
JOIN `sys_page` page ON page.`code` = 'events.qr'
SET menu.`title` = 'QR Event',
    menu.`page_id` = page.`id`,
    menu.`icon` = 'ti ti-qrcode',
    menu.`url` = 'events/qr',
    menu.`sort_order` = 21,
    menu.`is_visible` = 1,
    menu.`is_active` = 1
WHERE menu.`menu_key` = 'events-qr';
