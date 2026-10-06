-- Additive, independent local operations. No legacy catalog/member/loan row is reassigned.
CREATE TABLE IF NOT EXISTS network_books (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 title VARCHAR(255) NOT NULL,
 author VARCHAR(180) NULL,
 isbn VARCHAR(80) NULL,
 publisher VARCHAR(180) NULL,
 publish_year VARCHAR(4) NULL,
 classification VARCHAR(80) NULL,
 format ENUM('physical','digital') NOT NULL DEFAULT 'physical',
 digital_url VARCHAR(500) NULL,
 description TEXT NULL,
 status ENUM('draft','published') NOT NULL DEFAULT 'draft',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL,
 deleted_at DATETIME NULL,
 UNIQUE KEY uq_network_books_scope (library_id,id),
 KEY idx_network_books_listing (library_id,deleted_at,status),
 CONSTRAINT fk_network_books_library FOREIGN KEY (library_id) REFERENCES libraries(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS network_items (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 book_id BIGINT UNSIGNED NOT NULL,
 barcode VARCHAR(120) NOT NULL,
 inventory_no VARCHAR(120) NULL,
 rack VARCHAR(120) NULL,
 status ENUM('available','loaned','damaged','missing','withdrawn') NOT NULL DEFAULT 'available',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL,
 deleted_at DATETIME NULL,
 UNIQUE KEY uq_network_items_scope (library_id,id),
 UNIQUE KEY uq_network_items_barcode (library_id,barcode),
 KEY idx_network_items_book (library_id,book_id),
 CONSTRAINT fk_network_items_book FOREIGN KEY (library_id,book_id) REFERENCES network_books(library_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS network_members (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 member_no VARCHAR(120) NOT NULL,
 full_name VARCHAR(180) NOT NULL,
 birth_date DATE NULL,
 gender ENUM('L','P') NULL,
 phone VARCHAR(40) NULL,
 email VARCHAR(180) NULL,
 address TEXT NULL,
 status ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL,
 deleted_at DATETIME NULL,
 UNIQUE KEY uq_network_members_scope (library_id,id),
 UNIQUE KEY uq_network_members_number (library_id,member_no),
 KEY idx_network_members_listing (library_id,deleted_at,status),
 CONSTRAINT fk_network_members_library FOREIGN KEY (library_id) REFERENCES libraries(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS network_loans (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 member_id BIGINT UNSIGNED NOT NULL,
 item_id BIGINT UNSIGNED NOT NULL,
 loaned_at DATETIME NOT NULL,
 due_at DATETIME NOT NULL,
 returned_at DATETIME NULL,
 renewal_count INT UNSIGNED NOT NULL DEFAULT 0,
 created_by BIGINT UNSIGNED NOT NULL,
 returned_by BIGINT UNSIGNED NULL,
 return_note VARCHAR(500) NULL,
 UNIQUE KEY uq_network_loans_scope (library_id,id),
 KEY idx_network_loans_member (library_id,member_id,returned_at),
 KEY idx_network_loans_item (library_id,item_id,returned_at),
 KEY idx_network_loans_period (library_id,loaned_at),
 CONSTRAINT fk_network_loans_member FOREIGN KEY (library_id,member_id) REFERENCES network_members(library_id,id),
 CONSTRAINT fk_network_loans_item FOREIGN KEY (library_id,item_id) REFERENCES network_items(library_id,id),
 CONSTRAINT fk_network_loans_actor FOREIGN KEY (created_by) REFERENCES auth_user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS network_visits (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 member_id BIGINT UNSIGNED NULL,
 guest_name VARCHAR(180) NULL,
 purpose VARCHAR(120) NOT NULL,
 channel ENUM('offline','online') NOT NULL DEFAULT 'offline',
 visited_at DATETIME NOT NULL,
 created_by BIGINT UNSIGNED NOT NULL,
 KEY idx_network_visits_period (library_id,visited_at),
 CONSTRAINT fk_network_visits_library FOREIGN KEY (library_id) REFERENCES libraries(id),
 CONSTRAINT fk_network_visits_member FOREIGN KEY (library_id,member_id) REFERENCES network_members(library_id,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS network_settings (
 library_id BIGINT UNSIGNED PRIMARY KEY,
 loan_days SMALLINT UNSIGNED NOT NULL DEFAULT 7,
 max_loans SMALLINT UNSIGNED NOT NULL DEFAULT 3,
 max_renewals SMALLINT UNSIGNED NOT NULL DEFAULT 1,
 updated_at DATETIME NULL,
 CONSTRAINT fk_network_settings_library FOREIGN KEY (library_id) REFERENCES libraries(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS network_audit (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 actor_id BIGINT UNSIGNED NOT NULL,
 event VARCHAR(100) NOT NULL,
 entity_id BIGINT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_network_audit_scope (library_id,id),
 CONSTRAINT fk_network_audit_library FOREIGN KEY (library_id) REFERENCES libraries(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO auth_role (code,name,description,level,scope_type,is_system,is_active)
SELECT 'LIBRARY_ADMIN','Admin Perpustakaan','Satu peran pengelola operasional perpustakaan yang ditugaskan.',30,'library',1,1
WHERE NOT EXISTS (SELECT 1 FROM auth_role WHERE code='LIBRARY_ADMIN');

INSERT INTO sys_page (code,module,title,route,description)
SELECT 'library.workspace','library_network','Operasional Perpustakaan','library-workspace','Katalog, anggota lokal, sirkulasi dan laporan per perpustakaan.'
WHERE NOT EXISTS (SELECT 1 FROM sys_page WHERE code='library.workspace');

INSERT INTO auth_role_permission (role_id,page_id,can_view,can_create,can_edit,can_delete,can_export,can_approve)
SELECT r.id,p.id,1,1,1,1,1,0 FROM auth_role r CROSS JOIN sys_page p
WHERE r.code='LIBRARY_ADMIN' AND p.code='library.workspace'
AND NOT EXISTS (SELECT 1 FROM auth_role_permission a WHERE a.role_id=r.id AND a.page_id=p.id);

INSERT INTO sys_menu (page_id,menu_key,title,icon,url,sort_order,is_visible,is_active)
SELECT p.id,'library-workspace','Operasional Perpustakaan','ti ti-building-community','library-workspace',15,1,1
FROM sys_page p WHERE p.code='library.workspace'
AND NOT EXISTS (SELECT 1 FROM sys_menu WHERE menu_key='library-workspace');
