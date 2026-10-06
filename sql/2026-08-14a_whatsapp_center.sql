-- WA Center Pustaka: sesi engine, template, antrian, log dan persetujuan pesan promosi.
CREATE TABLE IF NOT EXISTS wa_session (
  id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
  status VARCHAR(24) NOT NULL DEFAULT 'DISCONNECTED',
  phone_number VARCHAR(32) NULL,
  qr_data LONGTEXT NULL,
  bot_api_url VARCHAR(255) NOT NULL DEFAULT 'http://127.0.0.1:3071',
  bot_api_token VARCHAR(128) NOT NULL,
  node_path VARCHAR(500) NULL,
  last_ping_at DATETIME NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE wa_session ADD COLUMN IF NOT EXISTS is_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER bot_api_token;
INSERT IGNORE INTO wa_session(id,bot_api_token) VALUES(1,'ganti-token-wa-pustaka');

CREATE TABLE IF NOT EXISTS wa_template (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  template_code VARCHAR(80) NOT NULL UNIQUE, name VARCHAR(160) NOT NULL,
  category ENUM('REGISTRATION','LOAN','OVERDUE','REMINDER','PROMO','INFO','CUSTOM') NOT NULL DEFAULT 'CUSTOM',
  body TEXT NOT NULL, is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_by BIGINT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_wa_template_category(category), CONSTRAINT fk_wa_template_user FOREIGN KEY(created_by) REFERENCES auth_user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS wa_outbox (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  event_code VARCHAR(80) NOT NULL, template_id BIGINT UNSIGNED NULL, member_id BIGINT UNSIGNED NULL,
  phone_number VARCHAR(32) NOT NULL, recipient_name VARCHAR(180) NULL, message TEXT NOT NULL,
  status ENUM('PENDING','SENDING','SENT','FAILED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  scheduled_at DATETIME NULL, sent_at DATETIME NULL, retry_count SMALLINT UNSIGNED NOT NULL DEFAULT 0, error_message VARCHAR(500) NULL,
  context_json TEXT NULL, created_by BIGINT UNSIGNED NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_wa_outbox_status_schedule(status,scheduled_at), KEY idx_wa_outbox_member(member_id), KEY idx_wa_outbox_event(event_code),
  CONSTRAINT fk_wa_outbox_template FOREIGN KEY(template_id) REFERENCES wa_template(id) ON DELETE SET NULL,
  CONSTRAINT fk_wa_outbox_member FOREIGN KEY(member_id) REFERENCES members(id) ON DELETE SET NULL,
  CONSTRAINT fk_wa_outbox_user FOREIGN KEY(created_by) REFERENCES auth_user(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO wa_template(template_code,name,category,body) VALUES
('member_registration_received','Pendaftaran diterima','REGISTRATION','Halo {member_name}, pendaftaran anggota Pustaka Digital Rembang telah kami terima. Nomor pengajuan: {request_code}. Kami akan memberi kabar setelah diverifikasi.'),
('member_registration_approved','Pendaftaran disetujui','REGISTRATION','Halo {member_name}, pendaftaran Anda telah disetujui. Nomor anggota: {member_no}. Silakan masuk ke Pustaka Digital Rembang untuk melihat kartu anggota Anda.'),
('loan_request_status','Status request buku','LOAN','Halo {member_name}, request buku "{book_title}" ({request_code}) berstatus: {status}. {staff_note}'),
('loan_due_reminder','Pengingat pengembalian','REMINDER','Halo {member_name}, buku "{book_title}" perlu dikembalikan paling lambat {due_date}. Terima kasih.'),
('loan_overdue','Pemberitahuan keterlambatan','OVERDUE','Halo {member_name}, buku "{book_title}" telah melewati jatuh tempo {due_date} selama {late_days} hari. Mohon segera dikembalikan atau hubungi petugas.');

INSERT INTO sys_page(code,title,module,route,description,is_active) VALUES
('wa.dashboard','WA Center','WA Center','wa','Dashboard koneksi dan antrian WhatsApp',1),
('wa.templates','Template WA','WA Center','wa/templates','Template otomatis dan pesan WhatsApp',1),
('wa.outbox','Antrian WA','WA Center','wa/outbox','Antrian dan log pengiriman WhatsApp',1),
('wa.settings','Pengaturan WA','WA Center','wa/settings','Sesi QR dan koneksi WhatsApp Engine',1)
ON DUPLICATE KEY UPDATE title=VALUES(title),module=VALUES(module),route=VALUES(route),description=VALUES(description),is_active=1;

INSERT INTO sys_menu(parent_id,page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active,is_locked)
VALUES(NULL,NULL,'MAIN','wa.center','WA Center','ti ti-brand-whatsapp',NULL,65,1,1,0)
ON DUPLICATE KEY UPDATE title=VALUES(title),icon=VALUES(icon),is_visible=1,is_active=1;
INSERT INTO sys_menu(parent_id,page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active,is_locked)
SELECT parent.id,p.id,'MAIN','wa.dashboard','Dashboard WA','ti ti-message-circle-2','wa',1,1,1,0 FROM sys_menu parent JOIN sys_page p ON p.code='wa.dashboard' WHERE parent.menu_key='wa.center'
ON DUPLICATE KEY UPDATE parent_id=VALUES(parent_id),page_id=VALUES(page_id),url=VALUES(url),is_visible=1,is_active=1;
INSERT INTO sys_menu(parent_id,page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active,is_locked)
SELECT parent.id,p.id,'MAIN','wa.templates','Template Pesan','ti ti-file-text','wa/templates',2,1,1,0 FROM sys_menu parent JOIN sys_page p ON p.code='wa.templates' WHERE parent.menu_key='wa.center'
ON DUPLICATE KEY UPDATE parent_id=VALUES(parent_id),page_id=VALUES(page_id),url=VALUES(url),is_visible=1,is_active=1;
INSERT INTO sys_menu(parent_id,page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active,is_locked)
SELECT parent.id,p.id,'MAIN','wa.outbox','Antrian & Log','ti ti-send','wa/outbox',3,1,1,0 FROM sys_menu parent JOIN sys_page p ON p.code='wa.outbox' WHERE parent.menu_key='wa.center'
ON DUPLICATE KEY UPDATE parent_id=VALUES(parent_id),page_id=VALUES(page_id),url=VALUES(url),is_visible=1,is_active=1;
INSERT INTO sys_menu(parent_id,page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active,is_locked)
SELECT parent.id,p.id,'MAIN','wa.settings','Pengaturan Koneksi','ti ti-settings-2','wa/settings',4,1,1,0 FROM sys_menu parent JOIN sys_page p ON p.code='wa.settings' WHERE parent.menu_key='wa.center'
ON DUPLICATE KEY UPDATE parent_id=VALUES(parent_id),page_id=VALUES(page_id),url=VALUES(url),is_visible=1,is_active=1;

INSERT INTO auth_role_permission(role_id,page_id,can_view,can_create,can_edit,can_delete,can_export,can_approve)
SELECT r.id,p.id,1,1,1,1,1,1 FROM auth_role r JOIN sys_page p ON p.code LIKE 'wa.%' WHERE r.code IN ('SUPERADMIN','ADMIN')
ON DUPLICATE KEY UPDATE can_view=1,can_create=1,can_edit=1,can_delete=1,can_export=1,can_approve=1;
