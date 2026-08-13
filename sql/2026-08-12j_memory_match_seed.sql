-- Konten awal Memory Match. Aman dijalankan ulang: kategori, set, dan item
-- diperiksa dahulu berdasarkan nama/term sebelum ditambahkan.

-- Kategori per jenjang dan mapel.
INSERT INTO learn_game_categories (game_type_id, grade_level_id, subject_id, name, description, is_active)
SELECT gt.id, 2, 38, 'Hewan dan Suaranya', 'Mencocokkan hewan dengan bunyi yang dikenalnya.', 1 FROM learn_game_types gt
WHERE gt.code = 'memory_match' AND NOT EXISTS (SELECT 1 FROM learn_game_categories c WHERE c.game_type_id = gt.id AND c.name = 'Hewan dan Suaranya');
INSERT INTO learn_game_categories (game_type_id, grade_level_id, subject_id, name, description, is_active)
SELECT gt.id, 3, 2, 'Kosakata Sehari-hari', 'Mencocokkan kata dengan makna sederhana.', 1 FROM learn_game_types gt
WHERE gt.code = 'memory_match' AND NOT EXISTS (SELECT 1 FROM learn_game_categories c WHERE c.game_type_id = gt.id AND c.name = 'Kosakata Sehari-hari');
INSERT INTO learn_game_categories (game_type_id, grade_level_id, subject_id, name, description, is_active)
SELECT gt.id, 4, 1, 'Berhitung Dasar', 'Mencocokkan operasi hitung dengan hasilnya.', 1 FROM learn_game_types gt
WHERE gt.code = 'memory_match' AND NOT EXISTS (SELECT 1 FROM learn_game_categories c WHERE c.game_type_id = gt.id AND c.name = 'Berhitung Dasar');
INSERT INTO learn_game_categories (game_type_id, grade_level_id, subject_id, name, description, is_active)
SELECT gt.id, 5, 39, 'Bagian Tumbuhan', 'Mencocokkan bagian tumbuhan dengan fungsinya.', 1 FROM learn_game_types gt
WHERE gt.code = 'memory_match' AND NOT EXISTS (SELECT 1 FROM learn_game_categories c WHERE c.game_type_id = gt.id AND c.name = 'Bagian Tumbuhan');
INSERT INTO learn_game_categories (game_type_id, grade_level_id, subject_id, name, description, is_active)
SELECT gt.id, 6, 39, 'Energi di Sekitar Kita', 'Mencocokkan sumber energi dengan pemanfaatannya.', 1 FROM learn_game_types gt
WHERE gt.code = 'memory_match' AND NOT EXISTS (SELECT 1 FROM learn_game_categories c WHERE c.game_type_id = gt.id AND c.name = 'Energi di Sekitar Kita');
INSERT INTO learn_game_categories (game_type_id, grade_level_id, subject_id, name, description, is_active)
SELECT gt.id, 9, 4, 'Sains Dasar', 'Mencocokkan konsep sains dengan pengertiannya.', 1 FROM learn_game_types gt
WHERE gt.code = 'memory_match' AND NOT EXISTS (SELECT 1 FROM learn_game_categories c WHERE c.game_type_id = gt.id AND c.name = 'Sains Dasar');

