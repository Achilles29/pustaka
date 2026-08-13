-- Tag turunan katalog Buku Pelajaran: jenjang/kelas dan mata pelajaran.
-- Aman dijalankan ulang. Tag dipisahkan dari data bibliografi agar satu judul
-- dapat berada di lebih dari satu jenjang atau mata pelajaran.

CREATE TABLE IF NOT EXISTS `textbook_grade_levels` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(40) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `education_level` VARCHAR(30) NOT NULL DEFAULT 'Lainnya',
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_textbook_grade_code` (`code`),
  KEY `idx_textbook_grade_active_sort` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `textbook_subjects` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(60) NOT NULL,
  `name` VARCHAR(140) NOT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_textbook_subject_code` (`code`),
  KEY `idx_textbook_subject_active_sort` (`is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `book_textbook_grade_tags` (
  `book_id` BIGINT UNSIGNED NOT NULL,
  `grade_level_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`book_id`, `grade_level_id`),
  KEY `idx_book_textbook_grade_level` (`grade_level_id`),
  CONSTRAINT `fk_book_textbook_grade_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_book_textbook_grade_level` FOREIGN KEY (`grade_level_id`) REFERENCES `textbook_grade_levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `book_textbook_subject_tags` (
  `book_id` BIGINT UNSIGNED NOT NULL,
  `subject_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`book_id`, `subject_id`),
  KEY `idx_book_textbook_subject` (`subject_id`),
  CONSTRAINT `fk_book_textbook_subject_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_book_textbook_subject_subject` FOREIGN KEY (`subject_id`) REFERENCES `textbook_subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `textbook_grade_levels` (`code`, `name`, `education_level`, `sort_order`) VALUES
('paud', 'PAUD / Fase Fondasi', 'PAUD', 10),
('sd-1', 'SD / MI Kelas 1', 'SD / MI', 101), ('sd-2', 'SD / MI Kelas 2', 'SD / MI', 102),
('sd-3', 'SD / MI Kelas 3', 'SD / MI', 103), ('sd-4', 'SD / MI Kelas 4', 'SD / MI', 104),
('sd-5', 'SD / MI Kelas 5', 'SD / MI', 105), ('sd-6', 'SD / MI Kelas 6', 'SD / MI', 106),
('smp-7', 'SMP / MTs Kelas 7', 'SMP / MTs', 207), ('smp-8', 'SMP / MTs Kelas 8', 'SMP / MTs', 208),
('smp-9', 'SMP / MTs Kelas 9', 'SMP / MTs', 209),
('sma-10', 'SMA / MA Kelas 10', 'SMA / MA', 310), ('sma-11', 'SMA / MA Kelas 11', 'SMA / MA', 311), ('sma-12', 'SMA / MA Kelas 12', 'SMA / MA', 312),
('smk-10', 'SMK / MAK Kelas 10', 'SMK / MAK', 410), ('smk-11', 'SMK / MAK Kelas 11', 'SMK / MAK', 411), ('smk-12', 'SMK / MAK Kelas 12', 'SMK / MAK', 412),
('smk-lintas', 'SMK / MAK lintas kelas', 'SMK / MAK', 420),
('slb', 'SLB / Pendidikan Khusus', 'SLB', 500), ('lintas-jenjang', 'Lintas Jenjang / Referensi Guru', 'Lainnya', 900)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `education_level` = VALUES(`education_level`), `sort_order` = VALUES(`sort_order`), `is_active` = 1;

INSERT INTO `textbook_subjects` (`code`, `name`, `sort_order`) VALUES
('agama-islam', 'Pendidikan Agama Islam dan Budi Pekerti', 10),
('agama-kristen', 'Pendidikan Agama Kristen dan Budi Pekerti', 11),
('agama-katolik', 'Pendidikan Agama Katolik dan Budi Pekerti', 12),
('agama-hindu', 'Pendidikan Agama Hindu dan Budi Pekerti', 13),
('agama-buddha', 'Pendidikan Agama Buddha dan Budi Pekerti', 14),
('agama-konghucu', 'Pendidikan Agama Khonghucu dan Budi Pekerti', 15), ('kepercayaan', 'Pendidikan Kepercayaan terhadap Tuhan YME', 16),
('pancasila', 'Pendidikan Pancasila', 20), ('bahasa-indonesia', 'Bahasa Indonesia', 30), ('bahasa-inggris', 'Bahasa Inggris', 31),
('matematika', 'Matematika', 40), ('ipas', 'IPAS', 50), ('ipa', 'Ilmu Pengetahuan Alam (IPA)', 51), ('ips', 'Ilmu Pengetahuan Sosial (IPS)', 52),
('informatika', 'Informatika', 60), ('pjok', 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 70),
('seni-musik', 'Seni Musik', 80), ('seni-rupa', 'Seni Rupa', 81), ('seni-tari', 'Seni Tari', 82), ('seni-teater', 'Seni Teater', 83),
('prakarya', 'Prakarya dan Kewirausahaan', 90), ('sejarah', 'Sejarah', 100), ('geografi', 'Geografi', 101), ('ekonomi', 'Ekonomi', 102),
('sosiologi', 'Sosiologi', 103), ('biologi', 'Biologi', 104), ('fisika', 'Fisika', 105), ('kimia', 'Kimia', 106),
('vokasi', 'Kejuruan / Vokasi', 120), ('pendidikan-khusus', 'Pendidikan Khusus', 130)
 ,('antropologi', 'Antropologi', 107), ('bahasa-asing', 'Bahasa Asing', 32), ('stem', 'STEM / Sains Terapan', 110), ('paud-terpadu', 'Pembelajaran PAUD Terpadu', 140), ('literasi', 'Literasi dan Perbukuan', 141), ('pembelajaran-terpadu', 'Pembelajaran Terpadu', 142)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `sort_order` = VALUES(`sort_order`), `is_active` = 1;

-- Memetakan 613 judul impor yang telah masuk kategori Buku Pelajaran.
-- Batas setelah angka/romawi mencegah "Kelas I" keliru terbaca sebagai II/III/IV.
INSERT IGNORE INTO `book_textbook_grade_tags` (`book_id`, `grade_level_id`)
SELECT b.id, g.id
FROM `books` b
JOIN `book_content_categories` c ON c.id = b.content_category_id AND c.code = 'buku-pelajaran'
JOIN `textbook_grade_levels` g ON (
  (g.code = 'sd-1' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(1|I)([^0-9IVX]|$)') OR
  (g.code = 'sd-2' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(2|II)([^0-9IVX]|$)') OR
  (g.code = 'sd-3' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(3|III)([^0-9IVX]|$)') OR
  (g.code = 'sd-4' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(4|IV)([^0-9IVX]|$)') OR
  (g.code = 'sd-5' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(5|V)([^0-9IVX]|$)') OR
  (g.code = 'sd-6' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(6|VI)([^0-9IVX]|$)') OR
  (g.code = 'smp-7' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(7|VII)([^0-9IVX]|$)') OR
  (g.code = 'smp-8' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(8|VIII)([^0-9IVX]|$)') OR
  (g.code = 'smp-9' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(9|IX)([^0-9IVX]|$)') OR
  (g.code = 'sma-10' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(10|X)([^0-9IVX]|$)') OR
  (g.code = 'sma-11' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(11|XI)([^0-9IVX]|$)') OR
  (g.code = 'sma-12' AND UPPER(b.title) REGEXP 'KELAS[[:space:]]+(12|XII)([^0-9IVX]|$)') OR
  (g.code = 'smk-lintas' AND UPPER(b.title) REGEXP 'SMK/MAK') OR
  (g.code = 'slb' AND UPPER(b.title) REGEXP '(SDLB|SMPLB|SMALB|PENDIDIKAN KHUSUS)') OR
  (g.code = 'paud' AND UPPER(b.title) REGEXP '(FASE FONDASI|BELAJAR DAN BERMAIN|JATI DIRI|NILAI AGAMA DAN BUDI PEKERTI)')
)
WHERE b.status = 'published' AND b.deleted_at IS NULL;

-- Empat panduan nasional tidak menyebut kelas tertentu; tetap ditemukan pada
-- filter khusus tanpa menebak-nebak kelasnya.
INSERT IGNORE INTO `book_textbook_grade_tags` (`book_id`, `grade_level_id`)
SELECT b.id, g.id
FROM `books` b
JOIN `book_content_categories` c ON c.id = b.content_category_id AND c.code = 'buku-pelajaran'
JOIN `textbook_grade_levels` g ON g.code = 'lintas-jenjang'
LEFT JOIN `book_textbook_grade_tags` existing_tag ON existing_tag.book_id = b.id
WHERE b.status = 'published' AND b.deleted_at IS NULL AND existing_tag.book_id IS NULL;

-- Sebagian judul lama dipotong saat migrasi. Nomor inventaris internal berikut
-- masih dapat dipetakan tepat karena berada dalam urutan paket mapel yang sama.
INSERT IGNORE INTO `book_textbook_grade_tags` (`book_id`, `grade_level_id`)
SELECT x.book_id, g.id
FROM (
  SELECT 14116 AS book_id, 'sd-1' AS grade_code UNION ALL SELECT 14130, 'sd-2' UNION ALL SELECT 14143, 'sd-3' UNION ALL SELECT 14155, 'sd-4'
  UNION ALL SELECT 14168, 'smp-9' UNION ALL SELECT 14181, 'sd-5' UNION ALL SELECT 14194, 'sd-6' UNION ALL SELECT 14208, 'smp-7'
  UNION ALL SELECT 14223, 'smp-8' UNION ALL SELECT 14287, 'sma-10' UNION ALL SELECT 14396, 'sma-11' UNION ALL SELECT 14476, 'sma-12'
) x
JOIN `textbook_grade_levels` g ON g.code = x.grade_code;

INSERT IGNORE INTO `book_textbook_grade_tags` (`book_id`, `grade_level_id`)
SELECT b.id, g.id
FROM `books` b
JOIN `book_content_categories` c ON c.id = b.content_category_id AND c.code = 'buku-pelajaran'
JOIN `textbook_grade_levels` g ON g.code = CASE WHEN UPPER(b.title) REGEXP '(SDLB|SMPLB|SMALB|DISABILITAS|HAMBATAN)' THEN 'slb' WHEN UPPER(b.title) REGEXP 'FASE FONDASI' THEN 'paud' END
WHERE b.status = 'published' AND b.deleted_at IS NULL;

INSERT IGNORE INTO `book_textbook_subject_tags` (`book_id`, `subject_id`)
SELECT b.id, s.id
FROM `books` b
JOIN `book_content_categories` c ON c.id = b.content_category_id AND c.code = 'buku-pelajaran'
JOIN `textbook_subjects` s ON (
  (s.code = 'agama-islam' AND UPPER(b.title) LIKE '%PENDIDIKAN AGAMA ISLAM%') OR
  (s.code = 'agama-kristen' AND UPPER(b.title) LIKE '%PENDIDIKAN AGAMA KRISTEN%') OR
  (s.code = 'agama-katolik' AND UPPER(b.title) LIKE '%PENDIDIKAN AGAMA KATOLIK%') OR
  (s.code = 'agama-hindu' AND UPPER(b.title) LIKE '%PENDIDIKAN AGAMA HINDU%') OR
  (s.code = 'agama-buddha' AND UPPER(b.title) LIKE '%PENDIDIKAN AGAMA BUDDHA%') OR
  (s.code = 'agama-konghucu' AND (UPPER(b.title) LIKE '%PENDIDIKAN AGAMA KONGHUCU%' OR UPPER(b.title) LIKE '%PENDIDIKAN AGAMA KHONGHUCU%')) OR
  (s.code = 'kepercayaan' AND UPPER(b.title) LIKE '%PENDIDIKAN KEPERCAYAAN%') OR
  (s.code = 'pancasila' AND UPPER(b.title) LIKE '%PENDIDIKAN PANCASILA%') OR
  (s.code = 'bahasa-indonesia' AND UPPER(b.title) LIKE '%BAHASA INDONESIA%') OR
  (s.code = 'bahasa-inggris' AND (UPPER(b.title) LIKE '%BAHASA INGGRIS%' OR UPPER(b.title) LIKE '%ENGLISH FOR NUSANTARA%')) OR
  (s.code = 'matematika' AND UPPER(b.title) LIKE '%MATEMATIKA%') OR
  (s.code = 'ipas' AND UPPER(b.title) REGEXP '(^|[[:space:][:punct:]])IPAS([[:space:][:punct:]]|$)') OR
  (s.code = 'ipa' AND (UPPER(b.title) LIKE '%ILMU PENGETAHUAN ALAM%' OR UPPER(b.title) REGEXP '(^|[[:space:][:punct:]])IPA([[:space:][:punct:]]|$)')) OR
  (s.code = 'ips' AND (UPPER(b.title) LIKE '%ILMU PENGETAHUAN SOSIAL%' OR UPPER(b.title) REGEXP '(^|[[:space:][:punct:]])IPS([[:space:][:punct:]]|$)')) OR
  (s.code = 'informatika' AND (UPPER(b.title) LIKE '%INFORMATIKA%' OR UPPER(b.title) LIKE '%KODING%')) OR
  (s.code = 'pjok' AND (UPPER(b.title) LIKE '%PENDIDIKAN JASMANI%' OR UPPER(b.title) LIKE '%PJOK%')) OR
  (s.code = 'seni-musik' AND UPPER(b.title) LIKE '%SENI MUSIK%') OR
  (s.code = 'seni-rupa' AND UPPER(b.title) LIKE '%SENI RUPA%') OR
  (s.code = 'seni-tari' AND UPPER(b.title) LIKE '%SENI TARI%') OR
  (s.code = 'seni-teater' AND UPPER(b.title) LIKE '%SENI TEATER%') OR
  (s.code = 'prakarya' AND UPPER(b.title) LIKE '%PRAKARYA%') OR
  (s.code = 'sejarah' AND UPPER(b.title) LIKE '%SEJARAH%') OR
  (s.code = 'geografi' AND UPPER(b.title) LIKE '%GEOGRAFI%') OR
  (s.code = 'ekonomi' AND UPPER(b.title) LIKE '%EKONOMI%') OR
  (s.code = 'sosiologi' AND UPPER(b.title) LIKE '%SOSIOLOGI%') OR
  (s.code = 'antropologi' AND UPPER(b.title) LIKE '%ANTROPOLOGI%') OR
  (s.code = 'bahasa-asing' AND (UPPER(b.title) LIKE '%BAHASA KOREA%' OR UPPER(b.title) LIKE '%BAHASA MANDARIN%')) OR
  (s.code = 'biologi' AND UPPER(b.title) LIKE '%BIOLOGI%') OR
  (s.code = 'fisika' AND UPPER(b.title) LIKE '%FISIKA%') OR
  (s.code = 'kimia' AND UPPER(b.title) LIKE '%KIMIA%') OR
  (s.code = 'vokasi' AND UPPER(b.title) REGEXP '(SMK|MAK|KEAHLIAN|VOKASI|ANIMASI|MANAJEMEN LOGISTIK|TEKNIK ENERGI)') OR
  (s.code = 'pendidikan-khusus' AND UPPER(b.title) REGEXP '(SDLB|SMPLB|SMALB|PENDIDIKAN KHUSUS|DISABILITAS|HAMBATAN)') OR
  (s.code = 'paud-terpadu' AND UPPER(b.title) REGEXP '(FASE FONDASI|BELAJAR DAN BERMAIN|JATI DIRI)') OR
  (s.code = 'stem' AND UPPER(b.title) LIKE '%STEM%') OR
  (s.code = 'literasi' AND UPPER(b.title) REGEXP '(LITERASI|PERBUKUAN|SIBI)') OR
  (s.code = 'pembelajaran-terpadu' AND UPPER(b.title) REGEXP '(NILAI AGAMA DAN BUDI PEKERTI|PROJEK PENGUATAN PROFIL PELAJAR PANCASILA)')
)
WHERE b.status = 'published' AND b.deleted_at IS NULL;
