ALTER TABLE `reading_sessions`
  ADD COLUMN IF NOT EXISTS `secure_token` varchar(80) DEFAULT NULL AFTER `reading_token_id`,
  ADD COLUMN IF NOT EXISTS `last_seen_at` datetime DEFAULT NULL AFTER `started_at`,
  ADD KEY IF NOT EXISTS `idx_reading_sessions_secure_token` (`secure_token`),
  ADD KEY IF NOT EXISTS `idx_reading_sessions_member_asset` (`member_id`, `digital_asset_id`, `status`);

CREATE TABLE IF NOT EXISTS `reader_access_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reading_session_id` bigint(20) unsigned DEFAULT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `digital_asset_id` bigint(20) unsigned DEFAULT NULL,
  `book_id` bigint(20) unsigned DEFAULT NULL,
  `event_type` enum('session_opened','pdf_stream','page_rendered','rate_limited','blocked','finished') NOT NULL,
  `page_number` int(10) unsigned DEFAULT NULL,
  `ip_address` varchar(80) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `meta_json` longtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reader_access_logs_session` (`reading_session_id`),
  KEY `idx_reader_access_logs_member_date` (`member_id`, `created_at`),
  KEY `idx_reader_access_logs_event_date` (`event_type`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
