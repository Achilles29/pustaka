CREATE TABLE IF NOT EXISTS `inlislite_master_references` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `source_system` varchar(50) NOT NULL DEFAULT 'inlislite_v3',
  `source_table` varchar(80) NOT NULL,
  `source_id` varchar(80) NOT NULL,
  `code` varchar(120) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `source_created_at` datetime DEFAULT NULL,
  `source_updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_inlislite_master_refs_source` (`source_system`, `source_table`, `source_id`),
  KEY `idx_inlislite_master_refs_table` (`source_table`),
  KEY `idx_inlislite_master_refs_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `members`
  ADD COLUMN IF NOT EXISTS `identity_type_label` varchar(120) DEFAULT NULL AFTER `identity_type`,
  ADD COLUMN IF NOT EXISTS `gender_label` varchar(80) DEFAULT NULL AFTER `gender`,
  ADD COLUMN IF NOT EXISTS `member_type_label` varchar(160) DEFAULT NULL AFTER `member_type`,
  ADD COLUMN IF NOT EXISTS `education_label` varchar(160) DEFAULT NULL AFTER `education`,
  ADD COLUMN IF NOT EXISTS `occupation_label` varchar(160) DEFAULT NULL AFTER `occupation`,
  ADD COLUMN IF NOT EXISTS `member_status_label` varchar(120) DEFAULT NULL AFTER `status`;

ALTER TABLE `book_items`
  ADD COLUMN IF NOT EXISTS `source_location_library_id` varchar(80) DEFAULT NULL AFTER `library_id`,
  ADD COLUMN IF NOT EXISTS `source_location_id` varchar(80) DEFAULT NULL AFTER `source_location_library_id`,
  ADD COLUMN IF NOT EXISTS `source_rule_id` varchar(80) DEFAULT NULL AFTER `source_location_id`,
  ADD COLUMN IF NOT EXISTS `source_category_id` varchar(80) DEFAULT NULL AFTER `source_rule_id`,
  ADD COLUMN IF NOT EXISTS `source_media_id` varchar(80) DEFAULT NULL AFTER `source_category_id`,
  ADD COLUMN IF NOT EXISTS `source_collection_source_id` varchar(80) DEFAULT NULL AFTER `source_media_id`,
  ADD COLUMN IF NOT EXISTS `source_status_id` varchar(80) DEFAULT NULL AFTER `source_collection_source_id`,
  ADD COLUMN IF NOT EXISTS `location_library_name` varchar(180) DEFAULT NULL AFTER `location_name`,
  ADD COLUMN IF NOT EXISTS `location_room_name` varchar(180) DEFAULT NULL AFTER `location_library_name`,
  ADD COLUMN IF NOT EXISTS `rule_name` varchar(160) DEFAULT NULL AFTER `location_room_name`,
  ADD COLUMN IF NOT EXISTS `category_name` varchar(180) DEFAULT NULL AFTER `collection_type`,
  ADD COLUMN IF NOT EXISTS `media_name` varchar(160) DEFAULT NULL AFTER `category_name`,
  ADD COLUMN IF NOT EXISTS `source_name` varchar(160) DEFAULT NULL AFTER `media_name`,
  ADD COLUMN IF NOT EXISTS `status_label` varchar(120) DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `is_public` tinyint(1) NOT NULL DEFAULT 1 AFTER `status_label`,
  ADD COLUMN IF NOT EXISTS `deleted_at` datetime DEFAULT NULL AFTER `updated_at`;

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'jenis_anggota', CAST(`id` AS CHAR), NULL, CONVERT(`jenisanggota` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`jenis_anggota`
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'status_anggota', CAST(`id` AS CHAR), NULL, CONVERT(`Nama` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`status_anggota`
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'master_jenis_identitas', CAST(`id` AS CHAR), NULL, CONVERT(`Nama` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`master_jenis_identitas`
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'jenis_kelamin', CAST(`ID` AS CHAR), NULL, CONVERT(`Name` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`jenis_kelamin`
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'master_pendidikan', CAST(`id` AS CHAR), NULL, CONVERT(`Nama` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`master_pendidikan`
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'master_jenjang_pendidikan', CAST(`ID` AS CHAR), NULL, CONVERT(`jenjang_pendidikan` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`master_jenjang_pendidikan`
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'master_pekerjaan', CAST(`id` AS CHAR), NULL, CONVERT(`Pekerjaan` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`master_pekerjaan`
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'collectioncategorys', CAST(`ID` AS CHAR), CONVERT(`Code` USING utf8mb4), CONVERT(`Name` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`collectioncategorys`
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`), `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'collectionrules', CAST(`ID` AS CHAR), NULL, CONVERT(`Name` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`collectionrules`
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'collectionstatus', CAST(`ID` AS CHAR), NULL, CONVERT(`Name` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`collectionstatus`
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'collectionlocations', CAST(`ID` AS CHAR), CONVERT(`Code` USING utf8mb4), CONVERT(`Name` USING utf8mb4), CONVERT(`Description` USING utf8mb4), `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`collectionlocations`
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`), `name` = VALUES(`name`), `description` = VALUES(`description`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'locations', CAST(`ID` AS CHAR), CONVERT(`Code` USING utf8mb4), CONVERT(`Name` USING utf8mb4), CONVERT(`Description` USING utf8mb4), `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`locations`
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`), `name` = VALUES(`name`), `description` = VALUES(`description`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'location_library', CAST(`ID` AS CHAR), CONVERT(`Code` USING utf8mb4), CONVERT(`Name` USING utf8mb4), CONVERT(`Address` USING utf8mb4), `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`location_library`
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`), `name` = VALUES(`name`), `description` = VALUES(`description`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'collectionmedias', CAST(`ID` AS CHAR), CONVERT(`Code` USING utf8mb4), CONVERT(`Name` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`collectionmedias`
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`), `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'collectionsources', CAST(`ID` AS CHAR), CONVERT(`Code` USING utf8mb4), CONVERT(`Name` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`collectionsources`
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`), `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

INSERT INTO `inlislite_master_references` (`source_system`, `source_table`, `source_id`, `code`, `name`, `description`, `source_created_at`, `source_updated_at`)
SELECT 'inlislite_v3', 'tujuan_kunjungan', CAST(`ID` AS CHAR), CONVERT(`Code` USING utf8mb4), CONVERT(`TujuanKunjungan` USING utf8mb4), NULL, `CreateDate`, `UpdateDate`
FROM `inlislite_v3`.`tujuan_kunjungan`
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`), `name` = VALUES(`name`), `source_updated_at` = VALUES(`source_updated_at`);

