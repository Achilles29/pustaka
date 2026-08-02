ALTER TABLE `member_registration_requests`
	ADD COLUMN IF NOT EXISTS `public_token` VARCHAR(64) NULL AFTER `registration_code`;

CREATE UNIQUE INDEX IF NOT EXISTS `idx_member_registration_public_token`
	ON `member_registration_requests` (`public_token`);

UPDATE `member_registration_requests`
SET `public_token` = SHA2(CONCAT(UUID(), '-', RAND(), '-', `id`), 256)
WHERE `public_token` IS NULL OR `public_token` = '';
