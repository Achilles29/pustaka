-- Normalisasi Story Quiz: 5 bacaan per kelas (SD1–SMA12), 5 soal per bacaan.
-- Tidak menghapus passage/attempt lama; idempotent berdasarkan code unik.
DELIMITER $$
DROP PROCEDURE IF EXISTS seed_story_5x5$$
CREATE PROCEDURE seed_story_5x5()
BEGIN
  DECLARE gid INT DEFAULT 3; DECLARE n INT; DECLARE slot INT; DECLARE pid INT;
  DECLARE gname VARCHAR(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE codev VARCHAR(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE titlev VARCHAR(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE charv VARCHAR(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE placev VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE actionv VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE lessonv VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  DECLARE bodyv TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE summaryv VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE sid INT; DECLARE qn INT;
  WHILE gid <= 14 DO
    SELECT name INTO gname FROM quiz_grade_levels WHERE id=gid;
    SELECT COUNT(*) INTO n FROM learn_story_passages WHERE grade_level_id=gid AND is_active=1;
    SET slot=1;
    WHILE n < 5 DO
      SET codev=CONCAT('literasi_',gid,'_',slot);
      IF NOT EXISTS(SELECT 1 FROM learn_story_passages WHERE code=codev) THEN
        SET charv=ELT(slot,'Raka','Sari','Damar','Nala','Aruna');
        SET placev=ELT(slot,'sungai di dekat desa','festival ilmu sekolah','perpustakaan daerah','kebun pangan warga','lokakarya teknologi');
        SET actionv=ELT(slot,'mengajak teman-temannya mengukur kebersihan air dan mencatat sumber sampah','menguji sebuah gagasan, membandingkan hasil, lalu memperbaiki percobaannya','menelusuri beberapa sumber sebelum menyimpulkan informasi yang ditemukan','bekerja bersama warga merawat tanaman serta menghemat air','merancang solusi sederhana dan menguji apakah solusi itu aman bagi pengguna');
        SET lessonv=ELT(slot,'kepedulian harus disertai pengamatan dan tindakan nyata','kesalahan adalah bagian penting dari proses belajar','informasi yang baik perlu diperiksa melalui lebih dari satu sumber','kerja sama membuat lingkungan lebih tangguh','teknologi seharusnya menyelesaikan masalah tanpa mengabaikan keselamatan');
        SET titlev=CONCAT(ELT(slot,'Penjaga Sungai','Festival Penemu Muda','Misteri di Rak Terakhir','Kebun untuk Masa Depan','Kode yang Membantu'), ' — ',gname);
        SET summaryv=CONCAT('Bacaan ',gname,' tentang ',LOWER(ELT(slot,'lingkungan dan pengamatan','eksperimen dan kegigihan','literasi informasi','pangan dan kerja sama','teknologi yang bertanggung jawab')),'.');
        SET bodyv=IF(gid<=5,
          CONCAT(charv,' datang ke ',placev,'. Ia ',actionv,'. Teman-temannya ikut membantu. Dari kegiatan itu, mereka memahami bahwa ',lessonv,'.'),
          IF(gid<=8,
            CONCAT('Ketika ',charv,' mengikuti kegiatan di ',placev,', ia menemukan masalah yang tidak dapat diselesaikan dengan tebakan. Ia ',actionv,'. Hasil pertama belum sempurna, tetapi kelompoknya mengevaluasi bukti dan mencoba kembali. Mereka akhirnya memahami bahwa ',lessonv,'.'),
            CONCAT('Kegiatan di ',placev,' membuat ',charv,' menghadapi dua pilihan: menerima jawaban tercepat atau memeriksa bukti secara sistematis. Ia memilih untuk ',actionv,'. Temuan kelompok menunjukkan bahwa keputusan yang tampak sederhana dapat berdampak pada banyak orang. Refleksi mereka menegaskan bahwa ',lessonv,'.')));
        SET sid=ELT(1+MOD(gid+slot,6),2,39,10,4,17,6);
        INSERT INTO learn_story_passages(code,title,body,summary,subject_id,grade_level_id,icon,color,estimated_minutes,sort_order,is_active)
        VALUES(codev,titlev,bodyv,summaryv,sid,gid,ELT(slot,'ti-ripple','ti-bulb','ti-books','ti-plant','ti-code'),'#0e7490',IF(gid<9,3,5),200+gid*10+slot,1);
        SET pid=LAST_INSERT_ID();
        INSERT INTO learn_story_questions(passage_id,question,option_a,option_b,option_c,option_d,correct_option,explanation,sort_order,is_active) VALUES
        (pid,'Siapa tokoh utama bacaan?',charv,'Bima','Maya','Tono',0,CONCAT('Tokoh yang disebut sejak awal adalah ',charv,'.'),1,1),
        (pid,'Di mana kegiatan utama berlangsung?',placev,'terminal kota','lapangan olahraga','rumah sakit',0,CONCAT('Kegiatan berlangsung di ',placev,'.'),2,1),
        (pid,'Apa tindakan utama tokoh?',actionv,'menghindari masalah dan pulang','menunggu orang lain bekerja','menyembunyikan hasil kegiatan',0,'Jawaban dinyatakan langsung dalam bacaan.',3,1),
        (pid,'Pelajaran utama yang diperoleh adalah ...',lessonv,'kecepatan selalu lebih penting daripada ketelitian','masalah selesai tanpa kerja sama','bukti tidak diperlukan saat mengambil keputusan',0,'Pelajaran tersebut menjadi penutup dan gagasan utama bacaan.',4,1),
        (pid,'Sikap yang paling tampak pada tokoh adalah ...','ingin tahu dan bertanggung jawab','ceroboh dan tidak peduli','mudah menyerah','menolak bekerja sama',0,'Tokoh mengamati masalah, bertindak, dan mengevaluasi hasilnya.',5,1);
        SET n=n+1;
      END IF;
      SET slot=slot+1;
    END WHILE;
    SET gid=gid+1;
  END WHILE;

  -- Lengkapi passage lama yang sudah memiliki 2–3 soal hingga tepat 5.
  BEGIN
    DECLARE done INT DEFAULT 0; DECLARE oldpid INT; DECLARE oldtitle VARCHAR(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE oldsummary VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE cur CURSOR FOR SELECT id,title,COALESCE(summary,title) FROM learn_story_passages WHERE is_active=1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done=1;
    OPEN cur;
    read_loop: LOOP
      FETCH cur INTO oldpid,oldtitle,oldsummary; IF done=1 THEN LEAVE read_loop; END IF;
      SELECT COUNT(*) INTO qn FROM learn_story_questions WHERE passage_id=oldpid AND is_active=1;
      WHILE qn<5 DO
        INSERT INTO learn_story_questions(passage_id,question,option_a,option_b,option_c,option_d,correct_option,explanation,sort_order,is_active)
        VALUES(oldpid,
          IF(MOD(qn,2)=0,'Ringkasan mana yang paling sesuai dengan bacaan?','Judul mana yang sesuai dengan bacaan tersebut?'),
          IF(MOD(qn,2)=0,oldsummary,oldtitle),'Informasi yang bertentangan dengan teks','Peristiwa yang tidak disebutkan','Kesimpulan tanpa bukti',0,
          IF(MOD(qn,2)=0,'Pilihan ini merangkum isi bacaan dengan paling tepat.','Judul tersebut sesuai dengan isi bacaan.'),qn+1,1);
        SET qn=qn+1;
      END WHILE;
    END LOOP; CLOSE cur;
  END;
END$$
CALL seed_story_5x5()$$
DROP PROCEDURE seed_story_5x5$$
DELIMITER ;
