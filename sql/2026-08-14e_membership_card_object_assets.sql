-- Aset gambar/logo yang dapat dipakai ulang sebagai objek pada kartu anggota.
CREATE TABLE IF NOT EXISTS membership_card_design_assets (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  asset_type ENUM('OBJECT') NOT NULL DEFAULT 'OBJECT',
  label VARCHAR(100) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  mime_type VARCHAR(80) NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  uploaded_by BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_membership_card_design_asset_path (file_path),
  KEY idx_membership_card_design_asset_active (asset_type,is_active,created_at),
  CONSTRAINT fk_membership_card_design_asset_user FOREIGN KEY (uploaded_by) REFERENCES auth_user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
