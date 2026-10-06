-- Additive service records; no legacy ownership/status or account is rewritten.
CREATE TABLE IF NOT EXISTS library_stock_sessions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 source ENUM('legacy','network') NOT NULL,
 title VARCHAR(180) NOT NULL,
 status ENUM('open','closed','cancelled') NOT NULL DEFAULT 'open',
 open_slot TINYINT UNSIGNED NULL DEFAULT 1,
 note VARCHAR(2000) NULL,
 created_by BIGINT UNSIGNED NOT NULL,
 closed_by BIGINT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 closed_at DATETIME NULL,
 UNIQUE KEY uq_stock_open (library_id,source,open_slot),
 KEY idx_stock_library (library_id,source,id),
 CONSTRAINT fk_stock_library FOREIGN KEY (library_id) REFERENCES libraries(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS library_stock_entries (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 session_id BIGINT UNSIGNED NOT NULL,
 item_id BIGINT UNSIGNED NOT NULL,
 barcode VARCHAR(120) NOT NULL,
 title VARCHAR(255) NOT NULL,
 location VARCHAR(255) NULL,
 initial_status VARCHAR(80) NULL,
 on_loan TINYINT UNSIGNED NOT NULL DEFAULT 0,
 observed ENUM('good','damaged') NULL,
 note VARCHAR(500) NULL,
 scanned_by BIGINT UNSIGNED NULL,
 scanned_at DATETIME NULL,
 UNIQUE KEY uq_stock_item (session_id,item_id),
 KEY idx_stock_barcode (session_id,barcode),
 CONSTRAINT fk_stock_entry_session FOREIGN KEY (session_id) REFERENCES library_stock_sessions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS network_reservations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 member_id BIGINT UNSIGNED NOT NULL,
 book_id BIGINT UNSIGNED NOT NULL,
 status ENUM('waiting','fulfilled','cancelled') NOT NULL DEFAULT 'waiting',
 active_slot TINYINT UNSIGNED NULL DEFAULT 1,
 expires_at DATETIME NOT NULL,
 created_by BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_reservation_active (library_id,member_id,book_id,active_slot),
 KEY idx_reservation_queue (library_id,book_id,status,id),
 CONSTRAINT fk_reservation_member FOREIGN KEY (library_id,member_id) REFERENCES network_members(library_id,id),
 CONSTRAINT fk_reservation_book FOREIGN KEY (library_id,book_id) REFERENCES network_books(library_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS network_registrations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 kind ENUM('new','renewal') NOT NULL DEFAULT 'new',
 member_no VARCHAR(120) NULL,
 full_name VARCHAR(180) NOT NULL,
 birth_date DATE NULL,
 gender ENUM('L','P') NULL,
 phone VARCHAR(40) NULL,
 email VARCHAR(180) NULL,
 address TEXT NULL,
 status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
 member_id BIGINT UNSIGNED NULL,
 reviewed_by BIGINT UNSIGNED NULL,
 reviewed_at DATETIME NULL,
 review_note VARCHAR(500) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_registration_scope (library_id,status,id),
 CONSTRAINT fk_registration_library FOREIGN KEY (library_id) REFERENCES libraries(id),
 CONSTRAINT fk_registration_member FOREIGN KEY (library_id,member_id) REFERENCES network_members(library_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS network_membership_terms (
 library_id BIGINT UNSIGNED NOT NULL,
 member_id BIGINT UNSIGNED NOT NULL,
 expires_on DATE NOT NULL,
 updated_by BIGINT UNSIGNED NOT NULL,
 PRIMARY KEY (library_id,member_id),
 CONSTRAINT fk_term_member FOREIGN KEY (library_id,member_id) REFERENCES network_members(library_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS network_kiosks (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL,
 expires_at DATETIME NOT NULL,
 revoked_at DATETIME NULL,
 created_by BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_kiosk_token (token_hash),
 KEY idx_kiosk_library (library_id,id),
 CONSTRAINT fk_kiosk_library FOREIGN KEY (library_id) REFERENCES libraries(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS network_public_limits (
 bucket CHAR(64) PRIMARY KEY,
 hits INT UNSIGNED NOT NULL DEFAULT 0,
 expires_at DATETIME NOT NULL,
 KEY idx_public_limit_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO sys_page (code,module,title,route,description)
SELECT 'library.services','library_network','Inventaris dan Layanan Perpustakaan','library-services/stock','Stok opname Perpusda dan jejaring dengan scope eksplisit.'
WHERE NOT EXISTS (SELECT 1 FROM sys_page WHERE code='library.services');
INSERT INTO auth_role_permission (role_id,page_id,can_view,can_create,can_edit,can_delete,can_export,can_approve)
SELECT r.id,p.id,1,1,1,0,1,1 FROM auth_role r CROSS JOIN sys_page p
WHERE r.code IN ('ADMIN','SUPERADMIN') AND p.code='library.services'
AND NOT EXISTS (SELECT 1 FROM auth_role_permission a WHERE a.role_id=r.id AND a.page_id=p.id);
INSERT INTO sys_menu (page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active)
SELECT p.id,'MAIN','library.services','Stok Opname','ti ti-clipboard-check','library-services/stock',26,1,1 FROM sys_page p
WHERE p.code='library.services' AND NOT EXISTS (SELECT 1 FROM sys_menu WHERE menu_key='library.services');
INSERT INTO sys_menu (page_id,menu_area,menu_key,title,icon,url,sort_order,is_visible,is_active)
SELECT p.id,'LIBRARY',n.menu_key,n.title,n.icon,n.url,n.sort_order,1,1 FROM sys_page p CROSS JOIN (
 SELECT 'network.stock' menu_key,'Stok Opname' title,'ti ti-clipboard-check' icon,'library-services/stock' url,35 sort_order
 UNION ALL SELECT 'network.labels','Label QR','ti ti-qrcode','library-services/labels',36
 UNION ALL SELECT 'network.cards','Kartu Anggota','ti ti-id-badge','library-services/cards',45
 UNION ALL SELECT 'network.registrations','Pendaftaran Online','ti ti-user-plus','library-services/registrations',46
 UNION ALL SELECT 'network.reservations','Reservasi','ti ti-bookmark','library-services/reservations',55
 UNION ALL SELECT 'network.kiosk','Buku Tamu Mandiri','ti ti-device-tablet','library-services/kiosk',65
) n WHERE p.code='library.workspace' AND NOT EXISTS (SELECT 1 FROM sys_menu m WHERE m.menu_key=n.menu_key);
