CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned NOT NULL DEFAULT 0,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `email` varchar(180) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(180) NOT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `member_source_id` bigint(20) unsigned DEFAULT NULL,
  `library_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `force_password_change` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_auth_user_username` (`username`),
  UNIQUE KEY `uq_auth_user_email` (`email`),
  KEY `idx_auth_user_library_id` (`library_id`),
  KEY `idx_auth_user_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(60) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `level` smallint(5) unsigned NOT NULL DEFAULT 100,
  `scope_type` enum('global','library','self') NOT NULL DEFAULT 'self',
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_auth_role_code` (`code`),
  KEY `idx_auth_role_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_user_role` (
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `role_id`),
  KEY `idx_auth_user_role_role_id` (`role_id`),
  CONSTRAINT `fk_auth_user_role_user` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_auth_user_role_role` FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_session_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event_type` enum('login_success','login_failed','logout','password_changed','permission_denied') NOT NULL,
  `username_attempt` varchar(180) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `meta_json` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_auth_session_log_user_id` (`user_id`),
  KEY `idx_auth_session_log_event_type` (`event_type`),
  KEY `idx_auth_session_log_created_at` (`created_at`),
  CONSTRAINT `fk_auth_session_log_user` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_page` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(120) NOT NULL,
  `module` varchar(80) NOT NULL,
  `title` varchar(160) NOT NULL,
  `route` varchar(180) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sys_page_code` (`code`),
  KEY `idx_sys_page_module` (`module`),
  KEY `idx_sys_page_route` (`route`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_role_permission` (
  `role_id` int(10) unsigned NOT NULL,
  `page_id` int(10) unsigned NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_create` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `can_export` tinyint(1) NOT NULL DEFAULT 0,
  `can_approve` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`role_id`, `page_id`),
  KEY `idx_auth_role_permission_page_id` (`page_id`),
  CONSTRAINT `fk_auth_role_permission_role` FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_auth_role_permission_page` FOREIGN KEY (`page_id`) REFERENCES `sys_page` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `auth_user_permission_override` (
  `user_id` bigint(20) unsigned NOT NULL,
  `page_id` int(10) unsigned NOT NULL,
  `can_view` tinyint(1) DEFAULT NULL,
  `can_create` tinyint(1) DEFAULT NULL,
  `can_edit` tinyint(1) DEFAULT NULL,
  `can_delete` tinyint(1) DEFAULT NULL,
  `can_export` tinyint(1) DEFAULT NULL,
  `can_approve` tinyint(1) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `page_id`),
  KEY `idx_auth_user_permission_override_page_id` (`page_id`),
  CONSTRAINT `fk_auth_user_permission_override_user` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_auth_user_permission_override_page` FOREIGN KEY (`page_id`) REFERENCES `sys_page` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `page_id` int(10) unsigned DEFAULT NULL,
  `menu_area` varchar(40) NOT NULL DEFAULT 'MAIN',
  `menu_key` varchar(120) NOT NULL,
  `title` varchar(120) NOT NULL,
  `icon` varchar(80) DEFAULT NULL,
  `url` varchar(180) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 100,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sys_menu_key` (`menu_key`),
  KEY `idx_sys_menu_parent_id` (`parent_id`),
  KEY `idx_sys_menu_page_id` (`page_id`),
  KEY `idx_sys_menu_area_order` (`menu_area`, `sort_order`),
  CONSTRAINT `fk_sys_menu_parent` FOREIGN KEY (`parent_id`) REFERENCES `sys_menu` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sys_menu_page` FOREIGN KEY (`page_id`) REFERENCES `sys_page` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sys_sidebar_favorite` (
  `user_id` bigint(20) unsigned NOT NULL,
  `menu_id` int(10) unsigned NOT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 100,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `menu_id`),
  KEY `idx_sys_sidebar_favorite_menu_id` (`menu_id`),
  CONSTRAINT `fk_sys_sidebar_favorite_user` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sys_sidebar_favorite_menu` FOREIGN KEY (`menu_id`) REFERENCES `sys_menu` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event_code` varchar(120) NOT NULL,
  `entity_type` varchar(120) DEFAULT NULL,
  `entity_id` varchar(80) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `old_json` text DEFAULT NULL,
  `new_json` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_log_user_id` (`user_id`),
  KEY `idx_audit_log_event_code` (`event_code`),
  KEY `idx_audit_log_entity` (`entity_type`, `entity_id`),
  KEY `idx_audit_log_created_at` (`created_at`),
  CONSTRAINT `fk_audit_log_user` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `auth_role` (`code`, `name`, `description`, `level`, `scope_type`, `is_system`, `is_active`) VALUES
('SUPERADMIN', 'Superadmin', 'Akses penuh seluruh sistem dan konfigurasi.', 1, 'global', 1, 1),
('ADMIN', 'Admin', 'Pengelola operasional perpustakaan atau unit yang ditugaskan.', 20, 'library', 1, 1),
('USER', 'User/Pemustaka', 'Pemustaka/member aplikasi digital.', 100, 'self', 1, 1)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `level` = VALUES(`level`),
  `scope_type` = VALUES(`scope_type`),
  `is_system` = VALUES(`is_system`),
  `is_active` = VALUES(`is_active`);

INSERT INTO `auth_user` (`username`, `email`, `password_hash`, `full_name`, `status`, `force_password_change`) VALUES
('superadmin', 'superadmin@pustaka.local', '$2y$10$TWH9LG5tA9N1Ap0MNTUCtOcimEFZTR1LqQFjF8ePID/CIc7V9AA.e', 'Superadmin Pustaka', 'active', 1)
ON DUPLICATE KEY UPDATE
  `email` = VALUES(`email`),
  `full_name` = VALUES(`full_name`),
  `status` = VALUES(`status`);

INSERT IGNORE INTO `auth_user_role` (`user_id`, `role_id`)
SELECT u.`id`, r.`id`
FROM `auth_user` u
JOIN `auth_role` r ON r.`code` = 'SUPERADMIN'
WHERE u.`username` = 'superadmin';

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('dashboard.index', 'dashboard', 'Dashboard', 'welcome/index', 'Ringkasan aplikasi dan status migrasi.'),
('libraries.index', 'libraries', 'Perpustakaan GIS', 'libraries', 'Direktori perpustakaan terintegrasi berbasis GIS.'),
('catalog.index', 'catalog', 'Katalog Buku', 'catalog', 'Manajemen katalog dan koleksi buku.'),
('members.index', 'members', 'Membership Digital', 'members', 'Manajemen akun/member dan kartu digital.'),
('gis.index', 'gis', 'Peta GIS', 'gis', 'Peta seluruh titik perpustakaan dan pojok baca.'),
('reading_points.index', 'reading_points', 'Pojok Baca Digital', 'reading-points', 'Lokasi GPS, token, dan kuota baca digital.'),
('events.index', 'events', 'Event Literasi', 'events', 'Agenda dan kegiatan literasi.'),
('auth.users.index', 'auth', 'Manajemen User', 'users', 'Kelola user, status, dan penugasan role.'),
('auth.roles.index', 'auth', 'Role dan Hak Akses', 'roles', 'Kelola role dan matriks permission.'),
('system.sidebar.manage', 'system', 'Manajemen Sidebar', 'sidebar/manage', 'Kelola susunan, ikon, dan akses menu sidebar.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 1, 1, 1
FROM `auth_role` r
JOIN `sys_page` p
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_delete` = 1,
  `can_export` = 1,
  `can_approve` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 0, 1, 0
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` IN (
  'dashboard.index',
  'libraries.index',
  'catalog.index',
  'members.index',
  'gis.index',
  'reading_points.index',
  'events.index'
)
WHERE r.`code` = 'ADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_delete` = 0,
  `can_export` = 1,
  `can_approve` = 0;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 0, 0, 0, 0, 0
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` IN (
  'dashboard.index',
  'catalog.index',
  'members.index',
  'events.index'
)
WHERE r.`code` = 'USER'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 0,
  `can_edit` = 0,
  `can_delete` = 0,
  `can_export` = 0,
  `can_approve` = 0;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT NULL, p.`id`, 'MAIN', 'dashboard', 'Dashboard', 'ti ti-layout-dashboard', 'welcome/index', 10, 1, 1, 1
FROM `sys_page` p WHERE p.`code` = 'dashboard.index'
ON DUPLICATE KEY UPDATE `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT NULL, p.`id`, 'MAIN', 'libraries.gis', 'Perpustakaan GIS', 'ti ti-map-2', 'libraries', 20, 1, 1, 0
FROM `sys_page` p WHERE p.`code` = 'libraries.index'
ON DUPLICATE KEY UPDATE `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT NULL, p.`id`, 'MAIN', 'catalog', 'Katalog', 'ti ti-books', 'catalog', 30, 1, 1, 0
FROM `sys_page` p WHERE p.`code` = 'catalog.index'
ON DUPLICATE KEY UPDATE `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT NULL, p.`id`, 'MAIN', 'members', 'Membership', 'ti ti-id-badge-2', 'members', 40, 1, 1, 0
FROM `sys_page` p WHERE p.`code` = 'members.index'
ON DUPLICATE KEY UPDATE `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT NULL, p.`id`, 'MAIN', 'reading-points', 'Pojok Baca', 'ti ti-current-location', 'reading-points', 50, 1, 1, 0
FROM `sys_page` p WHERE p.`code` = 'reading_points.index'
ON DUPLICATE KEY UPDATE `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT NULL, p.`id`, 'MAIN', 'events', 'Event', 'ti ti-calendar-event', 'events', 60, 1, 1, 0
FROM `sys_page` p WHERE p.`code` = 'events.index'
ON DUPLICATE KEY UPDATE `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`) VALUES
(NULL, NULL, 'MAIN', 'system', 'Sistem', 'ti ti-settings', NULL, 900, 1, 1, 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `icon` = VALUES(`icon`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.`id`, p.`id`, 'MAIN', 'system.users', 'Manajemen User', 'ti ti-users', 'users', 910, 1, 1, 0
FROM `sys_menu` parent
JOIN `sys_page` p ON p.`code` = 'auth.users.index'
WHERE parent.`menu_key` = 'system'
ON DUPLICATE KEY UPDATE `parent_id` = VALUES(`parent_id`), `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.`id`, p.`id`, 'MAIN', 'system.roles', 'Role & Akses', 'ti ti-shield-lock', 'roles', 920, 1, 1, 0
FROM `sys_menu` parent
JOIN `sys_page` p ON p.`code` = 'auth.roles.index'
WHERE parent.`menu_key` = 'system'
ON DUPLICATE KEY UPDATE `parent_id` = VALUES(`parent_id`), `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.`id`, p.`id`, 'MAIN', 'system.sidebar', 'Manajemen Sidebar', 'ti ti-layout-sidebar', 'sidebar/manage', 930, 1, 1, 0
FROM `sys_menu` parent
JOIN `sys_page` p ON p.`code` = 'system.sidebar.manage'
WHERE parent.`menu_key` = 'system'
ON DUPLICATE KEY UPDATE `parent_id` = VALUES(`parent_id`), `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `sort_order` = VALUES(`sort_order`), `is_visible` = 1, `is_active` = 1;
