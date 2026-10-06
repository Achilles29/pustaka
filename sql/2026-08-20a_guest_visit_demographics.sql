CREATE TABLE IF NOT EXISTS `member_visit_demographics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `visit_id` bigint(20) unsigned NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `age_group` varchar(30) NOT NULL,
  `education_id` varchar(80) DEFAULT NULL,
  `education_label` varchar(160) NOT NULL,
  `profession_id` varchar(80) DEFAULT NULL,
  `profession_label` varchar(160) NOT NULL,
  `people_count` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_visit_demographics_visit` (`visit_id`),
  KEY `idx_visit_demographics_gender` (`gender`),
  KEY `idx_visit_demographics_education` (`education_id`),
  KEY `idx_visit_demographics_profession` (`profession_id`),
  CONSTRAINT `fk_visit_demographics_visit` FOREIGN KEY (`visit_id`) REFERENCES `member_visits` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
