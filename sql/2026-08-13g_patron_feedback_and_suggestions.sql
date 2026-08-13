-- Suara Pemustaka: ulasan layanan dan usulan koleksi/fitur dari publik.
CREATE TABLE IF NOT EXISTS `patron_feedback` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `public_token` VARCHAR(64) NOT NULL,
  `submitted_by_auth_user_id` BIGINT UNSIGNED DEFAULT NULL,
  `feedback_type` ENUM('service_review','book_request','digital_content','facility','feature','complaint','other') NOT NULL,
  `rating` TINYINT UNSIGNED DEFAULT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `suggested_author` VARCHAR(255) DEFAULT NULL,
  `suggested_isbn` VARCHAR(32) DEFAULT NULL,
  `suggested_format` VARCHAR(80) DEFAULT NULL,
  `submitter_name` VARCHAR(180) DEFAULT NULL,
  `submitter_email` VARCHAR(180) DEFAULT NULL,
  `submitter_phone` VARCHAR(80) DEFAULT NULL,
  `status` ENUM('pending','reviewing','planned','resolved','declined') NOT NULL DEFAULT 'pending',
  `admin_note` TEXT DEFAULT NULL,
  `reviewed_by` BIGINT UNSIGNED DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_patron_feedback_token` (`public_token`),
  KEY `idx_patron_feedback_status_created` (`status`,`created_at`),
  KEY `idx_patron_feedback_type` (`feedback_type`),
  KEY `idx_patron_feedback_user` (`submitted_by_auth_user_id`),
  CONSTRAINT `fk_patron_feedback_submitter` FOREIGN KEY (`submitted_by_auth_user_id`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_patron_feedback_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sys_page` (`code`,`module`,`title`,`route`,`description`,`is_active`) VALUES
('patron_feedback.index','patron_feedback','Suara Pemustaka','patron-feedback','Tinjau ulasan layanan, usulan buku/konten, dan aspirasi pemustaka.',1)
ON DUPLICATE KEY UPDATE `module`=VALUES(`module`),`title`=VALUES(`title`),`route`=VALUES(`route`),`description`=VALUES(`description`),`is_active`=1;

INSERT INTO `auth_role_permission` (`role_id`,`page_id`,`can_view`,`can_create`,`can_edit`,`can_delete`,`can_export`,`can_approve`)
SELECT r.`id`,p.`id`,1,1,1,1,1,1
FROM `auth_role` r JOIN `sys_page` p ON p.`code`='patron_feedback.index'
WHERE r.`code` IN ('SUPERADMIN','ADMIN')
ON DUPLICATE KEY UPDATE `can_view`=1,`can_create`=1,`can_edit`=1,`can_delete`=1,`can_export`=1,`can_approve`=1;

INSERT INTO `sys_menu` (`parent_id`,`page_id`,`menu_area`,`menu_key`,`title`,`icon`,`url`,`sort_order`,`is_visible`,`is_active`,`is_locked`)
SELECT parent.`id`,p.`id`,'MAIN','patron_feedback.index','Suara Pemustaka','ti ti-message-heart','patron-feedback',50,1,1,0
FROM `sys_menu` parent JOIN `sys_page` p ON p.`code`='patron_feedback.index'
WHERE parent.`menu_key`='digital_services'
ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`),`page_id`=VALUES(`page_id`),`title`=VALUES(`title`),`icon`=VALUES(`icon`),`url`=VALUES(`url`),`sort_order`=VALUES(`sort_order`),`is_visible`=1,`is_active`=1;
