-- Perbaikan data sesi yang pernah tersimpan ulang dari editor lama.
-- Editor membandingkan integer dengan string secara strict, sehingga option
-- yang benar tidak terlihat selected dan dapat terkirim kembali sebagai NULL.
UPDATE `quiz_sessions`
SET `subject_id` = 1, `grade_level_id` = 4
WHERE `code` IN ('LAT26EC424', 'LAT41S26', 'LAT-SD2-MAT-CP26')
  AND `deleted_at` IS NULL;

UPDATE `quiz_sessions`
SET `subject_id` = 3, `grade_level_id` = 4
WHERE `code` = 'LAT43S26'
  AND `deleted_at` IS NULL;

UPDATE `quiz_sessions`
SET `subject_id` = 4, `grade_level_id` = 4
WHERE `code` = 'LAT44S26'
  AND `deleted_at` IS NULL;
