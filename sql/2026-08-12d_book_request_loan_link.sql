-- Request/reservasi dan transaksi sirkulasi adalah satu alur layanan.
-- Kolom ini menyimpan item transaksi yang diterbitkan ketika buku benar-benar diserahkan.
ALTER TABLE `book_requests`
  ADD COLUMN IF NOT EXISTS `loan_transaction_item_id` bigint(20) unsigned DEFAULT NULL AFTER `book_item_id`,
  ADD KEY IF NOT EXISTS `idx_book_requests_loan_item` (`loan_transaction_item_id`);

-- MySQL/MariaDB tidak sama-sama menyediakan ADD CONSTRAINT IF NOT EXISTS.
SET @has_request_loan_fk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'book_requests'
    AND CONSTRAINT_NAME = 'fk_book_requests_loan_item'
);
SET @request_loan_fk_sql := IF(
  @has_request_loan_fk = 0,
  'ALTER TABLE `book_requests` ADD CONSTRAINT `fk_book_requests_loan_item` FOREIGN KEY (`loan_transaction_item_id`) REFERENCES `loan_transaction_items` (`id`) ON DELETE SET NULL',
  'DO 0'
);
PREPARE request_loan_fk_stmt FROM @request_loan_fk_sql;
EXECUTE request_loan_fk_stmt;
DEALLOCATE PREPARE request_loan_fk_stmt;