-- Satu set aktif untuk setiap kategori.
INSERT INTO learn_game_content_sets (category_id, name, difficulty, is_active)
SELECT c.id, 'Kenali Hewan', 'easy', 1 FROM learn_game_categories c WHERE c.name = 'Hewan dan Suaranya' AND NOT EXISTS (SELECT 1 FROM learn_game_content_sets s WHERE s.category_id = c.id AND s.name = 'Kenali Hewan');
INSERT INTO learn_game_content_sets (category_id, name, difficulty, is_active)
SELECT c.id, 'Kata di Rumah dan Sekolah', 'easy', 1 FROM learn_game_categories c WHERE c.name = 'Kosakata Sehari-hari' AND NOT EXISTS (SELECT 1 FROM learn_game_content_sets s WHERE s.category_id = c.id AND s.name = 'Kata di Rumah dan Sekolah');
INSERT INTO learn_game_content_sets (category_id, name, difficulty, is_active)
SELECT c.id, 'Hitung Sampai 20', 'easy', 1 FROM learn_game_categories c WHERE c.name = 'Berhitung Dasar' AND NOT EXISTS (SELECT 1 FROM learn_game_content_sets s WHERE s.category_id = c.id AND s.name = 'Hitung Sampai 20');
INSERT INTO learn_game_content_sets (category_id, name, difficulty, is_active)
SELECT c.id, 'Fungsi Bagian Tumbuhan', 'medium', 1 FROM learn_game_categories c WHERE c.name = 'Bagian Tumbuhan' AND NOT EXISTS (SELECT 1 FROM learn_game_content_sets s WHERE s.category_id = c.id AND s.name = 'Fungsi Bagian Tumbuhan');
INSERT INTO learn_game_content_sets (category_id, name, difficulty, is_active)
SELECT c.id, 'Sumber dan Manfaat Energi', 'medium', 1 FROM learn_game_categories c WHERE c.name = 'Energi di Sekitar Kita' AND NOT EXISTS (SELECT 1 FROM learn_game_content_sets s WHERE s.category_id = c.id AND s.name = 'Sumber dan Manfaat Energi');
INSERT INTO learn_game_content_sets (category_id, name, difficulty, is_active)
SELECT c.id, 'Konsep Sains Kelas 7', 'medium', 1 FROM learn_game_categories c WHERE c.name = 'Sains Dasar' AND NOT EXISTS (SELECT 1 FROM learn_game_content_sets s WHERE s.category_id = c.id AND s.name = 'Konsep Sains Kelas 7');

-- 8 pasangan per set; aplikasi secara acak mengambil maksimum 6 pasangan saat dimainkan.
INSERT INTO learn_game_content_items (set_id, term, definition, sort_order)
SELECT s.id, v.term, v.definition, v.sort_order FROM learn_game_content_sets s
CROSS JOIN (SELECT 'Kucing' term, 'Meong' definition, 1 sort_order UNION ALL SELECT 'Ayam', 'Kukuruyuk', 2 UNION ALL SELECT 'Sapi', 'Moo', 3 UNION ALL SELECT 'Kambing', 'Mbek', 4 UNION ALL SELECT 'Bebek', 'Kwek kwek', 5 UNION ALL SELECT 'Anjing', 'Guk guk', 6 UNION ALL SELECT 'Burung', 'Cuit cuit', 7 UNION ALL SELECT 'Kuda', 'Ringkik', 8) v
JOIN learn_game_categories c ON c.id = s.category_id
WHERE c.name = 'Hewan dan Suaranya' AND s.name = 'Kenali Hewan' AND NOT EXISTS (SELECT 1 FROM learn_game_content_items i WHERE i.set_id = s.id AND i.term = v.term);

INSERT INTO learn_game_content_items (set_id, term, definition, sort_order)
SELECT s.id, v.term, v.definition, v.sort_order FROM learn_game_content_sets s
CROSS JOIN (SELECT 'Buku' term, 'Dibaca untuk menambah pengetahuan' definition, 1 sort_order UNION ALL SELECT 'Pensil', 'Digunakan untuk menulis', 2 UNION ALL SELECT 'Meja', 'Tempat meletakkan barang', 3 UNION ALL SELECT 'Kursi', 'Tempat untuk duduk', 4 UNION ALL SELECT 'Pintu', 'Jalan masuk dan keluar ruangan', 5 UNION ALL SELECT 'Jendela', 'Tempat masuk cahaya dan udara', 6 UNION ALL SELECT 'Tas', 'Tempat membawa perlengkapan', 7 UNION ALL SELECT 'Papan tulis', 'Tempat guru menulis di kelas', 8) v
JOIN learn_game_categories c ON c.id = s.category_id
WHERE c.name = 'Kosakata Sehari-hari' AND s.name = 'Kata di Rumah dan Sekolah' AND NOT EXISTS (SELECT 1 FROM learn_game_content_items i WHERE i.set_id = s.id AND i.term = v.term);

INSERT INTO learn_game_content_items (set_id, term, definition, sort_order)
SELECT s.id, v.term, v.definition, v.sort_order FROM learn_game_content_sets s
CROSS JOIN (SELECT '3 + 4' term, '7' definition, 1 sort_order UNION ALL SELECT '5 + 6', '11', 2 UNION ALL SELECT '9 - 3', '6', 3 UNION ALL SELECT '12 - 5', '7', 4 UNION ALL SELECT '8 + 7', '15', 5 UNION ALL SELECT '14 - 4', '10', 6 UNION ALL SELECT '10 + 9', '19', 7 UNION ALL SELECT '20 - 8', '12', 8) v
JOIN learn_game_categories c ON c.id = s.category_id
WHERE c.name = 'Berhitung Dasar' AND s.name = 'Hitung Sampai 20' AND NOT EXISTS (SELECT 1 FROM learn_game_content_items i WHERE i.set_id = s.id AND i.term = v.term);

