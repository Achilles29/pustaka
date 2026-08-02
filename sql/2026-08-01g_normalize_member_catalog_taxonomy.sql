UPDATE `members`
SET
	`identity_type` = COALESCE(NULLIF(`identity_type_label`, ''), `identity_type`),
	`gender` = COALESCE(NULLIF(`gender_label`, ''), `gender`),
	`member_type` = COALESCE(NULLIF(`member_type_label`, ''), `member_type`),
	`education` = COALESCE(NULLIF(`education_label`, ''), `education`),
	`occupation` = COALESCE(NULLIF(`occupation_label`, ''), `occupation`);

UPDATE `members` m
LEFT JOIN `inlislite_master_references` ident
	ON ident.`source_table` = 'master_jenis_identitas' AND ident.`source_id` = m.`identity_type`
LEFT JOIN `inlislite_master_references` gender
	ON gender.`source_table` = 'jenis_kelamin' AND gender.`source_id` = m.`gender`
LEFT JOIN `inlislite_master_references` mtype
	ON mtype.`source_table` = 'jenis_anggota' AND mtype.`source_id` = m.`member_type`
LEFT JOIN `inlislite_master_references` edu
	ON edu.`source_table` = 'master_pendidikan' AND edu.`source_id` = m.`education`
LEFT JOIN `inlislite_master_references` job
	ON job.`source_table` = 'master_pekerjaan' AND job.`source_id` = m.`occupation`
SET
	m.`identity_type` = COALESCE(ident.`name`, m.`identity_type`),
	m.`gender` = COALESCE(gender.`name`, m.`gender`),
	m.`member_type` = COALESCE(mtype.`name`, m.`member_type`),
	m.`education` = COALESCE(edu.`name`, m.`education`),
	m.`occupation` = COALESCE(job.`name`, m.`occupation`);

UPDATE `books` b
LEFT JOIN `book_classification_masters` cm
	ON cm.`code` = CONCAT(LEFT(REGEXP_REPLACE(COALESCE(b.`classification`, b.`call_number`, ''), '[^0-9]', ''), 1), '00')
SET b.`content_classification_id` = cm.`id`
WHERE b.`content_classification_id` IS NULL
	AND REGEXP_REPLACE(COALESCE(b.`classification`, b.`call_number`, ''), '[^0-9]', '') REGEXP '^[0-9]';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'anak-remaja'
SET b.`content_category_id` = cc.`id`
WHERE LOWER(CONCAT_WS(' ', b.`title`, b.`subtitle`, b.`abstract`, b.`call_number`)) REGEXP 'anak|remaja|dongeng|cerita rakyat|paud|tk|sd|smp|komik';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'karya-ilmiah'
SET b.`content_category_id` = cc.`id`
WHERE LOWER(CONCAT_WS(' ', b.`title`, b.`subtitle`, b.`abstract`, b.`call_number`)) REGEXP 'skripsi|tesis|disertasi|penelitian|jurnal|prosiding|karya ilmiah|laporan akhir';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'lokal-rembang'
SET b.`content_category_id` = cc.`id`
WHERE LOWER(CONCAT_WS(' ', b.`title`, b.`subtitle`, b.`abstract`, b.`publisher`, b.`publish_place`)) REGEXP 'rembang|lasem|sulang|sedan|sarang|pamotan|kragan|sluke|kaliori|gunem|bulu|sumber|sale|pancur';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'referensi'
SET b.`content_category_id` = cc.`id`
WHERE LOWER(CONCAT_WS(' ', b.`title`, b.`subtitle`, b.`abstract`)) REGEXP 'kamus|ensiklopedia|atlas|direktori|bibliografi'
	OR EXISTS (
		SELECT 1 FROM `book_items` i
		WHERE i.`book_id` = b.`id`
			AND i.`deleted_at` IS NULL
			AND LOWER(COALESCE(i.`category_name`, '')) LIKE '%referensi%'
	);

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'fiksi'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
	AND LEFT(REGEXP_REPLACE(COALESCE(b.`classification`, b.`call_number`, ''), '[^0-9]', ''), 1) = '8';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'agama'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
	AND LEFT(REGEXP_REPLACE(COALESCE(b.`classification`, b.`call_number`, ''), '[^0-9]', ''), 1) = '2';

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'teknologi'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
	AND LEFT(REGEXP_REPLACE(COALESCE(b.`classification`, b.`call_number`, ''), '[^0-9]', ''), 1) IN ('5', '6');

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'sejarah-budaya'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
	AND LEFT(REGEXP_REPLACE(COALESCE(b.`classification`, b.`call_number`, ''), '[^0-9]', ''), 1) IN ('7', '9');

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'pengetahuan'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL
	AND LEFT(REGEXP_REPLACE(COALESCE(b.`classification`, b.`call_number`, ''), '[^0-9]', ''), 1) IN ('0', '1', '3', '4');

UPDATE `books` b
JOIN `book_content_categories` cc ON cc.`code` = 'non-fiksi'
SET b.`content_category_id` = cc.`id`
WHERE b.`content_category_id` IS NULL;
