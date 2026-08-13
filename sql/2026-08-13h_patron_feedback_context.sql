ALTER TABLE `patron_feedback`
  ADD COLUMN `location_context` VARCHAR(500) NULL AFTER `suggested_format`;
