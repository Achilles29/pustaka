-- Otomasi notifikasi peminjaman WA. Jalankan setelah 2026-08-14a_whatsapp_center.sql.
ALTER TABLE wa_outbox
  ADD COLUMN IF NOT EXISTS dedupe_key VARCHAR(160) NULL AFTER event_code,
  ADD UNIQUE KEY IF NOT EXISTS uq_wa_outbox_dedupe_key (dedupe_key);

CREATE TABLE IF NOT EXISTS wa_automation_settings (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  due_reminder_enabled TINYINT(1) NOT NULL DEFAULT 1,
  overdue_auto_enabled TINYINT(1) NOT NULL DEFAULT 0,
  overdue_repeat_days TINYINT UNSIGNED NOT NULL DEFAULT 3,
  last_run_at DATETIME NULL,
  updated_by BIGINT UNSIGNED NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_wa_automation_user FOREIGN KEY (updated_by) REFERENCES auth_user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO wa_automation_settings (id, due_reminder_enabled, overdue_auto_enabled, overdue_repeat_days)
VALUES (1, 1, 0, 3);
