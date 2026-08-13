CREATE TABLE IF NOT EXISTS learn_english_rpg_profiles (
 user_id BIGINT UNSIGNED NOT NULL,hero_name VARCHAR(80) NULL,avatar VARCHAR(30) NOT NULL,class_code ENUM('word_knight','grammar_mage','listening_ranger','speaking_bard') NOT NULL DEFAULT 'word_knight',streak_days INT UNSIGNED NOT NULL DEFAULT 1,longest_streak INT UNSIGNED NOT NULL DEFAULT 1,last_active_date DATE NULL,total_correct INT UNSIGNED NOT NULL DEFAULT 0,total_wrong INT UNSIGNED NOT NULL DEFAULT 0,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,PRIMARY KEY(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS learn_english_rpg_activity (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,user_id BIGINT UNSIGNED NOT NULL,episode_code VARCHAR(50) NOT NULL,scene_number INT UNSIGNED NOT NULL,challenge_type VARCHAR(30) NOT NULL,is_correct TINYINT(1) NOT NULL,vocabulary_word VARCHAR(100) NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,PRIMARY KEY(id),KEY idx_rpg_activity_daily(user_id,created_at),KEY idx_rpg_activity_word(user_id,vocabulary_word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS learn_english_rpg_word_mastery (
 user_id BIGINT UNSIGNED NOT NULL,word VARCHAR(100) NOT NULL,meaning VARCHAR(150) NULL,correct_count INT UNSIGNED NOT NULL DEFAULT 0,wrong_count INT UNSIGNED NOT NULL DEFAULT 0,mastery_score INT NOT NULL DEFAULT 0,last_seen_at DATETIME NOT NULL,PRIMARY KEY(user_id,word),KEY idx_rpg_mastery_score(user_id,mastery_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
