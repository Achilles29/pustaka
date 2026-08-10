-- Memisahkan siapa yang boleh membaca dari cara file PDF dikirim.
-- access_policy dipertahankan sementara sebagai jejak kompatibilitas sesi lama.

ALTER TABLE `digital_assets`
  ADD COLUMN IF NOT EXISTS `reader_audience` enum('member','internal') NOT NULL DEFAULT 'member' AFTER `access_policy`,
  ADD COLUMN IF NOT EXISTS `pdf_delivery` enum('render_locked','download_allowed') NOT NULL DEFAULT 'render_locked' AFTER `reader_audience`,
  ADD KEY IF NOT EXISTS `idx_digital_assets_reader_audience` (`reader_audience`),
  ADD KEY IF NOT EXISTS `idx_digital_assets_pdf_delivery` (`pdf_delivery`);

UPDATE `digital_assets`
SET
  `reader_audience` = CASE WHEN `access_policy` = 'internal' THEN 'internal' ELSE 'member' END,
  `pdf_delivery` = CASE WHEN `access_policy` = 'download_allowed' OR `is_downloadable` = 1 THEN 'download_allowed' ELSE 'render_locked' END;
