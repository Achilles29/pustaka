ALTER TABLE `reading_tokens`
	ADD COLUMN IF NOT EXISTS `revoked_by` bigint(20) unsigned DEFAULT NULL AFTER `issued_by`,
	ADD COLUMN IF NOT EXISTS `revoked_at` datetime DEFAULT NULL AFTER `revoked_by`,
	ADD COLUMN IF NOT EXISTS `revoke_reason` varchar(255) DEFAULT NULL AFTER `revoked_at`,
	ADD KEY IF NOT EXISTS `idx_reading_tokens_revoked_by` (`revoked_by`);

ALTER TABLE `reading_sessions`
	ADD COLUMN IF NOT EXISTS `access_origin` enum('external','reading_point','library','admin') NOT NULL DEFAULT 'external' AFTER `access_policy`,
	ADD COLUMN IF NOT EXISTS `access_location_label` varchar(180) DEFAULT NULL AFTER `access_origin`,
	ADD COLUMN IF NOT EXISTS `quota_charged` int(10) unsigned NOT NULL DEFAULT 0 AFTER `access_location_label`,
	ADD COLUMN IF NOT EXISTS `quota_unit` enum('minutes','pages','books') DEFAULT NULL AFTER `quota_charged`;

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('reading_tokens.index', 'reading_points', 'Monitoring Token Baca', 'reading-points/tokens', 'Monitoring token Pojok Baca, sisa kuota, status, dan pencabutan token.')
ON DUPLICATE KEY UPDATE
	`title` = VALUES(`title`),
	`route` = VALUES(`route`),
	`description` = VALUES(`description`),
	`is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 0, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'reading_tokens.index'
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
	`can_view` = 1,
	`can_create` = 1,
	`can_edit` = 1,
	`can_approve` = 1;

SET @digital_services_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'digital_services' LIMIT 1);

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @digital_services_id, p.`id`, 'MAIN', 'reading_tokens.index', 'Monitoring Token', 'ti ti-ticket', 'reading-points/tokens', 45, 1, 1, 0
FROM `sys_page` p
WHERE p.`code` = 'reading_tokens.index'
	AND NOT EXISTS (SELECT 1 FROM `sys_menu` WHERE `menu_key` = 'reading_tokens.index');
