CREATE TABLE IF NOT EXISTS `learn_english_rpg_progress` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `episode_code` VARCHAR(50) NOT NULL DEFAULT 'lost_library',
  `current_scene` INT UNSIGNED NOT NULL DEFAULT 0,
  `xp` INT UNSIGNED NOT NULL DEFAULT 0,
  `hearts` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `correct_answers` INT UNSIGNED NOT NULL DEFAULT 0,
  `wrong_answers` INT UNSIGNED NOT NULL DEFAULT 0,
  `vocabulary_json` LONGTEXT NULL,
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `completed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_english_rpg_user_episode` (`user_id`, `episode_code`),
  KEY `idx_english_rpg_completed` (`is_completed`, `completed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

