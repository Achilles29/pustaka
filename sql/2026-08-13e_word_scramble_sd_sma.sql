-- Word Scramble per kelas SD1–SMA12: 12 set, masing-masing 10 kata.
DELIMITER $$
DROP PROCEDURE IF EXISTS seed_scramble_levels$$
CREATE PROCEDURE seed_scramble_levels()
BEGIN
 DECLARE g INT DEFAULT 3; DECLARE k INT; DECLARE gt INT; DECLARE cid INT; DECLARE sidset INT; DECLARE subj INT;
 DECLARE gn VARCHAR(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE cname VARCHAR(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE terms TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE clues TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE termv VARCHAR(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; DECLARE cluev VARCHAR(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
 SELECT id INTO gt FROM learn_game_types WHERE code='word_scramble' LIMIT 1;
 WHILE g<=14 DO
  SELECT name INTO gn FROM quiz_grade_levels WHERE id=g;
  SET subj=ELT(g-2,2,3,39,1,4,10,4,1,3,15,14,16);
  SET cname=CONCAT('Susun Kata — ',gn);
  INSERT INTO learn_game_categories(game_type_id,grade_level_id,subject_id,name,description,is_active)
  SELECT gt,g,subj,cname,CONCAT('Kosakata terkurasi untuk ',gn,'.'),1 FROM DUAL WHERE NOT EXISTS(SELECT 1 FROM learn_game_categories WHERE game_type_id=gt AND grade_level_id=g AND name=cname);
  SELECT id INTO cid FROM learn_game_categories WHERE game_type_id=gt AND grade_level_id=g AND name=cname LIMIT 1;
  INSERT INTO learn_game_content_sets(category_id,name,difficulty,is_active)
  SELECT cid,CONCAT('Tantangan ',gn),IF(g<=6,'easy',IF(g<=10,'medium','hard')),1 FROM DUAL WHERE NOT EXISTS(SELECT 1 FROM learn_game_content_sets WHERE category_id=cid AND name=CONCAT('Tantangan ',gn));
  SELECT id INTO sidset FROM learn_game_content_sets WHERE category_id=cid AND name=CONCAT('Tantangan ',gn) LIMIT 1;
  SET terms=CASE g
   WHEN 3 THEN 'BUKU|PENSIL|SEKOLAH|TEMAN|BELAJAR|MEMBACA|MENULIS|CERITA|GEMBIRA|BERBAGI'
   WHEN 4 THEN 'FAMILY|MOTHER|FATHER|SISTER|BROTHER|SCHOOL|TEACHER|STUDENT|YELLOW|PURPLE'
   WHEN 5 THEN 'TUMBUH|BERNAPAS|AKAR|BATANG|DAUN|BUNGA|HEWAN|HABITAT|MAKANAN|LINGKUNGAN'
   WHEN 6 THEN 'PECAHAN|PENYEBUT|PEMBILANG|KELILING|LUAS|SEJAJAR|SUDUT|DESIMAL|PERSEN|DIAMETER'
   WHEN 7 THEN 'ENERGI|GRAVITASI|EKOSISTEM|ADAPTASI|KONDUKTOR|ISOLATOR|EVAPORASI|KONDENSASI|ORGAN|JARINGAN'
   WHEN 8 THEN 'ALGORITMA|INTERNET|PERANGKAT|PROGRAM|DATA|PRIVASI|SANDI|PHISHING|DEKOMPOSISI|DEBUGGING'
   WHEN 9 THEN 'POPULASI|KOMUNITAS|BIOTIK|ABIOTIK|PRODUSEN|KONSUMEN|DEKOMPOSER|KLASIFIKASI|SIMBIOSIS|ORGANISME'
   WHEN 10 THEN 'VARIABEL|KOEFISIEN|KONSTANTA|PERSAMAAN|FUNGSI|DOMAIN|RANGE|GRADIEN|RELASI|PYTHAGORAS'
   WHEN 11 THEN 'CONGRATULATIONS|ENVIRONMENT|PRESERVE|NARRATIVE|PROCEDURE|PASSIVE|CONCLUSION|SUGGESTION|AGREEMENT|EXPRESSION'
   WHEN 12 THEN 'VEKTOR|SKALAR|KECEPATAN|PERCEPATAN|MOMENTUM|IMPULS|NEWTON|ENERGI|PENGUKURAN|RESULTAN'
   WHEN 13 THEN 'MEMBRAN|NUKLEUS|MITOKONDRIA|RIBOSOM|DIFUSI|OSMOSIS|EPITEL|HEMOGLOBIN|ALVEOLUS|NEFRON'
   ELSE 'OKSIDASI|REDUKSI|OKSIDATOR|REDUKTOR|ELEKTROLISIS|MOLALITAS|POLIMER|MONOMER|ALKANA|KATODE' END;
  SET clues=CASE g
   WHEN 3 THEN 'Dibaca untuk mendapat ilmu|Alat untuk menulis|Tempat belajar bersama|Orang yang bermain bersama|Kegiatan memperoleh pengetahuan|Kegiatan memahami tulisan|Kegiatan membuat kata|Kisah yang dibaca|Perasaan senang|Memberikan milik kepada orang lain'
   WHEN 4 THEN 'Keluarga dalam bahasa Inggris|Ibu dalam bahasa Inggris|Ayah dalam bahasa Inggris|Saudara perempuan|Saudara laki-laki|Sekolah dalam bahasa Inggris|Orang yang mengajar|Orang yang belajar|Warna kuning|Warna ungu'
   WHEN 5 THEN 'Bertambah ukuran|Mengambil oksigen|Menyerap air dari tanah|Menopang tumbuhan|Tempat fotosintesis|Alat perkembangbiakan tumbuhan|Makhluk hidup yang bergerak|Tempat hidup alami|Sumber energi makhluk hidup|Segala sesuatu di sekitar kita'
   WHEN 6 THEN 'Bagian dari keseluruhan|Angka bawah pecahan|Angka atas pecahan|Jumlah panjang sisi|Ukuran permukaan|Dua garis yang tidak berpotongan|Daerah pertemuan dua garis|Bilangan memakai tanda koma|Pecahan per seratus|Dua kali jari-jari'
   WHEN 7 THEN 'Kemampuan melakukan kerja|Gaya tarik bumi|Hubungan makhluk hidup dan lingkungan|Penyesuaian diri|Penghantar panas yang baik|Penghambat panas|Perubahan cair menjadi gas|Perubahan gas menjadi cair|Bagian tubuh dengan fungsi khusus|Kumpulan sel sejenis'
   WHEN 8 THEN 'Urutan langkah pemecahan masalah|Jaringan global|Alat fisik komputer|Kumpulan instruksi komputer|Fakta yang dapat diolah|Perlindungan informasi pribadi|Kunci untuk masuk akun|Penipuan melalui pesan palsu|Memecah masalah besar|Mencari kesalahan program'
   WHEN 9 THEN 'Organisme sejenis di satu tempat|Kumpulan berbagai populasi|Komponen hidup|Komponen tak hidup|Pembuat makanan sendiri|Pemakan organisme lain|Pengurai sisa makhluk hidup|Pengelompokan berdasarkan ciri|Hubungan erat antarorganisme|Individu makhluk hidup'
   WHEN 10 THEN 'Huruf pengganti nilai|Bilangan pengali variabel|Bilangan tanpa variabel|Kalimat matematika dengan tanda sama|Relasi satu input satu output|Himpunan masukan|Himpunan keluaran|Kemiringan garis|Hubungan dua himpunan|Teorema segitiga siku-siku'
   WHEN 11 THEN 'Ungkapan memberi selamat|Lingkungan dalam bahasa Inggris|Melindungi agar tetap baik|Teks cerita yang menghibur|Teks berisi langkah|Kalimat yang menekankan penerima aksi|Bagian akhir rangkuman|Ungkapan memberi saran|Ungkapan menyatakan setuju|Ungkapan dalam bahasa Inggris'
   WHEN 12 THEN 'Besaran bernilai dan berarah|Besaran tanpa arah|Perpindahan tiap waktu|Perubahan kecepatan tiap waktu|Massa dikali kecepatan|Gaya dikali selang waktu|Nama hukum tentang gerak|Kemampuan melakukan usaha|Proses membandingkan dengan satuan|Gabungan seluruh gaya'
   WHEN 13 THEN 'Pembatas selektif sel|Pusat kendali sel|Penghasil ATP|Tempat sintesis protein|Gerak partikel ke konsentrasi rendah|Perpindahan air melalui membran|Jaringan pelapis|Protein pengangkut oksigen|Tempat pertukaran gas|Unit fungsional ginjal'
   ELSE 'Pelepasan elektron|Penerimaan elektron|Zat penerima elektron|Zat pemberi elektron|Reaksi dipaksa listrik|Mol zat per kilogram pelarut|Molekul besar berulang|Unit penyusun polimer|Hidrokarbon jenuh|Elektrode tempat reduksi' END;
  SET k=1; WHILE k<=10 DO
   SET termv=SUBSTRING_INDEX(SUBSTRING_INDEX(terms,'|',k),'|',-1);SET cluev=SUBSTRING_INDEX(SUBSTRING_INDEX(clues,'|',k),'|',-1);
   INSERT INTO learn_game_content_items(set_id,term,definition,sort_order)
   SELECT sidset,termv,cluev,k FROM DUAL WHERE NOT EXISTS(SELECT 1 FROM learn_game_content_items WHERE set_id=sidset AND term=termv);
   SET k=k+1;
  END WHILE;
  SET g=g+1;
 END WHILE;
END$$
CALL seed_scramble_levels()$$
DROP PROCEDURE seed_scramble_levels$$
DELIMITER ;