INSERT INTO learn_game_content_items (set_id, term, definition, sort_order)
SELECT s.id, v.term, v.definition, v.sort_order FROM learn_game_content_sets s
CROSS JOIN (SELECT 'Akar' term, 'Menyerap air dan mineral dari tanah' definition, 1 sort_order UNION ALL SELECT 'Batang', 'Menopang tumbuhan dan menyalurkan air', 2 UNION ALL SELECT 'Daun', 'Tempat membuat makanan dengan bantuan cahaya', 3 UNION ALL SELECT 'Bunga', 'Alat perkembangbiakan tumbuhan', 4 UNION ALL SELECT 'Buah', 'Melindungi biji dan dapat menjadi makanan', 5 UNION ALL SELECT 'Biji', 'Calon tumbuhan baru', 6 UNION ALL SELECT 'Klorofil', 'Zat hijau daun yang menangkap cahaya', 7 UNION ALL SELECT 'Fotosintesis', 'Proses tumbuhan membuat makanan', 8) v
JOIN learn_game_categories c ON c.id = s.category_id
WHERE c.name = 'Bagian Tumbuhan' AND s.name = 'Fungsi Bagian Tumbuhan' AND NOT EXISTS (SELECT 1 FROM learn_game_content_items i WHERE i.set_id = s.id AND i.term = v.term);

INSERT INTO learn_game_content_items (set_id, term, definition, sort_order)
SELECT s.id, v.term, v.definition, v.sort_order FROM learn_game_content_sets s
CROSS JOIN (SELECT 'Matahari' term, 'Sumber energi cahaya dan panas' definition, 1 sort_order UNION ALL SELECT 'Angin', 'Dapat menggerakkan kincir angin', 2 UNION ALL SELECT 'Air mengalir', 'Dapat memutar turbin pembangkit listrik', 3 UNION ALL SELECT 'Baterai', 'Menyimpan energi listrik untuk alat kecil', 4 UNION ALL SELECT 'Makanan', 'Sumber energi bagi tubuh manusia', 5 UNION ALL SELECT 'Bensin', 'Bahan bakar kendaraan bermotor', 6 UNION ALL SELECT 'Panel surya', 'Mengubah cahaya matahari menjadi listrik', 7 UNION ALL SELECT 'Hemat energi', 'Menggunakan energi seperlunya', 8) v
JOIN learn_game_categories c ON c.id = s.category_id
WHERE c.name = 'Energi di Sekitar Kita' AND s.name = 'Sumber dan Manfaat Energi' AND NOT EXISTS (SELECT 1 FROM learn_game_content_items i WHERE i.set_id = s.id AND i.term = v.term);

INSERT INTO learn_game_content_items (set_id, term, definition, sort_order)
SELECT s.id, v.term, v.definition, v.sort_order FROM learn_game_content_sets s
CROSS JOIN (SELECT 'Sel' term, 'Unit struktural dan fungsional terkecil makhluk hidup' definition, 1 sort_order UNION ALL SELECT 'Organisme' , 'Makhluk hidup tunggal yang dapat menjalankan fungsi kehidupan', 2 UNION ALL SELECT 'Populasi', 'Kumpulan makhluk hidup sejenis di tempat dan waktu tertentu', 3 UNION ALL SELECT 'Ekosistem', 'Interaksi makhluk hidup dengan lingkungannya', 4 UNION ALL SELECT 'Massa', 'Ukuran banyaknya materi dalam benda', 5 UNION ALL SELECT 'Gaya', 'Tarikan atau dorongan pada benda', 6 UNION ALL SELECT 'Suhu', 'Ukuran derajat panas atau dingin benda', 7 UNION ALL SELECT 'Daur air', 'Peredaran air dari bumi ke atmosfer dan kembali lagi', 8) v
JOIN learn_game_categories c ON c.id = s.category_id
WHERE c.name = 'Sains Dasar' AND s.name = 'Konsep Sains Kelas 7' AND NOT EXISTS (SELECT 1 FROM learn_game_content_items i WHERE i.set_id = s.id AND i.term = v.term);
