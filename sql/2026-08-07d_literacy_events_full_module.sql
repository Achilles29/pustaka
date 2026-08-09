CREATE TABLE IF NOT EXISTS `event_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `color` varchar(20) NOT NULL DEFAULT '#005baa',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `event_categories` (`name`, `slug`, `description`, `color`, `sort_order`) VALUES
('Bedah Buku', 'bedah-buku', 'Diskusi dan apresiasi karya buku.', '#005baa', 10),
('Storytelling Anak', 'storytelling-anak', 'Kegiatan cerita dan literasi anak.', '#0f8b8d', 20),
('Literasi Digital', 'literasi-digital', 'Kelas pemanfaatan teknologi dan informasi.', '#2563eb', 30),
('Pelatihan Menulis', 'pelatihan-menulis', 'Workshop menulis kreatif, ilmiah, atau jurnalistik.', '#7c3aed', 40),
('Sejarah Lokal', 'sejarah-lokal', 'Arsip, budaya, dan sejarah Kabupaten Rembang.', '#b45309', 50),
('Lomba Literasi', 'lomba-literasi', 'Kompetisi literasi dan kreativitas.', '#dc2626', 60),
('Kunjungan Sekolah', 'kunjungan-sekolah', 'Kunjungan edukatif sekolah/rombongan.', '#059669', 70),
('Bimbingan Perpustakaan', 'bimbingan-perpustakaan', 'Orientasi layanan dan pemanfaatan perpustakaan.', '#0369a1', 80),
('Webinar', 'webinar', 'Kegiatan daring atau hybrid.', '#4338ca', 90),
('Komunitas Baca', 'komunitas-baca', 'Pertemuan komunitas dan klub baca.', '#be185d', 100)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `color` = VALUES(`color`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = 1;

ALTER TABLE `literacy_events`
  ADD COLUMN IF NOT EXISTS `event_category_id` int(10) unsigned DEFAULT NULL AFTER `library_id`,
  ADD COLUMN IF NOT EXISTS `slug` varchar(180) DEFAULT NULL AFTER `title`,
  ADD COLUMN IF NOT EXISTS `summary` varchar(255) DEFAULT NULL AFTER `slug`,
  ADD COLUMN IF NOT EXISTS `poster_path` varchar(255) DEFAULT NULL AFTER `summary`,
  ADD COLUMN IF NOT EXISTS `organizer_name` varchar(180) DEFAULT NULL AFTER `event_type`,
  ADD COLUMN IF NOT EXISTS `speaker_name` varchar(180) DEFAULT NULL AFTER `organizer_name`,
  ADD COLUMN IF NOT EXISTS `target_audience` varchar(180) DEFAULT NULL AFTER `speaker_name`,
  ADD COLUMN IF NOT EXISTS `venue_type` enum('onsite','online','hybrid') NOT NULL DEFAULT 'onsite' AFTER `ends_at`,
  ADD COLUMN IF NOT EXISTS `online_url` varchar(255) DEFAULT NULL AFTER `location_name`,
  ADD COLUMN IF NOT EXISTS `registration_mode` enum('none','open','member_only','invite') NOT NULL DEFAULT 'open' AFTER `quota`,
  ADD COLUMN IF NOT EXISTS `approval_mode` enum('auto','manual') NOT NULL DEFAULT 'auto' AFTER `registration_mode`,
  ADD COLUMN IF NOT EXISTS `registration_opens_at` datetime DEFAULT NULL AFTER `approval_mode`,
  ADD COLUMN IF NOT EXISTS `registration_closes_at` datetime DEFAULT NULL AFTER `registration_opens_at`,
  ADD COLUMN IF NOT EXISTS `attendance_enabled` tinyint(1) NOT NULL DEFAULT 1 AFTER `registration_closes_at`,
  ADD COLUMN IF NOT EXISTS `certificate_enabled` tinyint(1) NOT NULL DEFAULT 0 AFTER `attendance_enabled`,
  ADD COLUMN IF NOT EXISTS `published_at` datetime DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `cancelled_reason` varchar(255) DEFAULT NULL AFTER `published_at`;

ALTER TABLE `literacy_events`
  ADD UNIQUE KEY IF NOT EXISTS `uq_literacy_events_slug` (`slug`),
  ADD KEY IF NOT EXISTS `idx_literacy_events_category` (`event_category_id`),
  ADD KEY IF NOT EXISTS `idx_literacy_events_registration` (`registration_mode`, `registration_closes_at`);

ALTER TABLE `event_registrations`
  MODIFY COLUMN `status` enum('pending','registered','approved','rejected','attended','cancelled') NOT NULL DEFAULT 'registered',
  ADD COLUMN IF NOT EXISTS `registration_code` varchar(40) DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `participant_type` enum('member','public','group') NOT NULL DEFAULT 'public' AFTER `member_id`,
  ADD COLUMN IF NOT EXISTS `institution` varchar(180) DEFAULT NULL AFTER `participant_email`,
  ADD COLUMN IF NOT EXISTS `participant_count` int(10) unsigned NOT NULL DEFAULT 1 AFTER `institution`,
  ADD COLUMN IF NOT EXISTS `ticket_token` varchar(100) DEFAULT NULL AFTER `attendance_token`,
  ADD COLUMN IF NOT EXISTS `admin_note` text DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `approved_by` bigint(20) unsigned DEFAULT NULL AFTER `admin_note`,
  ADD COLUMN IF NOT EXISTS `approved_at` datetime DEFAULT NULL AFTER `approved_by`,
  ADD COLUMN IF NOT EXISTS `checked_in_by` bigint(20) unsigned DEFAULT NULL AFTER `attended_at`,
  ADD COLUMN IF NOT EXISTS `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `checked_in_by`;

