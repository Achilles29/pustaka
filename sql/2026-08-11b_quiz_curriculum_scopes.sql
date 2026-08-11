-- Kurikulum Merdeka guardrail for the quiz bank.
-- Run once after the quiz master tables already exist.

CREATE TABLE IF NOT EXISTS quiz_curriculum_scopes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    grade_level_id INT NOT NULL,
    subject_id INT NOT NULL,
    phase_code VARCHAR(12) NOT NULL,
    learning_focus VARCHAR(255) NOT NULL,
    assessment_rules TEXT NULL,
    is_optional TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_quiz_curriculum_scope (grade_level_id, subject_id),
    KEY idx_quiz_curriculum_scope_active (is_active, grade_level_id),
    CONSTRAINT fk_quiz_curriculum_scope_grade FOREIGN KEY (grade_level_id) REFERENCES quiz_grade_levels(id),
    CONSTRAINT fk_quiz_curriculum_scope_subject FOREIGN KEY (subject_id) REFERENCES quiz_subjects(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Selaraskan nama mata pelajaran di master tanpa mengubah kode yang telah dipakai sistem.
UPDATE quiz_subjects SET name = 'Pendidikan Pancasila' WHERE code = 'ppkn';
UPDATE quiz_subjects SET name = 'PJOK' WHERE code = 'penjaskes';
UPDATE quiz_subjects SET name = 'Informatika' WHERE code = 'tik';

INSERT INTO quiz_subjects (code, name, icon, color, sort_order, is_active)
SELECT 'paud_terpadu', 'PAUD Terpadu', 'ti ti-puzzle', '#8b5cf6', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM quiz_subjects WHERE code = 'paud_terpadu');

INSERT INTO quiz_subjects (code, name, icon, color, sort_order, is_active)
SELECT 'ipas', 'IPAS', 'ti ti-world', '#0f9f8f', 5, 1
WHERE NOT EXISTS (SELECT 1 FROM quiz_subjects WHERE code = 'ipas');

-- PAUD: satu area terpadu, bukan tes Matematika/Bahasa Indonesia per mata pelajaran.
INSERT IGNORE INTO quiz_curriculum_scopes (grade_level_id, subject_id, phase_code, learning_focus, assessment_rules)
SELECT g.id, s.id, 'PAUD', 'Literasi, pramatematika, eksplorasi sains, seni, dan sosial-emosional terpadu',
       'Gunakan aktivitas visual/interaktif dan konteks bermain; hindari operasi hitung formal, pembagian, serta bacaan panjang.'
FROM quiz_grade_levels g
JOIN quiz_subjects s ON s.code = 'paud_terpadu'
WHERE g.code IN ('tk_a', 'tk_b');

-- Fase A: kelas 1–2 SD.
INSERT IGNORE INTO quiz_curriculum_scopes (grade_level_id, subject_id, phase_code, learning_focus, assessment_rules)
SELECT g.id, s.id, 'A',
       CASE s.code
           WHEN 'matematika' THEN 'Bilangan sampai 100; penjumlahan dan pengurangan konkret sampai 20; pola, bentuk, dan pengukuran sederhana'
           WHEN 'bahasa_indonesia' THEN 'Menyimak, membaca kata/teks sederhana, berbicara, dan menulis awal'
           WHEN 'ppkn' THEN 'Identitas diri, keluarga, aturan sederhana, dan hidup rukun'
           WHEN 'seni_budaya' THEN 'Eksplorasi unsur dan ekspresi seni'
           WHEN 'penjaskes' THEN 'Gerak dasar, kebugaran, dan keselamatan diri'
       END,
       'Gunakan bahasa konkret dan singkat; satu kompetensi per soal; jangan melampaui capaian fase.'
FROM quiz_grade_levels g
JOIN quiz_subjects s ON s.code IN ('matematika', 'bahasa_indonesia', 'ppkn', 'seni_budaya', 'penjaskes')
WHERE g.code IN ('sd_1', 'sd_2');

-- Fase B: kelas 3–4 SD. IPAS dipakai, bukan IPA dan IPS terpisah.
INSERT IGNORE INTO quiz_curriculum_scopes (grade_level_id, subject_id, phase_code, learning_focus, assessment_rules)
SELECT g.id, s.id, 'B',
       CASE s.code
           WHEN 'matematika' THEN 'Bilangan sampai 10.000, operasi hitung, pecahan sederhana, pengukuran, dan data sederhana'
           WHEN 'bahasa_indonesia' THEN 'Memahami dan menghasilkan teks informatif/naratif sederhana'
           WHEN 'ipas' THEN 'Fenomena alam dan sosial sekitar melalui observasi, pengelompokan, dan penalaran sederhana'
           WHEN 'ppkn' THEN 'Aturan, hak-kewajiban, keberagaman, dan kerja sama'
           WHEN 'seni_budaya' THEN 'Apresiasi dan kreasi seni'
           WHEN 'penjaskes' THEN 'Gerak, kebugaran, dan pola hidup sehat'
       END,
       'Gunakan konteks keseharian; data dan angka harus dapat diselesaikan sesuai capaian fase.'
