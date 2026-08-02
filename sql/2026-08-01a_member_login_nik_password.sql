DROP TEMPORARY TABLE IF EXISTS `tmp_member_nik_usernames`;

CREATE TEMPORARY TABLE `tmp_member_nik_usernames` AS
SELECT u.`id` AS `auth_user_id`, m.`identity_number` AS `new_username`
FROM `auth_user` u
JOIN `members` m ON m.`auth_user_id` = u.`id`
LEFT JOIN `auth_user` other_user
  ON other_user.`username` = m.`identity_number`
  AND other_user.`id` <> u.`id`
WHERE m.`deleted_at` IS NULL
  AND m.`identity_number` IS NOT NULL
  AND m.`identity_number` <> ''
  AND other_user.`id` IS NULL;

UPDATE `auth_user` u
JOIN `tmp_member_nik_usernames` t ON t.`auth_user_id` = u.`id`
SET u.`username` = t.`new_username`,
    u.`updated_at` = NOW();

DROP TEMPORARY TABLE IF EXISTS `tmp_member_nik_usernames`;

UPDATE `auth_user` u
JOIN `members` m ON m.`auth_user_id` = u.`id`
SET u.`password_hash` = '$2y$10$zTdR.604eAH20PyVCOSHwOUzSg4dVMy0GA0bJyoY5iaUftHVOUgFe',
    u.`force_password_change` = 1,
    u.`updated_at` = NOW()
WHERE m.`deleted_at` IS NULL;
