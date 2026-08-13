CREATE TABLE IF NOT EXISTS learn_battle_sessions (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, code VARCHAR(20) NOT NULL UNIQUE,
 title VARCHAR(180) NOT NULL, description TEXT NULL,
 status ENUM('draft','open','closed','archived') NOT NULL DEFAULT 'draft',
 question_count TINYINT UNSIGNED NOT NULL DEFAULT 5,
 time_limit_seconds INT UNSIGNED NOT NULL DEFAULT 300,
 max_players SMALLINT UNSIGNED NULL DEFAULT 2,
 shuffle_questions TINYINT(1) NOT NULL DEFAULT 1,
 start_time DATETIME NULL, end_time DATETIME NULL, created_by INT UNSIGNED NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
 KEY idx_battle_session_status(status,start_time,end_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS learn_battle_session_questions (
 session_id INT UNSIGNED NOT NULL, question_id INT UNSIGNED NOT NULL, sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 1,
 PRIMARY KEY(session_id,question_id), KEY idx_bsq_order(session_id,sort_order),
 CONSTRAINT fk_bsq_session FOREIGN KEY(session_id) REFERENCES learn_battle_sessions(id) ON DELETE CASCADE,
 CONSTRAINT fk_bsq_question FOREIGN KEY(question_id) REFERENCES learn_battle_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE learn_battle_rooms ADD COLUMN IF NOT EXISTS battle_session_id INT UNSIGNED NULL AFTER id;
ALTER TABLE learn_battle_rooms ADD COLUMN IF NOT EXISTS time_limit_seconds INT UNSIGNED NOT NULL DEFAULT 300 AFTER max_players;
INSERT INTO learn_battle_sessions(code,title,description,status,question_count,time_limit_seconds,max_players,shuffle_questions,created_at)
SELECT 'BATTLE-UMUM','Battle Pengetahuan Umum','Sesi bawaan untuk kompatibilitas room dan bank soal lama.','open',5,300,2,1,NOW()
WHERE NOT EXISTS(SELECT 1 FROM learn_battle_sessions);
SET @default_battle_session=(SELECT id FROM learn_battle_sessions ORDER BY id LIMIT 1);
INSERT IGNORE INTO learn_battle_session_questions(session_id,question_id,sort_order) SELECT @default_battle_session,id,id FROM learn_battle_questions WHERE is_active=1;
UPDATE learn_battle_rooms SET battle_session_id=@default_battle_session WHERE battle_session_id IS NULL;
