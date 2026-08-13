-- Pengembalian transaksi legacy dilakukan sebagai keputusan lokal yang tidak
-- mengubah snapshot INLISLite dan tidak tertimpa ketika sinkronisasi diulang.
ALTER TABLE `loan_transaction_items`
  ADD COLUMN IF NOT EXISTS `local_return_at` datetime DEFAULT NULL AFTER `actual_return_at`,
  ADD COLUMN IF NOT EXISTS `local_returned_by` bigint(20) unsigned DEFAULT NULL AFTER `local_return_at`,
  ADD COLUMN IF NOT EXISTS `local_return_note` varchar(500) DEFAULT NULL AFTER `local_returned_by`,
  ADD KEY IF NOT EXISTS `idx_loan_item_local_return` (`local_return_at`),
  ADD KEY IF NOT EXISTS `idx_loan_item_local_returned_by` (`local_returned_by`);

SET @has_local_return_fk := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'loan_transaction_items'
    AND CONSTRAINT_NAME = 'fk_loan_item_local_returned_by'
);
SET @local_return_fk_sql := IF(
  @has_local_return_fk = 0,
  'ALTER TABLE `loan_transaction_items` ADD CONSTRAINT `fk_loan_item_local_returned_by` FOREIGN KEY (`local_returned_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL',
  'DO 0'
);
PREPARE local_return_fk_stmt FROM @local_return_fk_sql;
EXECUTE local_return_fk_stmt;
DEALLOCATE PREPARE local_return_fk_stmt;

-- Perbaikan awal: transaksi efektif aktif menahan eksemplar; eksemplar loaned
-- tanpa transaksi efektif aktif dibuka kembali. Status reserved/lost/dll tidak disentuh.
UPDATE `book_items` bi
SET bi.`status` = 'loaned', bi.`updated_at` = NOW()
WHERE bi.`deleted_at` IS NULL
  AND bi.`is_loanable` = 1
  AND bi.`status` IN ('available', 'loaned')
  AND EXISTS (
    SELECT 1 FROM `loan_transaction_items` li
    WHERE li.`book_item_id` = bi.`id`
      AND li.`actual_return_at` IS NULL
      AND li.`local_return_at` IS NULL
      AND UPPER(COALESCE(li.`loan_status`, '')) = 'LOAN'
  );

UPDATE `book_items` bi
SET bi.`status` = 'available', bi.`updated_at` = NOW()
WHERE bi.`deleted_at` IS NULL
  AND bi.`is_loanable` = 1
  AND bi.`status` = 'loaned'
  AND NOT EXISTS (
    SELECT 1 FROM `loan_transaction_items` li
    WHERE li.`book_item_id` = bi.`id`
      AND li.`actual_return_at` IS NULL
      AND li.`local_return_at` IS NULL
      AND UPPER(COALESCE(li.`loan_status`, '')) = 'LOAN'
  );
