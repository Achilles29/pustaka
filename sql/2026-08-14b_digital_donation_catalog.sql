-- Menautkan satu donasi digital yang diterima ke tepat satu entri katalog.
ALTER TABLE `digital_donation_submissions`
  ADD COLUMN IF NOT EXISTS `catalog_book_id` BIGINT UNSIGNED DEFAULT NULL AFTER `admin_note`,
  ADD COLUMN IF NOT EXISTS `cataloged_by` BIGINT UNSIGNED DEFAULT NULL AFTER `catalog_book_id`,
  ADD COLUMN IF NOT EXISTS `cataloged_at` DATETIME DEFAULT NULL AFTER `cataloged_by`,
  ADD UNIQUE KEY IF NOT EXISTS `uq_digital_donation_catalog_book` (`catalog_book_id`),
  ADD KEY IF NOT EXISTS `idx_digital_donation_cataloged_by` (`cataloged_by`);

SET @has_book_fk = (
  SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'digital_donation_submissions'
    AND CONSTRAINT_NAME = 'fk_digital_donation_catalog_book'
);
SET @book_fk_sql = IF(@has_book_fk = 0,
  'ALTER TABLE `digital_donation_submissions` ADD CONSTRAINT `fk_digital_donation_catalog_book` FOREIGN KEY (`catalog_book_id`) REFERENCES `books` (`id`) ON DELETE SET NULL',
  'SELECT 1');
PREPARE book_fk_stmt FROM @book_fk_sql; EXECUTE book_fk_stmt; DEALLOCATE PREPARE book_fk_stmt;

SET @has_user_fk = (
  SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'digital_donation_submissions'
    AND CONSTRAINT_NAME = 'fk_digital_donation_cataloged_by'
);
SET @user_fk_sql = IF(@has_user_fk = 0,
  'ALTER TABLE `digital_donation_submissions` ADD CONSTRAINT `fk_digital_donation_cataloged_by` FOREIGN KEY (`cataloged_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL',
  'SELECT 1');
PREPARE user_fk_stmt FROM @user_fk_sql; EXECUTE user_fk_stmt; DEALLOCATE PREPARE user_fk_stmt;
