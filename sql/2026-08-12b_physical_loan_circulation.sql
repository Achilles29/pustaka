-- Sirkulasi peminjaman fisik aplikasi. Histori INLISLite tetap memakai
-- loan_transactions dan loan_transaction_items yang sama.

ALTER TABLE `book_items`
  ADD COLUMN IF NOT EXISTS `is_loanable` tinyint(1) NOT NULL DEFAULT 1 AFTER `is_public`;

-- Ebook/berkas baca online bukan eksemplar yang dapat dipinjam secara fisik.
UPDATE `book_items`
SET `is_loanable` = 0
WHERE LOWER(COALESCE(`collection_type`, '')) = 'ebook'
   OR LOWER(COALESCE(`rule_name`, '')) LIKE '%baca digital%';

-- Sinkronkan kondisi eksemplar dengan transaksi pinjam yang masih terbuka.
-- Ini juga memperbaiki data histori hasil migrasi yang sebelumnya belum
-- menurunkan status eksemplar menjadi "loaned".
UPDATE `book_items` bi
JOIN `loan_transaction_items` li ON li.`book_item_id` = bi.`id`
SET bi.`status` = 'loaned', bi.`updated_at` = NOW()
WHERE li.`actual_return_at` IS NULL
  AND UPPER(COALESCE(li.`loan_status`, '')) = 'LOAN'
  AND bi.`deleted_at` IS NULL;

CREATE TABLE IF NOT EXISTS `library_loan_settings` (
  `id` tinyint unsigned NOT NULL,
  `is_loan_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `default_loan_days` tinyint unsigned NOT NULL DEFAULT 7,
  `max_active_loans` tinyint unsigned NOT NULL DEFAULT 3,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `chk_library_loan_settings_singleton` CHECK (`id` = 1),
  CONSTRAINT `fk_library_loan_settings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `library_loan_settings` (`id`, `is_loan_enabled`, `default_loan_days`, `max_active_loans`)
VALUES (1, 1, 7, 3)
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`);

-- Petugas ADMIN dapat menjalankan sirkulasi dari halaman Request Buku.
INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 0, 0, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'catalog.requests'
WHERE r.`code` = 'ADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_approve` = 1;
