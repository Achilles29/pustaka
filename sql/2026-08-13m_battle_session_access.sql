ALTER TABLE learn_battle_sessions
  ADD COLUMN IF NOT EXISTS access_mode ENUM('public','member') NOT NULL DEFAULT 'public' AFTER status;

ALTER TABLE learn_battle_sessions
  ADD KEY IF NOT EXISTS idx_battle_session_access (access_mode, status, start_time, end_time);

ALTER TABLE learn_battle_rooms
  ADD COLUMN IF NOT EXISTS host_mode ENUM('player','moderator') NOT NULL DEFAULT 'player' AFTER host_name;

ALTER TABLE learn_battle_participants MODIFY user_id INT UNSIGNED NULL;
ALTER TABLE learn_battle_participants ADD COLUMN IF NOT EXISTS guest_token CHAR(64) NULL AFTER user_id;
ALTER TABLE learn_battle_participants ADD UNIQUE KEY IF NOT EXISTS uq_battle_room_guest (room_id,guest_token);
ALTER TABLE learn_battle_rooms ADD COLUMN IF NOT EXISTS winner_participant_id INT UNSIGNED NULL AFTER winner_user_id;
ALTER TABLE learn_battle_rooms MODIFY started_at DATETIME(6) NULL;
ALTER TABLE learn_battle_sessions ADD COLUMN IF NOT EXISTS play_mode ENUM('sprint','paced') NOT NULL DEFAULT 'sprint' AFTER access_mode;
ALTER TABLE learn_battle_sessions ADD COLUMN IF NOT EXISTS question_time_seconds SMALLINT UNSIGNED NOT NULL DEFAULT 20 AFTER time_limit_seconds;
ALTER TABLE learn_battle_rooms ADD COLUMN IF NOT EXISTS play_mode ENUM('sprint','paced') NOT NULL DEFAULT 'sprint' AFTER status;
ALTER TABLE learn_battle_rooms ADD COLUMN IF NOT EXISTS current_question SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER question_count;
ALTER TABLE learn_battle_rooms ADD COLUMN IF NOT EXISTS question_started_at DATETIME(6) NULL AFTER started_at;
ALTER TABLE learn_battle_rooms ADD COLUMN IF NOT EXISTS question_time_seconds SMALLINT UNSIGNED NOT NULL DEFAULT 20 AFTER time_limit_seconds;
ALTER TABLE learn_battle_participants ADD COLUMN IF NOT EXISTS answered_question SMALLINT NULL DEFAULT NULL AFTER progress;
ALTER TABLE learn_battle_rooms ADD COLUMN IF NOT EXISTS end_reason ENUM('completed','stopped','expired') NULL AFTER finished_at;