ALTER TABLE `event_registrations`
  ADD UNIQUE KEY IF NOT EXISTS `uq_event_registrations_code` (`registration_code`),
  ADD UNIQUE KEY IF NOT EXISTS `uq_event_registrations_ticket` (`ticket_token`),
  ADD KEY IF NOT EXISTS `idx_event_registrations_status` (`status`, `registered_at`),
  ADD KEY IF NOT EXISTS `idx_event_registrations_approved_by` (`approved_by`),
  ADD KEY IF NOT EXISTS `idx_event_registrations_checked_in_by` (`checked_in_by`);

CREATE TABLE IF NOT EXISTS `event_form_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `field_key` varchar(80) NOT NULL,
  `field_label` varchar(160) NOT NULL,
  `field_type` enum('text','textarea','select','radio','checkbox','number','date','email','phone') NOT NULL DEFAULT 'text',
  `placeholder` varchar(180) DEFAULT NULL,
  `helper_text` varchar(255) DEFAULT NULL,
  `options_text` text DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_form_fields_key` (`event_id`, `field_key`),
  KEY `idx_event_form_fields_event` (`event_id`, `is_active`, `sort_order`),
  CONSTRAINT `fk_event_form_fields_event` FOREIGN KEY (`event_id`) REFERENCES `literacy_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_registration_answers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `registration_id` bigint(20) unsigned NOT NULL,
  `field_id` bigint(20) unsigned DEFAULT NULL,
  `field_key` varchar(80) NOT NULL,
  `field_label` varchar(160) NOT NULL,
  `value_text` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_answers_registration` (`registration_id`),
  KEY `idx_event_answers_field` (`field_id`),
  CONSTRAINT `fk_event_answers_registration` FOREIGN KEY (`registration_id`) REFERENCES `event_registrations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_answers_field` FOREIGN KEY (`field_id`) REFERENCES `event_form_fields` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `event_photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` bigint(20) unsigned NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `caption` varchar(180) DEFAULT NULL,
  `is_cover` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_photos_event` (`event_id`, `deleted_at`),
  CONSTRAINT `fk_event_photos_event` FOREIGN KEY (`event_id`) REFERENCES `literacy_events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_photos_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('events.index', 'events', 'Event Literasi', 'events', 'Agenda literasi, pendaftaran peserta, form dinamis, dan QR attendance.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 1, 1, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'events.index'
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_delete` = 1,
  `can_export` = 1,
  `can_approve` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 0, 1, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'events.index'
WHERE r.`code` = 'ADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_delete` = 0,
  `can_export` = 1,
  `can_approve` = 1;
