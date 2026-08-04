-- =====================================================
-- QUIZ ENGINE MODULE — Pustaka Digital Rembang
-- Jalankan sekali di database `pustaka`
-- =====================================================

-- Jenjang Kelas
CREATE TABLE IF NOT EXISTS quiz_grade_levels (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    code         VARCHAR(20)  NOT NULL UNIQUE,
    name         VARCHAR(100) NOT NULL,
    education_level ENUM('tk','sd','smp','sma','smk','pt','umum') NOT NULL DEFAULT 'sd',
    grade_number TINYINT NULL COMMENT 'Angka kelas (1-6 SD, 7-9 SMP, 10-12 SMA)',
    sort_order   INT NOT NULL DEFAULT 0,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO quiz_grade_levels (code, name, education_level, grade_number, sort_order) VALUES
('tk_a',  'TK – Kelompok A',      'tk',   NULL,  1),
('tk_b',  'TK – Kelompok B',      'tk',   NULL,  2),
('sd_1',  'SD Kelas 1',           'sd',   1,     3),
('sd_2',  'SD Kelas 2',           'sd',   2,     4),
('sd_3',  'SD Kelas 3',           'sd',   3,     5),
('sd_4',  'SD Kelas 4',           'sd',   4,     6),
('sd_5',  'SD Kelas 5',           'sd',   5,     7),
('sd_6',  'SD Kelas 6',           'sd',   6,     8),
('smp_7', 'SMP Kelas 7',          'smp',  7,     9),
('smp_8', 'SMP Kelas 8',          'smp',  8,     10),
('smp_9', 'SMP Kelas 9',          'smp',  9,     11),
('sma_10','SMA/SMK Kelas 10',     'sma',  10,    12),
('sma_11','SMA/SMK Kelas 11',     'sma',  11,    13),
('sma_12','SMA/SMK Kelas 12',     'sma',  12,    14),
('pt',    'Perguruan Tinggi',      'pt',   NULL,  15),
('umum',  'Umum',                  'umum', NULL,  16)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Mata Pelajaran
CREATE TABLE IF NOT EXISTS quiz_subjects (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    code       VARCHAR(30)  NOT NULL UNIQUE,
    name       VARCHAR(150) NOT NULL,
    icon       VARCHAR(100) NULL DEFAULT 'ti ti-book',
    color      VARCHAR(20)  NULL DEFAULT '#4a90d9',
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO quiz_subjects (code, name, icon, color, sort_order) VALUES
('matematika',      'Matematika',            'ti ti-math-symbols',      '#e74c3c', 1),
('bahasa_indonesia','Bahasa Indonesia',       'ti ti-book-2',            '#3498db', 2),
('bahasa_inggris',  'Bahasa Inggris',         'ti ti-world',             '#2ecc71', 3),
('ipa',             'IPA / Sains',            'ti ti-flask',             '#9b59b6', 4),
('ips',             'IPS / Sosial',           'ti ti-map',               '#f39c12', 5),
('ppkn',            'PPKn',                   'ti ti-flag',              '#c0392b', 6),
('agama',           'Pendidikan Agama',       'ti ti-building-mosque',   '#27ae60', 7),
('seni_budaya',     'Seni Budaya',            'ti ti-palette',           '#e91e63', 8),
('penjaskes',       'Penjaskes',              'ti ti-run',               '#ff5722', 9),
('tik',             'Teknologi Informasi',    'ti ti-device-laptop',     '#607d8b', 10),
('sejarah',         'Sejarah',                'ti ti-clock-history',     '#795548', 11),
('geografi',        'Geografi',               'ti ti-globe',             '#009688', 12),
('ekonomi',         'Ekonomi',                'ti ti-coins',             '#ff9800', 13),
('biologi',         'Biologi',                'ti ti-leaf',              '#4caf50', 14),
('fisika',          'Fisika',                 'ti ti-atom',              '#2196f3', 15),
('kimia',           'Kimia',                  'ti ti-test-pipe',         '#673ab7', 16),
('logika',          'Logika & Berpikir Kritis','ti ti-brain',            '#00bcd4', 17),
('umum',            'Umum / Campuran',        'ti ti-star',              '#9e9e9e', 18)
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Tag Soal
CREATE TABLE IF NOT EXISTS quiz_tags (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bank Soal
CREATE TABLE IF NOT EXISTS quiz_questions (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    subject_id          INT NOT NULL,
    grade_level_id      INT NOT NULL,
    type                ENUM('multiple_choice','essay') NOT NULL DEFAULT 'multiple_choice',
    difficulty          ENUM('easy','medium','hard') NOT NULL DEFAULT 'medium',
    question_text       TEXT NOT NULL,
    question_image      VARCHAR(500) NULL,
    explanation         TEXT NULL COMMENT 'Pembahasan / kunci jawaban',
    explanation_image   VARCHAR(500) NULL,
    correct_option_index TINYINT NULL COMMENT '0=A,1=B,2=C,3=D,4=E; NULL untuk essay',
    essay_rubric        TEXT NULL COMMENT 'Rubrik penilaian essay',
    score_weight        DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    is_active           TINYINT(1) NOT NULL DEFAULT 1,
    created_by          INT NULL,
    import_batch_id     INT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at          DATETIME NULL,
    INDEX idx_subject   (subject_id),
    INDEX idx_grade     (grade_level_id),
    INDEX idx_type      (type),
    INDEX idx_difficulty(difficulty),
    INDEX idx_active    (is_active),
    FOREIGN KEY (subject_id)      REFERENCES quiz_subjects(id),
    FOREIGN KEY (grade_level_id)  REFERENCES quiz_grade_levels(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pilihan Jawaban (Pilihan Ganda, max 5 opsi A-E)
CREATE TABLE IF NOT EXISTS quiz_question_options (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    question_id  INT NOT NULL,
    option_index TINYINT NOT NULL COMMENT '0=A,1=B,2=C,3=D,4=E',
    option_text  TEXT NOT NULL,
    option_image VARCHAR(500) NULL,
    UNIQUE KEY uq_option (question_id, option_index),
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tag pivot
CREATE TABLE IF NOT EXISTS quiz_question_tags (
    question_id INT NOT NULL,
    tag_id      INT NOT NULL,
    PRIMARY KEY (question_id, tag_id),
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id)      REFERENCES quiz_tags(id)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Batch Import
CREATE TABLE IF NOT EXISTS quiz_import_batches (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    filename     VARCHAR(500) NOT NULL,
    format       ENUM('csv','xlsx','json') NOT NULL DEFAULT 'csv',
    total_rows   INT NOT NULL DEFAULT 0,
    imported     INT NOT NULL DEFAULT 0,
    skipped      INT NOT NULL DEFAULT 0,
    errors       INT NOT NULL DEFAULT 0,
    error_log    TEXT NULL,
    context_type ENUM('bank','session','competition') NOT NULL DEFAULT 'bank',
    context_id   INT NULL,
    imported_by  INT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sesi Quiz (Latihan & Kompetisi)
CREATE TABLE IF NOT EXISTS quiz_sessions (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    code                     VARCHAR(30)  NOT NULL UNIQUE,
    title                    VARCHAR(200) NOT NULL,
    type                     ENUM('practice','competition') NOT NULL DEFAULT 'practice',
    status                   ENUM('draft','open','ongoing','closed','archived') NOT NULL DEFAULT 'draft',
    subject_id               INT NULL COMMENT 'NULL = semua mapel',
    grade_level_id           INT NULL COMMENT 'NULL = semua jenjang',
    difficulty_filter        ENUM('easy','medium','hard','mixed') NOT NULL DEFAULT 'mixed',
    question_count           INT NOT NULL DEFAULT 10,
    time_limit_minutes       INT NOT NULL DEFAULT 30 COMMENT '0 = tanpa batas',
    shuffle_questions        TINYINT(1) NOT NULL DEFAULT 1,
    shuffle_options          TINYINT(1) NOT NULL DEFAULT 1,
    show_result_immediately  TINYINT(1) NOT NULL DEFAULT 1,
    allow_review             TINYINT(1) NOT NULL DEFAULT 1,
    max_attempts             INT NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
    passing_score            DECIMAL(5,2) NOT NULL DEFAULT 60.00,
    -- Kompetisi
    registration_start       DATETIME NULL,
    registration_end         DATETIME NULL,
    start_time               DATETIME NULL,
    end_time                 DATETIME NULL,
    -- Anti-fraud
    fraud_detect_tab_switch  TINYINT(1) NOT NULL DEFAULT 1,
    fraud_detect_time_anomaly TINYINT(1) NOT NULL DEFAULT 1,
    fraud_max_tab_switches   INT NOT NULL DEFAULT 3,
    fraud_action             ENUM('warn','flag','disqualify') NOT NULL DEFAULT 'flag',
    -- Konten
    description              TEXT NULL,
    instructions             TEXT NULL,
    created_by               INT NULL,
    created_at               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at               DATETIME NULL,
    INDEX idx_type   (type),
    INDEX idx_status (status),
    FOREIGN KEY (subject_id)      REFERENCES quiz_subjects(id),
    FOREIGN KEY (grade_level_id)  REFERENCES quiz_grade_levels(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Soal Kompetisi (pre-assigned)
CREATE TABLE IF NOT EXISTS quiz_session_questions (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    session_id    INT NOT NULL,
    question_id   INT NOT NULL,
    sort_order    INT NOT NULL DEFAULT 0,
    score_override DECIMAL(5,2) NULL COMMENT 'Override bobot untuk sesi ini',
    UNIQUE KEY uq_sq (session_id, question_id),
    FOREIGN KEY (session_id)  REFERENCES quiz_sessions(id)  ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Peserta (member atau non-member)
CREATE TABLE IF NOT EXISTS quiz_participants (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    session_id          INT NOT NULL,
    member_id           INT NULL,
    user_id             INT NULL,
    full_name           VARCHAR(200) NOT NULL,
    identity_number     VARCHAR(50)  NULL,
    identity_type       VARCHAR(50)  NULL,
    school_name         VARCHAR(200) NULL,
    grade_class         VARCHAR(50)  NULL,
    phone               VARCHAR(30)  NULL,
    email               VARCHAR(200) NULL,
    birth_date          DATE NULL,
    gender              ENUM('L','P') NULL,
    address             TEXT NULL,
    registration_code   VARCHAR(30) NOT NULL UNIQUE,
    registration_pin    VARCHAR(10) NOT NULL,
    registration_status ENUM('registered','confirmed','disqualified','withdrawn') NOT NULL DEFAULT 'registered',
    registered_by       INT NULL,
    registered_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notes               TEXT NULL,
    INDEX idx_session (session_id),
    INDEX idx_code    (registration_code),
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Attempt (satu kali pengerjaan)
CREATE TABLE IF NOT EXISTS quiz_attempts (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    session_id        INT NOT NULL,
    participant_id    INT NOT NULL,
    attempt_number    INT NOT NULL DEFAULT 1,
    ip_address        VARCHAR(45)  NOT NULL,
    user_agent        VARCHAR(500) NOT NULL,
    session_token     VARCHAR(64)  NOT NULL UNIQUE,
    status            ENUM('in_progress','submitted','timed_out','disqualified','abandoned') NOT NULL DEFAULT 'in_progress',
    fraud_score       INT NOT NULL DEFAULT 0,
    is_flagged        TINYINT(1) NOT NULL DEFAULT 0,
    flag_reason       TEXT NULL,
    question_order    TEXT NOT NULL COMMENT 'JSON array of question IDs',
    started_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    submitted_at      DATETIME NULL,
    time_spent_seconds INT NULL,
    total_score       DECIMAL(8,2) NULL,
    max_possible_score DECIMAL(8,2) NULL,
    percentage        DECIMAL(5,2) NULL,
    is_passed         TINYINT(1) NULL,
    essay_graded      TINYINT(1) NOT NULL DEFAULT 0,
    graded_by         INT NULL,
    graded_at         DATETIME NULL,
    INDEX idx_session     (session_id),
    INDEX idx_participant (participant_id),
    INDEX idx_token       (session_token),
    FOREIGN KEY (session_id)     REFERENCES quiz_sessions(id),
    FOREIGN KEY (participant_id) REFERENCES quiz_participants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jawaban per Attempt
CREATE TABLE IF NOT EXISTS quiz_attempt_answers (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id          INT NOT NULL,
    question_id         INT NOT NULL,
    selected_option     TINYINT NULL COMMENT 'Pilihan ganda: 0=A…4=E',
    essay_answer        TEXT NULL,
    time_spent_seconds  INT NOT NULL DEFAULT 0,
    answered_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_correct          TINYINT(1) NULL COMMENT 'NULL = essay belum dinilai',
    score_earned        DECIMAL(5,2) NULL,
    essay_score         DECIMAL(5,2) NULL,
    essay_feedback      TEXT NULL,
    answer_change_count TINYINT NOT NULL DEFAULT 0,
    UNIQUE KEY uq_aq (attempt_id, question_id),
    FOREIGN KEY (attempt_id)  REFERENCES quiz_attempts(id)  ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log Anti-Fraud
CREATE TABLE IF NOT EXISTS quiz_fraud_logs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id   INT NOT NULL,
    event_type   ENUM('tab_switch','window_blur','copy_attempt','paste_attempt','right_click','devtools_open','time_anomaly','suspicious_pattern') NOT NULL,
    event_detail TEXT NULL COMMENT 'JSON detail',
    question_id  INT NULL,
    occurred_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attempt (attempt_id),
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log Aktivitas Quiz (audit trail)
CREATE TABLE IF NOT EXISTS quiz_activity_log (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    action         VARCHAR(100) NOT NULL,
    entity_type    VARCHAR(50)  NOT NULL,
    entity_id      INT NULL,
    user_id        INT NULL,
    participant_id INT NULL,
    attempt_id     INT NULL,
    ip_address     VARCHAR(45)  NULL,
    detail         TEXT NULL COMMENT 'JSON',
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
