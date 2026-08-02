ALTER TABLE `members`
  ADD COLUMN IF NOT EXISTS `card_status` enum('active','blocked') NOT NULL DEFAULT 'active' AFTER `member_status_label`,
  ADD COLUMN IF NOT EXISTS `card_block_reason` varchar(255) DEFAULT NULL AFTER `card_status`,
  ADD COLUMN IF NOT EXISTS `card_blocked_at` datetime DEFAULT NULL AFTER `card_block_reason`,
  ADD COLUMN IF NOT EXISTS `card_blocked_by` bigint(20) unsigned DEFAULT NULL AFTER `card_blocked_at`;

CREATE TABLE IF NOT EXISTS `book_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_code` varchar(40) NOT NULL,
  `book_id` bigint(20) unsigned NOT NULL,
  `book_item_id` bigint(20) unsigned DEFAULT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `request_type` enum('reservation','request') NOT NULL DEFAULT 'request',
  `requester_name` varchar(180) NOT NULL,
  `requester_email` varchar(180) DEFAULT NULL,
  `requester_phone` varchar(80) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','fulfilled','cancelled') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_book_requests_code` (`request_code`),
  KEY `idx_book_requests_book` (`book_id`),
  KEY `idx_book_requests_item` (`book_item_id`),
  KEY `idx_book_requests_member` (`member_id`),
  KEY `idx_book_requests_status` (`status`, `created_at`),
  CONSTRAINT `fk_book_requests_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_book_requests_item` FOREIGN KEY (`book_item_id`) REFERENCES `book_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_book_requests_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_book_requests_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `membership_renewal_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_code` varchar(40) NOT NULL,
  `member_id` bigint(20) unsigned NOT NULL,
  `current_expired_at` datetime DEFAULT NULL,
  `requested_months` smallint(5) unsigned NOT NULL DEFAULT 12,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_membership_renewal_code` (`request_code`),
  KEY `idx_membership_renewal_member` (`member_id`),
  KEY `idx_membership_renewal_status` (`status`, `created_at`),
  CONSTRAINT `fk_membership_renewal_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_membership_renewal_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reading_points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `library_id` bigint(20) unsigned DEFAULT NULL,
  `partner_name` varchar(180) DEFAULT NULL,
  `name` varchar(180) NOT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `radius_meters` int(10) unsigned NOT NULL DEFAULT 100,
  `daily_quota` int(10) unsigned NOT NULL DEFAULT 0,
  `quota_unit` enum('minutes','pages','books') NOT NULL DEFAULT 'minutes',
  `opening_hours` varchar(255) DEFAULT NULL,
  `status` enum('draft','active','inactive') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reading_points_library` (`library_id`),
  KEY `idx_reading_points_status` (`status`),
  CONSTRAINT `fk_reading_points_library` FOREIGN KEY (`library_id`) REFERENCES `libraries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reading_points_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reading_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` bigint(20) unsigned NOT NULL,
  `reading_point_id` bigint(20) unsigned DEFAULT NULL,
  `token` varchar(80) NOT NULL,
  `quota_total` int(10) unsigned NOT NULL DEFAULT 0,
  `quota_used` int(10) unsigned NOT NULL DEFAULT 0,
  `quota_unit` enum('minutes','pages','books') NOT NULL DEFAULT 'minutes',
  `issued_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  `status` enum('active','used','expired','revoked') NOT NULL DEFAULT 'active',
  `issued_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_reading_tokens_token` (`token`),
  KEY `idx_reading_tokens_member` (`member_id`),
  KEY `idx_reading_tokens_point` (`reading_point_id`),
  KEY `idx_reading_tokens_status` (`status`, `expires_at`),
  CONSTRAINT `fk_reading_tokens_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reading_tokens_point` FOREIGN KEY (`reading_point_id`) REFERENCES `reading_points` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reading_tokens_issued_by` FOREIGN KEY (`issued_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reading_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` bigint(20) unsigned NOT NULL,
  `book_id` bigint(20) unsigned NOT NULL,
  `digital_asset_id` bigint(20) unsigned DEFAULT NULL,
  `reading_point_id` bigint(20) unsigned DEFAULT NULL,
  `reading_token_id` bigint(20) unsigned DEFAULT NULL,
  `started_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` datetime DEFAULT NULL,
  `last_page` int(10) unsigned NOT NULL DEFAULT 1,
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `access_policy` enum('online_only','download_allowed','location_only','member_only','internal') NOT NULL DEFAULT 'online_only',
  `status` enum('active','finished','expired','blocked') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `idx_reading_sessions_member` (`member_id`),
  KEY `idx_reading_sessions_book` (`book_id`),
  KEY `idx_reading_sessions_asset` (`digital_asset_id`),
  KEY `idx_reading_sessions_point` (`reading_point_id`),
  CONSTRAINT `fk_reading_sessions_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reading_sessions_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reading_sessions_asset` FOREIGN KEY (`digital_asset_id`) REFERENCES `digital_assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reading_sessions_point` FOREIGN KEY (`reading_point_id`) REFERENCES `reading_points` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_reading_sessions_token` FOREIGN KEY (`reading_token_id`) REFERENCES `reading_tokens` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `literacy_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `library_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(220) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` varchar(120) DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `location_name` varchar(220) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `quota` int(10) unsigned DEFAULT NULL,
  `registration_required` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('draft','published','closed','cancelled') NOT NULL DEFAULT 'draft',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_literacy_events_library` (`library_id`),
  KEY `idx_literacy_events_status` (`status`, `starts_at`),
  CONSTRAINT `fk_literacy_events_library` FOREIGN KEY (`library_id`) REFERENCES `libraries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_literacy_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_registrations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `participant_name` varchar(180) NOT NULL,
  `participant_phone` varchar(80) DEFAULT NULL,
  `participant_email` varchar(180) DEFAULT NULL,
  `attendance_token` varchar(80) DEFAULT NULL,
  `status` enum('registered','attended','cancelled') NOT NULL DEFAULT 'registered',
  `registered_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attended_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_event_registrations_event` (`event_id`),
  KEY `idx_event_registrations_member` (`member_id`),
  CONSTRAINT `fk_event_registrations_event` FOREIGN KEY (`event_id`) REFERENCES `literacy_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_registrations_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('catalog.requests', 'catalog', 'Request Buku', 'catalog/requests', 'Daftar reservasi dan request buku dari katalog publik.'),
('members.renewals', 'members', 'Perpanjangan Membership', 'members/renewals', 'Pengajuan perpanjangan masa berlaku membership.'),
('reader.assets', 'reader', 'Reader PDF Aman', 'reader/assets', 'Fondasi manajemen aset PDF aman, akses online, dan audit baca.'),
('reading_points.index', 'reading_points', 'Pojok Baca Digital', 'reading-points', 'Titik GPS, radius, token, dan kuota pojok baca digital.'),
('events.index', 'events', 'Event Literasi', 'events', 'Agenda literasi, pendaftaran peserta, dan QR attendance.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 1, 1, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` IN ('catalog.requests', 'members.renewals', 'reader.assets', 'reading_points.index', 'events.index')
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_delete` = 1,
  `can_export` = 1,
  `can_approve` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT NULL, NULL, 'MAIN', 'digital_services', 'Layanan Digital', 'ti ti-device-tablet-star', NULL, 48, 1, 1, 0
WHERE NOT EXISTS (SELECT 1 FROM `sys_menu` WHERE `menu_key` = 'digital_services');

SET @digital_services_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'digital_services' LIMIT 1);

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @digital_services_id, p.`id`, 'MAIN', 'catalog.requests', 'Request Buku', 'ti ti-book-upload', 'catalog/requests', 10, 1, 1, 0
FROM `sys_page` p WHERE p.`code` = 'catalog.requests'
ON DUPLICATE KEY UPDATE `parent_id` = @digital_services_id, `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @digital_services_id, p.`id`, 'MAIN', 'members.renewals', 'Perpanjangan', 'ti ti-id-badge-2', 'members/renewals', 20, 1, 1, 0
FROM `sys_page` p WHERE p.`code` = 'members.renewals'
ON DUPLICATE KEY UPDATE `parent_id` = @digital_services_id, `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `is_visible` = 1, `is_active` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @digital_services_id, p.`id`, 'MAIN', 'reader.assets', 'Reader PDF Aman', 'ti ti-file-lock', 'reader/assets', 30, 1, 1, 0
FROM `sys_page` p WHERE p.`code` = 'reader.assets'
ON DUPLICATE KEY UPDATE `parent_id` = @digital_services_id, `page_id` = VALUES(`page_id`), `title` = VALUES(`title`), `icon` = VALUES(`icon`), `url` = VALUES(`url`), `is_visible` = 1, `is_active` = 1;

UPDATE `sys_menu`
SET `parent_id` = @digital_services_id,
    `title` = 'Pojok Baca',
    `icon` = 'ti ti-map-pin-star',
    `sort_order` = 40,
    `is_visible` = 1,
    `is_active` = 1
WHERE `menu_key` IN ('reading_points.index', 'reading-points');

UPDATE `sys_menu`
SET `parent_id` = @digital_services_id,
    `title` = 'Event Literasi',
    `icon` = 'ti ti-calendar-event',
    `sort_order` = 50,
    `is_visible` = 1,
    `is_active` = 1
WHERE `menu_key` IN ('events.index', 'events');

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @digital_services_id, p.`id`, 'MAIN', 'reading_points.index', 'Pojok Baca', 'ti ti-map-pin-star', 'reading-points', 40, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'reading_points.index'
  AND NOT EXISTS (SELECT 1 FROM `sys_menu` WHERE `menu_key` IN ('reading_points.index', 'reading-points'));

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @digital_services_id, p.`id`, 'MAIN', 'events.index', 'Event Literasi', 'ti ti-calendar-event', 'events', 50, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'events.index'
  AND NOT EXISTS (SELECT 1 FROM `sys_menu` WHERE `menu_key` IN ('events.index', 'events'));
