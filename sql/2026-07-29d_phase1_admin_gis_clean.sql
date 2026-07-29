CREATE TABLE IF NOT EXISTS `ref_districts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `province_code` varchar(2) NOT NULL DEFAULT '33',
  `regency_code` varchar(2) NOT NULL DEFAULT '17',
  `code` varchar(20) NOT NULL,
  `full_code` varchar(20) DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ref_districts_code` (`code`),
  UNIQUE KEY `uq_ref_districts_full_code` (`full_code`),
  UNIQUE KEY `uq_ref_districts_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ref_villages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `district_id` int(10) unsigned NOT NULL,
  `province_code` varchar(2) NOT NULL DEFAULT '33',
  `regency_code` varchar(2) NOT NULL DEFAULT '17',
  `district_code` varchar(2) DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `area_type` enum('desa','kelurahan') NOT NULL DEFAULT 'desa',
  `name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ref_villages_code` (`code`),
  KEY `idx_ref_villages_district_name` (`district_id`, `name`),
  CONSTRAINT `fk_ref_villages_district` FOREIGN KEY (`district_id`) REFERENCES `ref_districts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `ref_districts`
  ADD COLUMN IF NOT EXISTS `province_code` varchar(2) NOT NULL DEFAULT '33' AFTER `id`,
  ADD COLUMN IF NOT EXISTS `regency_code` varchar(2) NOT NULL DEFAULT '17' AFTER `province_code`,
  ADD COLUMN IF NOT EXISTS `full_code` varchar(20) DEFAULT NULL AFTER `code`,
  ADD UNIQUE KEY IF NOT EXISTS `uq_ref_districts_full_code` (`full_code`);

ALTER TABLE `ref_villages`
  ADD COLUMN IF NOT EXISTS `province_code` varchar(2) NOT NULL DEFAULT '33' AFTER `district_id`,
  ADD COLUMN IF NOT EXISTS `regency_code` varchar(2) NOT NULL DEFAULT '17' AFTER `province_code`,
  ADD COLUMN IF NOT EXISTS `district_code` varchar(2) DEFAULT NULL AFTER `regency_code`,
  ADD COLUMN IF NOT EXISTS `area_type` enum('desa','kelurahan') NOT NULL DEFAULT 'desa' AFTER `code`;

ALTER TABLE `libraries`
  ADD COLUMN IF NOT EXISTS `district_id` int(10) unsigned DEFAULT NULL AFTER `address`,
  ADD COLUMN IF NOT EXISTS `village_id` int(10) unsigned DEFAULT NULL AFTER `district_id`,
  ADD COLUMN IF NOT EXISTS `is_verified` tinyint(1) NOT NULL DEFAULT 0 AFTER `status`,
  ADD COLUMN IF NOT EXISTS `verified_by` bigint(20) unsigned DEFAULT NULL AFTER `is_verified`,
  ADD COLUMN IF NOT EXISTS `verified_at` datetime DEFAULT NULL AFTER `verified_by`;

ALTER TABLE `libraries`
  ADD INDEX IF NOT EXISTS `idx_libraries_district_id` (`district_id`),
  ADD INDEX IF NOT EXISTS `idx_libraries_village_id` (`village_id`),
  ADD INDEX IF NOT EXISTS `idx_libraries_verified` (`is_verified`);

ALTER TABLE `auth_user`
  ADD INDEX IF NOT EXISTS `idx_auth_user_library_status` (`library_id`, `status`);

ALTER TABLE `library_photos`
  ADD COLUMN IF NOT EXISTS `deleted_at` datetime DEFAULT NULL AFTER `created_at`,
  ADD COLUMN IF NOT EXISTS `deleted_by` bigint(20) unsigned DEFAULT NULL AFTER `deleted_at`,
  ADD INDEX IF NOT EXISTS `idx_library_photos_deleted` (`deleted_at`);

UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '01', `full_code` = '33.17.01' WHERE `name` = 'Sumber';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '02', `full_code` = '33.17.02' WHERE `name` = 'Bulu';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '03', `full_code` = '33.17.03' WHERE `name` = 'Gunem';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '04', `full_code` = '33.17.04' WHERE `name` = 'Sale';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '05', `full_code` = '33.17.05' WHERE `name` = 'Sarang';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '06', `full_code` = '33.17.06' WHERE `name` = 'Sedan';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '07', `full_code` = '33.17.07' WHERE `name` = 'Pamotan';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '08', `full_code` = '33.17.08' WHERE `name` = 'Sulang';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '09', `full_code` = '33.17.09' WHERE `name` = 'Kaliori';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '10', `full_code` = '33.17.10' WHERE `name` = 'Rembang';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '11', `full_code` = '33.17.11' WHERE `name` = 'Pancur';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '12', `full_code` = '33.17.12' WHERE `name` = 'Kragan';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '13', `full_code` = '33.17.13' WHERE `name` = 'Sluke';
UPDATE `ref_districts` SET `province_code` = '33', `regency_code` = '17', `code` = '14', `full_code` = '33.17.14' WHERE `name` = 'Lasem';

