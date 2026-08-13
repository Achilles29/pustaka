INSERT INTO learn_english_rpg_episodes(code,title,subtitle,description,is_active) VALUES
('clockwork_castle','The Clockwork Castle','Episode 7 · Race Against Time','Menjelajahi kastel mekanik sambil belajar waktu, ruangan, benda, dan kata kerja.',1),
('mountain_camp','Adventure on Eagle Mountain','Episode 8 · The Stormy Summit','Petualangan berkemah dengan materi alam, perlengkapan, keselamatan, dan arah.',1)
ON DUPLICATE KEY UPDATE title=VALUES(title),subtitle=VALUES(subtitle),description=VALUES(description),is_active=1;

DROP PROCEDURE IF EXISTS seed_rpg_to_ten;
DELIMITER $$
CREATE PROCEDURE seed_rpg_to_ten(IN p_code VARCHAR(50),IN p_words TEXT,IN p_meanings TEXT,IN p_place VARCHAR(150),IN p_emoji VARCHAR(30))
BEGIN
  DECLARE v_ep INT; DECLARE v_count INT; DECLARE v_word VARCHAR(100); DECLARE v_meaning VARCHAR(150);
  SELECT id INTO v_ep FROM learn_english_rpg_episodes WHERE code COLLATE utf8mb4_general_ci=p_code LIMIT 1;
  SELECT COUNT(*) INTO v_count FROM learn_english_rpg_scenes WHERE episode_id=v_ep;
  WHILE v_count < 10 DO
    SET v_count=v_count+1;
    SET v_word=SUBSTRING_INDEX(SUBSTRING_INDEX(p_words,',',v_count),',',-1);
    SET v_meaning=SUBSTRING_INDEX(SUBSTRING_INDEX(p_meanings,',',v_count),',',-1);
    INSERT INTO learn_english_rpg_scenes(episode_id,sort_order,place,speaker,emoji,dialogue,translation,prompt,choices_json,vocabulary_word,vocabulary_meaning,is_active)
    VALUES(v_ep,v_count,p_place,'Quest Guardian',p_emoji,
      CONCAT('A glowing sign shows the word “',v_word,'”. Learn it to continue your adventure.'),
      CONCAT('Sebuah tanda bercahaya menunjukkan kata “',v_word,'”. Pelajari untuk melanjutkan petualangan.'),
      CONCAT('What does “',v_word,'” mean?'),
      JSON_ARRAY(JSON_OBJECT('text',v_meaning,'correct',1,'feedback',CONCAT('Correct! ',v_word,' means ',v_meaning,'.')),JSON_OBJECT('text','tidak ada','correct',0,'feedback','Try again and listen to the clue.'),JSON_OBJECT('text','kemarin','correct',0,'feedback','That meaning does not match the word.')),
      v_word,v_meaning,1);
  END WHILE;
END$$
DELIMITER ;

CALL seed_rpg_to_ten('lost_library','help,lantern,cross,behind,table,knowledge,welcome,ancient,whisper,return','membantu,lentera,menyeberang,di belakang,meja,pengetahuan,sama-sama,kuno,berbisik,kembali','Ancient Library','📚');
CALL seed_rpg_to_ten('market_mystery','basket,three,shelf,price,coin,cheap,expensive,fresh,customer,change','keranjang,tiga,rak,harga,koin,murah,mahal,segar,pelanggan,kembalian','Market Square','🛒');
CALL seed_rpg_to_ten('forest_rescue','footprints,raincoat,left,branch,river,careful,injured,shelter,follow,rescue','jejak kaki,jas hujan,kiri,ranting,sungai,hati-hati,terluka,tempat berlindung,mengikuti,menyelamatkan','Green Forest','🌲');
CALL seed_rpg_to_ten('space_station','rocket,button,broken,launch,helmet,float,planet,oxygen,repair,signal','roket,tombol,rusak,peluncuran,helm,melayang,planet,oksigen,memperbaiki,sinyal','Star Station','🪐');
CALL seed_rpg_to_ten('school_festival','perform,nervous,together,practice,costume,audience,stage,proud,cheer,celebrate','tampil,gugup,bersama,berlatih,kostum,penonton,panggung,bangga,bersorak,merayakan','Festival Hall','🎭');
CALL seed_rpg_to_ten('ocean_treasure','beneath,gently,treasure,coral,turtle,deep,shallow,protect,clean,pearl','di bawah,dengan lembut,harta karun,karang,penyu,dalam,dangkal,melindungi,bersih,mutiara','Coral Kingdom','🐬');
CALL seed_rpg_to_ten('clockwork_castle','clock,key,tower,stairs,lever,gear,doorway,hurry,unlock,midnight','jam,kunci,menara,tangga,tuas,roda gigi,pintu,bergegas,membuka kunci,tengah malam','Clockwork Castle','🏰');
CALL seed_rpg_to_ten('mountain_camp','mountain,tent,rope,trail,compass,campfire,storm,safe,summit,descend','gunung,tenda,tali,jalur,kompas,api unggun,badai,aman,puncak,turun','Eagle Mountain','⛰️');
DROP PROCEDURE seed_rpg_to_ten;
