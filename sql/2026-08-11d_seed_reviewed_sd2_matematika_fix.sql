-- Perbaikan penerapan awal: melengkapi soal ke-20 untuk sesi LAT-SD2-MAT-CP26.
SET @subject_id := (SELECT id FROM quiz_subjects WHERE code = 'matematika' LIMIT 1);
SET @grade_id := (SELECT id FROM quiz_grade_levels WHERE code = 'sd_2' LIMIT 1);

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
SELECT @subject_id, @grade_id, 'multiple_choice', 'easy',
       'Tanaman A tingginya 12 kubus satuan. Tanaman B tingginya 9 kubus satuan. Tanaman yang lebih tinggi adalah ....',
       '12 lebih besar daripada 9, sehingga tanaman A lebih tinggi.', 0, 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM quiz_questions
    WHERE deleted_at IS NULL
      AND question_text = 'Tanaman A tingginya 12 kubus satuan. Tanaman B tingginya 9 kubus satuan. Tanaman yang lebih tinggi adalah ....'
);
SET @q := LAST_INSERT_ID();
INSERT INTO quiz_question_options (question_id, option_index, option_text)
SELECT @q, 0, 'Tanaman A' WHERE @q > 0
UNION ALL SELECT @q, 1, 'Tanaman B' WHERE @q > 0
UNION ALL SELECT @q, 2, 'Keduanya sama tinggi' WHERE @q > 0
UNION ALL SELECT @q, 3, 'Tidak dapat diketahui' WHERE @q > 0;
