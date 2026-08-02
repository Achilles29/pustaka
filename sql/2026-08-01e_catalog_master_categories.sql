CREATE TABLE IF NOT EXISTS `book_content_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_book_content_categories_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `book_classification_masters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `name` varchar(140) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_book_classification_masters_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `books`
  ADD COLUMN IF NOT EXISTS `content_category_id` int(10) unsigned DEFAULT NULL AFTER `classification`,
  ADD COLUMN IF NOT EXISTS `content_classification_id` int(10) unsigned DEFAULT NULL AFTER `content_category_id`,
  ADD KEY IF NOT EXISTS `idx_books_content_category` (`content_category_id`),
  ADD KEY IF NOT EXISTS `idx_books_content_classification` (`content_classification_id`);

INSERT INTO `book_content_categories` (`code`, `name`, `description`, `sort_order`, `is_active`) VALUES
('fiksi', 'Fiksi', 'Novel, cerita pendek, sastra populer, komik naratif, dan bacaan imajinatif.', 10, 1),
('non-fiksi', 'Non Fiksi', 'Buku pengetahuan, panduan, biografi, sosial, agama, teknologi, dan karya informatif.', 20, 1),
('pengetahuan', 'Pengetahuan Umum', 'Bacaan pengetahuan populer lintas topik.', 30, 1),
('referensi', 'Referensi', 'Kamus, ensiklopedia, direktori, atlas, dan bahan rujukan.', 40, 1),
('anak-remaja', 'Anak dan Remaja', 'Bacaan anak, remaja, dongeng, pembelajaran awal, dan literasi keluarga.', 50, 1),
('karya-ilmiah', 'Skripsi / Tesis / Karya Ilmiah', 'Skripsi, tesis, disertasi, laporan penelitian, jurnal, prosiding, dan karya ilmiah lokal.', 60, 1),
('lokal-rembang', 'Muatan Lokal Rembang', 'Koleksi tentang Rembang, sejarah lokal, kebudayaan, tokoh, dan potensi daerah.', 70, 1),
('sejarah-budaya', 'Sejarah dan Budaya', 'Sejarah, budaya, seni, bahasa, dan kajian humaniora.', 80, 1),
('agama', 'Agama', 'Koleksi agama, spiritualitas, dan etika.', 90, 1),
('teknologi', 'Sains dan Teknologi', 'Sains, komputer, teknologi, kesehatan, pertanian, dan ilmu terapan.', 100, 1)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = VALUES(`is_active`);

INSERT INTO `book_classification_masters` (`code`, `name`, `description`, `sort_order`, `is_active`) VALUES
('000', '000 - Karya Umum dan Komputer', 'Karya umum, informasi, komputer, perpustakaan, dan ensiklopedia.', 10, 1),
('100', '100 - Filsafat dan Psikologi', 'Filsafat, psikologi, etika, dan logika.', 20, 1),
('200', '200 - Agama', 'Agama, kitab, teologi, dan praktik keagamaan.', 30, 1),
('300', '300 - Ilmu Sosial', 'Sosial, pendidikan, ekonomi, hukum, administrasi, dan politik.', 40, 1),
('400', '400 - Bahasa', 'Bahasa, linguistik, dan pembelajaran bahasa.', 50, 1),
('500', '500 - Sains', 'Matematika, fisika, kimia, biologi, dan ilmu alam.', 60, 1),
('600', '600 - Teknologi dan Ilmu Terapan', 'Kesehatan, teknik, pertanian, manajemen, dan keterampilan terapan.', 70, 1),
('700', '700 - Seni dan Olahraga', 'Seni, musik, rekreasi, olahraga, dan hiburan.', 80, 1),
('800', '800 - Sastra', 'Sastra, cerita, puisi, drama, novel, dan kritik sastra.', 90, 1),
('900', '900 - Sejarah dan Geografi', 'Sejarah, geografi, biografi, perjalanan, dan kebudayaan wilayah.', 100, 1),
('ilmiah', 'Karya Ilmiah Lokal', 'Skripsi, tesis, disertasi, laporan penelitian, jurnal, prosiding, dan naskah akademik.', 110, 1)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = VALUES(`is_active`);

UPDATE `books` b
JOIN `book_classification_masters` cm
  ON cm.`code` = CONCAT(LEFT(TRIM(b.`classification`), 1), '00')
SET b.`content_classification_id` = cm.`id`
WHERE b.`content_classification_id` IS NULL
  AND b.`classification` REGEXP '^[0-9]';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'karya-ilmiah'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
  AND (LOWER(b.`title`) REGEXP 'skripsi|tesis|disertasi|penelitian|jurnal|prosiding|karya ilmiah');

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'lokal-rembang'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
  AND LOWER(CONCAT_WS(' ', b.`title`, b.`abstract`, b.`publisher`)) LIKE '%rembang%';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'referensi'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
  AND (LOWER(b.`title`) REGEXP 'kamus|ensiklopedia|atlas|direktori'
       OR EXISTS (
         SELECT 1 FROM `book_items` i
         WHERE i.`book_id` = b.`id` AND i.`category_name` = 'Koleksi Referensi' AND i.`deleted_at` IS NULL
       ));

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'fiksi'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
  AND b.`classification` REGEXP '^8';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'agama'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
  AND b.`classification` REGEXP '^2';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'teknologi'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
  AND b.`classification` REGEXP '^[56]';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'sejarah-budaya'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
  AND b.`classification` REGEXP '^[79]';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'pengetahuan'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
  AND b.`classification` REGEXP '^[034]';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'non-fiksi'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL;

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('catalog.masters', 'catalog', 'Master Buku', 'catalog/masters', 'Master kategori konten dan klasifikasi koleksi untuk kurasi katalog.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 1, 1, 1, 1, 1
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'catalog.masters'
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_create` = 1,
  `can_edit` = 1,
  `can_delete` = 1,
  `can_export` = 1,
  `can_approve` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT NULL, p.`id`, 'MAIN', 'catalog.masters', 'Master Buku', 'ti ti-category-2', 'catalog/masters', 36, 1, 1, 0
FROM `sys_page` p WHERE p.`code` = 'catalog.masters'
ON DUPLICATE KEY UPDATE
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;
