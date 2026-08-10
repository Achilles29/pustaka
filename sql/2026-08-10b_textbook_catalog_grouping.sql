-- Kelompok khusus untuk buku ajar impor. Idempoten dan aman dijalankan ulang.
-- Kode DDC asal tetap disimpan di books.classification / call_number;
-- master ini dipakai sebagai pengelompokan penelusuran yang ramah pembaca.

INSERT INTO `book_content_categories` (`code`, `name`, `description`, `sort_order`, `is_active`) VALUES
('buku-pelajaran', 'Buku Pelajaran', 'Buku ajar siswa, panduan guru, dan bahan pembelajaran kurikulum sekolah.', 55, 1)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = VALUES(`is_active`);

INSERT INTO `book_classification_masters` (`code`, `name`, `description`, `sort_order`, `is_active`) VALUES
('buku-pelajaran', 'Buku Pelajaran dan Pembelajaran', 'Pengelompokan koleksi buku ajar sekolah. Nomor klasifikasi mata pelajaran asal tetap tercatat pada bibliografi.', 115, 1)
ON DUPLICATE KEY UPDATE
  `name` = VALUES(`name`),
  `description` = VALUES(`description`),
  `sort_order` = VALUES(`sort_order`),
  `is_active` = VALUES(`is_active`);

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'buku-pelajaran'
JOIN `book_classification_masters` cm ON cm.`code` = 'buku-pelajaran'
SET
  b.`content_category_id` = cc.`id`,
  b.`content_classification_id` = cm.`id`
WHERE b.`source_system` = 'buku_kemendikdasmen_2026'
  AND b.`deleted_at` IS NULL;

UPDATE `book_items`
SET
  `collection_type` = 'Ebook',
  `category_name` = 'Buku Pelajaran',
  `media_name` = 'PDF',
  `rule_name` = 'Baca digital',
  `status` = 'available',
  `status_label` = 'Tersedia digital',
  `is_public` = 1
WHERE `source_system` = 'buku_kemendikdasmen_2026'
  AND `deleted_at` IS NULL;
