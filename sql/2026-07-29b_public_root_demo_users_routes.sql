UPDATE `sys_page`
SET `route` = 'admin/index'
WHERE `code` = 'dashboard.index';

UPDATE `sys_menu`
SET `url` = 'admin'
WHERE `menu_key` = 'dashboard';

INSERT INTO `auth_user` (`username`, `email`, `password_hash`, `full_name`, `status`, `force_password_change`) VALUES
('admin', 'admin@pustaka.local', '$2y$10$KUmmwFVXyIUIK6mEbtLtbOvHP.ryk4iuNhtt/KJgdzCepjXApPTUW', 'Admin Pustaka', 'active', 1),
('pemustaka', 'pemustaka@pustaka.local', '$2y$10$KUmmwFVXyIUIK6mEbtLtbOvHP.ryk4iuNhtt/KJgdzCepjXApPTUW', 'Pemustaka Demo', 'active', 1)
ON DUPLICATE KEY UPDATE
  `email` = VALUES(`email`),
  `full_name` = VALUES(`full_name`),
  `status` = VALUES(`status`);

INSERT IGNORE INTO `auth_user_role` (`user_id`, `role_id`)
SELECT u.`id`, r.`id`
FROM `auth_user` u
JOIN `auth_role` r ON r.`code` = 'ADMIN'
WHERE u.`username` = 'admin';

INSERT IGNORE INTO `auth_user_role` (`user_id`, `role_id`)
SELECT u.`id`, r.`id`
FROM `auth_user` u
JOIN `auth_role` r ON r.`code` = 'USER'
WHERE u.`username` = 'pemustaka';
