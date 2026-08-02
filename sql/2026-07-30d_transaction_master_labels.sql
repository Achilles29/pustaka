ALTER TABLE `member_visits`
  ADD COLUMN IF NOT EXISTS `gender_label` varchar(80) DEFAULT NULL AFTER `gender_id`,
  ADD COLUMN IF NOT EXISTS `profession_label` varchar(160) DEFAULT NULL AFTER `profession_id`,
  ADD COLUMN IF NOT EXISTS `education_label` varchar(160) DEFAULT NULL AFTER `education_id`,
  ADD COLUMN IF NOT EXISTS `visit_status_label` varchar(120) DEFAULT NULL AFTER `status_id`,
  ADD COLUMN IF NOT EXISTS `location_label` varchar(180) DEFAULT NULL AFTER `location_id`,
  ADD COLUMN IF NOT EXISTS `location_loan_label` varchar(180) DEFAULT NULL AFTER `location_loan_id`,
  ADD COLUMN IF NOT EXISTS `purpose_label` varchar(180) DEFAULT NULL AFTER `purpose_id`;

ALTER TABLE `member_access_rules`
  ADD COLUMN IF NOT EXISTS `rule_label` varchar(180) DEFAULT NULL AFTER `source_rule_id`;

UPDATE `member_visits` mv
LEFT JOIN `inlislite_master_references` gender ON gender.`source_table` = 'jenis_kelamin' AND gender.`source_id` = mv.`gender_id`
LEFT JOIN `inlislite_master_references` job ON job.`source_table` = 'master_pekerjaan' AND job.`source_id` = mv.`profession_id`
LEFT JOIN `inlislite_master_references` edu ON edu.`source_table` = 'master_pendidikan' AND edu.`source_id` = mv.`education_id`
LEFT JOIN `inlislite_master_references` st ON st.`source_table` = 'status_anggota' AND st.`source_id` = mv.`status_id`
LEFT JOIN `inlislite_master_references` loc ON loc.`source_table` = 'locations' AND loc.`source_id` = mv.`location_id`
LEFT JOIN `inlislite_master_references` loan_loc ON loan_loc.`source_table` = 'collectionlocations' AND loan_loc.`source_id` = mv.`location_loan_id`
LEFT JOIN `inlislite_master_references` purpose ON purpose.`source_table` = 'tujuan_kunjungan' AND purpose.`source_id` = mv.`purpose_id`
SET mv.`gender_label` = gender.`name`,
    mv.`profession_label` = job.`name`,
    mv.`education_label` = edu.`name`,
    mv.`visit_status_label` = st.`name`,
    mv.`location_label` = loc.`name`,
    mv.`location_loan_label` = loan_loc.`name`,
    mv.`purpose_label` = purpose.`name`;

UPDATE `member_access_rules` r
LEFT JOIN `inlislite_master_references` cat ON r.`rule_type` = 'category' AND cat.`source_table` = 'collectioncategorys' AND cat.`source_id` = r.`source_rule_id`
LEFT JOIN `inlislite_master_references` loc ON r.`rule_type` = 'location' AND loc.`source_table` = 'collectionlocations' AND loc.`source_id` = r.`source_rule_id`
SET r.`rule_label` = COALESCE(cat.`name`, loc.`name`);
