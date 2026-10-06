-- English RPG campaign seasons. Existing episodes belong to Season 1.
CREATE TABLE IF NOT EXISTS learn_english_rpg_seasons (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(50) NOT NULL,
    title VARCHAR(160) NOT NULL,
    subtitle VARCHAR(200) NULL,
    description TEXT NULL,
    cover_emoji VARCHAR(20) NOT NULL DEFAULT 'map',
    color VARCHAR(20) NOT NULL DEFAULT '#6750b7',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_english_rpg_season_code (code),
    KEY idx_english_rpg_season_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO learn_english_rpg_seasons (code,title,subtitle,description,cover_emoji,color,is_active,sort_order)
SELECT 'season_1','The First Quest','The Lost Library Campaign','A first journey to find the magic book, meet new friends, and build the courage to speak English.','book','#6750b7',1,1
WHERE NOT EXISTS (SELECT 1 FROM learn_english_rpg_seasons WHERE code='season_1');

SET @season_one_id := (SELECT id FROM learn_english_rpg_seasons WHERE code='season_1' LIMIT 1);
SET @add_season_id_sql := IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='learn_english_rpg_episodes' AND COLUMN_NAME='season_id')=0,
    'ALTER TABLE learn_english_rpg_episodes ADD COLUMN season_id INT UNSIGNED NULL AFTER id, ADD KEY idx_rpg_episode_season (season_id, is_active, id)',
    'SELECT 1'
);
PREPARE add_season_id_stmt FROM @add_season_id_sql;
EXECUTE add_season_id_stmt;
DEALLOCATE PREPARE add_season_id_stmt;

UPDATE learn_english_rpg_episodes SET season_id=@season_one_id WHERE season_id IS NULL;

SET @add_season_fk_sql := IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='learn_english_rpg_episodes' AND CONSTRAINT_NAME='fk_rpg_episode_season')=0,
    'ALTER TABLE learn_english_rpg_episodes ADD CONSTRAINT fk_rpg_episode_season FOREIGN KEY (season_id) REFERENCES learn_english_rpg_seasons(id) ON DELETE RESTRICT',
    'SELECT 1'
);
PREPARE add_season_fk_stmt FROM @add_season_fk_sql;
EXECUTE add_season_fk_stmt;
DEALLOCATE PREPARE add_season_fk_stmt;