UPDATE `members` m
JOIN `inlislite_v3`.`members` sm ON m.`source_system` = 'inlislite_v3' AND m.`source_id` = CONVERT(CAST(sm.`ID` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` ident ON ident.`source_table` = 'master_jenis_identitas' AND ident.`source_id` = CONVERT(CAST(sm.`IdentityType_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` gender ON gender.`source_table` = 'jenis_kelamin' AND gender.`source_id` = CONVERT(CAST(sm.`Sex_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` mtype ON mtype.`source_table` = 'jenis_anggota' AND mtype.`source_id` = CONVERT(CAST(sm.`JenisAnggota_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` edu1 ON edu1.`source_table` = 'master_pendidikan' AND edu1.`source_id` = CONVERT(CAST(sm.`EducationLevel_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` edu2 ON edu2.`source_table` = 'master_jenjang_pendidikan' AND edu2.`source_id` = CONVERT(CAST(sm.`JenjangPendidikan_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` job ON job.`source_table` = 'master_pekerjaan' AND job.`source_id` = CONVERT(CAST(sm.`Job_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` st ON st.`source_table` = 'status_anggota' AND st.`source_id` = CONVERT(CAST(sm.`StatusAnggota_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
SET m.`identity_type_label` = ident.`name`,
    m.`gender_label` = gender.`name`,
    m.`member_type_label` = mtype.`name`,
    m.`education_label` = COALESCE(edu1.`name`, edu2.`name`),
    m.`occupation_label` = job.`name`,
    m.`member_status_label` = st.`name`;

UPDATE `book_items` bi
JOIN `inlislite_v3`.`collections` c ON bi.`source_system` = 'inlislite_v3' AND bi.`source_id` = CONVERT(CAST(c.`ID` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` ll ON ll.`source_table` = 'location_library' AND ll.`source_id` = CONVERT(CAST(c.`Location_Library_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` loc ON loc.`source_table` = 'locations' AND loc.`source_id` = CONVERT(CAST(c.`Location_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` rule_ref ON rule_ref.`source_table` = 'collectionrules' AND rule_ref.`source_id` = CONVERT(CAST(c.`Rule_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` cat ON cat.`source_table` = 'collectioncategorys' AND cat.`source_id` = CONVERT(CAST(c.`Category_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` media ON media.`source_table` = 'collectionmedias' AND media.`source_id` = CONVERT(CAST(c.`Media_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` src ON src.`source_table` = 'collectionsources' AND src.`source_id` = CONVERT(CAST(c.`Source_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
LEFT JOIN `inlislite_master_references` st ON st.`source_table` = 'collectionstatus' AND st.`source_id` = CONVERT(CAST(c.`Status_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci
SET bi.`source_location_library_id` = CONVERT(CAST(c.`Location_Library_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
    bi.`source_location_id` = CONVERT(CAST(c.`Location_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
    bi.`source_rule_id` = CONVERT(CAST(c.`Rule_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
    bi.`source_category_id` = CONVERT(CAST(c.`Category_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
    bi.`source_media_id` = CONVERT(CAST(c.`Media_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
    bi.`source_collection_source_id` = CONVERT(CAST(c.`Source_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
    bi.`source_status_id` = CONVERT(CAST(c.`Status_id` AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci,
    bi.`location_name` = COALESCE(loc.`name`, ll.`name`, bi.`location_name`),
    bi.`location_library_name` = ll.`name`,
    bi.`location_room_name` = loc.`name`,
    bi.`rule_name` = rule_ref.`name`,
    bi.`collection_type` = COALESCE(cat.`name`, bi.`collection_type`),
    bi.`category_name` = cat.`name`,
    bi.`media_name` = media.`name`,
    bi.`source_name` = src.`name`,
    bi.`status_label` = st.`name`,
    bi.`is_public` = IFNULL(c.`ISOPAC`, 1);