INSERT INTO `ref_districts` (`province_code`, `regency_code`, `code`, `full_code`, `name`, `is_active`) VALUES
('33', '17', '01', '33.17.01', 'Sumber', 1),
('33', '17', '02', '33.17.02', 'Bulu', 1),
('33', '17', '03', '33.17.03', 'Gunem', 1),
('33', '17', '04', '33.17.04', 'Sale', 1),
('33', '17', '05', '33.17.05', 'Sarang', 1),
('33', '17', '06', '33.17.06', 'Sedan', 1),
('33', '17', '07', '33.17.07', 'Pamotan', 1),
('33', '17', '08', '33.17.08', 'Sulang', 1),
('33', '17', '09', '33.17.09', 'Kaliori', 1),
('33', '17', '10', '33.17.10', 'Rembang', 1),
('33', '17', '11', '33.17.11', 'Pancur', 1),
('33', '17', '12', '33.17.12', 'Kragan', 1),
('33', '17', '13', '33.17.13', 'Sluke', 1),
('33', '17', '14', '33.17.14', 'Lasem', 1)
ON DUPLICATE KEY UPDATE `province_code` = VALUES(`province_code`), `regency_code` = VALUES(`regency_code`), `full_code` = VALUES(`full_code`), `name` = VALUES(`name`), `is_active` = 1;

