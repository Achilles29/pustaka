CREATE TABLE IF NOT EXISTS `book_collection_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_book_collection_types_code` (`code`),
  UNIQUE KEY `uq_book_collection_types_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `book_collection_types` (`code`, `name`, `description`, `sort_order`, `is_active`) VALUES
('buku', 'Buku', 'Buku cetak atau monograf fisik.', 10, 1),
('ebook', 'Ebook', 'Buku digital yang dapat memiliki aset PDF/epub reader.', 20, 1),
('cd', 'CD', 'Compact disc, CD-ROM, atau media optik CD.', 30, 1),
('dvd', 'DVD', 'DVD atau media optik video/data.', 40, 1),
('majalah', 'Majalah', 'Terbitan berkala, majalah, atau jurnal populer.', 50, 1),
('audio', 'Audio', 'Audiobook atau rekaman audio.', 60, 1),
('peta', 'Peta', 'Peta, atlas lepas, atau bahan kartografis.', 70, 1),
('lainnya', 'Lainnya', 'Jenis koleksi lain yang belum tercakup.', 999, 1)
ON DUPLICATE KEY UPDATE
  `description` = VALUES(`description`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = VALUES(`is_active`);
