-- Batch perdana yang ditulis dan diperiksa satu per satu.
-- Kurikulum Merdeka Fase A, kalibrasi SD Kelas 2.
-- Setiap soal: satu kompetensi, empat opsi, satu kunci, dan pembahasan.

SET @subject_id := (SELECT id FROM quiz_subjects WHERE code = 'matematika' LIMIT 1);
SET @grade_id := (SELECT id FROM quiz_grade_levels WHERE code = 'sd_2' LIMIT 1);

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Bilangan yang tepat sebelum 48 adalah ....', 'Urutan bilangan bertambah satu. Sebelum 48 adalah 47.', 2, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'46'),(@q,1,'49'),(@q,2,'47'),(@q,3,'48');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Bilangan yang tepat sesudah 69 adalah ....', 'Sesudah 69 adalah 70.', 3, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'68'),(@q,1,'71'),(@q,2,'69'),(@q,3,'70');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Pada bilangan 63, angka 6 menunjukkan ....', 'Angka 6 berada di tempat puluhan, jadi nilainya enam puluhan.', 1, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'6 satuan'),(@q,1,'6 puluhan'),(@q,2,'60 satuan'),(@q,3,'3 puluhan');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Bentuk uraian yang tepat untuk 74 adalah ....', '74 terdiri dari 7 puluhan dan 4 satuan, yaitu 70 + 4.', 2, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'7 + 4'),(@q,1,'40 + 7'),(@q,2,'70 + 4'),(@q,3,'74 + 0');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'medium', 'Urutan bilangan dari yang terkecil adalah ....', 'Bandingkan puluhannya terlebih dahulu: 34, kemudian 39, lalu 43.', 1, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'43, 39, 34'),(@q,1,'34, 39, 43'),(@q,2,'39, 34, 43'),(@q,3,'34, 43, 39');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Tanda yang tepat untuk 58 .... 85 adalah ....', '58 lebih kecil daripada 85, sehingga tandanya adalah <.', 0, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'<'),(@q,1,'>'),(@q,2,'='),(@q,3,'+');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Ada 8 kelereng merah dan 7 kelereng biru. Jumlah kelereng semuanya adalah ....', '8 + 7 = 15.', 2, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'14'),(@q,1,'13'),(@q,2,'15'),(@q,3,'16');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Dina mempunyai 16 stiker. Ia memberikan 9 stiker kepada temannya. Sisa stiker Dina adalah ....', '16 - 9 = 7.', 1, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'6'),(@q,1,'7'),(@q,2,'8'),(@q,3,'9');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'medium', '12 + .... = 20. Bilangan yang tepat adalah ....', '20 - 12 = 8, jadi 12 + 8 = 20.', 3, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'6'),(@q,1,'7'),(@q,2,'9'),(@q,3,'8');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'medium', '19 - .... = 10. Bilangan yang tepat adalah ....', '19 - 9 = 10.', 0, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'9'),(@q,1,'8'),(@q,2,'10'),(@q,3,'11');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'medium', 'Tiga piring masing-masing berisi 4 apel. Jumlah apel semuanya adalah ....', 'Hitung 4 + 4 + 4 = 12.', 2, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'7'),(@q,1,'8'),(@q,2,'12'),(@q,3,'16');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'medium', 'Sepuluh biskuit dibagikan sama banyak kepada dua anak. Setiap anak mendapat .... biskuit.', '10 dibagi dua sama banyak menjadi 5 dan 5.', 1, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'4'),(@q,1,'5'),(@q,2,'8'),(@q,3,'10');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Bangun datar yang mempunyai 4 sisi sama panjang dan 4 sudut adalah ....', 'Persegi memiliki empat sisi sama panjang dan empat sudut.', 2, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'Segitiga'),(@q,1,'Lingkaran'),(@q,2,'Persegi'),(@q,3,'Oval');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Bola berbentuk seperti bangun ruang ....', 'Bola memiliki bentuk bangun ruang bola.', 3, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'Kubus'),(@q,1,'Balok'),(@q,2,'Kerucut'),(@q,3,'Bola');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Buku berada di sebelah kiri pensil. Letak pensil terhadap buku adalah di sebelah ....', 'Jika buku di kiri pensil, maka pensil berada di kanan buku.', 1, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'kiri'),(@q,1,'kanan'),(@q,2,'atas'),(@q,3,'bawah');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Rani bermain selama 20 menit. Bima bermain selama 10 menit. Yang bermain lebih lama adalah ....', '20 menit lebih lama daripada 10 menit.', 0, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'Rani'),(@q,1,'Bima'),(@q,2,'keduanya sama'),(@q,3,'tidak dapat diketahui');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'medium', 'Data buah favorit: apel dipilih 4 anak, pisang dipilih 2 anak, dan jeruk dipilih 3 anak. Buah yang paling banyak dipilih adalah ....', 'Jumlah pemilih apel adalah 4, paling banyak dibandingkan 2 dan 3.', 2, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'Pisang'),(@q,1,'Jeruk'),(@q,2,'Apel'),(@q,3,'Sama banyak');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Pola warna berikut adalah merah, biru, merah, biru, .... Warna berikutnya adalah ....', 'Polanya berulang: merah lalu biru.', 0, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'merah'),(@q,1,'kuning'),(@q,2,'hijau'),(@q,3,'biru');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Satu roti dibagi menjadi dua bagian sama besar. Satu bagian roti disebut ....', 'Satu dari dua bagian sama besar disebut setengah atau 1/2.', 1, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'seperempat'),(@q,1,'setengah'),(@q,2,'dua roti'),(@q,3,'satu utuh');

INSERT INTO quiz_questions (subject_id, grade_level_id, type, difficulty, question_text, explanation, correct_option_index, score_weight, is_active)
VALUES (@subject_id, @grade_id, 'multiple_choice', 'easy', 'Tanaman A tingginya 12 kubus satuan. Tanaman B tingginya 9 kubus satuan. Tanaman yang lebih tinggi adalah ....', '12 lebih besar daripada 9, sehingga tanaman A lebih tinggi.', 0, 1, 1);
SET @q := LAST_INSERT_ID(); INSERT INTO quiz_question_options (question_id, option_index, option_text) VALUES (@q,0,'Tanaman A'),(@q,1,'Tanaman B'),(@q,2,'Keduanya sama tinggi'),(@q,3,'Tidak dapat diketahui');

INSERT INTO quiz_sessions (code, title, type, status, subject_id, grade_level_id, difficulty_filter, question_count, time_limit_minutes, shuffle_questions, shuffle_options, show_result_immediately, allow_review, max_attempts, passing_score, access_mode, is_published, is_paused, description, instructions)
VALUES ('LAT-SD2-MAT-CP26', 'Matematika SD Kelas 2 — Bilangan dan Bentuk', 'practice', 'open', @subject_id, @grade_id, 'mixed', 20, 30, 1, 1, 1, 1, 0, 60, 'public', 1, 0,
        'Latihan Fase A: bilangan, operasi konkret, bentuk, posisi, pola, dan data sederhana.',
        'Baca setiap soal dengan teliti. Pilih satu jawaban yang paling tepat. Hasil dan pembahasan tersedia setelah latihan selesai.');
