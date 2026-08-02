INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('reports.visits', 'reports', 'Laporan Kunjungan', 'reports/visits', 'Analitik kunjungan fisik, online, Pojok Baca, QR, dan akses digital berdasarkan periode.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 0, 0, 0, 1, 0
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'reports.visits'
WHERE r.`code` IN ('SUPERADMIN', 'ADMIN')
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_export` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES
(NULL, NULL, 'MAIN', 'reports', 'Laporan & Analitik', 'ti ti-chart-histogram', NULL, 20, 1, 1, 0),
(NULL, NULL, 'MAIN', 'network_services', 'Jejaring & Agenda', 'ti ti-map-2', NULL, 30, 1, 1, 0),
(NULL, NULL, 'MAIN', 'collection_services', 'Koleksi & Katalog', 'ti ti-books', NULL, 40, 1, 1, 0),
(NULL, NULL, 'MAIN', 'membership_services', 'Keanggotaan', 'ti ti-id-badge-2', NULL, 50, 1, 1, 0)
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

SET @reports_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'reports' LIMIT 1);
SET @network_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'network_services' LIMIT 1);
SET @collection_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'collection_services' LIMIT 1);
SET @membership_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'membership_services' LIMIT 1);
SET @transactions_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'transactions' LIMIT 1);
SET @digital_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'digital_services' LIMIT 1);
SET @master_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'master-data' LIMIT 1);
SET @system_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'system' LIMIT 1);

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @reports_id, p.`id`, 'MAIN', 'reports.visits', 'Laporan Kunjungan', 'ti ti-chart-dots-3', 'reports/visits', 10, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'reports.visits'
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`),
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
VALUES (@transactions_id, NULL, 'MAIN', 'guestbook.monitor', 'Monitor Buku Tamu', 'ti ti-qrcode', 'guestbook/monitor', 15, 1, 1, 0)
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @collection_id, p.`id`, 'MAIN', 'catalog.sync', 'Sinkronisasi Katalog', 'ti ti-refresh', 'catalog/sync', 30, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'catalog.sync'
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`),
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @membership_id, p.`id`, 'MAIN', 'members.sync', 'Sinkronisasi Member', 'ti ti-refresh', 'members/sync', 40, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'members.sync'
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`),
  `page_id` = VALUES(`page_id`),
  `title` = 'Sinkronisasi Member',
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

UPDATE `sys_menu`
SET `title` = 'Dashboard',
    `icon` = 'ti ti-layout-dashboard',
    `parent_id` = NULL,
    `sort_order` = 10,
    `is_visible` = 1,
    `is_active` = 1
WHERE `menu_key` = 'dashboard';

UPDATE `sys_menu`
SET `title` = 'Layanan Harian',
    `icon` = 'ti ti-clipboard-heart',
    `parent_id` = NULL,
    `url` = NULL,
    `page_id` = NULL,
    `sort_order` = 60,
    `is_visible` = 1,
    `is_active` = 1
WHERE `menu_key` = 'transactions';

UPDATE `sys_menu`
SET `title` = 'Layanan Digital',
    `icon` = 'ti ti-device-tablet-star',
    `parent_id` = NULL,
    `url` = NULL,
    `page_id` = NULL,
    `sort_order` = 70,
    `is_visible` = 1,
    `is_active` = 1
WHERE `menu_key` = 'digital_services';

UPDATE `sys_menu`
SET `title` = 'Data Master',
    `icon` = 'ti ti-database-cog',
    `parent_id` = NULL,
    `url` = NULL,
    `page_id` = NULL,
    `sort_order` = 80,
    `is_visible` = 1,
    `is_active` = 1
WHERE `menu_key` = 'master-data';

UPDATE `sys_menu`
SET `title` = 'Pengaturan Sistem',
    `icon` = 'ti ti-settings-shield',
    `parent_id` = NULL,
    `url` = NULL,
    `page_id` = NULL,
    `sort_order` = 90,
    `is_visible` = 1,
    `is_active` = 1
WHERE `menu_key` = 'system';

