CREATE TABLE IF NOT EXISTS learn_english_rpg_inventory (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,user_id BIGINT UNSIGNED NOT NULL,item_code VARCHAR(60) NOT NULL,item_name VARCHAR(120) NOT NULL,item_icon VARCHAR(30) NOT NULL,item_type ENUM('weapon','armor','potion','key','artifact') NOT NULL,quantity INT UNSIGNED NOT NULL DEFAULT 1,is_equipped TINYINT(1) NOT NULL DEFAULT 0,obtained_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY(id),UNIQUE KEY uq_rpg_inventory(user_id,item_code),KEY idx_rpg_inventory_user(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS learn_english_rpg_loot_log (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,user_id BIGINT UNSIGNED NOT NULL,episode_code VARCHAR(50) NOT NULL,scene_number INT UNSIGNED NOT NULL,item_code VARCHAR(60) NOT NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id),UNIQUE KEY uq_rpg_loot_scene(user_id,episode_code,scene_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
