-- Pengaturan token baca; nilai dapat diubah dari UI tanpa mengubah kode.

CREATE TABLE IF NOT EXISTS reading_token_settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(80) NOT NULL,
    setting_value VARCHAR(80) NOT NULL,
    description VARCHAR(255) NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_reading_token_settings_key (setting_key),
    KEY idx_reading_token_settings_updated_by (updated_by),
    CONSTRAINT fk_reading_token_settings_updated_by FOREIGN KEY (updated_by) REFERENCES auth_user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO reading_token_settings (setting_key, setting_value, description) VALUES
    ('library_checkin_quota', '5', 'Jumlah token/sesi yang diterbitkan pada satu check-in Buku Tamu Perpustakaan Daerah.'),
    ('library_checkin_valid_days', '0', 'Masa berlaku token check-in; 0 berarti berakhir pada pukul 23:59 hari yang sama.'),
    ('library_checkin_daily_limit', '1', 'Maksimum penerbitan token check-in per member dalam satu hari.'),
    ('request_default_quota', '3', 'Jumlah token/sesi default pada permohonan luar zona.'),
    ('request_valid_days', '7', 'Masa berlaku token permohonan setelah disetujui.'),
    ('outside_session_charge', '1', 'Jumlah token yang dipotong saat mulai membaca satu buku dari luar zona.')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO sys_page (code, module, title, route, description, is_active)
VALUES ('reading_tokens.settings', 'reading_points', 'Pengaturan Token Baca', 'reading-points/token-settings', 'Mengatur kuota dan masa berlaku token check-in serta akses luar zona.', 1)
ON DUPLICATE KEY UPDATE module = VALUES(module), title = VALUES(title), route = VALUES(route), description = VALUES(description), is_active = 1;

INSERT INTO auth_role_permission (role_id, page_id, can_view, can_create, can_edit, can_delete, can_export, can_approve)
SELECT r.id, p.id, 1, 0, 1, 0, 0, 0
FROM auth_role r JOIN sys_page p ON p.code = 'reading_tokens.settings'
WHERE r.code IN ('SUPERADMIN', 'ADMIN')
ON DUPLICATE KEY UPDATE can_view = 1, can_edit = 1;

INSERT INTO sys_menu (parent_id, page_id, menu_area, menu_key, title, icon, url, sort_order, is_visible, is_active, is_locked)
SELECT parent.id, p.id, 'MAIN', 'reading_tokens.settings', 'Pengaturan Token', 'ti ti-adjustments', 'reading-points/token-settings', 35, 1, 1, 0
FROM sys_menu parent JOIN sys_page p ON p.code = 'reading_tokens.settings'
WHERE parent.menu_key = 'digital_services'
ON DUPLICATE KEY UPDATE parent_id = VALUES(parent_id), page_id = VALUES(page_id), title = VALUES(title), icon = VALUES(icon), url = VALUES(url), sort_order = VALUES(sort_order), is_visible = 1, is_active = 1;
