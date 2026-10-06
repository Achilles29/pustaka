CREATE TABLE IF NOT EXISTS library_self_registrations (
 library_id BIGINT UNSIGNED PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 registrant_name VARCHAR(180) NOT NULL,
 registrant_phone VARCHAR(30) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_self_registration_library FOREIGN KEY (library_id) REFERENCES libraries(id),
 CONSTRAINT fk_self_registration_user FOREIGN KEY (user_id) REFERENCES auth_user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
