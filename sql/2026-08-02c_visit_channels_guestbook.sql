ALTER TABLE `member_visits`
  ADD COLUMN IF NOT EXISTS `visit_channel` enum('inlislite_guestbook','library_guestbook','member_dashboard','digital_access','reading_point','service_monitor','qr_checkin') NOT NULL DEFAULT 'inlislite_guestbook' AFTER `source_system`,
  ADD COLUMN IF NOT EXISTS `visit_origin` enum('library','reading_point','digital_external','digital_internal','legacy') NOT NULL DEFAULT 'legacy' AFTER `visit_channel`,
  ADD COLUMN IF NOT EXISTS `library_id` bigint(20) unsigned DEFAULT NULL AFTER `member_id`,
  ADD COLUMN IF NOT EXISTS `reading_point_id` bigint(20) unsigned DEFAULT NULL AFTER `library_id`,
  ADD COLUMN IF NOT EXISTS `reading_session_id` bigint(20) unsigned DEFAULT NULL AFTER `reading_point_id`,
  ADD COLUMN IF NOT EXISTS `auth_user_id` bigint(20) unsigned DEFAULT NULL AFTER `reading_session_id`,
  ADD COLUMN IF NOT EXISTS `group_name` varchar(180) DEFAULT NULL AFTER `visitor_name`,
  ADD COLUMN IF NOT EXISTS `group_leader_name` varchar(180) DEFAULT NULL AFTER `group_name`,
  ADD COLUMN IF NOT EXISTS `visitor_count` int(10) unsigned NOT NULL DEFAULT 1 AFTER `group_leader_name`,
  ADD COLUMN IF NOT EXISTS `checkin_method` enum('guest_form','member_search','member_qr','member_gps','dashboard_auto','reader_quota','legacy_sync') NOT NULL DEFAULT 'legacy_sync' AFTER `visitor_count`,
  ADD COLUMN IF NOT EXISTS `qr_token_id` bigint(20) unsigned DEFAULT NULL AFTER `checkin_method`,
  ADD COLUMN IF NOT EXISTS `ip_address` varchar(80) DEFAULT NULL AFTER `information`,
  ADD COLUMN IF NOT EXISTS `user_agent` varchar(255) DEFAULT NULL AFTER `ip_address`,
  ADD COLUMN IF NOT EXISTS `latitude` decimal(10,7) DEFAULT NULL AFTER `user_agent`,
  ADD COLUMN IF NOT EXISTS `longitude` decimal(10,7) DEFAULT NULL AFTER `latitude`,
  ADD COLUMN IF NOT EXISTS `metadata_json` longtext DEFAULT NULL AFTER `longitude`;

ALTER TABLE `member_visits`
  ADD INDEX IF NOT EXISTS `idx_member_visits_channel_date` (`visit_channel`, `visited_at`),
  ADD INDEX IF NOT EXISTS `idx_member_visits_origin_date` (`visit_origin`, `visited_at`),
  ADD INDEX IF NOT EXISTS `idx_member_visits_library_date` (`library_id`, `visited_at`),
  ADD INDEX IF NOT EXISTS `idx_member_visits_reading_point_date` (`reading_point_id`, `visited_at`),
  ADD INDEX IF NOT EXISTS `idx_member_visits_auth_user_date` (`auth_user_id`, `visited_at`);

UPDATE `member_visits`
SET `visit_channel` = 'inlislite_guestbook',
    `visit_origin` = 'legacy',
    `checkin_method` = 'legacy_sync',
    `visitor_count` = IFNULL(NULLIF(`visitor_count`, 0), 1)
WHERE `source_system` = 'inlislite_v3';

CREATE TABLE IF NOT EXISTS `visit_kiosk_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(80) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_visit_kiosk_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `visit_kiosk_settings` (`setting_key`, `setting_value`, `description`)
VALUES
  ('qr_refresh_seconds', '60', 'Durasi masa berlaku QR dinamis pada monitor pelayanan, dalam detik.'),
  ('default_visit_library_id', '0', 'ID perpustakaan pusat yang dipakai halaman monitor pelayanan jika belum dipilih.')
ON DUPLICATE KEY UPDATE
  `description` = VALUES(`description`);

CREATE TABLE IF NOT EXISTS `visit_kiosk_qr_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `library_id` bigint(20) unsigned DEFAULT NULL,
  `token` varchar(80) NOT NULL,
  `status` enum('active','used','expired','revoked') NOT NULL DEFAULT 'active',
  `expires_at` datetime NOT NULL,
  `used_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_visit_kiosk_qr_tokens_token` (`token`),
  KEY `idx_visit_kiosk_qr_tokens_library` (`library_id`),
  KEY `idx_visit_kiosk_qr_tokens_status_expiry` (`status`, `expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
