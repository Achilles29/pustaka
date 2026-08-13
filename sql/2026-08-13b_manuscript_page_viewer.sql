-- Citra per halaman disimpan terpisah dari preview tunggal agar naskah dapat
-- dibaca, diperbesar, dan ditelusuri halaman demi halaman.
CREATE TABLE IF NOT EXISTS `ancient_manuscript_pages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `manuscript_id` BIGINT UNSIGNED NOT NULL,
  `page_number` SMALLINT UNSIGNED NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `original_name` VARCHAR(255) DEFAULT NULL,
  `mime_type` VARCHAR(120) NOT NULL,
  `file_size` BIGINT UNSIGNED DEFAULT NULL,
  `width` INT UNSIGNED DEFAULT NULL,
  `height` INT UNSIGNED DEFAULT NULL,
  `caption` VARCHAR(500) DEFAULT NULL,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_manuscript_page_number` (`manuscript_id`, `page_number`),
  KEY `idx_manuscript_pages_order` (`manuscript_id`, `page_number`),
  CONSTRAINT `fk_manuscript_pages_manuscript` FOREIGN KEY (`manuscript_id`) REFERENCES `ancient_manuscripts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_manuscript_pages_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
