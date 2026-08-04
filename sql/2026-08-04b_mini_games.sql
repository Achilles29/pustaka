-- ============================================================
-- Modul Pembelajaran Inklusif: Mini Games
-- Pustaka Digital Rembang
-- Dibuat: 2026-08-04
-- ============================================================

-- ────────────────────────────────────────────────────────────
-- 1. Tipe game yang tersedia (CRUD admin: aktif/nonaktif)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_game_types` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`        VARCHAR(40)  NOT NULL COMMENT 'e.g. memory_match, speed_math, word_scramble',
  `name`        VARCHAR(100) NOT NULL,
  `description` TEXT         NULL,
  `icon`        VARCHAR(60)  NOT NULL DEFAULT 'ti-puzzle',
  `color`       VARCHAR(20)  NOT NULL DEFAULT '#6366f1',
  `needs_content` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = butuh konten DB; 0 = konten generate JS',
  `config_schema` TEXT       NULL COMMENT 'JSON: opsi konfigurasi per sesi (jumlah kartu dll)',
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `sort_order`  SMALLINT     NOT NULL DEFAULT 0,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_game_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `learn_game_types` (`code`, `name`, `description`, `icon`, `color`, `needs_content`, `config_schema`, `sort_order`) VALUES
('memory_match',  'Memory Match',  'Balik kartu dan cocokkan pasangan kata dengan definisinya. Latih memori dan kosakata.', 'ti-cards',       '#8b5cf6', 1,
 '{"pairs":{"label":"Jumlah Pasangan","type":"select","options":[4,6,8,10,12],"default":6}}', 1),
('speed_math',    'Hitung Cepat',  'Jawab soal matematika sebanyak mungkin sebelum waktu habis. Tingkatkan kecepatan hitung!', 'ti-math-function', '#f59e0b', 0,
 '{"duration":{"label":"Durasi (detik)","type":"select","options":[30,60,90,120],"default":60},"operators":{"label":"Operasi","type":"multicheck","options":["+","-","×","÷"],"default":["+","-"]},"max_num":{"label":"Angka maks","type":"select","options":[10,20,50,100],"default":20}}', 2),
('word_scramble', 'Susun Kata',    'Huruf-huruf diacak. Susun kembali menjadi kata yang benar. Cocok untuk belajar kosakata.', 'ti-letter-case', '#10b981', 1,
 '{"time_per_word":{"label":"Waktu per kata (detik)","type":"select","options":[10,15,20,30],"default":15},"word_count":{"label":"Jumlah kata","type":"select","options":[5,10,15,20],"default":10}}', 3)
ON DUPLICATE KEY UPDATE
  `name`         = VALUES(`name`),
  `description`  = VALUES(`description`),
  `icon`         = VALUES(`icon`),
  `color`        = VALUES(`color`),
  `needs_content`= VALUES(`needs_content`),
  `config_schema`= VALUES(`config_schema`),
  `sort_order`   = VALUES(`sort_order`);

-- ────────────────────────────────────────────────────────────
-- 2. Kategori konten game (terhubung ke mapel + jenjang)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_game_categories` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `game_type_id`   INT UNSIGNED NOT NULL,
  `grade_level_id` INT UNSIGNED NULL,
  `subject_id`     INT UNSIGNED NULL,
  `name`           VARCHAR(150) NOT NULL,
  `description`    TEXT         NULL,
  `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gametype` (`game_type_id`),
  CONSTRAINT `fk_gc_gametype` FOREIGN KEY (`game_type_id`) REFERENCES `learn_game_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 3. Set konten (kumpulan item untuk satu sesi game)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_game_content_sets` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `name`        VARCHAR(150) NOT NULL,
  `difficulty`  ENUM('easy','medium','hard') NOT NULL DEFAULT 'easy',
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `created_by`  INT UNSIGNED NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  CONSTRAINT `fk_gcs_category` FOREIGN KEY (`category_id`) REFERENCES `learn_game_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 4. Item konten (pasangan untuk Memory Match, kata untuk Susun Kata)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_game_content_items` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `set_id`           INT UNSIGNED NOT NULL,
  `term`             TEXT         NOT NULL COMMENT 'Sisi pertama: kata, pertanyaan, dll',
  `definition`       TEXT         NOT NULL COMMENT 'Sisi kedua: jawaban, definisi, pasangan',
  `term_image_url`   VARCHAR(500) NULL,
  `def_image_url`    VARCHAR(500) NULL,
  `sort_order`       SMALLINT     NOT NULL DEFAULT 0,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_set` (`set_id`),
  CONSTRAINT `fk_gci_set` FOREIGN KEY (`set_id`) REFERENCES `learn_game_content_sets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 5. Log sesi game yang dimainkan
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `learn_game_sessions` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `game_type_id`   INT UNSIGNED NOT NULL,
  `content_set_id` INT UNSIGNED NULL COMMENT 'NULL untuk game generated (speed_math dll)',
  `user_id`        INT UNSIGNED NULL COMMENT 'NULL jika pengguna belum login',
  `config`         TEXT         NULL COMMENT 'JSON config sesi ini',
  `score`          INT          NOT NULL DEFAULT 0,
  `max_score`      INT          NOT NULL DEFAULT 0,
  `completed`      TINYINT(1)   NOT NULL DEFAULT 0,
  `duration_seconds` INT        NOT NULL DEFAULT 0,
  `played_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_gs_user`     (`user_id`, `played_at`),
  KEY `idx_gs_gametype` (`game_type_id`),
  CONSTRAINT `fk_gs_gametype` FOREIGN KEY (`game_type_id`) REFERENCES `learn_game_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