INSERT INTO `ref_villages` (`district_id`, `province_code`, `regency_code`, `district_code`, `code`, `area_type`, `name`, `is_active`)
SELECT d.`id`, '33', '17', seed.`district_code`, seed.`code`, 'desa', seed.`name`, 1
FROM (
SELECT '01' AS district_code, '3317012015' AS code, 'Bogorejo' AS name UNION ALL
SELECT '01' AS district_code, '3317012011' AS code, 'Grawan' AS name UNION ALL
SELECT '01' AS district_code, '3317012010' AS code, 'Jadi' AS name UNION ALL
SELECT '01' AS district_code, '3317012008' AS code, 'Jatihadi' AS name UNION ALL
SELECT '01' AS district_code, '3317012017' AS code, 'Kedungasem' AS name UNION ALL
SELECT '01' AS district_code, '3317012006' AS code, 'Kedungtulup' AS name UNION ALL
SELECT '01' AS district_code, '3317012005' AS code, 'Krikilan' AS name UNION ALL
SELECT '01' AS district_code, '3317012002' AS code, 'Logede' AS name UNION ALL
SELECT '01' AS district_code, '3317012004' AS code, 'Logung' AS name UNION ALL
SELECT '01' AS district_code, '3317012016' AS code, 'Megulung' AS name UNION ALL
SELECT '01' AS district_code, '3317012003' AS code, 'Pelemsari' AS name UNION ALL
SELECT '01' AS district_code, '3317012007' AS code, 'Polbayem' AS name UNION ALL
SELECT '01' AS district_code, '3317012012' AS code, 'Randuagung' AS name UNION ALL
SELECT '01' AS district_code, '3317012001' AS code, 'Ronggomulyo' AS name UNION ALL
SELECT '01' AS district_code, '3317012018' AS code, 'Sekarsari' AS name UNION ALL
SELECT '01' AS district_code, '3317012013' AS code, 'Sukorejo' AS name UNION ALL
SELECT '01' AS district_code, '3317012009' AS code, 'Sumber' AS name UNION ALL
SELECT '01' AS district_code, '3317012014' AS code, 'Tlogotunggal' AS name UNION ALL
SELECT '02' AS district_code, '3317022014' AS code, 'Bulu' AS name UNION ALL
SELECT '02' AS district_code, '3317022006' AS code, 'Cabean' AS name UNION ALL
SELECT '02' AS district_code, '3317022013' AS code, 'Jukung' AS name UNION ALL
SELECT '02' AS district_code, '3317022016' AS code, 'Kadiwono' AS name UNION ALL
SELECT '02' AS district_code, '3317022010' AS code, 'Karangasem' AS name UNION ALL
SELECT '02' AS district_code, '3317022007' AS code, 'Lambangan Kulon' AS name UNION ALL
SELECT '02' AS district_code, '3317022008' AS code, 'Lambangan Wetan' AS name UNION ALL
SELECT '02' AS district_code, '3317022015' AS code, 'Mantingan' AS name UNION ALL
SELECT '02' AS district_code, '3317022001' AS code, 'Mlatirejo' AS name UNION ALL
SELECT '02' AS district_code, '3317022012' AS code, 'Ngulaan' AS name UNION ALL
SELECT '02' AS district_code, '3317022011' AS code, 'Pasedan' AS name UNION ALL
SELECT '02' AS district_code, '3317022005' AS code, 'Pinggan' AS name UNION ALL
SELECT '02' AS district_code, '3317022003' AS code, 'Pondokrejo' AS name UNION ALL
SELECT '02' AS district_code, '3317022002' AS code, 'Sendangmulyo' AS name UNION ALL
SELECT '02' AS district_code, '3317022009' AS code, 'Sumbermulyo' AS name UNION ALL
SELECT '02' AS district_code, '3317022004' AS code, 'Warugunung' AS name UNION ALL
SELECT '03' AS district_code, '3317032015' AS code, 'Banyuurip' AS name UNION ALL
SELECT '03' AS district_code, '3317032014' AS code, 'Demaan' AS name UNION ALL
SELECT '03' AS district_code, '3317032006' AS code, 'Dowan' AS name UNION ALL
SELECT '03' AS district_code, '3317032008' AS code, 'Gunem' AS name UNION ALL
SELECT '03' AS district_code, '3317032001' AS code, 'Kajar' AS name UNION ALL
SELECT '03' AS district_code, '3317032009' AS code, 'Kulutan' AS name UNION ALL
SELECT '03' AS district_code, '3317032013' AS code, 'Panohan' AS name UNION ALL
SELECT '03' AS district_code, '3317032004' AS code, 'Pasucen' AS name UNION ALL
SELECT '03' AS district_code, '3317032016' AS code, 'Sambongpayak' AS name UNION ALL
SELECT '03' AS district_code, '3317032012' AS code, 'Sendangmulyo' AS name UNION ALL
SELECT '03' AS district_code, '3317032010' AS code, 'Sidomulyo' AS name UNION ALL
SELECT '03' AS district_code, '3317032005' AS code, 'Suntri' AS name UNION ALL
SELECT '03' AS district_code, '3317032003' AS code, 'Tegaldowo' AS name UNION ALL
SELECT '03' AS district_code, '3317032011' AS code, 'Telgawah' AS name UNION ALL
SELECT '03' AS district_code, '3317032002' AS code, 'Timbrangan' AS name UNION ALL
SELECT '03' AS district_code, '3317032007' AS code, 'Trembes' AS name UNION ALL
SELECT '04' AS district_code, '3317042001' AS code, 'Bancang' AS name UNION ALL
SELECT '04' AS district_code, '3317042012' AS code, 'Bitingan' AS name UNION ALL
SELECT '04' AS district_code, '3317042005' AS code, 'Gading' AS name UNION ALL
SELECT '04' AS district_code, '3317042006' AS code, 'Jinanten' AS name UNION ALL
SELECT '04' AS district_code, '3317042007' AS code, 'Joho' AS name UNION ALL
SELECT '04' AS district_code, '3317042002' AS code, 'Mrayun' AS name UNION ALL
SELECT '04' AS district_code, '3317042003' AS code, 'Ngajaran' AS name UNION ALL
SELECT '04' AS district_code, '3317042013' AS code, 'Pakis' AS name UNION ALL
SELECT '04' AS district_code, '3317042014' AS code, 'Rendeng' AS name UNION ALL
SELECT '04' AS district_code, '3317042008' AS code, 'Sale' AS name UNION ALL
SELECT '04' AS district_code, '3317042010' AS code, 'Sumbermulyo' AS name UNION ALL
SELECT '04' AS district_code, '3317042004' AS code, 'Tahunan' AS name UNION ALL
SELECT '04' AS district_code, '3317042011' AS code, 'Tengger' AS name UNION ALL
SELECT '04' AS district_code, '3317042015' AS code, 'Ukir' AS name UNION ALL
SELECT '04' AS district_code, '3317042009' AS code, 'Wonokerto' AS name UNION ALL
SELECT '05' AS district_code, '3317052007' AS code, 'Babaktulung' AS name UNION ALL
SELECT '05' AS district_code, '3317052021' AS code, 'Bajingjowo' AS name UNION ALL
SELECT '05' AS district_code, '3317052022' AS code, 'Bajingmeduro' AS name UNION ALL
SELECT '05' AS district_code, '3317052018' AS code, 'Banowan' AS name UNION ALL
SELECT '05' AS district_code, '3317052006' AS code, 'Baturno' AS name UNION ALL
SELECT '05' AS district_code, '3317052003' AS code, 'Bonjor' AS name UNION ALL
SELECT '05' AS district_code, '3317052016' AS code, 'Dadapmulyo' AS name UNION ALL
SELECT '05' AS district_code, '3317052011' AS code, 'Gilis' AS name UNION ALL
SELECT '05' AS district_code, '3317052013' AS code, 'Gonggang' AS name UNION ALL
SELECT '05' AS district_code, '3317052012' AS code, 'Gunungmulyo' AS name UNION ALL
SELECT '05' AS district_code, '3317052009' AS code, 'Jambangan' AS name UNION ALL
SELECT '05' AS district_code, '3317052015' AS code, 'Kalipang' AS name UNION ALL
SELECT '05' AS district_code, '3317052020' AS code, 'Karangmangu' AS name UNION ALL
SELECT '05' AS district_code, '3317052001' AS code, 'Lodan Kulon' AS name UNION ALL
SELECT '05' AS district_code, '3317052002' AS code, 'Lodan Wetan' AS name UNION ALL
SELECT '05' AS district_code, '3317052008' AS code, 'Nglojo' AS name UNION ALL
SELECT '05' AS district_code, '3317052010' AS code, 'Pelang' AS name UNION ALL
SELECT '05' AS district_code, '3317052005' AS code, 'Sampung' AS name UNION ALL
SELECT '05' AS district_code, '3317052023' AS code, 'Sarangmeduro' AS name UNION ALL
SELECT '05' AS district_code, '3317052017' AS code, 'Sendangmulyo' AS name UNION ALL
SELECT '05' AS district_code, '3317052014' AS code, 'Sumbermulyo' AS name UNION ALL
SELECT '05' AS district_code, '3317052004' AS code, 'Tawangrejo' AS name UNION ALL
SELECT '05' AS district_code, '3317052019' AS code, 'Temperak' AS name UNION ALL
SELECT '06' AS district_code, '3317062018' AS code, 'Bogorejo' AS name UNION ALL
SELECT '06' AS district_code, '3317062013' AS code, 'Candimulyo' AS name UNION ALL
SELECT '06' AS district_code, '3317062016' AS code, 'Dadapan' AS name UNION ALL
SELECT '06' AS district_code, '3317062012' AS code, 'Gandrirojo' AS name UNION ALL
SELECT '06' AS district_code, '3317062005' AS code, 'Gesikan' AS name UNION ALL
SELECT '06' AS district_code, '3317062020' AS code, 'Jambeyan' AS name UNION ALL
SELECT '06' AS district_code, '3317062008' AS code, 'Karangasem' AS name UNION ALL
SELECT '06' AS district_code, '3317062003' AS code, 'Karas' AS name UNION ALL
SELECT '06' AS district_code, '3317062011' AS code, 'Kedungringin' AS name UNION ALL
SELECT '06' AS district_code, '3317062019' AS code, 'Kenongo' AS name UNION ALL
SELECT '06' AS district_code, '3317062015' AS code, 'Kumbo' AS name UNION ALL
SELECT '06' AS district_code, '3317062014' AS code, 'Lemahputih' AS name UNION ALL
SELECT '06' AS district_code, '3317062021' AS code, 'Menoro' AS name UNION ALL
SELECT '06' AS district_code, '3317062004' AS code, 'Mojosari' AS name UNION ALL
SELECT '06' AS district_code, '3317062001' AS code, 'Ngulahan' AS name UNION ALL
SELECT '06' AS district_code, '3317062002' AS code, 'Pacing' AS name UNION ALL
SELECT '06' AS district_code, '3317062006' AS code, 'Sambiroto' AS name UNION ALL
SELECT '06' AS district_code, '3317062017' AS code, 'Sambong' AS name UNION ALL
SELECT '06' AS district_code, '3317062007' AS code, 'Sedan' AS name UNION ALL
SELECT '06' AS district_code, '3317062010' AS code, 'Sidomulyo' AS name UNION ALL
SELECT '06' AS district_code, '3317062009' AS code, 'Sidorejo' AS name UNION ALL
SELECT '07' AS district_code, '3317072006' AS code, 'Bamban' AS name UNION ALL
SELECT '07' AS district_code, '3317072007' AS code, 'Bangunrejo' AS name UNION ALL
SELECT '07' AS district_code, '3317072005' AS code, 'Gambiran' AS name UNION ALL
SELECT '07' AS district_code, '3317072018' AS code, 'Gegersimo' AS name UNION ALL
SELECT '07' AS district_code, '3317072020' AS code, 'Japerejo' AS name UNION ALL
SELECT '07' AS district_code, '3317072011' AS code, 'Joho' AS name UNION ALL
SELECT '07' AS district_code, '3317072013' AS code, 'Kepohagung' AS name UNION ALL
SELECT '07' AS district_code, '3317072016' AS code, 'Ketangi' AS name UNION ALL
SELECT '07' AS district_code, '3317072001' AS code, 'Megal' AS name UNION ALL
SELECT '07' AS district_code, '3317072012' AS code, 'Mlagen' AS name UNION ALL
SELECT '07' AS district_code, '3317072014' AS code, 'Mlawat' AS name UNION ALL
SELECT '07' AS district_code, '3317072002' AS code, 'Ngemplakrejo' AS name UNION ALL
SELECT '07' AS district_code, '3317072008' AS code, 'Pamotan' AS name UNION ALL
SELECT '07' AS district_code, '3317072003' AS code, 'Pragen' AS name UNION ALL
SELECT '07' AS district_code, '3317072022' AS code, 'Ringin' AS name UNION ALL
SELECT '07' AS district_code, '3317072004' AS code, 'Samaran' AS name UNION ALL
SELECT '07' AS district_code, '3317072015' AS code, 'Segoromulyo' AS name UNION ALL
SELECT '07' AS district_code, '3317072017' AS code, 'Sendangagung' AS name UNION ALL
SELECT '07' AS district_code, '3317072009' AS code, 'Sidorejo' AS name UNION ALL
SELECT '07' AS district_code, '3317072023' AS code, 'Sumbangrejo' AS name UNION ALL
SELECT '07' AS district_code, '3317072019' AS code, 'Sumberjo' AS name UNION ALL
SELECT '07' AS district_code, '3317072010' AS code, 'Tempaling' AS name UNION ALL
SELECT '07' AS district_code, '3317072021' AS code, 'Tulung' AS name UNION ALL
SELECT '08' AS district_code, '3317082011' AS code, 'Bogorame' AS name UNION ALL
SELECT '08' AS district_code, '3317082010' AS code, 'Glebeg' AS name UNION ALL
SELECT '08' AS district_code, '3317082008' AS code, 'Jatimudo' AS name UNION ALL
SELECT '08' AS district_code, '3317082012' AS code, 'Kaliombo' AS name UNION ALL
SELECT '08' AS district_code, '3317082007' AS code, 'Karangharjo' AS name UNION ALL
SELECT '08' AS district_code, '3317082014' AS code, 'Karangsari' AS name UNION ALL
SELECT '08' AS district_code, '3317082016' AS code, 'Kebonagung' AS name UNION ALL
SELECT '08' AS district_code, '3317082002' AS code, 'Kemadu' AS name UNION ALL
SELECT '08' AS district_code, '3317082021' AS code, 'Kerep' AS name UNION ALL
SELECT '08' AS district_code, '3317082006' AS code, 'Korowelang' AS name UNION ALL
SELECT '08' AS district_code, '3317082009' AS code, 'Kunir' AS name UNION ALL
SELECT '08' AS district_code, '3317082020' AS code, 'Landoh' AS name UNION ALL
SELECT '08' AS district_code, '3317082019' AS code, 'Pedak' AS name UNION ALL
SELECT '08' AS district_code, '3317082004' AS code, 'Pomahan' AS name UNION ALL
SELECT '08' AS district_code, '3317082015' AS code, 'Pragu' AS name UNION ALL
SELECT '08' AS district_code, '3317082018' AS code, 'Pranti' AS name UNION ALL
SELECT '08' AS district_code, '3317082005' AS code, 'Rukem' AS name UNION ALL
SELECT '08' AS district_code, '3317082017' AS code, 'Seren' AS name UNION ALL
SELECT '08' AS district_code, '3317082013' AS code, 'Sudo' AS name UNION ALL
SELECT '08' AS district_code, '3317082003' AS code, 'Sulang' AS name UNION ALL
SELECT '08' AS district_code, '3317082001' AS code, 'Tanjung' AS name UNION ALL
SELECT '09' AS district_code, '3317092010' AS code, 'Babadan' AS name UNION ALL
SELECT '09' AS district_code, '3317092005' AS code, 'Banggi' AS name UNION ALL
SELECT '09' AS district_code, '3317092022' AS code, 'Banyudono' AS name UNION ALL
SELECT '09' AS district_code, '3317092021' AS code, 'Bogoharjo' AS name UNION ALL
SELECT '09' AS district_code, '3317092017' AS code, 'Dresi Kulon' AS name UNION ALL
SELECT '09' AS district_code, '3317092018' AS code, 'Dresi Wetan' AS name UNION ALL
SELECT '09' AS district_code, '3317092007' AS code, 'Gunungsari' AS name UNION ALL
SELECT '09' AS district_code, '3317092009' AS code, 'Karangsekar' AS name UNION ALL
SELECT '09' AS district_code, '3317092006' AS code, 'Kuangsan' AS name UNION ALL
SELECT '09' AS district_code, '3317092002' AS code, 'Maguan' AS name UNION ALL
SELECT '09' AS district_code, '3317092001' AS code, 'Meteseh' AS name UNION ALL
SELECT '09' AS district_code, '3317092013' AS code, 'Mojorembun' AS name UNION ALL
SELECT '09' AS district_code, '3317092016' AS code, 'Mojowarno' AS name UNION ALL
SELECT '09' AS district_code, '3317092023' AS code, 'Pantiharjo' AS name UNION ALL
SELECT '09' AS district_code, '3317092011' AS code, 'Pengkol' AS name UNION ALL
SELECT '09' AS district_code, '3317092020' AS code, 'Purworejo' AS name UNION ALL
SELECT '09' AS district_code, '3317092012' AS code, 'Sambiyan' AS name UNION ALL
SELECT '09' AS district_code, '3317092008' AS code, 'Sendangagung' AS name UNION ALL
SELECT '09' AS district_code, '3317092003' AS code, 'Sidomulyo' AS name UNION ALL
SELECT '09' AS district_code, '3317092015' AS code, 'Tambakagung' AS name UNION ALL
SELECT '09' AS district_code, '3317092019' AS code, 'Tasikharjo' AS name UNION ALL
SELECT '09' AS district_code, '3317092014' AS code, 'Tunggulsari' AS name UNION ALL
SELECT '09' AS district_code, '3317092004' AS code, 'Wiroto' AS name UNION ALL
SELECT '10' AS district_code, '3317102011' AS code, 'Gedangan' AS name UNION ALL
SELECT '10' AS district_code, '3317102021' AS code, 'Gegunung Wetan' AS name UNION ALL
SELECT '10' AS district_code, '3317102033' AS code, 'Kabongan Kidul' AS name UNION ALL
SELECT '10' AS district_code, '3317102032' AS code, 'Kabongan Lor' AS name UNION ALL
SELECT '10' AS district_code, '3317102007' AS code, 'Kasreman' AS name UNION ALL
SELECT '10' AS district_code, '3317102001' AS code, 'Kedungrejo' AS name UNION ALL
SELECT '10' AS district_code, '3317102016' AS code, 'Ketanggi' AS name UNION ALL
SELECT '10' AS district_code, '3317102003' AS code, 'Kumendung' AS name UNION ALL
SELECT '10' AS district_code, '3317102014' AS code, 'Mondoteko' AS name UNION ALL
SELECT '10' AS district_code, '3317102015' AS code, 'Ngadem' AS name UNION ALL
SELECT '10' AS district_code, '3317102013' AS code, 'Ngotet' AS name UNION ALL
SELECT '10' AS district_code, '3317102030' AS code, 'Padaran' AS name UNION ALL
SELECT '10' AS district_code, '3317102005' AS code, 'Pandean' AS name UNION ALL
SELECT '10' AS district_code, '3317102010' AS code, 'Pasarbanggi' AS name UNION ALL
SELECT '10' AS district_code, '3317102017' AS code, 'Pulo' AS name UNION ALL
SELECT '10' AS district_code, '3317102008' AS code, 'Punjulharjo' AS name UNION ALL
SELECT '10' AS district_code, '3317102026' AS code, 'Sawahan' AS name UNION ALL
SELECT '10' AS district_code, '3317102004' AS code, 'Sridadi' AS name UNION ALL
SELECT '10' AS district_code, '3317102031' AS code, 'Sukoharjo' AS name UNION ALL
SELECT '10' AS district_code, '3317102024' AS code, 'Sumberjo' AS name UNION ALL
SELECT '10' AS district_code, '3317102025' AS code, 'Tasikagung' AS name UNION ALL
SELECT '10' AS district_code, '3317102034' AS code, 'Tireman' AS name UNION ALL
SELECT '10' AS district_code, '3317102006' AS code, 'Tlogomojo' AS name UNION ALL
SELECT '10' AS district_code, '3317102009' AS code, 'Tritunggal' AS name UNION ALL
SELECT '10' AS district_code, '3317102002' AS code, 'Turusgede' AS name UNION ALL
SELECT '10' AS district_code, '3317102018' AS code, 'Waru' AS name UNION ALL
SELECT '10' AS district_code, '3317102012' AS code, 'Weton' AS name UNION ALL
SELECT '10' AS district_code, '3317101028' AS code, 'Sidowayah' AS name UNION ALL
SELECT '10' AS district_code, '3317101029' AS code, 'Kutoharjo' AS name UNION ALL
SELECT '10' AS district_code, '3317101027' AS code, 'Leteh' AS name UNION ALL
SELECT '10' AS district_code, '3317101022' AS code, 'Pacar' AS name UNION ALL
SELECT '10' AS district_code, '3317101023' AS code, 'Tanjungsari' AS name UNION ALL
SELECT '10' AS district_code, '3317101019' AS code, 'Magersari' AS name UNION ALL
SELECT '10' AS district_code, '3317101020' AS code, 'Gegunung Kulon' AS name UNION ALL
SELECT '11' AS district_code, '3317112020' AS code, 'Banyuurip' AS name UNION ALL
SELECT '11' AS district_code, '3317112017' AS code, 'Criwik' AS name UNION ALL
SELECT '11' AS district_code, '3317112003' AS code, 'Doropayung' AS name UNION ALL
SELECT '11' AS district_code, '3317112007' AS code, 'Gemblengmulyo' AS name UNION ALL
SELECT '11' AS district_code, '3317112001' AS code, 'Japeledok' AS name UNION ALL
SELECT '11' AS district_code, '3317112002' AS code, 'Jeruk' AS name UNION ALL
SELECT '11' AS district_code, '3317112021' AS code, 'Johogunung' AS name UNION ALL
SELECT '11' AS district_code, '3317112009' AS code, 'Kalitengah' AS name UNION ALL
SELECT '11' AS district_code, '3317112004' AS code, 'Karaskepoh' AS name UNION ALL
SELECT '11' AS district_code, '3317112011' AS code, 'Kedung' AS name UNION ALL
SELECT '11' AS district_code, '3317112013' AS code, 'Langkir' AS name UNION ALL
SELECT '11' AS district_code, '3317112023' AS code, 'Ngroto' AS name UNION ALL
SELECT '11' AS district_code, '3317112019' AS code, 'Ngulangan' AS name UNION ALL
SELECT '11' AS district_code, '3317112014' AS code, 'Pancur' AS name UNION ALL
SELECT '11' AS district_code, '3317112006' AS code, 'Pandan' AS name UNION ALL
SELECT '11' AS district_code, '3317112015' AS code, 'Pohlandak' AS name UNION ALL
SELECT '11' AS district_code, '3317112012' AS code, 'Punggurharjo' AS name UNION ALL
SELECT '11' AS district_code, '3317112010' AS code, 'Sidowayah' AS name UNION ALL
SELECT '11' AS district_code, '3317112008' AS code, 'Sumberagung' AS name UNION ALL
SELECT '11' AS district_code, '3317112022' AS code, 'Trenggulunan' AS name UNION ALL
SELECT '11' AS district_code, '3317112005' AS code, 'Tuyuhan' AS name UNION ALL
SELECT '11' AS district_code, '3317112016' AS code, 'Warugunung' AS name UNION ALL
SELECT '11' AS district_code, '3317112018' AS code, 'Wuwur' AS name UNION ALL
SELECT '12' AS district_code, '3317122014' AS code, 'Balongmulyo' AS name UNION ALL
SELECT '12' AS district_code, '3317122009' AS code, 'Karanganyar' AS name UNION ALL
SELECT '12' AS district_code, '3317122011' AS code, 'Karangharjo' AS name UNION ALL
SELECT '12' AS district_code, '3317122010' AS code, 'Karanglincak' AS name UNION ALL
SELECT '12' AS district_code, '3317122008' AS code, 'Kebloran' AS name UNION ALL
SELECT '12' AS district_code, '3317122005' AS code, 'Kendalagung' AS name UNION ALL
SELECT '12' AS district_code, '3317122012' AS code, 'Kragan' AS name UNION ALL
SELECT '12' AS district_code, '3317122006' AS code, 'Mojokerto' AS name UNION ALL
SELECT '12' AS district_code, '3317122015' AS code, 'Narukan' AS name UNION ALL
SELECT '12' AS district_code, '3317122004' AS code, 'Ngasinan' AS name UNION ALL
SELECT '12' AS district_code, '3317122025' AS code, 'Pandangan Kulon' AS name UNION ALL
SELECT '12' AS district_code, '3317122024' AS code, 'Pandangan Wetan' AS name UNION ALL
SELECT '12' AS district_code, '3317122022' AS code, 'Plawangan' AS name UNION ALL
SELECT '12' AS district_code, '3317122018' AS code, 'Sendang' AS name UNION ALL
SELECT '12' AS district_code, '3317122002' AS code, 'Sendangmulyo' AS name UNION ALL
SELECT '12' AS district_code, '3317122003' AS code, 'Sendangwaru' AS name UNION ALL
SELECT '12' AS district_code, '3317122016' AS code, 'Sudan' AS name UNION ALL
SELECT '12' AS district_code, '3317122023' AS code, 'Sumbergayam' AS name UNION ALL
SELECT '12' AS district_code, '3317122027' AS code, 'Sumbersari' AS name UNION ALL
SELECT '12' AS district_code, '3317122021' AS code, 'Sumurpule' AS name UNION ALL
SELECT '12' AS district_code, '3317122026' AS code, 'Sumurtawang' AS name UNION ALL
SELECT '12' AS district_code, '3317122007' AS code, 'Tanjungan' AS name UNION ALL
SELECT '12' AS district_code, '3317122001' AS code, 'Tanjungsari' AS name UNION ALL
SELECT '12' AS district_code, '3317122013' AS code, 'Tegalmulyo' AS name UNION ALL
SELECT '12' AS district_code, '3317122017' AS code, 'Terjan' AS name UNION ALL
SELECT '12' AS district_code, '3317122019' AS code, 'Watupecah' AS name UNION ALL
SELECT '12' AS district_code, '3317122020' AS code, 'Woro' AS name UNION ALL
SELECT '13' AS district_code, '3317132003' AS code, 'Bendo' AS name UNION ALL
SELECT '13' AS district_code, '3317132006' AS code, 'Blimbing' AS name UNION ALL
SELECT '13' AS district_code, '3317132008' AS code, 'Jatisari' AS name UNION ALL
SELECT '13' AS district_code, '3317132011' AS code, 'Jurangjero' AS name UNION ALL
SELECT '13' AS district_code, '3317132004' AS code, 'Labuhan Kidul' AS name UNION ALL
SELECT '13' AS district_code, '3317132009' AS code, 'Langgar' AS name UNION ALL
SELECT '13' AS district_code, '3317132012' AS code, 'Leran' AS name UNION ALL
SELECT '13' AS district_code, '3317132007' AS code, 'Manggar' AS name UNION ALL
SELECT '13' AS district_code, '3317132014' AS code, 'Pangkalan' AS name UNION ALL
SELECT '13' AS district_code, '3317132002' AS code, 'Rakitan' AS name UNION ALL
SELECT '13' AS district_code, '3317132001' AS code, 'Sanetan' AS name UNION ALL
SELECT '13' AS district_code, '3317132005' AS code, 'Sendangmulyo' AS name UNION ALL
SELECT '13' AS district_code, '3317132010' AS code, 'Sluke' AS name UNION ALL
SELECT '13' AS district_code, '3317132013' AS code, 'Trahan' AS name UNION ALL
SELECT '14' AS district_code, '3317142005' AS code, 'Babagan' AS name UNION ALL
SELECT '14' AS district_code, '3317142020' AS code, 'Binangun' AS name UNION ALL
SELECT '14' AS district_code, '3317142019' AS code, 'Bonang' AS name UNION ALL
SELECT '14' AS district_code, '3317142008' AS code, 'Dasun' AS name UNION ALL
SELECT '14' AS district_code, '3317142006' AS code, 'Dorokandang' AS name UNION ALL
SELECT '14' AS district_code, '3317142007' AS code, 'Gedongmulyo' AS name UNION ALL
SELECT '14' AS district_code, '3317142015' AS code, 'Gowak' AS name UNION ALL
SELECT '14' AS district_code, '3317142002' AS code, 'Jolotundo' AS name UNION ALL
SELECT '14' AS district_code, '3317142014' AS code, 'Kajar' AS name UNION ALL
SELECT '14' AS district_code, '3317142004' AS code, 'Karangturi' AS name UNION ALL
SELECT '14' AS district_code, '3317142001' AS code, 'Karasgede' AS name UNION ALL
SELECT '14' AS district_code, '3317142013' AS code, 'Ngargomulyo' AS name UNION ALL
SELECT '14' AS district_code, '3317142010' AS code, 'Ngemplak' AS name UNION ALL
SELECT '14' AS district_code, '3317142011' AS code, 'Selopuro' AS name UNION ALL
SELECT '14' AS district_code, '3317142016' AS code, 'Sendangasri' AS name UNION ALL
SELECT '14' AS district_code, '3317142012' AS code, 'Sendangcoyo' AS name UNION ALL
SELECT '14' AS district_code, '3317142009' AS code, 'Soditan' AS name UNION ALL
SELECT '14' AS district_code, '3317142018' AS code, 'Sriombo' AS name UNION ALL
SELECT '14' AS district_code, '3317142003' AS code, 'Sumbergirang' AS name UNION ALL
SELECT '14' AS district_code, '3317142017' AS code, 'Tasiksono' AS name
) seed
JOIN `ref_districts` d ON d.`code` = seed.`district_code`
ON DUPLICATE KEY UPDATE `district_id` = VALUES(`district_id`), `province_code` = VALUES(`province_code`), `regency_code` = VALUES(`regency_code`), `district_code` = VALUES(`district_code`), `name` = VALUES(`name`), `is_active` = 1;

