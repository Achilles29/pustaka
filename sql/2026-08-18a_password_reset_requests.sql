CREATE TABLE IF NOT EXISTS password_reset_requests (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  request_code VARCHAR(32) NOT NULL,
  public_token VARCHAR(64) NOT NULL,
  status_token VARCHAR(64) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  member_id BIGINT UNSIGNED NULL,
  phone_number VARCHAR(32) NOT NULL,
  password_cipher TEXT NULL,
  delivery_status ENUM('queued','unavailable','failed') NOT NULL DEFAULT 'unavailable',
  approval_status ENUM('pending','approved','rejected','wa_sent') NOT NULL DEFAULT 'pending',
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at DATETIME NULL,
  review_note VARCHAR(500) NULL,
  wa_outbox_id BIGINT UNSIGNED NULL,
  requester_ip VARCHAR(45) NULL,
  expires_at DATETIME NOT NULL,
  viewed_at DATETIME NULL,
  completed_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_password_reset_code (request_code),
  UNIQUE KEY uq_password_reset_token (public_token),
  UNIQUE KEY uq_password_reset_status_token (status_token),
  KEY idx_password_reset_user_created (user_id, created_at),
  CONSTRAINT fk_password_reset_user FOREIGN KEY (user_id) REFERENCES auth_user(id) ON DELETE CASCADE,
  CONSTRAINT fk_password_reset_member FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Upgrade aman bila tabel versi awal (password sementara) sudah pernah dibuat.
ALTER TABLE password_reset_requests
  MODIFY password_cipher TEXT NULL,
  ADD COLUMN IF NOT EXISTS status_token VARCHAR(64) NULL AFTER public_token,
  ADD COLUMN IF NOT EXISTS completed_at DATETIME NULL AFTER viewed_at,
  ADD COLUMN IF NOT EXISTS approval_status ENUM('pending','approved','rejected','wa_sent') NOT NULL DEFAULT 'pending' AFTER delivery_status,
  ADD COLUMN IF NOT EXISTS reviewed_by BIGINT UNSIGNED NULL AFTER approval_status,
  ADD COLUMN IF NOT EXISTS reviewed_at DATETIME NULL AFTER reviewed_by,
  ADD COLUMN IF NOT EXISTS review_note VARCHAR(500) NULL AFTER reviewed_at;
UPDATE password_reset_requests
SET status_token = SHA2(CONCAT(public_token, '|status|', id), 256)
WHERE status_token IS NULL OR status_token = '';
ALTER TABLE password_reset_requests
  MODIFY status_token VARCHAR(64) NOT NULL,
  ADD UNIQUE KEY IF NOT EXISTS uq_password_reset_status_token (status_token);
