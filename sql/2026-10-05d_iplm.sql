ALTER TABLE library_types ADD COLUMN IF NOT EXISTS sort_order INT NOT NULL DEFAULT 100;
CREATE TABLE IF NOT EXISTS library_subtypes (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_type_id INT UNSIGNED NOT NULL,
 code VARCHAR(60) NOT NULL UNIQUE,
 name VARCHAR(180) NOT NULL,
 description VARCHAR(500) NULL,
 sort_order INT NOT NULL DEFAULT 100,
 iplm_eligible TINYINT NOT NULL DEFAULT 0,
 is_active TINYINT NOT NULL DEFAULT 1,
 UNIQUE KEY uq_subtype_parent (id,library_type_id),
 CONSTRAINT fk_subtype_type FOREIGN KEY (library_type_id) REFERENCES library_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE libraries ADD COLUMN IF NOT EXISTS library_subtype_id INT UNSIGNED NULL;
ALTER TABLE libraries ADD COLUMN IF NOT EXISTS institution_name VARCHAR(180) NULL;
ALTER TABLE libraries ADD COLUMN IF NOT EXISTS npp VARCHAR(80) NULL;
CREATE TABLE IF NOT EXISTS iplm_periods (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 year SMALLINT UNSIGNED NOT NULL UNIQUE,
 title VARCHAR(180) NOT NULL,
 start_date DATE NOT NULL,
 end_date DATE NOT NULL,
 dates_confirmed TINYINT NOT NULL DEFAULT 0,
 population INT UNSIGNED NULL,
 population_note VARCHAR(500) NULL,
 state ENUM('draft','open','closed') NOT NULL DEFAULT 'draft',
 version INT UNSIGNED NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS iplm_fields (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 code VARCHAR(80) NOT NULL UNIQUE,
 excel_column VARCHAR(4) NULL,
 label VARCHAR(500) NOT NULL,
 definition TEXT NOT NULL,
 section VARCHAR(30) NOT NULL,
 kind ENUM('text','number','money','select','url') NOT NULL,
 options_json LONGTEXT NULL,
 evidence_hint TEXT NULL,
 is_required TINYINT NOT NULL DEFAULT 0,
 evidence_required TINYINT NOT NULL DEFAULT 0,
 is_active TINYINT NOT NULL DEFAULT 1,
 sort_order INT NOT NULL DEFAULT 100,
 version INT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS iplm_submissions (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 library_id BIGINT UNSIGNED NOT NULL,
 period_id INT UNSIGNED NOT NULL,
 schema_json LONGTEXT NOT NULL,
 values_json LONGTEXT NOT NULL,
 evidence_json LONGTEXT NOT NULL,
 baseline_json LONGTEXT NOT NULL,
 resolutions_json LONGTEXT NOT NULL,
 status ENUM('draft','submitted','revision','verified') NOT NULL DEFAULT 'draft',
 review_note TEXT NULL,
 version INT UNSIGNED NOT NULL DEFAULT 1,
 created_by BIGINT UNSIGNED NOT NULL,
 updated_by BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 submitted_at DATETIME NULL,
 verified_at DATETIME NULL,
 deleted_at DATETIME NULL,
 active_slot TINYINT UNSIGNED NULL DEFAULT 1,
 UNIQUE KEY uq_iplm_library_period (library_id,period_id,active_slot),
 KEY idx_iplm_period_status (period_id,status),
 CONSTRAINT fk_iplm_library FOREIGN KEY (library_id) REFERENCES libraries(id),
 CONSTRAINT fk_iplm_period FOREIGN KEY (period_id) REFERENCES iplm_periods(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS iplm_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 submission_id BIGINT UNSIGNED NULL,
 actor_id BIGINT UNSIGNED NOT NULL,
 event VARCHAR(80) NOT NULL,
 payload_json LONGTEXT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_iplm_history_submission (submission_id,id),
 CONSTRAINT fk_iplm_history_submission FOREIGN KEY (submission_id) REFERENCES iplm_submissions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