UPDATE `libraries` l
JOIN `ref_districts` d ON LOWER(d.`name`) = LOWER(l.`district`)
SET l.`district_id` = d.`id`
WHERE l.`district_id` IS NULL AND l.`district` IS NOT NULL AND l.`district` <> '';

UPDATE `libraries` l
JOIN `ref_districts` d ON d.`id` = l.`district_id`
JOIN `ref_villages` v ON v.`district_id` = d.`id` AND LOWER(v.`name`) = LOWER(l.`village`)
SET l.`village_id` = v.`id`
WHERE l.`village_id` IS NULL AND l.`village` IS NOT NULL AND l.`village` <> '';

INSERT INTO `sys_page` (`code`, `module`, `title`, `route`, `description`) VALUES
('audit.index', 'audit', 'Audit Log', 'audit', 'Pantauan aktivitas penting sistem.')
ON DUPLICATE KEY UPDATE
  `module` = VALUES(`module`),
  `title` = VALUES(`title`),
  `route` = VALUES(`route`),
  `description` = VALUES(`description`),
  `is_active` = 1;

INSERT INTO `auth_role_permission` (`role_id`, `page_id`, `can_view`, `can_create`, `can_edit`, `can_delete`, `can_export`, `can_approve`)
SELECT r.`id`, p.`id`, 1, 0, 0, 0, 1, 0
FROM `auth_role` r
JOIN `sys_page` p ON p.`code` = 'audit.index'
WHERE r.`code` = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE
  `can_view` = 1,
  `can_export` = 1;

INSERT INTO `sys_menu` (`parent_id`, `page_id`, `menu_area`, `menu_key`, `title`, `icon`, `url`, `sort_order`, `is_visible`, `is_active`, `is_locked`)
SELECT parent.`id`, p.`id`, 'MAIN', 'system.audit', 'Audit Log', 'ti ti-history', 'audit', 940, 1, 1, 0
FROM `sys_menu` parent
JOIN `sys_page` p ON p.`code` = 'audit.index'
WHERE parent.`menu_key` = 'system'
ON DUPLICATE KEY UPDATE
  `parent_id` = VALUES(`parent_id`),
  `page_id` = VALUES(`page_id`),
  `title` = VALUES(`title`),
  `icon` = VALUES(`icon`),
  `url` = VALUES(`url`),
  `sort_order` = VALUES(`sort_order`),
  `is_visible` = 1,
  `is_active` = 1;

