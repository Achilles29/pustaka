ALTER TABLE `member_visit_demographics`
  ADD COLUMN IF NOT EXISTS `data_source` enum('entered','source','estimated') NOT NULL DEFAULT 'entered' AFTER `people_count`,
  ADD COLUMN IF NOT EXISTS `estimation_note` varchar(255) DEFAULT NULL AFTER `data_source`;

INSERT INTO `member_visit_demographics`
(`visit_id`,`gender`,`age_group`,`education_id`,`education_label`,`profession_id`,`profession_label`,`people_count`,`data_source`,`estimation_note`)
SELECT mv.id,
  CASE WHEN LOWER(TRIM(COALESCE(mv.gender_label,''))) IN ('p','pr','perempuan','wanita','female') OR mv.gender_id='2' THEN 'female' WHEN LOWER(TRIM(COALESCE(mv.gender_label,''))) IN ('l','lk','laki-laki','laki laki','pria','male') OR mv.gender_id='1' THEN 'male' WHEN MOD(mv.id,2)=0 THEN 'female' ELSE 'male' END,
  CASE WHEN LOWER(COALESCE(mv.education_label,'')) REGEXP '(^|[^a-z])(sd|mi)([^a-z]|$)' OR mv.education_id='1' THEN '7–12' WHEN LOWER(COALESCE(mv.education_label,'')) REGEXP 'smp|sltp|mts' OR mv.education_id='2' THEN '13–15' WHEN LOWER(COALESCE(mv.education_label,'')) REGEXP 'sma|smk|slta|aliyah' OR mv.education_id='3' THEN '16–18' WHEN mv.education_id IN ('4','5','6','7','8','9') THEN '19–24' ELSE '25–44' END,
  NULLIF(mv.education_id,''),COALESCE(NULLIF(mv.education_label,''),'Tidak diketahui'),COALESCE(NULLIF(mv.profession_id,''),'11'),COALESCE(NULLIF(mv.profession_label,''),'Lainnya'),GREATEST(1,mv.visitor_count),
  CASE WHEN mv.gender_id IS NULL OR mv.education_id IS NULL OR mv.profession_id IS NULL THEN 'estimated' ELSE 'source' END,
  CASE WHEN mv.gender_id IS NULL OR mv.education_id IS NULL OR mv.profession_id IS NULL THEN 'Sebagian klasifikasi dilengkapi otomatis dari data kunjungan lama.' ELSE NULL END
FROM `member_visits` mv LEFT JOIN `member_visit_demographics` d ON d.visit_id=mv.id
WHERE mv.member_id IS NULL AND d.id IS NULL AND mv.visitor_count=1;

INSERT INTO `member_visit_demographics`
(`visit_id`,`gender`,`age_group`,`education_id`,`education_label`,`profession_id`,`profession_label`,`people_count`,`data_source`,`estimation_note`)
SELECT mv.id,g.gender,CASE WHEN LOWER(COALESCE(mv.group_name,'')) LIKE '%smp%' THEN '13–15' WHEN LOWER(COALESCE(mv.group_name,'')) LIKE '%sd%' THEN '7–12' ELSE '25–44' END,
  CASE WHEN LOWER(COALESCE(mv.group_name,'')) LIKE '%smp%' THEN '2' WHEN LOWER(COALESCE(mv.group_name,'')) LIKE '%sd%' THEN '1' ELSE NULL END,
  CASE WHEN LOWER(COALESCE(mv.group_name,'')) LIKE '%smp%' THEN 'SMP' WHEN LOWER(COALESCE(mv.group_name,'')) LIKE '%sd%' THEN 'SD' ELSE 'Tidak diketahui' END,
  CASE WHEN LOWER(COALESCE(mv.group_name,'')) REGEXP '(^| )(sd|smp|sma|smk)( |$)' THEN '9' ELSE '11' END,
  CASE WHEN LOWER(COALESCE(mv.group_name,'')) REGEXP '(^| )(sd|smp|sma|smk)( |$)' THEN 'Pelajar' ELSE 'Lainnya' END,
  CASE WHEN g.gender='male' THEN CEIL(mv.visitor_count/2) ELSE FLOOR(mv.visitor_count/2) END,'estimated','Komposisi rombongan lama diestimasi seimbang karena rincian awal tidak tersedia.'
FROM `member_visits` mv JOIN (SELECT 'male' gender UNION ALL SELECT 'female') g LEFT JOIN `member_visit_demographics` d ON d.visit_id=mv.id
WHERE mv.member_id IS NULL AND d.id IS NULL AND mv.visitor_count>1 AND (g.gender='male' OR FLOOR(mv.visitor_count/2)>0);
