SET @current_db := DATABASE();

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @current_db AND TABLE_NAME = 'auth_user' AND COLUMN_NAME = 'source_system') = 0,
  'ALTER TABLE `auth_user` ADD COLUMN `source_system` varchar(50) NULL AFTER `library_id`',
  'DO 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @current_db AND TABLE_NAME = 'auth_user' AND COLUMN_NAME = 'source_table') = 0,
  'ALTER TABLE `auth_user` ADD COLUMN `source_table` varchar(80) NULL AFTER `source_system`',
  'DO 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @current_db AND TABLE_NAME = 'auth_user' AND COLUMN_NAME = 'source_id') = 0,
  'ALTER TABLE `auth_user` ADD COLUMN `source_id` varchar(80) NULL AFTER `source_table`',
  'DO 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @current_db AND TABLE_NAME = 'auth_user' AND COLUMN_NAME = 'source_role') = 0,
  'ALTER TABLE `auth_user` ADD COLUMN `source_role` varchar(160) NULL AFTER `source_id`',
  'DO 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = @current_db AND TABLE_NAME = 'auth_user' AND INDEX_NAME = 'ux_auth_user_source') = 0,
  'CREATE UNIQUE INDEX `ux_auth_user_source` ON `auth_user` (`source_system`, `source_table`, `source_id`)',
  'DO 0'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `auth_role` (`code`, `name`, `description`, `level`, `scope_type`, `is_system`, `is_active`)
SELECT 'ADMIN', 'Admin', 'Admin operasional perpustakaan', 20, 'library', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM `auth_role` WHERE `code` = 'ADMIN');

SET @admin_role_id := (SELECT `id` FROM `auth_role` WHERE `code` = 'ADMIN' LIMIT 1);
SET @main_library_id := (
  SELECT `id`
  FROM `libraries`
  WHERE `status` = 'active'
    AND (
      `code` = '001'
      OR LOWER(`name`) LIKE '%perpustakaan%daerah%'
      OR LOWER(`name`) LIKE '%perpsutakaan%daerah%'
      OR LOWER(`name`) LIKE '%perpusda%'
    )
  ORDER BY (`code` = '001') DESC, `id` ASC
  LIMIT 1
);
SET @default_admin_password_hash := '$2y$10$WJTIdlVi.l1fAn.nLaeQyeQTfId83SEQBJaUcbF78mCBjDmneDSji';
SET @has_inlislite_users := (
  SELECT COUNT(*)
  FROM information_schema.TABLES
  WHERE TABLE_SCHEMA = 'inlislite_v3'
    AND TABLE_NAME = 'users'
);

SET @sql := IF(
  @has_inlislite_users > 0,
  'INSERT INTO `auth_user` (`username`, `email`, `password_hash`, `full_name`, `library_id`, `status`, `force_password_change`, `source_system`, `source_table`, `source_id`, `source_role`, `created_at`)
   SELECT
     LEFT(TRIM(u.`username`), 80),
     CASE
       WHEN NULLIF(TRIM(COALESCE(u.`EmailAddress`, '''')), '''') IS NOT NULL
        AND NOT EXISTS (
          SELECT 1 FROM `auth_user` e
          WHERE e.`email` = LEFT(TRIM(u.`EmailAddress`), 180)
        )
       THEN LEFT(TRIM(u.`EmailAddress`), 180)
       ELSE NULL
     END,
     @default_admin_password_hash,
     LEFT(COALESCE(NULLIF(TRIM(u.`Fullname`), ''''), TRIM(u.`username`)), 180),
     @main_library_id,
     ''active'',
     1,
     ''inlislite_v3'',
     ''users'',
     CAST(u.`ID` AS CHAR),
     LEFT(CONCAT(COALESCE(r.`Code`, ''''), CASE WHEN r.`Code` IS NULL OR r.`Name` IS NULL THEN '''' ELSE '' - '' END, COALESCE(r.`Name`, '''')), 160),
     NOW()
   FROM `inlislite_v3`.`users` u
   LEFT JOIN `inlislite_v3`.`roles` r ON r.`ID` = u.`Role_id`
   WHERE CAST(u.`IsActive` AS UNSIGNED) = 1
     AND NULLIF(TRIM(u.`username`), '''') IS NOT NULL
     AND NOT EXISTS (
       SELECT 1 FROM `auth_user` existing_source
       WHERE existing_source.`source_system` = ''inlislite_v3''
         AND existing_source.`source_table` = ''users''
         AND existing_source.`source_id` = CAST(u.`ID` AS CHAR)
     )
     AND NOT EXISTS (
       SELECT 1 FROM `auth_user` existing_username
       WHERE existing_username.`username` = LEFT(TRIM(u.`username`), 80)
     )',
  'SELECT ''SKIP: database inlislite_v3.users tidak ditemukan'' AS message'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT IGNORE INTO `auth_user_role` (`user_id`, `role_id`)
SELECT u.`id`, @admin_role_id
FROM `auth_user` u
WHERE u.`source_system` = 'inlislite_v3'
  AND u.`source_table` = 'users'
  AND @admin_role_id IS NOT NULL;

UPDATE `auth_user`
SET `status` = 'suspended',
    `updated_at` = NOW()
WHERE (`username` = 'admin' AND `email` = 'admin@pustaka.local' AND `full_name` = 'Admin Pustaka')
   OR (`username` = 'pemustaka' AND `email` = 'pemustaka@pustaka.local' AND `full_name` LIKE 'Pemustaka%');
