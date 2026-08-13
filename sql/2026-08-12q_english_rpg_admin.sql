CREATE TABLE IF NOT EXISTS `learn_english_rpg_episodes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `code` VARCHAR(50) NOT NULL,
  `title` VARCHAR(160) NOT NULL, `subtitle` VARCHAR(200) NULL,
  `description` TEXT NULL, `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_rpg_episode_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `learn_english_rpg_scenes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `episode_id` INT UNSIGNED NOT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 1, `place` VARCHAR(150) NOT NULL,
  `speaker` VARCHAR(100) NOT NULL, `emoji` VARCHAR(30) NOT NULL,
  `dialogue` TEXT NOT NULL, `translation` TEXT NULL, `prompt` VARCHAR(255) NOT NULL,
  `choices_json` LONGTEXT NOT NULL, `vocabulary_word` VARCHAR(100) NULL,
  `vocabulary_meaning` VARCHAR(150) NULL, `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `idx_rpg_scene_episode` (`episode_id`,`sort_order`),
  CONSTRAINT `fk_rpg_scene_episode` FOREIGN KEY (`episode_id`) REFERENCES `learn_english_rpg_episodes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `learn_english_rpg_episodes` (`code`,`title`,`subtitle`,`description`,`is_active`)
VALUES ('lost_library','The Lost Library','Episode 1 · The Whispering Book','Petualangan mencari buku ajaib sambil belajar percakapan Inggris dasar.',1)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

INSERT INTO `sys_page` (`code`,`module`,`title`,`route`,`description`,`is_active`)
VALUES ('learn_english_rpg.index','Pembelajaran','English RPG','learn-english-rpg','Kelola episode dan adegan English Quest RPG',1)
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`),`route`=VALUES(`route`),`is_active`=1;
SET @learn_group=(SELECT `id` FROM `sys_menu` WHERE `menu_key`='learn.group' LIMIT 1);
INSERT INTO `sys_menu` (`parent_id`,`page_id`,`menu_area`,`menu_key`,`title`,`icon`,`url`,`sort_order`,`is_visible`,`is_active`,`is_locked`)
VALUES (@learn_group,(SELECT `id` FROM `sys_page` WHERE `code`='learn_english_rpg.index'),'MAIN','learn.english_rpg','English RPG','ti ti-sword','learn-english-rpg',5,1,1,0)
ON DUPLICATE KEY UPDATE `parent_id`=VALUES(`parent_id`),`page_id`=VALUES(`page_id`),`title`=VALUES(`title`),`url`=VALUES(`url`),`is_active`=1,`is_visible`=1;
INSERT INTO `auth_role_permission` (`role_id`,`page_id`,`can_view`,`can_create`,`can_edit`,`can_delete`,`can_export`,`can_approve`)
SELECT r.id,p.id,1,1,1,1,0,0 FROM `auth_role` r CROSS JOIN `sys_page` p
WHERE p.code='learn_english_rpg.index' AND (r.id=2 OR UPPER(r.name) IN ('ADMIN','SUPERADMIN'))
ON DUPLICATE KEY UPDATE `can_view`=1,`can_create`=1,`can_edit`=1,`can_delete`=1;
