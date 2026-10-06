-- Public contact details; rerunnable without overwriting saved settings.
CREATE TABLE IF NOT EXISTS patron_feedback_contacts (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  whatsapp VARCHAR(20) NOT NULL DEFAULT '',
  instagram VARCHAR(30) NOT NULL DEFAULT '',
  tiktok VARCHAR(24) NOT NULL DEFAULT '',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO patron_feedback_contacts (id, whatsapp, instagram, tiktok)
VALUES (1, '085165805518', 'dinarpusrembang', 'perpustakaan.umum.rbg');
