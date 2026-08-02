ALTER TABLE `books`
  ADD COLUMN IF NOT EXISTS `deleted_at` datetime DEFAULT NULL AFTER `updated_at`;

ALTER TABLE `members`
  ADD COLUMN IF NOT EXISTS `deleted_at` datetime DEFAULT NULL AFTER `updated_at`;

CREATE TABLE IF NOT EXISTS `member_visits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) DEFAULT NULL,
  `source_id` varchar(80) DEFAULT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `source_member_no` varchar(120) DEFAULT NULL,
  `visitor_no` varchar(80) DEFAULT NULL,
  `visitor_name` varchar(180) DEFAULT NULL,
  `gender_id` varchar(40) DEFAULT NULL,
  `profession_id` varchar(80) DEFAULT NULL,
  `education_id` varchar(80) DEFAULT NULL,
  `status_id` varchar(80) DEFAULT NULL,
  `location_id` varchar(80) DEFAULT NULL,
  `location_loan_id` varchar(80) DEFAULT NULL,
  `purpose_id` varchar(80) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `information` varchar(255) DEFAULT NULL,
  `visited_at` datetime DEFAULT NULL,
  `source_created_at` datetime DEFAULT NULL,
  `source_updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_member_visits_source` (`source_system`, `source_id`),
  KEY `idx_member_visits_member` (`member_id`),
  KEY `idx_member_visits_no` (`source_member_no`),
  KEY `idx_member_visits_visited` (`visited_at`),
  CONSTRAINT `fk_member_visits_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `member_access_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) DEFAULT NULL,
  `source_table` varchar(80) NOT NULL,
  `source_id` varchar(80) NOT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `source_member_id` varchar(80) DEFAULT NULL,
  `rule_type` enum('category','location') NOT NULL,
  `source_rule_id` varchar(80) DEFAULT NULL,
  `created_at_source` datetime DEFAULT NULL,
  `updated_at_source` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_member_access_rules_source` (`source_system`, `source_table`, `source_id`),
  KEY `idx_member_access_rules_member` (`member_id`),
  KEY `idx_member_access_rules_type` (`rule_type`, `source_rule_id`),
  CONSTRAINT `fk_member_access_rules_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `loan_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) DEFAULT NULL,
  `source_id` varchar(120) DEFAULT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `source_member_id` varchar(80) DEFAULT NULL,
  `branch_id` varchar(80) DEFAULT NULL,
  `location_library_id` varchar(80) DEFAULT NULL,
  `collection_count` int(10) unsigned NOT NULL DEFAULT 0,
  `loan_count` int(10) unsigned NOT NULL DEFAULT 0,
  `return_count` int(10) unsigned NOT NULL DEFAULT 0,
  `late_count` int(10) unsigned NOT NULL DEFAULT 0,
  `extend_count` int(10) unsigned NOT NULL DEFAULT 0,
  `source_created_at` datetime DEFAULT NULL,
  `source_updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_loan_transactions_source` (`source_system`, `source_id`),
  KEY `idx_loan_transactions_member` (`member_id`),
  KEY `idx_loan_transactions_source_member` (`source_member_id`),
  CONSTRAINT `fk_loan_transactions_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `loan_transaction_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) DEFAULT NULL,
  `source_id` varchar(80) DEFAULT NULL,
  `loan_transaction_id` bigint(20) unsigned DEFAULT NULL,
  `source_loan_id` varchar(120) DEFAULT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `book_item_id` bigint(20) unsigned DEFAULT NULL,
  `source_member_id` varchar(80) DEFAULT NULL,
  `source_collection_id` varchar(80) DEFAULT NULL,
  `loan_date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `actual_return_at` datetime DEFAULT NULL,
  `late_days` int(11) DEFAULT NULL,
  `loan_status` varchar(50) DEFAULT NULL,
  `source_created_at` datetime DEFAULT NULL,
  `source_updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_loan_transaction_items_source` (`source_system`, `source_id`),
  KEY `idx_loan_transaction_items_loan` (`loan_transaction_id`),
  KEY `idx_loan_transaction_items_member` (`member_id`),
  KEY `idx_loan_transaction_items_book_item` (`book_item_id`),
  KEY `idx_loan_transaction_items_status` (`loan_status`),
  KEY `idx_loan_transaction_items_date` (`loan_date`),
  CONSTRAINT `fk_loan_transaction_items_loan` FOREIGN KEY (`loan_transaction_id`) REFERENCES `loan_transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_loan_transaction_items_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_loan_transaction_items_book_item` FOREIGN KEY (`book_item_id`) REFERENCES `book_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transaction_sync_runs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_database` varchar(80) NOT NULL DEFAULT 'inlislite_v3',
  `source_table` varchar(120) DEFAULT NULL,
  `sync_type` enum('manual','scheduled','dry_run') NOT NULL DEFAULT 'manual',
  `mode` enum('import_new','refresh_existing','dry_run') NOT NULL DEFAULT 'import_new',
  `status` enum('queued','running','success','failed') NOT NULL DEFAULT 'queued',
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `total_source` int(10) unsigned NOT NULL DEFAULT 0,
  `total_inserted` int(10) unsigned NOT NULL DEFAULT 0,
  `total_updated` int(10) unsigned NOT NULL DEFAULT 0,
  `total_failed` int(10) unsigned NOT NULL DEFAULT 0,
  `message` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_transaction_sync_runs_status` (`status`),
  KEY `idx_transaction_sync_runs_created_at` (`created_at`),
  CONSTRAINT `fk_transaction_sync_runs_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('transactions.sync', 'transactions', 'Sinkronisasi Transaksi', 'transactions/sync', 'Sinkronisasi kunjungan, hak pinjam, dan histori peminjaman dari INLISLite.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 0, 1, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'transactions.sync'
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_export` = 1,
  `can_approve` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT NULL, p.`id`, 'MAIN', 'transactions.sync', 'Transaksi Harian', 'ti ti-arrows-transfer-down', 'transactions/sync', 45, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'transactions.sync'
ON DUPLICATE KEY UPDATE
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;
