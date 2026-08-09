ALTER TABLE `quiz_import_batches`
  MODIFY `format` ENUM('csv','xlsx','json','txt') NOT NULL DEFAULT 'csv';

UPDATE `quiz_import_batches`
SET `format` = 'txt'
WHERE (`format` = '' OR `format` IS NULL)
  AND LOWER(`filename`) LIKE '%.txt';

UPDATE `quiz_import_batches`
SET `format` = 'csv'
WHERE `format` = '' OR `format` IS NULL;
