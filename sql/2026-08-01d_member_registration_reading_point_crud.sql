CREATE TABLE IF NOT EXISTS `member_registration_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `registration_code` varchar(40) NOT NULL,
  `full_name` varchar(180) NOT NULL,
  `identity_number` varchar(80) NOT NULL,
  `birth_place` varchar(120) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` varchar(40) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `district` varchar(120) DEFAULT NULL,
  `village` varchar(120) DEFAULT NULL,
  `phone` varchar(80) DEFAULT NULL,
  `email` varchar(180) DEFAULT NULL,
  `member_type` varchar(80) DEFAULT 'Umum',
  `education` varchar(120) DEFAULT NULL,
  `occupation` varchar(120) DEFAULT NULL,
  `is_rembang_resident` tinyint(1) NOT NULL DEFAULT 1,
  `residency_note` varchar(180) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `ktp_path` varchar(255) DEFAULT NULL,
  `kk_path` varchar(255) DEFAULT NULL,
  `support_letter_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `member_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_member_registration_code` (`registration_code`),
  KEY `idx_member_registration_identity` (`identity_number`),
  KEY `idx_member_registration_status` (`status`, `created_at`),
  KEY `idx_member_registration_member` (`member_id`),
  CONSTRAINT `fk_member_registration_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_member_registration_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('members.registrations', 'members', 'Pendaftaran Online', 'members/registrations', 'Verifikasi pendaftaran member online sebelum akun aktif.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 1, 1, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'members.registrations'
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_delete` = 1,
  `can_export` = 1,
  `can_approve` = 1;

SET @digital_services_id := (SELECT `id` FROM `sys_menu` WHERE `menu_key` = 'digital_services' LIMIT 1);

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT @digital_services_id, p.`id`, 'MAIN', 'members.registrations', 'Pendaftaran Online', 'ti ti-user-plus', 'members/registrations', 5, 1, 1, 0
FROM `sys_page` p WHERE p.`code` = 'members.registrations'
ON DUPLICATE KEY UPDATE
  `parent_id` = @digital_services_id,
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;
