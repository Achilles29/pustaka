-- English RPG Season 2 vertical slice.
-- This migration is intentionally isolated from Season 1 episode/scene data.

CREATE TABLE IF NOT EXISTS learn_english_rpg_world_maps (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(60) NOT NULL,
    title VARCHAR(160) NOT NULL,
    subtitle VARCHAR(200) NULL,
    description TEXT NULL,
    width SMALLINT UNSIGNED NOT NULL DEFAULT 18,
    height SMALLINT UNSIGNED NOT NULL DEFAULT 12,
    layout_json LONGTEXT NOT NULL,
    start_x SMALLINT UNSIGNED NOT NULL DEFAULT 2,
    start_y SMALLINT UNSIGNED NOT NULL DEFAULT 9,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rpg_world_map_code (code),
    KEY idx_rpg_world_map_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learn_english_rpg_world_npcs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    map_id INT UNSIGNED NOT NULL,
    code VARCHAR(60) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(120) NULL,
    avatar VARCHAR(30) NOT NULL DEFAULT '🧑',
    x SMALLINT UNSIGNED NOT NULL,
    y SMALLINT UNSIGNED NOT NULL,
    greeting VARCHAR(255) NOT NULL,
    dialogue_json LONGTEXT NOT NULL,
    vocabulary_word VARCHAR(100) NULL,
    vocabulary_meaning VARCHAR(150) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rpg_world_npc_code (map_id, code),
    KEY idx_rpg_world_npc_map (map_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learn_english_rpg_world_quests (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    map_id INT UNSIGNED NOT NULL,
    code VARCHAR(60) NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    giver_npc_code VARCHAR(60) NOT NULL,
    objective_type VARCHAR(40) NOT NULL DEFAULT 'talk_to_npcs',
    objective_target SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    objective_codes_json LONGTEXT NOT NULL,
    reward_xp INT UNSIGNED NOT NULL DEFAULT 0,
    reward_item_code VARCHAR(60) NULL,
    reward_item_name VARCHAR(120) NULL,
    reward_item_icon VARCHAR(30) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rpg_world_quest_code (map_id, code),
    KEY idx_rpg_world_quest_map (map_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learn_english_rpg_world_player_states (
    user_id BIGINT UNSIGNED NOT NULL,
    map_id INT UNSIGNED NOT NULL,
    x SMALLINT UNSIGNED NOT NULL,
    y SMALLINT UNSIGNED NOT NULL,
    direction ENUM('up','down','left','right') NOT NULL DEFAULT 'down',
    world_xp INT UNSIGNED NOT NULL DEFAULT 0,
    last_seen_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, map_id),
    KEY idx_rpg_world_state_seen (map_id, last_seen_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learn_english_rpg_world_player_quests (
    user_id BIGINT UNSIGNED NOT NULL,
    quest_id INT UNSIGNED NOT NULL,
    status ENUM('active','completed','claimed') NOT NULL DEFAULT 'active',
    progress SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    visited_json LONGTEXT NOT NULL,
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    claimed_at DATETIME NULL,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, quest_id),
    KEY idx_rpg_world_quest_user (user_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS learn_english_rpg_world_interactions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    map_id INT UNSIGNED NOT NULL,
    npc_id INT UNSIGNED NULL,
    quest_id INT UNSIGNED NULL,
    interaction_type VARCHAR(40) NOT NULL,
    payload_json LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_rpg_world_interaction_user (user_id, created_at),
    KEY idx_rpg_world_interaction_map (map_id, interaction_type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO learn_english_rpg_seasons
    (code, title, subtitle, description, cover_emoji, color, is_active, sort_order)
VALUES
    ('season_2', 'World of Living Stories', 'Season 2 · The Living Map', 'Explore a living village, speak with its people, and complete English quests through conversation.', 'castle', '#167495', 1, 2)
ON DUPLICATE KEY UPDATE
    title = VALUES(title), subtitle = VALUES(subtitle), description = VALUES(description),
    cover_emoji = VALUES(cover_emoji), color = VALUES(color), is_active = VALUES(is_active), sort_order = VALUES(sort_order);

INSERT INTO learn_english_rpg_world_maps
    (code, title, subtitle, description, width, height, layout_json, start_x, start_y, is_active)
SELECT
    'season2_village', 'Lanternbrook Village', 'A village that remembers every kind word',
    'Find three missing story sigils by exploring the village and speaking English with its people.',
    18, 12,
    '["##################","#####.......######","#####.......######","#####.......######","##..#.......#..###","#.......##.......#","#####...##.....###","#####...##.....###","#####.......######","##..#.......#..###","#..........#####.#","##################"]',
    6, 10, 1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM learn_english_rpg_world_maps WHERE code = 'season2_village');

INSERT INTO learn_english_rpg_world_npcs
    (map_id, code, name, role, avatar, x, y, greeting, dialogue_json, vocabulary_word, vocabulary_meaning, is_active)
SELECT m.id, d.code, d.name, d.role, d.avatar, d.x, d.y, d.greeting, d.dialogue_json, d.word, d.meaning, 1
FROM learn_english_rpg_world_maps m
JOIN (
    SELECT 'elder_mira' code, 'Mira' name, 'Keeper of the Lantern Archive' role, '🧙‍♀️' avatar, 3 x, 3 y,
        'Welcome, traveler. Our village needs a careful listener.' greeting,
        '[{"en":"Three story sigils are missing. Will you help me find them?","id":"Three story sigils are missing. Will you help me find them?"},{"en":"Speak with Eli, Nova, and Ren. Each one remembers a different clue.","id":"Speak with Eli, Nova, and Ren. Each one remembers a different clue."}]' dialogue_json,
        'sigil' word, 'a symbol or seal' meaning
    UNION ALL SELECT 'gardener_eli', 'Eli', 'Gardener of the Listening Grove', '🧑‍🌾', 13, 3,
        'The garden is quiet, but every leaf has a story.',
        '[{"en":"I heard a whisper near the old bridge. It sounded like a promise.","id":"I heard a whisper near the old bridge. It sounded like a promise."},{"en":"The green sigil rested beside a seed of hope.","id":"The green sigil rested beside a seed of hope."}]',
        'whisper', 'a very soft voice'
    UNION ALL SELECT 'trader_nova', 'Nova', 'Traveling Story Merchant', '🧝‍♀️', 14, 8,
        'A good bargain begins with a clear question.',
        '[{"en":"A silver sigil was traded for a promise to share knowledge.","id":"A silver sigil was traded for a promise to share knowledge."},{"en":"Ask what the traveler learned, not only what the traveler found.","id":"Ask what the traveler learned, not only what the traveler found."}]',
        'bargain', 'a fair trade or deal'
    UNION ALL SELECT 'scout_ren', 'Ren', 'Scout of the Story Trails', '🏹', 5, 9,
        'Stay alert. The smallest sign can guide a brave explorer.',
        '[{"en":"The blue sigil is hidden where three paths meet.","id":"The blue sigil is hidden where three paths meet."},{"en":"A patient explorer notices details before choosing a path.","id":"A patient explorer notices details before choosing a path."}]',
        'patient', 'able to wait calmly'
) d ON m.code = 'season2_village'
WHERE NOT EXISTS (SELECT 1 FROM learn_english_rpg_world_npcs n WHERE n.map_id = m.id AND n.code = d.code);

-- Keep an existing installation aligned with the illustrated village map.
UPDATE learn_english_rpg_world_maps
SET start_x = 6, start_y = 10,
    layout_json = '["##################","#####.......######","#####.......######","#####.......######","##..#.......#..###","#.......##.......#","#####...##.....###","#####...##.....###","#####.......######","##..#.......#..###","#..........#####.#","##################"]'
WHERE code = 'season2_village';

UPDATE learn_english_rpg_world_npcs n
JOIN learn_english_rpg_world_maps m ON m.id = n.map_id
SET n.x = CASE n.code
    WHEN 'elder_mira' THEN 6
    WHEN 'gardener_eli' THEN 11
    WHEN 'trader_nova' THEN 14
    WHEN 'scout_ren' THEN 5
    ELSE n.x END,
    n.y = CASE n.code
    WHEN 'elder_mira' THEN 3
    WHEN 'gardener_eli' THEN 3
    WHEN 'trader_nova' THEN 4
    WHEN 'scout_ren' THEN 10
    ELSE n.y END
WHERE m.code = 'season2_village';

INSERT INTO learn_english_rpg_world_quests
    (map_id, code, title, description, giver_npc_code, objective_type, objective_target, objective_codes_json, reward_xp, reward_item_code, reward_item_name, reward_item_icon, is_active)
SELECT m.id, 'missing_story_sigils', 'The Missing Story Sigils',
    'Help Mira gather three clues from Lanternbrook villagers. Listen to their dialogue and discover the hidden key words.',
    'elder_mira', 'talk_to_npcs', 3, '["gardener_eli","trader_nova","scout_ren"]', 50,
    'lantern_sigil', 'Lantern Story Sigil', '🏮', 1
FROM learn_english_rpg_world_maps m
WHERE m.code = 'season2_village'
  AND NOT EXISTS (SELECT 1 FROM learn_english_rpg_world_quests q WHERE q.map_id = m.id AND q.code = 'missing_story_sigils');

UPDATE learn_english_rpg_world_quests
SET description = 'Follow eight connected conversations across Lanternbrook. Answer each English question, revisit villagers to connect their clues, and return to Mira for the final story check.'
WHERE code = 'missing_story_sigils';
