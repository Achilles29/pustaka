CREATE TABLE IF NOT EXISTS `library_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `marker_color` varchar(20) NOT NULL DEFAULT '#0b6b86',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_library_types_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `libraries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `library_type_id` int(10) unsigned NOT NULL,
  `code` varchar(60) NOT NULL,
  `name` varchar(180) NOT NULL,
  `manager_name` varchar(180) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `district` varchar(120) DEFAULT NULL,
  `village` varchar(120) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(180) DEFAULT NULL,
  `website` varchar(180) DEFAULT NULL,
  `opening_hours` varchar(180) DEFAULT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `service_radius_meters` int(10) unsigned NOT NULL DEFAULT 100,
  `description` text DEFAULT NULL,
  `facilities` text DEFAULT NULL,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'active',
  `source_system` varchar(50) DEFAULT NULL,
  `source_id` varchar(80) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_libraries_code` (`code`),
  KEY `idx_libraries_type` (`library_type_id`),
  KEY `idx_libraries_status` (`status`),
  KEY `idx_libraries_district_village` (`district`, `village`),
  KEY `idx_libraries_coordinate` (`latitude`, `longitude`),
  KEY `idx_libraries_source` (`source_system`, `source_id`),
  CONSTRAINT `fk_libraries_type` FOREIGN KEY (`library_type_id`) REFERENCES `library_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_libraries_created_by` FOREIGN KEY (`created_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_libraries_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `library_photos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `library_id` bigint(20) unsigned NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `caption` varchar(180) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 100,
  `is_cover` tinyint(1) NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_library_photos_library` (`library_id`),
  KEY `idx_library_photos_cover` (`library_id`, `is_cover`),
  CONSTRAINT `fk_library_photos_library` FOREIGN KEY (`library_id`) REFERENCES `libraries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_library_photos_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `auth_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `library_types` (`code`, `name`, `description`, `marker_color`) VALUES
('perpusda', 'Perpustakaan Daerah', 'Perpustakaan daerah/kabupaten sebagai simpul utama layanan.', '#0b6b86'),
('sekolah', 'Perpustakaan Sekolah', 'Perpustakaan sekolah SD, SMP, SMA/SMK, dan sederajat.', '#2f8f66'),
('desa', 'Perpustakaan Desa', 'Perpustakaan desa/kelurahan dan taman baca lokal.', '#c58a12'),
('swasta', 'Perpustakaan Swasta', 'Perpustakaan swasta atau institusi non-pemerintah.', '#4263eb'),
('komunitas', 'Komunitas Literasi', 'Komunitas, TBM, atau ruang baca masyarakat.', '#ae3ec9'),
('mitra', 'Mitra Pojok Baca', 'Lokasi mitra untuk pojok baca digital dan layanan kolaborasi.', '#d9480f')
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `marker_color` = VALUES(`marker_color`),
  `is_active` = 1;
