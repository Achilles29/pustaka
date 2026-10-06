-- Math Expedition: game matematika berjenjang dengan hint, pembahasan, dan tips.
-- Jalankan sekali pada database pustaka.

CREATE TABLE IF NOT EXISTS math_expedition_levels (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(60) NOT NULL,
  stage_number TINYINT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  subtitle VARCHAR(180) DEFAULT NULL,
  skill VARCHAR(160) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  color VARCHAR(20) NOT NULL DEFAULT '#2563eb',
  icon VARCHAR(80) NOT NULL DEFAULT 'ti ti-math',
  passing_correct TINYINT UNSIGNED NOT NULL DEFAULT 3,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_math_expedition_level_code (code),
  UNIQUE KEY uq_math_expedition_stage (stage_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS math_expedition_questions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  level_id INT UNSIGNED NOT NULL,
  question_code VARCHAR(80) NOT NULL,
  prompt TEXT NOT NULL,
  options_json TEXT NOT NULL,
  correct_option CHAR(1) NOT NULL,
  hint TEXT DEFAULT NULL,
  hint_deeper TEXT DEFAULT NULL,
  explanation TEXT DEFAULT NULL,
  tip TEXT DEFAULT NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_math_expedition_question_code (question_code),
  KEY idx_math_expedition_question_level (level_id, is_active),
  CONSTRAINT fk_math_expedition_question_level FOREIGN KEY (level_id) REFERENCES math_expedition_levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS math_expedition_attempts (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  level_id INT UNSIGNED NOT NULL,
  correct_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
  question_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
  score TINYINT UNSIGNED NOT NULL DEFAULT 0,
  duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  completed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_math_expedition_attempt_user (user_id, completed_at),
  KEY idx_math_expedition_attempt_level (level_id),
  CONSTRAINT fk_math_expedition_attempt_level FOREIGN KEY (level_id) REFERENCES math_expedition_levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS math_expedition_progress (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  level_id INT UNSIGNED NOT NULL,
  best_score TINYINT UNSIGNED NOT NULL DEFAULT 0,
  best_correct TINYINT UNSIGNED NOT NULL DEFAULT 0,
  attempts INT UNSIGNED NOT NULL DEFAULT 0,
  completed_at DATETIME DEFAULT NULL,
  last_played_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_math_expedition_progress (user_id, level_id),
  KEY idx_math_expedition_progress_level (level_id),
  CONSTRAINT fk_math_expedition_progress_level FOREIGN KEY (level_id) REFERENCES math_expedition_levels(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO math_expedition_levels (code,stage_number,title,subtitle,skill,description,color,icon,passing_correct,is_active) VALUES
('angka-ceria',1,'Angka Ceria','Mengenal dan membandingkan angka 1–10','Mengenal bilangan','Mulai dari pondasi: membaca angka, urutan, dan perbandingan sederhana.','#0ea5e9','ti ti-numbers',3,1),
('tambah-ceria',2,'Tambah Ceria','Penjumlahan sampai 10','Penjumlahan dasar','Gabungkan dua kelompok dan temukan jumlahnya.','#22c55e','ti ti-circle-plus',3,1),
('kurang-cermat',3,'Kurang Cermat','Pengurangan sampai 20','Pengurangan dasar','Latih cara mengambil sebagian tanpa mengurangi ketelitian.','#14b8a6','ti ti-circle-minus',3,1),
('puluhan-hebat',4,'Puluhan Hebat','Nilai tempat dan operasi sampai 100','Nilai tempat','Pahami puluhan dan satuan agar berhitung lebih teratur.','#6366f1','ti ti-stack-2',3,1),
('kali-berani',5,'Kali Berani','Perkalian sebagai penjumlahan berulang','Perkalian dasar','Kuasai kelompok-kelompok sama banyak dan fakta perkalian awal.','#8b5cf6','ti ti-x',3,1),
('bagi-adil',6,'Bagi Adil','Pembagian dengan hasil bulat','Pembagian dasar','Bagikan sama rata dan hubungan pembagian dengan perkalian.','#d946ef','ti ti-divide',3,1),
('pecahan-dekat',7,'Pecahan Dekat','Pecahan dalam kehidupan sehari-hari','Pecahan dasar','Gunakan gambar dan situasi nyata untuk membaca pecahan.','#f97316','ti ti-pie-chart',3,1),
('strategi-matematika',8,'Strategi Matematika','Pola, satuan, dan operasi campuran','Penalaran matematika','Tutup ekspedisi dasar dengan strategi memilih operasi yang tepat.','#ef4444','ti ti-brain',3,1),
('jam-penjelajah',9,'Stasiun Waktu','Membaca jam dan menghitung durasi','Waktu dan durasi','Bantu kereta hutan tiba tepat waktu dengan membaca jam dan selisih waktu.','#2563eb','ti ti-clock-hour-4',3,1),
('bengkel-bentuk',10,'Bengkel Bentuk','Bangun datar, keliling, dan sisi','Geometri','Rakit jembatan hutan dari bentuk yang tepat dan ukur sisi-sisinya.','#7c3aed','ti ti-shape',3,1),
('kebun-data',11,'Kebun Data','Membaca data dan membandingkan jumlah','Penyajian data','Baca papan panen untuk menentukan pilihan terbaik di kebun penjelajah.','#16a34a','ti ti-chart-bar',3,1),
('menara-logika',12,'Menara Logika','Masalah bertahap dan strategi','Pemecahan masalah','Naik ke menara terakhir dengan memilih informasi dan langkah yang paling tepat.','#db2777','ti ti-brain',3,1)
ON DUPLICATE KEY UPDATE title=VALUES(title),subtitle=VALUES(subtitle),skill=VALUES(skill),description=VALUES(description),color=VALUES(color),icon=VALUES(icon),passing_correct=VALUES(passing_correct),is_active=VALUES(is_active);

-- Setiap level berisi lima soal yang telah ditelaah, dengan satu jawaban tegas.
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'angka-01','Angka manakah yang paling besar?','{"A":"4","B":"7","C":"5","D":"2"}','B','Bayangkan posisi angka pada garis bilangan.','Pada garis bilangan, angka yang paling jauh ke kanan nilainya paling besar.','7 lebih besar daripada 5, 4, dan 2.','Saat membandingkan angka satu digit, lihat angka yang paling jauh ke kanan pada urutan 0–9.',1,1 FROM math_expedition_levels WHERE code='angka-ceria'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'angka-02','Angka yang tepat setelah 8 adalah ...','{"A":"7","B":"9","C":"10","D":"6"}','B','Ucapkan urutan angka: 6, 7, 8, ...','Tambahkan satu pada 8.','Setelah 8 adalah 9.','Untuk mencari angka setelahnya, tambahkan 1.',2,1 FROM math_expedition_levels WHERE code='angka-ceria'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'angka-03','Manakah urutan dari kecil ke besar yang benar?','{"A":"3, 5, 8","B":"8, 5, 3","C":"5, 3, 8","D":"8, 3, 5"}','A','Mulailah dari angka yang nilainya paling kecil.','Bandingkan 3, 5, dan 8 satu per satu.','3 lebih kecil dari 5, dan 5 lebih kecil dari 8; jadi urutannya 3, 5, 8.','Urutan naik berarti nilainya bertambah dari kiri ke kanan.',3,1 FROM math_expedition_levels WHERE code='angka-ceria'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'angka-04','Ada 6 bintang. Jika ditambah 1 bintang, jumlahnya menjadi ...','{"A":"5","B":"6","C":"7","D":"8"}','C','Tambahkan satu benda setelah enam.','Hitung: 6 lalu satu langkah lagi menjadi 7.','6 ditambah 1 sama dengan 7.','Menambah 1 berarti maju satu langkah pada garis bilangan.',4,1 FROM math_expedition_levels WHERE code='angka-ceria'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'angka-05','Angka manakah yang berada di antara 4 dan 6?','{"A":"3","B":"5","C":"7","D":"8"}','B','Ucapkan urutan 4, ..., 6.','Hanya ada satu angka setelah 4 sebelum sampai 6.','Angka di antara 4 dan 6 adalah 5.','Gunakan urutan bilangan untuk menemukan angka di tengah.',5,1 FROM math_expedition_levels WHERE code='angka-ceria'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'tambah-01','3 + 4 = ...','{"A":"6","B":"7","C":"8","D":"9"}','B','Mulai dari 3, lalu maju empat langkah.','3, lalu 4, 5, 6, 7.','3 ditambah 4 sama dengan 7.','Untuk penjumlahan kecil, lanjutkan hitungan dari angka pertama.',1,1 FROM math_expedition_levels WHERE code='tambah-ceria'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'tambah-02','Lani memiliki 5 pensil. Ia mendapat 2 pensil lagi. Berapa pensil Lani sekarang?','{"A":"6","B":"7","C":"8","D":"3"}','B','Kata “mendapat lagi” berarti jumlahnya bertambah.','Gabungkan 5 pensil dan 2 pensil.','5 + 2 = 7, jadi Lani memiliki 7 pensil.','Cari kata petunjuk: mendapat, bertambah, dan digabung biasanya memakai penjumlahan.',2,1 FROM math_expedition_levels WHERE code='tambah-ceria'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'tambah-03','6 + 3 = ...','{"A":"8","B":"9","C":"10","D":"7"}','B','Mulai dari 6 lalu maju tiga langkah.','Setelah 6: 7, 8, 9.','6 + 3 = 9.','Pasangan angka yang jumlahnya 10 berguna dihafal; 6 membutuhkan 4, jadi tambah 3 menghasilkan 9.',3,1 FROM math_expedition_levels WHERE code='tambah-ceria'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'tambah-04','Bilangan yang harus ditambahkan pada 4 agar menjadi 10 adalah ...','{"A":"5","B":"6","C":"7","D":"4"}','B','Tanyakan: 4 ditambah berapa hasilnya 10?','Dari 4 ke 10 ada enam langkah.','4 + 6 = 10.','Mencari angka yang hilang dapat dibantu dengan menghitung jarak dari angka awal ke hasil.',4,1 FROM math_expedition_levels WHERE code='tambah-ceria'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'tambah-05','2 + 5 + 1 = ...','{"A":"7","B":"8","C":"6","D":"9"}','B','Jumlahkan dua angka pertama terlebih dahulu.','2 + 5 = 7, lalu tambah 1.','2 + 5 + 1 = 7 + 1 = 8.','Untuk tiga bilangan, kerjakan bertahap agar tidak tertukar.',5,1 FROM math_expedition_levels WHERE code='tambah-ceria'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'kurang-01','9 − 4 = ...','{"A":"4","B":"5","C":"6","D":"3"}','B','Mulai dari 9 dan mundur empat langkah.','9, lalu 8, 7, 6, 5.','9 dikurangi 4 sama dengan 5.','Pengurangan bisa dibayangkan sebagai langkah mundur pada garis bilangan.',1,1 FROM math_expedition_levels WHERE code='kurang-cermat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'kurang-02','Ada 12 jeruk. Sebanyak 5 jeruk dimakan. Sisa jeruk adalah ...','{"A":"6","B":"7","C":"8","D":"17"}','B','Kata “sisa” menunjukkan ada yang diambil.','Kurangi 5 dari 12: 12, 11, 10, 9, 8, 7.','12 − 5 = 7, jadi tersisa 7 jeruk.','Pada soal cerita, kata sisa, diambil, dan dimakan biasanya menunjukkan pengurangan.',2,1 FROM math_expedition_levels WHERE code='kurang-cermat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'kurang-03','15 − 6 = ...','{"A":"8","B":"9","C":"10","D":"11"}','B','Pecah 6 menjadi 5 dan 1.','15 − 5 = 10, kemudian 10 − 1 = 9.','15 − 6 = 9.','Mengurangi sampai puluhan terdekat sering membuat hitungan lebih mudah.',3,1 FROM math_expedition_levels WHERE code='kurang-cermat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'kurang-04','Bilangan yang dikurangi dari 14 sehingga hasilnya 8 adalah ...','{"A":"5","B":"6","C":"7","D":"8"}','B','Ubah pertanyaan menjadi 8 + ... = 14.','Dari 8 menuju 14 bertambah 6.','14 − 6 = 8.','Pengurangan dengan angka hilang dapat dicek kembali memakai penjumlahan.',4,1 FROM math_expedition_levels WHERE code='kurang-cermat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'kurang-05','20 − 9 = ...','{"A":"10","B":"11","C":"12","D":"9"}','B','Kurangi 10 terlebih dahulu, lalu kembalikan 1.','20 − 10 = 10. Karena yang dikurangi hanya 9, hasilnya 10 + 1.','20 − 9 = 11.','Untuk mengurangi 9, kurangi 10 lalu tambah 1 kembali.',5,1 FROM math_expedition_levels WHERE code='kurang-cermat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'puluhan-01','Nilai angka 5 pada bilangan 58 adalah ...','{"A":"5 satuan","B":"5 puluhan","C":"8 puluhan","D":"8 ratusan"}','B','Lihat letak angka 5: ia berada di sebelah kiri angka satuan.','Pada bilangan dua digit, angka kiri menunjukkan puluhan.','Pada 58, angka 5 berarti 5 puluhan atau 50.','Bacalah bilangan dua digit sebagai puluhan + satuan.',1,1 FROM math_expedition_levels WHERE code='puluhan-hebat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'puluhan-02','36 + 20 = ...','{"A":"46","B":"56","C":"66","D":"54"}','B','Tambahkan puluhan dengan puluhan.','36 adalah 3 puluhan 6 satuan. Tambah 2 puluhan menjadi 5 puluhan 6 satuan.','36 + 20 = 56.','Saat menambah kelipatan 10, angka satuannya tetap.',2,1 FROM math_expedition_levels WHERE code='puluhan-hebat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'puluhan-03','47 + 12 = ...','{"A":"59","B":"69","C":"58","D":"49"}','A','Pisahkan puluhan dan satuan.','40 + 10 = 50, sedangkan 7 + 2 = 9.','50 + 9 = 59.','Memecah bilangan menjadi puluhan dan satuan membuat penjumlahan lebih rapi.',3,1 FROM math_expedition_levels WHERE code='puluhan-hebat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'puluhan-04','Bilangan manakah yang terdiri dari 7 puluhan dan 3 satuan?','{"A":"37","B":"73","C":"70","D":"703"}','B','Tujuh puluhan berarti 70.','Tambahkan 3 satuan ke 70.','7 puluhan + 3 satuan = 70 + 3 = 73.','Tuliskan puluhan di tempat kiri dan satuan di tempat kanan.',4,1 FROM math_expedition_levels WHERE code='puluhan-hebat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'puluhan-05','82 − 30 = ...','{"A":"42","B":"52","C":"62","D":"79"}','B','Kurangi angka puluhannya saja.','82 adalah 8 puluhan 2 satuan. Jika dikurangi 3 puluhan, tersisa 5 puluhan 2 satuan.','82 − 30 = 52.','Pada pengurangan puluhan bulat, angka satuan tidak berubah.',5,1 FROM math_expedition_levels WHERE code='puluhan-hebat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'kali-01','Di kebun ada 3 bedeng. Setiap bedeng berisi 4 bunga. Jumlah bunganya adalah ...','{"A":"7","B":"12","C":"14","D":"10"}','B','Bayangkan 3 kelompok, masing-masing berisi 4.','4 + 4 + 4 = 12.','Tiga bedeng berisi empat bunga berarti 3 × 4 = 12 bunga.','Perkalian adalah penjumlahan berulang.',1,1 FROM math_expedition_levels WHERE code='kali-berani'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'kali-02','Ada 4 kantong. Setiap kantong berisi 5 kelereng. Jumlah kelereng seluruhnya adalah ...','{"A":"9","B":"15","C":"20","D":"25"}','C','Ada empat kelompok dengan isi sama.','Hitung 5 + 5 + 5 + 5.','4 × 5 = 20, jadi ada 20 kelereng.','Jika banyaknya tiap kelompok sama, gunakan perkalian.',2,1 FROM math_expedition_levels WHERE code='kali-berani'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'kali-03','Penjaga kebun menaruh 2 lebah pada masing-masing 6 bunga. Ada ... lebah semuanya.','{"A":"8","B":"10","C":"12","D":"14"}','C','Ada enam kelompok bunga, masing-masing berisi dua lebah.','2 + 2 + 2 + 2 + 2 + 2 = 12.','6 × 2 = 12, jadi ada 12 lebah.','Perkalian boleh ditukar: 6 × 2 sama dengan 2 × 6.',3,1 FROM math_expedition_levels WHERE code='kali-berani'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'kali-04','Lima pot masing-masing berisi 3 benih. Bentuk penjumlahan berulang yang tepat adalah ...','{"A":"5 + 3","B":"3 + 3 + 3 + 3 + 3","C":"5 + 5 + 5","D":"3 + 5 + 3"}','B','Angka pertama menunjukkan banyak kelompok.','Ada 5 pot, masing-masing berisi 3 benih.','5 × 3 = 3 + 3 + 3 + 3 + 3.','Baca a × b sebagai “a kelompok, tiap kelompok b”.',4,1 FROM math_expedition_levels WHERE code='kali-berani'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'kali-05','Satu keranjang panen berisi 7 apel. Jumlah apel pada 1 keranjang adalah ...','{"A":"1","B":"6","C":"7","D":"8"}','C','Hanya ada satu kelompok.','Satu kelompok berisi 7 menghasilkan 7.','1 × 7 = 7; satu keranjang tetap berisi 7 apel.','Ingat aturan cepat: bilangan × 1 = bilangan itu sendiri.',5,1 FROM math_expedition_levels WHERE code='kali-berani'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'bagi-01','Kapal membawa 12 bekal untuk 3 kru. Jika dibagi sama rata, setiap kru mendapat ... bekal.','{"A":"3","B":"4","C":"5","D":"6"}','B','Tanyakan: 3 dikali berapa hasilnya 12?','3 × 4 = 12.','12 ÷ 3 = 4, jadi setiap kru mendapat 4 bekal.','Pembagian dapat dicek dengan perkalian: hasil bagi × pembagi = bilangan awal.',1,1 FROM math_expedition_levels WHERE code='bagi-adil'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'bagi-02','18 permen dibagikan sama rata kepada 6 anak. Setiap anak mendapat ... permen.','{"A":"2","B":"3","C":"4","D":"6"}','B','Pembagian sama rata berarti jumlah total dibagi banyak anak.','Cari bilangan yang jika dikali 6 hasilnya 18.','18 ÷ 6 = 3, setiap anak mendapat 3 permen.','Gambarkan kelompok yang sama banyak jika soal pembagian terasa sulit.',2,1 FROM math_expedition_levels WHERE code='bagi-adil'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'bagi-03','Ada 20 bibit yang akan ditanam pada 5 pot secara sama rata. Setiap pot berisi ... bibit.','{"A":"3","B":"4","C":"5","D":"6"}','B','Gunakan fakta perkalian lima.','5 × 4 = 20.','20 ÷ 5 = 4, jadi setiap pot berisi 4 bibit.','Hafalan perkalian membantu pembagian menjadi lebih cepat.',3,1 FROM math_expedition_levels WHERE code='bagi-adil'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'bagi-04','Sebanyak 24 pelampung dibagi ke 6 perahu dengan jumlah sama. Setiap perahu mendapat ... pelampung.','{"A":"3","B":"4","C":"5","D":"6"}','B','Cari pasangan perkalian untuk 6 dan 24.','6 × 4 = 24.','24 ÷ 6 = 4, jadi setiap perahu mendapat 4 pelampung.','Jika ragu, kalikan kembali jawaban dengan pembagi.',4,1 FROM math_expedition_levels WHERE code='bagi-adil'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'bagi-05','Manakah kalimat pembagian yang benar untuk 4 kelompok, masing-masing berisi 3 bola?','{"A":"4 ÷ 3 = 12","B":"12 ÷ 4 = 3","C":"3 ÷ 4 = 12","D":"12 ÷ 3 = 3"}','B','Total bolanya 4 × 3.','Total 12 bola dibagi ke 4 kelompok menghasilkan 3 bola tiap kelompok.','12 ÷ 4 = 3.','Tentukan dulu jumlah seluruh benda, lalu bagi dengan banyak kelompok.',5,1 FROM math_expedition_levels WHERE code='bagi-adil'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'pecahan-01','Satu pizza dibagi menjadi 2 bagian sama besar. Jika diambil 1 bagian, pecahan yang diambil adalah ...','{"A":"1/2","B":"1/3","C":"2/1","D":"2/2"}','A','Penyebut menunjukkan jumlah bagian sama besar seluruhnya.','Ada 2 bagian total dan 1 bagian diambil.','Pecahannya 1/2: satu dari dua bagian sama besar.','Pada pecahan, pembilang = bagian yang dipilih; penyebut = jumlah semua bagian sama besar.',1,1 FROM math_expedition_levels WHERE code='pecahan-dekat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'pecahan-02','Di Kafe Pecahan, 3 dari 4 potong kue sama besar sudah disiapkan. Pecahan yang tepat adalah ...','{"A":"4/3","B":"3/4","C":"1/4","D":"3/3"}','B','Angka atas menyatakan bagian yang dipilih.','Pilih 3 sebagai pembilang dan 4 sebagai penyebut.','Tiga dari empat bagian ditulis 3/4.','Baca pecahan dari atas ke bawah: pembilang per penyebut.',2,1 FROM math_expedition_levels WHERE code='pecahan-dekat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'pecahan-03','Dua pizza sama besar: pizza pertama dibagi 4, pizza kedua dibagi 2. Manakah bagian yang lebih besar?','{"A":"1/4","B":"1/2","C":"Keduanya sama","D":"Tidak dapat dibandingkan"}','B','Bayangkan satu pizza dibagi 2 dan satu pizza dibagi 4.','Satu dari dua bagian lebih besar daripada satu dari empat bagian.','1/2 lebih besar daripada 1/4.','Jika pembilang sama, penyebut lebih kecil berarti bagian lebih besar.',3,1 FROM math_expedition_levels WHERE code='pecahan-dekat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'pecahan-04','Setengah loyang kue yang dipotong menjadi 4 bagian sama besar berarti 2/4. Pecahan yang senilai adalah ...','{"A":"1/2","B":"1/3","C":"2/3","D":"3/4"}','A','Bayangkan loyang dibagi menjadi 4 bagian sama besar.','Dua dari empat bagian sama dengan setengah loyang.','2/4 = 1/2.','Pecahan senilai diperoleh dengan membagi atau mengalikan atas dan bawah dengan bilangan yang sama.',4,1 FROM math_expedition_levels WHERE code='pecahan-dekat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'pecahan-05','Rina makan 1/4 bagian kue dan Bima makan 2/4 bagian kue. Siapa yang makan lebih banyak?','{"A":"Rina","B":"Bima","C":"Sama banyak","D":"Tidak dapat diketahui"}','B','Kedua pecahan memiliki penyebut yang sama.','Bandingkan angka atasnya: 2 lebih besar dari 1.','2/4 lebih besar daripada 1/4, jadi Bima makan lebih banyak.','Jika penyebut sama, pecahan dengan pembilang lebih besar nilainya lebih besar.',5,1 FROM math_expedition_levels WHERE code='pecahan-dekat'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'strategi-01','Lampu taman menyala berurutan 2, 5, 8, 11, ... Menurut pola, lampu berikutnya bernomor ...','{"A":"12","B":"13","C":"14","D":"15"}','C','Perhatikan selisih antarangka.','Setiap angka bertambah 3.','11 + 3 = 14.','Untuk pola bilangan, cek apakah selisihnya tetap.',1,1 FROM math_expedition_levels WHERE code='strategi-matematika'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'strategi-02','Tiga kios menerima (4 + 2) kotak minuman masing-masing. Total kotak minuman adalah ...','{"A":"14","B":"18","C":"20","D":"24"}','B','Kerjakan yang berada dalam tanda kurung lebih dulu.','4 + 2 = 6 kotak per kios, kemudian ada 3 kios.','3 × (4 + 2) = 3 × 6 = 18.','Dalam operasi campuran, tanda kurung dikerjakan terlebih dahulu.',2,1 FROM math_expedition_levels WHERE code='strategi-matematika'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'strategi-03','Panjang pita untuk hiasan pasar adalah 2 meter. Panjang itu sama dengan ... sentimeter.','{"A":"20","B":"100","C":"200","D":"2000"}','C','Ingat hubungan meter dan sentimeter.','Satu meter sama dengan 100 sentimeter.','2 × 100 = 200 sentimeter.','Tulis satuan sebelum menghitung agar konversi tidak tertukar.',3,1 FROM math_expedition_levels WHERE code='strategi-matematika'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'strategi-04','Harga 3 buku masing-masing Rp4.000. Total harga buku adalah ...','{"A":"Rp7.000","B":"Rp12.000","C":"Rp16.000","D":"Rp40.000"}','B','Ada tiga harga yang sama.','3 × 4.000 = 12.000.','Total harga adalah Rp12.000.','Untuk beberapa barang dengan harga sama, kalikan jumlah barang dengan harga satuannya.',4,1 FROM math_expedition_levels WHERE code='strategi-matematika'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'strategi-05','Taman bermain berbentuk persegi dengan sisi 6 cm pada peta. Keliling taman itu adalah ...','{"A":"12 cm","B":"18 cm","C":"24 cm","D":"36 cm"}','C','Persegi memiliki empat sisi yang sama panjang.','Jumlahkan 6 cm sebanyak empat kali.','Keliling = 4 × 6 cm = 24 cm.','Keliling berarti jumlah seluruh sisi bangun.',5,1 FROM math_expedition_levels WHERE code='strategi-matematika'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

-- Bab 3 · Hutan Penjelajah: tiap pos memakai konteks cerita, bukan operasi lepas.
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'waktu-01','Kereta Hutan berangkat pukul 07.00 dan tiba pukul 08.00. Lama perjalanannya adalah ...','{"A":"30 menit","B":"1 jam","C":"2 jam","D":"8 jam"}','B','Bandingkan angka jam saat berangkat dan tiba.','Dari angka 7 menuju angka 8 pada jam adalah satu putaran kecil.','Dari 07.00 sampai 08.00 selisihnya 1 jam.','Untuk jam tepat, hitung selisih angka jamnya.',1,1 FROM math_expedition_levels WHERE code='jam-penjelajah'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'waktu-02','Jam di pos hutan menunjukkan jarum pendek di 3 dan jarum panjang di 12. Waktunya adalah ...','{"A":"03.00","B":"03.30","C":"12.15","D":"15.00"}','A','Jarum panjang di 12 menunjukkan menitnya nol.','Perhatikan angka yang ditunjuk jarum pendek.','Jarum pendek di 3 dan jarum panjang di 12 menunjukkan pukul 03.00.','Baca jarum panjang dahulu untuk menit, lalu jarum pendek untuk jam.',2,1 FROM math_expedition_levels WHERE code='jam-penjelajah'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'waktu-03','Penjaga hutan mulai bertugas pukul 09.15 dan selesai pukul 09.45. Lama bertugas adalah ...','{"A":"15 menit","B":"30 menit","C":"45 menit","D":"1 jam"}','B','Kedua waktu masih pada jam 09.','Kurangi menit akhir dengan menit awal: 45 − 15.','Dari 09.15 ke 09.45 ada 30 menit.','Jika jamnya sama, cukup bandingkan menitnya.',3,1 FROM math_expedition_levels WHERE code='jam-penjelajah'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'waktu-04','Pertunjukan kunang-kunang dimulai pukul 18.30. Jika berlangsung 1 jam, selesai pukul ...','{"A":"18.45","B":"19.00","C":"19.30","D":"20.30"}','C','Menambah satu jam tidak mengubah menitnya.','Dari jam 18 maju satu jam menjadi 19.','18.30 ditambah 1 jam adalah 19.30.','Saat menambah jam bulat, bagian menit tetap.',4,1 FROM math_expedition_levels WHERE code='jam-penjelajah'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'waktu-05','Kereta berikutnya datang 20 menit setelah pukul 10.40. Kereta itu datang pukul ...','{"A":"10.50","B":"11.00","C":"11.20","D":"12.00"}','B','Hitung maju dari menit 40 hingga menit 60.','Dari 10.40 ke 11.00 tepat 20 menit.','10.40 + 20 menit = 11.00.','Saat menit mencapai 60, jam bertambah satu.',5,1 FROM math_expedition_levels WHERE code='jam-penjelajah'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'bentuk-01','Papan petunjuk berbentuk segitiga. Segitiga memiliki ... sisi.','{"A":"2","B":"3","C":"4","D":"5"}','B','Hitung garis lurus yang membentuk tepi segitiga.','Segitiga memiliki tiga sudut dan tiga sisi.','Segitiga selalu memiliki 3 sisi.','Nama bangun sering membantu: segi tiga berarti tiga sisi.',1,1 FROM math_expedition_levels WHERE code='bengkel-bentuk'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'bentuk-02','Jendela pos penjaga berbentuk persegi panjang. Bangun ini memiliki ... sisi.','{"A":"3","B":"4","C":"5","D":"6"}','B','Telusuri tepi jendela satu per satu.','Ada sisi atas, kanan, bawah, dan kiri.','Persegi panjang memiliki 4 sisi.','Persegi dan persegi panjang sama-sama memiliki empat sisi.',2,1 FROM math_expedition_levels WHERE code='bengkel-bentuk'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'bentuk-03','Jembatan berbentuk persegi memiliki panjang setiap sisi 5 meter. Keliling jembatan adalah ...','{"A":"10 meter","B":"15 meter","C":"20 meter","D":"25 meter"}','C','Persegi mempunyai empat sisi sama panjang.','Jumlahkan 5 meter sebanyak empat kali.','Keliling = 4 × 5 meter = 20 meter.','Keliling adalah jumlah seluruh sisi di bagian luar bangun.',3,1 FROM math_expedition_levels WHERE code='bengkel-bentuk'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'bentuk-04','Papan lantai berbentuk persegi panjang, panjangnya 8 cm dan lebarnya 3 cm. Kelilingnya adalah ...','{"A":"11 cm","B":"16 cm","C":"22 cm","D":"24 cm"}','C','Persegi panjang memiliki dua pasang sisi yang sama.','Jumlahkan 8 + 3 + 8 + 3.','8 + 3 + 8 + 3 = 22 cm.','Gunakan rumus keliling persegi panjang: 2 × (panjang + lebar).',4,1 FROM math_expedition_levels WHERE code='bengkel-bentuk'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'bentuk-05','Roda gerobak berbentuk lingkaran. Lingkaran tidak memiliki ...','{"A":"sisi lurus","B":"warna","C":"titik tengah","D":"bentuk"}','A','Perhatikan tepi roda yang melengkung.','Tidak ada garis lurus di tepi lingkaran.','Lingkaran tidak memiliki sisi lurus; tepinya melengkung.','Amati bentuk benda sekitar untuk mengingat ciri bangun datar.',5,1 FROM math_expedition_levels WHERE code='bengkel-bentuk'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'data-01','Papan panen menunjukkan: apel 8 keranjang, jeruk 5 keranjang, mangga 6 keranjang. Buah yang paling banyak dipanen adalah ...','{"A":"apel","B":"jeruk","C":"mangga","D":"sama banyak"}','A','Bandingkan angka 8, 5, dan 6.','Angka terbesar menunjukkan jumlah terbanyak.','8 adalah angka terbesar, jadi apel paling banyak dipanen.','Saat membaca data, cari dulu angka terbesar atau terkecil sesuai pertanyaan.',1,1 FROM math_expedition_levels WHERE code='kebun-data'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'data-02','Kebun A menghasilkan 7 pot bunga dan Kebun B menghasilkan 10 pot bunga. Selisih hasil keduanya adalah ... pot.','{"A":"2","B":"3","C":"7","D":"17"}','B','Cari selisih dengan mengurangkan jumlah lebih besar dan lebih kecil.','10 − 7 = 3.','Selisih 10 pot dan 7 pot adalah 3 pot.','Selisih berarti jarak antara dua jumlah; gunakan pengurangan.',2,1 FROM math_expedition_levels WHERE code='kebun-data'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'data-03','Catatan penjaga menunjukkan pengunjung hari Senin 12 orang, Selasa 15 orang, dan Rabu 9 orang. Jumlah pengunjung selama tiga hari adalah ...','{"A":"24","B":"27","C":"36","D":"45"}','C','Jumlahkan data tiap hari.','12 + 15 = 27, lalu tambah 9.','12 + 15 + 9 = 36 orang.','Saat menjumlahkan banyak data, kerjakan dua angka dulu lalu lanjutkan.',3,1 FROM math_expedition_levels WHERE code='kebun-data'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'data-04','Dari 20 bibit, 8 adalah bunga matahari. Banyak bibit yang bukan bunga matahari adalah ...','{"A":"8","B":"10","C":"12","D":"28"}','C','Jumlah seluruh bibit dikurangi bibit bunga matahari.','20 − 8 = 12.','Bibit selain bunga matahari ada 12.','Jika ditanya “bukan”, kurangkan bagian yang disebut dari jumlah seluruhnya.',4,1 FROM math_expedition_levels WHERE code='kebun-data'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'data-05','Papan panen menunjukkan tomat 14 keranjang dan cabai 14 keranjang. Pernyataan yang tepat adalah ...','{"A":"tomat lebih banyak","B":"cabai lebih banyak","C":"jumlahnya sama","D":"tidak dapat diketahui"}','C','Bandingkan kedua angka pada papan panen.','Keduanya tertulis 14.','Tomat dan cabai sama-sama berjumlah 14 keranjang.','Jika dua data memiliki angka sama, berarti jumlahnya sama.',5,1 FROM math_expedition_levels WHERE code='kebun-data'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'logika-01','Untuk membuka gerbang menara diperlukan 3 kunci merah dan 2 kunci biru. Jika sudah ada 3 kunci merah, kunci yang masih diperlukan adalah ...','{"A":"1 kunci merah","B":"2 kunci biru","C":"3 kunci biru","D":"5 kunci biru"}','B','Bandingkan kunci yang sudah ada dengan daftar kebutuhan.','Kunci merah sudah lengkap, sehingga tinggal memenuhi kunci biru.','Masih diperlukan 2 kunci biru.','Pada masalah bertahap, tandai dahulu bagian yang sudah terpenuhi.',1,1 FROM math_expedition_levels WHERE code='menara-logika'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'logika-02','Rute ke menara terdiri dari 4 langkah ke timur lalu 3 langkah ke utara. Jumlah langkah seluruhnya adalah ...','{"A":"1","B":"7","C":"12","D":"43"}','B','Jumlahkan semua langkah, walaupun arahnya berbeda.','4 + 3 = 7.','Total langkah yang ditempuh adalah 7 langkah.','Jika ditanya total, gabungkan semua bagian yang disebut.',2,1 FROM math_expedition_levels WHERE code='menara-logika'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'logika-03','Penjaga membawa 15 obor. Ia memasang 6 obor di jalan pertama dan 4 obor di jalan kedua. Obor yang tersisa adalah ...','{"A":"5","B":"9","C":"11","D":"25"}','A','Cari jumlah obor yang sudah dipasang lebih dahulu.','6 + 4 = 10 obor sudah dipasang, lalu kurangi dari 15.','15 − 10 = 5 obor tersisa.','Untuk masalah dua langkah, selesaikan bagian pertama sebelum mencari sisa.',3,1 FROM math_expedition_levels WHERE code='menara-logika'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'logika-04','Kamu punya Rp10.000. Membeli peta hutan seharga Rp4.000 dan bekal seharga Rp3.000. Uang yang tersisa adalah ...','{"A":"Rp3.000","B":"Rp4.000","C":"Rp7.000","D":"Rp13.000"}','A','Jumlahkan harga barang yang dibeli.','Rp4.000 + Rp3.000 = Rp7.000, lalu kurangi dari Rp10.000.','Rp10.000 − Rp7.000 = Rp3.000.','Saat ada dua pembelian, jumlahkan pengeluaran dahulu sebelum menghitung sisa.',4,1 FROM math_expedition_levels WHERE code='menara-logika'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;
INSERT INTO math_expedition_questions (level_id,question_code,prompt,options_json,correct_option,hint,hint_deeper,explanation,tip,sort_order,is_active)
SELECT id,'logika-05','Kode menara mengikuti pola 4, 8, 12, 16, ... Angka berikutnya untuk kode adalah ...','{"A":"18","B":"19","C":"20","D":"24"}','C','Perhatikan selisih setiap angka dalam kode.','Setiap angka bertambah 4.','16 + 4 = 20.','Untuk pola berulang, temukan aturan perubahan dari satu angka ke angka berikutnya.',5,1 FROM math_expedition_levels WHERE code='menara-logika'
ON DUPLICATE KEY UPDATE prompt=VALUES(prompt),options_json=VALUES(options_json),correct_option=VALUES(correct_option),hint=VALUES(hint),hint_deeper=VALUES(hint_deeper),explanation=VALUES(explanation),tip=VALUES(tip),sort_order=VALUES(sort_order),is_active=1;

INSERT INTO learn_point_rules (action_code,label,description,points,cooldown_hours,is_active)
VALUES ('math.expedition.complete','Level Math Expedition selesai','Poin untuk pertama kali menuntaskan setiap level Math Expedition.',8,0,1)
ON DUPLICATE KEY UPDATE label=VALUES(label),description=VALUES(description),points=VALUES(points),cooldown_hours=VALUES(cooldown_hours),is_active=VALUES(is_active);
