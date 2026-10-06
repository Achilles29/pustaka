ALTER TABLE libraries ADD COLUMN IF NOT EXISTS institution_status ENUM('negeri','swasta','belum_diketahui') NULL;
CREATE TABLE IF NOT EXISTS library_source_records (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 source_system VARCHAR(50) NOT NULL,
 source_id VARCHAR(80) NOT NULL,
 library_id BIGINT UNSIGNED NULL,
 source_row INT UNSIGNED NOT NULL,
 classification VARCHAR(60) NOT NULL,
 decision VARCHAR(60) NOT NULL,
 note TEXT NULL,
 source_json LONGTEXT NOT NULL,
 source_sha256 CHAR(64) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_source (source_system,source_id),
 CONSTRAINT fk_source_library FOREIGN KEY (library_id) REFERENCES libraries(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS library_activation_codes (
 library_id BIGINT UNSIGNED PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 token_hash CHAR(64) NOT NULL,
 expires_at DATETIME NOT NULL,
 issued_by BIGINT UNSIGNED NOT NULL,
 issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 claimed_at DATETIME NULL,
 CONSTRAINT fk_activation_library FOREIGN KEY (library_id) REFERENCES libraries(id),
 CONSTRAINT fk_activation_user FOREIGN KEY (user_id) REFERENCES auth_user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