UPDATE `sys_menu` SET `parent_id` = @network_id, `title` = 'Perpustakaan GIS', `icon` = 'ti ti-map-2', `sort_order` = 10 WHERE `menu_key` = 'libraries.gis';
UPDATE `sys_menu` SET `parent_id` = @network_id, `title` = 'Event Literasi', `icon` = 'ti ti-calendar-event', `sort_order` = 20 WHERE `menu_key` = 'events';

UPDATE `sys_menu` SET `parent_id` = @collection_id, `title` = 'Katalog Buku', `icon` = 'ti ti-books', `sort_order` = 10 WHERE `menu_key` = 'catalog';
UPDATE `sys_menu` SET `parent_id` = @collection_id, `title` = 'Master Buku', `icon` = 'ti ti-category-2', `sort_order` = 20 WHERE `menu_key` = 'catalog.masters';

UPDATE `sys_menu` SET `parent_id` = @membership_id, `title` = 'Data Member', `icon` = 'ti ti-id-badge-2', `sort_order` = 10 WHERE `menu_key` = 'members';
UPDATE `sys_menu` SET `parent_id` = @membership_id, `title` = 'Pendaftaran Online', `icon` = 'ti ti-user-plus', `sort_order` = 20 WHERE `menu_key` = 'members.registrations';
UPDATE `sys_menu` SET `parent_id` = @membership_id, `title` = 'Perpanjangan', `icon` = 'ti ti-id-badge-2', `sort_order` = 30 WHERE `menu_key` = 'members.renewals';

UPDATE `sys_menu` SET `parent_id` = @transactions_id, `title` = 'Aktivitas Layanan', `icon` = 'ti ti-timeline-event', `sort_order` = 10 WHERE `menu_key` = 'transactions.index';
UPDATE `sys_menu` SET `parent_id` = @transactions_id, `title` = 'Sinkronisasi Layanan', `icon` = 'ti ti-refresh', `sort_order` = 20 WHERE `menu_key` = 'transactions.sync';

UPDATE `sys_menu` SET `parent_id` = @collection_id, `title` = 'Request Buku', `icon` = 'ti ti-book-upload', `sort_order` = 25 WHERE `menu_key` = 'catalog.requests';

UPDATE `sys_menu` SET `parent_id` = @digital_id, `title` = 'Reader PDF Aman', `icon` = 'ti ti-file-lock', `sort_order` = 10 WHERE `menu_key` = 'reader.assets';
UPDATE `sys_menu` SET `parent_id` = @digital_id, `title` = 'Pojok Baca', `icon` = 'ti ti-map-pin-star', `sort_order` = 20 WHERE `menu_key` = 'reading-points';
UPDATE `sys_menu` SET `parent_id` = @digital_id, `title` = 'Monitoring Token', `icon` = 'ti ti-ticket', `sort_order` = 30 WHERE `menu_key` = 'reading_tokens.index';

UPDATE `sys_menu` SET `parent_id` = @master_id, `title` = 'Master Wilayah', `icon` = 'ti ti-map-pin-cog', `sort_order` = 10 WHERE `menu_key` = 'master.regions';

UPDATE `sys_menu` SET `parent_id` = @system_id, `title` = 'User & Role', `icon` = 'ti ti-users', `sort_order` = 10 WHERE `menu_key` = 'system.users';
UPDATE `sys_menu` SET `parent_id` = @system_id, `title` = 'Role & Permission', `icon` = 'ti ti-key', `sort_order` = 20 WHERE `menu_key` = 'system.roles';
UPDATE `sys_menu` SET `parent_id` = @system_id, `title` = 'Pengaturan Sidebar', `icon` = 'ti ti-layout-sidebar', `sort_order` = 30 WHERE `menu_key` = 'system.sidebar';
UPDATE `sys_menu` SET `parent_id` = @system_id, `title` = 'Registry Halaman', `icon` = 'ti ti-file-settings', `sort_order` = 40 WHERE `menu_key` = 'system.pages';
UPDATE `sys_menu` SET `parent_id` = @system_id, `title` = 'Audit Log', `icon` = 'ti ti-history', `sort_order` = 50 WHERE `menu_key` = 'system.audit';
UPDATE `sys_menu` SET `parent_id` = @system_id, `title` = 'Migrasi Aset', `icon` = 'ti ti-file-import', `sort_order` = 60 WHERE `menu_key` = 'assets.migration';
