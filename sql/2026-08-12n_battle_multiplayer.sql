ALTER TABLE learn_battle_rooms
  ADD COLUMN IF NOT EXISTS max_players SMALLINT UNSIGNED NULL DEFAULT 2 AFTER question_ids;

CREATE TABLE IF NOT EXISTS learn_battle_participants (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  player_name VARCHAR(120) NOT NULL,
  is_host TINYINT(1) NOT NULL DEFAULT 0,
  score INT UNSIGNED NOT NULL DEFAULT 0,
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  finished TINYINT(1) NOT NULL DEFAULT 0,
  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  finished_at DATETIME(6) NULL,
  UNIQUE KEY uq_battle_room_user (room_id, user_id),
  KEY idx_battle_room_finish (room_id, finished, finished_at),
  CONSTRAINT fk_battle_participant_room FOREIGN KEY (room_id) REFERENCES learn_battle_rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO learn_battle_participants
  (room_id,user_id,player_name,is_host,score,progress,finished,joined_at,finished_at)
SELECT id,host_user_id,COALESCE(host_name,'Host'),1,host_score,host_progress,host_finished,created_at,
       IF(host_finished=1,COALESCE(finished_at,started_at),NULL)
FROM learn_battle_rooms WHERE host_user_id IS NOT NULL;

INSERT IGNORE INTO learn_battle_participants
  (room_id,user_id,player_name,is_host,score,progress,finished,joined_at,finished_at)
SELECT id,guest_user_id,COALESCE(guest_name,'Guest'),0,guest_score,guest_progress,guest_finished,COALESCE(started_at,created_at),
       IF(guest_finished=1,finished_at,NULL)
FROM learn_battle_rooms WHERE guest_user_id IS NOT NULL;
