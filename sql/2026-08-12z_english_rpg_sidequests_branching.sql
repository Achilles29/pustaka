ALTER TABLE learn_english_rpg_profiles ADD COLUMN IF NOT EXISTS reputation INT NOT NULL DEFAULT 0 AFTER total_wrong;
ALTER TABLE learn_english_rpg_profiles ADD COLUMN IF NOT EXISTS class_energy INT UNSIGNED NOT NULL DEFAULT 0 AFTER reputation;
CREATE TABLE IF NOT EXISTS learn_english_rpg_side_quests (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,code VARCHAR(60) NOT NULL,title VARCHAR(150) NOT NULL,description VARCHAR(255) NOT NULL,icon VARCHAR(30) NOT NULL,metric ENUM('correct','listening','sentence','words','chapters') NOT NULL,target INT UNSIGNED NOT NULL,reward_item_code VARCHAR(60) NULL,reward_item_name VARCHAR(120) NULL,reward_item_icon VARCHAR(30) NULL,is_active TINYINT(1) NOT NULL DEFAULT 1,PRIMARY KEY(id),UNIQUE KEY uq_rpg_sidequest_code(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS learn_english_rpg_side_quest_claims (
 user_id BIGINT UNSIGNED NOT NULL,quest_id INT UNSIGNED NOT NULL,claimed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(user_id,quest_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS learn_english_rpg_story_choices (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,user_id BIGINT UNSIGNED NOT NULL,episode_code VARCHAR(50) NOT NULL,scene_number INT UNSIGNED NOT NULL,choice_code VARCHAR(60) NOT NULL,choice_text VARCHAR(255) NOT NULL,reputation_delta INT NOT NULL DEFAULT 0,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id),UNIQUE KEY uq_rpg_story_choice(user_id,episode_code,scene_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO learn_english_rpg_side_quests(code,title,description,icon,metric,target,reward_item_code,reward_item_name,reward_item_icon) VALUES
('word_hunter','Word Hunter','Kuasai 20 kosakata berbeda.','📜','words',20,'scholar_scroll','Scholar Scroll','📜'),
('keen_ears','Keen Ears','Menangkan 15 Listening Challenge.','🎧','listening',15,'echo_earring','Echo Earring','🎧'),
('sentence_smith','Sentence Smith','Selesaikan 15 Sentence Forge.','🔨','sentence',15,'grammar_hammer','Grammar Hammer','🔨'),
('battle_veteran','Battle Veteran','Jawab benar 50 tantangan.','🏅','correct',50,'veteran_medal','Veteran Medal','🏅'),
('world_walker','World Walker','Selesaikan 3 chapter.','🗺️','chapters',3,'world_compass','World Compass','🧭')
ON DUPLICATE KEY UPDATE title=VALUES(title),description=VALUES(description),target=VALUES(target),is_active=1;
