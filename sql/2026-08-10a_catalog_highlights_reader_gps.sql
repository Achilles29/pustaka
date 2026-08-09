START TRANSACTION;

CREATE TABLE IF NOT EXISTS `catalog_highlights` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `target_type` enum('book','category') NOT NULL DEFAULT 'book',
  `book_id` bigint(20) unsigned DEFAULT NULL,
  `content_category_id` int(10) unsigned DEFAULT NULL,
  `highlight_type` enum('featured','new_arrival','digital','local','recommendation') NOT NULL DEFAULT 'featured',
  `label` varchar(120) DEFAULT NULL,
  `title_override` varchar(180) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 100,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_catalog_highlights_target` (`target_type`, `book_id`, `content_category_id`),
  KEY `idx_catalog_highlights_active` (`is_active`, `starts_at`, `ends_at`, `sort_order`),
  KEY `idx_catalog_highlights_type` (`highlight_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`, `is_active`)
VALUES ('catalog.highlights', 'catalog', 'Highlight Katalog', 'catalog/highlights', 'Mengatur buku dan kategori katalog pilihan yang tampil di dashboard pemustaka.', 1)
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = VALUES(`is_active`);

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 1, 0, 0
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'catalog.highlights'
WHERE r.`code` IN ('SUPERADMIN', 'ADMIN')
ON DUPLICATE KEY UPDATE
  `can_view` = VALUES(`can_view`),
  `can_create` = VALUES(`can_create`),
  `can_edit` = VALUES(`can_edit`),
  `can_delete` = VALUES(`can_delete`);

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.`id`, page.`id`, 'MAIN', 'catalog.highlights', 'Highlight Katalog', 'ti ti-sparkles', 'catalog/highlights', 20, 1, 1, 0
FROM `sys_menu` parent
JOIN `sys_page` page ON page.`code` = 'catalog.highlights'
WHERE parent.`menu_key` = 'collection_services'
  AND NOT EXISTS (
    SELECT 1 FROM `sys_menu` existing
    WHERE existing.`menu_key` = 'catalog.highlights'
  );

UPDATE `sys_menu` menu
JOIN `sys_menu` parent ON parent.`menu_key` = 'collection_services'
JOIN `sys_page` page ON page.`code` = 'catalog.highlights'
SET menu.`parent_id` = parent.`id`,
    menu.`page_id` = page.`id`,
    menu.`title` = 'Highlight Katalog',
    menu.`icon` = 'ti ti-sparkles',
    menu.`url` = 'catalog/highlights',
    menu.`sort_order` = 20,
    menu.`is_visible` = 1,
    menu.`is_active` = 1
WHERE menu.`menu_key` = 'catalog.highlights';

COMMIT;
