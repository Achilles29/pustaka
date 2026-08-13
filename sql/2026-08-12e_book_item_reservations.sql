-- Status reservasi membuat ketersediaan fisik akurat sejak request masuk.
-- Eksemplar baru boleh dipinjam lagi setelah request ditolak/dibatalkan atau
-- setelah transaksi dikembalikan.
ALTER TABLE `book_items`
  MODIFY COLUMN `status` enum('available','reserved','loaned','missing','damaged','unknown') NOT NULL DEFAULT 'unknown';

-- Request yang sebelumnya sudah disiapkan oleh petugas juga harus menahan
-- eksemplarnya agar tidak tampil sebagai tersedia di katalog publik.
UPDATE `book_items` bi
JOIN `book_requests` br ON br.`book_item_id` = bi.`id`
SET bi.`status` = 'reserved', bi.`updated_at` = NOW()
WHERE br.`status` IN ('pending', 'approved')
  AND bi.`status` = 'available'
  AND bi.`deleted_at` IS NULL;
