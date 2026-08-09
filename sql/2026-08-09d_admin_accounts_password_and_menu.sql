SET @admin_password_hash := '$2y$10$WJTIdlVi.l1fAn.nLaeQyeQTfId83SEQBJaUcbF78mCBjDmneDSji';

UPDATE `auth_user` u
JOIN `auth_user_role` ur ON ur.`user_id` = u.`id`
JOIN `auth_role` r ON r.`id` = ur.`role_id`
SET u.`password_hash` = @admin_password_hash,
    u.`force_password_change` = 1,
    u.`updated_at` = NOW()
WHERE u.`source_system` = 'inlislite_v3'
  AND u.`source_table` = 'users'
  AND r.`code` <> 'USER';

UPDATE `sys_page`
SET `title` = 'Daftar Admin',
    `route` = 'rbac/admins',
    `updated_at` = NOW()
WHERE `code` = 'auth.users.index';

UPDATE `sys_menu`
SET `title` = 'Daftar Admin',
    `url` = 'rbac/admins',
    `icon` = 'ti ti-shield-check',
    `updated_at` = NOW()
WHERE `menu_key` = 'system.users';