FROM quiz_grade_levels g
JOIN quiz_subjects s ON s.code IN ('matematika', 'bahasa_indonesia', 'ipas', 'ppkn', 'seni_budaya', 'penjaskes')
WHERE g.code IN ('sd_3', 'sd_4');

-- Fase C: kelas 5–6 SD. Informatika hanya ditandai pilihan sesuai kesiapan sekolah.
INSERT IGNORE INTO quiz_curriculum_scopes (grade_level_id, subject_id, phase_code, learning_focus, assessment_rules, is_optional)
SELECT g.id, s.id, 'C',
       CASE s.code
           WHEN 'matematika' THEN 'Bilangan, pecahan, operasi, rasio, pengukuran, geometri, dan data sesuai Fase C'
           WHEN 'bahasa_indonesia' THEN 'Memahami dan menanggapi beragam teks; menulis terstruktur'
           WHEN 'ipas' THEN 'Sistem alam, sosial, dan perubahan di lingkungan'
           WHEN 'ppkn' THEN 'Pancasila, norma, keberagaman, dan tanggung jawab warga'
           WHEN 'seni_budaya' THEN 'Apresiasi, refleksi, dan karya seni'
           WHEN 'penjaskes' THEN 'Keterampilan gerak, kebugaran, kesehatan, dan keselamatan'
           WHEN 'tik' THEN 'Literasi digital, berpikir komputasional, dan penggunaan teknologi aman'
       END,
       'Setiap soal harus dapat dilacak ke elemen CP; Informatika hanya digunakan bila diimplementasikan sekolah.',
       CASE WHEN s.code = 'tik' THEN 1 ELSE 0 END
FROM quiz_grade_levels g
JOIN quiz_subjects s ON s.code IN ('matematika', 'bahasa_indonesia', 'ipas', 'ppkn', 'seni_budaya', 'penjaskes', 'tik')
WHERE g.code IN ('sd_5', 'sd_6');

-- Fase D: SMP kelas 7–9.
INSERT IGNORE INTO quiz_curriculum_scopes (grade_level_id, subject_id, phase_code, learning_focus, assessment_rules, is_optional)
SELECT g.id, s.id, 'D', 'Sesuai elemen Capaian Pembelajaran Fase D untuk mata pelajaran terkait',
       'Soal harus satu konsep, berbahasa jelas, dan memiliki kunci serta pembahasan yang dapat diverifikasi.',
       CASE WHEN s.code = 'tik' THEN 1 ELSE 0 END
FROM quiz_grade_levels g
JOIN quiz_subjects s ON s.code IN ('matematika', 'bahasa_indonesia', 'bahasa_inggris', 'ipa', 'ips', 'ppkn', 'agama', 'seni_budaya', 'penjaskes', 'tik')
WHERE g.code IN ('smp_7', 'smp_8', 'smp_9');

-- Fase E/F: SMA kelas 10–12. Mapel peminatan hanya digunakan sesuai kurikulum satuan pendidikan.
INSERT IGNORE INTO quiz_curriculum_scopes (grade_level_id, subject_id, phase_code, learning_focus, assessment_rules, is_optional)
SELECT g.id, s.id,
       CASE WHEN g.code = 'sma_10' THEN 'E' ELSE 'F' END,
       'Sesuai elemen Capaian Pembelajaran fase dan mata pelajaran terkait',
       'Soal harus memuat konteks yang cukup, satu jawaban benar, dan pembahasan yang menjelaskan penalarannya.',
       CASE WHEN s.code IN ('sejarah', 'geografi', 'ekonomi', 'biologi', 'fisika', 'kimia', 'tik') THEN 1 ELSE 0 END
FROM quiz_grade_levels g
JOIN quiz_subjects s ON s.code IN ('matematika', 'bahasa_indonesia', 'bahasa_inggris', 'ppkn', 'agama', 'seni_budaya', 'penjaskes', 'tik', 'sejarah', 'geografi', 'ekonomi', 'biologi', 'fisika', 'kimia')
WHERE g.code IN ('sma_10', 'sma_11', 'sma_12');
