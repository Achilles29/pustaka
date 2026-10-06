-- Identitas eksemplar utama katalog manual harus unik per buku karena
-- uq_book_items_source mencakup pasangan (source_system, source_id).
UPDATE `book_items`
SET `source_id` = CONCAT('catalog-primary-', `book_id`)
WHERE `source_system` = 'manual'
  AND `source_id` = 'catalog-primary';
